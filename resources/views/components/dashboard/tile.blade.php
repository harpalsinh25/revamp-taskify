<div class="col-lg-3 col-sm-6 mb-4">
    <div class="card h-100 custom-card {{ $customCardClass }}" id="{{ $id ?? '' }}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <!-- Left Side -->
                <div class="card-info">
                    <p class="text-muted fw-medium text-uppercase small mb-2">
                        {{ $label }}
                    </p>
                    <h3 class="card-title count fw-bold mb-1">{{ $count }}</h3>
                    <a href="{{ $url }}" class="text-decoration-none {{ $linkColor }} fw-medium">
                        <small><i class="bx bx-right-arrow-alt align-middle"></i>
                            {{ get_label('view_more', 'View more') }}</small>
                    </a>
                </div>
                <!-- Right Side (Icon) -->
                <div class="avatar flex-shrink-0">
                    <span class="avatar-initial {{ $iconBg }} rounded">
                        <i class="{{ $icon }} fs-3"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .custom-card {
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border: none;
        background: #fff;
        overflow: hidden;
        position: relative;
    }

    /* Subtle corner gradient */
    .custom-card::before {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        width: 150px; /* Smaller size for subtle effect */
        height: 130px;
        background: radial-gradient(circle at top right,
            var(--card-glow-color, rgba(0, 123, 255, 0.2)) 0%,
            transparent 70%);
        z-index: 0;
        pointer-events: none;
    }

    .custom-card .card-body {
        position: relative;
        z-index: 1;
    }

    /* Map bg-label-* classes to gradient colors */
    .custom-card.custom-card-primary::before {
        --card-glow-color: rgba(var(--bs-primary-rgb), 0.2);
    }
    .custom-card.custom-card-success::before {
        --card-glow-color: rgba(var(--bs-success-rgb), 0.2);
    }
    .custom-card.custom-card-danger::before {
        --card-glow-color: rgba(var(--bs-danger-rgb), 0.2);
    }
    .custom-card.custom-card-warning::before {
        --card-glow-color: rgba(var(--bs-warning-rgb), 0.2);
    }
    .custom-card.custom-card-info::before {
        --card-glow-color: rgba(var(--bs-info-rgb), 0.2);
    }
    .custom-card.custom-card-secondary::before {
        --card-glow-color: rgba(var(--bs-secondary-rgb), 0.2);
    }
</style>
