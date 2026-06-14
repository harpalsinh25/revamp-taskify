@extends('layout')

@section('title')
    <?= get_label('social_media_calendar', 'Social Media Calendar') ?>
@endsection
@section('content')
    <link rel="stylesheet" href="{{ asset('assets/css/social/social.css') }}">
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/home') }}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a
                                href="{{ route('social.index') }}">{{ get_label('social_media', 'Social Media') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ get_label('calendar', 'Calendar') }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('social.create') }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title="{{ get_label('create_post', 'Create Post') }}">
                        <i class="bx bx-plus"></i>
                    </button>
                </a>
                <a href="{{ route('social.index') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('list_view', 'List View') ?>"><i
                            class='bx bx-list-ul'></i></button></a>
            </div>
        </div>

        <div class="calendar-container">
            <!-- Header -->
            <div class="calendar-header todo-gradient-primary">
                <div class="calendar-nav">
                    <button class="nav-btn" id="prevMonth">
                        <i class="bx bx-chevron-left"></i>
                    </button>
                    <h3 id="currentMonth" class="mb-0 text-white">{{ now()->format('F Y') }}</h3>
                    <button class="nav-btn" id="nextMonth">
                        <i class="bx bx-chevron-right"></i>
                    </button>
                    <button class="nav-btn" id="todayBtn" title="{{ get_label('go_to_today', 'Go to Today') }}">
                        <i class="bx bx-calendar-event"></i>
                    </button>
                </div>

                <div class="view-controls">
                    <button class="view-btn active" data-view="month">{{ get_label('month', 'Month') }}</button>
                    <button class="view-btn" data-view="week">{{ get_label('week', 'Week') }}</button>
                    <a href="{{ route('social.create') }}" class="btn btn-light">
                        <i class="bx bx-plus"></i> {{ get_label('new_post', 'New Post') }}
                    </a>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-count text-success" id="publishedCount">0</div>
                    <div class="stat-label">{{ get_label('published', 'Published') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-count text-warning" id="scheduledCount">0</div>
                    <div class="stat-label">{{ get_label('scheduled', 'Scheduled') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-count text-danger" id="failedCount">0</div>
                    <div class="stat-label">{{ get_label('failed', 'Failed') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-count text-info" id="partialCount">0</div>
                    <div class="stat-label">{{ get_label('partial', 'Partial') }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="calendar-filters">
                <div class="filter-group">
                    <div class="filter-item active" data-filter="all">
                        <i class="bx bx-globe"></i>
                        <span>{{ get_label('all_posts', 'All Posts') }}</span>
                        <span class="badge bg-secondary" id="allCount">0</span>
                    </div>
                    <div class="filter-item" data-filter="facebook">
                        <i class="bx bxl-facebook-circle platform-icon" style="color: #1877f2;"></i>
                        <span>Facebook</span>
                        <span class="badge bg-secondary" id="facebookCount">0</span>
                    </div>
                    <div class="filter-item" data-filter="instagram">
                        <i class="bx bxl-instagram platform-icon" style="color: #e4405f;"></i>
                        <span>Instagram</span>
                        <span class="badge bg-secondary" id="instagramCount">0</span>
                    </div>
                    <div class="filter-item" data-filter="linkedin">
                        <i class="bx bxl-linkedin platform-icon" style="color: #0077b5;"></i>
                        <span>LinkedIn</span>
                        <span class="badge bg-secondary" id="linkedinCount">0</span>
                    </div>
                    <div class="filter-item" data-filter="pinterest">
                        <i class="bx bxl-pinterest platform-icon" style="color: #e60023;"></i>
                        <span>Pinterest</span>
                        <span class="badge bg-secondary" id="pinterestCount">0</span>
                    </div>

                    <div style="margin-left: auto; display: flex; gap: 10px;">
                        <div class="filter-item" data-status="published">
                            <div class="bg-success" style="width: 12px; height: 12px; border-radius: 2px;"></div>
                            <span>{{ get_label('published', 'Published') }}</span>
                        </div>
                        <div class="filter-item" data-status="scheduled">
                            <div class="bg-warning" style="width: 12px; height: 12px; border-radius: 2px;"></div>
                            <span>{{ get_label('scheduled', 'Scheduled') }}</span>
                        </div>
                        <div class="filter-item" data-status="failed">
                            <div class="bg-danger" style="width: 12px; height: 12px; border-radius: 2px;"></div>
                            <span>{{ get_label('failed', 'Failed') }}</span>
                        </div>
                        <div class="filter-item" data-status="pending">
                            <div class="bg-secondary" style="width: 12px; height: 12px; border-radius: 2px;"></div>
                            <span>{{ get_label('pending', 'Pending') }}</span>
                        </div>
                        <div class="filter-item" data-status="partially_published">
                            <div class="bg-primary" style="width: 12px; height: 12px; border-radius: 2px;"></div>
                            <span>{{ get_label('partial', 'Partial') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div id="calendarContent">
                <div class="loading">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="background:var(--bg-1);border:1px solid var(--line);border-radius:var(--r-3)">
                <div class="modal-header" style="border-bottom:1px solid var(--line);padding:14px 18px">
                    <h6 class="modal-title" id="quickViewModalLabel" style="font-size:14px;font-weight:600;color:var(--fg-0);display:flex;align-items:center;gap:8px">
                        <i class="bx bx-show-alt" style="color:var(--signal);font-size:18px"></i>
                        {{ get_label('post_publishing_details', 'Post Publishing Details') }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter:var(--btn-close-filter,none)"></button>
                </div>
                <div class="modal-body" style="padding:18px">
                    <div id="quickViewContent">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <script>
        window.calendarConfig = {
            routes: {
                calendarData: '{{ route('social.calendar.data') }}',
                socialCreate: '{{ route('social.create') }}',
            },
            labels: {
                sun: '{{ get_label('sun', 'Sun') }}',
                mon: '{{ get_label('mon', 'Mon') }}',
                tue: '{{ get_label('tue', 'Tue') }}',
                wed: '{{ get_label('wed', 'Wed') }}',
                thu: '{{ get_label('thu', 'Thu') }}',
                fri: '{{ get_label('fri', 'Fri') }}',
                sat: '{{ get_label('sat', 'Sat') }}',
                addPost: '{{ get_label('add_post', 'Add Post') }}',
                successfulPlatforms: '{{ get_label('successful_platforms', 'Successful Platforms') }}',
                failedPlatforms: '{{ get_label('failed_platforms', 'Failed Platforms') }}',
                caption: '{{ get_label('caption', 'Caption') }}',
                noCaption: '{{ get_label('no_caption', 'No caption') }}',
                media: '{{ get_label('media', 'Media') }}',
                details: '{{ get_label('details', 'Details') }}',
                status: '{{ get_label('status', 'Status') }}',
                platforms: '{{ get_label('platforms', 'Platforms') }}',
                scheduled: '{{ get_label('scheduled', 'Scheduled') }}',
                created: '{{ get_label('created', 'Created') }}',
                author: '{{ get_label('author', 'Author') }}',
                postDetails: '{{ get_label('post_details', 'Post Details') }}'
            }
        };
    </script>
    <script src="{{ asset('assets/js/social/social-calendar.js') }}"></script>
    <script src="{{ asset('assets/js/social/social.js') }}"></script>
@endsection
