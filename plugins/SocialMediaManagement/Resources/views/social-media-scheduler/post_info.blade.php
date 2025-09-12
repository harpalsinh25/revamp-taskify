@extends('layout')

@section('title',
    get_label('post_details', 'Post Details') .
    ' - ' .
    get_label(
    'social_media_scheduler',
    'Social Media
    Scheduler',
    ))

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('social.index') }}">{{ get_label('social_media', 'Social Media') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('post_details', 'Post Details') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('social.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>{{ get_label('back_to_list', 'Back to List') }}
                </a>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <!-- Main Content -->
            <div class="col-lg-8 d-flex">
                <div class="w-100 d-flex flex-column h-100">
                    <!-- Post Status Card -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i
                                    class="bx bx-info-circle me-2"></i>{{ get_label('post_information', 'Post Information') }}
                            </h5>
                            @php
                                $statusClasses = [
                                    'published' => 'bg-success',
                                    'scheduled' => 'bg-warning',
                                    'failed' => 'bg-danger',
                                    'pending' => 'bg-secondary',
                                    'partially_published' => 'bg-primary',
                                ];
                                $statusClass = $statusClasses[$post->status] ?? 'bg-secondary';
                                $postStatus = str_replace('_', ' ', ucfirst($post->status));
                            @endphp
                            <span
                                class="badge {{ $statusClass }} px-3 py-2">{{ get_label($post->status, $postStatus) }}</span>
                        </div>
                        <div class="card-body">
                            <div class="row gy-2">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>{{ get_label('created_by', 'Created By') }}:</strong>
                                        {{ $post->user->first_name }} {{ $post->user->last_name }}</p>
                                    <p class="mb-1"><strong>{{ get_label('created_at', 'Created At') }}:</strong>
                                        {{ format_date($post->created_at, false, 'Y-m-d H:i') }}</p>
                                    @if ($post->scheduled_at)
                                        <p class="mb-1">
                                            <strong>{{ get_label('scheduled_for', 'Scheduled For') }}:</strong>
                                            {{ $post->scheduled_at ? format_date($post->scheduled_at, true) : '-' }}
                                        </p>
                                    @endif
                                    <p class="mb-1"><strong>{{ get_label('platforms', 'Platforms') }}:</strong>
                                        {{ count($post->platforms) }} {{ get_label('selected', 'selected') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>{{ get_label('status', 'Status') }}:</strong>
                                        {{ $postStatus }}</p>
                                    <p class="mb-1"><strong>{{ get_label('last_updated', 'Last Updated') }}:</strong>
                                        {{ format_date($post->updated_at, false, 'Y-m-d H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Post Caption -->
                    @if ($post->caption)
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0"><i
                                        class="bx bx-text me-2"></i>{{ get_label('post_caption', 'Post Caption') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="bg-light rounded border p-3" style="max-height: 300px; overflow-y: auto;">
                                    <p class="mb-0" style="white-space: pre-line;">{!! $post->caption !!}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Media Files -->
                    @if ($mediaFiles->count() > 0)
                        <div class="card flex-fill shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i
                                        class="bx bx-image me-2"></i>{{ get_label('media_files', 'Media Files') }}</h5>
                                <span class="badge bg-primary">{{ $mediaFiles->count() }}
                                    {{ $mediaFiles->count() === 1 ? get_label('file', 'File') : get_label('files', 'Files') }}</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach ($mediaFiles as $media)
                                        <div class="col-md-4 col-sm-6">
                                            <div
                                                class="h-100 d-flex flex-column justify-content-between rounded border p-2">
                                                @if ($media['is_image'])
                                                    <a href="{{ $media['url'] }}" data-lightbox="post-media">
                                                        <img src="{{ $media['url'] }}" alt="{{ $media['name'] }}"
                                                            class="img-fluid mb-2 rounded"
                                                            style="height: 150px; width: 100%; object-fit: cover; cursor:zoom-in;">
                                                    </a>
                                                @elseif($media['is_video'])
                                                    <video class="w-100 mb-2 rounded"
                                                        style="height: 150px; object-fit: cover;" controls>
                                                        <source src="{{ $media['url'] }}"
                                                            type="{{ $media['mime_type'] }}">
                                                        {{ get_label('video_not_supported', 'Your browser does not support the video tag.') }}
                                                    </video>
                                                @endif
                                                <div class="text-center">
                                                    <small
                                                        class="text-muted d-block text-truncate">{{ $media['name'] }}</small>
                                                    <small class="text-muted">{{ $media['human_readable_size'] }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Empty spacer to fill remaining space when no media files -->
                        <div class="flex-fill"></div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 d-flex">
                <div class="w-100 d-flex flex-column h-100">
                    <!-- Platforms -->
                    <div class="card flex-fill mb-4 shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i
                                    class="bx bx-share me-2"></i>{{ get_label('publishing_platforms', 'Publishing Platforms') }}
                            </h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="flex-fill">
                                @foreach ($platformsInfo as $platform)
                                    <div class="d-flex align-items-center justify-content-between mb-2 rounded border p-3">
                                        <div class="d-flex">
                                            <i class="bx {{ $platform['icon'] }} me-2"
                                                style="color: {{ $platform['color'] }}; font-size: 1.5rem;"></i>
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-0">{{ $platform['display_name'] }}</h6>
                                                @if ($platform['status'] === 'published')
                                                    <small class="text-success">
                                                        <i
                                                            class="bx bx-check-circle me-1"></i>{{ get_label('published', 'Published') }}
                                                    </small>
                                                    <small class="text-muted">
                                                        <span>{{ $platform['published_at'] ? format_date($platform['published_at'], true) : '-' }}</span>
                                                    </small>
                                                @elseif($platform['status'] === 'failed')
                                                    <small class="text-danger">
                                                        <i
                                                            class="bx bx-x-circle me-1"></i>{{ get_label('failed', 'Failed') }}
                                                        @if (isset($platform['error']))
                                                            <br>{{ Str::limit($platform['error'], 30) }}
                                                        @endif
                                                    </small>
                                                @else
                                                    <small class="text-warning">
                                                        <i
                                                            class="bx bx-time me-1"></i>{{ get_label($platform['status'], ucfirst($platform['status'])) }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        @if (isset($platform['post_url']))
                                            <a href="{{ $platform['post_url'] }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-link-external"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-bar-chart me-2"></i>{{ get_label('summary', 'Summary') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between flex-wrap gap-3 text-center">
                                <div class="flex-fill bg-light rounded p-3">
                                    <div class="h4 text-success">
                                        {{ $platformsInfo->where('status', 'published')->count() }}</div>
                                    <small class="text-muted">{{ get_label('published', 'Published') }}</small>
                                </div>
                                <div class="flex-fill bg-light rounded p-3">
                                    <div class="h4 text-danger">{{ $platformsInfo->where('status', 'failed')->count() }}
                                    </div>
                                    <small class="text-muted">{{ get_label('failed', 'Failed') }}</small>
                                </div>
                                <div class="flex-fill bg-light rounded p-3">
                                    <div class="h4 text-warning">{{ $platformsInfo->where('status', 'pending')->count() }}
                                    </div>
                                    <small class="text-muted">{{ get_label('pending', 'Pending') }}</small>
                                </div>
                                <div class="flex-fill bg-light rounded p-3">
                                    <div class="h4 text-primary">{{ $mediaFiles->count() }}</div>
                                    <small class="text-muted">{{ get_label('media_files', 'Media Files') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
