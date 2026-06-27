@extends('layout')
@section('title')
<?= get_label('search_results', 'Search results') ?>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 mt-4 gap-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <h4 class="fw-bold mb-0 me-2" style="font-size: 1.35rem;"><?= get_label('search_results', 'Search results') ?></h4>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <nav class="breadcrumb mb-0" aria-label="breadcrumb">
                <a class="breadcrumb-item" href="{{ url('home') }}"><?= get_label('home', 'Home') ?></a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current"><?= get_label('search_results', 'Search results') ?></span>
            </nav>
        </div>
    </div>

    @if ($results->count() > 0)
    <div class="row">
        @foreach ($results as $result)
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div class="mb-3 mb-md-0">
                        <span class="badge bg-label-primary mb-2 px-3 py-2 rounded-pill">{{class_basename($result)}}</span>
                        <h5 class="mb-1 text-dark fw-bold" style="font-size: 1.1rem;">{{$result->getresult()}}</h5>
                    </div>
                    <a href="{{$result->getlink()}}" class="btn btn-outline-primary px-4">
                        <?= get_label('view_details', 'View Details') ?> <i class='bx bx-right-arrow-alt ms-1'></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
            <i class='bx bx-search fs-1 text-muted mb-3'></i>
            <h5 class="mb-0 text-muted"><?= get_label('no_results_found', 'No Results Found!') ?></h5>
        </div>
    </div>
    @endif
</div>
@endsection