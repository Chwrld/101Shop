# Service Progress Comments - System Architecture & Flow

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    SERVICE REPORT PAGE                          │
│        (staff/staff_service_report_new.php)                    │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Repair Progress Timeline                                │  │
│  │  ├─ Pending       [Comment] [Pending comments]          │  │
│  │  ├─ Under Repair  [Comment] [Under Repair comments]     │  │
│  │  ├─ Completed     [Comment] [Completed comments]        │  │
│  │  └─ Report Info   [Comment] [Report comments]           │  │
│  └──────────────────────────────────────────────────────────┘  │
│                           ↓                                      │
│              JavaScript AJAX Requests                            │
│                           ↓                                      │
└───────────────────────────┼──────────────────────────────────────┘
                            │
                ┌───────────┼───────────┐
                ↓           ↓           ↓
            ┌────────────────────────────────────┐
            │ /backend/api/service_report_api.php│
            │                                    │
            │ Actions:                           │
            │ • addProgressComment               │
            │ • getProgressComments              │
            │ • deleteProgressComment            │
            └────────────────────────────────────┘
                            │
                            ↓
            ┌────────────────────────────────────┐
            │     Database Connection             │
            │  (backend/handlers/Database.php)   │
            └────────────────────────────────────┘
                            │
                            ↓
            ┌────────────────────────────────────┐
            │   service_progress_comments Table   │
            │                                    │
            │  id | report_id | progress_key    │
            │  comment_text | created_by        │
            │  created_by_name | timestamps     │
            └────────────────────────────────────┘
```

---

## 🔄 Data Flow Diagram

### Adding a Comment
```
User Action
    ↓
User clicks [Comment] button
    ↓
Modal dialog opens
    ↓
User enters comment text
    ↓
Click [Save]
    ↓
JavaScript validates input
    ↓
AJAX POST Request
    ↓
API receives request
    ↓
Validate data
    ↓
Create table if not exists
    ↓
Insert into database
    ↓
Return success response
    ↓
JavaScript receives response
    ↓
Close modal
    ↓
Reload comments display
    ↓
Comment appears on page ✅
```

### Retrieving Comments
```
Page Load Event
    ↓
Call loadProgressComments(reportId)
    ↓
AJAX GET Request to API
    ↓
API receives report_id
    ↓
Query database for comments
    ↓
Sort by created_at ASC
    ↓
Return comments array
    ↓
JavaScript receives response
    ↓
Organize by progress_key
    ↓
Call displayAllProgressComments()
    ↓
For each progress stage:
    Display comments
    ↓
Comments visible on page ✅
```

### Deleting a Comment
```
User clicks [Delete] icon
    ↓
Confirmation dialog
    ↓
User confirms delete
    ↓
AJAX GET with comment id
    ↓
API receives comment id
    ↓
Delete from database
    ↓
Return success
    ↓
Reload comments
    ↓
Comment removed from page ✅
```

---

## 📊 Database Schema Visualization

```
service_reports (Existing Table)
┌────────────────┐
│ report_id (PK) │◄────┐
│ customer_name  │     │
│ appliance_name │     │ FK
│ status         │     │
│ ... other cols │     │
└────────────────┘     │
                       │
                       │
service_progress_comments (New Table)
┌──────────────────────────┐
│ id (PK)                  │
│ report_id (FK)───────────┤
│ progress_key             │
│ comment_text             │
│ created_by               │
│ created_by_name          │
│ created_at               │
│ updated_at               │
└──────────────────────────┘

CASCADE: When report deleted → comments deleted
INDEX: Fast queries by report_id, progress_key, created_at
```

---

## 🔐 Security Flow

```
User Input
    ↓
Received by JavaScript
    ↓
Trimmed and validated
    ↓
Sent via AJAX to API
    ↓
PHP receives input
    ↓
Session checked ✓
    ↓
Input validated (not empty) ✓
    ↓
Prepared Statement created ✓
    ├─ Prevents SQL Injection
    ├─ Parameters: ?, ?, ?, ?, ?
    └─ Types: i, s, s, i, s
    ↓
Data inserted safely ✓
    ↓
Response returned
    ↓
JavaScript escapeHtml() ✓
    ├─ Prevents XSS
    └─ Creates safe HTML
    ↓
Displayed on page safely ✓
```

---

## 📝 API Endpoint Details

### 1. Add Comment
```
Request:
  POST /backend/api/service_report_api.php
  Content-Type: application/json
  
  {
    "action": "addProgressComment",
    "report_id": 6,
    "progress_key": "under_repair",
    "comment_text": "Started repair work"
  }

Processing:
  1. Validate all fields present
  2. Check if table exists (create if not)
  3. Prepare INSERT statement with types: i,s,s,i,s
  4. Bind parameters safely
  5. Execute insert
  6. Return inserted ID

Response:
  {
    "success": true,
    "data": { "id": 42 },
    "message": "Comment added successfully"
  }
```

### 2. Get Comments
```
Request:
  GET /backend/api/service_report_api.php?action=getProgressComments&report_id=6

Processing:
  1. Get report_id from query parameter
  2. Validate report_id is present
  3. Check if table exists
  4. If not exists: return empty array
  5. SELECT * WHERE report_id = ?
  6. ORDER BY created_at ASC
  7. Format results

Response:
  {
    "success": true,
    "data": [
      {
        "id": 42,
        "report_id": 6,
        "progress_key": "under_repair",
        "comment_text": "Started repair work",
        "created_by": "John Technician",
        "created_at": "2025-12-16 10:30:45"
      },
      ...
    ],
    "message": "Comments retrieved successfully"
  }
```

### 3. Delete Comment
```
Request:
  GET /backend/api/service_report_api.php?action=deleteProgressComment&id=42

Processing:
  1. Get comment id from query parameter
  2. Validate id is present
  3. Check if table exists
  4. DELETE FROM service_progress_comments WHERE id = ?
  5. Return success/error

Response:
  {
    "success": true,
    "data": null,
    "message": "Comment deleted successfully"
  }
```

---

## 🎯 Component Interactions

```
┌────────────────────────────────────────────────────────────────┐
│ Frontend (JavaScript)                                           │
│ ├─ openProgressCommentModal()                                  │
│ ├─ saveProgressComment()                                       │
│ ├─ loadProgressComments()                                      │
│ ├─ displayAllProgressComments()                                │
│ ├─ displayProgressItemComments()                               │
│ ├─ deleteProgressComment()                                     │
│ └─ escapeHtml()  (XSS protection)                             │
└────────────────────────────────────────────────────────────────┘
                            ↕ AJAX
┌────────────────────────────────────────────────────────────────┐
│ Backend API (PHP)                                               │
│ ├─ handleAddProgressComment()                                  │
│ ├─ handleGetProgressComments()                                 │
│ ├─ handleDeleteProgressComment()                               │
│ └─ sendResponse()  (JSON formatted)                           │
└────────────────────────────────────────────────────────────────┘
                            ↕ SQL
┌────────────────────────────────────────────────────────────────┐
│ Database (MySQL)                                                │
│ └─ service_progress_comments table                             │
│    ├─ CREATE TABLE IF NOT EXISTS                               │
│    ├─ INSERT INTO                                              │
│    ├─ SELECT * WHERE                                           │
│    └─ DELETE FROM                                              │
└────────────────────────────────────────────────────────────────┘
```

---

## 🔍 Progress Keys Mapping

```
Progress Stages         Progress Key           Timeline Position
─────────────────────────────────────────────────────────────
Pending                 pending                1st position
Under Repair            under_repair           2nd position
Completed               completed              3rd position
Unrepairable            unrepairable           Alternative 2nd
Release Out             release_out            Alternative 3rd
Report Created          report_created         Below timeline
```

---

## 📊 Database Query Examples

### Get all comments for a report
```sql
SELECT * FROM service_progress_comments
WHERE report_id = 6
ORDER BY created_at DESC;
```

### Get comments by progress stage
```sql
SELECT * FROM service_progress_comments
WHERE report_id = 6 AND progress_key = 'under_repair'
ORDER BY created_at DESC;
```

### Get comments by specific staff
```sql
SELECT * FROM service_progress_comments
WHERE created_by = 16
ORDER BY created_at DESC;
```

### Get recent comments (last 24 hours)
```sql
SELECT * FROM service_progress_comments
WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY created_at DESC;
```

### Count comments by stage
```sql
SELECT progress_key, COUNT(*) as count
FROM service_progress_comments
WHERE report_id = 6
GROUP BY progress_key;
```

---

## 🚀 Performance Characteristics

| Operation | Time Complexity | Index Used |
|-----------|-----------------|-----------|
| Add comment | O(1) | Auto-increment PK |
| Get all comments | O(n) | idx_report_id |
| Get by progress | O(n) | idx_report_progress |
| Get by staff | O(n) | idx_created_by |
| Get recent | O(n) | idx_created_at |
| Delete comment | O(1) | Primary Key |

---

## ✅ Verification Checklist

When everything is working properly, you should see:

```
☑ Comments save successfully
☑ Comments appear immediately after save
☑ Comments persist after page refresh
☑ Comments appear under correct progress stage
☑ Author name displays correctly
☑ Timestamp shows correctly
☑ Delete button works
☑ Can add multiple comments per stage
☑ Can add comments to multiple stages
☑ Admin can see all comments
☑ Staff can see all comments
☑ Comments are ordered by creation time
☑ Database table has all indexes
☑ Foreign key constraint active
```

---

## 📝 Testing Checklist

```
Integration Testing:
  ☑ Add comment → Save → Page refresh → Comment persists
  ☑ Add multiple comments → All appear
  ☑ Switch stages → Correct comments show
  ☑ Delete comment → Actually removed

Database Testing:
  ☑ Table exists: SHOW TABLES LIKE 'service_progress_comments'
  ☑ Structure correct: DESCRIBE service_progress_comments
  ☑ Data present: SELECT COUNT(*) FROM service_progress_comments
  ☑ FK constraint: SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE

Performance Testing:
  ☑ Queries complete in < 100ms
  ☑ Adding comment responds instantly
  ☑ Page load with comments is smooth

Security Testing:
  ☑ Can't inject SQL: Try comment: "' OR '1'='1"
  ☑ Can't inject XSS: Try comment: "<script>alert('xss')</script>"
  ☑ Session required: Logout and try to add comment
```

---

## 🎓 Architecture Summary

The service progress comments system uses a **3-tier architecture**:

1. **Presentation Tier** (Frontend)
   - HTML form elements
   - JavaScript handling
   - AJAX communication
   - HTML escaping for security

2. **Application Tier** (Backend)
   - PHP API endpoints
   - Input validation
   - Business logic
   - Database operations

3. **Data Tier** (Database)
   - MySQL tables
   - Indexes for performance
   - Foreign key constraints
   - Data persistence

**Result**: Secure, scalable, and maintainable comment storage system! ✨
