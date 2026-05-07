<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * List all quizzes (students see all, teachers see their own).
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
            // Students see all quizzes
            $quizzes = Quiz::with('creator')->latest()->get();
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
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions'   => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.points'        => 'nullable|integer|min:1',
            'questions.*.options'       => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct'  => 'required|boolean',
        ]);

        $quiz = Quiz::create([
            'title'       => $request->title,
            'description' => $request->description,
            'creator_id'  => $user->id,
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
        ]);

        $quiz->update($request->only('title', 'description'));

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

        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully']);
    }
}
