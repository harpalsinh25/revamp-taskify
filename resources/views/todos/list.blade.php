@extends('layout')
@section('title')
    <?= get_label('todo_list', 'Todo list') ?>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('home') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('todos', 'Todos') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <span data-bs-toggle="modal" data-bs-target="#create_todo_modal"><a href="javascript:void(0);"
                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('create_todo', 'Create todo') ?>"><i
                            class='bx bx-plus'></i></a></span>
            </div>
        </div>
        @php
            $total_todos = $todos->count();
            $completed_todos = $todos->where('is_completed', '1')->count();
            $progress = $total_todos > 0 ? ($completed_todos / $total_todos) * 100 : 0;
            $progress = number_format($progress, 2);
        @endphp
        <div class="tk-card p-3 mb-4">
            <div class="d-flex justify-content-between tk-fg-2 small mb-2">
                <span class="fw-bold">{{ get_label('todos_overview', 'Todos Overview') }}</span>
                <span class="fw-bold tk-fg-1">{{ $completed_todos }} / {{ $total_todos }} ({{ $progress }}%)</span>
            </div>
            <div class="progress" style="height: 6px; background-color: var(--line);">
                <div class="progress-bar" style="background-color: var(--signal); width: {{ $progress }}%;" role="progressbar"
                    aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
        <div class="row g-4">
            <!-- Unfinished Tasks Column -->
            <div class="col-lg-6">
                <div class="tk-card h-100 todo-card todo-card-incomplete" style="border-top: 3px solid var(--signal);">
                    <div class="p-4 border-bottom position-relative bg-transparent todo-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center justify-content-center rounded me-3" style="width: 36px; height: 36px; background: rgba(105, 108, 255, 0.08); color: var(--signal);">
                                    <i class="bx bx-list-check fs-5"></i>
                                </div>
                                <h5 class="fw-bold mb-0 tk-fg-0">
                                    {{ get_label('incomplete_todos', 'Incomplete Todo\'s') }}</h5>
                            </div>
                            <span class="badge bg-secondary rounded-pill px-3 todo-counter">{{ $todos->where('is_completed', 0)->count() }}</span>
                        </div>
                    </div>
                    <div class="p-4">
                        <div style="max-height: 500px; overflow-y: auto;" class="pe-2 todo-list-container">
                            @if ($todos->where('is_completed', 0)->count() > 0)
                                @foreach ($todos->where('is_completed', 0) as $incomplete_todo)
                                    @php
                                        $priorityClass = 'badge-primary';
                                        if ($incomplete_todo->priority == 'high') {
                                            $priorityClass = 'badge-err';
                                        } elseif ($incomplete_todo->priority == 'medium') {
                                            $priorityClass = 'badge-warn';
                                        } elseif ($incomplete_todo->priority == 'low') {
                                            $priorityClass = 'badge-info';
                                        }
                                    @endphp
                                    <div class="tk-card mb-3 p-3 d-flex flex-row align-items-center todo-item todo-priority-{{ $incomplete_todo->priority }}"
                                        data-todo-id="{{ $incomplete_todo->id }}">
                                        <div class="tk-muted me-2 todo-drag-handle" style="cursor: grab;">
                                            <i class="bx bx-grid-vertical fs-4"></i>
                                        </div>
                                        <div class="me-3">
                                            <input type="checkbox" class="form-check-input border-2 todo-check-input" style="width: 18px; height: 18px;"
                                                id="{{ $incomplete_todo->id }}" onclick='update_status(this)'
                                                name="{{ $incomplete_todo->id }}">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1 tk-fg-0 todo-title">{{ $incomplete_todo->title }}</h6>
                                            <div class="d-flex align-items-center gap-2 small tk-muted todo-meta">
                                                <span class="todo-meta-item"><i class="bx bx-calendar-alt me-1"></i>{{ format_date($incomplete_todo->created_at) }}</span>
                                                <span class="badge {{ $priorityClass }}">
                                                    {{ ucfirst($incomplete_todo->priority) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="todo-actions-container">
                                            <div class="d-flex">
                                                <a href="javascript:void(0);" class="edit-todo me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_todo_modal" data-id="{{ $incomplete_todo->id }}"
                                                    title="<?= get_label('update', 'Update') ?>"><i
                                                        class='bx bx-edit fs-5'></i></a>
                                                <a href="javascript:void(0);" type="button"
                                                    data-id="{{ $incomplete_todo->id }}" data-type="todos"
                                                    data-reload="true" title="<?= get_label('delete', 'Delete') ?>"
                                                    class="delete text-danger"><i
                                                        class='bx bx-trash fs-5'></i></a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted py-3 text-center">{{ get_label('no_incomplete_todos', 'No incomplete todos') }}</div>
                            @endif
                            <div class="mt-3">
                                <div class="d-flex align-items-center bg-transparent border-0 p-2"
                                    data-list="incomplete">
                                    <div class="me-3"></div>
                                    <div class="flex-grow-1">
                                        <input type="text"
                                            class="new-todo-title form-control form-control-sm shadow-none"
                                            placeholder="{{ get_label('add_todo_info', 'Add todo (Enter to save)') }}"
                                            data-list="incomplete">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Completed Tasks Column -->
            <div class="col-lg-6">
                <div class="tk-card h-100 todo-card todo-card-complete" style="border-top: 3px solid var(--success);">
                    <div class="p-4 border-bottom position-relative bg-transparent todo-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center justify-content-center rounded me-3" style="width: 36px; height: 36px; background: rgba(40, 199, 111, 0.08); color: var(--success);">
                                    <i class="bx bx-check-double fs-5"></i>
                                </div>
                                <h5 class="fw-bold mb-0 tk-fg-0">
                                    {{ get_label('completed_todos', 'Completed Todo\'s') }}</h5>
                            </div>
                            <span class="badge bg-secondary rounded-pill px-3 todo-counter">{{ $todos->where('is_completed', '1')->count() }}</span>
                        </div>
                    </div>
                    <div class="p-4">
                        <div style="max-height: 500px; overflow-y: auto;" class="pe-2 todo-list-container">
                            @if ($todos->where('is_completed', '1')->count() > 0)
                                @foreach ($todos->where('is_completed', '1') as $completed_todo)
                                    <div class="tk-card mb-3 p-3 d-flex flex-row align-items-center todo-item todo-completed todo-priority-{{ $completed_todo->priority }}"
                                        data-todo-id="{{ $completed_todo->id }}">
                                        <div class="tk-muted me-2 todo-drag-handle" style="cursor: grab;">
                                            <i class="bx bx-grid-vertical fs-4"></i>
                                        </div>
                                        <div class="me-3">
                                            <input type="checkbox" class="form-check-input border-2 border-success bg-success todo-check-input" style="width: 18px; height: 18px;"
                                                id="{{ $completed_todo->id }}" onclick='update_status(this)'
                                                name="{{ $completed_todo->id }}" checked>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1 tk-muted text-decoration-line-through todo-title">{{ $completed_todo->title }}</h6>
                                            <div class="d-flex align-items-center gap-2 small tk-muted todo-meta">
                                                <span class="todo-meta-item"><i class="bx bx-calendar-alt me-1"></i>{{ format_date($completed_todo->created_at) }}</span>
                                                <span class="badge badge-ok"><i class="bx bx-check-double me-1"></i>
                                                    {{ get_label('completed', 'Completed') }}</span>
                                            </div>
                                        </div>
                                        <div class="todo-actions-container">
                                            <div class="d-flex">
                                                <a href="javascript:void(0);" class="edit-todo me-2" data-bs-toggle="modal"
                                                    data-bs-target="#edit_todo_modal" data-id="{{ $completed_todo->id }}"
                                                    title="<?= get_label('update', 'Update') ?>"><i
                                                        class='bx bx-edit fs-5'></i></a>
                                                <a href="javascript:void(0);" type="button"
                                                    data-id="{{ $completed_todo->id }}" data-type="todos"
                                                    data-reload="true" title="<?= get_label('delete', 'Delete') ?>"
                                                    class="delete text-danger"><i
                                                        class='bx bx-trash fs-5'></i></a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted py-3 text-center">{{ get_label('no_completed_todos', 'No completed todos') }}</div>
                            @endif
                            <div class="mt-3">
                                <div class="d-flex align-items-center bg-transparent border-0 p-2"
                                    data-list="complete">
                                    <div class="me-3"></div>
                                    <div class="flex-grow-1">
                                        <input type="text"
                                            class="new-todo-title form-control form-control-sm shadow-none"
                                            placeholder="{{ get_label('add_todo_info', 'Add todo (Enter to save)') }}"
                                            data-list="complete">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/todos.js') }}"></script>
@endsection
