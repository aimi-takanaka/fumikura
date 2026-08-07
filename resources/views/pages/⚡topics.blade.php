<?php

use App\Livewire\Concerns\ListsTopics;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.public')] class extends Component
{
    use ListsTopics;

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return $this->topicListData(9);
    }
}; ?>

<div>
    @include('partials.topic-list', ['heading' => 'お題一覧'])
</div>
