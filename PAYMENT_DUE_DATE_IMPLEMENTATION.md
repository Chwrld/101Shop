# Payment Due Date Implementation Summary

## Overview
Added comprehensive Payment Due Date functionality to the transaction system. This allows admin users to set and track payment due dates for transactions.

## Changes Made

### 1. Database Migration Script
**File:** `add_payment_due_column.php`
- Created migration script to add `payment_due_date` column to the `transactions` table
- Column type: DATE
- Default: NULL
- Column added with comment: "Date when payment is due"

### 2. Backend - TransactionsHandler
**File:** `backend/handlers/transactionsHandler.php`

#### Updated Methods:
- **getAllTransactions()** - Now includes `t.payment_due_date` in SELECT clause
- **getAllTransactionsPaginated()** - Now includes `t.payment_due_date` in SELECT clause
- **getTransactionById()** - Now includes `t.payment_due_date` in SELECT clause
- **updatePaymentStatus()** - Enhanced with new parameter `$paymentDueDate`
  - Now accepts and saves payment_due_date with payment status updates
  - Added payment_due_date to response data

#### New Methods:
- **setPaymentDueDate($transactionId, $paymentDueDate)** - Dedicated function to set payment due date
  - Validates date format (YYYY-MM-DD)
  - Updates only the payment_due_date field
  - Returns transaction_id and payment_due_date in response

### 3. Backend - Transaction API
**File:** `backend/api/transaction_api.php`

#### Enhanced Routes:
- **updatePayment** - Now accepts `payment_due_date` parameter
  - Added validation and forwarding to handler
  - Passes payment_due_date to updatePaymentStatus method

#### New Route:
- **setPaymentDueDate** - Dedicated API endpoint for setting payment due date
  - Method: PUT
  - Parameters:
    - `transaction_id` (required, integer)
    - `payment_due_date` (required, string in YYYY-MM-DD format)
  - Returns success/failure response with transaction details

### 4. Frontend - Transactions View
**File:** `views/transactions.php`

#### UI Updates:
- **Table Header** - Added "Payment Due" column between "Payment Date" and "Received By"
- **Transaction Rows** - Now display payment_due_date from API response

#### Modal Updates:
- **Update Payment Modal** - Added new input field
  - Label: "Payment Due Date"
  - Input type: date
  - Name: payment_due_date
  - Placed after "GCash Reference Number" field

#### JavaScript Functions:

**Updated `updatePaymentStatus()`:**
- Now captures payment_due_date from form input
- Includes payment_due_date in form data sent to API
- Passes payment_due_date parameter to updatePayment action

**New `setPaymentDueDate(transactionId, paymentDueDate)`:**
- Standalone function to set payment due date
- Validates date format (YYYY-MM-DD)
- Calls `setPaymentDueDate` API endpoint
- Reloads transactions on success
- Shows appropriate alerts for success/failure

**Error Messages Updated:**
- Colspan values updated from 8 to 9 to accommodate new column

## Usage Examples

### Setting Payment Due Date with Payment Status Update
1. User opens "Update Payment Status" modal
2. Selects payment status (Paid/Pending)
3. Selects staff member who received payment
4. Selects payment method (Cash/GCash)
5. Optionally sets payment due date using date picker
6. Clicks "Update" button
7. System saves payment status and payment due date together

### Setting Payment Due Date Directly
```javascript
// From browser console or page script
setPaymentDueDate(123, '2025-12-31');
// Where 123 is the transaction_id
// And '2025-12-31' is the payment due date in YYYY-MM-DD format
```

### API Call Example
```javascript
fetch('../backend/api/transaction_api.php?action=setPaymentDueDate', {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        transaction_id: 123,
        payment_due_date: '2025-12-31'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

## Database Schema
```sql
-- Column added to transactions table
ALTER TABLE transactions ADD COLUMN payment_due_date DATE NULL DEFAULT NULL COMMENT 'Date when payment is due';
```

## Field Details

### payment_due_date Column
- **Data Type:** DATE
- **Default Value:** NULL
- **Nullable:** Yes
- **Used For:** Storing the date by which payment should be received
- **Format:** YYYY-MM-DD

## Testing Checklist

- [x] Payment due date column displays in transaction table
- [x] Payment due date can be set via Update Payment modal
- [x] Payment due date updates are saved to database
- [x] API endpoint for setting payment due date works
- [x] setPaymentDueDate function works independently
- [x] Date format validation works
- [x] Error handling for invalid dates
- [x] Transaction list refreshes after updating payment due date
- [x] No SQL errors or warnings

## Dependencies
- jQuery (for AJAX calls)
- Bootstrap (for modals and form elements)
- PHP 7.0+ (for type declarations)
- MySQL (for DATE data type)

## Compatibility
- Works with existing payment status system
- Backward compatible - optional field
- No breaking changes to existing API
- Works with all payment methods (Cash, GCash)

## Future Enhancements
- Add reminder notifications for overdue payments
- Add payment due date filters to transaction list
- Add due date alerts/indicators in UI
- Add date range filtering for payments by due date
- Add dashboard statistics for overdue transactions

## Notes
- The migration script uses Docker host configuration
- Script validates that column doesn't already exist before creating it
- Payment due date is optional - can be set to NULL
- Date validation enforces YYYY-MM-DD format in API
