# 📅 Monthly Leave Accrual System Guide

## 🎯 Overview

The Monthly Accrual System allows employees to **earn** paid leaves gradually throughout the year instead of receiving the full quota upfront. This is a more professional and fair approach used by many companies worldwide.

---

## 💡 Why Monthly Accrual?

### Problems with Lump Sum Allocation:

```
❌ Lump Sum (Old Way):
   Jan 1: Employee gets 15 days immediately

   Problems:
   - New joiners in December get full 15 days for 1 month
   - Employee can take all 15 days in January, then quit
   - Not fair for mid-year joiners
   - Company financial risk
```

### Solution: Monthly Accrual ✅

```
✅ Monthly Accrual (Your Way):
   Every month: Employee earns 1.25 days

   Benefits:
   - Fair to everyone (earn as you work)
   - Automatic proration for mid-year joiners
   - Prevents abuse (can't take unearned leaves)
   - Industry best practice
   - Better cash flow for company
```

---

## 📊 How It Works

### Formula:

```
Monthly Accrual Rate = Total Annual Leaves ÷ 12
Example: 15 days per year ÷ 12 months = 1.25 days/month
```

### Accrual Calculation:

```
Accrued Leaves = Months Worked × Monthly Rate
```

---

## 📈 Examples

### Example 1: Full Year Employee

```
Employee: John Doe
Joining Date: Jan 1, 2024
Annual Quota: 15 days
Monthly Rate: 1.25 days/month

Month-by-Month Accrual:
┌──────────┬────────────┬───────────────┐
│  Month   │  Accrued   │  Cumulative   │
├──────────┼────────────┼───────────────┤
│ January  │   1.25     │     1.25      │
│ February │   1.25     │     2.50      │
│ March    │   1.25     │     3.75      │
│ April    │   1.25     │     5.00      │
│ May      │   1.25     │     6.25      │
│ June     │   1.25     │     7.50      │
│ July     │   1.25     │     8.75      │
│ August   │   1.25     │    10.00      │
│ September│   1.25     │    11.25      │
│ October  │   1.25     │    12.50      │
│ November │   1.25     │    13.75      │
│ December │   1.25     │    15.00      │
└──────────┴────────────┴───────────────┘

By year-end: Full 15 days accrued ✅
```

### Example 2: Mid-Year Joiner

```
Employee: Jane Smith
Joining Date: July 1, 2025
Annual Quota: 15 days
Monthly Rate: 1.25 days/month

Accrual in 2025:
┌──────────┬────────────┬───────────────┐
│  Month   │  Accrued   │  Cumulative   │
├──────────┼────────────┼───────────────┤
│ July     │   1.25     │     1.25      │
│ August   │   1.25     │     2.50      │
│ September│   1.25     │     3.75      │
│ October  │   1.25     │     5.00      │
│ November │   1.25     │     6.25      │
│ December │   1.25     │     7.50      │
└──────────┴────────────┴───────────────┘

By year-end: 7.50 days accrued (for 6 months) ✅

In 2026:
Gets full 15 days (working full year) ✅
```

### Example 3: Leave Request Validation

```
Current Date: October 15, 2025
Employee: John Doe (joined Jan 1, 2025)
Months Worked: 10 months
Accrued: 10 × 1.25 = 12.50 days
Already Used: 8.00 days
Available: 12.50 - 8.00 = 4.50 days

Leave Request Scenarios:
┌─────────────────┬────────────┬──────────────┐
│  Request        │  Days      │  Result      │
├─────────────────┼────────────┼──────────────┤
│ Nov 1-2 (2d)    │    2.00    │  ✅ Approved  │
│ Nov 10-13 (4d)  │    4.00    │  ✅ Approved  │
│ Nov 1-5 (5d)    │    5.00    │  ❌ Rejected  │
│                 │            │  (Insufficient│
│                 │            │   balance)    │
└─────────────────┴────────────┴──────────────┘

By November 1, will have accrued:
11 months × 1.25 = 13.75 days
Available: 13.75 - 8.00 = 5.75 days
Then the 5-day request would be approved! ✅
```

---

## ⚙️ Configuration

### In Settings Page:

1. Navigate to **Settings → General Settings**
2. Find **"Leave Accrual Type"** dropdown
3. Select **"Monthly Accrual"**
4. Set **"Total Paid Leaves / Year"** (e.g., 15)
5. System automatically calculates monthly rate
6. Click **"Update"**

### Monthly Rate Calculation:

The system shows you the rate in real-time:
```
Total Leaves: 15
Monthly Rate: 1.25 days/month (auto-calculated)
```

### Switching Between Systems:

```
From Lump Sum → Monthly Accrual:
- Click "Initialize & Recalculate Balances"
- System recalculates accrued leaves for everyone
- Based on months worked in current year

From Monthly Accrual → Lump Sum:
- Change setting to "Lump Sum"
- Click "Initialize & Recalculate Balances"
- Everyone gets full annual quota
```

---

## 🎨 User Interface

### What Employees See:

```
╔═══════════════════════════════════════════════╗
║           MY LEAVE BALANCE (2025)             ║
╠═══════════════════════════════════════════════╣
║  Total Annual Leaves:     15.00               ║
║  ├─ Accrued: 12.50 days                       ║
║                                               ║
║  Used Paid Leaves:         8.00               ║
║  Remaining Paid Leaves:    4.50               ║
║  Unpaid Leaves Taken:      0                  ║
╚═══════════════════════════════════════════════╝

ℹ️ Monthly Accrual System:
   • You earn 1.25 days per month
   • Worked: 10 months
   • Accrued so far: 12.50 days
```

### Balance Cards Show:

1. **Total Annual Leaves**: 15.00
   - Badge: "Accrued: 12.50" (if monthly accrual enabled)
2. **Used Paid Leaves**: Shows actual usage
3. **Remaining**: Accrued - Used (not Total - Used)
4. **Info Alert**: Explains the monthly accrual system

---

## 🔧 Technical Implementation

### Database Structure:

```sql
user_leave_balances table:
├── total_annual_leaves (15.00)
├── accrued_leaves (12.50)       ← NEW
├── months_worked (10)            ← NEW
├── accrual_start_date (2025-01-01) ← NEW
├── used_paid_leaves (8.00)
└── remaining_paid_leaves (4.50)
```

### Calculation Logic:

```php
// In LeaveBalanceService.php

// 1. Determine accrual start date
if (user joined in current year) {
    accrualStartDate = joining date (start of month)
} else {
    accrualStartDate = January 1
}

// 2. Calculate months worked
monthsWorked = diff in months from accrualStartDate to today
monthsWorked = min(monthsWorked, 12) // Cap at 12

// 3. Calculate accrued leaves
monthlyRate = totalAnnualLeaves / 12  // 1.25
accruedLeaves = monthsWorked × monthlyRate

// 4. Calculate remaining
remainingLeaves = accruedLeaves - usedLeaves
```

---

## 📅 Monthly Update Process

### Automatic Updates:

The system automatically updates accrued leaves when:
1. User views their balance (real-time calculation)
2. Admin clicks "Initialize & Recalculate Balances"
3. Leave request is submitted (validates against current accrual)
4. New month starts (calculated on next access)

### No Cron Job Needed!

The accrual is calculated **on-demand** based on:
- Current date
- User's joining date
- Months worked

So it's always up-to-date without manual intervention! ✨

---

## 🎯 Best Practices

### 1. Clear Communication

**Email Template for Employees:**
```
Subject: New Leave Accrual System - Earn as You Work!

Dear Team,

Starting [date], we're implementing a Monthly Leave Accrual system:

✓ You'll earn 1.25 days of paid leave each month
✓ Total remains 15 days per year
✓ Your current accrued balance: [X] days

Benefits:
• Fair to new joiners
• Transparent tracking
• Earn as you work

Questions? Contact HR.
```

### 2. Gradual Rollout

```
Phase 1: Enable for new joiners only
Phase 2: Apply to all employees next year (Jan 1)
Phase 3: Full implementation
```

### 3. Grace Period

Consider allowing employees to use slightly more than accrued for first few months (advance leave policy).

---

## ❓ FAQ

### Q1: What if employee needs more leaves than accrued?

**A:** Admin can:
1. Approve as unpaid leave
2. Grant advance leave (mark as paid, will deduct from future accrual)
3. Make exception for emergencies

### Q2: What happens at year-end?

**A:** On Jan 1:
- Previous year balance archived
- New year starts fresh
- Accrued = 0
- Starts accruing again (1.25/month)

### Q3: Does unused accrual carry forward?

**A:** Depends on your company policy:
- **Option A:** Reset to 0 (use it or lose it)
- **Option B:** Carry forward up to X days
- Configure in settings (future enhancement)

### Q4: What about probation period?

**A:** Can be configured:
- Option 1: Start accruing from day 1
- Option 2: Start accruing after probation
- Set `accrual_start_date` accordingly

### Q5: Mid-month joiner calculation?

**A:** System uses `startOfMonth()`:
- Join anytime in July → Accrue from July 1
- Fair and simple calculation
- Can be customized if needed

---

## 🔄 Comparison Table

| Feature | Lump Sum | Monthly Accrual ⭐ |
|---------|----------|-------------------|
| **Allocation** | All at once (Jan 1) | Earned monthly |
| **Mid-year joiners** | Get full quota | Prorated automatically |
| **Fairness** | ⚠️ Can be exploited | ✅ Earn as you work |
| **Risk** | High (advance payment) | Low (pay as earned) |
| **Calculation** | Simple | Automated |
| **Industry Standard** | Old method | Modern HR practice |
| **Transparency** | Less visible | Clear monthly tracking |
| **Cash Flow** | Front-loaded | Distributed |

---

## 📊 Real-World Scenarios

### Scenario 1: New Company (No History)

```
Status: Fresh implementation
Recommendation: Monthly Accrual ✅

Why:
- Start with best practice from day 1
- No legacy issues to handle
- Clear, fair system
```

### Scenario 2: Mid-Year Switch

```
Status: Switching from lump sum to monthly
Recommendation: Hybrid for transition year

Implementation:
1. Current year (2025): Keep lump sum
2. Announce change for 2026
3. Jan 1, 2026: Switch to monthly accrual
4. Clear communication about change
```

### Scenario 3: Growing Startup

```
Status: Small team growing rapidly
Recommendation: Monthly Accrual ✅

Why:
- Many new joiners throughout year
- Fair allocation crucial for morale
- Scales well as team grows
```

---

## 🚀 Getting Started

### Step 1: Enable Monthly Accrual
```
Settings → General Settings
├── Total Paid Leaves / Year: 15
├── Leave Accrual Type: Monthly Accrual
└── Save
```

### Step 2: Initialize Balances
```
Click: "Initialize & Recalculate Balances"
Result: All users get accrued leaves calculated
```

### Step 3: Communicate
```
Email employees about:
├── New system explanation
├── Their current accrued balance
├── How to check balance
└── FAQ link
```

### Step 4: Monitor
```
First month:
├── Check if calculations are correct
├── Handle any questions
├── Make adjustments if needed
└── Gather feedback
```

---

## 📈 Success Metrics

After implementing monthly accrual, track:

1. **Employee Satisfaction**
   - Survey before/after
   - Feedback on fairness

2. **Leave Abuse Reduction**
   - Compare: leaves taken in first quarter
   - Before vs after implementation

3. **Administrative Efficiency**
   - Time saved on manual calculations
   - Fewer disputes/exceptions

4. **Financial Impact**
   - Cash flow improvement
   - Reduced advance leave provision

---

## 🎉 Benefits Summary

### For Employees:
- ✅ Fair system (earn as you work)
- ✅ Clear, transparent calculations
- ✅ Real-time balance visibility
- ✅ No confusion about entitlement

### For HR/Admin:
- ✅ Automated calculations
- ✅ No manual proration needed
- ✅ Easy to explain and defend
- ✅ Industry-standard approach

### For Company:
- ✅ Better cash flow
- ✅ Reduced financial risk
- ✅ Fairer to all employees
- ✅ Professional HR practice

---

## 📞 Support

For questions:
- **Technical**: Check `app/Services/LeaveBalanceService.php`
- **Business**: Refer to this guide
- **Configuration**: Settings → General Settings

---

**Feature Status:** ✅ **PRODUCTION READY**

**Recommendation:** **Highly recommended for all implementations!**

**Your Specific Use Case (1.25 days/month for 15 annual):** **✅ Perfectly Supported!**























