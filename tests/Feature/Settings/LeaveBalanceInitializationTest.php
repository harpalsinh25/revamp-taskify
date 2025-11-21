<?php

use App\Models\User;
use App\Models\Workspace;
use App\Models\UserLeaveBalance;
use App\Models\Setting;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    Setting::updateOrCreate(
        ['variable' => 'general_settings'],
        ['value' => json_encode([
            'total_paid_leaves_per_year' => 15,
        ])]
    );
});

test('admin can save total paid leaves in general settings', function () {
    $this->actingAs($this->admin);

    $response = $this->putJson('/settings/store_general', [
        'company_title' => 'Test Company',
        'site_url' => 'https://test.com',
        'timezone' => 'Asia/Kolkata',
        'currency_full_form' => 'US Dollar',
        'currency_symbol' => '$',
        'currency_code' => 'USD',
        'date_format' => 'DD-MM-YYYY|d-m-Y',
        'total_paid_leaves_per_year' => 20,
        'company_year_start' => '01-01',
        'company_year_end' => '12-31',
    ]);

    $response->assertStatus(200)
        ->assertJson(['error' => false]);

    $settings = Setting::where('variable', 'general_settings')->first();
    $decoded = json_decode($settings->value, true);

    expect($decoded['total_paid_leaves_per_year'])->toBe(20);
});

test('company year start and end are parsed correctly', function () {
    $this->actingAs($this->admin);

    $response = $this->putJson('/settings/store_general', [
        'company_title' => 'Test Company',
        'site_url' => 'https://test.com',
        'timezone' => 'Asia/Kolkata',
        'currency_full_form' => 'US Dollar',
        'currency_symbol' => '$',
        'currency_code' => 'USD',
        'date_format' => 'DD-MM-YYYY|d-m-Y',
        'total_paid_leaves_per_year' => 15,
        'company_year_start' => '04-01', // April 1
        'company_year_end' => '03-31',   // March 31
    ]);

    $settings = Setting::where('variable', 'general_settings')->first();
    $decoded = json_decode($settings->value, true);

    expect($decoded['company_year_start_month'])->toBe(4)
        ->and($decoded['company_year_start_day'])->toBe(1)
        ->and($decoded['company_year_end_month'])->toBe(3)
        ->and($decoded['company_year_end_day'])->toBe(31);
});

test('admin can initialize leave balances via API', function () {
    $this->actingAs($this->admin);

    $workspace = Workspace::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $workspace->users()->attach([$user1->id, $user2->id]);

    $response = $this->postJson('/settings/initialize-leave-balances');

    $response->assertStatus(200)
        ->assertJson(['error' => false])
        ->assertJsonStructure([
            'message',
            'summary' => ['workspaces', 'total_users', 'initialized']
        ]);

    // Check balances were created
    expect(UserLeaveBalance::where('user_id', $user1->id)->exists())->toBeTrue()
        ->and(UserLeaveBalance::where('user_id', $user2->id)->exists())->toBeTrue();
});

test('initialize balances is idempotent', function () {
    $this->actingAs($this->admin);

    $workspace = Workspace::factory()->create();
    $user = User::factory()->create();
    $workspace->users()->attach($user->id);

    // First initialization
    $this->postJson('/settings/initialize-leave-balances');
    $firstCount = UserLeaveBalance::count();

    // Second initialization
    $response = $this->postJson('/settings/initialize-leave-balances');
    $secondCount = UserLeaveBalance::count();

    expect($firstCount)->toBe($secondCount) // No duplicates
        ->and($response->json('summary.newly_initialized'))->toBe(0); // Nothing new
});

test('non-admin cannot initialize balances', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->postJson('/settings/initialize-leave-balances');

    $response->assertStatus(403); // Forbidden
});

test('artisan command initializes balances for all users', function () {
    $workspace = Workspace::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $workspace->users()->attach([$user1->id, $user2->id]);

    $this->artisan('leaves:initialize-balances')
        ->expectsOutput('✅ Initialization complete!')
        ->assertExitCode(0);

    expect(UserLeaveBalance::where('user_id', $user1->id)->exists())->toBeTrue()
        ->and(UserLeaveBalance::where('user_id', $user2->id)->exists())->toBeTrue();
});

test('artisan command can initialize for specific workspace', function () {
    $workspace1 = Workspace::factory()->create();
    $workspace2 = Workspace::factory()->create();

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $workspace1->users()->attach($user1->id);
    $workspace2->users()->attach($user2->id);

    $this->artisan('leaves:initialize-balances', ['--workspace' => $workspace1->id])
        ->assertExitCode(0);

    expect(UserLeaveBalance::where('workspace_id', $workspace1->id)->exists())->toBeTrue()
        ->and(UserLeaveBalance::where('workspace_id', $workspace2->id)->exists())->toBeFalse();
});

test('artisan command can initialize for specific year', function () {
    $workspace = Workspace::factory()->create();
    $user = User::factory()->create();
    $workspace->users()->attach($user->id);

    $this->artisan('leaves:initialize-balances', ['--year' => 2026])
        ->assertExitCode(0);

    $balance = UserLeaveBalance::where('user_id', $user->id)->first();
    expect($balance->year)->toBe(2026);
});
