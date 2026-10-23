{{-- One message in the Chat tab of the task page. Also returned after sending, to add it without a reload. --}}
@php($isOwn = $chat->user_id === auth()->id())
<div class="task-chat-item {{ $isOwn ? 'is-own' : '' }}" data-created-at="{{ $chat->created_at?->timestamp ?? 0 }}">
    <div class="task-chat-avatar">{{ strtoupper(mb_substr($chat->user?->name ?? '?', 0, 1)) }}</div>
    <div class="task-chat-bubble">
        <div class="task-chat-meta">
            <strong>{{ $isOwn ? 'You' : $chat->user?->name ?? 'Unknown user' }}</strong>
            <span title="{{ $chat->created_at?->format('d M Y, g:i A') }}">{{ $chat->created_at?->format('d M Y, g:i A') }}</span>
        </div>
        <div class="task-chat-body">{!! $chat->message !!}</div>
    </div>
</div>
