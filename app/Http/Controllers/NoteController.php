<?php

namespace App\Http\Controllers;

use App\Models\Note;
use http\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $notes = Note::query()
            ->select(['id', 'user_id', 'title', 'body', 'status', 'is_pinned', 'created_at'])
            ->with([
                'user:id,first_name,last_name',
                'categories:id,name,color',
            ])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'notes' => $notes,
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],

            'title' => ['required', 'string', 'min:3', 'max:255'],
            'body'  => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_pinned' => ['sometimes', 'boolean'],

            'categories' => ['sometimes', 'array', 'max:3'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ]);

        $note = Note::create([
            'user_id'   => $validated['user_id'],
            'title'     => $validated['title'],
            'body'      => $validated['body'] ?? null,
            'status'    => $validated['status'] ?? 'draft',
            'is_pinned' => $validated['is_pinned'] ?? false,
        ]);

        if (!empty($validated['categories'])) {
            $note->categories()->sync($validated['categories']);
        }

        return response()->json([
            'message' => 'Poznámka bola úspešne vytvorená.',
            'note' => $note->load([
                'user:id,first_name,last_name',
                'categories:id,name,color',
            ]),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
        $note = Note::with([
            'user:id,first_name,last_name',
            'categories:id,name,color',
            'comments.user:id,first_name,last_name',
            'tasks.comments.user:id,first_name,last_name'
        ])->find($id);

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'note' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(
                ['message' => 'Poznámka nenájdená.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_pinned' => ['sometimes', 'boolean'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ]);

        // aktualizujeme iba to, čo prešlo validáciou
        $note->update($validated);

        // spoj. tabulku synchronizujeme iba ak boli poslané idčka
        if (array_key_exists('categories', $validated)) {
            $note->categories()->sync($validated['categories']);
        }

        return response()->json([
            'message' => 'Poznámka bola aktualizovaná.',
            'note' => $note->load([
                'user:id,first_name,last_name',
                'categories:id,name,color',
            ]),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $note->delete(); // soft delete

        return response()->json(['message' => 'Poznámka bola úspešne odstránená.'], Response::HTTP_OK);
    }


    // vlastné metódy - QB
    public function statsByStatus()
    {
        $stats = Note::whereNull('deleted_at')
            ->groupBy('status')
            ->select('status')
            ->selectRaw('COUNT(*) as count')
            ->orderBy('status')
            ->get();

        return response()->json(['stats' => $stats], Response::HTTP_OK);
    }

    public function archiveOldDrafts()
    {
        $affected = Note::query()
            ->draft()
            ->where('updated_at', '<', now()->subDays(30))
            ->update([
                'status' => 'archived',
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Staré koncepty boli archivované.',
            'affected_rows' => $affected,
        ]);
    }

    public function userNotesWithCategories(string $userId)
    {
        $notes = Note::with('categories')
            ->where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get()
            ->map(function($note) {
                return [
                    'id' => $note->id,
                    'title' => $note->title,
                    'categories' => $note->categories->pluck('name'),
                ];
            });

        return response()->json(['notes' => $notes], Response::HTTP_OK);
    }

    // ORM
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $notes = Note::searchPublished($q);

        return response()->json(['query' => $q, 'notes' => $notes], Response::HTTP_OK);
    }

    public function pin(string $id) {
        $note = Note::find($id);
        if (!$note) {
            return response()->json([
                'message' => "Poznamka nenajdena"
            ], Response::HTTP_NOT_FOUND);
        }

        $note->pin();
        return response()->json([
            'message' => "Poznamka bola pripnuta",
            'note' => $note,
        ]);
    }

    public function unpin(string $id) {
        $note = Note::find($id);
        if (!$note) {
            return response()->json([
                'message' => "Poznamka nenajdena"
            ], Response::HTTP_NOT_FOUND);
        }
        $note->unpin();
        return response()->json([
            'message' => "Poznamka bola odopnuta",
            'note' => $note,
        ]);
    }

    public function archive(string $id) {
        $note = Note::find($id);
        if (!$note) {
            return response()->json([
                'message' => "Poznamka nenajdena"
            ], Response::HTTP_NOT_FOUND);
        }
        $note->archive();
        return response()->json([
            'message' => "Poznamka bola archivovana",
            'note' => $note,
        ], Response::HTTP_OK);
    }

    public function publish(string $id) {
        $note = Note::find($id);
        if (!$note) {
            return response()->json([
                'message' => "Poznamka nenajdena"
            ], Response::HTTP_NOT_FOUND);
        }
        $note->publish();
        return response()->json([
            'message' => "Poznamka bola publikovana",
            'note' => $note,
        ], Response::HTTP_OK);
    }

    public function userNoteCount(string $userId) {
        $count = Note::countByUser($userId);
        return response()->json([
            'note_count' => $count,
            'user_id' => $userId,
        ],Response::HTTP_OK);
    }

    public function pinnedNotes() {
        $notes = Note::pinned()->orderBy('updated_at', 'desc')->get();
        return response()->json(['notes' => $notes], Response::HTTP_OK);
    }

    public function recentNotes(int $days = 7) {
        $notes = Note::recent($days)->orderBy('updated_at', 'desc')->get();
        return response()->json(['notes' => $notes], Response::HTTP_OK);
    }

    public function userDraftNotes(string $userId) {
        $notes = Note::user($userId)->draft()->orderBy('updated_at', 'desc')->get();
        return response()->json(['notes' => $notes], Response::HTTP_OK);
    }


}
