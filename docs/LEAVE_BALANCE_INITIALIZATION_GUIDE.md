# Leave Balance Initialization Guide

## 📌 Overview

You have **THREE methods** to initialize/recalculate leave balances. Here's when to use each:

---

## ✅ Method 1: Migration (AUTOMATIC)

### When to Use:
- ✅ **Initial installation** - When customers first install your system
- ✅ **Software updates** - When deploying v2.0+ to existing customers
- ✅ **Production deployments** - Automatic, no manual intervention needed

### Command:
```bash
php artisan migrate
```

### What It Does:
1. Creates `user_leave_balances` table (if not exists)
2. Adds `paid_days`, `unpaid_days`, `is_paid`, `total_days` columns to `leave_requests`
3. **Seeds initial balances** for all users (if not exist)
4. **Fixes existing leave requests** - Populates paid/unpaid days for old requests
5. **Recalculates all balances** based on actual approved leaves

### Migrations Involved:
- `2025_10_28_104837_create_user_leave_balances_table.php`
- `2025_10_28_104916_add_paid_leave_fields_to_leave_requests_table.php`
- `2025_10_28_111914_seed_initial_leave_balances.php`
- `2025_10_28_120000_fix_existing_leave_requests_data.php` ← **This one fixes your issue!**

### ✅ Advantages:
- **Automatic** - No manual action required
- **One-time setup** - Runs during update deployment
- **Comprehensive** - Fixes old data + calculates balances
- **Production-safe** - Doesn't run if already completed

---

## 🎛️ Method 2: Settings Page Button (MANUAL UI)

### When to Use:
- 🔧 **After changing** "Total Paid Leaves / Year" in settings
- 🔄 **Data corrections** - When balances seem incorrect
- 👥 **After bulk user import** - For new users added via import
- 🆕 **New year rollover** - When starting a new calendar year
- 🧹 **Manual recalculation** - Admin wants to verify/fix balances

### How to Access:
1. Navigate to: **Settings → General Settings**
2. Scroll to: **"Total Paid Leaves / Year"** field
3. Click button: **"Initialize & Recalculate Balances"**
4. Confirm the action

### What It Does:
```javascript
POST /settings/initialize-leave-balances
```
- Creates missing leave balances for new users
- **Recalculates existing balances** from approved leaves (✨ **Now improved!**)
- Shows summary: "Newly initialized: X users, Recalculated: Y users"

### ✅ Advantages:
- **User-friendly** - No command line needed
- **On-demand** - Run anytime admin needs it
- **Safe to repeat** - Can run multiple times
- **Real-time feedback** - Shows toast notifications

---

## 💻 Method 3: Artisan Command (DEVELOPER/AUTOMATED)

### When to Use:
- 🤖 **Scheduled tasks** - Automated via cron/Laravel scheduler
- 🎯 **Specific workspace** - Initialize only one workspace
- 📅 **Specific year** - Initialize for past/future years
- 🔧 **SSH/Terminal access** - Server maintenance
- 🧪 **Testing** - During development/staging

### Commands:
```bash
# Initialize for current year, all workspaces
php artisan leaves:initialize-balances

# Initialize for specific year
php artisan leaves:initialize-balances --year=2024

# Initialize for specific workspace only
php artisan leaves:initialize-balances --workspace=1

# Combine options
php artisan leaves:initialize-balances --year=2025 --workspace=2
```

### What It Does:
- Creates balances for specified year/workspace
- Gets total annual leaves from settings
- Logs detailed output to console
- Safe to run multiple times

### ✅ Advantages:
- **Flexible** - Year and workspace targeting
- **Scriptable** - Can be automated
- **Detailed logs** - Console output for debugging
- **Scheduled** - Auto-runs Jan 1st at midnight (configured in `Kernel.php`)

### Auto-Schedule Setup:
Already configured in `app/Console/Kernel.php`:
```php
$schedule->command('leaves:initialize-balances')
    ->yearlyOn(1, 1, '00:00'); // Runs every January 1st at midnight
```

---

## 🎯 Recommended Workflow

### For Your Current Situation (Existing Customers Getting Update):

```
Customer extracts update files
         ↓
Customer runs: php artisan migrate
         ↓
✅ Migration creates tables
✅ Migration adds new columns
✅ Migration seeds initial balances (0 used, 15 remaining)
✅ Migration fixes old leave requests (populates paid/unpaid days)
✅ Migration recalculates balances (5 used, 10 remaining) ← YOUR ISSUE FIXED!
         ↓
System ready! All balances accurate!
```

**Customer action required:** Just run `php artisan migrate` ✨

---

## 🔄 For Future Scenarios

| Scenario | Recommended Method | Notes |
|----------|-------------------|-------|
| **New installation** | Migration (automatic) | Runs during `php artisan migrate` |
| **Software update v2.0** | Migration (automatic) | Fixes all existing data |
| **Changed total leaves setting** | Settings page button | Admin clicks button after changing value |
| **Imported new users** | Settings page button | Quick UI action |
| **New year (Jan 1st)** | Auto-scheduled command | Runs automatically at midnight |
| **Manual verification** | Settings page button | Admin wants to double-check |
| **Specific workspace only** | Artisan command | Use `--workspace=X` flag |
| **Server maintenance** | Artisan command | SSH access, detailed logs |

---

## 🚨 Important Notes

### Migration Safety:
- ✅ All migrations check if tables/columns exist before creating
- ✅ Won't duplicate data if run multiple times
- ✅ Safe to run on existing installations
- ✅ Logs all actions for debugging

### Settings Page Button:
- ✅ **NOW RECALCULATES** existing balances (previously only created new ones)
- ✅ Shows detailed summary of actions taken
- ✅ Safe to run multiple times
- ✅ No risk of data loss

### Artisan Command:
- ✅ Year-aware (defaults to current year)
- ✅ Workspace-specific option available
- ✅ Verbose console output
- ✅ Can be scheduled via Laravel scheduler

---

## 📊 Data Flow After Your Fix

```
Old Leave Request (Pre-v2.0)
├── from_date: 2025-10-28
├── to_date: 2025-11-01
├── status: approved
├── paid_days: NULL ❌
├── unpaid_days: NULL ❌
└── is_paid: NULL ❌

         ↓ Migration runs

Fixed Leave Request
├── from_date: 2025-10-28
├── to_date: 2025-11-01
├── status: approved
├── total_days: 5 ✅
├── paid_days: 5 ✅
├── unpaid_days: 0 ✅
└── is_paid: true ✅

         ↓ Balance recalculated

User Leave Balance
├── total_annual_leaves: 15
├── used_paid_leaves: 5 ✅ (was 0)
├── remaining_paid_leaves: 10 ✅ (was 15)
└── year: 2025
```

---

## 📝 Summary

**For your current situation:**
✅ Use **Migration** (already done!) - Fixed your pre-existing leave issue

**For day-to-day admin needs:**
✅ Use **Settings Page Button** - Easy UI access, now with recalculation

**For automation:**
✅ Use **Artisan Command** - Scheduled yearly, or manual with options

All three methods are safe, production-ready, and can be used as needed! 🚀























