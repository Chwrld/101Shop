# Add Payment Method & Reference Number to Clever Cloud Database

## Steps:

1. Go to your phpMyAdmin (console.clever-cloud.com)
2. Click on the **SQL** tab
3. Copy and paste the SQL commands below
4. Click **Execute**

---

## SQL Commands:

```sql
ALTER TABLE transactions 
ADD COLUMN payment_method VARCHAR(50) DEFAULT 'Cash' AFTER payment_status;

ALTER TABLE transactions 
ADD COLUMN reference_number VARCHAR(100) DEFAULT NULL AFTER payment_method;
```

---

## Or run them one at a time:

### Step 1: Add payment_method column
```sql
ALTER TABLE transactions 
ADD COLUMN payment_method VARCHAR(50) DEFAULT 'Cash' AFTER payment_status;
```

### Step 2: Add reference_number column
```sql
ALTER TABLE transactions 
ADD COLUMN reference_number VARCHAR(100) DEFAULT NULL AFTER payment_method;
```

---

## After Adding Columns:

✅ Your transactions table will have:
- `payment_method` (stores "Cash" or "GCash")
- `reference_number` (stores GCash reference ID)

✅ Your application is already updated:
- Payment method dropdown in UI
- Reference number field (shows only for GCash)
- Backend API handles both fields
- Database handler saves both values
- Transaction ID sorting is working

✅ Test it by updating any transaction payment with the new payment method field
