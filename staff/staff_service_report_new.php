<?php
require_once __DIR__ . '/../bootstrap.php';
session_start();
require __DIR__ . '/../backend/handlers/authHandler.php';
require __DIR__ . '/../backend/handlers/Database.php';
require __DIR__ . '/../backend/handlers/serviceHandler.php';
require __DIR__ . '/../backend/handlers/customersHandler.php';
require __DIR__ . '/../backend/handlers/partsHandler.php';
require __DIR__ . '/../backend/handlers/staffsHandler.php';

$auth = new AuthHandler();
$userSession = $auth->requireAuth('staff');

// Initialize database connection
try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Service Reports</title>
    <link rel="shortcut Icon" href="../img/Repair.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        .icon-btn {
            background: #ececec;
            border: 1.5px solid #ececec;
            border-radius: 8px;
            transition: background 0.2s, border 0.2s, box-shadow 0.2s;
        }

        .icon-btn:hover,
        .icon-btn:focus {
            background: #f8f9fa;
            border-color: #bdbdbd;
            box-shadow: 0 2px 8px rgba(53, 59, 72, 0.10);
        }

        .blink-badge {
            animation: blink-badge 1.2s infinite alternate;
        }

        @keyframes blink-badge {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0.5;
            }
        }

        .actions-col {
            min-width: 180px;
        }

        .actions-col a {
            display: inline-block;
            margin-right: 8px;
            vertical-align: middle;
        }

        .actions-col a:last-child {
            margin-right: 0;
        }

        .alert-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.5s forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading {
            position: relative;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .actions-col a {
            display: inline-flex;
            align-items: center;
            margin-right: 8px;
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .actions-col a:hover {
            background-color: #f8f9fa;
        }

        .actions-col a span {
            margin-left: 4px;
            font-size: 12px;
        }

        .actions-col a.edit-report-completed {
            color: #28a745;
        }

        .actions-col a.edit-report-completed:hover {
            background-color: #e8f5e8;
        }

        .actions-col a.edit-report {
            color: #007bff;
        }

        .actions-col a.edit-report:hover {
            background-color: #e3f2fd;
        }

        .actions-col a.delete-report {
            color: #dc3545;
        }

        .actions-col a.delete-report:hover {
            background-color: #fde8e8;
        }

        .part-used-section {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #ffffffff;
        }

        .part-used-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .parts-container-wrapper {
            margin-bottom: 15px;
        }

        .add-part-btn-container {
            display: flex;
            justify-content: flex-start;
            margin-top: 10px;
        }

        .section-border {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        #serviceReportListModal .modal-dialog {
            margin: 1.75rem auto;
            max-height: 90vh;
            display: flex;
            align-items: center;
        }

        #serviceReportListModal .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }

        #serviceReportListModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom-color: #007bff;
            color: #007bff;
            font-weight: 500;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }

        .findings-section {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        
        .findings-header {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: 10px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .findings-row {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: 10px;
            align-items: center;
        }
        
        .checkbox-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .findings-checkbox {
            width: 20px;
            height: 20px;
            margin: 0;
        }

        .modal-xxl {
            max-width: 90vw !important;
            width: 90vw !important;
            margin: 0 auto;
        }

        .modal-xxl .modal-content {
            min-height: 96vh;
            height: auto;
        }
        
        .modal-title {
            color: #000000 !important;
            font-weight: 600;
        }

        /* Print styles */
        @media print {
            body {
                visibility: hidden;
                margin: 0 !important;
                padding: 0 !important;
                color: #000000 !important;
            }
            
            .wrapper,
            .xp-menubar,
            .body-overlay,
            #sidebar,
            #content .main-content .row .col-md-12 .card,
            .modal-header,
            .modal-footer,
            #serviceReportListModal,
            .modal-header button,
            .ms-auto,
            .d-flex {
                display: none !important;
            }
            
            #printReportModal {
                position: static !important;
                display: block !important;
                width: 100% !important;
                height: 100% !important;
                background: white !important;
                border: none !important;
                opacity: 1 !important;
            }
            
            #printReportModal .modal-dialog {
                position: static !important;
                display: block !important;
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            
            #printReportModal .modal-content {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                height: auto !important;
                background: white !important;
                opacity: 1 !important;
            }
            
            #printReportModal .modal-body {
                padding: 0 !important;
                display: block !important;
                height: auto !important;
                background: white !important;
                opacity: 1 !important;
            }
            
            #print-report-body {
                display: block !important;
                width: 100% !important;
                height: auto !important;
                background: white !important;
                page-break-inside: avoid !important;
                opacity: 1 !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            /* Force ALL text to be pure black - not gray */
            #print-report-body,
            #print-report-body * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
                color: #000000 !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* Specific text elements - force pure black */
            #print-report-body h1,
            #print-report-body h2,
            #print-report-body h3,
            #print-report-body h4,
            #print-report-body h5,
            #print-report-body h6 {
                color: #000000 !important;
                opacity: 1 !important;
                visibility: visible !important;
                text-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            #print-report-body p,
            #print-report-body span,
            #print-report-body strong,
            #print-report-body b,
            #print-report-body em,
            #print-report-body i {
                color: #000000 !important;
                opacity: 1 !important;
                visibility: visible !important;
                text-shadow: none !important;
                -webkit-text-fill-color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Force table text to pure black */
            #print-report-body table {
                width: 100% !important;
                border-collapse: collapse !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            #print-report-body tbody,
            #print-report-body thead,
            #print-report-body tfoot {
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            #print-report-body tr {
                page-break-inside: avoid !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            #print-report-body td,
            #print-report-body th {
                color: #000000 !important;
                border-color: #000000 !important;
                opacity: 1 !important;
                visibility: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Force div and section text to black */
            #print-report-body div {
                color: #000000 !important;
                opacity: 1 !important;
                visibility: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Remove any background colors and shadows when printing so text remains visible */
            #print-report-body,
            #print-report-body * {
                background: transparent !important;
                background-color: transparent !important;
                box-shadow: none !important;
                -webkit-box-shadow: none !important;
                filter: none !important;
            }
            
            @page {
                size: A4;
                margin: 0.3in;
            }
        }

        .print-content {
            margin: 0;
            padding: 20px;
            font-size: 11px;
            line-height: 1.4;
            background: transparent;
        }

        #transactionForm input,
        #transactionForm select,
        #transactionForm textarea {
            background-color: #f8f9fa;
        }

        #transactionForm input:not([readonly]),
        #transactionForm select:not([readonly]),
        #transactionForm textarea:not([readonly]) {
            background-color: #ffffff;
        }

        .pagination .page-link {
            color: #242424ff;
            border-color: #dee2e6;
        }
        .pagination .page-item.active .page-link {
            background-color: #242424ff;
            border-color: #242424ff;
            color: #fff;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
        }

        /* Service Report List Modal Styles */
        #serviceReportListModal .modal-dialog {
            margin: 1.75rem auto;
            max-height: 90vh;
            display: flex;
            align-items: center;
        }

        #serviceReportListModal .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }

        #serviceReportListModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        #serviceReportListModal .modal-header {
            background-color: #0066e6 !important;
        }

        #serviceReportListModal .modal-title {
            color: white !important;
        }

        #serviceReportListModal .close {
            color: white !important;
            opacity: 1 !important;
            font-size: 1.8rem;
            background: none;
            border: none;
        }

        #serviceReportListModal .close:hover {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        /* Customer search suggestion styles */
        .customer-search-wrapper {
            position: relative;
        }
        .customer-suggestions {
            position: absolute;
            left: 0;
            top: calc(100% + 6px);
            width: 100%;
            z-index: 2000;
            box-shadow: 0 10px 30px rgba(50,50,93,0.12);
            border-radius: 8px;
            overflow: auto;
        }
        .customer-suggestions .list-group-item {
            cursor: pointer;
            background-color: #ffffff !important;
            color: #212529 !important;
            border: none;
        }
        .customer-suggestions .list-group-item:hover,
        .customer-suggestions .list-group-item:focus,
        .customer-suggestions .list-group-item.active {
            background-color: #f8f9fa !important;
            color: #212529 !important;
        }

        /* Status Progress Indicator Styles */
        .status-progress-container {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .status-progress-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .progress-bar-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .progress-step {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .progress-step-number {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            margin-bottom: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .progress-step-number.inactive {
            background-color: #e0e0e0;
            color: #666;
        }

        .progress-step-number.active {
            background-color: #ffc107;
            color: #000;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.3);
        }

        .progress-step-number.completed {
            background-color: #28a745;
            color: white;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
        }

        .progress-step-label {
            font-size: 12px;
            font-weight: 500;
            color: #666;
            text-align: center;
            max-width: 80px;
        }

        .progress-step-label.active {
            color: #ffc107;
            font-weight: 600;
        }

        .progress-step-label.completed {
            color: #28a745;
            font-weight: 600;
        }

        .progress-connector {
            flex: 1;
            height: 3px;
            background-color: #e0e0e0;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .progress-connector.active,
        .progress-connector.completed {
            background-color: #28a745;
        }

        .status-timeline {
            margin-top: 15px;
            padding: 12px;
            background-color: #ffffff;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }

        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #007bff;
            margin-right: 10px;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .timeline-text {
            flex: 1;
        }

        .timeline-text strong {
            color: #333;
        }

        .timeline-text span {
            color: #666;
        }

        .status-dropdown-toggle {
            cursor: pointer;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .status-dropdown-toggle:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .status-dropdown-toggle.collapsed::after {
            content: '▼';
            display: inline-block;
            transition: transform 0.2s;
        }

        .status-dropdown-toggle:not(.collapsed)::after {
            content: '▲';
            display: inline-block;
            transition: transform 0.2s;
        }

        /* Comment Styles */
        .comment-btn {
            background-color: #17a2b8;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
        }

        .comment-btn:hover {
            background-color: #138496;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(23, 162, 184, 0.3);
        }

        .progress-comments-list {
            padding: 8px;
            background-color: #f9f9f9;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }

        .comment-item {
            background-color: #ffffff;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 4px;
            border-left: 3px solid #17a2b8;
        }

        .comment-item:last-child {
            margin-bottom: 0;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .comment-author {
            font-weight: 600;
            color: #333;
            font-size: 12px;
        }

        .comment-time {
            font-size: 11px;
            color: #999;
        }

        .comment-text {
            font-size: 12px;
            color: #555;
            line-height: 1.4;
            word-break: break-word;
        }

        .no-comments {
            font-size: 12px;
            color: #999;
            font-style: italic;
            padding: 8px;
            text-align: center;
        }

        .progress-timeline-content {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px 0;
        }
    </style>
    <script>
        // Current logged-in staff name available for client JS
        window.currentStaffName = '<?php echo addslashes($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? ''); ?>';
    </script>
</head>
<body>
    <div class="wrapper">
        <!-- Include Sidebar -->
        <?php include 'staff_sidebar.php'; ?>

        <div id="content">
            <!-- Navbar -->
            <?php
              $pageTitle = "Service Report";
              $pageCrumb = "Customer";
              include 'staffnavbar.php'; 
            ?>
       
            <div class="content-area">
                <!-- Service Report Form -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Service Report Form</h5>
                        <button type="button" class="btn icon-btn position-relative p-2" data-toggle="modal" data-target="#serviceReportListModal">
                            <i class="material-icons align-middle" style="font-size: 2em; color: #353b48;">list</i>
                            <span id="report-badge" class="position-absolute blink-badge" style="display:none; top: 2px; right: 2px; width: 12px; height: 12px; background: #ff6b6b; border-radius: 50%;"></span>
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="serviceReportForm" method="post" action="">
                            <div class="container-fluid">
                                <!-- First Row: Customer, Appliance, Date In, Status -->
                                <div class="row mb-2">
                                    <div class="col-md-3">
                                        <label>Customer</label>
                                        <!-- Searchable customer input + hidden select to store id -->
                                        <div class="customer-search-wrapper" style="position: relative;">
                                            <input type="text" id="customer-search" class="form-control" placeholder="Search customer by name" autocomplete="off" spellcheck="false" autocorrect="off" autocapitalize="off">
                                            <select class="form-control customer-select d-none" name="customer" id="customer-select" aria-hidden="true" tabindex="-1">
                                                <option value="">Select Customer</option>
                                            </select>
                                            <div id="customer-suggestions" class="list-group customer-suggestions" style="display:none; max-height: 260px; overflow-y: auto; position: absolute; left: 0; top: calc(100% + 6px); width: 100%; z-index: 2000;"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Appliance</label>
                                        <select class="form-control appliance-select" name="appliance" id="appliance-select">
                                            <option>Select Appliance</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Date In</label>
                                                <input type="date" class="form-control" name="date_in" id="date-in" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Status</label>
                                        <select class="form-control" name="status" id="create_status" required>
                                            <option value="">Select Status</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Under Repair">Under Repair</option>
                                            <option value="Unrepairable">Unrepairable</option>
                                            <option value="Release Out">Release Out</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Status Progress Indicator -->
                                <div id="status-progress-container" class="status-progress-container" style="display: none;">
                                    <div class="status-progress-title">Repair Progress</div>
                                    
                                    <!-- Main Progress Bar -->
                                    <div class="progress-bar-container">
                                        <!-- Pending Step -->
                                        <div class="progress-step">
                                            <div class="progress-step-number inactive" id="step-1">1</div>
                                            <div class="progress-step-label" id="step-1-label">Pending</div>
                                        </div>
                                        
                                        <!-- Connector 1 -->
                                        <div class="progress-connector inactive" id="connector-1"></div>
                                        
                                        <!-- Under Repair Step -->
                                        <div class="progress-step">
                                            <div class="progress-step-number inactive" id="step-2">2</div>
                                            <div class="progress-step-label" id="step-2-label">Under Repair</div>
                                        </div>
                                        
                                        <!-- Connector 2 -->
                                        <div class="progress-connector inactive" id="connector-2"></div>
                                        
                                        <!-- Completed Step -->
                                        <div class="progress-step">
                                            <div class="progress-step-number inactive" id="step-3">3</div>
                                            <div class="progress-step-label" id="step-3-label">Completed</div>
                                        </div>
                                    </div>

                                    <!-- Status Timeline with Dropdown -->
                                    <div style="text-align: center; margin-top: 10px;">
                                        <a href="#" class="status-dropdown-toggle collapsed" id="status-timeline-toggle" data-toggle="collapse" data-target="#status-timeline-content">
                                            View Progress Timeline
                                        </a>
                                    </div>

                                    <div id="status-timeline-content" class="collapse">
                                        <div class="status-timeline">
                                            <div class="progress-timeline-content" id="progress-timeline-items">
                                                <div class="timeline-item">
                                                    <div class="timeline-dot"></div>
                                                    <div class="timeline-text">
                                                        <strong>Status Created</strong><br>
                                                        <span id="timeline-created-date">Not yet created</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Second Row: Dealer, DOP, Date Pulled-Out -->
                                <div class="row mb-2">
                                    <div class="col-md-3">
                                        <label>Dealer</label>
                                        <input type="text" class="form-control" name="dealer">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Date of Purchase</label>
                                        <input type="date" class="form-control" name="dop">
                                    </div>
                                </div>
                                <!-- Findings Row -->
                                <div class="row mb-0 align-items-end">
                                    <div class="col-md-7">
                                        <label>Findings</label>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <label class="form-label p-0 m-0 w-100" style="font-weight: normal; font-size: 0.9rem;">Shop</label>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <label class="form-label p-0 m-0 w-100" style="font-weight: normal;">Field</label>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <label class="form-label p-0 m-0 w-100" style="font-weight: normal;">In WTY</label>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <label class="form-label p-0 m-0 w-100" style="font-weight: normal;">Out WTY</label>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-md-7">
                                        <input type="text" class="form-control" name="findings">
                                    </div>
                                    <div class="col-md-1 text-center d-flex flex-column align-items-center justify-content-center">
                                        <input class="form-check-input" type="checkbox" name="shop" id="shop" style="width: 1.4em; height: 1.4em;">
                                    </div>
                                    <div class="col-md-1 text-center d-flex flex-column align-items-center justify-content-center">
                                        <input class="form-check-input" type="checkbox" name="field" id="field" style="width: 1.4em; height: 1.4em;">
                                    </div>
                                    <div class="col-md-1 text-center d-flex flex-column align-items-center justify-content-center">
                                        <input class="form-check-input" type="checkbox" name="in_wty" id="in_wty" style="width: 1.4em; height: 1.4em;">
                                    </div>
                                    <div class="col-md-1 text-center d-flex flex-column align-items-center justify-content-center">
                                        <input class="form-check-input" type="checkbox" name="out_wty" id="out_wty" style="width: 1.4em; height: 1.4em;">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-9">
                                        <label>Remarks</label>
                                        <input type="text" class="form-control" name="remarks">
                                    </div>
                                </div>
                                
                                <!-- Part Used Section -->
                                <div class="part-used-section">
                                    <div class="row mb-2 pl-3">
                                       <button type="button" class="btn btn-primary add-part d-flex align-items-center justify-content-center" id="add-part">
                                            <i class="material-icons me-1">add</i> Add Part
                                        </button>
                                    </div>
                                    
                                    <!-- Parts Container for dynamic rows -->
                                    <div id="parts-container" class="parts-container-wrapper">
                                        <div class="row mb-2 align-items-center g-2 parts-row">
                                            <div class="col-md-3 pe-1">
                                                <select class="form-control part-select" name="part_used[]">
                                                    <option value="">Select Part</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 px-1">
                                                <input type="number" class="form-control quantity-input" name="quantity[]" placeholder="Quantity" min="1">
                                            </div>
                                            <div class="col-md-2 px-1">
                                                <input type="number" class="form-control amount-input" name="amount[]" placeholder="Amount" readonly>
                                            </div>
                                            <div class="col-md-1 px-1 d-flex justify-content-end align-items-center ml-3">
                                                <button type="button" class="btn btn-danger remove-part d-flex align-items-center justify-content-center" style="display:none;">
                                                    <span class="material-icons" style="font-size: 1.2em;">delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                
                                    <!-- Checkboxes for Service Type -->
                                    <div class="row mb-1">
                                        <div class="col-md-12">
                                            <div class="d-flex flex-row" style="gap: 2rem;">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="installation" id="installation">
                                                    <label class="form-check-label" for="installation">Installation</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="repair" id="repair">
                                                    <label class="form-check-label" for="repair">Repair</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="cleaning" id="cleaning">
                                                    <label class="form-check-label" for="cleaning">Cleaning</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="checkup" id="checkup">
                                                    <label class="form-check-label" for="checkup">Check-Up</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Complaint, Date Repaired and Date Delivered Row -->
                                <div class="row mb-2">
                                    <div class="col-md-6 pe-1">
                                        <label>Complaint</label>
                                        <textarea class="form-control" name="complaint" id="complaint" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-3 px-1">
                                        <label>Date Repaired</label>
                                        <input type="date" class="form-control" name="date_repaired">
                                    </div>
                                    <div class="col-md-3 ps-1">
                                        <label>Date Delivered</label>
                                        <input type="date" class="form-control" name="date_delivered">
                                    </div>
                                </div>
                                <!-- Charged Details Section Header -->
                                <div class="row mt-3 mb-1">
                                    <div class="col-md-12">
                                        <h5 class="fw-bold mb-1">Charged Details</h5>
                                    </div>
                                </div>
                                <!-- Charged Details Row -->
                                <div class="row mb-2 align-items-end">
                                    <div class="col-md-3 pe-1">
                                        <label class="mb-1">Labor:</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" name="labor" id="labor-amount" value="0.00" min="0" step="1.00">
                                        </div>
                                    </div>
                                    <div class="col-md-3 px-1">
                                        <label class="mb-1">Pull-Out Delivery:</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" name="pullout_delivery" id="pullout-delivery" value="0.00" min="0" step="1.00">
                                        </div>
                                    </div>
                                    <div class="col-md-3 px-1">
                                        <label class="mb-1">Total:</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" name="total_amount" id="total-amount-2" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3 ps-1">
                                        <label class="mb-1">Parts Charge:</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" name="parts_charge" readonly>
                                        </div>
                                    </div>
                                </div>
                                <!-- Signature Fields Row -->
                                <div class="row mb-2">
                                    <div class="col-md-3 pe-1 d-flex flex-column justify-content-end">
                                       <select name="receptionist" id="receptionist-select" class="form-control staff-select" data-role="Secretary">
                                            <option value="">Receptionist</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 px-1 d-flex flex-column justify-content-end">
                                        <label class="mb-1">Manager:</label>
                                        <select name="manager" id="manager-select" class="form-control staff-select" data-role="Manager">
                                            <option value="">Manager</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 px-1 d-flex flex-column justify-content-end">
                                        <label class="mb-1">Technician:</label>
                                        <select name="technician" id="technician-select" class="form-control staff-select" data-role="Technician">
                                            <option value="">Technician</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 ps-1 d-flex flex-column justify-content-end">
                                        <label class="mb-1">Released By:</label>
                                        <select name="released_by" id="released-by-select" class="form-control staff-select" data-role="Secretary">
                                            <option value="">Released By</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Submit Button -->
                                <div class="d-flex justify-content-end mt-4" style="gap: 1rem;">
                                        <button type="button" class="btn btn-secondary px-4" id="cancel-button">Cancel</button>
                                        <input type="hidden" name="report_id" id="report_id">
                                        <button type="submit" class="btn btn-primary px-4" id="submit-report-btn" style="background-color: #0066e6; border: none;">Create Report</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Comment Modal -->
    <div class="modal fade" id="progressCommentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #17a2b8; color: white;">
                    <h5 class="modal-title">
                        <span class="material-icons align-middle" style="font-size: 20px; margin-right: 8px;">add_comment</span>
                        Add Comment to <span id="progress-title-modal">Progress</span>
                    </h5>
                    <button type="button" class="close btn-close-white" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="progress-comment-text"><strong>Your Comment</strong></label>
                        <textarea class="form-control" id="progress-comment-text" rows="4" placeholder="Add a comment about this progress..."></textarea>
                        <small class="form-text text-muted">This comment will be displayed under the progress item.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-progress-comment-btn" onclick="saveProgressComment()">Save Comment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Report List Modal -->
    <div class="modal fade" id="serviceReportListModal" tabindex="-1" aria-labelledby="serviceReportListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1200px; width: 95%;">
            <div class="modal-content" style="border-radius: 18px;">
                <div class="modal-header justify-content-center" style="background-color: #0066e6;">
                    <h4 class="modal-title w-100 text-center text-white" id="serviceReportListModalLabel">Service Report List</h4>
                    <button type="button" class="close close-modal-report position-absolute" data-dismiss="modal" aria-label="Close" style="right: 20px; top: 18px; color: white; background: none; border: none; cursor: pointer; font-size: 1.8rem; font-weight: 300; padding: 0; opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="px-3 py-2 border-bottom">
                        <div class="row">
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="input-group input-group-sm me-2" style="min-width: 240px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="serviceReportSearchIcon">🔍</span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm" id="service-report-search" placeholder="Search reports by ID, customer, appliance, or type" aria-label="Search reports" aria-describedby="serviceReportSearchIcon" autocomplete="off">
                                </div>
                                <div>
                                    <select id="service-report-filter" class="form-control form-control-sm" style="width: 160px;">
                                        <option value="All">All</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Under Repair">Under Repair</option>
                                        <option value="Unrepairable">Unrepairable</option>
                                        <option value="Release Out">Release Out</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0" style="font-family: 'Poppins', sans-serif;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="white-space: nowrap;">Report ID</th>
                                    <th>Customer</th>
                                    <th>Appliance</th>
                                    <th>Service Type</th>
                                    <th>Date In</th>
                                    <th>Date Released Out</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--Data are placed here-->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Report Modal -->
    <div class="modal fade" id="editReportModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form>
                    <div class="modal-header">
                        <h4 class="modal-title">Update Service Report</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="report_id" id="edit_report_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" id="edit_status" class="form-control" required>
                                        <option value="">Select Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Under Repair">Under Repair</option>
                                        <option value="Unrepairable">Unrepairable</option>
                                        <option value="Release Out">Release Out</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cost</label>
                                    <input type="number" name="cost" id="edit_cost" class="form-control" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date Repaired</label>
                                    <input type="date" name="date_repaired" id="edit_date_repaired" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date Delivered</label>
                                    <input type="date" name="date_delivered" id="edit_date_delivered" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <div class="modal fade" id="transactionDetailsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transaction Details</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="transaction-details">
                        <div class="form-row">
                            <div class="form-group">
                                <div class="info-label">Transaction ID</div>
                                <div class="info-value">TRX-001</div>
                            </div>
                            <div class="form-group">
                                <div class="info-label">Customer</div>
                                <div class="info-value">John Doe</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Report Modal -->
    <div class="modal fade" id="printReportModal" tabindex="-1" aria-labelledby="printReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 900px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printReportModalLabel">Print Service Report</h5>
                    <div class="ms-auto d-flex gap-2">
                        <button class="btn btn-secondary close-print-modal" type="button">Close</button>
                        <button id="print-report-btn" class="btn btn-primary" type="button">Print</button>
                    </div>
                </div>
                <div class="modal-body" style="max-height: 85vh; overflow-y: auto; padding: 0; background: #ffffff;">
                    <div id="print-report-body" class="print-section" style="background: #ffffff;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/jquery-3.3.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="javascripts/script_for_report.js"></script>
    <script type="text/javascript">
        // Customer search function
        function initCustomerSearch() {
            const $input = $('#customer-search');
            const $hiddenSelect = $('#customer-select');
            const $suggestions = $('#customer-suggestions');

            if (!$input.length) return;

            $suggestions.hide();

            let allowSuggestionsOnFocus = false;
            $input.on('input', function() {
                const text = $(this).val().trim();
                renderCustomerSuggestions(text);
            });

            $input.on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const first = $suggestions.find('.list-group-item').first();
                    if (first.length) {
                        first.trigger('click');
                    }
                }
            });

            $input.on('pointerdown touchstart mousedown', function() {
                allowSuggestionsOnFocus = true;
            });

            $input.on('focus', function() {
                if (allowSuggestionsOnFocus) {
                    renderCustomerSuggestions('');
                }
                allowSuggestionsOnFocus = false;
            });

            $hiddenSelect.on('change', function() {
                const text = $(this).find('option:selected').text();
                if (text && text !== 'Select Customer') {
                    console.debug('hiddenSelect change -> setting #customer-search to:', text);
                    $input.val(text);
                    console.debug('#customer-search value after hiddenSelect change:', $input.val());
                }
            });

            $input.on('blur', function() {
                setTimeout(() => $suggestions.hide(), 150);
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#customer-search, #customer-suggestions').length) {
                    $suggestions.hide();
                }
            });
        }

        function renderCustomerSuggestions(filterText) {
            const $suggestions = $('#customer-suggestions');
            const $input = $('#customer-search');

            if (!window.customersList || window.customersList.length === 0) {
                $suggestions.hide();
                return;
            }

            const text = (filterText || '').toLowerCase();
            let matches;
            if (!text) {
                matches = window.customersList.slice(0, 20);
            } else {
                matches = window.customersList.filter(c => c.name && c.name.toLowerCase().startsWith(text)).slice(0, 20);
            }

            if (matches.length === 0) {
                $suggestions.hide();
                return;
            }

            $suggestions.empty();
            matches.forEach(c => {
                const $item = $(`<button type="button" class="list-group-item list-group-item-action">${c.name}</button>`);
                $item.data('id', c.id);
                $item.on('click', function() {
                    setCustomerFromSuggestion(c.id, c.name);
                });
                $suggestions.append($item);
            });

            const $wrapper = $input.closest('.customer-search-wrapper');
            const wrapperWidth = $wrapper.length ? $wrapper.innerWidth() : $input.outerWidth();
            $suggestions.css({
                display: 'block',
                width: wrapperWidth + 'px'
            });
        }

        function setCustomerFromSuggestion(id, name) {
            const $hiddenSelect = $('#customer-select');
            const $input = $('#customer-search');
            
            console.log('Setting customer from suggestion - ID:', id, 'Name:', name);
            
            // Update the visible input field
            $input.val(name);
            console.log('Updated #customer-search to:', name);
            
            // Update or create option in hidden select
            let $option = $hiddenSelect.find(`option[value="${id}"]`);
            if ($option.length === 0) {
                console.log('Creating new option for customer ID:', id);
                $option = $(`<option></option>`).val(id).text(name);
                $hiddenSelect.append($option);
            }
            
            // Set the value and trigger change event
            $hiddenSelect.val(id);
            console.log('Set #customer-select value to:', id);
            console.log('Customer options in select:', $hiddenSelect.find('option').map((i, el) => $(el).val() + ':' + $(el).text()).get());
            
            // Trigger change to load appliances and other details
            $hiddenSelect.trigger('change');
            console.log('Triggered change event on #customer-select');
            
            // Hide suggestions
            $('#customer-suggestions').hide();
        }
        
        // Function to set dropdown by matching text content (for staff names)
        function setDropdownValueByText(selector, value) {
            if (!value) return;

            // Skip placeholder/default values
            const placeholders = ['Select Appliance', 'Select an Appliance', 'Select staff', 'Receptionist', 'Manager', 'Technician', 'Released By'];
            if (placeholders.includes(value.trim())) {
                console.log(`Skipping placeholder value "${value}" for dropdown ${selector}`);
                return;
            }

            const $dropdown = $(selector);
            const $options = $dropdown.find('option');

            // Step 1: Extract the actual username/name (remove role in parentheses)
            // Stored value might be "admin123 (Manager)" or "admin123 (Cashier)" - normalize old role names to new ones
            const storedNameMatch = value.match(/^([^(]+)/);
            let cleanStoredName = storedNameMatch ? storedNameMatch[1].trim() : value;
            const storedRoleMatch = value.match(/\(([^)]+)\)/);
            let storedRole = storedRoleMatch ? storedRoleMatch[1].trim() : '';

            // Normalize old role names to new ones for backward compatibility
            const roleMapping = {
                'Cashier': 'Secretary',
                'Accountant': 'Secretary',
                'cashier': 'Secretary',
                'accountant': 'Secretary'
            };
            if (roleMapping[storedRole]) {
                storedRole = roleMapping[storedRole];
            }

            cleanStoredName = cleanStoredName.toLowerCase();

            console.log(`Setting dropdown ${selector} - Original value: "${value}", Clean name: "${cleanStoredName}", Role: "${storedRole}"`);

            // Step 2: Try exact match on clean names (with role normalization)
            let $option = $options.filter((i, el) => {
                const optionText = $(el).text();
                const optionNameMatch = optionText.match(/^([^(]+)/);
                const cleanOptionName = optionNameMatch ? optionNameMatch[1].trim().toLowerCase() : optionText.toLowerCase();
                const optionRoleMatch = optionText.match(/\(([^)]+)\)/);
                const optionRole = optionRoleMatch ? optionRoleMatch[1].trim() : '';

                // Match by name, and if roles are specified, they should match (after normalization)
                if (cleanOptionName === cleanStoredName) {
                    if (!storedRole || !optionRole) {
                        // If either has no role specified, match on name alone
                        return true;
                    }
                    // Both have roles - they should match or storedRole maps to optionRole
                    return optionRole === storedRole || roleMapping[optionRole] === storedRole;
                }
                return false;
            });

            // Step 3: Try case-insensitive partial match if exact match failed
            if ($option.length === 0) {
                $option = $options.filter((i, el) => {
                    const optionText = $(el).text().toLowerCase();
                    return optionText.includes(cleanStoredName);
                });
            }

            // Set the selected option
            if ($option.length > 0) {
                $option.first().prop('selected', true);
                console.log(`Successfully set dropdown ${selector} to: "${$option.first().text()}"`);
            } else {
                console.warn(`Could not match value "${value}" in dropdown ${selector}. Available options:`, 
                    $options.map((i, el) => $(el).text()).get());
            }
        }

        // Status Progress Functions
        function updateStatusProgress(status) {
            const container = $('#status-progress-container');
            if (!status) {
                container.hide();
                return;
            }

            container.show();

            // Define status order and steps
            const statusFlow = {
                'Pending': { step: 1, isCompleted: false },
                'Under Repair': { step: 2, isCompleted: false },
                'Completed': { step: 3, isCompleted: true },
                'Unrepairable': { step: 2, isCompleted: false, isAlternate: true },
                'Release Out': { step: 3, isCompleted: true, isAlternate: true }
            };

            const currentStatus = statusFlow[status] || { step: 0, isCompleted: false };

            // Update progress steps display
            updateProgressSteps(currentStatus.step, currentStatus.isCompleted);

            // Load comments for this report before updating timeline
            const reportId = $('#report_id').val();
            if (reportId) {
                // Load comments first, then update timeline
                $.ajax({
                    url: '../backend/api/service_report_api.php?action=getProgressComments&report_id=' + reportId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            progressComments = {};
                            if (response.data && response.data.length > 0) {
                                response.data.forEach(function(comment) {
                                    if (!progressComments[comment.progress_key]) {
                                        progressComments[comment.progress_key] = [];
                                    }
                                    progressComments[comment.progress_key].push(comment);
                                });
                            }
                        }
                        // Update timeline after comments are loaded
                        updateProgressTimeline(status);
                    },
                    error: function() {
                        // Even if error, still update timeline
                        updateProgressTimeline(status);
                    }
                });
            } else {
                // No report ID, just update timeline
                updateProgressTimeline(status);
            }
        }

        function updateProgressSteps(currentStep, isCompleted) {
            // Reset all steps
            for (let i = 1; i <= 3; i++) {
                const $stepNumber = $(`#step-${i}`);
                const $stepLabel = $(`#step-${i}-label`);
                const $connector = $(`#connector-${i}`);

                $stepNumber.removeClass('active completed inactive');
                $stepLabel.removeClass('active completed');
                if ($connector.length) {
                    $connector.removeClass('active completed');
                }

                $stepNumber.addClass('inactive');
            }

            // Set completed steps
            for (let i = 1; i < currentStep; i++) {
                $(`#step-${i}`).removeClass('inactive').addClass('completed');
                $(`#step-${i}-label`).addClass('completed');
                if ($(`#connector-${i}`).length) {
                    $(`#connector-${i}`).removeClass('inactive').addClass('completed');
                }
            }

            // Set current step as active
            if (currentStep > 0 && currentStep <= 3) {
                $(`#step-${currentStep}`).removeClass('inactive').addClass('active');
                $(`#step-${currentStep}-label`).addClass('active');
            }

            // Set remaining steps
            for (let i = currentStep + 1; i <= 3; i++) {
                $(`#step-${i}`).removeClass('completed').addClass('inactive');
                $(`#step-${i}-label`).removeClass('completed');
            }
        }

        function updateProgressTimeline(status) {
            const timelineContainer = $('#progress-timeline-items');
            const currentDate = new Date().toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            let timelineHTML = '';

            // Map statuses to timeline events
            const timelineEvents = {
                'Pending': {
                    title: 'Received for Service',
                    description: 'Awaiting repair technician',
                    key: 'pending'
                },
                'Under Repair': {
                    title: 'Under Repair',
                    description: 'Technician is working on the unit',
                    key: 'under_repair'
                },
                'Unrepairable': {
                    title: 'Unit is Unrepairable',
                    description: 'Unable to repair - marked as unrepairable',
                    key: 'unrepairable'
                },
                'Release Out': {
                    title: 'Released to Customer',
                    description: 'Unit has been released out',
                    key: 'release_out'
                },
                'Completed': {
                    title: 'Repair Completed',
                    description: 'Service completed and ready for delivery',
                    key: 'completed'
                }
            };

            const event = timelineEvents[status] || { title: 'Status Unknown', description: '', key: 'unknown' };

            // Current status item with comment section
            timelineHTML += `
                <div class="timeline-item" id="timeline-${event.key}">
                    <div class="timeline-dot"></div>
                    <div class="timeline-text">
                        <strong>${event.title}</strong><br>
                        <span>${event.description}</span><br>
                        <small style="color: #999;">${currentDate}</small>
                        
                        <!-- Comment Button for this progress -->
                        <div style="margin-top: 10px;">
                            <button type="button" class="comment-btn" onclick="openProgressCommentModal('${event.key}', '${event.title}')">
                                <span class="material-icons" style="font-size: 14px;">add_comment</span>
                                <span>Comment</span>
                            </button>
                        </div>
                        
                        <!-- Comments Container for this progress -->
                        <div id="comments-${event.key}" class="progress-comments-list" style="margin-top: 10px;">
                            <div class="no-comments" style="font-size: 11px;">No comments</div>
                        </div>
                    </div>
                </div>
            `;

            // Add report info if available
            const reportId = $('#report_id').val();
            if (reportId) {
                timelineHTML += `
                    <div class="timeline-item" id="timeline-report-created" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e0e0e0;">
                        <div class="timeline-dot"></div>
                        <div class="timeline-text">
                            <strong>Report Created</strong><br>
                            <span>Service report initiated</span><br>
                            <small style="color: #999;">Report ID: #${reportId}</small>
                            
                            <!-- Comment Button for report creation -->
                            <div style="margin-top: 10px;">
                                <button type="button" class="comment-btn" onclick="openProgressCommentModal('report_created', 'Report Created')">
                                    <span class="material-icons" style="font-size: 14px;">add_comment</span>
                                    <span>Comment</span>
                                </button>
                            </div>
                            
                            <!-- Comments Container for report creation -->
                            <div id="comments-report_created" class="progress-comments-list" style="margin-top: 10px;">
                                <div class="no-comments" style="font-size: 11px;">No comments</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            timelineContainer.html(timelineHTML);
            
            // Refresh comments display for all items
            displayAllProgressComments();
        }

        function generateStatusProgressHTML(status) {
            // Define status order and steps
            const statusFlow = {
                'Pending': { step: 1, isCompleted: false },
                'Under Repair': { step: 2, isCompleted: false },
                'Completed': { step: 3, isCompleted: true },
                'Unrepairable': { step: 2, isCompleted: false, isAlternate: true },
                'Release Out': { step: 3, isCompleted: true, isAlternate: true }
            };

            const currentStatus = statusFlow[status] || { step: 0, isCompleted: false };
            let html = '<div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">';

            // Define steps
            const steps = [
                { num: 1, label: 'Pending' },
                { num: 2, label: 'Under Repair' },
                { num: 3, label: 'Completed' }
            ];

            // Generate progress visualization
            steps.forEach((step, index) => {
                let stepColor = '#e0e0e0';
                let textColor = '#666';
                let fontWeight = 'normal';

                if (step.num < currentStatus.step) {
                    stepColor = '#28a745';
                    textColor = '#28a745';
                    fontWeight = 'bold';
                } else if (step.num === currentStatus.step) {
                    stepColor = '#ffc107';
                    textColor = '#ffc107';
                    fontWeight = 'bold';
                }

                // Step circle
                html += `
                    <div style="display: flex; flex-direction: column; align-items: center; flex: 1;">
                        <div style="width: 28px; height: 28px; border-radius: 50%; background-color: ${stepColor}; display: flex; align-items: center; justify-content: center; color: ${stepColor === '#28a745' ? 'white' : '#000'}; font-weight: bold; font-size: 11px; margin-bottom: 4px;">
                            ${step.num < currentStatus.step ? '✓' : step.num}
                        </div>
                        <span style="font-size: 10px; color: ${textColor}; font-weight: ${fontWeight}; text-align: center; max-width: 60px;">${step.label}</span>
                    </div>
                `;

                // Connector line
                if (index < steps.length - 1) {
                    let connectorColor = '#e0e0e0';
                    if (step.num < currentStatus.step) {
                        connectorColor = '#28a745';
                    }
                    html += `<div style="flex: 0.5; height: 2px; background-color: ${connectorColor}; margin-top: 10px; margin-bottom: 10px;"></div>`;
                }
            });

            html += '</div>';
            return html;
        }

        $(document).ready(function() {
            // Initialize the application
            initializeServiceReport();
            bindEventHandlers();
        });

        function initializeServiceReport() {
            // Load initial data
            loadCustomers();
            loadParts();
            loadStaff();
            loadServiceReports();
            
            // Initialize customer search after a brief delay to ensure DOM is ready
            setTimeout(function() {
                initCustomerSearch();
            }, 100);
        }

        function bindEventHandlers() {
            // Event handlers for form interactions
            $(document).on('change', '#customer-select', function() {
                const customerId = $(this).val();
                if (customerId) {
                    console.log('Customer selected:', customerId);
                    
                    // Load appliances for the customer
                    loadAppliances(customerId);
                    
                    // Load customer details
                    loadCustomerDetails(customerId);
                    
                    // NOTE: Date In is now set based on the selected appliance,
                    // not from previous service reports. This allows staff to
                    // work with appliances added at different times.
                    console.log('Date In will be set when appliance is selected');
                }
            });

            $(document).on('click', '#add-part-btn', function() {
                addPartRow();
            });

            $(document).on('click', '.remove-part', function() {
                $(this).closest('.parts-row').remove();
                calculateTotals();
            });

            $(document).on('change', '#appliance-select', function() {
                const applianceId = $(this).val();
                console.log('Appliance selected:', applianceId);
                
                if (applianceId && window.appliancesData) {
                    // Find the selected appliance by ID
                    const selectedAppliance = window.appliancesData.find(a => {
                        const aId = a.appliance_id || a.id;
                        return aId == applianceId;
                    });
                    
                    if (selectedAppliance) {
                        console.log('Selected appliance data:', selectedAppliance);
                        
                        // Try multiple date field names since API might return different formats
                        const applianceDate = selectedAppliance.date_in 
                            || selectedAppliance.date_created 
                            || selectedAppliance.dateIn
                            || selectedAppliance.date_added
                            || selectedAppliance.registration_date;
                        
                        if (applianceDate) {
                            // Update date_in based on appliance's registration/creation date
                            $('#date-in').val(applianceDate);
                            console.log('✅ Date In auto-filled from appliance:', applianceDate);
                        } else {
                            console.warn('⚠️ No date field found in appliance data. Available fields:', Object.keys(selectedAppliance));
                        }
                    } else {
                        console.warn('⚠️ Could not find selected appliance in data');
                    }
                } else {
                    console.log('No appliance selected or appliances data not available');
                }
            });

            $(document).on('change', '.part-select, .quantity-input', function() {
                const $row = $(this).closest('.parts-row');
                const quantity = parseFloat($row.find('.quantity-input').val() || 0) || 0;
                const unitPrice = parseFloat($row.find('.part-select option:selected').data('price') || 0) || 0;
                const amount = quantity * unitPrice;
                $row.find('.amount-input').val(amount.toFixed(2));
                calculateTotals();
            });

            $(document).on('change', '#total-serviceCharge, #labor-amount, #pullout-delivery', function() {
                calculateTotals();
            });

            $(document).on('click', '#submit-report-btn', function(e) {
                e.preventDefault();
                submitServiceReport();
            });
        }

        function showAlert(type, message) {
            try {
                $('.alert-notification').remove();
                const alertHtml = `
                    <div class="alert-notification alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                $('body').prepend(alertHtml);
                setTimeout(() => {
                    const $alert = $('.alert-notification');
                    if ($alert.length) {
                        $alert.alert('close');
                    }
                }, 5000);
            } catch (error) {
                console.error('Error showing alert:', error);
            }
        }

        function loadCustomers() {
            $.ajax({
                url: '../backend/api/customer_appliance_api.php?action=getAllCustomers&itemsPerPage=100',
                method: 'GET',
                dataType: 'json',
                timeout: 10000,
                success: function(data) {
                    console.log('Customer API Response:', data);
                    const select = $('#customer-select');
                    select.empty().append('<option value="">Select Customer</option>');
                    
                    // Extract customers from nested data structure - handle paginated response
                    let customers = [];
                    if (data.success && data.data) {
                        if (Array.isArray(data.data)) {
                            customers = data.data;
                        } else if (data.data.customers && Array.isArray(data.data.customers)) {
                            customers = data.data.customers;
                        } else if (data.data.data && Array.isArray(data.data.data)) {
                            customers = data.data.data;
                        }
                    }
                    
                    // Also populate the search suggestions list
                    window.customersList = [];
                    
                    if (customers.length > 0) {
                        customers.forEach(function(customer) {
                            const id = customer.customer_id || customer.id;
                            const name = customer.FullName || customer.full_name || customer.name || (customer.first_name ? customer.first_name + ' ' + customer.last_name : 'Unknown');
                            const phone = customer.phone_no || customer.phone || '';
                            const address = customer.address || '';
                            
                            // Add to dropdown
                            select.append(`<option value="${id}">${name}</option>`);
                            
                            // Add to search suggestions list
                            window.customersList.push({
                                id: id,
                                name: name,
                                phone: phone,
                                address: address
                            });
                        });
                    } else {
                        console.warn('No customers in response:', data);
                    }
                    
                    console.log('Customers list populated:', window.customersList);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading customers:', error, 'Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    if (status !== 'abort') {
                        console.warn('Customer API failed - continuing without customers');
                        // Initialize empty list so search doesn't break
                        window.customersList = [];
                    }
                }
            });
        }

        function loadCustomerDetails(customerId) {
            // Find customer from the customersList
            if (!window.customersList || window.customersList.length === 0) {
                console.warn('Customer list not available');
                return;
            }
            
            const customer = window.customersList.find(c => c.id == customerId);
            if (customer) {
                console.log('✅ Customer details found:', customer);
                // Customer info could be displayed in other fields if needed
                // For now, the dropdown already shows the customer is selected
            } else {
                console.warn('❌ Customer not found in list for ID:', customerId);
                console.log('Available customer IDs:', window.customersList.map(c => c.id).join(', '));
            }
        }

        function loadLatestCustomerDateIn(customerId) {
            // Get customer name from the selected option
            const customerName = $('#customer-select').find('option:selected').text();
            
            // Fetch all service reports and find the latest one for this customer
            $.ajax({
                url: '../backend/api/service_api.php?action=getAll&limit=1000',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Service reports response:', data);
                    if (data.success && Array.isArray(data.data)) {
                        // Filter reports for this customer by matching customer name
                        const customerReports = data.data.filter(r => {
                            const reportCustName = (r.customer_name || '').trim();
                            const selectedCustName = (customerName || '').trim();
                            const nameMatch = reportCustName.toLowerCase() === selectedCustName.toLowerCase();
                            console.log('Comparing customer names:', reportCustName, '===', selectedCustName, '?', nameMatch);
                            return nameMatch;
                        });
                        
                        console.log('Found', customerReports.length, 'reports for customer:', customerName);
                        
                        if (customerReports.length > 0) {
                            // Sort by date_in to get the most recent
                            customerReports.sort((a, b) => new Date(b.date_in) - new Date(a.date_in));
                            const latestReport = customerReports[0];
                            
                            if (latestReport.date_in) {
                                // Use the date from the most recent report
                                $('#date-in').val(latestReport.date_in);
                                console.log('Date In filled from latest customer record:', latestReport.date_in);
                            }
                        } else {
                            // If no previous reports for this customer, use today's date
                            console.log('No previous reports found, using today date');
                            const today = new Date().toISOString().split('T')[0];
                            $('#date-in').val(today);
                        }
                    } else {
                        // Fallback to today's date if API fails
                        console.warn('Invalid response from service reports API');
                        const today = new Date().toISOString().split('T')[0];
                        $('#date-in').val(today);
                    }
                },
                error: function(xhr, status, error) {
                    console.warn('Could not load service reports, using today:', error);
                    // Fallback to today's date if API fails
                    const today = new Date().toISOString().split('T')[0];
                    $('#date-in').val(today);
                }
            });
        }

        function loadAppliances(customerId, applianceIdToSelect = null) {
            return $.ajax({
                url: '../backend/api/customer_appliance_api.php?action=getAppliancesByCustomerId&customerId=' + customerId,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Appliances API Response:', data);
                    const select = $('#appliance-select');
                    select.empty().append('<option value="">Select Appliance</option>');
                    
                    let appliances = [];
                    if (data.success && data.data) {
                        if (Array.isArray(data.data)) {
                            appliances = data.data;
                        } else if (data.data.appliances && Array.isArray(data.data.appliances)) {
                            appliances = data.data.appliances;
                        } else if (data.data.data && Array.isArray(data.data.data)) {
                            appliances = data.data.data;
                        }
                    }
                    
                    if (appliances.length > 0) {
                        // Store appliances data globally for use in change handlers
                        window.appliancesData = appliances;
                        
                        appliances.forEach(function(appliance) {
                            const id = appliance.appliance_id || appliance.applianceId || appliance.id || appliance.applianceId;
                            // Build name to match format used in service reports: Brand - SerialNo (Category)
                            let name = '';
                            
                            // Check if we have brand and category (required for proper format)
                            if (appliance.brand) {
                                const serial = appliance.serial_no || appliance.model_no || 'No Serial';
                                const category = appliance.category || appliance.model_no || 'No Model';
                                name = `${appliance.brand} - ${serial} (${category})`;
                            } else if (appliance.product) {
                                name = appliance.product;
                            } else {
                                name = appliance.appliance_name || appliance.name || ('Appliance ' + (id || ''));
                            }
                            
                            select.append(`<option value="${id}">${name}</option>`);
                        });
                        
                        // Auto-select appliance if ID provided
                        if (applianceIdToSelect) {
                            select.val(applianceIdToSelect);
                            console.log('Auto-selected appliance ID:', applianceIdToSelect);
                            // Trigger change event to update date if needed
                            select.trigger('change');
                        }
                    } else {
                        console.warn('No appliances found for this customer');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading appliances:', error);
                    if (status !== 'abort') {
                        console.warn('Appliances API failed - continuing without appliances');
                    }
                }
            });
        }

        function loadParts() {
            $.ajax({
                url: '../backend/api/parts_api.php?action=getAllParts',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Parts API Response:', data);
                    if (data.success && data.data) {
                        let parts = [];
                        if (Array.isArray(data.data)) {
                            parts = data.data;
                        } else if (data.data.parts && Array.isArray(data.data.parts)) {
                            parts = data.data.parts;
                        } else if (data.data.data && Array.isArray(data.data.data)) {
                            parts = data.data.data;
                        }
                        $('body').data('parts', parts);
                        // Populate any existing part-select elements in the form
                        $('.part-select').each(function() {
                            const $sel = $(this);
                            $sel.empty().append('<option value="">Select Part</option>');
                            parts.forEach(function(part) {
                                const pid = part.part_id || part.id || part.partId || part.ID;
                                const price = part.price || part.part_price || 0;
                                const partName = part.part_no || part.name || part.part_name || `Part ${pid}`;
                                const stock = part.quantity_stock || 0;
                                const label = `${partName} (Stock: ${stock})`;
                                $sel.append(`<option value="${pid}" data-price="${price}">${label}</option>`);
                            });
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading parts:', error, xhr.status, xhr.responseText);
                    if (status !== 'abort') {
                        console.warn('Parts API failed - continuing without parts');
                    }
                }
            });
        }

        function loadStaff() {
            $.ajax({
                url: '../backend/api/staff_api.php?action=getAllStaffs',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Staff API Response:', data);
                    if (data.success && data.data) {
                        let staffList = [];
                        if (Array.isArray(data.data)) {
                            staffList = data.data;
                        } else if (data.data.staffs && Array.isArray(data.data.staffs)) {
                            staffList = data.data.staffs;
                        } else if (data.data.data && Array.isArray(data.data.data)) {
                            staffList = data.data.data;
                        }
                        populateStaffSelects(staffList);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading staff:', error, xhr.status, xhr.responseText);
                    if (status !== 'abort') {
                        console.warn('Staff API failed - continuing without staff list');
                    }
                }
            });
        }

        // Load service reports and populate the modal table
        function loadServiceReports() {
            $.ajax({
                url: '../backend/api/service_api.php?action=getAll',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    console.log('Service Reports API Response:', res);

                    if (!res.success || !res.data || !Array.isArray(res.data)) {
                        allServiceReports = [];
                        renderServiceReports([]);
                        updateBadgeFromReports([]);
                        return;
                    }

                    // Store all reports for filtering
                    allServiceReports = res.data;
                    
                    // Reset search/filter and render all reports
                    $('#service-report-search').val('');
                    $('#service-report-filter').val('All');
                    
                    renderServiceReports(allServiceReports);
                    updateBadgeFromReports(allServiceReports);

                    updateBadgeFromReports(res.data);
                },
                error: function(xhr) {
                    console.error('Error loading service reports:', xhr.responseText);
                    $('#serviceReportListModal tbody').html('<tr><td colspan="8">Failed to load reports</td></tr>');
                }
            });
        }

        // Refresh reports without resetting filters
        window.refreshServiceReports = function() {
            return $.ajax({
                url: '../backend/api/service_api.php?action=getAll',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    console.log('Refreshing Service Reports:', res);

                    if (!res.success || !res.data || !Array.isArray(res.data)) {
                        return;
                    }

                    // Store all reports for filtering
                    allServiceReports = res.data;
                    
                    // Re-apply current filters
                    applyStatusAndSearch();
                    updateBadgeFromReports(allServiceReports);
                },
                error: function(xhr) {
                    console.error('Error refreshing service reports:', xhr.responseText);
                }
            });
        };

        // Optimistically update a report in the local cache
        window.updateReportInCache = function(reportId, updatedData) {
            const index = allServiceReports.findIndex(r => r.report_id == reportId);
            if (index !== -1) {
                // Merge updated data with existing report
                allServiceReports[index] = { ...allServiceReports[index], ...updatedData };
                console.log('Optimistically updated report in cache:', reportId);
                
                // Re-render with current filters
                applyStatusAndSearch();
                updateBadgeFromReports(allServiceReports);
            }
        };

        function updateBadgeFromReports(reports) {
            const $badge = $('#report-badge');
            const hasPending = Array.isArray(reports) && reports.some(r => {
                let status = r.status;
                // Map numeric status codes to text
                if (typeof status === 'number' || /^\d+$/.test(status)) {
                    const statusMap = {'0': 'Completed', '1': 'Pending', '2': 'Under Repair', '3': 'Unrepairable', '4': 'Release Out'};
                    status = statusMap[String(status)] || status;
                }
                return status === 'Pending';
            });
            if (hasPending) {
                $badge.show().addClass('blink-badge');
            } else {
                $badge.hide().removeClass('blink-badge');
            }
        }

        function populateStaffSelects(staffData) {
            const roles = {
                'receptionist-select': 'Secretary',
                'manager-select': 'Manager',
                'technician-select': 'Technician',
                'released-by-select': 'Secretary'
            };

            Object.entries(roles).forEach(([selectId, role]) => {
                const select = $('#' + selectId);
                select.empty().append(`<option value=\"\">Select ${role}</option>`);
                staffData.forEach(function(staff) {
                    // Normalize and support multiple field names for id, name and role
                    const staffId = staff.staff_id || staff.id || staff.staffId || staff.ID;
                    const staffName = staff.full_name || staff.fullName || staff.name || staff.staff_name || staff.username;
                    const staffRole = (staff.role || staff.position || staff.job_title || '').toString().toLowerCase();
                    if (staffRole === role.toString().toLowerCase()) {
                        select.append(`<option value=\"${staffId}\">${staffName}</option>`);
                    }
                });
            });
        }

        // Allow custom close button to hide the service report list modal
        $(document).on('click', '.close-modal-report', function(e) {
            e.preventDefault();
            $('#serviceReportListModal').modal('hide');
            setTimeout(() => { $('.modal-backdrop').remove(); $('body').removeClass('modal-open'); }, 200);
        });

        // Use event delegation for action buttons; hide the list modal when an action is clicked
        $(document).on('click', '.edit-report', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const reportId = $(this).data('id');
            console.log('Edit button clicked for report ID:', reportId);
            
            // Force close the modal with multiple methods
            const $modal = $('#serviceReportListModal');
            $modal.modal('hide');
            $modal.removeClass('show');
            $modal.css('display', 'none');
            $modal.attr('aria-hidden', 'true');
            
            // Immediate and delayed cleanup of modal backdrop and body classes
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({
                'overflow': '',
                'padding-right': ''
            });
            
            // Additional cleanup after brief delay
            setTimeout(() => { 
                $('.modal-backdrop').remove(); 
                $('body').removeClass('modal-open').css({
                    'overflow': '',
                    'padding-right': ''
                });
                console.log('Modal forcefully closed and cleaned up');
            }, 100);
            
            // Load report for editing
            loadReportForEditing(reportId);
        });

        $(document).on('click', '.delete-report', function(e) {
            e.preventDefault();
            const reportId = $(this).data('id');
            $('#serviceReportListModal').modal('hide');
            setTimeout(() => { $('.modal-backdrop').remove(); $('body').removeClass('modal-open'); }, 300);
            deleteReport(reportId);
        });

        // Print report button handler
        $(document).on('click', '.print-report', async function(e) {
            e.preventDefault();
            const reportId = $(this).data('id');
            if (!reportId) return;
            // hide the list modal immediately
            $('#serviceReportListModal').modal('hide');
            setTimeout(() => { $('.modal-backdrop').remove(); $('body').removeClass('modal-open'); }, 300);
            await renderPrintModal(reportId);
            $('#printReportModal').modal('show');
        });

        // Print button inside modal
        $(document).on('click', '#print-report-btn', function() {
            window.print();
        });

        // Close print modal button handler
        $(document).on('click', '.close-print-modal', function() {
            $('#printReportModal').modal('hide');
            setTimeout(() => { 
                $('.modal-backdrop').remove(); 
                $('body').removeClass('modal-open').css({
                    'overflow': '',
                    'padding-right': ''
                });
            }, 100);
        });

        // Assign-to-me button handler
        $(document).on('click', '.assign-report', function(e) {
            e.preventDefault();
            const reportId = $(this).data('id');
            const techName = window.currentStaffName || '';
            if (!techName) {
                showAlert('error', 'Cannot determine your staff name for assignment');
                return;
            }

            if (!confirm('Assign report #' + reportId + ' to you (' + techName + ')?')) return;

            $.ajax({
                url: '../backend/api/service_api.php?action=assign',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ report_id: reportId, technician: techName }),
                success: function(res) {
                    if (res && res.success) {
                        showAlert('success', 'Report assigned successfully');
                        loadServiceReports();
                        // notify other tabs/dashboards
                        try { localStorage.setItem('dashboardRefreshNeeded', Date.now()); } catch (e) {}
                    } else {
                        showAlert('error', 'Assign failed: ' + (res.message || 'Unknown'));
                    }
                },
                error: function(xhr) {
                    showAlert('error', 'Assign request failed');
                }
            });
        });

        // Service report list search input
        $(document).on('input', '#service-report-search', function() {
            filterServiceReports($(this).val());
        });

        // Status change handler - update progress
        $(document).on('change', '#create_status, select[name="status"]', function() {
            const status = $(this).val();
            updateStatusProgress(status);
            
            // Auto-fill date_pulled_out when status changes to 'Completed'
            if (status === 'Completed') {
                const today = new Date().toISOString().split('T')[0];
                // Note: Date input field has been removed from form, but auto-fill logic preserved
                // for potential future use or if field is restored
            }
        });

        $(document).on('change', '#service-report-filter', function() {
            // Re-render based on the selected status filter and current search query
            const q = $('#service-report-search').val();
            applyStatusAndSearch();
            if (q) {
                filterServiceReports(q);
            }
        });

        // Modal on show - load service reports
        $('#serviceReportListModal').on('show.bs.modal', loadServiceReports);

        // Ensure modal closes properly when clicking close button or backdrop
        $('#serviceReportListModal').on('hidden.bs.modal', function() {
            $('body').removeClass('modal-open');
        });

        function addPartRow() {
            const partSelect = $('<select class="form-control part-select" name="part_used[]"><option value="">Select Part</option></select>');
            
            const parts = $('body').data('parts') || [];
            parts.forEach(function(part) {
                const pid = part.part_id || part.id || part.partId || part.ID;
                const price = part.price || part.part_price || 0;
                const partName = part.part_no || part.name || part.part_name || `Part ${pid}`;
                const stock = part.quantity_stock || 0;
                const label = `${partName} (Stock: ${stock})`;
                partSelect.append(`<option value="${pid}" data-price="${price}">${label}</option>`);
            });

            const row = `
                <div class="row mb-2 align-items-center g-2 parts-row">
                    <div class="col-md-3 pe-1">
                        ${partSelect[0].outerHTML}
                    </div>
                    <div class="col-md-2 px-1">
                        <input type="number" class="form-control quantity-input" name="quantity[]" placeholder="Quantity" min="1">
                    </div>
                    <div class="col-md-2 px-1">
                        <input type="number" class="form-control amount-input" name="amount[]" placeholder="Amount" readonly>
                    </div>
                    <div class="col-md-1 px-1 d-flex justify-content-end align-items-center ml-3">
                        <button type="button" class="btn btn-danger remove-part d-flex align-items-center justify-content-center">
                            <span class="material-icons" style="font-size: 1.2em;">delete</span>
                        </button>
                    </div>
                </div>
            `;

            $('#parts-container').append(row);
        }

        function calculateTotals() {
            // Sum part amounts (amount-input already holds price * qty)
            let partsTotal = 0;
            $('.parts-row').each(function() {
                const amount = parseFloat($(this).find('.amount-input').val() || 0) || 0;
                partsTotal += amount;
            });

            // Update parts charge display
            $('[name="parts_charge"]').val(partsTotal.toFixed(2));

            // Service charge (could be a separate field); read it as numeric
            const serviceCharge = parseFloat($('#total-serviceCharge').val() || 0) || 0;
            const labor = parseFloat($('#labor-amount').val() || 0) || 0;
            const pullout = parseFloat($('#pullout-delivery').val() || 0) || 0;

            // Grand total = service charge + labor + pullout + parts total
            const grandTotal = serviceCharge + labor + pullout + partsTotal;

            // Update total amount display fields
            $('#total-amount').val(grandTotal.toFixed(2));
            $('#total-amount-2').val(grandTotal.toFixed(2));
        }

        function submitServiceReport() {
            // Build a JSON payload because the API expects JSON in the body
            const payload = {};

            // Basic fields
            payload.customer_name = $('#customer-select').find('option:selected').text() || $('#customer-select').val();
            payload.customer_id = $('#customer-select').val();
            payload.appliance_name = $('#appliance-select').find('option:selected').text() || $('#appliance-select').val();
            payload.appliance_id = $('#appliance-select').val();
            payload.date_in = $('#date-in').val();
            payload.status = $('#create_status').val() || $('select[name="status"]').first().val();
            payload.dealer = $('input[name="dealer"]').val() || '';
            payload.dop = $('input[name="dop"]').val() || null;
            payload.findings = $('input[name="findings"]').val() || '';
            payload.remarks = $('input[name="remarks"]').val() || '';
            payload.location = ['shop'];

            // Service types from checkboxes
            const serviceTypes = [];
            if ($('#installation').is(':checked')) serviceTypes.push('installation');
            if ($('#repair').is(':checked')) serviceTypes.push('repair');
            if ($('#cleaning').is(':checked')) serviceTypes.push('cleaning');
            if ($('#checkup').is(':checked')) serviceTypes.push('checkup');
            if (serviceTypes.length === 0) serviceTypes.push('repair');
            payload.service_types = serviceTypes;

            // Charges
            payload.service_charge = parseFloat($('#total-serviceCharge').val() || 0) || 0;
            payload.labor = parseFloat($('#labor-amount').val() || 0) || 0;
            payload.pullout_delivery = parseFloat($('#pullout-delivery').val() || 0) || 0;
            payload.parts_total_charge = parseFloat($('[name="parts_charge"]').val() || 0) || 0;
            // total amount (service + labor + parts + pullout)
            payload.total_amount = parseFloat($('#total-amount').val() || 0) || 0;

            // Dates
            payload.date_repaired = $('input[name="date_repaired"]').val() || null;
            payload.date_delivered = $('input[name="date_delivered"]').val() || null;

            // Complaint
            payload.complaint = $('#complaint').val() || '';

            // Staff selections
            payload.receptionist = $('#receptionist-select').val() || '';
            payload.manager = $('#manager-select').val() || '';
            payload.technician = $('#technician-select').val() || '';
            payload.released_by = $('#released-by-select').val() || '';

            // Parts array: gather each parts-row and compute parts total
            payload.parts = [];
            let partsTotal = 0;
            $('#parts-container .parts-row').each(function() {
                const partId = $(this).find('.part-select').val();
                const partName = $(this).find('.part-select option:selected').text();
                const qty = parseInt($(this).find('.quantity-input').val() || 0, 10) || 0;
                const unitPrice = parseFloat($(this).find('.part-select option:selected').data('price') || 0) || 0;
                const rowAmount = parseFloat($(this).find('.amount-input').val() || 0) || (unitPrice * qty);
                partsTotal += rowAmount;
                if (partId && qty > 0) {
                    payload.parts.push({
                        part_id: partId,
                        part_name: partName,
                        quantity: qty,
                        unit_price: unitPrice
                    });
                }
            });

            // Refresh parts charge and compute grand total from numeric components to avoid mismatch
            payload.parts_total_charge = partsTotal;
            payload.service_charge = parseFloat($('#total-serviceCharge').val() || 0) || 0;
            payload.labor = parseFloat($('#labor-amount').val() || 0) || 0;
            payload.pullout_delivery = parseFloat($('#pullout-delivery').val() || 0) || 0;
            payload.total_amount = payload.service_charge + payload.labor + payload.pullout_delivery + partsTotal;

            // Make sure required fields exist to avoid 400
            if (!payload.customer_name || !payload.appliance_name || !payload.date_in || !payload.status) {
                showAlert('error', 'Please fill required fields: customer, appliance, date in, status');
                return;
            }

            // Check if this is an update or create
            const reportId = $('#report_id').val();
            const action = reportId ? 'update' : 'create';
            const method = reportId ? 'PUT' : 'POST';
            const url = reportId ? 
                ('/backend/api/service_api.php?action=update&id=' + reportId) : 
                '/backend/api/service_api.php?action=create';

            $.ajax({
                url: url,
                method: method,
                data: JSON.stringify(payload),
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                success: async function(response) {
                    const successMsg = reportId ? 'Service report updated successfully!' : 'Service report created successfully!';
                    showAlert('success', successMsg);
                    
                    const finalReportId = response.data ? (response.data.id || response.data.report_id || reportId) : reportId;
                    const currentStatus = payload.status;
                    
                    // If updating, reload the report and its comments to keep everything fresh
                    if (reportId) {
                        // If status is Completed, create a transaction
                        if (currentStatus === 'Completed') {
                            console.log('Status is Completed, creating transaction...');
                            await createTransactionFromReport(reportId);
                        }
                        // Reload the report for editing to keep it displayed with all comments
                        loadReportForEditing(reportId);
                    } else {
                        // If creating new, clear the form
                        $('#serviceReportForm')[0].reset();
                        $('#report_id').val('');
                        $('#submit-report-btn').text('Create Report').css('background-color', '#0066e6');
                        loadCustomers();
                        loadServiceReports(); // Refresh the list
                        $('#appliance-select').empty().append('<option value="">Select Appliance</option>');
                    }
                    
                    // Update dashboard in real-time
                    updateDashboardData();
                },
                error: function(xhr) {
                    let msg = reportId ? 'Error updating service report' : 'Error creating service report';
                    try {
                        const res = xhr.responseJSON || JSON.parse(xhr.responseText || '{}');
                        msg = res.message || msg;
                    } catch (e) {
                        // ignore parse errors
                    }
                    showAlert('error', msg);
                    console.error('Service create error response:', xhr.responseText);
                }
            });
        }

        // Load report for editing (populate the form with existing data)
        function loadReportForEditing(reportId) {
            console.log('Starting loadReportForEditing for reportId:', reportId);
            
            $.ajax({
                url: '../backend/api/service_api.php?action=getById&id=' + reportId,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    console.log('Raw API Response:', res);
                    if (res.success && res.data) {
                        const report = res.data;
                        console.log('Report data received:', report);
                        
                        // Populate form with data immediately
                        console.log('Populating form with data...');
                            
                            // Set report ID
                            $('#report_id').val(reportId);
                            console.log('Report ID set to:', reportId);
                            
                            // Set customer - try ID first, then fallback to name matching
                            const custId = report.customer_id || report.cust_id || report.customer;
                            console.log('Customer ID from report:', custId, 'Customer name:', report.customer_name);
                            let selectedCustId = null;
                            
                            // Try to set by ID first
                            if (custId) {
                                $('#customer-select').val(custId);
                                selectedCustId = $('#customer-select').val(); // Get the actual value after setting
                                console.log('Customer ID set to:', custId, 'Selected value:', selectedCustId);
                            }
                            
                            // If ID didn't work, find option by name
                            if (!selectedCustId && report.customer_name) {
                                $('#customer-select option').each(function() {
                                    if ($(this).text().includes(report.customer_name)) {
                                        $(this).prop('selected', true);
                                        selectedCustId = $(this).val();
                                        console.log('Customer selected by name:', report.customer_name, 'ID:', selectedCustId);
                                        return false;
                                    }
                                });
                            }
                            
                            // Update visible customer search input with the customer name
                            if (report.customer_name) {
                                $('#customer-search').val(report.customer_name);
                                console.log('Customer search input updated to:', report.customer_name);
                            }
                            
                            // Trigger change event to sync hidden select and visible input
                            if (selectedCustId) {
                                $('#customer-select').trigger('change');
                                console.log('Customer select change event triggered');
                            }
                            
                            // Set basic fields
                            $('#date-in').val(report.date_in || '');
                            
                            // Set status with validation
                            const statusValue = report.status || '';
                            console.log('Setting status to:', statusValue);
                            $('select[name="status"]').val(statusValue);
                            if ($('select[name="status"]').val() !== statusValue && statusValue) {
                                console.warn('Status value not found in dropdown options:', statusValue);
                            }
                            // Update status progress display
                            updateStatusProgress(statusValue);
                            
                            $('input[name="dealer"]').val(report.dealer || '');
                            $('input[name="dop"]').val(report.dop || '');
                            $('input[name="findings"]').val(report.findings || '');
                            $('input[name="remarks"]').val(report.remarks || '');
                            console.log('Basic fields populated');
                            
                            // Populate complaint and charges
                            $('#complaint').val(report.complaint || '');
                            $('#labor-amount').val(parseFloat(report.labor) || 0);
                            $('#pullout-delivery').val(parseFloat(report.pullout_delivery) || 0);
                            $('input[name="date_repaired"]').val(report.date_repaired || '');
                            $('input[name="date_delivered"]').val(report.date_delivered || '');
                            console.log('Complaint and charges populated');
                            
                            // Populate location checkboxes (shop, field, out_wty)
                            $('#shop, #field, #out_wty').prop('checked', false);
                            console.log('Location data:', report.location);
                            if (report.location && Array.isArray(report.location)) {
                                report.location.forEach(function(loc) {
                                    const locId = loc.toLowerCase().trim();
                                    console.log('Checking location:', locId);
                                    $('#' + locId).prop('checked', true);
                                });
                            }
                            console.log('Location checkboxes populated');
                            
                            // Clear and populate service type checkboxes
                            $('#installation, #repair, #cleaning, #checkup').prop('checked', false);
                            console.log('Service types before population:', report.service_types);
                            
                            if (report.service_types) {
                                let typesToCheck = [];
                                if (Array.isArray(report.service_types)) {
                                    typesToCheck = report.service_types;
                                } else if (typeof report.service_types === 'string') {
                                    typesToCheck = report.service_types.split(',').map(t => t.trim());
                                }
                                
                                console.log('Types to check:', typesToCheck);
                                typesToCheck.forEach(function(type) {
                                    const typeId = type.toLowerCase().trim();
                                    console.log('Checking:', typeId);
                                    $('#' + typeId).prop('checked', true);
                                });
                            }
                            console.log('Service type checkboxes populated');
                            
                            // Populate staff by matching names (backend now returns 'username (Role)' format)
                            console.log('Staff data - receptionist:', report.receptionist, 'manager:', report.manager, 'technician:', report.technician, 'released_by:', report.released_by);
                            
                            // Set staff dropdowns using the name matching function
                            if (report.receptionist) {
                                setDropdownValueByText('#receptionist-select', report.receptionist);
                                console.log('Receptionist set to:', report.receptionist);
                            }
                            
                            // For manager
                            if (report.manager) {
                                setDropdownValueByText('#manager-select', report.manager);
                                console.log('Manager set to:', report.manager);
                            }
                            
                            // For technician
                            if (report.technician) {
                                setDropdownValueByText('#technician-select', report.technician);
                                console.log('Technician set to:', report.technician);
                            }
                            
                            // For released by
                            if (report.released_by) {
                                setDropdownValueByText('#released-by-select', report.released_by);
                                console.log('Released by set to:', report.released_by);
                            }
                            console.log('Staff selections populated');
                            
                            // Load appliances for customer
                            const finalCustId = selectedCustId || custId || $('#customer-select').val();
                            console.log('Final customer ID for loading appliances:', finalCustId);
                            
                            if (finalCustId) {
                                const appId = report.appliance_id || report.app_id;
                                console.log('Appliance ID to select:', appId, 'Appliance name:', report.appliance_name);
                                
                                // Load appliances and auto-select the correct one
                                loadAppliances(finalCustId, appId).done(function() {
                                    console.log('Appliances loaded, checking selection...');
                                    const currentVal = $('#appliance-select').val();
                                    console.log('Current appliance select value:', currentVal);
                                    
                                    // If ID selection didn't work, try by name
                                    if (!currentVal || (appId && currentVal != appId)) {
                                        if (report.appliance_name) {
                                            console.log('Trying to select by name:', report.appliance_name);
                                            let found = false;
                                            
                                            // First try exact match
                                            $('#appliance-select option').each(function() {
                                                const optionText = $(this).text().trim();
                                                if (optionText === report.appliance_name.trim()) {
                                                    $(this).prop('selected', true);
                                                    console.log('Appliance selected by exact match:', optionText);
                                                    found = true;
                                                    return false;
                                                }
                                            });
                                            
                                            // If no exact match, try partial match
                                            if (!found) {
                                                $('#appliance-select option').each(function() {
                                                    const optionText = $(this).text();
                                                    const reportName = report.appliance_name;
                                                    
                                                    // Extract key parts for comparison (brand and category)
                                                    const extractParts = (str) => {
                                                        const brandMatch = str.match(/^([^-]+)/);
                                                        const categoryMatch = str.match(/\(([^)]+)\)/);
                                                        return {
                                                            brand: brandMatch ? brandMatch[1].trim() : '',
                                                            category: categoryMatch ? categoryMatch[1].trim() : ''
                                                        };
                                                    };
                                                    
                                                    const optionParts = extractParts(optionText);
                                                    const reportParts = extractParts(reportName);
                                                    
                                                    // Match if brand and category are the same
                                                    if (optionParts.brand && reportParts.brand && 
                                                        optionParts.brand === reportParts.brand &&
                                                        optionParts.category && reportParts.category &&
                                                        optionParts.category === reportParts.category) {
                                                        $(this).prop('selected', true);
                                                        console.log('Appliance selected by brand+category match:', optionText);
                                                        found = true;
                                                        return false;
                                                    }
                                                });
                                            }
                                            
                                            if (!found) {
                                                console.warn('Could not find appliance by name:', report.appliance_name);
                                                // If we have the appliance_id, still try to set it
                                                if (appId) {
                                                    $('#appliance-select').val(appId);
                                                    console.log('Set appliance by ID as fallback:', appId);
                                                }
                                            }
                                        }
                                    } else {
                                        console.log('Appliance successfully selected by ID:', currentVal);
                                    }
                                }).fail(function(error) {
                                    console.error('Failed to load appliances:', error);
                                });
                            } else {
                                console.warn('No customer ID available to load appliances');
                            }
                            
                            // Populate parts
                            if (report.parts && Array.isArray(report.parts) && report.parts.length > 0) {
                                console.log('Populating parts:', report.parts);
                                $('#parts-container .parts-row:not(:first)').remove();
                                
                                const firstRow = $('#parts-container .parts-row:first');
                                report.parts.forEach((part, index) => {
                                    let row = firstRow;
                                    if (index > 0) {
                                        row = firstRow.clone(true, true);
                                        $('#parts-container').append(row);
                                    }
                                    // Get the select element and populate it
                                    const $partSelect = row.find('.part-select');
                                    
                                    // Ensure part options are populated
                                    const storedParts = $('body').data('parts') || [];
                                    if (storedParts.length === 0) {
                                        console.warn('Parts list not populated yet, loading parts...');
                                        // Rebuild part options from stored data or reload
                                        loadParts();
                                    }
                                    
                                    // Set the part value
                                    $partSelect.val(part.part_id || part.id || '');
                                    
                                    // If value wasn't set, try to find by name
                                    if (!$partSelect.val() && part.part_no) {
                                        $partSelect.find('option').each(function() {
                                            if ($(this).text().includes(part.part_no)) {
                                                $(this).prop('selected', true);
                                                return false;
                                            }
                                        });
                                    }
                                    
                                    row.find('.quantity-input').val(part.quantity || 0);
                                    row.find('.amount-input').val((part.quantity * (part.unit_price || part.price || 0)).toFixed(2));
                                });
                            }
                            
                            // Calculate totals
                            calculateTotals();
                            console.log('Totals calculated');
                            
                            // Update button
                            $('#submit-report-btn').text('Update Report').css('background-color', '#28a745');
                            
                            showAlert('success', 'Report loaded for editing');
                            console.log('Form population complete');
                            
                            // Load progress comments
                            loadProgressComments(reportId);
                            
                            // Scroll to top
                            $('html, body').animate({ scrollTop: 0 }, 'smooth');
                    } else {
                        console.error('Failed response:', res);
                        showAlert('error', 'Failed to load report: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response text:', xhr.responseText);
                    showAlert('error', 'Error loading report');
                }
            });
        }

        // Filter service reports based on search query and status
        let allServiceReports = [];

        function filterServiceReports(query) {
            const status = $('#service-report-filter').val();
            const searchTerm = query.toLowerCase();
            
            let filtered = allServiceReports;
            
            // Filter by status
            if (status !== 'All') {
                filtered = filtered.filter(report => {
                    let reportStatus = report.status;
                    // Map numeric status codes to text
                    if (typeof reportStatus === 'number' || /^\d+$/.test(reportStatus)) {
                        const statusMap = {'0': 'Completed', '1': 'Pending', '2': 'Under Repair', '3': 'Unrepairable', '4': 'Release Out'};
                        reportStatus = statusMap[String(reportStatus)] || reportStatus;
                    }
                    return reportStatus === status;
                });
            }
            
            // Filter by search term
            if (searchTerm) {
                filtered = filtered.filter(report => {
                    const reportId = String(report.report_id || '').toLowerCase();
                    const customer = String(report.customer_name || '').toLowerCase();
                    const appliance = String(report.appliance_name || '').toLowerCase();
                    const serviceTypes = String(report.service_types || '').toLowerCase();
                    
                    return reportId.includes(searchTerm) || 
                           customer.includes(searchTerm) || 
                           appliance.includes(searchTerm) || 
                           serviceTypes.includes(searchTerm);
                });
            }
            
            renderServiceReports(filtered);
        }

        function applyStatusAndSearch() {
            const status = $('#service-report-filter').val();
            const searchTerm = $('#service-report-search').val().toLowerCase();
            
            let filtered = allServiceReports;
            
            // Filter by status
            if (status !== 'All') {
                filtered = filtered.filter(report => {
                    let reportStatus = report.status;
                    // Map numeric status codes to text
                    if (typeof reportStatus === 'number' || /^\d+$/.test(reportStatus)) {
                        const statusMap = {'0': 'Completed', '1': 'Pending', '2': 'Under Repair', '3': 'Unrepairable', '4': 'Release Out'};
                        reportStatus = statusMap[String(reportStatus)] || reportStatus;
                    }
                    return reportStatus === status;
                });
            }
            
            // Filter by search term
            if (searchTerm) {
                filtered = filtered.filter(report => {
                    const reportId = String(report.report_id || '').toLowerCase();
                    const customer = String(report.customer_name || '').toLowerCase();
                    const appliance = String(report.appliance_name || '').toLowerCase();
                    const serviceTypes = String(report.service_types || '').toLowerCase();
                    
                    return reportId.includes(searchTerm) || 
                           customer.includes(searchTerm) || 
                           appliance.includes(searchTerm) || 
                           serviceTypes.includes(searchTerm);
                });
            }
            
            renderServiceReports(filtered);
        }

        function renderServiceReports(reports) {
            const $tbody = $('#serviceReportListModal tbody');
            $tbody.empty();
            
            if (!Array.isArray(reports) || reports.length === 0) {
                $tbody.append('<tr><td colspan="8">No reports found</td></tr>');
                return;
            }
            
            reports.forEach(function(report) {
                const serviceTypes = Array.isArray(report.service_types) ? report.service_types.join(', ') : (report.service_types || 'N/A');
                const dateIn = report.date_in ? new Date(report.date_in).toLocaleDateString() : 'N/A';
                const dateReleasedOut = report.date_pulled_out ? new Date(report.date_pulled_out).toLocaleDateString() : '';
                
                // Map numeric status codes to text (for legacy data compatibility)
                let statusText = report.status;
                if (typeof statusText === 'number' || /^\d+$/.test(statusText)) {
                    const statusMap = {
                        '0': 'Completed',
                        '1': 'Pending',
                        '2': 'Under Repair',
                        '3': 'Unrepairable',
                        '4': 'Release Out'
                    };
                    statusText = statusMap[String(statusText)] || statusText;
                }
                
                // Status badge with same styling as admin
                let statusBadge = '';
                switch (statusText) {
                    case 'Completed':
                        statusBadge = '<span class="badge badge-success">Completed</span>';
                        break;
                    case 'Pending':
                        statusBadge = '<span class="badge badge-warning">Pending</span>';
                        break;
                    case 'Under Repair':
                        statusBadge = '<span class="badge badge-primary">Under Repair</span>';
                        break;
                    case 'Unrepairable':
                        statusBadge = '<span class="badge badge-danger">Unrepairable</span>';
                        break;
                    case 'Release Out':
                        statusBadge = '<span class="badge badge-secondary">Release Out</span>';
                        break;
                    default:
                        statusBadge = `<span class="badge badge-light">${statusText || 'N/A'}</span>`;
                }
                
                // show assign to me button when pending and not assigned
                let assignBtn = '';
                if (report.status === 'Pending' && (!report.technician || report.technician === '')) {
                    assignBtn = `<a href="#" class="assign-report" data-id="${report.report_id}" title="Assign to me"><i class="material-icons text-info">person_add</i></a>`;
                }
                
                $tbody.append(`
                    <tr>
                        <td>${report.report_id || ''}</td>
                        <td>${report.customer_name || ''}</td>
                        <td>${report.appliance_name || ''}</td>
                        <td>${serviceTypes}</td>
                        <td>${dateIn}</td>
                        <td>${dateReleasedOut}</td>
                        <td>${statusBadge}</td>
                        <td class="actions-col">
                            <a href="#" class="print-report" data-id="${report.report_id}" title="Print Report"><i class="material-icons text-success">print</i></a>
                            <a href="#" class="edit-report" data-id="${report.report_id}"><i class="material-icons text-primary">edit</i></a>
                            <a href="#" class="delete-report" data-id="${report.report_id}"><i class="material-icons text-danger">delete</i></a>
                            ${assignBtn}
                        </td>
                    </tr>
                `);
            });
        }

        // Delete report
        function deleteReport(reportId) {
            $.ajax({
                url: '../backend/api/service_api.php?action=delete&id=' + reportId,
                method: 'DELETE',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        showAlert('success', 'Report deleted successfully');
                        loadServiceReports(); // Refresh the list
                    } else {
                        showAlert('error', 'Failed to delete report: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    console.error('Error deleting report:', xhr.responseText);
                    showAlert('error', 'Error deleting report');
                }
            });
        }
        
        // Function to update dashboard data in real-time
        function updateDashboardData() {
            $.ajax({
                url: '../backend/api/dashboard_api.php?action=getAll',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    console.log('Dashboard data updated:', res);
                    if (res.success && res.data) {
                        // Update assigned reports card
                        const assigned = res.data.assignedReports || {};
                        const assignedTotal = assigned.total || 0;
                        const assignedChange = assigned.weekly_change || 0;
                        
                        // Try to update if dashboard elements exist
                        if (parent.document && parent.document.getElementById) {
                            // Update in parent/dashboard window
                            const assignedCard = parent.document.querySelector('[data-card="assigned"]');
                            if (assignedCard) {
                                assignedCard.querySelector('h2').textContent = assignedTotal;
                                assignedCard.querySelector('.growth').textContent = (assignedChange >= 0 ? '+' : '') + assignedChange + ' this week';
                            }
                            
                            // Update pending orders card
                            const pending = res.data.pendingOrders || {};
                            const pendingTotal = pending.total || 0;
                            const pendingChange = pending.daily_change || 0;
                            const pendingCard = parent.document.querySelector('[data-card="pending"]');
                            if (pendingCard) {
                                pendingCard.querySelector('h2').textContent = pendingTotal;
                                pendingCard.querySelector('.growth').textContent = (pendingChange >= 0 ? '+' : '') + pendingChange + ' today';
                            }
                            
                            // Update completed services card
                            const completed = res.data.completedServices || {};
                            const completedTotal = completed.total || 0;
                            const completedChange = completed.daily_change || 0;
                            const completedCard = parent.document.querySelector('[data-card="completed"]');
                            if (completedCard) {
                                completedCard.querySelector('h2').textContent = completedTotal;
                                completedCard.querySelector('.growth').textContent = (completedChange >= 0 ? '+' : '') + completedChange + ' today';
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error updating dashboard:', error);
                }
            });
            
            // Also broadcast to dashboard if it's in localStorage (for cross-tab communication)
            if (localStorage) {
                localStorage.setItem('dashboardRefreshNeeded', JSON.stringify({
                    timestamp: new Date().getTime(),
                    action: 'refresh'
                }));
            }
        }

        // Render print modal with report details as a screenshot
        async function renderPrintModal(reportId) {
            try {
                showLoading(true, '#printReportModal');
                
                const response = await callServiceAPI('getById', null, reportId);
                
                if (!response.success || !response.data) {
                    throw new Error(response.message || 'Failed to load report');
                }

                const report = response.data;
                
                // Create a temporary container to build the formatted report
                const tempContainer = document.createElement('div');
                tempContainer.id = 'temp-screenshot-container';
                tempContainer.style.position = 'absolute';
                tempContainer.style.left = '-9999px';
                tempContainer.style.top = '-9999px';
                tempContainer.style.width = '900px';
                tempContainer.style.background = '#ffffff';
                tempContainer.style.padding = '25px';
                tempContainer.style.fontSize = '13px';
                tempContainer.style.lineHeight = '1.8';
                tempContainer.style.fontFamily = 'Arial, sans-serif';
                tempContainer.style.color = '#000';
                
                // Format dates
                const dateIn = report.date_in ? new Date(report.date_in).toLocaleDateString() : '';
                const dop = report.dop ? new Date(report.dop).toLocaleDateString() : '';
                const dateRepaired = report.date_repaired ? new Date(report.date_repaired).toLocaleDateString() : '';

                // Format service types
                let serviceTypes = '';
                if (report.service_types && Array.isArray(report.service_types)) {
                    serviceTypes = report.service_types.join(', ');
                } else if (typeof report.service_types === 'string') {
                    serviceTypes = report.service_types;
                }

                // Build parts list with table
                let partsHtml = '<table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;"><tr style="border-bottom: 2px solid #000;"><th style="text-align: left; padding: 6px; color: #000; font-weight: bold;">Part Name</th><th style="text-align: center; padding: 6px; color: #000; font-weight: bold;">Qty</th></tr>';
                if (report.parts && Array.isArray(report.parts)) {
                    report.parts.forEach(part => {
                        partsHtml += `<tr style="border-bottom: 1px solid #000;"><td style="padding: 6px; color: #000;">${part.part_name || ''}</td><td style="text-align: center; padding: 6px; color: #000;">${part.quantity || 0}</td></tr>`;
                    });
                } else {
                    partsHtml += '<tr><td colspan="2" style="padding: 6px; color: #000;"></td></tr>';
                }
                partsHtml += '</table>';

                // Build charges breakdown with table
                let chargesHtml = '<table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;"><tr style="border-bottom: 2px solid #000;"><th style="text-align: left; padding: 6px; color: #000; font-weight: bold;">Description</th><th style="text-align: right; padding: 6px; color: #000; font-weight: bold;">Amount</th></tr>';
                chargesHtml += `<tr style="border-bottom: 1px solid #000;"><td style="padding: 6px; color: #000;">Labor</td><td style="text-align: right; padding: 6px; color: #000;">₱${parseFloat(report.labor || 0).toFixed(2)}</td></tr>`;
                chargesHtml += `<tr style="border-bottom: 1px solid #000;"><td style="padding: 6px; color: #000;">Pull-Out/Delivery</td><td style="text-align: right; padding: 6px; color: #000;">₱${parseFloat(report.pullout_delivery || 0).toFixed(2)}</td></tr>`;
                chargesHtml += `<tr style="border-bottom: 1px solid #000;"><td style="padding: 6px; color: #000;">Parts Charge</td><td style="text-align: right; padding: 6px; color: #000;">₱${parseFloat(report.parts_total_charge || 0).toFixed(2)}</td></tr>`;
                chargesHtml += '</table>';

                const printContentHtml = `
                    <div style="padding: 25px; font-size: 13px; line-height: 1.8; font-family: Arial, sans-serif; color: #000;">
                        <div style="text-align: center; margin-bottom: 20px; border-bottom: 3px solid #000; padding-bottom: 10px;">
                            <h2 style="margin: 0 0 8px 0; font-size: 24px; font-weight: bold; color: #000;">SERVICE REPAIR REPORT</h2>
                            <p style="margin: 0; font-size: 12px; color: #000; font-weight: 600;">Service Report ID: #${report.report_id}</p>
                        </div>
                        
                        <!-- First Row: Info -->
                        <div style="margin-bottom: 15px; display: table; width: 100%; border-collapse: collapse;">
                            <div style="display: table-cell; width: 25%; padding: 8px 10px; border: 2px solid #000; background: #ffffff; font-weight: bold; color: #000;">
                                <strong style="font-size: 13px; color: #000;">Date In:</strong><br><span style="font-size: 12px; color: #000;">${dateIn}</span>
                            </div>
                            <div style="display: table-cell; width: 25%; padding: 8px 10px; border: 2px solid #000; background: #ffffff; font-weight: bold; color: #000;">
                                <strong style="font-size: 13px; color: #000;">Status:</strong><br><span style="font-size: 12px; color: #000;">${report.status || ''}</span>
                            </div>
                            <div style="display: table-cell; width: 25%; padding: 8px 10px; border: 2px solid #000; background: #ffffff; font-weight: bold; color: #000;">
                                <strong style="font-size: 13px; color: #000;">Dealer:</strong><br><span style="font-size: 12px; color: #000;">${report.dealer || ''}</span>
                            </div>
                            <div style="display: table-cell; width: 25%; padding: 8px 10px; border: 2px solid #000; background: #ffffff; font-weight: bold; color: #000;">
                                <strong style="font-size: 13px; color: #000;">Staff:</strong><br><span style="font-size: 12px; color: #000;">${report.staff_name || ''}</span>
                            </div>
                        </div>
                        
                        <!-- Customer & Appliance -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">CUSTOMER INFORMATION</h4>
                            <div style="display: table; width: 100%; border-collapse: collapse;">
                                <div style="display: table-cell; width: 50%; padding: 8px 10px; border: 1px solid #000; color: #000;">
                                    <strong style="font-size: 12px; color: #000;">Name:</strong> <span style="font-size: 12px; color: #000;">${report.customer_name || ''}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Contact:</strong> <span style="font-size: 12px; color: #000;">${report.customer_contact || ''}</span>
                                </div>
                                <div style="display: table-cell; width: 50%; padding: 8px 10px; border: 1px solid #000; color: #000;">
                                    <strong style="font-size: 12px; color: #000;">Appliance:</strong> <span style="font-size: 12px; color: #000;">${report.appliance_name || ''}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Model:</strong> <span style="font-size: 12px; color: #000;">${report.appliance_model || ''}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Serial:</strong> <span style="font-size: 12px; color: #000;">${report.appliance_serial || ''}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Service Details -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">SERVICE INFORMATION</h4>
                            <div style="display: table; width: 100%; border-collapse: collapse;">
                                <div style="display: table-cell; width: 50%; padding: 8px 10px; border: 1px solid #000; color: #000;">
                                    <strong style="font-size: 12px; color: #000;">Service Type:</strong> <span style="font-size: 12px; color: #000;">${serviceTypes}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Findings:</strong> <span style="font-size: 12px; color: #000;">${report.findings || ''}</span>
                                </div>
                                <div style="display: table-cell; width: 50%; padding: 8px 10px; border: 1px solid #000; color: #000;">
                                    <strong style="font-size: 12px; color: #000;">Date of Problem:</strong> <span style="font-size: 12px; color: #000;">${dop}</span><br>
                                    <strong style="font-size: 12px; color: #000;">Date Repaired:</strong> <span style="font-size: 12px; color: #000;">${dateRepaired}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Complaint -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">COMPLAINT</h4>
                            <div style="padding: 10px; border: 1px solid #000; min-height: 40px; background: #ffffff; font-size: 12px; color: #000;">
                                ${report.complaint || ''}
                            </div>
                        </div>
                        
                        <!-- Parts Used -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">PARTS USED</h4>
                            <div style="padding: 10px; border: 1px solid #000; background: #ffffff;">
                                ${partsHtml}
                            </div>
                        </div>
                        
                        <!-- Charges -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">CHARGES BREAKDOWN</h4>
                            <div style="padding: 10px; border: 1px solid #000; background: #ffffff;">
                                ${chargesHtml}
                            </div>
                        </div>
                        
                        <!-- Total Amount -->
                        <div style="margin-bottom: 15px; background-color: #ffffff; padding: 15px; border: 3px solid #000; font-weight: bold; font-size: 16px; text-align: right; color: #000;">
                            TOTAL AMOUNT: ₱${parseFloat(report.total_amount || 0).toFixed(2)}
                        </div>
                        
                        <!-- Remarks -->
                        <div style="margin-bottom: 15px;">
                            <h4 style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; color: #000;">REMARKS</h4>
                            <div style="padding: 10px; border: 1px solid #000; min-height: 40px; background: #ffffff; font-size: 12px; color: #000;">
                                ${report.remarks || ''}
                            </div>
                        </div>
                        
                        <!-- Signature Area -->
                        <div style="margin-top: 20px; border-top: 2px solid #000; padding-top: 15px;">
                            <div style="display: table; width: 100%; border-collapse: collapse;">
                                <div style="display: table-cell; width: 33%; padding: 0 8px 0 0; text-align: center;">
                                    <div style="height: 60px; border-bottom: 2px solid #000; margin-bottom: 8px;"></div>
                                    <strong style="font-size: 12px; display: block;">Technician</strong>
                                    <span style="font-size: 11px;">Date: _____________</span>
                                </div>
                                <div style="display: table-cell; width: 33%; padding: 0 8px; text-align: center;">
                                    <div style="height: 60px; border-bottom: 2px solid #000; margin-bottom: 8px;"></div>
                                    <strong style="font-size: 12px; display: block;">Manager</strong>
                                    <span style="font-size: 11px;">Date: _____________</span>
                                </div>
                                <div style="display: table-cell; width: 33%; padding: 0 0 0 8px; text-align: center;">
                                    <div style="height: 60px; border-bottom: 2px solid #000; margin-bottom: 8px;"></div>
                                    <strong style="font-size: 12px; display: block;">Released By</strong>
                                    <span style="font-size: 11px;">Date: _____________</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                tempContainer.innerHTML = printContentHtml;
                document.body.appendChild(tempContainer);

                // Wait for images/content to load
                await new Promise(resolve => setTimeout(resolve, 500));

                // Capture the formatted report as a screenshot
                const canvas = await html2canvas(tempContainer, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    logging: false
                });
                
                // Convert canvas to image
                const screenshotImage = canvas.toDataURL('image/png');
                
                // Remove temporary container
                document.body.removeChild(tempContainer);
                
                // Inject the screenshot image into the print body
                const screenshotHtml = `
                    <div style="text-align: center; width: 100%; height: auto;">
                        <img src="${screenshotImage}" style="max-width: 100%; height: auto; border: none; display: block;" />
                    </div>
                `;
                
                $('#print-report-body').html(screenshotHtml);
                showLoading(false, '#printReportModal');

            } catch (error) {
                console.error('Error rendering print modal:', error);
                showLoading(false, '#printReportModal');
                showAlert('danger', error.message || 'Failed to load report for printing');
            }
        }

        // ============ PROGRESS COMMENT FUNCTIONS ============

        var currentProgressKey = null;
        var currentReportId = null;
        var progressComments = {};

        function openProgressCommentModal(progressKey, progressTitle) {
            currentProgressKey = progressKey;
            currentReportId = $('#report_id').val();
            
            $('#progress-title-modal').text(progressTitle);
            $('#progress-comment-text').val('');
            $('#progressCommentModal').modal('show');
        }

        function saveProgressComment() {
            const commentText = $('#progress-comment-text').val().trim();
            
            if (!commentText) {
                showAlert('warning', 'Please enter a comment before saving.');
                return;
            }
            
            if (!currentReportId) {
                showAlert('danger', 'Report ID not found. Please reload the report.');
                return;
            }

            console.log('Saving comment:', {
                reportId: currentReportId,
                progressKey: currentProgressKey,
                commentText: commentText
            });

            // Show loading state
            showLoading(true, '#progressCommentModal');
            
            $.ajax({
                url: '../backend/api/service_report_api.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'addProgressComment',
                    report_id: currentReportId,
                    progress_key: currentProgressKey,
                    comment_text: commentText
                }),
                success: function(response) {
                    showLoading(false, '#progressCommentModal');
                    
                    console.log('Save comment response:', response);
                    
                    if (response.success) {
                        showAlert('success', 'Comment saved successfully!');
                        $('#progressCommentModal').modal('hide');
                        
                        // Reload comments to display the newly added one
                        loadProgressComments(currentReportId);
                    } else {
                        showAlert('danger', response.message || 'Failed to save comment');
                    }
                },
                error: function(xhr, status, error) {
                    showLoading(false, '#progressCommentModal');
                    console.error('Error saving comment:', error);
                    showAlert('danger', 'Error saving comment. Please try again.');
                }
            });
        }

        function loadProgressComments(reportId) {
            console.log('Loading progress comments for reportId:', reportId);
            $.ajax({
                url: '../backend/api/service_report_api.php?action=getProgressComments&report_id=' + reportId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Progress comments response:', response);
                    if (response.success) {
                        progressComments = {};
                        
                        // Organize comments by progress key
                        if (response.data && response.data.length > 0) {
                            console.log('Comments found:', response.data.length);
                            response.data.forEach(function(comment) {
                                if (!progressComments[comment.progress_key]) {
                                    progressComments[comment.progress_key] = [];
                                }
                                progressComments[comment.progress_key].push(comment);
                            });
                        } else {
                            console.log('No comments found for this report');
                        }
                        
                        // Display all comments
                        displayAllProgressComments();
                    } else {
                        console.warn('Response not successful:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading progress comments:', error);
                    console.error('XHR Response:', xhr.responseText);
                }
            });
        }

        function displayProgressItemComments(progressKey) {
            const commentsContainer = $('#comments-' + progressKey);
            
            if (!commentsContainer.length) {
                return;
            }
            
            if (!progressComments[progressKey] || progressComments[progressKey].length === 0) {
                commentsContainer.html('<div class="no-comments">No comments yet</div>');
                return;
            }
            
            let commentsHtml = '';
            progressComments[progressKey].forEach(function(comment) {
                const createdAt = new Date(comment.created_at).toLocaleString();
                
                commentsHtml += `
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-author">${escapeHtml(comment.created_by || 'Unknown')}</span>
                            <span class="comment-time">${createdAt}</span>
                            <button type="button" class="btn btn-sm btn-link text-danger" 
                                    onclick="deleteProgressComment(${comment.id}, '${progressKey}')" 
                                    style="padding: 0; margin-left: auto;">
                                <span class="material-icons" style="font-size: 18px; vertical-align: middle;">delete</span>
                            </button>
                        </div>
                        <div class="comment-text">${escapeHtml(comment.comment_text).replace(/\n/g, '<br>')}</div>
                    </div>
                `;
            });
            
            commentsContainer.html(commentsHtml);
        }

        function displayAllProgressComments() {
            const progressKeys = ['pending', 'under_repair', 'unrepairable', 'release_out', 'completed', 'report_created'];
            progressKeys.forEach(function(key) {
                displayProgressItemComments(key);
            });
        }

        function deleteProgressComment(commentId, progressKey) {
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }
            
            $.ajax({
                url: '../backend/api/service_report_api.php?action=deleteProgressComment&id=' + commentId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Comment deleted successfully');
                        // Reload to refresh display
                        loadProgressComments(currentReportId);
                    } else {
                        showAlert('danger', response.message || 'Failed to delete comment');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting comment:', error);
                    showAlert('danger', 'Error deleting comment');
                }
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Create transaction from completed service report
        async function createTransactionFromReport(reportId) {
            try {
                console.log('Creating transaction for report:', reportId);
                
                // First check if transaction already exists
                const checkResponse = await $.ajax({
                    url: '../backend/api/transaction_api.php?action=getAll',
                    method: 'GET',
                    dataType: 'json',
                    timeout: 10000
                });

                if (checkResponse.success && checkResponse.data) {
                    const transactionsList = Array.isArray(checkResponse.data) ? checkResponse.data : (checkResponse.data.transactions || []);
                    const existingTransaction = transactionsList.find(t => t.report_id == reportId || t.reportId == reportId);
                    if (existingTransaction) {
                        console.log('Transaction already exists for this report');
                        showAlert('info', 'Transaction already exists for this report');
                        return;
                    }
                }

                // Get the complete report data
                const response = await $.ajax({
                    url: '../backend/api/service_api.php?action=getById&id=' + reportId,
                    method: 'GET',
                    dataType: 'json',
                    timeout: 10000
                });

                if (!response.success || !response.data) {
                    throw new Error('Failed to load report data');
                }

                const reportData = response.data;
                console.log('Report data loaded:', reportData);

                // CHECK: Only create transaction if status is "Completed"
                if (reportData.status !== 'Completed') {
                    console.warn('⚠️ Transaction not created - Service report status is not Completed. Current status:', reportData.status);
                    showAlert('warning', 'Transaction can only be created for Completed service reports. Current status: ' + reportData.status);
                    return;
                }

                const totalAmt = parseFloat(reportData.total_amount || 0);
                if (typeof totalAmt !== 'number' || isNaN(totalAmt)) {
                    throw new Error('Invalid total amount for this report');
                }

                const transactionData = {
                    report_id: reportId,
                    customer_name: reportData.customer_name,
                    appliance_name: reportData.appliance_name,
                    total_amount: totalAmt,
                    service_types: reportData.service_types || [],
                    payment_status: 'Pending'
                };

                console.log('✅ Creating transaction for COMPLETED report with data:', transactionData);

                const transactionResponse = await $.ajax({
                    url: '../backend/api/transaction_api.php?action=createFromReport',
                    method: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(transactionData),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    timeout: 10000
                });

                if (!transactionResponse.success) {
                    const errMsg = transactionResponse.message || 'Failed to create transaction';
                    throw new Error(errMsg);
                }

                console.log('Transaction created successfully');
                showAlert('success', 'Transaction created successfully and dashboard updated!');
                
                // Update the button appearance after transaction is created
                $('#submit-report-btn').text('Update Report').css('background-color', '#0066e6');

            } catch (error) {
                console.error('Error creating transaction:', error);
                showAlert('warning', 'Report updated but transaction creation encountered an issue: ' + (error.message || error));
            }
        }

    </script>
</body>
</html>
