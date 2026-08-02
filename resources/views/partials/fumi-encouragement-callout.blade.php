@if (session('fumi_message'))
    <flux:callout color="lime" icon="chat-bubble-left-right" class="mb-6" dismissible>
        <flux:callout.heading>ふみより</flux:callout.heading>
        <flux:callout.text class="whitespace-pre-line">{{ session('fumi_message') }}</flux:callout.text>
    </flux:callout>
@endif
