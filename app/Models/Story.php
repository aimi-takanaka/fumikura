<?php

namespace App\Models;

use App\StoryStatus;
use Database\Factories\StoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $image_id
 * @property int $user_id
 * @property string $title
 * @property string $body
 * @property StoryStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['image_id', 'user_id', 'title', 'body', 'status'])]
class Story extends Model
{
    /** @use HasFactory<StoryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StoryStatus::class,
        ];
    }

    /**
     * The image (prompt) this story belongs to.
     *
     * @return BelongsTo<Image, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * The user who wrote this story.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The comments posted on this story.
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * The likes for this story.
     *
     * @return HasMany<Like, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * The bookmarks for this story.
     *
     * @return HasMany<Bookmark, $this>
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Scope a query to only include completed stories.
     *
     * @param  Builder<Story>  $query
     */
    public function scopeComplete(Builder $query): void
    {
        $query->where('status', StoryStatus::Complete);
    }

    /**
     * Scope a query to stories readable by the given user.
     *
     * @param  Builder<Story>  $query
     */
    public function scopeReadableBy(Builder $query, ?User $user): void
    {
        if ($user === null) {
            $query->where('status', StoryStatus::Complete);

            return;
        }

        $query->where(function (Builder $query) use ($user): void {
            $query->whereIn('status', [StoryStatus::Complete, StoryStatus::Idea])
                ->orWhere(function (Builder $query) use ($user): void {
                    $query->where('status', StoryStatus::Draft)
                        ->where('user_id', $user->id);
                });
        });
    }

    /**
     * Determine whether the story can be read by the given user.
     */
    public function isReadableBy(?User $user): bool
    {
        return match ($this->status) {
            StoryStatus::Complete => true,
            StoryStatus::Idea => $user !== null,
            StoryStatus::Draft => $user?->is($this->user) ?? false,
        };
    }
}
