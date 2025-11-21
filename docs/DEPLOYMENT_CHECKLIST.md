# 🚀 Deployment Checklist - Leave Management v2.0

## ✅ **Pre-Deployment Verification**

### **1. Database Migrations** ✅
```bash
☑ 2025_10_28_104837_create_user_leave_balances_table.php
☑ 2025_10_28_104916_add_paid_leave_fields_to_leave_requests_table.php
☑ 2025_10_28_111914_seed_initial_leave_balances.php
☑ 2025_10_28_120000_fix_existing_leave_requests_data.php
☑ 2025_10_28_130000_transition_year_leave_balances.php
☑ 2025_10_28_140000_add_leave_accrual_settings.php
```

### **2. Core Files Modified** ✅
```bash
☑ app/Services/LeaveBalanceService.php
☑ app/Models/User.php
☑ app/Models/UserLeaveBalance.php
☑ app/Http/Controllers/LeaveRequestController.php
☑ app/Http/Controllers/SettingsController.php
☑ resources/views/leave_requests/list.blade.php
☑ resources/views/settings/general_settings.blade.php
☑ public/assets/js/custom.js
```

### **3. Test Files Removed** ✅
```bash
☑ Removed: hcontroler.php
☑ Removed: script-test.php
☑ Removed: update_doj.php (temporary)
☑ Removed: check_leave_settings.php (temporary)
☑ Removed: fix_accrual_calculation.php (temporary)
```

---

## 📋 **Deployment Steps**

### **For Production Deployment:**

```bash
# Step 1: Backup Database
mysqldump -u username -p database_name > backup_before_v2.sql

# Step 2: Pull/Extract Latest Code
git pull origin main
# OR
# Extract update ZIP file

# Step 3: Run Migrations
php artisan migrate

# Step 4: Clear Caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Step 5: Initialize Leave Balances
php artisan leaves:initialize-balances

# Step 6: Verify
# Check: Settings → General Settings
# Check: Leave Requests page balance display
```

---

## ✅ **Post-Deployment Verification**

### **Test Case 1: Settings Page**
```
Navigate to: Settings → General Settings
Verify:
☑ "Total Paid Leaves / Year" field exists (default: 15)
☑ "Leave Accrual Type" dropdown exists
☑ "Monthly Accrual" is selected
☑ "Monthly rate: 1.25 days/month" displays correctly
☑ "Initialize & Recalculate Balances" button exists
```

### **Test Case 2: Leave Balance Display**
```
Navigate to: Leave Requests page
Verify:
☑ "My Leave Balance" section displays
☑ Shows: Total Annual Leaves
☑ Shows: Accrued badge (if monthly accrual enabled)
☑ Shows: Used Paid Leaves
☑ Shows: Remaining Paid Leaves
☑ Shows: Unpaid Leaves Taken
☑ Monthly accrual info alert displays (if enabled)
☑ Progress bar shows correct percentage
```

### **Test Case 3: New User**
```
Create new user with DOJ: Today
Navigate to Leave Requests as that user
Verify:
☑ Balance initialized automatically
☑ Months worked: 1
☑ Accrued: 1.25 days (for 15 annual / monthly accrual)
☑ Used: 0
☑ Remaining: 1.25
```

### **Test Case 4: Leave Request & Deletion**
```
Create & approve a leave request (2 days)
Verify:
☑ Balance deducts: Used = 2, Remaining decreases

Delete the leave request
Verify:
☑ Balance restores: Used = 0, Remaining increases
☑ Calculation uses accrued leaves (not total annual)
```

### **Test Case 5: Monthly Rate Calculation**
```
Settings → General Settings
Change "Total Paid Leaves / Year" to different values:

15 days → Monthly rate: 1.25 ✅
12 days → Monthly rate: 1.00 ✅
18 days → Monthly rate: 1.50 ✅
24 days → Monthly rate: 2.00 ✅

Verify rate updates automatically
```

---

## 🐛 **Common Issues & Solutions**

### **Issue: Balance shows wrong accrued amount**
```bash
Solution:
php artisan leaves:initialize-balances
# OR via UI: Settings → Click "Initialize & Recalculate Balances"
```

### **Issue: DOJ is null for existing users**
```sql
Solution:
UPDATE users SET doj = created_at WHERE doj IS NULL;
# Then run:
php artisan leaves:initialize-balances
```

### **Issue: Migration fails "table already exists"**
```
Solution: Already handled!
All migrations have hasTable() / hasColumn() checks
Safe to run multiple times
```

### **Issue: Used leaves not updating after delete**
```
Solution: Already fixed!
restoreBalance() now uses accrued_leaves
Just refresh page after delete
```

---

## 📊 **Expected Results (Your Setup)**

### **Configuration:**
```
Total Paid Leaves / Year: 15
Leave Accrual Type: Monthly Accrual
Monthly Rate: 1.25 days/month
```

### **User: Siddharth Gor**
```
DOJ: Oct 8, 2025
Current Date: Oct 28, 2025
Expected:
├─ Months Worked: 1
├─ Accrued: 1.25 days ✅
├─ Used: 0 days
└─ Remaining: 1.25 days ✅
```

---

## 📚 **Documentation Available**

Reference these files for details:

1. **FINAL_IMPLEMENTATION_SUMMARY.md** - Complete overview
2. **MONTHLY_ACCRUAL_GUIDE.md** - Detailed guide with examples
3. **MONTHLY_ACCRUAL_SUMMARY.md** - Quick reference
4. **TRANSITION_YEAR_POLICY.md** - Policy for existing customers
5. **LEAVE_BALANCE_INITIALIZATION_GUIDE.md** - Initialization methods
6. **DEPLOYMENT_CHECKLIST.md** - This file

---

## 🎯 **Success Criteria**

Your deployment is successful if:

- ✅ All migrations run without errors
- ✅ Leave balance displays correctly
- ✅ Monthly accrual shows 1.25 days/month
- ✅ DOJ is used for calculation
- ✅ Delete/restore balance works
- ✅ Settings page allows configuration
- ✅ No console errors
- ✅ No database errors

---

## 🔧 **Rollback Plan** (If Needed)

```bash
# Step 1: Restore Database
mysql -u username -p database_name < backup_before_v2.sql

# Step 2: Revert Code
git checkout previous_commit
# OR restore from backup

# Step 3: Clear Caches
php artisan config:clear
php artisan cache:clear
```

---

## 📞 **Support**

If issues persist:
1. Check `storage/logs/laravel.log`
2. Run: `php artisan leaves:initialize-balances`
3. Review `FINAL_IMPLEMENTATION_SUMMARY.md`
4. Check database: `SELECT * FROM user_leave_balances`

---

## ✨ **Final Notes**

- **Monthly accrual is now DEFAULT** for new installations
- **Transition policy active** for existing customers (pre-Oct 28 leaves don't count)
- **DOJ field used** for accurate calculations
- **All test files cleaned up**
- **Production ready** ✅

---

**Deployment Date:** October 28, 2025
**Version:** v2.0
**Status:** ✅ **READY FOR PRODUCTION**

🎉 **Your professional leave management system is ready!** 🚀























