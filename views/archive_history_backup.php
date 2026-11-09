<?php
require_once __DIR__ . '/../bootstrap.php';
session_start();
require __DIR__ . '/../backend/handlers/authHandler.php';
$auth = new AuthHandler();
$userSession = $auth->requireAuth('both'); 
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Archive History</title>
    <link rel="shortcut Icon" href="../img/Repair.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --primary: #6c6e6cff;
            --secondary: #2d3748;
            --highlight: #e53e3e;
            --success: #28a745;
            --warning: #dd6b20;
            --info: #17a2b8;
            --light-bg: #f4f6f9;
            --card-bg: #ffffff;
            --text: #333;
            --text-light: #666;
            --border: #545455ff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text);
            line-height: 1.6;
            font-weight: 400;
        }
        
        .wrapper {
            display: flex;
        }
        
        /* Main Content */
        #content {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        
        /* Header */
        .header {
            background: var(--primary);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .content-area {
            padding: 20px;
            flex: 1;
            overflow-y: auto;
        }
        
        .page-title {
            margin-bottom: 20px;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Card Styles */
        .card {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        /* Updated Search Box Styles */
        .search-box {
            position: relative;
            min-width: 250px;
        }
        
        .search-box .form-control {
            height: 38px;
            padding: 8px 12px 8px 35px;
            font-size: 14px;
            border: 1px solid var(--border);
            border-radius: 6px;
            transition: border-color 0.3s;
        }
        
        .search-box .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 18px;
            pointer-events: none;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
            margin: 0;
        }
        
        /* Button Styles */
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .btn:hover {
            color: white;
            background: green;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-info {
            background: var(--info);
            color: white;
        }
        
        .btn-warning {
            background: var(--warning);
            color: white;
        }
        
        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .table th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: var(--text);
            border-bottom: 2px solid #dee2e6;
        }
        
        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.02);
        }
        
        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .bg-success {
            color: green;
        }
        
        .bg-warning {
            color: blue;
        }
        
        .bg-info {
            color: orange;
        }
        
        /* Action Buttons */
        .btn-action {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.55rem;
            transition: all 0.3s;
            margin-right: 5px;
        }
        
        .btn-view {
            color: gray;
        }
        
        .btn-complete {
            color: green;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* archive styles*/
        .archive-table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            white-space: nowrap;
        }

        .archive-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .archive-table .id-column {
            width: 90px;
        }

        .archive-table .type-column {
            width: 130px;
        }

        .archive-table .record-column {
            width: 120px;
        }

        .archive-table .date-column {
            width: 140px;
            white-space: nowrap;
        }

        .archive-table .reason-column {
            min-width: 240px;
            white-space: normal;
        }

        .archive-table .actions-column {
            width: 180px;
        }

        .description-content {
            line-height: 1.4;
        }

        .description-content .main-text {
            font-size: 1rem;
            font-weight: 200;
            margin-bottom: 4px;
            color: var(--text);
        }

        .description-content .sub-text {
            font-size: 0.85rem;
            color: #6c757d;
            line-height: 1.3;
        }

        .activity-log-content {
            flex-grow: 1;
        }

        .activity-log-message {
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text);
        }

        .activity-log-details {
            font-size: 0.85rem;
            color: #6c757d;
            line-height: 1.4;
        }

        .activity-log-time {
            font-size: 0.8rem;
            color: #adb5bd;
            white-space: nowrap;
            margin-left: 15px;
        }

        .activity-log-empty {
            padding: 40px 20px;
            text-align: center;
            color: #6c757d;
        }

        .activity-log-empty .material-icons {
            font-size: 48px;
            margin-bottom: 15px;
            color: #dee2e6;
        }

        .activity-log-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .activity-log-filter .btn {
            font-size: 0.8rem;
            padding: 5px 10px;
        }

        /*  type Badges */
        .badge-transaction {
            color: #007bff;
        }
        
        .badge-customer {
            color: #28a745;
        }
        
        .badge-parts {
            color: #ffc107;
        }
        
        /* pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
            border-radius: 0 0 14px 14px;
        }
        
        .pagination-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .pagination-info .font-weight-bold {
            font-weight: 600;
            color: var(--text);
        }
        
        .pagination {
            margin: 0;
        }
        
        .pagination .page-link {
            color: #242424ff;
            border-color: #dee2e6;
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 4px;
        }

        .pagination .page-item.active .page-link {
            background-color: #242424ff;
            border-color: #242424ff;
            color: #fff;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #f8f9fa;
        }

        /* Activity Log Styles */
        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 16px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .activity-icon.success {
            background-color: #d4edda;
            color: #155724;
        }

        .activity-icon.warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .activity-icon.danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .activity-icon.info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .activity-icon.primary {
            background-color: #cce5ff;
            color: #004085;
        }

        .activity-icon.secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .activity-action {
            font-weight: 600;
            color: var(--text);
            font-size: 0.95rem;
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .activity-details {
            display: flex;
            gap: 12px;
            margin-bottom: 6px;
        }

        .activity-table {
            font-size: 0.85rem;
            color: var(--text-light);
            background-color: #f8f9fa;
            padding: 2px 8px;
            border-radius: 12px;
            text-transform: capitalize;
        }

        .activity-user {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .activity-message {
            font-size: 0.9rem;
            color: var(--text);
            line-height: 1.4;
        }
        
        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
            color: #242424ff;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #content {
                margin-left: 0;
                width: 100%;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-box {
                min-width: 200px;
                flex: 1;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab {
                border-bottom: 1px solid var(--border);
                border-left: 3px solid transparent;
            }
            
            .tab.active {
                border-left-color: var(--primary);
            }
            
            .pagination-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
        
        @media (max-width: 576px) {
            .header-actions {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            .search-box .form-control {
                width: 100%;
            }
            
            .search-icon {
                left: 12px;
            }
            
            .activity-log-filter {
                flex-direction: column;
            }
            
            .pagination .page-link {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper ml-2 mr-2">
        <div class="body-overlay"></div>

        <!-- Sidebar -->
        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <div id="content">
            <!-- Top Navbar -->
            <?php
            $pageTitle = ' History';
            $breadcrumb = 'Archive';
            include __DIR__ . '/../layout/navbar.php';
            ?>

            <div class="content-area">
                <!-- tabs -->
                <div class="tabs">
                    <div class="tab active" data-tab="activity">Activity Log</div>
                    <div class="tab" data-tab="archive">Archived Records</div>
                </div>
                
                <!-- activity log tab -->
                <div class="tab-content active" id="activityTab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Activity Log</h5>
                            <div class="header-actions">
                                <div class="search-box">
                                    <span class="material-icons search-icon">search</span>
                                    <input type="text" class="form-control" id="searchActivity" placeholder="Search activities...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="activity-log-filter">
                                <button class="btn btn-outline-primary active" data-filter="all">All Activities</button>
                                <button class="btn btn-outline-primary" data-filter="transaction">Transactions</button>
                                <button class="btn btn-outline-primary" data-filter="customer">Customers</button>
                                <button class="btn btn-outline-primary" data-filter="parts">Parts</button>
                            </div>
                            <div class="activity-log-list" id="activityLogList">
                                <!-- activity logs will be populated by JavaScript -->
                            </div>
                            
                            <!-- Activity Log Pagination -->
                            <div class="pagination-container" id="activityPaginationContainer">
                                <div class="pagination-info" id="activityPaginationInfo">
                                    Showing <span class="font-weight-bold">0</span> to <span class="font-weight-bold">0</span> of <span class="font-weight-bold">0</span> entries
                                </div>
                                <nav aria-label="Activity log navigation">
                                    <ul class="pagination pagination-sm mb-0" id="activityPagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- archive records tab -->
                <div class="tab-content" id="archiveTab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Archived Records</h5>
                            <div class="header-actions">
                                <div class="search-box">
                                    <span class="material-icons search-icon">search</span>
                                    <input type="text" class="form-control" id="searchArchive" placeholder="Search archived records...">
                                </div>
                                <button id="refreshArchiveBtn" class="btn btn-sm btn-info" title="Refresh" style="margin-left: 10px;">
                                    <span class="material-icons">refresh</span>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover archive-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="id-column">Archive ID</th>
                                            <th class="type-column">Type</th>
                                            <th class="record-column">Record ID</th>
                                            <th class="date-column">Deleted At</th>
                                            <th class="reason-column">Reason</th>
                                            <th class="actions-column">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="archiveTableBody">
                                    </tbody>
                                </table>
                                
                                <!-- archive Records Pagination -->
                                <div class="pagination-container" id="archivePaginationContainer">
                                    <div class="pagination-info" id="archivePaginationInfo">
                                        Showing <span class="font-weight-bold">0</span> to <span class="font-weight-bold">0</span> of <span class="font-weight-bold">0</span> entries
                                    </div>
                                    <nav aria-label="Archive navigation">
                                        <ul class="pagination pagination-sm mb-0" id="archivePagination">
                                            <!-- Pagination will be generated by JavaScript -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Details Modal -->
    <div class="modal fade" id="archiveDetailsModal" tabindex="-1" aria-labelledby="archiveDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archiveDetailsModalLabel">Archived Record Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="archiveDetailsBody">
                    <!-- Details inserted by JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/jquery-3.3.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>

    <script type="text/javascript">
        // API Base URL
        const API_BASE_URL = '../backend/api/archive_history_api.php';
        
        $(document).ready(function() {
            // Variables
            let activityLogData = [];
            let archiveData = [];
            let currentActivityPage = 1;
            let currentArchivePage = 1;
            let itemsPerPage = 10;

            // Initialize the page
            initializeActivityLog();
            initializeArchiveTable();

            // Sidebar 
            $(".xp-menubar").on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            $(".xp-menubar,.body-overlay").on('click', function() {
                $('#sidebar,.body-overlay').toggleClass('show-nav');
            });

            // Tab
            $('.tab').on('click', function() {
                const tabId = $(this).data('tab');
                
                // Update active tab
                $('.tab').removeClass('active');
                $(this).addClass('active');
                
                // Show corresponding content
                $('.tab-content').removeClass('active');
                $(`#${tabId}Tab`).addClass('active');
                
                // Load data when switching tabs
                if (tabId === 'archive') {
                    $('#searchArchive').val('');
                    loadArchivedRecords(1);
                } else if (tabId === 'activity') {
                    loadActivityLog(currentActivityPage);
                }
            });

            // Activity Log Filtering
            $('.activity-log-filter .btn').on('click', function() {
                const filter = $(this).data('filter');
                
                // Update active button
                $('.activity-log-filter .btn').removeClass('active');
                $(this).addClass('active');
                
                // Reload data with new filter
                currentActivityPage = 1;
                loadActivityLog(1);
            });

            // Search functionality
            $('#searchArchive').on('input', function() {
                currentArchivePage = 1;
                loadArchivedRecords(1);
            });

            // Manual refresh button
            $('#refreshArchiveBtn').on('click', function() {
                currentArchivePage = 1;
                loadArchivedRecords(1);
            });

            // Load archives when archive tab is clicked (clears search to make sure we get full list)
            $('.tab[data-tab="archive"]').on('click', function() {
                currentArchivePage = 1;
                $('#searchArchive').val('');
                loadArchivedRecords(1);
            });

            $('#searchActivity').on('input', function() {
                currentActivityPage = 1;
                loadActivityLog(1);
            });

            // Restore functionality
            $(document).on('click', '.restore-btn', function() {
                const archiveId = $(this).data('id');
                if (confirm('Are you sure you want to restore this record?')) {
                    restoreRecord(archiveId);
                }
            });

            // View archived details
            $(document).on('click', '.view-archive-btn', function() {
                const id = $(this).data('id');
                const archive = archiveData.find(a => a.id == id);
                if (!archive) {
                    alert('Archived record not found');
                    return;
                }
                renderArchiveDetails(archive);
                $('#archiveDetailsModal').modal('show');
            });

            // Pagination event handlers
            $(document).on('click', '.activity-page-link', function(e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'));
                if (page !== currentActivityPage) {
                    currentActivityPage = page;
                    loadActivityLog(page);
                }
            });

            $(document).on('click', '.archive-page-link', function(e) {
                e.preventDefault();
                const page = parseInt($(this).data('page'));
                if (page !== currentArchivePage) {
                    currentArchivePage = page;
                    loadArchivedRecords(page);
                }
            });

            // Restore record function with loading states and better feedback
            function restoreRecord(archiveId) {
                // Find the button that triggered this
                const $button = $(`.restore-btn[data-id="${archiveId}"]`);
                const originalHtml = $button.html();
                
                // Disable button and show loading state
                $button.prop('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                    <span>Restoring...</span>
                `);
                
                $.ajax({
                    url: API_BASE_URL + '?action=restoreRecord&id=' + archiveId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            showAlert('success', 'Record restored successfully!');
                            // Reload the archive table
                            loadArchivedRecords(currentArchivePage);
                        } else {
                            showAlert('error', response.message || 'Failed to restore record');
                            // Re-enable button on error
                            $button.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Restore error:', error, xhr.responseText);
                        showAlert('error', 'Error restoring record: ' + error);
                        // Re-enable button on error
                        $button.prop('disabled', false).html(originalHtml);
                    }
                });
            }
            
            // Helper function to show alerts
            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const iconName = type === 'success' ? 'check_circle' : 'error';
                
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px;">
                        <span class="material-icons" style="vertical-align: middle; margin-right: 8px;">${iconName}</span>
                        ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                
                $('body').append(alertHtml);
                
                // Auto-dismiss after 5 seconds
                setTimeout(function() {
                    $('.alert').fadeOut(300, function() { $(this).remove(); });
                }, 5000);
            }

            // Update pagination functions
            function updateActivityPagination(data) {
                const $pagination = $('#activityPagination');
                const $info = $('#activityPaginationInfo');
                
                $pagination.empty();
                
                if (data.total_pages <= 1) {
                    $pagination.hide();
                } else {
                    $pagination.show();
                    
                    // Previous button
                    if (data.current_page > 1) {
                        $pagination.append(`<li class="page-item"><a class="page-link activity-page-link" href="#" data-page="${data.current_page - 1}">Previous</a></li>`);
                    }
                    
                    // Page numbers
                    for (let i = 1; i <= data.total_pages; i++) {
                        const active = i === data.current_page ? 'active' : '';
                        $pagination.append(`<li class="page-item ${active}"><a class="page-link activity-page-link" href="#" data-page="${i}">${i}</a></li>`);
                    }
                    
                    // Next button
                    if (data.current_page < data.total_pages) {
                        $pagination.append(`<li class="page-item"><a class="page-link activity-page-link" href="#" data-page="${data.current_page + 1}">Next</a></li>`);
                    }
                }
                
                // Update info
                const start = (data.current_page - 1) * data.items_per_page + 1;
                const end = Math.min(data.current_page * data.items_per_page, data.total_items);
                $info.html(`Showing <span class="font-weight-bold">${start}</span> to <span class="font-weight-bold">${end}</span> of <span class="font-weight-bold">${data.total_items}</span> entries`);
            }

            function updateArchivePagination(data) {
                const $pagination = $('#archivePagination');
                const $info = $('#archivePaginationInfo');
                
                $pagination.empty();
                
                if (data.total_pages <= 1) {
                    $pagination.hide();
                } else {
                    $pagination.show();
                    
                    // Previous button
                    if (data.current_page > 1) {
                        $pagination.append(`<li class="page-item"><a class="page-link archive-page-link" href="#" data-page="${data.current_page - 1}">Previous</a></li>`);
                    }
                    
                    // Page numbers
                    for (let i = 1; i <= data.total_pages; i++) {
                        const active = i === data.current_page ? 'active' : '';
                        $pagination.append(`<li class="page-item ${active}"><a class="page-link archive-page-link" href="#" data-page="${i}">${i}</a></li>`);
                    }
                    
                    // Next button
                    if (data.current_page < data.total_pages) {
                        $pagination.append(`<li class="page-item"><a class="page-link archive-page-link" href="#" data-page="${data.current_page + 1}">Next</a></li>`);
                    }
                }
                
                // Update info
                const start = (data.current_page - 1) * data.items_per_page + 1;
                const end = Math.min(data.current_page * data.items_per_page, data.total_items);
                $info.html(`Showing <span class="font-weight-bold">${start}</span> to <span class="font-weight-bold">${end}</span> of <span class="font-weight-bold">${data.total_items}</span> entries`);
            }

            function initializeActivityLog() {
                loadActivityLog(1);
            }

            function initializeArchiveTable() {
                loadArchivedRecords(1);
            }

            // Load activity log from API
            function loadActivityLog(page = 1) {
                const filter = $('.activity-log-filter .btn.active').data('filter') || 'all';
                const search = $('#searchActivity').val() || '';
                
                console.log('Loading activity log, page:', page, 'filter:', filter, 'search:', search);
                
                $.ajax({
                    url: API_BASE_URL + '?action=getActivityLog&page=' + page + '&itemsPerPage=10&filter=' + filter + '&search=' + encodeURIComponent(search),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Activity log API response:', response);
                        if (response && response.success) {
                            activityLogData = response.data.activities;
                            currentActivityPage = response.data.current_page;
                            console.log('Activity log data:', activityLogData);
                            renderActivityLog();
                            updateActivityPagination(response.data);
                } else {
                            console.error('Error loading activity log:', response.message || response.error);
                            $('#activityLogList').html('<div class="text-center py-4">Error loading activity log: ' + (response.message || response.error || 'Unknown error') + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        $('#activityLogList').html('<div class="text-center py-4">Failed to load activity log</div>');
                    }
                });
            }

            // Load archived records from API (robust + debug logs)
            function loadArchivedRecords(page = 1) {
                const search = $('#searchArchive').val() || '';
                const apiUrl = API_BASE_URL + '?action=getArchivedRecords&page=' + page + '&itemsPerPage=10&search=' + encodeURIComponent(search);
                console.log('Fetching archived records from:', apiUrl);

                // show loading with spinner
                $('#archiveTableBody').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2 mb-0">Loading archived records...</p>
                        </td>
                    </tr>
                `);

                $.ajax({
                    url: apiUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Archive API response:', response);
                        if (response && response.success) {
                            const payload = response.data || {};
                            // Try multiple shapes
                            let archives = [];
                            if (Array.isArray(payload.archives)) archives = payload.archives;
                            else if (Array.isArray(payload)) archives = payload;
                            else if (Array.isArray(response.archives)) archives = response.archives;
                            else if (Array.isArray(response.data)) archives = response.data;

                            archiveData = archives;
                            currentArchivePage = payload.current_page || payload.currentPage || 1;
                            renderArchiveTable();
                            updateArchivePagination({
                                current_page: currentArchivePage,
                                total_pages: payload.total_pages || payload.totalPages || 1,
                                total_items: payload.total_items || payload.totalItems || (archives ? archives.length : 0),
                                items_per_page: payload.items_per_page || payload.itemsPerPage || 10
                            });
                        } else {
                            console.error('Error loading archived records:', response?.message || response?.error || 'Unknown');
                            $('#archiveTableBody').html(`
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="material-icons text-warning" style="font-size: 48px;">warning</i>
                                        <p class="mb-0">Error loading archived records: ${response?.message || response?.error || 'Unknown'}</p>
                                    </td>
                                </tr>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error, 'ResponseText:', xhr.responseText);
                        let errorMessage = 'Failed to load archived records';
                        
                        if (xhr.status === 404) {
                            errorMessage = 'Archive API endpoint not found. Check backend/api/archive_api.php';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Server error. Check PHP error logs.';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        $('#archiveTableBody').html(`
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="material-icons text-danger" style="font-size: 48px;">error</i>
                                    <p class="mb-0">${errorMessage}</p>
                                    <small class="text-muted">Status: ${xhr.status} ${status}</small>
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            function renderActivityLog() {
                console.log('Rendering activity log, data:', activityLogData);
                const $container = $('#activityLogList');
                $container.empty();

                if (!activityLogData || activityLogData.length === 0) {
                    console.log('No activity log data found');
                    $container.html('<div class="text-center py-4">No activity logs found</div>');
                    return;
                }

                console.log('Rendering', activityLogData.length, 'activity items');
                activityLogData.forEach(activity => {
                    const activityHtml = createActivityItem(activity);
                    $container.append(activityHtml);
                });
            }

            function createActivityItem(activity) {
                const actionIcon = getActionIcon(activity.action);
                const actionColor = getActionColor(activity.action);
                const timeAgo = getTimeAgo(activity.created_at);
                
                return `
                    <div class="activity-item">
                        <div class="activity-icon ${actionColor}">
                            <span class="material-icons">${actionIcon}</span>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <span class="activity-action">${activity.action}</span>
                                <span class="activity-time">${timeAgo}</span>
                            </div>
                            <div class="activity-details">
                                <span class="activity-table">${activity.table_name}</span>
                                <span class="activity-user">by ${activity.username || 'System'}</span>
                            </div>
                            <div class="activity-message">
                                ${getActivityMessage(activity)}
                            </div>
                        </div>
                    </div>
                `;
            }

            function getActionIcon(action) {
                const icons = {
                    'CREATE': 'add_circle',
                    'UPDATE': 'edit',
                    'DELETE': 'delete',
                    'LOGIN': 'login',
                    'LOGOUT': 'logout'
                };
                return icons[action.toUpperCase()] || 'info';
            }

            function getActionColor(action) {
                const colors = {
                    'CREATE': 'success',
                    'UPDATE': 'warning',
                    'DELETE': 'danger',
                    'LOGIN': 'info',
                    'LOGOUT': 'secondary'
                };
                return colors[action.toUpperCase()] || 'primary';
            }

            function getTimeAgo(timestamp) {
                const now = new Date();
                const time = new Date(timestamp);
                const diff = now - time;
                const minutes = Math.floor(diff / 60000);
                const hours = Math.floor(diff / 3600000);
                const days = Math.floor(diff / 86400000);
                
                if (minutes < 1) return 'Just now';
                if (minutes < 60) return `${minutes}m ago`;
                if (hours < 24) return `${hours}h ago`;
                return `${days}d ago`;
            }

            function getActivityMessage(activity) {
                const tableName = activity.table_name;
                const action = (activity.action || '').toUpperCase();
                const recordId = activity.record_id;

                const oldValues = activity.old_values || null;
                const newValues = activity.new_values || null;

                function formatValues(values, limit = 5) {
                    if (!values || typeof values !== 'object') return '';
                    const formatVal = v => {
                        if (v === null || v === undefined) return 'null';
                        if (typeof v === 'object') return JSON.stringify(v);
                        return v;
                    };
                    const entries = Object.entries(values).slice(0, limit).map(([k, v]) => `${k}: ${formatVal(v)}`);
                    const more = Object.keys(values).length > limit ? ` +${Object.keys(values).length - limit} more` : '';
                    return entries.join('<br>') + more;
                }

                if (action === 'DELETE') {
                    const details = formatValues(oldValues);
                    return `Deleted <strong>${tableName}</strong> record #${recordId}${details ? '<br><small>' + details + '</small>' : ''}`;
                }

                if (action === 'UPDATE') {
                    // Build list of changed fields
                    const diffs = [];
                    if (oldValues && newValues) {
                        const keys = Array.from(new Set([...Object.keys(oldValues), ...Object.keys(newValues)]));
                        keys.forEach(k => {
                            const oldV = oldValues[k];
                            const newV = newValues[k];
                            if (oldV != newV) {
                                diffs.push(`${k}: ${oldV} → ${newV}`);
                            }
                        });
                    }
                    const diffText = diffs.length ? diffs.slice(0,5).join('<br>') + (diffs.length > 5 ? ` +${diffs.length - 5} more` : '') : '';
                    return `Updated <strong>${tableName}</strong> record #${recordId}${diffText ? '<br><small>' + diffText + '</small>' : ''}`;
                }

                if (action === 'CREATE') {
                    const details = formatValues(newValues);
                    return `Created <strong>${tableName}</strong> record #${recordId}${details ? '<br><small>' + details + '</small>' : ''}`;
                }

                // default
                return `${action} operation on ${tableName} record #${recordId}`;
            }

            function renderArchiveTable() {
                const $tbody = $('#archiveTableBody');
                $tbody.empty();

                if (!archiveData || archiveData.length === 0) {
                    $tbody.html('<tr><td colspan="6" class="text-center">No archived records found</td></tr>');
                    return;
                }

                archiveData.forEach(archive => {
                    const rowHtml = createArchiveRow(archive);
                    $tbody.append(rowHtml);
                });
            }

            function createArchiveRow(archive) {
                const deletedAt = new Date(archive.deleted_at).toLocaleDateString();
                const tableName = archive.table_name;
                const recordId = archive.record_id;
                const reason = archive.reason || 'No reason provided';
                
                return `
                    <tr>
                        <td>${archive.id}</td>
                        <td><span class="badge badge-secondary">${tableName}</span></td>
                        <td>#${recordId}</td>
                        <td>${deletedAt}</td>
                        <td><small>${reason}</small></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info view-archive-btn me-1" 
                                    data-id="${archive.id}" 
                                    style="display: inline-flex; align-items: center; gap: 4px;">
                                <span class="material-icons" style="font-size: 16px;">visibility</span>
                                <span>View</span>
                            </button>
                            <button class="btn btn-sm btn-success restore-btn" 
                                    data-id="${archive.id}"
                                    style="display: inline-flex; align-items: center; gap: 4px;">
                                <span class="material-icons" style="font-size: 16px;">restore</span>
                                <span>Restore</span>
                            </button>
                        </td>
                    </tr>
                `;
            }

            function renderArchiveDetails(archive) {
                const deletedData = archive.deleted_data || {};
                const deletedBy = archive.deleted_by || 'System';
                const deletedAt = new Date(archive.deleted_at).toLocaleString();
                const reason = archive.reason || 'No reason provided';

                let detailsHtml = `
                    <div class="mb-3">
                        <strong>Archive ID:</strong> ${archive.id}<br>
                        <strong>Table:</strong> ${archive.table_name}<br>
                        <strong>Record ID:</strong> #${archive.record_id}<br>
                        <strong>Deleted At:</strong> ${deletedAt}<br>
                        <strong>Deleted By:</strong> ${deletedBy}
                    </div>
                    <div class="mb-3">
                        <strong>Reason:</strong><br>
                        <div>${reason}</div>
                    </div>
                    <div>
                        <strong>Deleted Data:</strong>
                        <pre style="white-space: pre-wrap; background:#f8f9fa; padding:10px; border-radius:6px;">${JSON.stringify(deletedData, null, 2)}</pre>
                    </div>
                `;

                $('#archiveDetailsBody').html(detailsHtml);
            }
    </script>
</body>

</html>