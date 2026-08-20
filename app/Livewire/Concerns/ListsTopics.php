<?php

namespace App\Livewire\Concerns;

use App\Models\Image;
use App\StoryStatus;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

trait ListsTopics
{
    use WithPagination;

    public string $sort = 'latest';

    /**
     * Reset pagination when the sort order changes.
     */
    public function updatedSort(): void
    {
        $this->resetPage();
    }

    /**
     * Build the sorted image query for the topic list.
     *
     * @return Builder<Image>
     */
    private function imagesQuery(): Builder
    {
        $query = Image::query()
            ->with('user')
            ->withCount([
                'stories as completed_stories_count' => fn (Builder $query) => $query->complete(),
                'stories as idea_stories_count' => fn (Builder $query) => $query->where('status', StoryStatus::Idea),
                'stories as stories_count',
                'likes',
            ]);

        return match ($this->sort) {
            'popular' => $query->orderByDesc('stories_count')->orderByDesc('id'),
            'most_completed' => $query->orderByDesc('completed_stories_count')->orderByDesc('id'),
            default => $query->latest(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function topicListData(int $perPage): array
    {
        return [
            'images' => $this->imagesQuery()->paginate($perPage),
        ];
    }
}
