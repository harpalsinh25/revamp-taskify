# Leave Management Migrations - Correct Order for Update

## ✅ Actions Completed
1. ✅ Deleted 3 problematic migrations with incorrect dates (2025_01_15_*)
2. ✅ Created 1 consolidated migration with correct date (2025_11_21_*)
3. ✅ Deleted UserLeaveBalanceFactory.php (not needed for production updates)

---

## 📋 Complete Leave-Related Migrations List (In Order)

### **Include ONLY These Migrations in Your Update Package:**

```
1. 2025_10_28_104837_create_user_leave_balances_table.php
   └─ Creates user_leave_balances table with basic fields

2. 2025_10_28_104916_add_paid_leave_fields_to_leave_requests_table.php
   └─ Adds paid_days, unpaid_days, total_days, is_paid fields to leave_requests

3. 2025_10_28_111914_seed_initial_leave_balances.php
   └─ Seeds initial balances for existing users

4. 2025_10_28_120000_fix_existing_leave_requests_data.php
   └─ Migrates existing leave request data to new fields

5. 2025_10_28_130000_transition_year_leave_balances.php
   └─ Handles transition year logic (pre-implementation vs post-implementation)

6. 2025_10_28_140000_add_leave_accrual_settings.php
   └─ Adds accrued_leaves, months_worked, accrual_start_date fields
   └─ Updates settings for monthly accrual

7. 2025_11_19_054250_add_advanced_paid_leaves_to_user_leave_balances_table.php
   └─ Adds advanced_paid_leaves field
   └─ Migrates negative remaining_paid_leaves to advanced_paid_leaves
   └─ Recalculates used_paid_leaves from LeaveRequests

8. 2025_11_21_100000_add_critical_leave_management_features.php ⭐ NEW
   └─ Creates leave_balance_adjustments table (CRITICAL)
   └─ Creates leave_overlap_logs table (audit trail)
   └─ Adds company_year field (CRITICAL - used 80+ times)
   └─ Adds carry_forward_leaves and expired_leaves fields (future features)
```

---

## 🗂️ Files to Include in Update Package

### **Migrations (8 files):**
```
database/migrations/2025_10_28_104837_create_user_leave_balances_table.php
database/migrations/2025_10_28_104916_add_paid_leave_fields_to_leave_requests_table.php
database/migrations/2025_10_28_111914_seed_initial_leave_balances.php
database/migrations/2025_10_28_120000_fix_existing_leave_requests_data.php
database/migrations/2025_10_28_130000_transition_year_leave_balances.php
database/migrations/2025_10_28_140000_add_leave_accrual_settings.php
database/migrations/2025_11_19_054250_add_advanced_paid_leaves_to_user_leave_balances_table.php
database/migrations/2025_11_21_100000_add_critical_leave_management_features.php
```

### **Models (2 new files):**
```
app/Models/LeaveBalanceAdjustment.php
app/Models/LeaveOverlapLog.php
```

### **Services (5 files):**
```
app/Services/LeaveBalanceEngine.php
app/Services/LeaveBalanceService.php
app/Services/LeaveCalculationService.php
app/Services/LeaveBalanceSyncService.php
app/Services/LeaveRequestValidator.php
```

### **Commands (1 file):**
```
app/Console/Commands/InitializeLeaveBalances.php
```

### **Controllers (updated):**
```
app/Http/Controllers/LeaveRequestController.php
app/Http/Controllers/LeaveBalanceController.php
app/Http/Controllers/PayslipsController.php
app/Http/Controllers/SettingsController.php
```

### **Views (updated):**
```
resources/views/leave_requests/list.blade.php
resources/views/leave_balances/index.blade.php
resources/views/components/leave/remaining-leaves-pill.blade.php
resources/views/settings/languages.blade.php
```

### **JavaScript (updated):**
```
public/assets/js/pages/leave-balances.js
```

---

## ❌ Files to EXCLUDE from Update

### **DO NOT Include:**
```
❌ database/factories/UserLeaveBalanceFactory.php (deleted - only for dev/testing)
❌ database/factories/LeaveRequestFactory.php (only for dev/testing)
❌ Any 2025_01_15_* migration files (deleted - had wrong dates)
```

---

## 🎯 Migration Execution Order Summary

When users run `php artisan migrate`, Laravel will execute in this order:

1. **October 28 migrations** - Create tables, add fields, seed data, fix data
2. **November 19 migration** - Add advanced_paid_leaves field
3. **November 21 migration** - Add critical tables and company_year field ⭐

This order ensures:
- ✅ Tables exist before fields are added
- ✅ Fields exist before data is migrated
- ✅ No column dependency issues
- ✅ All critical features are properly installed

---

## 📌 Critical Features in New Migration (2025_11_21)

### **1. leave_balance_adjustments table** (CRITICAL)
- Tracks payslip overrides and advanced paid leaves
- Used by LeaveBalanceSyncService, LeaveBalanceEngine, LeaveCalculationService
- Required for payslip override functionality to work

### **2. company_year field** (CRITICAL)
- Used 80+ times across 15 files
- Supports company fiscal year vs calendar year
- Required for all leave balance calculations

### **3. leave_overlap_logs table** (Optional)
- Audit trail for overlap detection
- Model exists, not actively used yet
- Future feature

### **4. carry_forward_leaves & expired_leaves** (Optional)
- Defined with defaults (0)
- Not actively calculated yet
- Future features

---

## ✅ Verification Checklist

After users update, they should:

1. ✅ Run `php artisan migrate` - should complete without errors
2. ✅ Check tables exist:
   - `user_leave_balances` (with company_year, advanced_paid_leaves, carry_forward_leaves, expired_leaves)
   - `leave_balance_adjustments` (new)
   - `leave_overlap_logs` (new)
3. ✅ Test payslip override functionality - should create adjustment records
4. ✅ Check leave balance dashboard - should show advanced leaves
5. ✅ Run "Initialize & Recalculate Leave Balances" button - should fix any historical data

---

## 🚀 Update Instructions for Users

**Step 1:** Backup database
```bash
php artisan backup:database
```

**Step 2:** Pull/apply update files

**Step 3:** Run migrations
```bash
php artisan migrate
```

**Step 4:** Recalculate leave balances (via Settings > General > Leave Settings)
- Click "Initialize & Recalculate Leave Balances" button
- This will:
  - Fix any historical adjustment records with incorrect delta_paid
  - Update total_annual_leaves if settings changed
  - Recalculate all balances including adjustments

**Step 5:** Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 📝 Notes

- All migrations use `if (!Schema::hasTable())` and `if (!Schema::hasColumn())` checks
- Safe to run multiple times without errors
- Migrations are idempotent (can be run multiple times safely)
- No data loss will occur on rollback (down() methods are safe)

---

Generated: November 21, 2025

