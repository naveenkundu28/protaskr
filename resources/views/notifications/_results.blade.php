<div class="notifications-list" role="list">
    @forelse($notifications as $notification)
        @php($senderName = $notification->sender?->name ?? 'Someone')
        @php($senderInitials = collect(preg_split('/\s+/', trim($senderName)))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode(''))
        <a class="notification-page-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}" role="listitem" href="{{ route('tasks.mentions.open', $notification) }}">
            <span class="notification-page-avatar avatar-tone-{{ $notification->mentioned_by % 5 }}" aria-hidden="true">{{ mb_strtoupper($senderInitials) }}</span>
            <span class="notification-page-content">
                <span class="notification-page-main"><strong>{{ $senderName }}</strong> mentioned you in a comment</span>
                <span class="notification-page-excerpt">{{ $notification->message_excerpt }}</span>
                <small class="notification-page-related"><i class="bi bi-file-earmark-text" aria-hidden="true"></i>{{ $notification->task?->title ?? 'Task unavailable' }}@if($notification->task?->deliveryProject) <span>·</span> {{ $notification->task->deliveryProject->name }} @endif</small>
            </span>
            <time class="notification-page-time">{{ $notification->created_at->diffForHumans() }}</time>
            @if(!$notification->read_at)<i class="notification-unread-dot" aria-label="Unread"></i>@else<i class="notification-read-dot" aria-label="Read"></i>@endif
            <i class="bi bi-chevron-right notification-page-chevron" aria-hidden="true"></i>
        </a>
    @empty
        <div class="notifications-empty">No notifications match your search.</div>
    @endforelse
</div>
<div class="notifications-list-footer">
    <span class="notifications-range">
        @if($notifications->total())
            Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} notifications
        @else
            Showing 0 notifications
        @endif
    </span>
    <div class="notifications-pagination">{{ $notifications->onEachSide(1)->links() }}</div>
</div>
