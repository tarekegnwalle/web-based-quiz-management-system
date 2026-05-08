<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users (Admin only).
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        return response()->json($query->latest()->get());
    }

    /**
     * Create a new user (Admin only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:admin,teacher,student',
            'year'     => 'nullable|integer|min:1|max:4',
            'status'   => 'nullable|string|in:active,pending',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'year'     => $request->role === 'student' ? $request->year : null,
            'status'   => $request->status ?? 'active',
        ]);

        return response()->json($user, 201);
    }

    /**
     * Show a specific user (Admin only).
     */
    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update a user (Admin only).
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8',
            'role'     => 'sometimes|required|in:admin,teacher,student',
            'year'     => 'nullable|integer|min:1|max:4',
            'status'   => 'sometimes|required|string|in:active,pending',
        ]);

        $data = $request->only('name', 'email', 'role', 'year', 'status');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if (isset($data['role']) && $data['role'] !== 'student') {
            $data['year'] = null;
        }

        $user->update($data);

        return response()->json($user);
    }

    /**
     * Delete a user (Admin only).
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Import users from CSV (Admin only).
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        ini_set('auto_detect_line_endings', true);
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // Read header

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($header) !== count($row)) {
                continue; // Skip rows that don't match the header count
            }
            $data = array_combine($header, $row);

            // Basic validation/existence check
            if (empty($data['email']) || User::where('email', $data['email'])->exists()) {
                continue;
            }

            $password = !empty($data['password']) ? $data['password'] : 'Student@123';

            User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($password),
                'role'     => $data['role'] ?? 'student',
                'year'     => ($data['role'] ?? 'student') === 'student' ? ($data['year'] ?? 1) : null,
                'status'   => $data['status'] ?? 'pending',
            ]);
            $count++;
        }
        fclose($handle);

        return response()->json(['message' => "Successfully imported $count users"]);
    }
}
