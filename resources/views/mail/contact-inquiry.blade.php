<x-mail::message>
# お問い合わせを受け付けました

以下の内容でお問い合わせがありました。

**お名前**  
{{ $inquiry->name }}

**メールアドレス**  
{{ $inquiry->email }}

**件名**  
{{ $inquiry->subject }}

**お問い合わせ内容**  
{{ $inquiry->body }}

受信日時: {{ $inquiry->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
</x-mail::message>
