<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Quizzes
    Route::apiResource('quizzes', QuizController::class);

    // Submissions
    Route::post('/quizzes/{quiz}/submit',      [SubmissionController::class, 'store']);
    Route::get('/quizzes/{quiz}/submissions',  [SubmissionController::class, 'index']);
    Route::get('/my-submissions',              [SubmissionController::class, 'mySubmissions']);
    Route::get('/submissions/{submission}',    [SubmissionController::class, 'show']);

    // Admin: User management
    Route::middleware('admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});
