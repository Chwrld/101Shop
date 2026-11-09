# Progress Comments Persistence - Complete Guide

## Issue Summary

Comments were appearing to disappear after:
1. Refreshing the page
2. Updating the service report
3. Changing the report status

**Root Cause**: This was NOT a database storage problem. Comments were being saved to the database correctly. The issue was frontend state management:
- After form submission, the `report_id` was being cleared
- When status changed, comments weren't reloaded before display
- The `progressComments` data structure was stale/empty

## How Comments Work (Complete Flow)

### 1. **Storing Comments** (Backend)
```
User clicks "Add Comment" in progress timeline
    ↓
Modal opens, gets report_id from form: $('#report_id').val()
    ↓
User types comment and clicks "Save"
    ↓
JavaScript calls: $.ajax to /backend/api/service_report_api.php?action=addProgressComment
    ↓
API receives: report_id, progress_key, comment_text, session user_id, user name
    ↓
API inserts into service_progress_comments table using prepared statement
    ↓
Comment stored with: id, report_id, progress_key, comment_text, created_by, created_by_name, created_at
```

### 2. **Loading Comments** (Retrieval)
```
When report is loaded/displayed:
    ↓
JavaScript calls: $.ajax to /backend/api/service_report_api.php?action=getProgressComments
    ↓
API retrieves: SELECT * FROM service_progress_comments WHERE report_id = ?
    ↓
Organizes comments by progress_key in progressComments object
    ↓
JavaScript displays comments in correct timeline section
```

### 3. **Problem Scenario - Before Fix**
```
User creates/updates report → submits form
    ↓
Success: Form clears, report_id set to '' ← PROBLEM!
    ↓
User refreshes page or changes status
    ↓
No report_id available to load comments
    ↓
progressComments object stays empty
    ↓
Timeline shows no comments (even though they exist in database!)
```

### 4. **Solution - After Fix**
```
User creates report → submits form
    ↓
Success (NEW): If updating, calls loadReportForEditing(reportId) ← FIX!
    ↓
Report is reloaded from database with all its comments
    ↓
report_id stays in form, progressComments object is populated
    ↓
User can see comments and continue editing
    ↓
Page refresh? Comments are reloaded with the report data
```

## Database Structure

### service_progress_comments Table

```sql
CREATE TABLE service_progress_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    progress_key VARCHAR(50) NOT NULL,
    comment_text TEXT NOT NULL,
    created_by INT,
    created_by_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES service_reports(report_id) ON DELETE CASCADE,
    KEY idx_report_id (report_id),
    KEY idx_progress_key (progress_key)
);
```

**Key Fields**:
- `report_id`: Links comment to specific repair report (FK to service_reports)
- `progress_key`: Stage where comment was added (e.g., 'diagnostics', 'repair', 'testing')
- `comment_text`: The actual comment content
- `created_by`: User ID of comment author
- `created_by_name`: Name of comment author (for display)
- `created_at`: When comment was created (automatically set)

## Fixed Code Sections

### Fix #1: Submit Service Report (lines 2365-2383)

**Before:**
```javascript
success: function(response) {
    showAlert('success', 'Service report created/updated!');
    $('#serviceReportForm')[0].reset();  // ← Clears form including report_id!
    $('#report_id').val('');             // ← Explicitly clears report_id!
    // ... rest of cleanup
}
```

**After:**
```javascript
success: function(response) {
    const reportId = $('#report_id').val();
    const successMsg = reportId ? 'Service report updated successfully!' : 'Service report created successfully!';
    
    if (reportId) {
        // If UPDATING, reload the report instead of clearing it
        // This keeps report_id and reloads all comments from database
        loadReportForEditing(reportId);
    } else {
        // If CREATING new report, clear form normally
        $('#serviceReportForm')[0].reset();
        $('#report_id').val('');
        // ... cleanup for new report
    }
}
```

**Why This Works**: 
- After updating, `loadReportForEditing()` is called
- This function: fetches report data + calls `loadProgressComments(reportId)`
- Result: Report stays displayed with fresh comments from database

### Fix #2: Update Status Progress (lines 1494-1541)

**Before:**
```javascript
function updateStatusProgress(status) {
    // ... validation ...
    updateProgressTimeline(status);  // ← Updates timeline WITHOUT loading comments!
}
```

**After:**
```javascript
function updateStatusProgress(status) {
    // ... validation ...
    
    // Load comments FIRST before updating timeline display
    const reportId = $('#report_id').val();
    if (reportId) {
        $.ajax({
            url: '../backend/api/service_report_api.php?action=getProgressComments&report_id=' + reportId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    // Populate progressComments object with fresh data
                    progressComments = {};
                    response.data.forEach(function(comment) {
                        if (!progressComments[comment.progress_key]) {
                            progressComments[comment.progress_key] = [];
                        }
                        progressComments[comment.progress_key].push(comment);
                    });
                }
                // NOW update timeline with populated comments
                updateProgressTimeline(status);
            }
        });
    } else {
        updateProgressTimeline(status);
    }
}
```

**Why This Works**:
- Before updating the timeline, comments are loaded from the database
- The `progressComments` global object is populated with real data
- When `updateProgressTimeline()` displays comments, they're there!

## Testing Procedure

### Test 1: Comment Creation & Persistence

**Steps**:
1. Go to staff service report form
2. Create a new repair report (enter customer, appliance, initial diagnosis)
3. Click "Create Report"
4. In the timeline, click "Add Comment" in the Diagnostics section
5. Type: "Initial inspection complete"
6. Click "Save Comment"
7. **Observe**: Comment appears in timeline
8. **Refresh the page** (F5)
9. **Expected Result**: Select the same report from list - comment is still there ✓

**What To Check**:
- Comment appears immediately after saving (before refresh)
- Comment still appears after page refresh
- Comment author name is shown correctly
- Comment timestamp is accurate

### Test 2: Update & Comment Persistence

**Steps**:
1. Open an existing repair report in edit mode
2. Add a comment: "Testing the repair"
3. Click "Save Comment"
4. **Observe**: Comment appears
5. Change the report status (e.g., from "In Progress" to "Completed")
6. **Observe**: Timeline updates and status changes
7. **Observe**: Comments are still visible ✓
8. Update another field (e.g., add notes)
9. Click "Update Report"
10. **Observe**: Report reloads with comments still displayed ✓
11. Refresh the page
12. **Observe**: Select report again - comments still there ✓

**What To Check**:
- Comments visible after status change
- Comments persist after report update
- Comments survive page refresh
- Form maintains report context after update

### Test 3: Multi-User Comment Visibility

**Setup**: 
- Have admin and staff logged in on two browsers

**Steps**:
1. **Admin**: Open a repair report, add comment in Diagnostics: "Check for water damage"
2. **Admin**: Save comment
3. **Staff**: Refresh their page (if same report open) OR select report from list
4. **Staff**: Observe that admin's comment is visible ✓
5. **Staff**: Add their own comment: "No water damage found"
6. **Staff**: Save comment
7. **Admin**: Refresh OR re-select report
8. **Admin**: Observe that staff's comment is visible ✓

**What To Check**:
- Comments authored by one user visible to others
- Author names display correctly
- Timestamps are accurate across users
- Comments attributed to correct user

### Test 4: Multiple Comments in Same Section

**Steps**:
1. Open a repair report
2. Add multiple comments to the same progress stage:
   - Comment 1: "Starting inspection"
   - Comment 2: "Found the issue"
   - Comment 3: "Repair begun"
3. All three should display in chronological order ✓
4. Refresh page
5. Comments still there in correct order ✓
6. Expand a different progress section and add comment there
7. Navigate back to first section
8. Comments from first section still there ✓

**What To Check**:
- Multiple comments in same section display
- Comments in chronological order
- Comments persist when navigating between sections
- No comment loss

### Test 5: Delete Comment

**Steps**:
1. Add a comment to a report
2. Hover over comment (delete button should appear)
3. Click delete button
4. Comment disappears immediately ✓
5. Refresh page
6. Comment doesn't come back ✓

**What To Check**:
- Delete works immediately
- Delete persists after refresh
- No accidental comment recreation

## Database Verification

### Check if Comments Table Exists

```php
<?php
// Run this in PHP to verify table exists
include '../config/app.php';
include '../backend/handlers/Database.php';

$db = new Database();
$conn = $db->connect();

$result = $conn->query("SHOW TABLES LIKE 'service_progress_comments'");
if ($result && $result->num_rows > 0) {
    echo "✓ Table exists";
} else {
    echo "✗ Table missing - creating...";
    // Setup script will create it
}
?>
```

### Check Comments in Database

```sql
-- View all comments for a specific report
SELECT * FROM service_progress_comments WHERE report_id = 5;

-- View comments by author
SELECT created_by_name, COUNT(*) as comment_count 
FROM service_progress_comments 
GROUP BY created_by_name;

-- View comments by progress stage
SELECT progress_key, COUNT(*) as stage_count 
FROM service_progress_comments 
GROUP BY progress_key;

-- View most recent comments
SELECT report_id, progress_key, comment_text, created_by_name, created_at
FROM service_progress_comments
ORDER BY created_at DESC
LIMIT 10;
```

## Common Issues & Solutions

### Issue: "Comments disappeared after I refresh"

**Check**:
1. Open DevTools (F12) → Console tab
2. Add a comment
3. Look for any red error messages
4. Check that `loadProgressComments()` is being called
5. Verify report_id is not empty: `$('#report_id').val()` should show a number

**Solution**:
- Make sure you're opening the report via the service report list
- The form needs to load the report first with `loadReportForEditing()`
- Simply refreshing without loading a report won't show comments

### Issue: "Comments show for me but not for other staff"

**Check**:
1. Verify comment author name is correct in database
2. Check that session user info is passed to API
3. Ensure both users are seeing the same report_id

**Solution**:
- Comments are stored globally in database - all users should see them
- If not visible, check browser console for AJAX errors
- Verify API is returning comments: look at Network tab in DevTools

### Issue: "Delete button doesn't appear"

**Check**:
1. Make sure JavaScript is enabled
2. Hover over comment text (button appears on hover)
3. Check browser console for JavaScript errors

**Solution**:
- Delete button is hidden by default, appears on hover
- If it doesn't appear, check console for JS errors
- Verify jQuery is loaded properly

## API Reference

### Add Comment
```
POST /backend/api/service_report_api.php?action=addProgressComment

Parameters:
- report_id: int (required)
- progress_key: string (required) - 'diagnostics', 'repair', 'testing', etc.
- comment_text: string (required)
- User info from SESSION automatically

Response:
{
    "success": true,
    "data": {
        "id": 123
    }
}
```

### Get Comments
```
GET /backend/api/service_report_api.php?action=getProgressComments&report_id=5

Parameters:
- report_id: int (required)

Response:
{
    "success": true,
    "data": [
        {
            "id": 1,
            "report_id": 5,
            "progress_key": "diagnostics",
            "comment_text": "Initial inspection complete",
            "created_by": 3,
            "created_by_name": "John Admin",
            "created_at": "2024-01-15 10:30:00"
        }
    ]
}
```

### Delete Comment
```
DELETE /backend/api/service_report_api.php?action=deleteProgressComment&comment_id=1

Parameters:
- comment_id: int (required)

Response:
{
    "success": true
}
```

## Key Implementation Details

### Session Handling
```php
// API starts session to access user info
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// User info is available
$created_by = $_SESSION['user_id'];
$created_by_name = $_SESSION['name'];
```

### Prepared Statements
```php
// Comments are inserted safely using prepared statements
$stmt = $conn->prepare("INSERT INTO service_progress_comments 
    (report_id, progress_key, comment_text, created_by, created_by_name) 
    VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('issis', $report_id, $progress_key, $comment_text, $created_by, $created_by_name);
```

### Data Integrity
```sql
-- Foreign key ensures report can't be deleted while comments exist
FOREIGN KEY (report_id) REFERENCES service_reports(report_id) ON DELETE CASCADE

-- When report is deleted, all its comments are automatically deleted
```

## Summary

The comments feature now:
- ✅ **Stores** comments in database with full integrity
- ✅ **Retrieves** comments when reports are loaded/updated
- ✅ **Displays** comments in proper progress timeline sections
- ✅ **Persists** through page refreshes (if report is reloaded)
- ✅ **Shares** across users (all users see same comments)
- ✅ **Tracks** author and timestamp for each comment
- ✅ **Allows** deletion of individual comments

The two critical fixes ensure:
1. Report context (report_id) stays available after updates
2. Comment data is always fresh when timeline is displayed

Test using the procedures above to verify all functionality works correctly!
