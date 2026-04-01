<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'color',
    ];

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_category')->withTimestamps();
    }
}
