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
<link rel="stylesheet" href="{{ asset('assets/css/social/social.css') }}">
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
                <a href="{{ route('social.index') }}" class="tk-btn tk-btn-secondary tk-btn-sm">
                    <i class="bx bx-arrow-back"></i>{{ get_label('back_to_list', 'Back to List') }}
                </a>
            </div>
        </div>

        <div class="row g-4 mt-2">
            <!-- Main Content -->
            <div class="col-lg-8 d-flex">
                <div class="w-100 d-flex flex-column h-100" style="gap:16px">

                    <!-- Post Information Card -->
                    <div class="tk-card">
                        <div class="tk-card-head">
                            <h6 class="tk-card-title" style="display:flex;align-items:center;gap:6px">
                                <i class="bx bx-info-circle" style="color:var(--signal);font-size:16px"></i>
                                {{ get_label('post_information', 'Post Information') }}
                            </h6>
                            @php
                                $statusBadge = [
                                    'published' => 'tk-badge-success',
                                    'scheduled' => 'tk-badge-warning',
                                    'failed' => 'tk-badge-danger',
                                    'pending' => '',
                                    'partially_published' => 'tk-badge-primary',
                                ];
                                $badgeClass = $statusBadge[$post->status] ?? '';
                                $postStatus = str_replace('_', ' ', ucfirst($post->status));
                            @endphp
                            <span class="tk-badge {{ $badgeClass }}">{{ get_label($post->status, $postStatus) }}</span>
                        </div>
                        <div class="tk-card-body">
                            <div class="tk-meta" style="grid-template-columns:110px 1fr">
                                <dt>{{ get_label('created_by', 'Created By') }}</dt>
                                <dd>{{ $post->user->first_name }} {{ $post->user->last_name }}</dd>

                                <dt>{{ get_label('created_at', 'Created At') }}</dt>
                                <dd>{{ format_date($post->created_at, false, 'Y-m-d H:i') }}</dd>

                                @if ($post->scheduled_at)
                                <dt>{{ get_label('scheduled_for', 'Scheduled For') }}</dt>
                                <dd>{{ $post->scheduled_at ? format_date($post->scheduled_at, true) : '-' }}</dd>
                                @endif

                                <dt>{{ get_label('platforms', 'Platforms') }}</dt>
                                <dd>{{ count($post->platforms) }} {{ get_label('selected', 'selected') }}</dd>

                                <dt>{{ get_label('status', 'Status') }}</dt>
                                <dd>{{ $postStatus }}</dd>

                                <dt>{{ get_label('last_updated', 'Last Updated') }}</dt>
                                <dd>{{ format_date($post->updated_at, false, 'Y-m-d H:i') }}</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Post Caption -->
                    @if ($post->caption)
                        <div class="tk-card">
                            <div class="tk-card-head">
                                <h6 class="tk-card-title" style="display:flex;align-items:center;gap:6px">
                                    <i class="bx bx-text" style="color:var(--signal);font-size:16px"></i>
                                    {{ get_label('post_caption', 'Post Caption') }}
                                </h6>
                            </div>
                            <div class="tk-card-body">
                                <div class="tk-tile" style="max-height:300px;overflow-y:auto">
                                    <p style="margin:0;white-space:pre-line;color:var(--fg-1);font-size:var(--fs-base);line-height:1.6">{!! $post->caption !!}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Media Files -->
                    @if ($mediaFiles->count() > 0)
                        <div class="tk-card" style="flex:1 1 auto">
                            <div class="tk-card-head">
                                <h6 class="tk-card-title" style="display:flex;align-items:center;gap:6px">
                                    <i class="bx bx-image" style="color:var(--signal);font-size:16px"></i>
                                    {{ get_label('media_files', 'Media Files') }}
                                </h6>
                                <span class="tk-badge tk-badge-primary">{{ $mediaFiles->count() }}
                                    {{ $mediaFiles->count() === 1 ? get_label('file', 'File') : get_label('files', 'Files') }}</span>
                            </div>
                            <div class="tk-card-body">
                                <div class="row g-3">
                                    @foreach ($mediaFiles as $media)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="tk-tile" style="height:100%;display:flex;flex-direction:column;justify-content:space-between">
                                                @if ($media['is_image'])
                                                    <a href="{{ $media['url'] }}" data-lightbox="post-media">
                                                        <img src="{{ $media['url'] }}" alt="{{ $media['name'] }}"
                                                            style="width:100%;height:150px;object-fit:cover;border-radius:var(--r-2);cursor:zoom-in;margin-bottom:8px">
                                                    </a>
                                                @elseif($media['is_video'])
                                                    <video style="width:100%;height:150px;object-fit:cover;border-radius:var(--r-2);margin-bottom:8px" controls>
                                                        <source src="{{ $media['url'] }}"
                                                            type="{{ $media['mime_type'] }}">
                                                        {{ get_label('video_not_supported', 'Your browser does not support the video tag.') }}
                                                    </video>
                                                @endif
                                                <div style="text-align:center">
                                                    <span class="tk-muted" style="font-size:var(--fs-sm);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $media['name'] }}</span>
                                                    <span class="tk-muted" style="font-size:var(--fs-xs)">{{ $media['human_readable_size'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div style="flex:1 1 auto"></div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 d-flex">
                <div class="w-100 d-flex flex-column h-100" style="gap:16px">

                    <!-- Publishing Platforms -->
                    <div class="tk-card" style="flex:1 1 auto">
                        <div class="tk-card-head">
                            <h6 class="tk-card-title" style="display:flex;align-items:center;gap:6px">
                                <i class="bx bx-share" style="color:var(--signal);font-size:16px"></i>
                                {{ get_label('publishing_platforms', 'Publishing Platforms') }}
                            </h6>
                        </div>
                        <div class="tk-card-body">
                            <div class="tk-stack" style="gap:8px">
                                @foreach ($platformsInfo as $platform)
                                    <div class="tk-tile">
                                        <div class="tk-between" style="margin-bottom:4px">
                                            <div class="tk-cluster">
                                                <i class="bx {{ $platform['icon'] }}"
                                                    style="color:{{ $platform['color'] }};font-size:22px"></i>
                                                <span style="font-weight:600;color:var(--fg-0);font-size:var(--fs-md)">{{ $platform['display_name'] }}</span>
                                            </div>
                                            @if (isset($platform['post_url']))
                                                <a href="{{ $platform['post_url'] }}" target="_blank" class="tk-iconbtn" title="View post">
                                                    <i class="bx bx-link-external" style="font-size:16px"></i>
                                                </a>
                                            @endif
                                        </div>
                                        @if ($platform['status'] === 'published')
                                            <div class="tk-cluster" style="gap:4px">
                                                <span class="tk-badge tk-badge-success">
                                                    <i class="bx bx-check-circle" style="font-size:12px"></i>
                                                    {{ get_label('published', 'Published') }}
                                                </span>
                                                <span class="tk-muted" style="font-size:var(--fs-xs)">
                                                    {{ $platform['published_at'] ? format_date($platform['published_at'], true) : '-' }}
                                                </span>
                                            </div>
                                        @elseif($platform['status'] === 'failed')
                                            <div class="tk-stack" style="gap:4px">
                                                <span class="tk-badge tk-badge-danger">
                                                    <i class="bx bx-x-circle" style="font-size:12px"></i>
                                                    {{ get_label('failed', 'Failed') }}
                                                </span>
                                                @if (isset($platform['error']))
                                                    <span class="tk-muted" style="font-size:var(--fs-xs)">{{ Str::limit($platform['error'], 30) }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="tk-badge tk-badge-warning">
                                                <i class="bx bx-time" style="font-size:12px"></i>
                                                {{ get_label($platform['status'], ucfirst($platform['status'])) }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="tk-card">
                        <div class="tk-card-head">
                            <h6 class="tk-card-title" style="display:flex;align-items:center;gap:6px">
                                <i class="bx bx-bar-chart" style="color:var(--signal);font-size:16px"></i>
                                {{ get_label('summary', 'Summary') }}
                            </h6>
                        </div>
                        <div class="tk-card-body">
                            <div class="tk-facts">
                                <div class="tk-fact" style="flex-direction:column;align-items:center;text-align:center;padding:14px 11px">
                                    <span class="tk-fact-v" style="font-size:20px;color:var(--ok)">
                                        {{ $platformsInfo->where('status', 'published')->count() }}
                                    </span>
                                    <span class="tk-fact-k">{{ get_label('published', 'Published') }}</span>
                                </div>
                                <div class="tk-fact" style="flex-direction:column;align-items:center;text-align:center;padding:14px 11px">
                                    <span class="tk-fact-v" style="font-size:20px;color:var(--err)">
                                        {{ $platformsInfo->where('status', 'failed')->count() }}
                                    </span>
                                    <span class="tk-fact-k">{{ get_label('failed', 'Failed') }}</span>
                                </div>
                                <div class="tk-fact" style="flex-direction:column;align-items:center;text-align:center;padding:14px 11px">
                                    <span class="tk-fact-v" style="font-size:20px;color:var(--warn)">
                                        {{ $platformsInfo->where('status', 'pending')->count() }}
                                    </span>
                                    <span class="tk-fact-k">{{ get_label('pending', 'Pending') }}</span>
                                </div>
                                <div class="tk-fact" style="flex-direction:column;align-items:center;text-align:center;padding:14px 11px">
                                    <span class="tk-fact-v" style="font-size:20px;color:var(--signal)">
                                        {{ $mediaFiles->count() }}
                                    </span>
                                    <span class="tk-fact-k">{{ get_label('media_files', 'Media Files') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
