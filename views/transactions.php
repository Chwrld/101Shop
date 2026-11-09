<?php
require_once __DIR__ . '/../bootstrap.php';
session_start();
require __DIR__ . '/../backend/handlers/authHandler.php';
$auth = new AuthHandler();
$userSession = $auth->requireAuth('admin');
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Transactions</title>
    <link rel="shortcut Icon" href="../img/Repair.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
   <style>
    .modal-xxl {
        max-width: 90vw !important;
        width: 90vw !important;
        margin: 0 auto;
    }

    .modal-xxl .modal-content {
        min-height: 96vh;
        height: auto;
    }
    
    /* Make modal title black */
    .modal-title {
        color: #000000 !important;
        font-weight: 600;
    }
    
    /* Tabs styling */
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
    }
    
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
        border: none;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }
    
    .nav-tabs .nav-link:hover {
        color: #242424ff;
        border-bottom-color: #242424ff;
        background-color: transparent;
    }
    
    .nav-tabs .nav-link.active {
        color: #242424ff;
        border-bottom-color: #242424ff;
        background-color: transparent;
        font-weight: 600;
    }
    
    .nav-tabs .nav-link i {
        vertical-align: middle;
        font-size: 20px;
        margin-right: 5px;
    }
    
    /* Badge styling for table names */
    .badge-secondary {
        background-color: #6c757d;
        padding: 0.4em 0.8em;
        font-size: 0.85em;
    }
    
    /* Filter Controls Styles */
    .filter-controls {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .filter-controls select {
        min-width: 150px;
    }
    
    .sort-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .sort-controls .form-group {
        margin-bottom: 0;
    }
    
    .sort-controls label {
        margin-bottom: 0;
        margin-right: 5px;
        font-weight: 500;
    }
    
    /* Improved filter alignment */
    .filter-container {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .filter-group label {
        margin-bottom: 0;
        font-weight: 500;
        white-space: nowrap;
    }
    
    /* Print button styling */
    .print-header-btn {
        margin-left: auto;
    }
    
    /* Findings Section Styles */
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
    
    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
            color: #000000 !important;
        }

        .print-section,
        .print-section * {
            visibility: visible;
            color: #000000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .print-section {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
            background: white;
            color: #000000 !important;
        }

        .no-print {
            display: none !important;
        }

        .modal-xxl {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
        }

        .modal-content {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        .modal-header {
            border-bottom: 2px solid #333 !important;
            color: #000000 !important;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
            color: #000000 !important;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px;
            color: #000000 !important;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #000000 !important;
        }

        .findings-section {
            border: 1px solid #000 !important;
            background-color: white !important;
        }

        .findings-checkbox:checked {
            background-color: #000 !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .card-header {
            background: white !important;
            border-bottom: 2px solid #333 !important;
            color: #000000 !important;
        }

        .card-title {
            font-size: 18px;
            font-weight: bold;
            color: #000000 !important;
        }
        
        .modal-title {
            color: #000000 !important;
        }

        h5 {
            color: #000000 !important;
        }

        p, span, td, th, div {
            color: #000000 !important;
        }
    }
    
    /* Ensure all form elements are readonly by default */
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
    
   .status-badge {
    display: inline-block;
    padding: 0.25em 1em;
    border-radius: 999px;
    font-size: 0.95em;
    font-weight: 500;
    color: #fff;
    min-width: 70px;
    text-align: center;
    letter-spacing: 1px;
}

.status-paid {
    
    color: #28a745;
}

.status-pending {
            color: #ffc107;
}
    /* Pagination footer */
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
    
    /* Transaction list print section */
    .transaction-list-print {
        display: none;
    }
    
    @media print {
        .transaction-list-print {
            display: block;
        }

        .transaction-list-print .card {
            page-break-inside: avoid;
        }
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="body-overlay"></div>

        <!-- Sidebar -->
        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <div id="content">
            <!-- Top Navbar -->
            <?php
            $pageTitle = 'Transactions';
            $breadcrumb = 'Transactions';
            include __DIR__ . '/../layout/navbar.php';
            ?>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs mb-3" id="transactionTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="transactions-tab" data-toggle="tab" href="#transactions" role="tab">
                            <i class="material-icons align-middle">payment</i> Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="logs-tab" data-toggle="tab" href="#logs" role="tab">
                            <i class="material-icons align-middle">history</i> Archive Logs
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="transactionTabsContent">
                    <!-- Transactions Tab -->
                    <div class="tab-pane fade show active" id="transactions" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <!-- Card Header with Filter -->
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">Transaction List</h5>
                                <div class="filter-container">
                                    <!-- For Date -->
                                    <div class="filter-group">
                                        <label for="dateSort">Sort by:</label>
                                        <select id="dateSort" class="form-control">
                                            <option value="">Select</option>
                                            <option value="latest">Latest</option>
                                            <option value="oldest">Oldest</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Transaction ID -->
                                    <div class="filter-group">
                                        <label for="transactionIdSort">Transaction ID:</label>
                                        <select id="transactionIdSort" class="form-control">
                                            <option value="">Select</option>
                                            <option value="ascending">Ascending</option>
                                            <option value="descending">Descending</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Amount -->
                                    <div class="filter-group">
                                        <label for="amountSort">Amount:</label>
                                        <select id="amountSort" class="form-control">
                                            <option value="">Select</option>
                                            <option value="highest">Highest</option>
                                            <option value="lowest">Lowest</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Status -->
                                    <div class="filter-group">
                                        <label for="filterBy">Filter by:</label>
                                        <select id="filterBy" class="form-control">
                                            <option value="all">Select</option>
                                            <option value="Paid">Paid Only</option>
                                            <option value="Pending">Pending Only</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Print Button -->
                                    <button type="button" class="btn btn-light border print-header-btn no-print print-transactions-btn" style="font-weight: 500;">
                                        <i class="material-icons align-middle">print</i> Print List
                                    </button>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="transactionsTable">
                                        <thead>
                                            <tr>
                                                <th>Transaction ID</th>
                                                <th>Customer</th>
                                                <th>Appliance</th>
                                                <th>Total Amount</th>
                                                <th>Payment Status</th>
                                                <th>Payment Date</th>
                                                <th>Payment Due</th>
                                                <th>Received By</th>
                                                <th class="no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="transactionsTableBody">
                                            <!-- Transactions will be loaded dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                    <div class="text-muted">
                                        <span id="txPaginationInfo">Showing 0 to 0 of 0 entries</span>
                                    </div>
                                    <nav aria-label="Transactions navigation">
                                        <ul class="pagination pagination-sm mb-0" id="txPagination"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Archive Logs Tab -->
            <div class="tab-pane fade" id="logs" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Archive Logs</h5>
                                <div class="filter-container">
                                    <!-- Search -->
                                    <div class="filter-group">
                                        <label for="logSearch">Search:</label>
                                        <input type="text" id="logSearch" class="form-control" placeholder="Search logs...">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="logsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Table Name</th>
                                                <th>Record ID</th>
                                                <th>Action</th>
                                                <th>Deleted By</th>
                                                <th>Deleted At</th>
                                                <th class="no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="logsTableBody">
                                            <!-- Logs will be loaded dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                    <div class="text-muted">
                                        <span id="logPaginationInfo">Showing 0 to 0 of 0 entries</span>
                                    </div>
                                    <nav aria-label="Logs navigation">
                                        <ul class="pagination pagination-sm mb-0" id="logPagination"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

            <!-- Hidden print section for transaction list -->
            <div class="transaction-list-print print-section">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Transaction List Report</h5>
                         <span id="print-date"></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="transactionsPrintTable">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Customer</th>
                                        <th>Appliance</th>
                                        <th>Total Amount</th>
                                        <th>Payment Status</th>
                                        <th>Payment Date</th>
                                        <th>Received By</th>
                                    </tr>
                                </thead>
                                <tbody id="transactionsPrintTableBody">
                                    <!-- Will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Payment Modal -->
            <div class="modal fade" id="updatePaymentModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="updatePaymentForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Update Payment Status</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="transaction_id" id="update_transaction_id">
                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <select name="payment_status" id="update_payment_status" class="form-control" required>
                                        <option value="Paid">Paid</option>
                                        <option value="Pending">Pending</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Received By</label>
                                    <select name="received_by" id="update_received_by" class="form-control" required>
                                        <option value="">Select Staff</option>
                                        <!-- Staff options will be populated dynamically -->
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="form-control" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="Cash">Cash</option>
                                        <option value="GCash">GCash</option>
                                    </select>
                                </div>
                                <div class="form-group" id="reference_number_group" style="display: none;">
                                    <label>GCash Reference Number</label>
                                    <input type="text" name="reference_number" id="reference_number" class="form-control" placeholder="Enter GCash reference number">
                                </div>
                                <div class="form-group">
                                    <label>Payment Due Date</label>
                                    <input type="date" name="payment_due_date" id="payment_due_date" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Transaction Form Modal -->
            <div class="modal fade" id="transactionFormModal" tabindex="-1" role="dialog" aria-labelledby="transactionFormModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xxl" role="document">
                    <div class="modal-content print-section">                   
                        <div class="modal-header bg-light d-flex justify-content-between align-items-center" style="border-bottom: 1px solid #ccc;">
                            <div class="d-flex align-items-center w-100">
                                <h5 class="modal-title mb-0 " id="transactionFormModalLabel">Transaction Form</h5>
                            </div>
                            <div class="d-flex align-items-center no-print">
                                <button type="button" class="btn btn-warning border mr-2 no-print edit-btn" style="font-weight: 500;">
                                    <i class="material-icons align-middle">edit</i> Edit Report
                                </button>
                                <button type="button" class="btn btn-light border mr-2 no-print print-btn" style="font-weight: 500;">
                                    <i class="material-icons align-middle">print</i> Print
                                </button>
                                <button type="button" class="btn btn-success border mr-2 no-print finalize-edit-btn" style="font-weight: 500; display: none;">
                                    <i class="material-icons align-middle">save</i> Finalize Edit
                                </button>
                            </div>
                            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="transactionForm">
                            <input type="hidden" name="report_id" id="update_report_id">
                                <div class="container-fluid">
                                    <div class="row mb-2">
                                        <div class="col-md-3">
                                            <label>Customer</label>
                                            <input type="text" class="form-control" name="customer" id="customer-field" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Appliance</label>
                                            <input type="text" class="form-control" name="appliance" id="appliance-field" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Date In</label>
                                            <input type="text" class="form-control" name="date_in" id="date-in-field" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Status</label>
                                            <!-- <input type="text" class="form-control" name="status" readonly>  -->
                                                            <select name="status" id="status-field" class="form-control" readonly>
                                                                <option value="Pending">Pending</option>
                                                                <option value="Under Repair">Under Repair</option>
                                                                <option value="Unrepairable">Unrepairable</option>
                                                                <option value="Release Out">Release Out</option>
                                                                <option value="Completed">Completed</option>
                                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3">
                                            <label>Dealer</label>
                                            <input type="text" class="form-control" name="dealer" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Date</label>
                                            <input type="text" class="form-control" name="dop" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Date Pulled - Out</label>
                                            <input type="text" class="form-control" name="date_pulled_out" readonly>
                                        </div>
                                    </div>
                                    
                                    <!-- Fixed Findings Section -->
                                    <div class="findings-section">
                                        <div class="findings-header">
                                            <div>Findings</div>
                                            <div class="text-center">Shop</div>
                                            <div class="text-center">Field</div>
                                            <div class="text-center">Out WTY</div>
                                        </div>
                                        <div class="findings-row">
                                            <input type="text" class="form-control" name="findings" id="findings-field" readonly>
                                            <div class="checkbox-container">
                                                <input class="findings-checkbox" type="checkbox" name="shop" id="shop-field" disabled>
                                            </div>
                                            <div class="checkbox-container">
                                                <input class="findings-checkbox" type="checkbox" name="field" id="field-field" disabled>
                                            </div>
                                            <div class="checkbox-container">
                                                <input class="findings-checkbox" type="checkbox" name="out_wty" id="out_wty-field" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-2">
                                        <div class="col-md-9">
                                            <label>Remarks</label>
                                            <input type="text" class="form-control" name="remarks" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-1 mt-3">
                                        <div class="col-md-12">
                                            <h5 class="fw-bold mb-1">Part Used</h5>
                                        </div>
                                    </div>
                                    <!-- Parts Used Container -->
                                    <div id="parts-container">
                                        <div class="row mb-2 align-items-center g-2 parts-row">
                                            <div class="col-md-3 pe-1">
                                                <input type="text" class="form-control" name="part_name[]" readonly>
                                            </div>
                                            <div class="col-md-2 px-1">
                                                <input type="text" class="form-control" name="quantity[]" readonly>
                                            </div>
                                            <div class="col-md-2 px-1">
                                                <input type="text" class="form-control" name="part_amount[]" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="d-flex flex-row" style="gap: 2rem;">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="installation" id="installation-field" disabled>
                                                    <label class="form-check-label" for="installation-field">Installation</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="repair" id="repair-field" disabled>
                                                    <label class="form-check-label" for="repair-field">Repair</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="cleaning" id="cleaning-field" disabled>
                                                    <label class="form-check-label" for="cleaning-field">Cleaning</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="checkup" id="checkup-field" disabled>
                                                    <label class="form-check-label" for="checkup-field">Check-Up</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-8 pe-1">
                                            <label>Total Amount</label>
                                            <div class="input-group mb-0">
                                                <span class="input-group-text">₱</span>
                                                <input type="text" class="form-control" name="total_amount" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4 ps-1">
                                            <label>Date Repaired</label>
                                            <input type="text" class="form-control" name="date_repaired" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-12">
                                            <label>Date Delivered</label>
                                            <input type="text" class="form-control" name="date_delivered" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-12">
                                            <label>Complaint</label>
                                            <input type="text" class="form-control" name="complaint" readonly>
                                        </div>
                                    </div>
                                    <div class="row mt-3 mb-1">
                                        <div class="col-md-12">
                                            <h5 class="fw-bold mb-1">Charged Details</h5>
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-end">
                                        <div class="col-md-3 pe-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Labor:</label>
                                            <div class="input-group mb-0">
                                                <span class="input-group-text">₱</span>
                                                <input type="text" class="form-control" id="labor-amount" step="1" min="0" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3 px-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Pull-Out Delivery:</label>
                                            <div class="input-group mb-0">
                                                <span class="input-group-text">₱</span>
                                                <input type="text" class="form-control" id="pullout-delivery" step="1" min="0" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3 px-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Total:</label>
                                            <div class="input-group mb-0">
                                                <span class="input-group-text">₱</span>
                                                <input type="text" class="form-control" id="total-serviceCharge" step="1" min="0" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3 ps-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Parts Charge:</label>
                                            <div class="input-group mb-0">
                                                <span class="input-group-text">₱</span>
                                                <input type="text" class="form-control" name="parts_charge" id="parts-charge" step="1" min="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4"></div>
                                    <div class="row mb-2">
                                        <div class="col-md-3 pe-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Receptionist:</label>
                                            <input type="text" class="form-control" name="receptionist" readonly>
                                        </div>
                                        <div class="col-md-3 px-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Manager:</label>
                                            <input type="text" class="form-control" name="manager" readonly>
                                        </div>
                                        <div class="col-md-3 px-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Technician:</label>
                                            <input type="text" class="form-control" name="technician" readonly>
                                        </div>
                                        <div class="col-md-3 ps-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Released By:</label>
                                            <input type="text" class="form-control" name="released_by" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-2 mt-3">
                                        <div class="col-md-12">
                                            <h5 class="fw-bold mb-1">Payment Information</h5>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3 pe-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Payment Status:</label>
                                            <input type="text" class="form-control" name="payment_status" readonly>
                                        </div>
                                        <div class="col-md-3 px-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Payment Method:</label>
                                            <input type="text" class="form-control" name="payment_method" readonly>
                                        </div>
                                        <div class="col-md-3 px-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Reference Number:</label>
                                            <input type="text" class="form-control" name="reference_number" readonly>
                                        </div>
                                        <div class="col-md-3 ps-1 d-flex flex-column justify-content-end">
                                            <label class="mb-1">Payment Date:</label>
                                            <input type="text" class="form-control" name="payment_date" readonly>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/jquery-3.3.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Toggle sidebar
            $(".xp-menubar").on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            $(".xp-menubar,.body-overlay").on('click', function() {
                $('#sidebar,.body-overlay').toggleClass('show-nav');
            });
            loadServicePrices();
            loadTransactions();
            loadStaffForPaymentModal();

                // Set transaction ID for payment update
                $('.update-payment').click(function() {
                    const transactionId = $(this).data('id');
                    $('#update_transaction_id').val(transactionId);
                    
                    // Store transaction ID for later use
                    $('#updatePaymentModal').data('transactionId', transactionId);
                });

                // When modal is fully shown, load the current transaction data
                $('#updatePaymentModal').on('shown.bs.modal', function() {
                    const transactionId = $(this).data('transactionId');
                    
                    if (!transactionId) return;
                    
                    console.log('Loading current data for transaction:', transactionId);
                    
                    // Get transaction data to populate current values
                    $.ajax({
                        url: '../backend/api/transaction_api.php?action=getById&id=' + transactionId,
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            console.log('Transaction data loaded:', response);
                            
                            if (response.success && response.data) {
                                const data = Array.isArray(response.data) ? response.data[0] : response.data;
                                
                                console.log('Setting form values:', data);
                                
                                // Set current payment status
                                $('#update_payment_status').val(data.payment_status || 'Pending');
                                
                                // Set current received by
                                if (data.received_by) {
                                    $('#update_received_by').val(data.received_by);
                                    console.log('Set received_by to:', data.received_by);
                                }
                                
                                // Set current payment method
                                if (data.payment_method) {
                                    $('#payment_method').val(data.payment_method);
                                    console.log('Set payment_method to:', data.payment_method);
                                    
                                    // Show reference number field if GCash
                                    if (data.payment_method === 'GCash') {
                                        $('#reference_number_group').slideDown();
                                        $('#reference_number').val(data.reference_number || '');
                                        $('#reference_number').prop('required', true);
                                        console.log('Set reference_number to:', data.reference_number);
                                    } else {
                                        $('#reference_number_group').hide();
                                        $('#reference_number').val('');
                                        $('#reference_number').prop('required', false);
                                    }
                                } else {
                                    $('#payment_method').val('');
                                    $('#reference_number_group').hide();
                                    $('#reference_number').prop('required', false);
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading transaction data:', error);
                            console.error('Response:', xhr.responseText);
                        }
                    });
                });

                $('.view-transaction').click(function() {
                    const transactionId = $(this).data('id');
                    loadTransactionData(transactionId);
                })

                // Show 'No Transaction Found' if table is empty
                var tbody = $(".table tbody");
                if (tbody.children('tr').length === 0) {
                    tbody.append('<tr><td colspan="8" class="text-center">No Transaction Found</td></tr>');
                }

                // Print for transaction form
                $('.print-btn').click(function() {
                    window.print();
                });
                
                // Print for transaction list
                $('.print-transactions-btn').click(function() {
                    prepareTransactionListPrint();
                    window.print();
                });

                // Update payment form submission
                $('#updatePaymentForm').submit(function(e) {
                    e.preventDefault();
                    updatePaymentStatus();
                });
            });

            // Global variables
            let txCurrentPage = 1;
            let txPageSize = 10;
            let txSearchTerm = '';
            let allTransactions = [];

            // Archive Logs variables
            let logCurrentPage = 1;
            let logPageSize = 10;
            let logSearchTerm = '';


            // Load staff for payment modal - fixed version
            function loadStaffForPaymentModal() {
                $.ajax({
                    url: '../backend/api/staff_api.php?action=getStaffsByRole&role=Secretary',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Staff API Response:', response);

                        const $staffSelect = $('#update_received_by');
                        $staffSelect.empty().append('<option value="">Select Staff</option>');

                        if (!response.success) {
                            console.error('API returned failure:', response.message);
                            $staffSelect.append('<option value="">Error: ' + response.message + '</option>');
                            return;
                        }

                        let staffArray = null;

                        // Check for staff data in the response structure
                        if (response.data) {
                            // Your actual case: response.data.staffs contains the array
                            if (response.data.staffs && Array.isArray(response.data.staffs)) {
                                staffArray = response.data.staffs;
                                console.log('Found staff data in response.data.staffs:', staffArray.length, 'items');
                            }
                            // If staffs is not found, try other possible locations
                            else if (Array.isArray(response.data)) {
                                staffArray = response.data;
                                console.log('Found staff data in response.data (direct array)');
                            } else if (response.data.data && Array.isArray(response.data.data)) {
                                staffArray = response.data.data;
                                console.log('Found staff data in response.data.data');
                            }
                        }

                        // If we found staff data, populate the dropdown
                        if (staffArray && Array.isArray(staffArray)) {
                            console.log('Processing staff array with', staffArray.length, 'items');

                            staffArray.forEach(staff => {
                                if (staff && staff.staff_id) {
                                    const displayName = staff.full_name || staff.username || 'Unknown';
                                    const role = staff.role || 'No Role';

                                    $staffSelect.append(
                                        $('<option></option>')
                                        .val(staff.staff_id)
                                        .text(`${displayName} (${role})`)
                                    );
                                }
                            });

                            if (staffArray.length === 0) {
                                $staffSelect.append('<option value="">No staff members found</option>');
                            } else {
                                console.log('Successfully populated staff dropdown with', staffArray.length, 'options');
                            }
                        } else {
                            console.error('Could not find staff data in any expected location.');
                            console.error('Response data structure:', response.data);
                            $staffSelect.append('<option value="">No staff data available</option>');

                            // Debug: Show what's actually in the response
                            if (response.data) {
                                console.log('Available keys in response.data:', Object.keys(response.data));
                                if (response.data.staffs) {
                                    console.log('response.data.staffs exists but is not array:', typeof response.data.staffs, response.data.staffs);
                                }
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading staff:', error);
                        console.error('Response text:', xhr.responseText);

                        const $staffSelect = $('#updatePaymentForm select[name="received_by"]');
                        $staffSelect.append('<option value="">Error loading staff list</option>');
                    }
                });
            }

            // Update payment status
            function updatePaymentStatus() {
                const paymentMethod = $('select[name="payment_method"]').val();
                const referenceNumber = $('#reference_number').val();
                const paymentDueDate = $('#payment_due_date').val();

                // Validation
                if (!paymentMethod) {
                    showAlert('danger', 'Please select a payment method');
                    return;
                }

                if (paymentMethod === 'GCash' && !referenceNumber) {
                    showAlert('danger', 'Please enter GCash reference number');
                    return;
                }

                const formData = {
                    transaction_id: $('#update_transaction_id').val(),
                    payment_status: $('select[name="payment_status"]').val(),
                    received_by: $('select[name="received_by"]').val(),
                    payment_method: paymentMethod,
                    reference_number: paymentMethod === 'GCash' ? referenceNumber : '',
                    payment_due_date: paymentDueDate || null
                };

                if (!formData.received_by) {
                    showAlert('danger', 'Please select staff who received the payment');
                    return;
                }

                showLoading(true, '#updatePaymentModal .modal-body');

                $.ajax({
                    url: '../backend/api/transaction_api.php?action=updatePayment',
                    method: 'PUT',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', 'Payment status updated successfully');
                            $('#updatePaymentModal').modal('hide');
                            loadTransactions();
                        } else {
                            throw new Error(response.message || 'Failed to update payment status');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating payment:', error);
                        console.error('Response text:', xhr.responseText);
                        console.error('Response JSON:', xhr.responseJSON);
                        let errorMsg = 'Failed to update payment status';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg += ': ' + xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const resp = JSON.parse(xhr.responseText);
                                if (resp.message) errorMsg += ': ' + resp.message;
                            } catch(e) {}
                        }
                        showAlert('danger', errorMsg);
                    },
                    complete: function() {
                        showLoading(false, '#updatePaymentModal .modal-body');
                    }
                });
            }

            // Handle payment method change - show/hide reference number field
            $(document).on('change', '#payment_method', function() {
                if ($(this).val() === 'GCash') {
                    $('#reference_number_group').slideDown();
                    $('#reference_number').prop('required', true);
                } else {
                    $('#reference_number_group').slideUp();
                    $('#reference_number').prop('required', false).val('');
                }
            });

            // Set Payment Due Date function
            function setPaymentDueDate(transactionId, paymentDueDate) {
                if (!transactionId || !paymentDueDate) {
                    showAlert('danger', 'Transaction ID and payment due date are required');
                    return;
                }

                // Validate date format (YYYY-MM-DD)
                if (!/^\d{4}-\d{2}-\d{2}$/.test(paymentDueDate)) {
                    showAlert('danger', 'Invalid date format. Use YYYY-MM-DD');
                    return;
                }

                const formData = {
                    transaction_id: transactionId,
                    payment_due_date: paymentDueDate
                };

                $.ajax({
                    url: '../backend/api/transaction_api.php?action=setPaymentDueDate',
                    method: 'PUT',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', 'Payment due date set successfully');
                            loadTransactions();
                        } else {
                            throw new Error(response.message || 'Failed to set payment due date');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error setting payment due date:', error);
                        let errorMsg = 'Failed to set payment due date';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg += ': ' + xhr.responseJSON.message;
                        }
                        showAlert('danger', errorMsg);
                    }
                });
            }

            // Load paginated transactions
            function loadTransactions(page = 1) {
                showLoading(true, '.card-body');

                const params = new URLSearchParams({
                    action: 'getAll',
                    page: String(page),
                    itemsPerPage: String(txPageSize),
                    search: txSearchTerm || ''
                });

                $.ajax({
                    url: '../backend/api/transaction_api.php?' + params.toString(),
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            const payload = response.data; // {transactions, currentPage, totalPages, totalItems, itemsPerPage}
                            const transactions = Array.isArray(payload.transactions) ? payload.transactions : [];
                            allTransactions = transactions; // used by sort/filter/display
                            txCurrentPage = Number(payload.currentPage) || 1;
                            txPageSize = Number(payload.itemsPerPage) || txPageSize;
                            applySortingAndFiltering();

                            // Footer info/pagination
                            const start = (txCurrentPage - 1) * txPageSize + 1;
                            const end = (txCurrentPage - 1) * txPageSize + transactions.length;
                            updateTxPaginationInfo(payload.totalItems || 0, start, end);
                            renderTxPagination(payload.totalPages || 1);

                            if (typeof updateTransactionStats === 'function') {
                                updateTransactionStats(transactions);
                            }
                        } else {
                            throw new Error(response.message || 'Failed to load transactions');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading transactions:', error);
                        console.error('Response:', xhr.responseText);
                        showAlert('danger', 'Failed to load transactions: ' + (xhr.responseJSON?.message || error));
                        $('#transactionsTableBody').html('<tr><td colspan="9" class="text-center">Error loading transactions</td></tr>');
                    },
                    complete: function() {
                        showLoading(false, '.card-body');
                    }
                });
            }
                
                // Prepare transaction list for printing
                function prepareTransactionListPrint() {
                    // Set current date
                    const now = new Date();
                    $('#print-date').text(now.toLocaleDateString() + ' ' + now.toLocaleTimeString());
                    
                    // Copy all transactions to print table
                    const $printBody = $('#transactionsPrintTableBody');
                    $printBody.empty();
                    
                    // Get all current transactions from the main table
                    $('#transactionsTableBody tr').each(function() {
                        const $row = $(this);
                        const cells = $row.find('td');
                        
                        if (cells.length > 0) {
                            const printRow = `
                                <tr>
                                    <td>${$(cells[0]).text()}</td>
                                    <td>${$(cells[1]).text()}</td>
                                    <td>${$(cells[2]).text()}</td>
                                    <td>${$(cells[3]).text()}</td>
                                    <td>${$(cells[4]).text()}</td>
                                    <td>${$(cells[5]).text()}</td>
                                    <td>${$(cells[6]).text()}</td>
                                </tr>
                            `;
                            $printBody.append(printRow);
                        }
                    });
                }
                
            // Sorting and Filtering functionality
                
                // Apply sorting and filtering
                function applySortingAndFiltering() {
                let filteredTransactions = [...allTransactions];
                    
                    // Apply filter
                    const filterValue = $('#filterBy').val();
                    if (filterValue !== 'all') {
                        filteredTransactions = filteredTransactions.filter(transaction => 
                        transaction.payment_status === filterValue
                        );
                    }
                    
                    // Apply sorting
                    const dateSortValue = $('#dateSort').val();
                    const amountSortValue = $('#amountSort').val();
                    const transactionIdSortValue = $('#transactionIdSort').val();
                    
                    // Transaction ID sorting
                    if (transactionIdSortValue === 'ascending') {
                        filteredTransactions.sort((a, b) => a.id - b.id);
                    } else if (transactionIdSortValue === 'descending') {
                        filteredTransactions.sort((a, b) => b.id - a.id);
                    }
                    
                    // Date sorting
                    if (dateSortValue === 'latest') {
                    filteredTransactions.sort((a, b) => new Date(b.payment_date) - new Date(a.payment_date));
                    } else if (dateSortValue === 'oldest') {
                    filteredTransactions.sort((a, b) => new Date(a.payment_date) - new Date(b.payment_date));
                    }
                    
                    // Amount sorting
                    if (amountSortValue === 'highest') {
                    filteredTransactions.sort((a, b) => b.total_amount - a.total_amount);
                    } else if (amountSortValue === 'lowest') {
                    filteredTransactions.sort((a, b) => a.total_amount - b.total_amount);
                }

                renderTransactions(filteredTransactions);
            }

            // Render transactions to table
            function renderTransactions(transactions) {
                    const $tableBody = $('#transactionsTableBody');
                    $tableBody.empty();
                    
                if (transactions.length === 0) {
                        $tableBody.html('<tr><td colspan="9" class="text-center">No transactions found</td></tr>');
                        return;
                    }
                    
                const html = transactions.map(transaction => {
                    const statusClass = transaction.payment_status === 'Paid' ? 'status-paid' : 'status-pending';
                    const paymentDate = transaction.payment_date || '-';
                    const paymentDueDate = transaction.payment_due_date || '-';
                        
                        return `
                            <tr>
                                <td>${transaction.id}</td>
                        <td>${transaction.customer_name}</td>
                        <td>${transaction.appliance_name}</td>
                        <td>₱${parseFloat(transaction.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td><span class="status-badge ${statusClass}">${transaction.payment_status}</span></td>
                                <td>${paymentDate}</td>
                                <td>${paymentDueDate}</td>
                        <td>${transaction.received_by_name || transaction.received_by}</td>
                                <td class="no-print">
                                    <a href="#" class="update-payment" data-id="${transaction.id}" data-toggle="modal" data-target="#updatePaymentModal">
                                        <i class="material-icons" data-toggle="tooltip" title="Update Payment">payment</i>
                                    </a>
                                    <a href="#" class="view-transaction" data-toggle="modal" data-target="#transactionFormModal" data-id="${transaction.id}">
                                        <i class="material-icons" data-toggle="tooltip" title="View">visibility</i>
                                    </a>
                                </td>
                            </tr>
                        `;
                    }).join('');
                    
                    $tableBody.html(html);
                    
                    // Re-bind events after rendering
                    $('.update-payment').click(function() {
                        $('#update_transaction_id').val($(this).data('id'));
                    });

                $('.view-transaction').click(function() {
                    const transactionId = $(this).data('id');
                    loadTransactionData(transactionId);
                });
            }

            // Initialize search functionality (server-side)
            $('#searchInput').on('keyup', function() {
                txSearchTerm = ($(this).val() || '').toLowerCase().trim();
                txCurrentPage = 1;
                loadTransactions(1);
            });

            // Server-side search; no DOM-only filter
            function updateTxPaginationInfo(totalItems, start, end) {
                const $info = $('#txPaginationInfo');
                if (totalItems === 0) {
                    $info.text('Showing 0 to 0 of 0 entries');
                    return;
                }
                $info.text(`Showing ${start} to ${Math.min(end, totalItems)} of ${totalItems} entries`);
            }

            function renderTxPagination(totalPages) {
                const $pagination = $('#txPagination');
                $pagination.empty();

                const prevDisabled = txCurrentPage === 1 ? 'disabled' : '';
                $pagination.append(`<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${txCurrentPage - 1}">Previous</a></li>`);

                const maxVisible = 5;
                let start = Math.max(1, txCurrentPage - Math.floor(maxVisible / 2));
                let end = Math.min(totalPages, start + maxVisible - 1);
                if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);

                if (start > 1) {
                    $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
                    if (start > 2) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }

                for (let i = start; i <= end; i++) {
                    const active = i === txCurrentPage ? 'active' : '';
                    $pagination.append(`<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
                }

                if (end < totalPages) {
                    if (end < totalPages - 1) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
                }

                if (totalPages < 1) totalPages = 1;
                const nextDisabled = txCurrentPage === totalPages ? 'disabled' : '';
                $pagination.append(`<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${txCurrentPage + 1}">Next</a></li>`);

                $pagination.find('.page-link').on('click', function(e) {
                    e.preventDefault();
                    const page = parseInt($(this).data('page'));
                    if (!isNaN(page) && page !== txCurrentPage) {
                        txCurrentPage = page;
                        loadTransactions(txCurrentPage);
                    }
                });
            }

            // Initialize sorting and filtering
            $('#dateSort, #amountSort, #transactionIdSort, #filterBy').change(function() {
                applySortingAndFiltering();
            });

            function loadTransactionData(transactionId) {
                showLoading(true, '#transactionFormModal .modal-body');

                $.ajax({
                    url: '../backend/api/transaction_api.php?action=getById&id=' + transactionId,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Full API response:', response);

                        if (response.success && response.data) {
                            // Handle both array and object responses
                            let transactionData;
                            if (Array.isArray(response.data)) {
                                console.log('Data is an array, taking first element:', response.data[0]);
                                transactionData = response.data[0];
                            } else {
                                transactionData = response.data;
                            }

                            console.log('Transaction data to populate:', transactionData);
                            console.log('Parts data:', transactionData.parts);

                            populateTransactionForm(transactionData);
                        } else {
                            throw new Error(response.message || 'Failed to load service report data');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading service report:', error);
                        showAlert('danger', 'Failed to load service report: ' + (xhr.responseJSON?.message || error));
                    },
                    complete: function() {
                        showLoading(false, '#transactionFormModal .modal-body');
                    }
                });
            }

            function populateTransactionForm(data) {
                console.log('Populating transaction form with: ', data);

                const totalAmount = parseFloat(data.total_amount || 0);

                // Basic fields
                $('input[name="customer"]').val(data.customer_name || '');
                $('input[name="appliance"]').val(data.appliance_name || '');
                $('input[name="date_in"]').val(data.date_in || '');
                $('#status-field').val(data.status || '');
                $('input[name="dealer"]').val(data.dealer || '');
                $('input[name="dop"]').val(data.dop || '');
                $('input[name="date_pulled_out"]').val(data.date_pulled_out || '');
                $('#findings-field').val(data.findings || '');
                $('input[name="remarks"]').val(data.remarks || '');

                // Location checkboxes
                const location = Array.isArray(data.location) ? data.location : JSON.parse(data.location || '[]');
                $('#shop-field').prop('checked', location.includes('shop'));
                $('#field-field').prop('checked', location.includes('field'));
                $('#out_wty-field').prop('checked', location.includes('out_wty'));

                // Service type checkboxes
                let serviceTypes = [];
                if (data.service_types) {
                    serviceTypes = Array.isArray(data.service_types) ? data.service_types : JSON.parse(data.service_types || '[]');
                } else if (data.service_type) {
                    serviceTypes = Array.isArray(data.service_type) ? data.service_type : JSON.parse(data.service_type || '[]');
                }
                $('#installation-field').prop('checked', serviceTypes.includes('installation'));
                $('#repair-field').prop('checked', serviceTypes.includes('repair'));
                $('#cleaning-field').prop('checked', serviceTypes.includes('cleaning'));
                $('#checkup-field').prop('checked', serviceTypes.includes('checkup'));

                // Dates and other fields
                $('input[name="date_repaired"]').val(data.date_repaired || '');
                $('input[name="date_delivered"]').val(data.date_delivered || '');
                $('input[name="complaint"]').val(data.complaint || '');

                // Staff fields
                $('input[name="receptionist"]').val(data.receptionist || '');
                $('input[name="manager"]').val(data.manager || '');
                $('input[name="technician"]').val(data.technician || '');
                $('input[name="released_by"]').val(data.released_by || '');

                // Payment fields
                $('input[name="payment_status"]').val(data.payment_status || 'Pending');
                $('input[name="payment_method"]').val(data.payment_method || '');
                $('input[name="reference_number"]').val(data.reference_number || '');
                $('input[name="payment_date"]').val(data.payment_date || '');

                $('#update_report_id').val(data.report_id);

                // DIRECTLY USE THE VALUES FROM DATABASE - NO RECALCULATION NEEDED
                // These values were already calculated when the service report was created
                $('#labor-amount').val(parseFloat(data.labor || 0).toFixed(2));
                $('#pullout-delivery').val(parseFloat(data.pullout_delivery || 0).toFixed(2));
                $('#total-serviceCharge').val(parseFloat(data.service_charge || 0).toFixed(2));
                $('#parts-charge').val(parseFloat(data.parts_total_charge || 0).toFixed(2));

                // Display the exact total amount from the database
                $('input[name="total_amount"]').val(parseFloat(data.total_amount || 0).toFixed(2));

                // Populate parts (this should not affect the total amount calculation)
                populatePartsUsed(data.parts || []);

                console.log('Database values used (no recalculation):', {
                    labor: data.labor,
                    pullout_delivery: data.pullout_delivery,
                    service_charge: data.service_charge,
                    parts_total_charge: data.parts_total_charge,
                    total_amount: data.total_amount
                });

                disableFormEditing();
            }

            function populatePartsUsed(parts) {
                console.log('Populating parts with data:', parts);

                // Clear all existing parts rows except the first one
                $('.parts-row:not(:first)').remove();

                // Clear the first row
                $('.parts-row:first').find('input[name="part_name[]"]').val('');
                $('.parts-row:first').find('input[name="quantity[]"]').val('');
                $('.parts-row:first').find('input[name="part_amount[]"]').val('');

                if (parts && parts.length > 0) {
                    console.log('Parts data received:', parts);

                    parts.forEach((part, index) => {
                        let $row;

                        if (index === 0) {
                            $row = $('.parts-row:first');
                        } else {
                            // Create new row for additional parts
                            $row = $('.parts-row:first').clone(true, true);
                            $('#parts-container').append($row);
                        }

                        // Populate part data
                        $row.find('input[name="part_name[]"]').val(part.part_name || '');
                        $row.find('input[name="quantity[]"]').val(part.quantity || '');

                        // Calculate the total amount for this part (quantity * unit_price)
                        const quantity = parseFloat(part.quantity || 0);
                        const unitPrice = parseFloat(part.unit_price || 0);
                        const totalAmount = (quantity * unitPrice).toFixed(2);

                        $row.find('input[name="part_amount[]"]').val(totalAmount);

                        console.log(`Part ${index + 1}:`, {
                            name: part.part_name,
                            quantity: quantity,
                            unitPrice: unitPrice,
                            totalAmount: totalAmount,
                            rawData: part
                        });
                    });
                } else {
                    console.log('No parts data found');
                }
            }

            function addPartRow() {
                const newRow = $('.parts-row').first().clone(true, true);
                newRow.find('input').val('');
                $('#parts-container').append(newRow);
                return newRow;
            }

            function showAlert(type, message) {
                $('.alert-notification').remove();
                const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            `;
                $('body').prepend(alertHtml);
                setTimeout(() => $('.alert').alert('close'), 5000);
            }

            function showLoading(show, element) {
                const $element = $(element);
                if (show) {
                    $element.addClass('loading');
                    $element.append('<div class="loading-overlay"><div class="spinner-border"></div></div>');
                } else {
                    $element.removeClass('loading');
                    $element.find('.loading-overlay').remove();
                }
            }

            let isEditing = false;
            let originalFormData = {};

            $('.edit-btn').click(function() {
                enableFormEditing();
            });

            $('.finalize-edit-btn').click(function() {
                finalizeEdit();
            });

            function enableFormEditing() {
                if (isEditing) return;

                isEditing = true;
                originalFormData = gatherFormData();

                // Enable form fields
                $('#transactionForm input:not([type="hidden"])').prop('readonly', false);
                $('#transactionForm select').prop('disabled', false);
                $('#transactionForm textarea').prop('readonly', false);
                $('#shop-field, #field-field, #out_wty-field').prop('disabled', false);
                $('#installation-field, #repair-field, #cleaning-field, #checkup-field').prop('disabled', false);

                // Remove all calculation event listeners since we're using database values
                $('.edit-btn').hide();
                $('.finalize-edit-btn').show();

                $('#transactionForm input, #transactionForm select, #transactionForm textarea')
                    .not('[type="hidden"]')
                    .css('background-color', '#fff');

                showAlert('info', 'You can now edit the service report. Click "Finalize Edit" to save changes.');
            }

            function disableFormEditing() {
                isEditing = false;

                // Remove event listeners
                $('#labor-amount, #pullout-delivery').off('input');
                $(document).off('input', 'input[name="part_amount[]"]');
                $(document).off('input', 'input[name="quantity[]"], input[name="part_amount[]"]');

                // Disable form fields
                $('#transactionForm input:not([type="hidden"])').prop('readonly', true);
                $('#transactionForm select').prop('disabled', true);
                $('#transactionForm textarea').prop('readonly', true);
                $('#shop-field, #field-field, #out_wty-field').prop('disabled', true);
                $('#installation-field, #repair-field, #cleaning-field, #checkup-field').prop('disabled', true);

                $('.edit-btn').show();
                $('.finalize-edit-btn').hide();

                $('#transactionForm input, #transactionForm select, #transactionForm textarea')
                    .not('[type="hidden"]')
                    .css('background-color', '#f8f9fa');
            }

            function finalizeEdit() {
                if (!isEditing) return;

                const updatedData = gatherFormData();
                const reportId = $('#update_report_id').val();

                if (!reportId) {
                    showAlert('danger', 'No report ID found. Cannot update');
                    return;
                }

                if (!validateEditForm(updatedData)) {
                    return;
                }

                showLoading(true, '#transactionFormModal .modal-body');

                $.ajax({
                    url: '../backend/api/service_api.php?action=update&id=' + reportId,
                    method: 'PUT',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(updatedData),
                    success: function(response) {
                        if (response.success) {
                            disableFormEditing();
                            reloadServiceReportData(reportId);
                        } else {
                            throw new Error(response.message || 'Failed to update service report');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating service report: ', error);
                        showAlert('danger', 'Failed to update service report: ' + (xhr.responseJSON.message || error));
                        populateFormData(originalFormData);
                    },
                    complete: function() {
                        showLoading(false, '#transactionFormModal .modal-body');
                        }
                    });
                }
                
            function reloadServiceReportData(reportId) {
                showLoading(true, '#transactionFormModal .modal-body');

                $.ajax({
                    url: '../backend/api/service_api.php?action=getById&id=' + reportId,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            let serviceData;
                            if (Array.isArray(response.data)) {
                                serviceData = response.data[0];
                            } else {
                                serviceData = response.data;
                            }

                            populateTransactionForm(serviceData);
                            showAlert('success', 'Service report updated and reloaded successfully');
                        } else {
                            throw new Error(response.message || 'Failed to reload service report data');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error reloading service report:', error);
                        // Don't show error alert here since the update was successful
                        // Just log it and close the loading indicator
                    },
                    complete: function() {
                        showLoading(false, '#transactionFormModal .modal-body');
                    }
                });
            }

            function gatherFormData() {
                const formatDateForPHP = (dateStr) => {
                    if (!dateStr) return null;
                    return new Date(dateStr).toISOString().split('T')[0];
                };

                const formData = {
                    customer_name: $('#customer-field').val(),
                    appliance_name: $('#appliance-field').val(),
                    date_in: formatDateForPHP($('#date-in-field').val()),
                    status: $('#status-field').val(),
                    dealer: $('input[name="dealer"]').val(),
                    dop: formatDateForPHP($('input[name="dop"]').val()),
                    date_pulled_out: formatDateForPHP($('input[name="date_pulled_out"]').val()),
                    findings: $('#findings-field').val(),
                    remarks: $('input[name="remarks"]').val(),
                    location: [],

                    service_types: [],
                    date_repaired: formatDateForPHP($('input[name="date_repaired"]').val()),
                    date_delivered: formatDateForPHP($('input[name="date_delivered"]').val()),
                    complaint: $('input[name="complaint"]').val(),
                    labor: parseFloat($('#labor-amount').val()) || 0,
                    pullout_delivery: parseFloat($('#pullout-delivery').val()) || 0,
                    service_charge: parseFloat($('#total-serviceCharge').val()) || 0,
                    parts_total_charge: parseFloat($('#parts-charge').val()) || 0,
                    total_amount: parseFloat($('input[name="total_amount"]').val()) || 0,
                    receptionist: $('input[name="receptionist"]').val(),
                    manager: $('input[name="manager"]').val(),
                    technician: $('input[name="technician"]').val(),
                    released_by: $('input[name="released_by"]').val(),

                    parts: []
                };

                // Capture location checkboxes
                if ($('#shop-field').is(':checked')) formData.location.push('shop');
                if ($('#field-field').is(':checked')) formData.location.push('field');
                if ($('#out_wty-field').is(':checked')) formData.location.push('out_wty');

                // Capture service type checkboxes
                if ($('#installation-field').is(':checked')) formData.service_types.push('installation');
                if ($('#repair-field').is(':checked')) formData.service_types.push('repair');
                if ($('#cleaning-field').is(':checked')) formData.service_types.push('cleaning');
                if ($('#checkup-field').is(':checked')) formData.service_types.push('checkup');

                // Capture parts data
                $('.parts-row').each(function() {
                    const partName = $(this).find('input[name="part_name[]"]').val();
                    const quantity = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;
                    const totalPrice = parseFloat($(this).find('input[name="part_amount[]"]').val()) || 0;
                    const unitPrice = quantity > 0 ? totalPrice / quantity : 0;

                    if (partName && quantity > 0) {
                        formData.parts.push({
                            part_name: partName,
                            quantity: quantity,
                            unit_price: unitPrice,
                            parts_total: totalPrice
                        });
                    }
                });

                return formData;
            }

            // Service prices for calculation
            let servicePrices = {};

            // Load service prices from API
            async function loadServicePrices() {
                try {
                    const response = await $.ajax({
                        url: '../backend/api/service_price_api.php?action=getAll',
                        type: 'GET',
                        dataType: 'json'
                    });

                    if (response && response.success) {
                        servicePrices = response.data;
                        console.log('Loaded service prices: ', servicePrices);
                    } else {
                        // fallback to default prices 
                        servicePrices = {
                            installation: 500,
                            repair: 300,
                            cleaning: 200,
                            checkup: 150
                        };
                        console.warn('Using fallback service prices');
                    }

                } catch (error) {
                    console.error('Failed to load service prices: ', error);

                    // fallback to default prices 
                    servicePrices = {
                        installation: 500,
                        repair: 300,
                        cleaning: 200,
                        checkup: 150
                    };
                    console.warn('Using fallback service prices');
                }
            }

            function calculateTransactionTotal() {
                const laborCharge = parseFloat($('#labor-amount').val()) || 0;
                const deliveryCharge = parseFloat($('#pullout-delivery').val()) || 0;
                const partsTotal = parseFloat($('#parts-charge').val()) || 0;

                // Calculate service charge from selected service types
                const serviceCharge = calculateServiceCharge();

                // Total Service Charge = Only service charge from checkboxes
                const totalServiceCharge = serviceCharge.toFixed(2);

                // Calculate grand total (labor + delivery + service charge + parts)
                const grandTotal = (
                    parseFloat(laborCharge.toFixed(2)) +
                    parseFloat(deliveryCharge.toFixed(2)) +
                    parseFloat(serviceCharge.toFixed(2)) +
                    parseFloat(partsTotal.toFixed(2))
                ).toFixed(2);

                // Update the total amount field (grand total)
                $('input[name="total_amount"]').val(grandTotal);

                // Update the total field in charged details section to match total amount
                $('#total-serviceCharge').val(grandTotal);

                console.log('Calculated totals:', {
                    labor: laborCharge,
                    delivery: deliveryCharge,
                    service: serviceCharge,
                    totalServiceCharge: totalServiceCharge,
                    parts: partsTotal,
                    grandTotal: grandTotal
                });
            }

            function calculateServiceCharge() {
                let total = 0;

                if ($('#installation-field').is(':checked')) total += servicePrices.installation;
                if ($('#repair-field').is(':checked')) total += servicePrices.repair;
                if ($('#cleaning-field').is(':checked')) total += servicePrices.cleaning;
                if ($('#checkup-field').is(':checked')) total += servicePrices.checkup;

                return parseFloat(total.toFixed(2));
            }

            function validateEditForm(data) {
                if (!data.customer_name) {
                    showAlert('danger', 'Customer name is required');
                }
                if (!data.appliance_name) {
                    showAlert('danger', 'Appliancem is required');
                }
                if (!data.date_in) {
                    showAlert('danger', 'Date in is required');
                    return false;
                }
                if (!data.status) {
                    showAlert('danger', 'Status is required');
                    return false;
                }
                return true;
            }
    </script>

    <script>
    // ==================== ARCHIVE LOGS FUNCTIONALITY ====================
    
    // Load archive logs with pagination
    function loadArchiveLogs(page = 1) {
        showLoading(true, '#logs .card-body');

        const params = new URLSearchParams({
            action: 'getAll',
            page: String(page),
            itemsPerPage: String(logPageSize),
            search: logSearchTerm || ''
        });

        $.ajax({
            url: '../backend/api/archive_api.php?' + params.toString(),
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Archive logs response:', response);
                
                if (response.success && response.data) {
                    const payload = response.data;
                    const archives = Array.isArray(payload.archives) ? payload.archives : [];
                    
                    logCurrentPage = Number(payload.current_page) || 1;
                    const totalPages = Number(payload.total_pages) || 1;
                    const totalItems = Number(payload.total_items) || 0;
                    
                    renderArchiveLogs(archives);
                    
                    // Update pagination info
                    const start = (logCurrentPage - 1) * logPageSize + 1;
                    const end = (logCurrentPage - 1) * logPageSize + archives.length;
                    updateLogPaginationInfo(totalItems, start, end);
                    renderLogPagination(totalPages);
                } else {
                    throw new Error(response.message || 'Failed to load archive logs');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading archive logs:', error);
                showAlert('danger', 'Failed to load archive logs: ' + (xhr.responseJSON?.message || error));
                $('#logsTableBody').html('<tr><td colspan="7" class="text-center">Error loading archive logs</td></tr>');
            },
            complete: function() {
                showLoading(false, '#logs .card-body');
            }
        });
    }

    // Render archive logs to table
    function renderArchiveLogs(archives) {
        const $tableBody = $('#logsTableBody');
        $tableBody.empty();
        
        if (archives.length === 0) {
            $tableBody.html('<tr><td colspan="7" class="text-center">No archive logs found</td></tr>');
            return;
        }
        
        const html = archives.map(archive => {
            const deletedAt = new Date(archive.deleted_at).toLocaleString();
            const reason = archive.reason || 'No reason provided';
            const deletedBy = archive.deleted_by || 'System';
            
            return `
                <tr>
                    <td>${archive.id}</td>
                    <td><span class="badge badge-secondary">${archive.table_name}</span></td>
                    <td>${archive.record_id}</td>
                    <td>${reason}</td>
                    <td>${deletedBy}</td>
                    <td>${deletedAt}</td>
                    <td class="no-print">
                        <a href="#" class="view-archive-details" data-id="${archive.id}" data-details='${JSON.stringify(archive).replace(/'/g, "&#39;")}' title="View Details">
                            <i class="material-icons" data-toggle="tooltip">visibility</i>
                        </a>
                    </td>
                </tr>
            `;
        }).join('');
        
        $tableBody.html(html);
        
        // Re-bind events after rendering
        $('.view-archive-details').click(function(e) {
            e.preventDefault();
            const details = $(this).data('details');
            showArchiveDetails(details);
        });
    }

    // Show archive details in a modal/alert
    function showArchiveDetails(archive) {
        let detailsHtml = `
            <div style="text-align: left;">
                <h5>Archive Record Details</h5>
                <p><strong>ID:</strong> ${archive.id}</p>
                <p><strong>Table:</strong> ${archive.table_name}</p>
                <p><strong>Record ID:</strong> ${archive.record_id}</p>
                <p><strong>Deleted At:</strong> ${new Date(archive.deleted_at).toLocaleString()}</p>
                <p><strong>Deleted By:</strong> ${archive.deleted_by || 'System'}</p>
                <p><strong>Reason:</strong> ${archive.reason || 'No reason provided'}</p>
                <hr>
                <h6>Deleted Data:</h6>
                <pre style="max-height: 300px; overflow-y: auto; background: #f5f5f5; padding: 10px; border-radius: 4px;">${JSON.stringify(archive.deleted_data, null, 2)}</pre>
            </div>
        `;
        
        // Create a custom modal or use a simple alert
        if ($('#archiveDetailsModal').length === 0) {
            $('body').append(`
                <div class="modal fade" id="archiveDetailsModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Archive Details</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body" id="archiveDetailsBody"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
        
        $('#archiveDetailsBody').html(detailsHtml);
        $('#archiveDetailsModal').modal('show');
    }

    // Update log pagination info
    function updateLogPaginationInfo(totalItems, start, end) {
        const $info = $('#logPaginationInfo');
        if (totalItems === 0) {
            $info.text('Showing 0 to 0 of 0 entries');
            return;
        }
        $info.text(`Showing ${start} to ${Math.min(end, totalItems)} of ${totalItems} entries`);
    }

    // Render log pagination
    function renderLogPagination(totalPages) {
        const $pagination = $('#logPagination');
        $pagination.empty();

        const prevDisabled = logCurrentPage === 1 ? 'disabled' : '';
        $pagination.append(`<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${logCurrentPage - 1}">Previous</a></li>`);

        const maxVisible = 5;
        let start = Math.max(1, logCurrentPage - Math.floor(maxVisible / 2));
        let end = Math.min(totalPages, start + maxVisible - 1);
        if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);

        if (start > 1) {
            $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
            if (start > 2) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }

        for (let i = start; i <= end; i++) {
            const active = i === logCurrentPage ? 'active' : '';
            $pagination.append(`<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
        }

        if (end < totalPages) {
            if (end < totalPages - 1) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
            $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
        }

        if (totalPages < 1) totalPages = 1;
        const nextDisabled = logCurrentPage === totalPages ? 'disabled' : '';
        $pagination.append(`<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${logCurrentPage + 1}">Next</a></li>`);

        $pagination.find('.page-link').on('click', function(e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            if (!isNaN(page) && page !== logCurrentPage) {
                logCurrentPage = page;
                loadArchiveLogs(logCurrentPage);
            }
        });
    }

    // Initialize log search functionality
    $('#logSearch').on('keyup', function() {
        logSearchTerm = ($(this).val() || '').toLowerCase().trim();
        logCurrentPage = 1;
        loadArchiveLogs(1);
    });

    // Tab switch event - load logs when tab is shown
    $('#logs-tab').on('shown.bs.tab', function (e) {
        if ($('#logsTableBody tr').length === 0) {
            loadArchiveLogs(1);
        }
    });

    </script>
</body>

</html>