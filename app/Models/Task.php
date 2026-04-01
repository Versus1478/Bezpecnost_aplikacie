<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'note_id',
        'title',
        'is_done',
        'due_at',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'due_at' => 'datetime',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'note_id', 'id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    // tu budú relácie
}
