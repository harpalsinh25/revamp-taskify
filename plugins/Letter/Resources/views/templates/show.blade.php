@extends('layout')

@section('title', $template->name)

@section('content')
<div class="container-fluid">

    {{-- Breadcrumb --}}
    <div class="d-flex justify-content-between mt-4 mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('letter-templates.index') }}">{{ get_label('letter_templates', 'Letter Templates') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ $template->name }}</li>
            </ol>
        </nav>
        <a href="{{ route('letter-templates.edit', $template->id) }}" class="btn btn-sm btn-primary">
            <i class="bx bx-edit"></i> {{ get_label('edit', 'Edit') }}
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $template->name }}</h5>
                    <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $template->is_active ? get_label('active', 'Active') : get_label('inactive', 'Inactive') }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>{{ get_label('category', 'Category') }}:</strong> {{ ucfirst(str_replace('_', ' ', $template->category)) }}
                    </div>
                    @if ($template->description)
                        <div class="mb-2">
                            <strong>{{ get_label('description', 'Description') }}:</strong> {{ $template->description }}
                        </div>
                    @endif

                    <hr class="my-3">

                    {{-- Live Rendered Preview --}}
                    <h6 class="text-muted mb-2">{{ get_label('preview', 'Preview') }}</h6>
                    <div class="border rounded bg-white overflow-auto" style="max-height: 80vh;">
                        @php
                            $logo_url = $general_settings['full_logo'] ?? asset('storage/logos/default_full_logo.png');
                            $company_title = $general_settings['company_title'] ?? env('APP_NAME');
                            $content = \Plugins\Letter\Helper\LetterHelper::processContent($template->content, $template->user ?? null);


                            echo View::make('letters::templates.render', [
                                'logo_url' => $logo_url,
                                'company_title' => $company_title,
                                'content' => $content,
                            ])->render();
                        @endphp
                    </div>

                    {{-- Meta --}}
                    <div class="text-muted small mt-3">
                        {{ get_label('created_at', 'Created At') }}: {{ format_date($template->created_at, true) }}<br>
                        {{ get_label('updated_at', 'Updated At') }}: {{ format_date($template->updated_at, true) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Variables Sidebar --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">{{ get_label('available_variables', 'Available Variables') }}</h6>
                </div>
                <div class="card-body">
                    @php
                        $variablesGrouped = \Plugins\Letter\Helper\LetterHelper::getAvailableVariables();
                    @endphp
                    @foreach ($variablesGrouped as $groupName => $variables)
                        <label class="form-label text-muted text-uppercase small mt-2">{{ ucfirst($groupName) }} {{ get_label('variables', 'Variables') }}</label>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @forelse ($variables as $key => $label)
                                <span class="badge bg-label-secondary small">
                                    {{ '{' . strtoupper($key) . '}' }}
                                </span>
                            @empty
                                <span class="text-muted small">No {{ $groupName }} variables available.</span>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
