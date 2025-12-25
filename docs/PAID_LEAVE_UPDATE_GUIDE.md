# 📋 Paid Leave Management - Update Guide

## 🚀 For Existing Customers

This update introduces a complete **Paid Leave Management System** with automatic leave balance tracking. Here's how to update your system:

---

## ⚡ **Quick Update Process (Zero Manual Steps)**

### **Step 1: Update Your Files**
Replace/update the following files from the update package:
- All files in `app/`, `database/`, `resources/`, `public/`, and `routes/` directories

### **Step 2: Run Migrations**
```bash
php artisan migrate
```

**That's it!** 🎉

The system will **automatically initialize leave balances** for all existing users during the migration process.

---

## 🔄 **Multiple Initialization Methods** (Pick One)

We provide **4 different ways** to initialize leave balances. The system handles this automatically, but you can use any of these backup methods:

### **Method 1: Automatic Migration (Recommended) ✅**
**Already done when you run migrations!**

The migration `seed_initial_leave_balances` automatically:
- Creates balance records for all users
- Sets default leaves (12 days per year)
- Skips users who already have balances (safe to run multiple times)

**No action needed from customers!**

---

### **Method 2: Admin UI Button (Easiest for End Users)**

For customers who want manual control:

1. Login as **Admin**
2. Go to **Settings → General Settings**
3. Scroll to **"Total Paid Leaves / Year"** field
4. Click the **"Initialize Leave Balances"** button
5. Confirm the action

✅ **Safe to click multiple times** - only creates missing balances

---

### **Method 3: Artisan Command (For Developers)**

For customers with server/SSH access:

```bash
# Initialize for current year (all workspaces)
php artisan leaves:initialize-balances

# Initialize for specific year
php artisan leaves:initialize-balances --year=2025

# Initialize for specific workspace
php artisan leaves:initialize-balances --workspace=1
```

---

### **Method 4: Scheduled Automatic Initialization**

The system **automatically runs** every January 1st at midnight to initialize balances for the new year.

**Configured in:** `app/Console/Kernel.php`

---

## 📊 **Configuration**

### Set Default Annual Leaves

1. Go to **Settings → General Settings**
2. Find **"Total Paid Leaves / Year"**
3. Set desired value (default: 12 days)
4. Click **Update**

This value applies to all users across all workspaces.

---

## 🔍 **Verification Steps**

After updating, verify the system is working:

### **For Admins:**
1. Go to **Leave Requests** page
2. You should see the **Leave Balance Widget** showing:
   - Total Annual Leaves
   - Used Paid Leaves
   - Remaining Balance
   - Unpaid Leaves

### **For All Users:**
1. Create a test leave request
2. Admin approves with **"Mark as Paid Leave"** toggle
3. Check that balance updates correctly

---

## 🎯 **Key Features**

### **Automatic Tracking**
- ✅ Auto-calculates paid vs unpaid leaves
- ✅ Updates balance on approval/rejection
- ✅ Restores balance on deletion
- ✅ Handles partial (half-day) leaves

### **Admin Control**
- ✅ Toggle to mark leaves as paid/unpaid
- ✅ View all users' balances
- ✅ One-click initialization button

### **User-Friendly**
- ✅ Balance widget on Leave Requests page
- ✅ Color-coded warnings (low/exhausted)
- ✅ Progress bar showing utilization

---

## 🆘 **Troubleshooting**

### **Issue: Balances not showing after update**

**Solution 1:** Click the **"Initialize Leave Balances"** button in General Settings

**Solution 2:** Run the artisan command:
```bash
php artisan leaves:initialize-balances
```

**Solution 3:** Check if migrations ran successfully:
```bash
php artisan migrate:status
```

Look for:
- `create_user_leave_balances_table` - should show "Ran"
- `seed_initial_leave_balances` - should show "Ran"

---

### **Issue: Getting errors when approving leaves**

**Cause:** Database not migrated properly

**Solution:**
```bash
php artisan migrate --force
php artisan leaves:initialize-balances
```

---

### **Issue: Balance shows 0 for all users**

**Cause:** Annual leaves not configured in settings

**Solution:**
1. Go to **Settings → General Settings**
2. Set **"Total Paid Leaves / Year"** to desired value (e.g., 12)
3. Click **Update**
4. Click **"Initialize Leave Balances"** button

---

## 📝 **Database Changes**

### **New Table: `user_leave_balances`**
```
- user_id
- workspace_id
- year
- total_annual_leaves
- used_paid_leaves
- remaining_paid_leaves
- timestamps
```

### **Updated Table: `leave_requests`**
```
New columns:
- total_days
- paid_days
- unpaid_days
- is_paid
```

---

## 🔐 **Permissions**

- **Regular Users:** Can view their own leave balance
- **Admin/Leave Editor:** Can mark leaves as paid/unpaid, view all balances
- **Admin Only:** Can configure annual leaves, initialize balances

---

## 📅 **Important Notes**

### **Calendar Year Basis**
- Leave balances are tracked per calendar year (Jan 1 - Dec 31)
- No carryover to next year (resets annually)

### **Partial Leaves**
- Half-day leaves = 0.5 days
- Properly deducted from balance

### **Multiple Workspaces**
- Each user has separate balance per workspace
- Balance is workspace-specific

---

## 🎁 **What's Included**

✅ **Database migrations** (auto-run)
✅ **Leave balance tracking** (auto-enabled)
✅ **Admin UI controls** (Settings page)
✅ **User balance widget** (Leave Requests page)
✅ **Paid/Unpaid toggle** (For admins)
✅ **Artisan commands** (For developers)
✅ **Scheduled tasks** (Auto-renewal)
✅ **Helper functions** (Developer-friendly)

---

## 📞 **Support**

If you encounter any issues:
1. Check this guide's Troubleshooting section
2. Verify migrations ran successfully
3. Try the "Initialize Leave Balances" button
4. Contact support with error details

---

## 🎉 **Success Indicators**

Your system is working correctly if:
- ✅ Leave Requests page shows balance widget
- ✅ Edit leave modal has "Mark as Paid Leave" toggle (for admins)
- ✅ General Settings has "Total Paid Leaves / Year" field
- ✅ Approving a leave updates the balance
- ✅ Deleting an approved leave restores the balance

---

**Thank you for updating! Your Paid Leave Management System is now active.** 🚀




