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
    <title>Archive History - Repair System</title>
    <link rel="shortcut Icon" href="../img/Repair.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --primary: #2d3748;
            --secondary: #2d3748;
            --highlight: #e53e3e;
            --success: #28a745;
            --warning: #dd6b20;
            --info: #17a2b8;
            --light-bg: #f4f6f9;
            --card-bg: #ffffff;
            --text: #333;
            --text-light: #666;
            --border: #dee2e6;
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
        
        .content-area {
            padding: 20px;
            flex: 1;
            overflow-y: auto;
        }
        
        /* Card Styles */
        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid var(--border);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 2px solid var(--border);
            flex-wrap: wrap;
            gap: 15px;
            background: #353b48;
            border-radius: 12px 12px 0 0;
        }
        
        .card-header h5 {
            margin: 0;
            color: white;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .search-box {
            position: relative;
            min-width: 300px;
        }
        
        .search-box .form-control {
            height: 40px;
            padding: 8px 12px 8px 40px;
            font-size: 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .search-box .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 110, 108, 0.1);
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 20px;
            pointer-events: none;
        }
        
        /* Filter Section */
        .filter-section {
            padding: 20px 25px;
            background: #f8f9fa;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-label {
            font-weight: 600;
            color: var(--text);
            font-size: 0.95rem;
        }
        
        .filter-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border: 2px solid var(--border);
            background: white;
            color: var(--text);
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .filter-btn:hover {
            background: var(--light-bg);
            border-color: var(--primary);
        }
        
        .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .stats-container {
            padding: 20px 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid var(--border);
        }
        
        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .stat-box h6 {
            font-size: 0.85rem;
            color: var(--text-light);
            margin: 0 0 5px 0;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .stat-box .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        /* Button Styles */
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.3s;
            font-weight: 500;
            min-width: 100px;
            text-align: center;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
        
        .btn-info {
            background: var(--info);
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
        }
        
        .btn-warning {
            background: var(--warning);
            color: white;
        }
        
        .btn-danger {
            background: var(--highlight);
            color: white;
        }
        
        .btn-primary {
            background: #353b48;
            color: white;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        /* Table Styles */
        .table-container {
            padding: 25px;
        }
        
        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 14px;
            background: white;
        }
        
        .table thead th {
            background: #353b48;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table thead th:first-child {
            border-radius: 8px 0 0 0;
        }
        
        .table thead th:last-child {
            border-radius: 0 8px 0 0;
        }
        
        .table tbody td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .table tbody tr:last-child td:first-child {
            border-radius: 0 0 0 8px;
        }
        
        .table tbody tr:last-child td:last-child {
            border-radius: 0 0 8px 0;
        }
        
        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .badge-customer {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-transaction {
            background: #cce5ff;
            color: #004085;
        }
        
        .badge-parts {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-appliance {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-staff {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-default {
            background: #e2e3e5;
            color: #383d41;
        }
        
        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-top: 2px solid var(--border);
            background: #f8f9fa;
            border-radius: 0 0 12px 12px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .pagination-info {
            font-size: 0.9rem;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .pagination {
            margin: 0;
            display: flex;
            gap: 5px;
        }
        
        .page-link {
            padding: 8px 14px;
            border: 2px solid var(--border);
            background: white;
            color: var(--text);
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .page-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .page-item.active .page-link {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        /* Empty State */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }
        
        .empty-state .material-icons {
            font-size: 80px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: var(--text);
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .empty-state p {
            color: var(--text-light);
            font-size: 0.95rem;
        }
        
        /* Loading State */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .loading-overlay.show {
            display: flex;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Alert Styles */
        .alert-custom {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 350px;
            max-width: 500px;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        /* Modal Enhancements */
        .modal-header {
            background: #353b48;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        .modal-header .close {
            color: white;
            opacity: 0.8;
        }
        
        .modal-content {
            border-radius: 8px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .detail-grid-landscape {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--text);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            color: var(--text-light);
            font-size: 1rem;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 3px solid var(--info);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #content {
                margin-left: 0;
                width: 100%;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .pagination-container {
                flex-direction: column;
                text-align: center;
            }
            
            .alert-custom {
                min-width: 90%;
                right: 5%;
            }
        }
    </style>
</head>

<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <div class="wrapper">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <div id="content">
            <!-- Top Navbar -->
            <?php
            $pageTitle = 'Archive History';
            $breadcrumb = 'Archive';
            include __DIR__ . '/../layout/navbar.php';
            ?>

            <div class="content-area">
                <!-- Main Card -->
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <span class="material-icons">archive</span>
                            Archive & Deleted Records
                        </h5>
                        <div class="header-actions">
                            <div class="search-box">
                                <span class="material-icons search-icon">search</span>
                                <input type="text" class="form-control" id="searchArchive" placeholder="Search by table, record ID, or reason...">
                            </div>
                            <button id="refreshBtn" class="btn btn-sm" style="background: white; color: var(--primary);">
                                <span class="material-icons">refresh</span>
                                Refresh
                            </button>
                            <button id="exportBtn" class="btn btn-sm" style="background: white; color: var(--primary);">
                                <span class="material-icons">download</span>
                                Export
                            </button>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="stats-container" id="statsContainer">
                        <div class="stat-box">
                            <h6>Total Archived</h6>
                            <div class="stat-value" id="totalArchived">0</div>
                        </div>
                        <div class="stat-box">
                            <h6>This Month</h6>
                            <div class="stat-value" id="thisMonth">0</div>
                        </div>
                        <div class="stat-box">
                            <h6>This Week</h6>
                            <div class="stat-value" id="thisWeek">0</div>
                        </div>
                        <div class="stat-box">
                            <h6>Today</h6>
                            <div class="stat-value" id="today">0</div>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="filter-section">
                        <span class="filter-label">Filter by Type:</span>
                        <div class="filter-group" id="filterGroup">
                            <button class="filter-btn active" data-table="all">
                                <span class="material-icons" style="font-size: 16px;">select_all</span>
                                All Records
                            </button>
                            <!-- Dynamic filters will be added here -->
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th style="width: 150px;">Table Type</th>
                                        <th style="width: 250px;">Record Details</th>
                                        <th style="width: 180px;">Deleted At</th>
                                        <th style="width: 140px;">Deleted By</th>
                                        <th>Reason</th>
                                        <th style="width: 200px;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="archiveTableBody">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        <div class="pagination-info" id="paginationInfo">
                            Showing 0 to 0 of 0 entries
                        </div>
                        <nav>
                            <ul class="pagination" id="pagination">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <span class="material-icons align-middle" style="font-size: 20px; margin-right: 8px;">info</span>
                        Archived Record Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailsModalBody">
                    <!-- Details will be loaded here -->
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

    <script>
        const API_URL = '../backend/api/archive_history_api.php';
        let archiveData = [];
        let currentPage = 1;
        let itemsPerPage = 10;
        let currentFilter = 'all';
        let searchTerm = '';
        let userMap = {}; // Cache for user data

        $(document).ready(function() {
            // Initialize
            loadArchiveData();

            // Search
            let searchTimeout;
            $('#searchArchive').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    searchTerm = $('#searchArchive').val();
                    currentPage = 1;
                    loadArchiveData();
                }, 500);
            });

            // Refresh
            $('#refreshBtn').on('click', function() {
                loadArchiveData();
            });

            // Export
            $('#exportBtn').on('click', function() {
                exportToCSV();
            });

            // Filter buttons
            $(document).on('click', '.filter-btn', function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                currentFilter = $(this).data('table');
                currentPage = 1;
                loadArchiveData();
            });

            // Pagination
            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page !== currentPage) {
                    currentPage = page;
                    loadArchiveData();
                }
            });

            // View details
            $(document).on('click', '.btn-view', function() {
                const id = $(this).data('id');
                showDetails(id);
            });

            // Restore record
            $(document).on('click', '.btn-restore', function() {
                const id = $(this).data('id');
                const $btn = $(this);
                
                if (confirm('Are you sure you want to restore this record?')) {
                    restoreRecord(id, $btn);
                }
            });

            // Sidebar toggle
            $(".xp-menubar").on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            $(".xp-menubar,.body-overlay").on('click', function() {
                $('#sidebar,.body-overlay').toggleClass('show-nav');
            });
        });

        function loadArchiveData() {
            showLoading(true);
            
            const params = new URLSearchParams({
                action: 'getArchivedRecords',
                page: currentPage,
                itemsPerPage: itemsPerPage,
                search: searchTerm
            });

            $.ajax({
                url: `${API_URL}?${params}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Archive data loaded:', response);
                    if (response.success) {
                        archiveData = response.data.archives || [];
                        
                        // Extract unique user IDs to fetch their details
                        const userIds = [...new Set(archiveData.map(a => a.deleted_by).filter(id => id && id !== '0' && id !== 0))];
                        
                        // Load user details if we have IDs to fetch
                        if (userIds.length > 0) {
                            loadUserDetails(userIds, function() {
                                renderTable(archiveData);
                                updatePagination(response.data);
                                updateStats(response.data);
                                updateFilters(archiveData);
                                showLoading(false);
                            });
                        } else {
                            renderTable(archiveData);
                            updatePagination(response.data);
                            updateStats(response.data);
                            updateFilters(archiveData);
                            showLoading(false);
                        }
                    } else {
                        showAlert('error', response.message || 'Failed to load archive data');
                        renderEmptyState('error');
                        showLoading(false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading archive data:', error);
                    showAlert('error', 'Failed to load archive data: ' + error);
                    renderEmptyState('error');
                    showLoading(false);
                }
            });
        }

        function loadUserDetails(userIds, callback) {
            // Fetch user information for the given IDs
            // We'll need to call the staff API multiple times or check the staffs
            let loadedCount = 0;
            let totalToLoad = userIds.length;

            if (totalToLoad === 0) {
                callback();
                return;
            }

            userIds.forEach(userId => {
                $.ajax({
                    url: `../backend/api/staff_api.php?action=getStaffsById&id=${userId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            const user = response.data;
                            userMap[userId] = {
                                name: user.first_name && user.last_name 
                                    ? `${user.first_name} ${user.last_name}` 
                                    : (user.name || user.fullname || `User #${userId}`),
                                role: user.role === 'admin' ? 'Admin' : 'Staff'
                            };
                        }
                        loadedCount++;
                        if (loadedCount === totalToLoad) {
                            console.log('User map loaded:', userMap);
                            callback();
                        }
                    },
                    error: function(xhr, status, error) {
                        loadedCount++;
                        console.warn(`Could not load user ${userId}:`, error);
                        if (loadedCount === totalToLoad) {
                            callback();
                        }
                    }
                });
            });
        }

        function renderTable(data) {
            const $tbody = $('#archiveTableBody');
            $tbody.empty();

            if (!data || data.length === 0) {
                renderEmptyState('no-data');
                return;
            }

            // Filter data if needed
            let filteredData = data;
            if (currentFilter !== 'all') {
                filteredData = data.filter(item => item.table_name === currentFilter);
            }

            filteredData.forEach(item => {
                const row = createTableRow(item);
                $tbody.append(row);
            });
        }

        function createTableRow(item) {
            const badgeClass = getBadgeClass(item.table_name);
            const deletedAt = formatDateTime(item.deleted_at);
            const deletedByFormatted = formatDeletedBy(item.deleted_by);
            const reason = item.reason || 'No reason provided';
            const recordName = getRecordName(item);

            return `
                <tr>
                    <td><strong>#${item.id}</strong></td>
                    <td><span class="badge ${badgeClass}">${item.table_name}</span></td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <div><span style="color: #999; font-size: 0.85rem;">Record ID:</span></div>
                            <div><strong>#${item.record_id}</strong></div>
                            <div style="margin-top: 6px;"><span style="color: #999; font-size: 0.85rem;">Record Name:</span></div>
                            <div><strong>${recordName}</strong></div>
                        </div>
                    </td>
                    <td><small>${deletedAt}</small></td>
                    <td><small>${deletedByFormatted}</small></td>
                    <td><small>${truncateText(reason, 50)}</small></td>
                    <td class="text-center" style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                        <button class="btn btn-sm btn-info btn-view" data-id="${item.id}" style="width: 110px;">
                            <span class="material-icons" style="font-size: 18px;">visibility</span>
                            <span>View</span>
                        </button>
                        <button class="btn btn-sm btn-success btn-restore" data-id="${item.id}" style="width: 110px;">
                            <span class="material-icons" style="font-size: 18px;">restore</span>
                            <span>Restore</span>
                        </button>
                    </td>
                </tr>
            `;
        }

        function formatDeletedBy(userId) {
            // Handle system deletions
            if (!userId || userId === '0' || userId === 0) {
                return 'System';
            }

            // Check if we have cached user info
            if (userMap[userId]) {
                const user = userMap[userId];
                return `${user.role} - ${user.name}`;
            }

            // Return as is if no cached data (will try to load later)
            return `User #${userId}`;
        }

        function getRecordName(item) {
            let data = item.deleted_data || {};
            const recordId = item.record_id;
            const tableName = (item.table_name || '').toLowerCase();
            
            // Handle if deleted_data is a string
            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    console.log('Failed to parse deleted_data:', data);
                    return `Record #${recordId}`;
                }
            }
            
            // Log for debugging
            console.log(`=== RECORD [${tableName}#${recordId}] ===`, Object.keys(data));
            
            // Helper function to get a value from multiple possible field names
            const getField = (fieldNames) => {
                if (!Array.isArray(fieldNames)) fieldNames = [fieldNames];
                for (let field of fieldNames) {
                    if (data[field]) return data[field];
                }
                return null;
            };
            
            // Extract name based on table type
            if (tableName.includes('customer')) {
                const name = getField(['customer_name', 'first_name', 'name']);
                const lastName = getField(['last_name']);
                if (name && lastName && name !== lastName) {
                    return `${name} ${lastName}`;
                }
                return name || `Customer #${recordId}`;
                
            } else if (tableName.includes('staff')) {
                const firstName = getField(['first_name', 'name']);
                const lastName = getField(['last_name']);
                if (firstName && lastName && firstName !== lastName) {
                    return `${firstName} ${lastName}`;
                }
                return firstName || `Staff #${recordId}`;
                
            } else if (tableName.includes('part')) {
                return getField(['parts_name', 'part_name', 'name']) || `Part #${recordId}`;
                
            } else if (tableName.includes('appliance')) {
                return getField(['appliance_name', 'appliance', 'name']) || `Appliance #${recordId}`;
                
            } else if (tableName.includes('work_service') || tableName.includes('workservice')) {
                return getField(['work_service_name', 'service_name', 'name']) || `Work Service #${recordId}`;
                
            } else if (tableName.includes('transaction')) {
                const customerName = getField(['customer_name', 'customer']);
                const amount = getField(['total_amount', 'amount']);
                if (customerName && amount) {
                    return `${customerName} - ₱${parseFloat(amount).toFixed(2)}`;
                }
                return customerName || getField(['transaction_id', 'service_number']) || `Transaction #${recordId}`;
                
            } else if (tableName.includes('service_detail')) {
                const customerName = getField(['customer_name', 'customer']);
                const applianceName = getField(['appliance_name', 'appliance']);
                if (customerName && applianceName) {
                    return `${customerName} - ${applianceName}`;
                }
                return customerName || applianceName || getField(['service_number', 'service_id']) || `Service #${recordId}`;
                
            } else if (tableName.includes('service_report')) {
                const customerName = getField(['customer_name', 'customer']);
                const serviceNum = getField(['service_number']);
                if (customerName && serviceNum) {
                    return `${customerName} - ${serviceNum}`;
                }
                return customerName || serviceNum || getField(['report_id']) || `Report #${recordId}`;
                
            } else {
                // Default: try to find any name-like field
                const nameField = getField(['customer_name', 'name', 'first_name', 'parts_name', 'appliance_name', 'service_number']);
                return nameField || `Record #${recordId}`;
            }
        }

        function getBadgeClass(tableName) {
            const badges = {
                'customer': 'badge-customer',
                'transaction': 'badge-transaction',
                'parts': 'badge-parts',
                'appliance': 'badge-appliance',
                'staff': 'badge-staff'
            };
            return badges[tableName] || 'badge-default';
        }

        function formatDateTime(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function truncateText(text, maxLength) {
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        }

        function updatePagination(data) {
            const $pagination = $('#pagination');
            const $info = $('#paginationInfo');
            
            $pagination.empty();

            const totalPages = data.total_pages || 1;
            const totalItems = data.total_items || 0;
            const start = totalItems > 0 ? ((currentPage - 1) * itemsPerPage) + 1 : 0;
            const end = Math.min(currentPage * itemsPerPage, totalItems);

            $info.html(`Showing <strong>${start}</strong> to <strong>${end}</strong> of <strong>${totalItems}</strong> entries`);

            if (totalPages <= 1) return;

            // Previous button
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            $pagination.append(`
                <li class="page-item ${prevDisabled}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">
                        <span class="material-icons" style="font-size: 16px;">chevron_left</span>
                    </a>
                </li>
            `);

            // Page numbers
            const maxPages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
            let endPage = Math.min(totalPages, startPage + maxPages - 1);

            if (endPage - startPage < maxPages - 1) {
                startPage = Math.max(1, endPage - maxPages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const active = i === currentPage ? 'active' : '';
                $pagination.append(`
                    <li class="page-item ${active}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            }

            // Next button
            const nextDisabled = currentPage === totalPages ? 'disabled' : '';
            $pagination.append(`
                <li class="page-item ${nextDisabled}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">
                        <span class="material-icons" style="font-size: 16px;">chevron_right</span>
                    </a>
                </li>
            `);
        }

        function updateStats(data) {
            const archives = data.archives || [];
            const now = new Date();
            
            // Total
            $('#totalArchived').text(data.total_items || 0);

            // This month
            const thisMonth = archives.filter(item => {
                const date = new Date(item.deleted_at);
                return date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear();
            }).length;
            $('#thisMonth').text(thisMonth);

            // This week
            const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
            const thisWeek = archives.filter(item => new Date(item.deleted_at) >= weekAgo).length;
            $('#thisWeek').text(thisWeek);

            // Today
            const today = archives.filter(item => {
                const date = new Date(item.deleted_at);
                return date.toDateString() === now.toDateString();
            }).length;
            $('#today').text(today);
        }

        function updateFilters(data) {
            const tables = [...new Set(data.map(item => item.table_name))];
            const $filterGroup = $('#filterGroup');
            
            // Keep the "All Records" button
            $filterGroup.find('[data-table!="all"]').remove();

            // Add table-specific filters
            tables.forEach(table => {
                const count = data.filter(item => item.table_name === table).length;
                const icon = getTableIcon(table);
                $filterGroup.append(`
                    <button class="filter-btn" data-table="${table}">
                        <span class="material-icons" style="font-size: 16px;">${icon}</span>
                        ${capitalize(table)} (${count})
                    </button>
                `);
            });
        }

        function getTableIcon(table) {
            const icons = {
                'customer': 'person',
                'transaction': 'receipt',
                'parts': 'build',
                'appliance': 'devices',
                'staff': 'badge'
            };
            return icons[table] || 'table_chart';
        }

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function showDetails(id) {
            const item = archiveData.find(a => a.id == id);
            if (!item) {
                showAlert('error', 'Record not found');
                return;
            }

            const deletedData = item.deleted_data || {};
            const deletedAt = formatDateTime(item.deleted_at);
            const deletedByFormatted = formatDeletedBy(item.deleted_by);

            let html = `
                <div class="detail-grid-landscape">
                    <div class="detail-item">
                        <div class="detail-label">Archive ID</div>
                        <div class="detail-value"><strong>#${item.id}</strong></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Record ID</div>
                        <div class="detail-value"><strong>#${item.record_id}</strong></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Table Type</div>
                        <div class="detail-value"><span class="badge ${getBadgeClass(item.table_name)}">${item.table_name}</span></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Deleted By</div>
                        <div class="detail-value">${deletedByFormatted}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Deleted At</div>
                        <div class="detail-value">${deletedAt}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Reason</div>
                        <div class="detail-value">${item.reason || 'No reason provided'}</div>
                    </div>
                </div>
                <hr>
                <h6 class="mt-3 mb-3"><strong>Deleted Data:</strong></h6>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 400px; overflow-y: auto;">
                    ${formatDeletedData(deletedData)}
                </div>
            `;

            $('#detailsModalBody').html(html);
            $('#detailsModal').modal('show');
        }

        function formatDeletedData(data) {
            if (!data || Object.keys(data).length === 0) {
                return '<p style="margin: 0; color: #999;">No data available</p>';
            }

            let html = '<div style="font-size: 0.9rem;">';
            
            for (const [key, value] of Object.entries(data)) {
                // Skip null or undefined values
                if (value === null || value === undefined) continue;
                
                // Format the key to be more readable
                const label = key.split('_').map(word => 
                    word.charAt(0).toUpperCase() + word.slice(1)
                ).join(' ');
                
                // Format the value
                let displayValue = value;
                if (typeof value === 'object') {
                    displayValue = JSON.stringify(value, null, 2);
                } else if (key.toLowerCase().includes('date') || key.toLowerCase().includes('time')) {
                    // Try to format dates
                    const date = new Date(value);
                    if (!isNaN(date.getTime())) {
                        displayValue = formatDateTime(value);
                    }
                } else if (typeof value === 'boolean') {
                    displayValue = value ? 'Yes' : 'No';
                }
                
                html += `
                    <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #dee2e6;">
                        <div style="font-weight: 600; color: #495057; margin-bottom: 4px;">${label}:</div>
                        <div style="color: #6c757d; word-break: break-word;">${displayValue}</div>
                    </div>
                `;
            }
            
            html += '</div>';
            return html;
        }

        function restoreRecord(id, $btn) {
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Restoring...');

            $.ajax({
                url: `${API_URL}?action=restoreRecord&id=${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Record restored successfully!');
                        loadArchiveData();
                    } else {
                        showAlert('error', response.message || 'Failed to restore record');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr, status, error) {
                    showAlert('error', 'Error restoring record: ' + error);
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        }

        function exportToCSV() {
            if (archiveData.length === 0) {
                showAlert('warning', 'No data to export');
                return;
            }

            const headers = ['ID', 'Table Type', 'Record ID', 'Deleted At', 'Deleted By', 'Reason'];
            const rows = archiveData.map(item => [
                item.id,
                item.table_name,
                item.record_id,
                item.deleted_at,
                item.deleted_by || 'System',
                (item.reason || 'No reason provided').replace(/,/g, ';')
            ]);

            let csv = headers.join(',') + '\n';
            rows.forEach(row => {
                csv += row.join(',') + '\n';
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `archive_history_${new Date().getTime()}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            showAlert('success', 'Data exported successfully!');
        }

        function renderEmptyState(type) {
            let icon, title, message;
            
            if (type === 'no-data') {
                icon = 'inventory_2';
                title = 'No Archived Records';
                message = 'There are no deleted records in the archive at this time.';
            } else {
                icon = 'error_outline';
                title = 'Error Loading Data';
                message = 'Unable to load archived records. Please try again.';
            }

            const html = `
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <span class="material-icons">${icon}</span>
                            <h4>${title}</h4>
                            <p>${message}</p>
                        </div>
                    </td>
                </tr>
            `;
            $('#archiveTableBody').html(html);
        }

        function showLoading(show) {
            if (show) {
                $('#loadingOverlay').addClass('show');
            } else {
                $('#loadingOverlay').removeClass('show');
            }
        }

        function showAlert(type, message) {
            const icons = {
                success: 'check_circle',
                error: 'error',
                warning: 'warning',
                info: 'info'
            };

            const alertHtml = `
                <div class="alert-custom alert-${type}">
                    <span class="material-icons">${icons[type]}</span>
                    <span>${message}</span>
                    <button type="button" class="close" onclick="$(this).parent().fadeOut(300, function() { $(this).remove(); })">
                        <span>&times;</span>
                    </button>
                </div>
            `;

            $('body').append(alertHtml);

            setTimeout(function() {
                $('.alert-custom').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    </script>
</body>
</html>
