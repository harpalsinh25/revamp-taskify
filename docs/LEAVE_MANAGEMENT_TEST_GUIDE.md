# Leave Management System - Test Guide

## 📋 Overview

This guide explains how to run the comprehensive test suite for the Leave Management System, covering the complete flow from Leave Requests to Payslips.

## 🧪 Test File Location

```
tests/Feature/LeaveManagement/CompleteLeaveManagementFlowTest.php
```

## 📦 Prerequisites

Before running tests, ensure:

1. **Database Setup**: Tests use `DatabaseTransactions` trait, so they won't affect your actual database
2. **Migrations**: Run migrations to ensure all tables exist:
   ```bash
   php artisan migrate
   ```
3. **Test Database**: Configure `.env.testing` or ensure your test environment is set up

## 🚀 Running Tests

### 1. Run All Tests

Run the entire test suite:
```bash
php artisan test
```

### 2. Run Only Leave Management Tests

Run all tests in the Leave Management directory:
```bash
php artisan test tests/Feature/LeaveManagement/
```

### 3. Run Complete Flow Test File

Run only the comprehensive flow test file:
```bash
php artisan test tests/Feature/LeaveManagement/CompleteLeaveManagementFlowTest.php
```

### 4. Run Specific Test

Run a single test by name:
```bash
php artisan test --filter "complete flow: create pending leave request"
```

### 5. Run Tests with Verbose Output

See detailed test output:
```bash
php artisan test --verbose
```

### 6. Run Tests with Coverage (if configured)

Generate code coverage report:
```bash
php artisan test --coverage
```

## 📊 Test Coverage

The test file includes **15 comprehensive tests** covering:

### Flow 1: Leave Request Management (6 tests)
- ✅ Create pending leave request
- ✅ Admin approves leave and balance updates via event
- ✅ Leave exceeding balance splits into paid and unpaid
- ✅ Reject approved leave restores balance via event
- ✅ Delete approved leave restores balance via event
- ✅ Overlap validation prevents overlapping approved leaves

### Flow 2: Leave Balance Management (3 tests)
- ✅ Balance initialization with company year
- ✅ Balance recalculation from leave requests (single source of truth)
- ✅ Monthly accrual with advance reduction

### Flow 3: Payslip Management (4 tests)
- ✅ Payslip calculates baseline LOP from leave requests
- ✅ Payslip adjustment creates adjustment record
- ✅ Payslip override creates advance leaves
- ✅ Payslip update reverses old adjustment and creates new

### Integration Tests (3 tests)
- ✅ Complete integration: leave request → balance → payslip flow
- ✅ Complete integration: unpaid leave → payslip LOP → balance adjustment
- ✅ Complete integration: admin adjusts payslip LOP → creates adjustment → updates balance

## 🔍 Understanding Test Output

### Successful Test
```
✓ complete flow: create pending leave request
```

### Failed Test
```
✗ complete flow: create pending leave request
  Expected: 3.0
  Actual: 0.0
```

### Test Summary
```
Tests:  15 passed
Time:   2.45s
```

## 🛠️ Troubleshooting

### Issue: Tests fail with "Table doesn't exist"
**Solution**: Run migrations first
```bash
php artisan migrate
```

### Issue: Tests fail with "Class not found"
**Solution**: Clear cache and regenerate autoload
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Issue: Tests fail with "Event not dispatched"
**Solution**: Ensure events are registered in `EventServiceProvider`

### Issue: Tests fail with "Factory not found"
**Solution**: Ensure factories exist for:
- `User`
- `Workspace`
- `LeaveRequest`
- `Payslip`
- `UserLeaveBalance`

## 📝 Test Structure

Each test follows this pattern:

```php
test('test description', function () {
    // 1. Setup (acting as user, creating data)
    $this->actingAs($this->admin);

    // 2. Action (making API calls or service calls)
    $response = $this->postJson('/leave-requests/store', [...]);

    // 3. Assertions (verifying results)
    $response->assertStatus(200);
    expect($balance->used_paid_leaves)->toBe(3.0);
});
```

## 🎯 Key Test Scenarios

### 1. Event-Driven Balance Updates
Tests verify that:
- Events are fired when leave status changes
- Listeners update balance correctly
- Balance calculations are accurate

### 2. Single Source of Truth
Tests verify that:
- Balance is calculated from `LeaveRequest` records
- Adjustments are tracked separately
- Recalculation works correctly

### 3. Payslip Integration
Tests verify that:
- Baseline LOP is calculated from leave requests
- Adjustments create proper records
- Override logic works correctly
- Advance leaves are tracked

## 🔄 Continuous Testing

For development, run tests in watch mode (if available):
```bash
php artisan test --watch
```

Or use Pest's watch mode:
```bash
./vendor/bin/pest --watch
```

## 📈 Test Performance

- **Expected Runtime**: 2-5 seconds for all 15 tests
- **Database**: Uses transactions (no data persistence)
- **Isolation**: Each test is independent

## ✅ Best Practices

1. **Run tests before committing**: Ensure all tests pass
2. **Run tests after refactoring**: Verify nothing broke
3. **Add tests for new features**: Maintain coverage
4. **Fix failing tests immediately**: Don't let them accumulate

## 🎓 Learning from Tests

The tests serve as:
- **Documentation**: Show how the system works
- **Examples**: Demonstrate proper usage
- **Specifications**: Define expected behavior
- **Regression Prevention**: Catch breaking changes

## 📞 Support

If tests fail:
1. Check error messages carefully
2. Verify database migrations are up to date
3. Ensure all required models/factories exist
4. Check that services are properly registered

## 🎉 Success Indicators

When all tests pass, you'll see:
```
✓ 15 tests passed
✓ All assertions passed
✓ No errors or warnings
```

This confirms your Leave Management System is working correctly!

