<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Note;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $noteId)
    {
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'tasks' => $note->tasks()->get()
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $noteId)
    {
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'is_done' => ['sometimes', 'boolean'],
            'due_at' => ['nullable', 'date']
        ]);

        $task = $note->tasks()->create($validated);

        return response()->json([
            'message' => 'Úloha bola vytvorená.',
            'task' => $task
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $noteId, string $taskId)
    {
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $task = $note->tasks()
            ->with('comments.user:id,first_name,last_name')
            ->where('id', $taskId)
            ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Úloha nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'task' => $task
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $noteId, int $taskId)
    {
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $task = $note->tasks()->where('id', $taskId)->first();

        if (!$task) {
            return response()->json([
                'message' => 'Úloha nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'is_done' => ['sometimes', 'boolean'],
            'due_at' => ['nullable', 'date'],
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Úloha bola úspešne aktualizovaná.',
            'task' => $task->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $noteId, string $taskId)
    {
        $note = Note::find($noteId);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $task = $note->tasks()->where('id', $taskId)->first();

        if (!$task) {
            return response()->json([
                'message' => 'Úloha nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        $task->delete();

        return response()->json([
            'message' => 'Úloha bola odstránená.'
        ], Response::HTTP_OK);
    }
}
