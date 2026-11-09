# Archive History System - Complete Documentation

## ✅ System Status: FULLY IMPLEMENTED & CONNECTED

The Archive History system is already built and integrated into your RepairSystem. Here's what's working:

---

## 📊 What's Already Implemented

### 1. **Database Table** ✅
- Table: `archive_records`
- Stores all deleted records from any table
- Fields:
  - `id` - Auto-increment primary key
  - `table_name` - Which table the record came from
  - `record_id` - Original record ID
  - `deleted_data` - Full JSON of the deleted record
  - `deleted_by` - User ID who deleted it
  - `deleted_at` - Timestamp of deletion
  - `reason` - Why it was deleted

### 2. **Backend Handler** ✅
File: `backend/handlers/archiveHandler.php`

**Functions:**
- `archiveRecord()` - Saves deleted records to archive
- `getArchivedRecords()` - Retrieves archived records with pagination
- `restoreRecord()` - Restores a deleted record back to its original table

### 3. **API Endpoint** ✅
File: `backend/api/archive_history_api.php`

**Available Actions:**
- `getActivityLog` - Gets activity log with filtering
- `getArchivedRecords` - Gets paginated archived records
- `restoreRecord` - Restores a deleted record

### 4. **Frontend Page** ✅
File: `views/archive_history.php`

**Features:**
- Two tabs: Activity Log & Archived Records
- Search functionality
- Pagination
- View details modal
- Restore button for each archived record
- Beautiful modern UI with Material Icons

### 5. **Sidebar Navigation** ✅
File: `layout/sidebar.php` (Line 33-35)

The Archive History link is already in the sidebar:
```php
<li class="<?php echo basename($_SERVER['PHP_SELF']) == 'archive_history.php' ? 'active' : ''; ?>">
    <a href="archive_history.php">
        <i class="material-icons">archive</i>
        <span>Archive History</span>
    </a>
</li>
```

---

## 🔄 Which Modules Archive Records?

### Currently Archiving (When Deleted):
1. ✅ **Customers** (`customersHandler.php`)
2. ✅ **Appliances** (`appliancesHandler.php`)
3. ✅ **Parts** (`partsHandler.php`)
4. ✅ **Staff** (`staffsHandler.php`)
5. ✅ **Service Reports** (`serviceHandler.php`)
6. ✅ **Service Prices** (`servicePriceHandler.php`)

### How It Works:
When you delete any of these records, the system automatically:
1. Saves the complete record data to `archive_records`
2. Records who deleted it and when
3. Stores the deletion reason
4. Then deletes the original record

---

## 📋 How to Use the Archive System

### For Admins:

**Step 1: Access Archive History**
1. Log in to admin panel
2. Click "Archive History" in the sidebar
3. You'll see two tabs:
   - **Activity Log**: All create/update/delete actions
   - **Archived Records**: All deleted records

**Step 2: View Archived Records**
- See list of all deleted records
- Shows: Archive ID, Type, Record ID, Deleted Date, Reason
- Use search box to find specific records

**Step 3: View Details**
- Click "View" button to see full details of deleted record
- Shows all field values at time of deletion

**Step 4: Restore a Record**
1. Find the deleted record
2. Click "Restore" button
3. Confirm restoration
4. Record is moved back to original table
5. Removed from archive

---

## 🧪 Testing the System

### Test Scenario 1: Delete a Customer
```
1. Go to Customers page
2. Delete a customer
3. Go to Archive History
4. Click "Archived Records" tab
5. You should see the deleted customer
6. Click "View" to see customer details
7. Click "Restore" to bring them back
```

### Test Scenario 2: Delete a Part
```
1. Go to Parts page
2. Delete a part
3. Check Archive History
4. The part should be listed with reason "Part deleted"
5. Restore if needed
```

### Test Scenario 3: Search Archives
```
1. Go to Archive History
2. Type in search box (e.g., customer name, ID, table name)
3. Archives filter in real-time
4. Clear search to see all again
```

---

## 🔍 API Endpoints

### Get Archived Records
```
GET /backend/api/archive_history_api.php?action=getArchivedRecords&page=1&itemsPerPage=10&search=
```

Response:
```json
{
  "success": true,
  "data": {
    "archives": [...],
    "current_page": 1,
    "total_pages": 5,
    "total_items": 42,
    "items_per_page": 10
  }
}
```

### Restore Record
```
GET /backend/api/archive_history_api.php?action=restoreRecord&id=123
```

Response:
```json
{
  "success": true,
  "message": "Record restored successfully"
}
```

---

## 🎨 Frontend Features

### Archive Table Columns:
1. **Archive ID** - Unique identifier
2. **Type** - Table name (customers, parts, staff, etc.)
3. **Record ID** - Original record's ID
4. **Deleted At** - When it was deleted
5. **Reason** - Why it was deleted
6. **Actions** - View & Restore buttons

### Pagination:
- 10 items per page
- Previous/Next buttons
- Page numbers
- Shows "Showing X to Y of Z entries"

### Search:
- Real-time search across:
  - Table name
  - Record ID
  - Reason

---

## 🚀 Deployment Status

### Local Files: ✅ Complete
- All handlers have archiveRecord() calls
- API endpoint configured
- Frontend page styled and functional
- Sidebar link active

### Render Deployment:
After you commit and push, the system will be live on Render with:
- Archive records stored in production database
- Full restore functionality
- Activity logging

---

## 📝 Example Archive Data

When you delete a customer named "John Doe":

```json
{
  "id": 45,
  "table_name": "customers",
  "record_id": 22,
  "deleted_data": {
    "customer_id": 22,
    "FullName": "John Doe",
    "Contact": "09123456789",
    "Address": "123 Main St",
    "created_at": "2025-12-01 10:30:00"
  },
  "deleted_by": 1,
  "deleted_at": "2025-12-11 14:25:30",
  "reason": "Customer deleted"
}
```

You can view this in Archive History and restore it anytime!

---

## 🔧 Maintenance

### Check Archive Size:
```sql
SELECT COUNT(*) FROM archive_records;
```

### Archives by Type:
```sql
SELECT table_name, COUNT(*) as count 
FROM archive_records 
GROUP BY table_name;
```

### Cleanup Old Archives (Optional):
```sql
-- Delete archives older than 6 months
DELETE FROM archive_records 
WHERE deleted_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
```

---

## ✅ Summary

**The Archive History system is 100% ready to use!**

- ✅ Database table exists
- ✅ All modules integrated
- ✅ API working
- ✅ UI complete
- ✅ Sidebar link active
- ✅ Restore functionality ready

**Just access:** `https://repairservice.onrender.com/views/archive_history.php`

**Or locally:** `http://localhost/RepairSystem-main/views/archive_history.php`

All deleted records are automatically saved and can be restored at any time! 🎉
