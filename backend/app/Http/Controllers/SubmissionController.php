<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    /**
     * Submit answers for a quiz (Students only).
     */
    public function store(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        if ($user->isAdmin() || $user->isTeacher()) {
            return response()->json(['message' => 'Only students can submit answers'], 403);
        }

        // Check if student already submitted
        $existing = Submission::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)->first();

        if ($existing) {
            return response()->json(['message' => 'You have already submitted this quiz'], 409);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|integer', // option_id for each question
            'comment' => 'nullable|string',
        ]);

        // Calculate score
        $questions = $quiz->questions()->with('options')->get();
        $score = 0;
        $totalPoints = 0;

        foreach ($questions as $question) {
            $totalPoints += $question->points;
            $selectedOptionId = $request->answers[$question->id] ?? null;
            if ($selectedOptionId) {
                $correctOption = $question->options->where('is_correct', true)->first();
                if ($correctOption && $correctOption->id == $selectedOptionId) {
                    $score += $question->points;
                }
            }
        }

        $submission = Submission::create([
            'quiz_id'  => $quiz->id,
            'user_id'  => $user->id,
            'answers'  => $request->answers,
            'score'    => $score,
            'comment'  => $request->comment,
        ]);

        return response()->json([
            'submission'   => $submission,
            'score'        => $score,
            'total_points' => $totalPoints,
            'percentage'   => $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0,
        ], 201);
    }

    /**
     * Get all submissions for a quiz (Admin or owner Teacher).
     */
    public function index(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        if (! $user->isAdmin() && $quiz->creator_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $submissions = Submission::with('user')
            ->where('quiz_id', $quiz->id)
            ->get();

        return response()->json($submissions);
    }

    /**
     * Get the current student's own submissions.
     */
    public function mySubmissions(Request $request)
    {
        $submissions = Submission::with('quiz')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($submissions);
    }

    /**
     * Get a single submission result.
     */
    public function show(Request $request, Submission $submission)
    {
        $user = $request->user();

        if (! $user->isAdmin() && $submission->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($submission->load('quiz.questions.options'));
    }
}
