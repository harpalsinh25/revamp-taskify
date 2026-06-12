@extends('layout')
@section('title')
    <?= get_label('permission_settings', 'Permission settings') ?>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
        <h4 class="fw-bold mb-0 fs-4"><?= get_label('permission_settings', 'Permission Settings') ?></h4>
        <div class="d-flex align-items-center gap-3">
            <nav class="breadcrumb mb-0" aria-label="breadcrumb">
                <a class="breadcrumb-item" href="{{ url('home') }}"><?= get_label('home', 'Home') ?></a>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-item"><?= get_label('settings', 'Settings') ?></span>
                <span class="breadcrumb-sep">/</span>
                <span class="breadcrumb-current"><?= get_label('permissions', 'Permissions') ?></span>
            </nav>
            <a href="{{ url('roles/create') }}">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="<?= get_label('create_role', 'Create role') ?>">
                    <i class='bx bx-plus me-1'></i><?= get_label('create_role', 'Create Role') ?>
                </button>
            </a>
        </div>
    </div>
    
@php
    $columns = [
        ['field' => 'id', 'label' => get_label('id', 'ID')],
        ['field' => 'role', 'label' => get_label('role', 'Role')],
        ['field' => 'permissions', 'label' => get_label('permissions', 'Permissions')],
        ['field' => 'actions', 'label' => get_label('actions', 'Actions')]
    ];
@endphp

<div class="card mb-3 shadow-none border">
    <div class="card-body p-0">
        <x-tk-table 
            id="roles_table" 
            :columns="$columns"
            data-pagination="false"
            data-search="false"
            data-show-refresh="false"
            data-show-columns="false"
        >
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td>
                            {{ $role->id }}
                        </td>
                        <td>
                            <span class="fw-semibold text-dark text-capitalize fs-6">{{ ucfirst($role->name) }}</span>
                        </td>
                        @if ($role->name == 'admin')
                            <td>
                                <span class="badge bg-primary px-3 py-1"><?= get_label('admin_has_all_permissions', 'Admin has all the permissions') ?></span>
                            </td>
                            <td data-field="actions" class="text-muted">-</td>
                        @else
                            <?php 
                                $permissions = $role->permissions; 
                                $totalPermissions = count($permissions);
                                $displayLimit = 5;
                            ?>
                            @if ($totalPermissions != 0)
                                <td class="permissions-container text-wrap">
                                    @foreach ($permissions->take($displayLimit) as $permission)
                                        <span class="badge bg-primary m-1 py-1 px-2 text-capitalize rounded">
                                            {{ str_replace('_', ' ', $permission->name) }}
                                        </span>
                                    @endforeach
                                    @if($totalPermissions > $displayLimit)
                                        <span class="badge bg-secondary m-1 py-1 px-2 rounded">+{{ $totalPermissions - $displayLimit }} more</span>
                                    @endif
                                </td>
                            @else
                                <td>
                                    <span class="text-muted small">
                                        <?= get_label('no_permissions_assigned', 'No Permissions Assigned!') ?>
                                    </span>
                                </td>
                            @endif
                            <td data-field="actions">
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded fs-5 text-secondary"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <a class="dropdown-item py-1 px-3" href="{{ url('/roles/edit/' . $role->id) }}">
                                            <i class='bx bx-edit me-2 fs-6 text-secondary'></i><?= get_label('edit', 'Edit') ?>
                                        </a>
                                        @if (!in_array($role->name, ['Client', 'member']))
                                            <a class="dropdown-item delete py-1 px-3" href="javascript:void(0);" data-id="{{ $role->id }}" data-type="roles" data-reload="true">
                                                <i class='bx bx-trash text-danger me-2 fs-6'></i><?= get_label('delete', 'Delete') ?>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </x-tk-table>
    </div>
</div>
</div>
@endsection
