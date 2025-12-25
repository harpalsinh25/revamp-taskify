# 🎉 Update Instructions - Paid Leave Management v2.0

## ⚡ **Quick Update (2 Steps)**

### **Step 1: Backup (Important!)**
```bash
# Backup your database
mysqldump -u username -p database_name > backup_before_update.sql

# Backup your files (optional but recommended)
cp -r /path/to/installation /path/to/backup
```

### **Step 2: Apply Update**

**Option A: Via cPanel/Web Host**
1. Extract the update ZIP file
2. Upload all files to your installation directory
3. Overwrite when prompted

**Option B: Via Terminal/SSH**
```bash
# Navigate to your installation directory
cd /path/to/installation

# Extract update
unzip update-v2.0.zip

# Run migrations
php artisan migrate
```

### **That's It! 🚀**

The system will automatically:
- ✅ Create new database tables
- ✅ Add required columns
- ✅ Initialize leave balances for all users
- ✅ Set default values (12 paid leaves/year)

**No manual configuration needed!**

---

## ✅ **Verify Installation**

After updating, check these:

### **1. Database Check**
Run in your database:
```sql
-- Should return rows
SELECT * FROM user_leave_balances LIMIT 5;

-- Should show new columns
DESCRIBE leave_requests;
```

### **2. Admin Panel Check**
1. Login as Admin
2. Go to **Settings → General Settings**
3. Scroll down - you should see **"Total Paid Leaves / Year"** field
4. Set your desired value (default is 12)

### **3. Leave Requests Check**
1. Go to **Leave Requests** page
2. You should see a **Leave Balance Widget** at the top showing:
   - Total Annual Leaves
   - Used Paid Leaves
   - Remaining Balance
   - Unpaid Leaves

### **4. Test Workflow**
1. Create a test leave request
2. As Admin/Leave Editor, edit the request
3. You should see **"Mark as Paid Leave"** toggle
4. Approve the leave
5. Check that balance updates

---

## 🔧 **Configuration**

### **Set Annual Leave Quota**

1. Go to **Settings → General Settings**
2. Find **"Total Paid Leaves / Year"**
3. Enter desired value (e.g., 12, 15, 20)
4. Click **Update**

This applies to all users across all workspaces.

---

## ❓ **FAQ**

### **Q: Do I need to run any commands?**
**A:** No! Just run `php artisan migrate` and everything else is automatic.

### **Q: What if I have 1000+ users?**
**A:** The migration handles this efficiently. It may take 1-2 minutes for very large installations.

### **Q: Can I change the annual leaves after initialization?**
**A:** Yes! Change it in Settings. Existing balances won't change, but new users will get the new value.

### **Q: What happens on January 1st next year?**
**A:** The system automatically creates new balances for the new year (scheduled task).

### **Q: Can users see their balance?**
**A:** Yes! They see it on the Leave Requests page. Admins can see all users' balances.

### **Q: What if migration fails?**
**A:** Check error log in `storage/logs/laravel.log`. Common issues:
- Database connection error
- Insufficient permissions
- Duplicate migration (already ran)

**Solution:** Run `php artisan migrate:status` to check which migrations ran.

---

## 🆘 **Troubleshooting**

### **Issue: "Table user_leave_balances already exists"**
**Cause:** Migration already ran successfully.
**Solution:** No action needed. This is normal.

---

### **Issue: "Column 'total_paid_leaves_per_year' not found"**
**Cause:** Settings not saved yet.
**Solution:**
1. Go to Settings → General Settings
2. Enter any value in "Total Paid Leaves / Year"
3. Click Update

---

### **Issue: Leave balance shows 0 for all users**
**Cause:** Annual leaves not configured.
**Solution:**
1. Settings → General Settings
2. Set "Total Paid Leaves / Year" to 12 (or your value)
3. Click Update
4. Optional: Click "Initialize Leave Balances" button

---

### **Issue: Balances not showing after update**
**Solution:** Run initialization manually:

**Via Admin UI:**
1. Settings → General Settings
2. Click **"Initialize Leave Balances"** button

**Via Terminal:**
```bash
php artisan leaves:initialize-balances
```

---

## 📊 **What's New**

### **For Users:**
✅ **Leave Balance Tracking** - See available paid leaves
✅ **Balance Widget** - Real-time balance on Leave Requests page
✅ **Color Warnings** - Visual indicators when balance is low

### **For Admins:**
✅ **Paid/Unpaid Toggle** - Mark leaves as paid or unpaid
✅ **Auto-Calculation** - System auto-splits paid/unpaid based on balance
✅ **Balance Restoration** - Auto-restores when leaves are deleted
✅ **Annual Reset** - Auto-creates new balances on Jan 1st

### **For Developers:**
✅ **Service Class** - `LeaveBalanceService` for custom logic
✅ **Helper Functions** - `calculate_leave_days()`, `get_user_leave_balance()`
✅ **Artisan Commands** - `leaves:initialize-balances`
✅ **API Endpoint** - `/leave-requests/get-user-balance`

---

## 🎯 **Key Features**

### **Automatic Tracking**
- System tracks paid vs unpaid leaves
- Updates balance on approval/rejection
- Restores balance on deletion
- Handles partial (0.5 day) leaves

### **Smart Calculations**
- If user has 5 days remaining and requests 7 days:
  - 5 days marked as paid
  - 2 days marked as unpaid
- Admin can override via toggle

### **Workspace-Specific**
- Each workspace has separate leave policies
- Users can have different balances per workspace

---

## 📞 **Support**

If you encounter issues:

1. **Check Laravel Log**: `storage/logs/laravel.log`
2. **Verify Migrations**: `php artisan migrate:status`
3. **Review This Guide**: Especially Troubleshooting section
4. **Contact Support**: Include error logs and screenshots

---

## ✨ **Success Checklist**

After update, you should have:

- [x] New "Total Paid Leaves / Year" field in General Settings
- [x] Leave Balance Widget on Leave Requests page
- [x] "Mark as Paid Leave" toggle in edit modal (Admin only)
- [x] Database table `user_leave_balances` created
- [x] New columns in `leave_requests` table
- [x] All users have initial balance records

---

**Update Date:** October 2025
**Version:** 2.0
**Compatibility:** All previous versions

---

## 🎊 **Thank You!**

Your system now includes enterprise-grade leave management. Enjoy the new features!

For questions or support, please contact us with your license key.




