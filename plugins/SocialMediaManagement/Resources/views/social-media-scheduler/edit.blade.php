<!DOCTYPE html>

@extends('layout')

@section('title')
    {{ get_label('edit_social_post', 'Edit Social Post') }}
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('assets/css/social/social.css') }}">
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a
                            href="{{ route('social.index') }}">{{ get_label('social_media', 'Social Media') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ get_label('edit_post', 'Edit Post') }}
                    </li>
                </ol>
            </nav>
            <div>
                <span class="status-badge {{ $post->status }}">
                    {{ ucfirst($post->status) }}
                </span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bx bx-edit me-1"></i>
                            {{ get_label('edit_post', 'Edit Post') }}
                            #{{ $post->id }}
                        </h4>
                    </div>
                    <div class="card-body mt-2">
                        <form id="edit-post-form" class="form-submit-event"
                            action="{{ route('social.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf


                            <!-- Social Caption AI Section -->
                            <div class="social-caption-ai-wrapper">
                                <!-- AI Generation Controls Row -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ get_label('caption', 'Caption') }}</label>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="d-flex align-items-center justify-content-md-end">
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input social-caption-enable-custom-prompt"
                                                    type="checkbox" id="socialCaptionEnableCustomPrompt">
                                                <label class="form-check-label" for="socialCaptionEnableCustomPrompt">
                                                    {{ get_label('use_custom_prompt', 'Use Custom Prompt') }}
                                                </label>
                                            </div>
                                            <button type="button"
                                                class="btn btn-outline-primary social-caption-generate-ai btn-sm">
                                                <i class="fas fa-magic me-1"></i>
                                                {{ get_label('generate_with_ai', 'Generate with AI') }}
                                            </button>
                                            <i class="bx bx-info-circle text-primary ms-2" data-bs-toggle="tooltip"
                                                data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true"
                                                title=""
                                                data-bs-original-title="<b>{{ get_label('generate_with_ai', 'Generate with AI') }}:</b> {{ get_label('ai_caption_help', 'Enable custom prompt to write your own AI instructions. If disabled, AI will enhance existing caption or create a new engaging one based on selected platforms.') }}">
                                            </i>
                                            <div class="spinner-border text-primary social-caption-ai-loader d-none ms-2"
                                                role="status">
                                                <span
                                                    class="visually-hidden">{{ get_label('loading', 'Loading...') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Custom Prompt Input (initially hidden) -->
                                <div class="social-caption-custom-prompt-container d-none mb-3">
                                    <label
                                        class="form-label text-muted">{{ get_label('custom_prompt', 'Custom Prompt') }}</label>
                                    <textarea class="form-control social-caption-ai-custom-prompt" rows="3" maxlength="500"
                                        placeholder="{{ get_label('enter_custom_prompt_caption', 'E.g., Create a fun, engaging caption with emojis for a product launch post targeting young professionals...') }}"></textarea>
                                    <small
                                        class="text-muted">{{ get_label('custom_prompt_help', 'Describe what kind of caption you want. Max 500 characters.') }}</small>
                                </div>

                                <!-- Caption Textarea -->
                                <div class="mb-3">
                                    <textarea class="form-control caption social-caption-ai-output" id="social-media-caption" name="caption" rows="4"
                                        maxlength="2000" placeholder="{{ get_label('enter_post_caption', "What's on your mind?") }}">{{ old('caption', $post->caption) }}</textarea>
                                    <div class="char-counter mt-2">
                                        <div class="progress">
                                            <div id="charProgress" class="progress-bar bg-success" style="width: 0%"></div>
                                        </div>
                                        <small class="char-counter-text" id="caption-count">0/2000</small>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bx bx-bulb me-1"></i>
                                        {{ get_label('caption_tip', 'Tip: Select platforms and upload media first for better AI-generated captions!') }}
                                    </small>
                                </div>
                            </div>

                            @if ($post->getMedia('social-media')->count() > 0)
                                <div class="mb-3" id="existing-media-section">
                                    <label class="form-label">{{ get_label('current_media', 'Current Media') }}</label>
                                    <div class="row g-2" id="existing-media-preview">
                                        @foreach ($post->getMedia('social-media') as $media)
                                            <div class="col-6 col-md-3">
                                                <div class="media-item position-relative">
                                                    @if (Str::startsWith($media->mime_type, 'image/'))
                                                        <img src="{{ $media->getUrl() }}"
                                                            class="media-thumb img-fluid rounded" alt="Media">
                                                    @elseif(Str::startsWith($media->mime_type, 'video/'))
                                                        <video class="media-thumb img-fluid rounded" muted>
                                                            <source src="{{ $media->getUrl() }}"
                                                                type="{{ $media->mime_type }}">
                                                        </video>
                                                        <div class="position-absolute start-0 top-0 m-2">
                                                            <span class="badge bg-dark"><i
                                                                    class="bx bx-play-circle me-1"></i></span>
                                                        </div>
                                                    @endif
                                                    <button type="button" class="remove-media"
                                                        data-media-id="{{ $media->id }}">
                                                        <i class="bx bx-x"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Media Upload -->
                            <div class="mb-3">
                                <label class="form-label">{{ get_label('add_media_files', 'Add Media Files') }}</label>
                                <input type="file" class="form-control" name="media[]" multiple accept="image/*,video/*"
                                    id="media-upload">
                                <small
                                    class="text-muted">{{ get_label('media_upload_help', 'Supported formats: JPG, PNG, GIF, MP4. Max size: 10MB per file.') }}</small>
                            </div>

                            <!-- Platform Selection -->
                            <div class="mb-3">
                                <label class="form-label">{{ get_label('select_platforms', 'Select Platforms') }} <span
                                        class="text-danger">*</span></label>
                                <div class="platform-selector">
                                    <div class="platform-card {{ in_array('facebook', $post->platforms ?? []) ? 'selected' : '' }}"
                                        data-platform="facebook">
                                        <i class="bx bxl-facebook-circle platform-icon" style="color: #1877f2;"></i>
                                        <div class="fw-semibold">{{ get_label('facebook', 'Facebook') }}</div>
                                        <input type="checkbox" class="d-none" name="platforms[]" value="facebook"
                                            id="platform-facebook"
                                            {{ in_array('facebook', $post->platforms ?? []) ? 'checked' : '' }}>
                                    </div>
                                    <div class="platform-card {{ in_array('instagram', $post->platforms ?? []) ? 'selected' : '' }}"
                                        data-platform="instagram">
                                        <i class="bx bxl-instagram platform-icon" style="color: #e4405f;"></i>
                                        <div class="fw-semibold">{{ get_label('instagram', 'Instagram') }}</div>
                                        <input type="checkbox" class="d-none" name="platforms[]" value="instagram"
                                            id="platform-instagram"
                                            {{ in_array('instagram', $post->platforms ?? []) ? 'checked' : '' }}>
                                    </div>
                                    <div class="platform-card {{ in_array('linkedin', $post->platforms ?? []) ? 'selected' : '' }}"
                                        data-platform="linkedin">
                                        <i class="bx bxl-linkedin platform-icon" style="color: #0077b5;"></i>
                                        <div class="fw-semibold">{{ get_label('linkedin', 'LinkedIn') }}</div>
                                        <input type="checkbox" class="d-none" name="platforms[]" value="linkedin"
                                            id="platform-linkedin"
                                            {{ in_array('linkedin', $post->platforms ?? []) ? 'checked' : '' }}>
                                    </div>
                                    <div class="platform-card {{ in_array('pinterest', $post->platforms ?? []) ? 'selected' : '' }}"
                                        data-platform="pinterest">
                                        <i class="bx bxl-pinterest platform-icon" style="color: #e60023;"></i>
                                        <div class="fw-semibold">{{ get_label('pinterest', 'Pinterest') }}</div>
                                        <input type="checkbox" class="d-none" name="platforms[]" value="pinterest"
                                            id="platform-pinterest"
                                            {{ in_array('pinterest', $post->platforms ?? []) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <!-- Post Type -->
                            <div class="mb-3">
                                <label class="form-label">{{ get_label('when_to_post', 'When to Post') }}</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="post_type"
                                                value="now" id="post-now"
                                                {{ old('post_type', $post->scheduled_at ? 'schedule' : 'now') === 'now' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="post-now">
                                                <i class="bx bx-send me-1"></i>
                                                {{ get_label('post_now', 'Post Now') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="post_type"
                                                value="schedule" id="post-schedule"
                                                {{ old('post_type', $post->scheduled_at ? 'schedule' : 'now') === 'schedule' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="post-schedule">
                                                <i class="bx bx-calendar me-1"></i>
                                                {{ get_label('schedule_post', 'Schedule Post') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Schedule DateTime -->
                            <div class="mb-3" id="schedule-section"
                                style="display: {{ old('post_type', $post->scheduled_at ? 'schedule' : 'now') === 'schedule' ? 'block' : 'none' }};">
                                <label
                                    class="form-label">{{ get_label('schedule_date_time', 'Schedule Date & Time') }}</label>
                                @php
                                    $userTimezone = $settings['general']['timezone'] ?? 'Asia/Kolkata';
                                    $scheduledValue = $post->scheduled_at
                                        ? $post->scheduled_at->copy()->setTimezone($userTimezone)->format('Y-m-d\TH:i')
                                        : '';
                                @endphp

                                <input type="datetime-local" class="form-control" name="scheduled_at"
                                    min="{{ now($userTimezone)->format('Y-m-d\TH:i') }}"
                                    value="{{ old('scheduled_at', $scheduledValue) }}">
                            </div>

                            <!-- EDIT: Additional Info -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>{{ get_label('created_at', 'Created') }}:</strong>
                                        {{ $post->created_at->format('M d, Y h:i A') }}
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>{{ get_label('last_updated', 'Last Updated') }}:</strong>
                                        {{ $post->updated_at->format('M d, Y h:i A') }}
                                    </small>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary me-2" id="submit_btn">
                                    <i class="bx bx-save me-1"></i>
                                    <span id="submit-text">{{ get_label('update_post', 'Update Post') }}</span>
                                </button>
                                <a href="{{ route('social.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-x me-1"></i>
                                    {{ get_label('cancel', 'Cancel') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bx bx-show me-1"></i>{{ get_label('post_preview', 'Post Preview') }}
                        </h5>
                        <ul class="nav nav-pills platform-preview-selector">
                        </ul>
                    </div>
                    <div class="card-body">
                        <div id="post-preview" class="post-preview">
                            <div class="text-muted py-3 text-center">
                                {{ get_label('preview_will_appear_here', 'Post preview will appear here...') }}
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">{{ get_label('selected_platforms', 'Selected Platforms:') }}</small>
                            <div id="selectedPlatforms" class="mt-1">
                                <small
                                    class="text-muted">{{ get_label('no_platforms_selected', 'No Platforms Selected') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i
                                class="bx bx-info-circle me-1"></i>{{ get_label('platform_requirements', 'Platform Requirements') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="platformAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#facebook-req">
                                        <i class="bx bxl-facebook-circle platform-icon me-1" style="color: #1877f2;"></i>
                                        {{ get_label('facebook', 'Facebook') }}
                                    </button>
                                </h2>
                                <div id="facebook-req" class="accordion-collapse collapse"
                                    data-bs-parent="#platformAccordion">
                                    <div class="accordion-body">
                                        <small class="text-muted">
                                            • {{ get_label('text_limit', 'Text: Up to 63,206 characters') }}<br>
                                            • {{ get_label('image_formats', 'Images: JPG, PNG, GIF') }}<br>
                                            • {{ get_label('video_formats', 'Videos: MP4, MOV, AVI') }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#instagram-req">
                                        <i class="bx bxl-instagram platform-icon me-1" style="color: #e4405f;"></i>
                                        {{ get_label('instagram', 'Instagram') }}
                                    </button>
                                </h2>
                                <div id="instagram-req" class="accordion-collapse collapse"
                                    data-bs-parent="#platformAccordion">
                                    <div class="accordion-body">
                                        <small class="text-muted">
                                            • {{ get_label('text_limit', 'Text: Up to 2,200 characters') }}<br>
                                            • {{ get_label('image_required', 'Images: Required (JPG, PNG)') }}<br>
                                            • {{ get_label('square_format', 'Square format recommended') }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#pinterest-req">
                                        <i class="bx bxl-pinterest platform-icon me-1" style="color: #bd081c;"></i>
                                        {{ get_label('pinterest', 'Pinterest') }}
                                    </button>
                                </h2>
                                <div id="pinterest-req" class="accordion-collapse collapse"
                                    data-bs-parent="#platformAccordion">
                                    <div class="accordion-body">
                                        <small class="text-muted">
                                            •
                                            {{ get_label('text_limit', 'Text: Up to 500 characters for Pin description') }}<br>
                                            • {{ get_label('image_required', 'Images: Required (JPG, PNG)') }}<br>
                                            •
                                            {{ get_label('vertical_format', 'Vertical format recommended (2:3 aspect ratio)') }}<br>
                                            • {{ get_label('max_image_size', 'Max image size: 20 MB') }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#linkedin-req">
                                        <i class="bx bxl-linkedin platform-icon me-1" style="color: #0a66c2;"></i>
                                        {{ get_label('linkedin', 'LinkedIn') }}
                                    </button>
                                </h2>
                                <div id="linkedin-req" class="accordion-collapse collapse"
                                    data-bs-parent="#platformAccordion">
                                    <div class="accordion-body">
                                        <small class="text-muted">
                                            • {{ get_label('text_limit', 'Text: Up to 3,000 characters for posts') }}<br>
                                            • {{ get_label('image_optional', 'Images: Optional (JPG, PNG)') }}<br>
                                            •
                                            {{ get_label('recommended_image_size', 'Recommended image size: 1200 × 627 px') }}<br>
                                            • {{ get_label('max_image_size', 'Max image size: 5 MB') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- EDIT: Confirmation Modal for Media Deletion -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">
                        {{ get_label('confirm_deletion', 'Confirm Deletion') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ get_label('confirm_delete_media', 'Are you sure you want to remove this media? This action cannot be undone.') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ get_label('cancel', 'Cancel') }}</button>
                    <button type="button" class="btn btn-danger"
                        id="confirmDeleteBtn">{{ get_label('delete', 'Delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.labels = {
            schedule_post: '{!! addslashes(get_label('schedule_post', 'Schedule Post')) !!}',
            post_now: '{!! addslashes(get_label('post_now', 'Post Now')) !!}',
            update_post: '{!! addslashes(get_label('update_post', 'Update Post')) !!}',
            preview_will_appear_here: '{!! addslashes(get_label('preview_will_appear_here', 'Post preview will appear here...')) !!}',
            no_platforms_selected: '{!! addslashes(get_label('no_platforms_selected', 'No Platforms Selected')) !!}',
            use_custom_prompt: '{!! addslashes(get_label('use_custom_prompt', 'Use Custom Prompt')) !!}',
            generate_with_ai: '{!! addslashes(get_label('generate_with_ai', 'Generate with AI')) !!}',
            enter_custom_prompt: '{!! addslashes(get_label('enter_custom_prompt', 'Enter custom prompt for AI generation')) !!}',
            enter_custom_prompt_first: '{!! addslashes(get_label('enter_custom_prompt_first', 'Please enter a custom prompt first.')) !!}',
            something_went_wrong: '{!! addslashes(get_label('something_went_wrong', 'Something went wrong. Please try again.')) !!}',
            media_deleted_successfully: '{!! addslashes(get_label('media_deleted_successfully', 'Media deleted successfully.')) !!}',
            error_deleting_media: '{!! addslashes(get_label('error_deleting_media', 'Error deleting media. Please try again.')) !!}'
        };

        //Pass existing data to JavaScript
        window.existingMedia = [
            @foreach ($post->getMedia('social-media') as $index => $media)

                {
                    id: {{ $media->id }},
                    type: "{{ Str::startsWith($media->mime_type, 'image') ? 'image' : 'video' }}",
                    path: "{{ $media->getUrl() }}",
                    mime_type: "{{ $media->mime_type }}",
                    size: {{ $media->size }}
                }
                {{ $loop->last ? '' : ',' }}
            @endforeach
        ];

        window.existingCaption = {!! json_encode(old('caption', $post->caption)) !!};
        window.isEditMode = true;
        window.postId = {{ $post->id }};
    </script>

    <script src="{{ asset('assets/js/social/social.js') }}"></script>
@endsection
