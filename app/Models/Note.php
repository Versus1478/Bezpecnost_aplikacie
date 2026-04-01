<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Note extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'notes';

    protected $primaryKey = 'id';

    //public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'status',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function publish(): bool
    {
        $this->status = 'published';
        return $this->save();
    }

    public function archive(): bool
    {
        $this->status = 'archived';
        return $this->save();
    }

    public static function searchPublished(string $q, int $limit = 20)
    {
        $q = trim($q);

        return static::query()
            ->where('status', 'published')
            ->where(function (Builder $x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                    ->orWhere('body', 'like', "%{$q}%");
            })
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    public function pin() {
        $this->update(['is_pinned' => true]);
    }

    public function unpin() {
        $this->update(['is_pinned' => false]);
    }

    public static function countByUser(int $userId): int {
        return static::where('user_id', $userId)->count();
    }

    public function scopePinned($query) {
        return $query->where('is_pinned', true);
    }

    public function scopePublished($query) {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query) {
        return $query->where('status', 'draft');
    }

    public function scopeRecent($query, int $days = 7) {
        return $query->where('updated_at', '>=', now()->subDays($days));
    }

    public function scopeUser($query, int $userId) {
        return $query->where('user_id', $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'note_category')->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'note_id', 'id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

}
