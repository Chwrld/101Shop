# Staff Service Report - Customer Loading Troubleshooting Guide

## Problem
Customer list is not showing in the staff service report form.

## How to Diagnose

### Step 1: Open the Page and Developer Console
1. Navigate to `staff/staff_service_report.php` in your browser
2. Press **F12** to open Developer Console
3. Go to the **Console** tab

### Step 2: Check Console Logs
Look for debug messages like:
```
[DEBUG] Starting loadInitialData
[DEBUG] API URLs: {CUSTOMER_APPLIANCE_API_URL: "../../backend/api/customer_appliance_api.php", PARTS_API_URL: "../../backend/api/parts_api.php"}
[DEBUG] API Response for type=customer: {success: true, data: {...}, message: "Customers loaded"}
[DEBUG] Customers loaded. window.customersList: Array(5) [...]
```

### Step 3: Manual Tests in Console

**Test 1: Check if customers list is loaded**
```javascript
console.log(window.customersList);
// Should show an array like: [{id: 34, name: "Krystuff Sam"}, ...]
```

**Test 2: Check API endpoint**
```javascript
fetch('../backend/api/customer_appliance_api.php?action=getAllCustomers&page=1&itemsPerPage=5')
    .then(r => r.json())
    .then(d => console.log(d));
// Should show JSON response with customers data
```

**Test 3: Simulate renderCustomerSuggestions**
```javascript
renderCustomerSuggestions('');
// Should display customer suggestions in the dropdown
```

### Step 4: Check Network Tab
1. Open **Network** tab in Developer Tools
2. Reload the page
3. Look for calls to `customer_appliance_api.php`
4. Check the response:
   - Should return HTTP 200 status
   - Response should contain customers array
   - Look at the Response tab to see the JSON data

## Common Issues and Solutions

### Issue 1: Console shows no debug logs
**Possible Cause:** Page hasn't fully loaded yet  
**Solution:**  
1. Reload the page (F5)
2. Check console immediately
3. Look for "Starting loadInitialData" message

### Issue 2: `window.customersList is undefined`
**Possible Cause:** API call failed or returned no data  
**Solution:**
1. Check Network tab - is `customer_appliance_api.php` being called?
2. If YES but error: Check the Response for error message
3. If NO: API is not being called at all
   - Verify `loadInitialData()` is being called
   - Check for JavaScript errors in console

### Issue 3: API returns 404 error
**Possible Cause:** Wrong file path or missing authentication  
**Solution:**
1. Verify the URL in Network tab matches actual file location
2. Check if you're logged in as staff
3. Verify `backend/api/customer_appliance_api.php` file exists
4. Check file permissions

### Issue 4: API returns 403 or authentication error
**Possible Cause:** Not logged in or wrong user role  
**Solution:**
1. Log out and log back in
2. Make sure you're logged in as a staff user
3. Check that auth.php session is valid

### Issue 5: Suggestions not showing even though customers are loaded
**Possible Cause:** JavaScript event handlers not working  
**Solution:**
1. Click on customer search input to trigger suggestions
2. Type a customer name to filter
3. Check if renderCustomerSuggestions is being called:
   ```javascript
   // Add this in console:
   renderCustomerSuggestions('');
   // Should show suggestions immediately
   ```

## Quick Test Script

Open browser console and run:

```javascript
// Test 1: Check customers loaded
console.log('Customers loaded:', window.customersList ? window.customersList.length : 'NOT LOADED');

// Test 2: Check API directly
fetch('../backend/api/customer_appliance_api.php?action=getAllCustomers&page=1&itemsPerPage=5')
    .then(r => r.json())
    .then(d => {
        console.log('API Response:', d);
        console.log('Success:', d.success);
        console.log('Customer count:', d.data?.customers?.length || 0);
        if (d.data?.customers?.length > 0) {
            console.log('First customer:', d.data.customers[0]);
        }
    })
    .catch(e => console.error('API Error:', e));

// Test 3: Check if render function works
setTimeout(() => {
    console.log('Calling renderCustomerSuggestions...');
    renderCustomerSuggestions('');
    console.log('Check if suggestions appeared below customer input');
}, 500);
```

## Expected Behavior

1. **Page Loads**: Console shows "[DEBUG] Starting loadInitialData"
2. **API Calls**: Network tab shows successful calls to `customer_appliance_api.php`
3. **Data Loaded**: Console shows "[DEBUG] Customers loaded" with array of customers
4. **Search Activates**: Clicking customer search input shows suggestion list
5. **Filtering Works**: Typing a name filters the suggestions
6. **Selection Works**: Clicking a suggestion fills in the field and sets hidden customer-id

## If Everything Fails

1. **Clear Browser Cache**: Ctrl+Shift+Delete (or Cmd+Shift+Delete on Mac)
2. **Hard Reload Page**: Ctrl+Shift+R (or Cmd+Shift+R on Mac)
3. **Check File Syntax**: Run `php -l staff/staff_service_report.php`
4. **Test Admin Page**: Open `views/service_report.php` to see if it works there
5. **Check PHP Errors**: Look at `error_log` in XAMPP

## Files to Verify

- `staff/staff_service_report.php` - Main form page
- `backend/api/customer_appliance_api.php` - API endpoint
- `backend/handlers/customersHandler.php` - Customer data handler
- `database/repairsystem.sql` - Database schema
- `authentication/auth.php` - Authentication check

## Debug Output Location

When debugging with console logs, the output appears in:
1. Browser Console (F12 → Console tab) - Shows all [DEBUG] logs
2. Network tab (F12 → Network tab) - Shows API calls and responses
3. Application tab (F12 → Application tab) - Check sessionStorage/localStorage if needed

## Support

If you still can't see customers after these steps:
1. Note all error messages from console
2. Screenshot of Network tab showing API calls
3. Run: `php database/migrations/add_customer_appliance_ids.php`
4. Check: `SELECT COUNT(*) FROM customers;` in database
