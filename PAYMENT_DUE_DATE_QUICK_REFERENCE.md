# Payment Due Date - Quick Reference Guide

## What's New?

Added a **Payment Due Date** column to track when payments are expected for each transaction.

## Where to Find It

### In Transactions View
1. Go to **Transactions** page
2. Look at the transaction table
3. New column: **"Payment Due"** appears between "Payment Date" and "Received By"

### In Update Payment Modal
1. Click the payment icon (✓) for any transaction
2. Modal opens: "Update Payment Status"
3. Scroll down to see: **"Payment Due Date"** input field
4. It's a date picker - click to select or type date

## How to Use

### Method 1: Set Payment Due Date While Updating Payment
1. Open transaction to update
2. Click payment icon
3. Select payment status
4. Select who received payment
5. Select payment method
6. **Fill in Payment Due Date** (optional)
7. Click "Update"

### Method 2: Set Payment Due Date Only
```javascript
// In browser console
setPaymentDueDate(transactionID, 'YYYY-MM-DD');

// Example:
setPaymentDueDate(123, '2025-12-31');
```

## Date Format
Always use: **YYYY-MM-DD**
- ✓ Correct: 2025-12-31
- ✗ Wrong: 12/31/2025
- ✗ Wrong: 31-12-2025

## What Gets Saved

| Field | When Saved |
|-------|-----------|
| Payment Due Date | When you update payment status with date filled in, or use setPaymentDueDate function |
| Payment Status | When you update payment (Paid/Pending) |
| Payment Date | Auto-set to today when marked as Paid |

## API Endpoints

### Update Payment with Due Date
```
PUT /backend/api/transaction_api.php?action=updatePayment
```
Payload:
```json
{
  "transaction_id": 123,
  "payment_status": "Pending",
  "received_by": 5,
  "payment_method": "Cash",
  "reference_number": "",
  "payment_due_date": "2025-12-31"
}
```

### Set Payment Due Date Only
```
PUT /backend/api/transaction_api.php?action=setPaymentDueDate
```
Payload:
```json
{
  "transaction_id": 123,
  "payment_due_date": "2025-12-31"
}
```

## Common Tasks

### Set Payment Due 30 Days from Today
```javascript
const today = new Date();
const dueDate = new Date(today.setDate(today.getDate() + 30));
const formattedDate = dueDate.toISOString().split('T')[0];
setPaymentDueDate(123, formattedDate);
```

### Set Payment Due End of Month
```javascript
const today = new Date();
const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
const formattedDate = endOfMonth.toISOString().split('T')[0];
setPaymentDueDate(123, formattedDate);
```

### Clear Payment Due Date (Set to NULL)
Currently requires direct database update - API enhancement coming soon.

## Troubleshooting

### Date not saving?
- Check format: must be YYYY-MM-DD
- Ensure all required fields are filled (payment status, received by, method)
- Check browser console for errors

### Payment Due Date shows as "-"?
- Date hasn't been set yet (it's optional)
- Either set it in the modal or use setPaymentDueDate function

### Getting "Invalid date format" error?
- Use YYYY-MM-DD format
- Example: 2025-12-25 (not 12-25-2025)

## Files Modified

| File | Change |
|------|--------|
| backend/handlers/transactionsHandler.php | Added payment_due_date to all queries and new setPaymentDueDate() method |
| backend/api/transaction_api.php | Added payment_due_date parameter to updatePayment, added setPaymentDueDate endpoint |
| views/transactions.php | Added Payment Due column to table, added date input to modal, added setPaymentDueDate() function |
| add_payment_due_column.php | New migration script to add database column |

## Database Migration

Run this to add the column (if needed):
```bash
cd /path/to/application
php add_payment_due_column.php
```

Or run SQL directly:
```sql
ALTER TABLE transactions ADD COLUMN payment_due_date DATE NULL DEFAULT NULL;
```

## Tips & Best Practices

1. **Set due dates for all Pending transactions** - Helps track payment deadlines
2. **Update when payment received** - Mark as Paid and update due date if needed
3. **Use for follow-up tracking** - Filter by due date to find overdue payments (feature coming soon)
4. **Standard terms** - Consider setting consistent terms like 30/60/90 days

## Questions?

Check the full implementation details in:
`PAYMENT_DUE_DATE_IMPLEMENTATION.md`
