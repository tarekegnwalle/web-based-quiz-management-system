<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * List all courses (filtered by year if provided).
     */
    public function index(Request $request)
    {
        $query = Course::query();
        if ($request->has('year')) {
            $query->where('year', $request->year);
        }
        return response()->json($query->orderBy('name')->get());
    }

    /**
     * Create a new course (Admin only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'year' => 'required|integer|min:1|max:4',
        ]);

        $course = Course::create($request->all());
        return response()->json($course, 201);
    }

    /**
     * Delete a course (Admin only).
     */
    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json(['message' => 'Course deleted successfully']);
    }

    /**
     * Bulk import courses from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        ini_set('auto_detect_line_endings', true);
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($header) !== count($row)) {
                continue;
            }
            $data = array_combine($header, $row);
            Course::create([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'year' => $data['year'],
            ]);
            $count++;
        }
        fclose($handle);

        return response()->json(['message' => "Successfully imported $count courses"]);
    }
}
