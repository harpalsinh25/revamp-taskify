@extends('layout')

@section('title')
    {{ get_label('create_letter_template', 'Create Letter Template') }}
@endsection

@section('content')
    <div class="container-fluid">


        {{-- Card --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ get_label('create_letter_template', 'Create Letter Template') }}</h5>
                <a href="{{ route('letter-templates.index') }}" class="btn btn-sm btn-light">
                    <i class="bx bx-arrow-back"></i> {{ get_label('back', 'Back') }}
                </a>
            </div>
            <div class="card-body">
                <form id="create_letter_template_form" class="form-submit-event" method="POST" action="{{ route('letter-templates.store') }}">
                    @csrf
                    <input type="hidden" name="redirect_url" value="{{ route('letter-templates.index') }}">
                    <div class="row g-3">

                        {{-- Name --}}
                        <div class="col-md-6">
                            <label class="form-label required">{{ get_label('name', 'Name') }}</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter template name"
                                required>
                        </div>

                        {{-- Category --}}
                        <div class="col-md-6">
                            <label class="form-label required">{{ get_label('category', 'Category') }}</label>
                            <select name="category" id="category" class="form-select" required>
                                <option value="">{{ get_label('select_category', 'Select Category') }}</option>
                                @foreach (\Plugins\Letter\Helper\LetterHelper::getLetterCategories() as $key => $category)
                                    <option value="{{ $key }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Description --}}
                        <div class="col-md-12">
                            <label class="form-label">{{ get_label('description', 'Description') }}</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional brief description"></textarea>
                        </div>

                        {{-- Content --}}
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label required">{{ get_label('content', 'Content') }}</label>
                                <button type="button" id="load_sample_content" class="btn btn-sm btn-light">
                                    <i class="bx bx-download"></i>
                                    {{ get_label('load_sample_content', 'Load Sample Content') }}
                                </button>
                            </div>
                            <textarea id="content" name="content" class="form-control min-vh-40"></textarea>
                        </div>

                        {{-- Active --}}
                        <div class="col-md-3 mt-2">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" id="create_is_active"
                                    checked>
                                <label class="form-check-label"
                                    for="create_is_active">{{ get_label('active', 'Active') }}</label>
                            </div>
                        </div>

                        {{-- Available Variables --}}
                        <div class="col-md-12 mt-4">
                            <label class="form-label">{{ get_label('available_variables', 'Available Variables') }}</label>

                            @php
                                $variablesGrouped = \Plugins\Letter\Helper\LetterHelper::getAvailableVariables();
                            @endphp

                            <div class="mb-2">
                                <input type="text" id="variable-search" class="form-control form-control-sm"
                                    placeholder="Search variables to filter">
                            </div>

                            @foreach ($variablesGrouped as $groupName => $variables)
                                <div class="mt-2">
                                    <label
                                        class="form-label text-muted text-uppercase small mb-1">{{ ucfirst($groupName) }}
                                        Variables</label>
                                    <div class="d-flex variable-group flex-wrap gap-2"
                                        data-group="{{ strtolower($groupName) }}">
                                        @forelse ($variables as $key => $label)
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary copy-variable-btn"
                                                data-variable="{{ '{' . strtoupper($key) . '}' }}" title="Click to copy">
                                                {{ '{' . strtoupper($key) . '}' }}
                                            </button>
                                        @empty
                                            <span class="text-muted small">No {{ $groupName }} variables
                                                available.</span>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach

                        </div>

                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ route('letter-templates.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-x"></i> {{ get_label('cancel', 'Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save"></i> {{ get_label('create', 'Create') }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        var sampleContentUrl = @json(route('letter-templates.sample_content'));

    </script>
    <script src="{{ asset('assets/js/letter-plugin/letter-template-create.js') }}">
    </script>
@endsection
