@extends('layout')
@section('title')
    <?= get_label('permission_settings', 'Permission settings') ?>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <h4 class="fw-bold mb-0"><?= get_label('permission_settings', 'Permission Settings') ?></h4>
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
                        <i class='bx bx-plus'></i>
                    </button>
                </a>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= get_label('id', 'ID') ?></th>
                                <th><?= get_label('role', 'Role') ?></th>
                                <th><?= get_label('permissions', 'Permissions') ?></th>
                                <th><?= get_label('actions', 'Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td>
                                        {{ $role->id }}
                                    </td>
                                    <td>
                                        <h4 class="text-capitalize fw-bold mb-0">{{ ucfirst($role->name) }}</h4>
                                    </td>
                                    @if ($role->name == 'admin')
                                        <td>
                                            <span class="badge bg-success m-1 rounded p-2 px-3"><?= get_label('admin_has_all_permissions', 'Admin has all the permissions') ?></span>
                                        </td>
                                        <td>-</td>
                                    @else
                                        <?php 
                                            $permissions = $role->permissions; 
                                            $totalPermissions = count($permissions);
                                            $displayLimit = 5;
                                        ?>
                                        @if ($totalPermissions != 0)
                                            <td class="permissions-container text-wrap" style="max-width: 500px;">
                                                @foreach ($permissions->take($displayLimit) as $permission)
                                                    <span class="badge bg-{{ $permission->name == 'access_all_data' ? 'success' : 'primary' }} m-1 rounded p-2 px-3">
                                                        {{ $role->hasPermissionTo($permission) ? str_replace('_', ' ', $permission->name) : '' }}
                                                    </span>
                                                @endforeach
                                                @if($totalPermissions > $displayLimit)
                                                    <span class="badge bg-secondary m-1 rounded p-2 px-3">+{{ $totalPermissions - $displayLimit }} more</span>
                                                @endif
                                            </td>
                                        @else
                                            <td class="align-items-center">
                                                <span class="text-muted">
                                                    <?= get_label('no_permissions_assigned', 'No Permissions Assigned!') ?>
                                                </span>
                                            </td>
                                        @endif
                                        <td class="align-items-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ url('/roles/edit/' . $role->id) }}"><i class='bx bx-edit mx-1'></i> <?= get_label('edit', 'Edit') ?></a>
                                                    @if (!in_array($role->name, ['Client', 'member']))
                                                        <a class="dropdown-item delete" href="javascript:void(0);" data-id="{{ $role->id }}" data-type="roles" data-reload="true"><i class='bx bx-trash text-danger mx-1'></i> <?= get_label('delete', 'Delete') ?></a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
