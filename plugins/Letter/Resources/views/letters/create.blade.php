@extends('layout')

@section('title')
    <?= get_label('create_letter', 'Create Letter') ?>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('letters.index') }}">{{ get_label('letters', 'Letters') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ get_label('create', 'Create') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Form -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form id="createLetterForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ get_label('title', 'Title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" required placeholder="{{ get_label('enter_title', 'Enter Title') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ get_label('letter_date', 'Letter Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="letter_date" id="letter_date" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ get_label('template', 'Template') }}</label>
                            <select name="template_id" id="template_id" class="form-select">
                                <option value="">{{ get_label('select_template', 'Select Template') }}</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ get_label('user', 'User') }} <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-select" required>
                                <option value="">{{ get_label('select_user', 'Select User') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ get_label('content', 'Content') }} <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" class="form-control" rows="8" required placeholder="{{ get_label('enter_content', 'Enter letter content') }}"></textarea>
                        </div>

                        <div class="d-flex justify-content-start gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" id="saveLetterBtn">{{ get_label('create', 'Create') }}</button>
                            <button type="button" class="btn btn-outline-secondary" id="previewLetterBtn">{{ get_label('preview', 'Preview') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">{{ get_label('preview', 'Preview') }}</div>
                <div class="card-body" id="letterPreview">
                    <p class="text-muted">{{ get_label('preview_will_appear_here', 'Your letter preview will appear here...') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

    function updatePreview() {
        let data = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            title: $('#title').val(),
            letter_date: $('#letter_date').val(),
            template_id: $('#template_id').val(),
            user_id: $('#user_id').val(),
            content: $('#content').val()
        };

        $.post('/letters/preview', data, function(response) {
            $('#letterPreview').html(response.preview);
        }).fail(function(xhr) {
            $('#letterPreview').html('<p class="text-danger">Error generating preview.</p>');
        });
    }

    $('#previewLetterBtn').on('click', function() {
        updatePreview();
    });

    $('#template_id').on('change', function() {
        let templateId = $(this).val();
        if (templateId) {
            $.get('/letters/template/' + templateId, function(response) {
                $('#content').val(response.content);
                updatePreview();
            });
        }
    });

    $('#content, #title, #letter_date, #user_id').on('input change', function() {
        // Optional live auto-preview as user types
        // updatePreview();
    });

    $('#createLetterForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let submitBtn = $('#saveLetterBtn');
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Saving...');

        $.post('/letters', form.serialize(), function(response) {
            if (response.success) {
                window.location.href = '/letters';
            } else {
                alert('Error: ' + response.message);
            }
        }).fail(function(xhr) {
            alert('Error: ' + xhr.responseText);
        }).always(function() {
            submitBtn.prop('disabled', false).html('{{ get_label('create', 'Create') }}');
        });
    });
});

</script>
@endsection
