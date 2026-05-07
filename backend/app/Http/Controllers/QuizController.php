<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    /**
     * List all quizzes (students see only their year, teachers see their own).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $quizzes = Quiz::with('creator', 'questions')->latest()->get();
        } elseif ($user->isTeacher()) {
            $quizzes = Quiz::with('creator', 'questions')
                ->where('creator_id', $user->id)
                ->latest()->get();
        } else {
            // Students see only quizzes matching their year
            $quizzes = Quiz::with('creator', 'questions')
                ->where('year', $user->year)
                ->latest()->get();
        }

        return response()->json($quizzes);
    }

    /**
     * Create a new quiz (Admin or Teacher only).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->isTeacher()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'course_id'   => 'required|exists:courses,id',
            'year'        => 'required|integer|min:1|max:4',
            'description' => 'nullable|string',
            'document'    => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'questions'   => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.points'        => 'nullable|integer|min:1',
            'questions.*.options'       => 'required|array|size:4',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct'  => 'required|boolean',
        ]);

        $course = \App\Models\Course::find($request->course_id);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('quiz_documents', 'public');
        }

        $quiz = Quiz::create([
            'course_id'     => $request->course_id,
            'title'         => $course->name, // Automatically set title from course name
            'description'   => $request->description,
            'year'          => $request->year,
            'document_path' => $documentPath,
            'creator_id'    => $user->id,
        ]);

        foreach ($request->questions as $qData) {
            $question = $quiz->questions()->create([
                'question_text' => $qData['question_text'],
                'points'        => $qData['points'] ?? 1,
            ]);

            foreach ($qData['options'] as $oData) {
                $question->options()->create([
                    'option_text' => $oData['option_text'],
                    'is_correct'  => $oData['is_correct'],
                ]);
            }
        }

        return response()->json($quiz->load('questions.options'), 201);
    }

    /**
     * Show a single quiz with questions and options.
     */
    public function show(Quiz $quiz)
    {
        return response()->json($quiz->load('questions.options', 'creator'));
    }

    /**
     * Update a quiz (Admin or owner Teacher).
     */
    public function update(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        if (! $user->isAdmin() && $quiz->creator_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'year'        => 'nullable|integer|min:1|max:4',
            'document'    => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data = $request->only('title', 'description', 'year');

        if ($request->hasFile('document')) {
            if ($quiz->document_path) {
                Storage::disk('public')->delete($quiz->document_path);
            }
            $data['document_path'] = $request->file('document')->store('quiz_documents', 'public');
        }

        $quiz->update($data);

        return response()->json($quiz->load('questions.options'));
    }

    /**
     * Delete a quiz (Admin or owner Teacher).
     */
    public function destroy(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        if (! $user->isAdmin() && $quiz->creator_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($quiz->document_path) {
            Storage::disk('public')->delete($quiz->document_path);
        }

        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully']);
    }
}
