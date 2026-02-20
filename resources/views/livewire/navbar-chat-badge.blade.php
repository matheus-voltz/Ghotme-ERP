<li class="nav-item me-2 me-xl-0" wire:poll.10s="updateCount">
    <a class="nav-link" href="{{ route('support.chat') }}">
        <div class="position-relative">
            <i class="icon-base ti tabler-message icon-md"></i>
            @if($unreadCount > 0)
                <span class="badge rounded-pill bg-danger badge-dot position-absolute top-0 start-100 translate-middle-x"></span>
            @endif
        </div>
    </a>
</li>
