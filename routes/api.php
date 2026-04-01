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
    });

    Route::middleware(['auth:sanctum', 'verified'])->get('/verified', function () {
        return 'ok';
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // všetci prihlásení môžu čítať kategórie
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

    // iba admin môže vytvárať, upravovať, mazať kategórie
    Route::middleware('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    });
});

Route::get('notes/stats/status', [NoteController::class, 'statsByStatus']);
Route::get('notes/pinned', [NoteController::class, 'pinnedNotes']);
Route::get('notes/recent/{days?}', [NoteController::class, 'recentNotes']);
Route::get('notes-actions/search', [NoteController::class, 'search']);

Route::apiResource('notes', NoteController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('notes.tasks', TaskController::class)->scoped();

Route::patch('notes/actions/archive-old-drafts', [NoteController::class, 'archiveOldDrafts']);
Route::patch('notes/{id}/pin', [NoteController::class, 'pin']);
Route::patch('notes/{id}/unpin', [NoteController::class, 'unpin']);
Route::patch('notes/{id}/archive', [NoteController::class, 'archive']);
Route::patch('notes/{id}/publish', [NoteController::class, 'publish']);

Route::get('users/{user}/notes', [NoteController::class, 'userNotesWithCategories']);
Route::patch('users/{user}/notes/count', [NoteController::class, 'userNoteCount']);
Route::get('users/{user}/draft-notes', [NoteController::class, 'userDraftNotes']);
