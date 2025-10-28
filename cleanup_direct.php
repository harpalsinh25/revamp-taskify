#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Cleaning database...\n";

// Drop user_leave_balances table
DB::statement('DROP TABLE IF EXISTS user_leave_balances');
echo "✓ Dropped user_leave_balances table\n";

// Drop columns from leave_requests
$columns = ['total_days', 'paid_days', 'unpaid_days', 'is_paid', 'balance_deducted', 'manually_marked_as'];
foreach ($columns as $column) {
    try {
        DB::statement("ALTER TABLE leave_requests DROP COLUMN $column");
        echo "✓ Dropped column: $column\n";
    } catch (\Exception $e) {
        // Column might not exist, continue
    }
}

// Clean migration records
DB::table('migrations')->whereIn('migration', [
    '2025_10_28_072045_create_user_leave_balances_table',
    '2025_10_28_072113_add_leave_tracking_to_leave_requests_table',
    '2025_10_28_075907_add_balance_deducted_flag_to_leave_requests',
    '2025_10_28_080500_cleanup_leave_management_tables'
])->delete();

echo "✓ Cleaned migration records\n";
echo "\n✅ DATABASE CLEANUP COMPLETE!\n";

