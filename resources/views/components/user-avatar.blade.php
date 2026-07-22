@props([
    'user' => null,
    'size' => 'sm',
])

@if ($user)
    <flux:avatar
        :name="$user->name"
        :initials="$user->initials()"
        :src="$user->avatarUrl()"
        :size="$size"
    />
@endif
