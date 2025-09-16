@php
    $flag =
        (Request::segment(1) == 'home' || Request::segment(1) == 'users' || Request::segment(1) == 'clients') &&
        (strtolower($type) == 'projects' || strtolower($type) == 'tasks')
            ? 0
            : 1;
    $currentPath = request()->path();
    $showCreateButton = !in_array($currentPath, ['projects/list/favorite', 'projects/favorite']);
@endphp
<div class="<?= $flag == 1 ? 'card ' : '' ?>text-center empty-state">
    @if ($flag == 1)
        <div class="card-body">
    @endif
    <div class="misc-wrapper">
        <h2 class="mx-2 mb-2"><?= get_label(strtolower($type), $type) . ' ' . get_label('not_found', 'Not Found') ?></h2>
        <p class="mx-2 mb-4"><?= get_label('oops!', 'Oops!') ?> 😖
            <?= get_label('data_does_not_exists', 'Data does not exists') ?>.</p>
        @if ($type != 'Notifications' && $showCreateButton)
            @php
                $typeSlug = strtolower(str_replace(' ', '-', $type));
                $modalMap = [
                    'todos' => ['target' => '#create_todo_modal', 'toggle' => 'modal'],
                    'tags' => ['target' => '#create_tag_modal', 'toggle' => 'modal'],
                    'status' => ['target' => '#create_status_modal', 'toggle' => 'modal'],
                    'leave-requests' => ['target' => '#create_leave_request_modal', 'toggle' => 'modal'],
                    'contract-types' => ['target' => '#create_contract_type_modal', 'toggle' => 'modal'],
                    'contracts' => ['target' => '#create_contract_modal', 'toggle' => 'modal'],
                    'payment-methods' => ['target' => '#create_pm_modal', 'toggle' => 'modal'],
                    'allowances' => ['target' => '#create_allowance_modal', 'toggle' => 'modal'],
                    'deductions' => ['target' => '#create_deduction_modal', 'toggle' => 'modal'],
                    'notes' => ['target' => '#create_note_modal', 'toggle' => 'modal'],
                    'timesheet' => ['target' => '#timerModal', 'toggle' => 'modal'],
                    'taxes' => ['target' => '#create_tax_modal', 'toggle' => 'modal'],
                    'units' => ['target' => '#create_unit_modal', 'toggle' => 'modal'],
                    'items' => ['target' => '#create_item_modal', 'toggle' => 'modal'],
                    'expense-types' => ['target' => '#create_expense_type_modal', 'toggle' => 'modal'],
                    'expenses' => ['target' => '#create_expense_modal', 'toggle' => 'modal'],
                    'payments' => ['target' => '#create_payment_modal', 'toggle' => 'modal'],
                    'languages' => ['target' => '#create_language_modal', 'toggle' => 'modal'],
                    'tasks' => ['target' => '#create_task_offcanvas', 'toggle' => 'offcanvas'], // ✅ offcanvas
                    'projects' => ['target' => '#create_project_offcanvas', 'toggle' => 'offcanvas'], // ✅ offcanvas
                    'priorities' => ['target' => '#create_priority_modal', 'toggle' => 'modal'],
                    'workspaces' => ['target' => '#createWorkspaceModal', 'toggle' => 'modal'],
                    'meetings' => ['target' => '#createMeetingModal', 'toggle' => 'modal'],
                    'task-lists' => ['target' => '#create_task_list_modal', 'toggle' => 'modal'],
                    'lead-sources' => ['target' => '#create_lead_source_modal', 'toggle' => 'modal'],
                    'lead-stages' => ['target' => '#create_lead_stage_modal', 'toggle' => 'modal'],
                    'candidates' => ['target' => '#candidateModal', 'toggle' => 'modal'],
                    'interview' => ['target' => '#createInterviewModal', 'toggle' => 'modal'],
                    'email-templates' => ['target' => '#createTemplateModal', 'toggle' => 'modal'],
                ];

                $hasModal = array_key_exists($typeSlug, $modalMap);
                $href = $hasModal ? 'javascript:void(0)' : $link ?? url($typeSlug . '/create');
                $modalAttribute = $hasModal
                    ? 'data-bs-toggle="' .
                        $modalMap[$typeSlug]['toggle'] .
                        '" data-bs-target="' .
                        $modalMap[$typeSlug]['target'] .
                        '"'
                    : '';
            @endphp

            <a href="{{ $href }}" {!! $modalAttribute !!} class="btn btn-primary m-1">
                {{ get_label('create_now', 'Create now') }}
            </a>
        @endif
        <div class="mt-3">
            <img src="{{ asset('/storage/no-result.png') }}" alt="page-misc-error-light" width="500" class="img-fluid"
                data-app-dark-img="illustrations/page-misc-error-dark.png"
                data-app-light-img="illustrations/page-misc-error-light.png" />
        </div>
    </div>
    @if ($flag == 1)
</div>
@endif
</div>
