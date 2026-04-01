<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::patch('/change-password', [AuthController::class, 'changePassword']);
    });
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);

    Route::middleware('admin')->group(function () {
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
    });

    Route::prefix('notes')->group(function () {
        Route::get('stats/status', [NoteController::class, 'statsByStatus']);
        Route::get('pinned', [NoteController::class, 'pinnedNotes']);
        Route::get('recent/{days?}', [NoteController::class, 'recentNotes']);
        Route::get('search', [NoteController::class, 'search']);
        Route::patch('actions/archive-old-drafts', [NoteController::class, 'archiveOldDrafts']);

        Route::patch('{id}/pin', [NoteController::class, 'pin']);
        Route::patch('{id}/unpin', [NoteController::class, 'unpin']);
        Route::patch('{id}/archive', [NoteController::class, 'archive']);
        Route::patch('{id}/publish', [NoteController::class, 'publish']);
    });

    Route::apiResource('notes', NoteController::class);
    Route::apiResource('notes.tasks', TaskController::class)->scoped();

    Route::prefix('users/{user}')->group(function () {
        Route::get('notes', [NoteController::class, 'userNotesWithCategories']);
        Route::patch('notes/count', [NoteController::class, 'userNoteCount']);
        Route::get('draft-notes', [NoteController::class, 'userDraftNotes']);
    });

    Route::middleware('verified')->get('/verified-check', function () {
        return 'ok';
    });
});
