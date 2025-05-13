<?php

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\TaxesController;
use App\Http\Controllers\TodosController;
use App\Http\Controllers\UnitsController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\MeetingsController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WorkspacesController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\SignUpController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PaymentMethodsController;
use App\Http\Controllers\EstimatesInvoicesController;
use App\Http\Controllers\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// User Authentication
Route::post('/users/signup', [SignUpController::class, 'create_account'])->middleware(['checkSignupEnabled', 'isApi']);
Route::post('/users/login', [UserController::class, 'authenticate'])->middleware(['customThrottle', 'isApi']);
Route::post('/password/reset-request', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware(['isApi']);
Route::post('/password/reset', [ForgotPasswordController::class, 'ResetPassword'])->middleware(['isApi']);
// Route::post('/users/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

// Roles
Route::get('/roles/{id?}', [RolesController::class, 'apiList']);
// Permissions
Route::get('/permissions/{permission?}', [RolesController::class, 'checkPermissions']);
// Settings
Route::get('/settings/{variable}', [SettingsController::class, 'show'])->middleware(['multiguard', 'custom-verified']);
Route::post('/settings/update', [SettingsController::class, 'store_settings_api'])->middleware(['multiguard', 'custom-verified', 'isApi','customRole:admin']);

// Protected Routes
Route::middleware(['multiguard', 'custom-verified', 'has_workspace'])->group(function () {

    Route::patch('/user/fcm-token', [UserController::class, 'updateFcmToken']);

    // Profile Management
    Route::middleware('auth:sanctum')->get('/user', [ProfileController::class, 'profile']);
    Route::post('/users/photo', [ProfileController::class, 'update_photo'])->middleware(['demo_restriction', 'isApi']);
    Route::post('users/{id}/profile', [ProfileController::class, 'update'])->middleware(['demo_restriction', 'isApi']);
    Route::delete('/account/destroy', [ProfileController::class, 'destroy'])->middleware(['demo_restriction']);

    // User Management
    Route::get('/users/{id?}', [UserController::class, 'apiList']);
    Route::middleware(['customcan:manage_users'])->group(function () {
        Route::post('/users/store', [UserController::class, 'store'])->middleware(['customcan:create_users', 'log.activity', 'isApi']);
        Route::post('/users/update', [UserController::class, 'update_user'])->middleware(['customcan:edit_users', 'demo_restriction', 'log.activity', 'isApi']);
        Route::delete('/users/destroy/{id}', [UserController::class, 'delete_user'])->middleware(['customcan:delete_users', 'demo_restriction', 'log.activity']);
    });

    // Client Management
    Route::get('/clients/{id?}', [ClientController::class, 'apiList']);
    Route::middleware(['customcan:manage_clients'])->group(function () {
        Route::post('/clients/store', [ClientController::class, 'store'])->middleware(['customcan:create_clients', 'log.activity', 'isApi']);
        Route::post('/clients/update', [ClientController::class, 'update'])->middleware(['customcan:edit_clients', 'demo_restriction', 'log.activity', 'isApi']);
        Route::delete('/clients/destroy/{id}', [ClientController::class, 'destroy'])->middleware(['customcan:delete_clients', 'demo_restriction', 'log.activity']);
    });



    // Dashboard
    Route::get('/upcoming-birthdays', [HomeController::class, 'upcomingBirthdaysApi']);
    Route::get('/upcoming-work-anniversaries', [HomeController::class, 'upcomingWorkAnniversariesApi']);
    Route::get('/members-on-leave', [HomeController::class, 'membersOnLeaveApi']);
    Route::get('/dashboard/statistics', [HomeController::class, 'getStatistics']);


    // Projects
    Route::get('/projects/{id?}', [ProjectsController::class, 'apiList']);
    Route::middleware(['customcan:manage_projects'])->group(function () {
        Route::post('/projects/store', [ProjectsController::class, 'store'])->middleware(['customcan:create_projects', 'log.activity', 'isApi']);
        Route::post('/projects/update', [ProjectsController::class, 'update'])->middleware(['customcan:edit_projects', 'log.activity', 'isApi']);
        Route::patch('projects/{id}/favorite', [ProjectsController::class, 'update_favorite'])->middleware(['isApi']);
        Route::patch('projects/{id}/pinned', [ProjectsController::class, 'update_pinned'])->middleware(['isApi']);
        Route::patch('projects/{id}/status', [ProjectsController::class, 'update_status'])->middleware(['customcan:edit_projects', 'log.activity', 'isApi']);
        Route::patch('projects/{id}/priority', [ProjectsController::class, 'update_priority'])->middleware(['customcan:edit_projects', 'log.activity', 'isApi']);
        Route::delete('/projects/destroy/{id}', [ProjectsController::class, 'destroy'])->middleware(['customcan:delete_projects', 'demo_restriction', 'checkAccess:App\Models\Project,projects,id,projects', 'log.activity']);
        Route::get('/projects/{id}/status-timelines', [ProjectsController::class, 'get_status_timelines_api']);
        Route::get('/projects/{id}/mind-map', [ProjectsController::class, 'getMindMapData'])->middleware(['isApi']);
    });

    // Milestones
    Route::middleware(['customcan:manage_milestones'])->group(function () {
        Route::post('/milestones/store', [ProjectsController::class, 'store_milestone'])->middleware(['customcan:create_milestones', 'log.activity', 'isApi']);
        Route::get('/milestones/{id?}', [ProjectsController::class, 'get_milestones_api']);
        Route::get('/milestones/get/{id?}', [ProjectsController::class, 'get_milestone']);
        Route::post('/milestones/update', [ProjectsController::class, 'update_milestone'])->middleware(['customcan:edit_milestones', 'log.activity', 'isApi']);
        Route::delete('/milestones/destroy/{id}', [ProjectsController::class, 'delete_milestone'])->middleware(['customcan:delete_milestones', 'demo_restriction', 'checkAccess:App\Models\Milestone,milestones,id,milestones', 'log.activity']);

    });

    // Project Comments
    Route::prefix('projects')->group(function () {
        Route::post('/{id}/comments', [ProjectsController::class, 'comments'])->middleware(['isApi']);
        Route::get('/comments/get/{id}', [ProjectsController::class, 'get_comment']);
        Route::post('/comments/update', [ProjectsController::class, 'update_comment'])->middleware(['isApi']);
        Route::delete('/comments/destroy', [ProjectsController::class, 'destroy_comment'])->middleware(['demo_restriction', 'log.activity']);
    });

    //Project Media
    Route::prefix('projects')->middleware(['customcan:manage_media'])->group(function () {
        Route::post('/upload-media', [ProjectsController::class, 'upload_media'])
            ->middleware(['customcan:create_media', 'log.activity', 'validate.upload.media']);

        Route::get('/get-media/{id}', [ProjectsController::class, 'get_media_api']);

        Route::delete('/delete-media/{id}', [ProjectsController::class, 'delete_media'])
            ->middleware(['customcan:delete_media', 'log.activity']);
    });



    // Tasks
    Route::middleware(['customcan:manage_tasks'])->group(function () {
        Route::post('/tasks/store', [TasksController::class, 'store'])->middleware(['customcan:create_tasks', 'log.activity', 'isApi']);
        Route::get('/tasks/{id?}', [TasksController::class, 'apiList']);
        Route::post('/tasks/update', [TasksController::class, 'update'])->middleware(['customcan:edit_tasks', 'log.activity', 'isApi']);
        Route::patch('tasks/{id}/favorite', [TasksController::class, 'update_favorite'])->middleware(['isApi']);
        Route::patch('tasks/{id}/pinned', [TasksController::class, 'update_pinned'])->middleware(['isApi']);
        Route::patch('tasks/{id}/status', [TasksController::class, 'update_status'])->middleware(['customcan:edit_tasks', 'log.activity', 'isApi']);
        Route::patch('tasks/{id}/priority', [TasksController::class, 'update_priority'])->middleware(['customcan:edit_tasks', 'log.activity', 'isApi']);
        Route::delete('/tasks/destroy/{id}', [TasksController::class, 'destroy'])->middleware(['customcan:delete_tasks', 'demo_restriction', 'checkAccess:App\Models\Task,tasks,id,tasks', 'log.activity']);
        Route::get('/tasks/{id}/status-timelines', [TasksController::class, 'get_status_timelines_api']);


    });

    //Task Comments
    Route::prefix('tasks')->group(function () {
        Route::post('/information/{id}/comments', [TasksController::class, 'comments'])->middleware(['isApi']);
        Route::get('/comments/get/{id}', [TasksController::class, 'get_comment']);
        Route::post('/comments/update', [TasksController::class, 'update_comment'])->middleware(['isApi']);
        Route::delete('/comments/destroy', [TasksController::class, 'destroy_comment']);
    });

    //Task Media
    Route::prefix('tasks')->middleware(['customcan:manage_media'])->group(function () {
        Route::post('/upload-media', [TasksController::class, 'upload_media'])
            ->middleware(['customcan:create_media', 'log.activity', 'validate.upload.media']);

        Route::get('/get-media/{id}', [TasksController::class, 'get_media_api']);

        Route::delete('/delete-media/{id}', [TasksController::class, 'delete_media'])
            ->middleware(['customcan:delete_media', 'log.activity']);
    });

    // Statuses
    Route::get('/statuses/{id?}', [StatusController::class, 'apiList']);
    Route::middleware(['customcan:manage_statuses'])->group(function () {
        Route::post('/status/store', [StatusController::class, 'store'])->middleware(['customcan:create_statuses', 'isApi', 'log.activity']);
        Route::post('/status/update', [StatusController::class, 'update'])->middleware(['customcan:edit_statuses', 'isApi', 'log.activity']);
        Route::get('/status/get/{id}', [StatusController::class, 'get']);
        Route::delete('/status/destroy/{id}', [StatusController::class, 'destroy'])->middleware(['customcan:delete_statuses', 'log.activity']);
    });

    // Priorities
    Route::get('/priorities/{id?}', [PriorityController::class, 'apiList']);
    Route::middleware(['customcan:manage_priorities'])->group(function () {
        Route::post('/priority/store', [PriorityController::class, 'store'])->middleware(['customcan:create_priorities', 'log.activity']);
        Route::post('/priority/update', [PriorityController::class, 'update'])->middleware(['customcan:edit_priorities', 'log.activity']);
        Route::get('/priority/get/{id}', [PriorityController::class, 'get']);
        Route::delete('/priority/destroy/{id}', [PriorityController::class, 'destroy'])->middleware(['customcan:delete_priorities', 'log.activity']);
    });

    // Tags
    // Route::middleware(['customcan:manage_tags'])->group(function () {
    Route::get('/tags/{id?}', [TagsController::class, 'apiList']);
    // });

    // Workspaces
    Route::middleware(['customcan:manage_workspaces'])->group(function () {
        Route::post('/workspaces/store', [WorkspacesController::class, 'store'])->middleware(['customcan:create_workspaces', 'log.activity', 'isApi']);
        Route::get('/workspaces/{id?}', [WorkspacesController::class, 'apiList']);
        Route::post('/workspaces/update', [WorkspacesController::class, 'update'])->middleware(['customcan:edit_workspaces', 'demo_restriction', 'log.activity', 'isApi']);
        Route::delete('/workspaces/destroy/{id}', [WorkspacesController::class, 'destroy'])->middleware(['customcan:delete_workspaces', 'demo_restriction', 'checkAccess:App\Models\Workspace,workspaces,id,workspaces', 'log.activity']);
        // Route::patch('workspaces/{id}/switch', [WorkspacesController::class, 'switch'])->middleware(['checkAccess:App\Models\Workspace,workspaces,id,workspaces']);
    });
    Route::patch('workspaces/{id}/default', [WorkspacesController::class, 'setDefaultWorkspace'])->middleware(['isApi'])->middleware(['demo_restriction']);
    Route::delete('/workspaces/remove-participant', [WorkspacesController::class, 'remove_participant'])->middleware(['demo_restriction']);

    // Meetings
    Route::middleware(['customcan:manage_meetings'])->group(function () {
        Route::post('/meetings/store', [MeetingsController::class, 'store'])->middleware(['customcan:create_meetings', 'log.activity', 'isApi']);
        Route::get('/meetings/{id?}', [MeetingsController::class, 'apiList']);
        Route::post('/meetings/update', [MeetingsController::class, 'update'])->middleware(['customcan:edit_meetings', 'log.activity', 'isApi']);
        Route::delete('/meetings/destroy/{id}', [MeetingsController::class, 'destroy'])->middleware(['customcan:delete_meetings', 'demo_restriction', 'checkAccess:App\Models\Meeting,meetings,id,meetings', 'log.activity']);
    });

    // Todos
    Route::post('/todos/store', [TodosController::class, 'store'])->middleware(['log.activity', 'isApi']);
    Route::get('/todos/{id?}', [TodosController::class, 'apiList']);
    Route::post('/todos/update', [TodosController::class, 'update'])->middleware(['log.activity', 'isApi']);
    Route::patch('/todos/{id}/status', [TodosController::class, 'update_status'])->middleware(['log.activity', 'isApi']);
    Route::patch('/todos/{id}/priority', [TodosController::class, 'update_priority'])->middleware(['log.activity', 'isApi']);
    Route::delete('/todos/destroy/{id}', [TodosController::class, 'destroy'])->middleware(['demo_restriction', 'log.activity']);

    // Notes
    Route::post('/notes/store', [NotesController::class, 'store'])->middleware(['log.activity', 'isApi']);
    Route::get('/notes/{id?}', [NotesController::class, 'apiList']);
    Route::post('/notes/update', [NotesController::class, 'update'])->middleware(['log.activity', 'isApi']);
    Route::delete('/notes/destroy/{id}', [NotesController::class, 'destroy'])->middleware(['demo_restriction', 'log.activity']);

    // Notifications
    Route::middleware(['customcan:manage_system_notifications'])->group(function () {
        Route::get('/notifications/{id?}', [NotificationsController::class, 'apiList']);
        Route::delete('/notifications/destroy/{id}', [NotificationsController::class, 'destroy'])->middleware(['customcan:delete_system_notifications', 'demo_restriction']);
        Route::patch('/notifications/mark-as-read/{id?}', [NotificationsController::class, 'markAsReadAPI']);
    });

    // Leave Requests
    Route::middleware(['admin_or_user'])->group(function () {
        Route::get('leave-requests/get-calendar-data', [LeaveRequestController::class, 'get_calendar_data'])->name('leave_requests.get_calendar_data');
        Route::post('/leave-requests/store', [LeaveRequestController::class, 'store'])->middleware(['log.activity', 'isApi']);
        Route::get('/leave-requests/{id?}', [LeaveRequestController::class, 'apiList']);
        Route::post('/leave-requests/update', [LeaveRequestController::class, 'update'])->middleware(['log.activity', 'isApi']);
        Route::delete('/leave-requests/destroy/{id}', [LeaveRequestController::class, 'destroy'])->middleware(['admin_or_leave_editor', 'demo_restriction', 'log.activity']);
    });

    // Activity Log
    Route::middleware(['customcan:manage_activity_log'])->group(function () {
        Route::get('/activity-log/{id?}', [ActivityLogController::class, 'list'])->middleware('isApi');
        Route::delete('/activity-log/destroy/{id}', [ActivityLogController::class, 'destroy'])->middleware(['demo_restriction', 'customcan:delete_activity_log']);
    });

    // Roles and Permissions
    Route::middleware(['customRole:admin'])->group(function () {
        Route::post('/roles/store',[RolesController::class, 'store_api']);
        Route::post('/roles/update/{id}',[RolesController::class, 'update_api']);
        Route::delete('/roles/destroy/{id}',[RolesController::class, 'destroy_api']);

        Route::get('/roles/get/{id?}', [RolesController::class, 'get_role_api']);
        Route::get('/permissions-list', [RolesController::class, 'get_permissions_api']);
    });
    Route::get('/reports/income-vs-expense-report-data', [ReportsController::class, 'getIncomeVsExpenseReportData'])->middleware(['isApi']);

    // Tags
    Route::middleware(['customcan:manage_tags', 'isApi'])->group(function () {
        Route::post('/tags/store', [TagsController::class, 'store'])->middleware(['customcan:create_tags', 'log.activity']);
        Route::post('/tags/update', [TagsController::class, 'update'])->middleware(['customcan:edit_tags', 'log.activity']);
        Route::delete('/tags/destroy/{id}', [TagsController::class, 'destroy'])->middleware(['customcan:delete_tags', 'log.activity']);
    });

    // Expenses
    Route::middleware(['customcan:manage_expenses', 'isApi'])->group(function () {
        Route::post('/expenses/store', [ExpensesController::class, 'store'])->middleware(['customcan:create_expenses', 'log.activity']);
        Route::get('/expenses/{id?}', [ExpensesController::class, 'apiList']);
        Route::post('/expenses/update', [ExpensesController::class, 'update'])->middleware(['customcan:edit_expenses', 'log.activity']);
        Route::delete('/expenses/destroy/{id}', [ExpensesController::class, 'destroy'])->middleware(['customcan:delete_expenses', 'log.activity']);
        Route::post('/expenses/expense-types/store', [ExpensesController::class, 'store_expense_type'])->middleware(['customcan:create_expenses', 'log.activity']);
        Route::get('/expenses/expense-types/list/{id?}', [ExpensesController::class, 'apiListExpenseTypes']);
        Route::post('/expenses/expense-types/update', [ExpensesController::class, 'update_expense_type'])->middleware(['customcan:edit_expenses', 'log.activity']);
        Route::delete('/expenses/expense-types/destroy/{id}', [ExpensesController::class, 'delete_expense_type'])->middleware(['customcan:delete_expenses', 'log.activity']);
    });

    //Payments
    Route::middleware(['customcan:manage_payments', 'isApi'])->group(function () {
        Route::post('/payments/store', [PaymentsController::class, 'store'])->middleware(['customcan:create_payments', 'log.activity']);
        Route::get('/payments/{id?}', [PaymentsController::class, 'apiList']);
        Route::post('/payments/update', [PaymentsController::class, 'update'])->middleware(['customcan:edit_payments', 'log.activity']);
        Route::delete('/payments/destroy/{id}', [PaymentsController::class, 'destroy'])->middleware(['customcan:delete_payments', 'log.activity']);
    });

    //Payment Methods
    Route::middleware(['customcan:manage_payment_methods', 'isApi'])->group(function () {
        Route::get('/payment-methods/{id?}', [PaymentMethodsController::class, 'apiList']);
        Route::post('/payment-methods/store', [PaymentMethodsController::class, 'store'])->middleware(['customcan:create_payment_methods', 'log.activity']);
        Route::post('/payment-methods/update', [PaymentMethodsController::class, 'update'])->middleware(['customcan:edit_payment_methods', 'log.activity']);
        Route::delete('/payment-methods/destroy/{id}', [PaymentMethodsController::class, 'destroy'])->middleware(['customcan:delete_payment_methods', 'demo_restriction', 'log.activity']);
    });

    //Taxes
    Route::middleware(['customcan:manage_taxes', 'isApi'])->group(function () {

        Route::get('/taxes/{id?}', [TaxesController::class, 'apiList']);
        Route::post('/taxes/store', [TaxesController::class, 'store'])->middleware(['customcan:create_taxes', 'log.activity']);
        Route::post('/taxes/update', [TaxesController::class, 'update'])->middleware(['customcan:edit_taxes', 'log.activity']);
        Route::delete('/taxes/destroy/{id}', [TaxesController::class, 'destroy'])->middleware(['customcan:delete_taxes', 'demo_restriction', 'log.activity']);
    });

    //Units
    Route::middleware(['customcan:manage_units', 'isApi'])->group(function () {

        Route::get('/units/{id?}', [UnitsController::class, 'apiList']);
        Route::post('/units/store', [UnitsController::class, 'store'])->middleware(['customcan:create_units', 'log.activity']);
        Route::post('/units/update', [UnitsController::class, 'update'])->middleware(['customcan:edit_units', 'log.activity']);
        Route::delete('/units/destroy/{id}', [UnitsController::class, 'destroy'])->middleware(['customcan:delete_units', 'demo_restriction', 'log.activity']);
    });

    //Items
    Route::middleware(['customcan:manage_items', 'isApi'])->group(function () {
        Route::get('/items/{id?}', [ItemsController::class, 'apiList']);
        Route::post('/items/store', [ItemsController::class, 'store'])->middleware(['customcan:create_items', 'log.activity']);
        Route::post('/items/update', [ItemsController::class, 'update'])->middleware(['customcan:edit_items', 'log.activity']);
        Route::delete('/items/destroy/{id}', [ItemsController::class, 'destroy'])->middleware(['customcan:delete_items', 'demo_restriction', 'log.activity']);
    });

    //Estimate Invoices
    Route::middleware(['customcan:manage_estimates_invoices', 'isApi'])->group(function () {

        Route::get('/estimates-invoices', [EstimatesInvoicesController::class, 'apiList']);
        Route::post('/estimates-invoices/store', [EstimatesInvoicesController::class, 'store'])->middleware(['customcan:create_estimates_invoices', 'log.activity']);
        Route::get('/estimates-invoices/pdf/{id}', [EstimatesInvoicesController::class, 'pdf'])->middleware(['checkAccess:App\Models\EstimatesInvoice,estimates_invoices,id,estimates_invoices']);
        Route::post('/estimates-invoices/update', [EstimatesInvoicesController::class, 'update'])->middleware(['customcan:edit_estimates_invoices', 'log.activity']);
        Route::delete('/estimates-invoices/destroy/{id}', [EstimatesInvoicesController::class, 'destroy'])->middleware(['demo_restriction', 'customcan:delete_estimates_invoices', 'checkAccess:App\Models\EstimatesInvoice,estimates_invoices,id,estimates_invoices', 'log.activity']);
    });
});
