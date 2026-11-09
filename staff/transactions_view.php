<?php
require_once __DIR__ . '/../bootstrap.php';
session_start();
require __DIR__ . '/../backend/handlers/authHandler.php';
$auth = new AuthHandler();
$userSession = $auth->requireAuth('staff');
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Transactions - View Only</title>
    <link rel="shortcut Icon" href="../img/Repair.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <style>
        .badge-paid {
            background-color: #28a745;
            color: white;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .filter-container {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group label {
            margin-bottom: 0;
            min-width: 80px;
            font-weight: 500;
        }

        .filter-group select,
        .filter-group input {
            min-width: 150px;
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

        .modal-dialog {
            max-width: 1100px;
        }

        .modal-title {
            color: #000000 !important;
            font-weight: 600;
        }

        .transaction-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .transaction-details-grid .form-group {
            margin-bottom: 0;
        }

        .transaction-details-grid .form-group.full-width {
            grid-column: 1 / -1;
        }

        .read-only-badge {
            background-color: #e7f3ff;
            color: #0056b3;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 10px;
        }

        @media print {
            .no-print,
            .filter-container,
            .pagination,
            .read-only-badge {
                display: none !important;
            }

            body {
                color: #000 !important;
            }

            .table {
                border-collapse: collapse;
            }

            .table th,
            .table td {
                border: 1px solid #000 !important;
                padding: 8px !important;
                color: #000 !important;
            }

            .badge {
                border: 1px solid #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Include Sidebar -->
        <?php include 'staff_sidebar.php'; ?>

        <div id="content">
            <!-- Navbar -->
            <?php
            $pageTitle = "Transactions";
            $pageCrumb = "View Only";
            include 'staffnavbar.php';
            ?>

            <div class="content-area">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="card-title mb-0">Transaction List</h5>
                            <span class="read-only-badge">
                                <i class="material-icons align-middle" style="font-size: 14px; vertical-align: middle;">visibility</i> VIEW ONLY
                            </span>
                        </div>
                        <button type="button" class="btn btn-light border print-transactions-btn" style="font-weight: 500;">
                            <i class="material-icons align-middle">print</i> Print List
                        </button>
                    </div>

                    <!-- Card Header with Filter -->
                    <div style="padding: 15px 20px; border-bottom: 1px solid #dee2e6;">
                        <div class="filter-container">
                            <!-- Search -->
                            <div class="filter-group">
                                <label for="searchInput">Search:</label>
                                <input type="text" id="searchInput" class="form-control" placeholder="Customer, Appliance...">
                            </div>

                            <!-- Sort by Date -->
                            <div class="filter-group">
                                <label for="dateSort">Sort:</label>
                                <select id="dateSort" class="form-control">
                                    <option value="">Date</option>
                                    <option value="latest">Latest First</option>
                                    <option value="oldest">Oldest First</option>
                                </select>
                            </div>

                            <!-- Filter by Status -->
                            <div class="filter-group">
                                <label for="filterBy">Status:</label>
                                <select id="filterBy" class="form-control">
                                    <option value="all">All</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="transactionsTable">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Customer</th>
                                        <th>Appliance</th>
                                        <th>Total Amount</th>
                                        <th>Payment Status</th>
                                        <th>Payment Date</th>
                                        <th>Received By</th>
                                        <th class="no-print">Details</th>
                                    </tr>
                                </thead>
                                <tbody id="transactionsTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="material-icons align-middle" style="font-size: 30px; opacity: 0.5;">hourglass_empty</i>
                                            <br>Loading transactions...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div class="text-muted">
                                <span id="paginationInfo">Showing 0 to 0 of 0 entries</span>
                            </div>
                            <nav aria-label="Transactions navigation">
                                <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Details Modal (Read-Only) -->
    <div class="modal fade" id="transactionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="transaction-details-grid">
                        <div class="form-group">
                            <label><strong>Transaction ID</strong></label>
                            <input type="text" class="form-control" id="modal_transaction_id" readonly>
                        </div>
                        <div class="form-group">
                            <label><strong>Customer Name</strong></label>
                            <input type="text" class="form-control" id="modal_customer_name" readonly>
                        </div>
                        <div class="form-group">
                            <label><strong>Appliance</strong></label>
                            <input type="text" class="form-control" id="modal_appliance_name" readonly>
                        </div>
                        <div class="form-group">
                            <label><strong>Total Amount</strong></label>
                            <input type="text" class="form-control" id="modal_total_amount" readonly>
                        </div>
                        <div class="form-group">
                            <label><strong>Payment Status</strong></label>
                            <input type="text" class="form-control" id="modal_payment_status" readonly>
                        </div>
                        <div class="form-group">
                            <label><strong>Payment Date</strong></label>
                            <input type="text" class="form-control" id="modal_payment_date" readonly>
                        </div>
                        <div class="form-group">
                            <label><strong>Received By</strong></label>
                            <input type="text" class="form-control" id="modal_received_by" readonly>
                        </div>
                        <div class="form-group">
                            <label><strong>Service Types</strong></label>
                            <textarea class="form-control" id="modal_service_types" readonly rows="2"></textarea>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3" role="alert">
                        <i class="material-icons align-middle" style="font-size: 18px; vertical-align: middle;">info</i>
                        This is a read-only view. You cannot modify transaction information.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/jquery-3.3.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>

    <script>
        const TRANSACTIONS_API = '../backend/api/transaction_api.php';
        let allTransactions = [];
        let currentPage = 1;
        const itemsPerPage = 10;

        $(document).ready(function() {
            loadTransactions();
            setupEventHandlers();
        });

        function setupEventHandlers() {
            // Search
            $(document).on('keyup', '#searchInput', function() {
                currentPage = 1;
                filterAndRender();
            });

            // Date Sort
            $(document).on('change', '#dateSort', function() {
                currentPage = 1;
                filterAndRender();
            });

            // Status Filter
            $(document).on('change', '#filterBy', function() {
                currentPage = 1;
                filterAndRender();
            });

            // Print
            $(document).on('click', '.print-transactions-btn', function() {
                window.print();
            });

            // View Details
            $(document).on('click', '.view-transaction', function() {
                const transactionId = $(this).data('id');
                showTransactionDetails(transactionId);
            });
        }

        function loadTransactions() {
            $.ajax({
                url: TRANSACTIONS_API + '?action=getAll',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        allTransactions = Array.isArray(response.data) ? response.data : (response.data.transactions || []);
                        filterAndRender();
                    } else {
                        showAlert('warning', 'No transactions found');
                        $('#transactionsTableBody').html('<tr><td colspan="8" class="text-center text-muted py-4">No transactions found</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading transactions:', error);
                    showAlert('danger', 'Error loading transactions: ' + error);
                    $('#transactionsTableBody').html('<tr><td colspan="8" class="text-center text-danger py-4">Error loading transactions</td></tr>');
                }
            });
        }

        function filterAndRender() {
            const searchQuery = $('#searchInput').val().toLowerCase();
            const dateSort = $('#dateSort').val();
            const statusFilter = $('#filterBy').val();

            let filtered = allTransactions.filter(tx => {
                const matchesSearch = !searchQuery || 
                    (tx.customer_name && tx.customer_name.toLowerCase().includes(searchQuery)) ||
                    (tx.appliance_name && tx.appliance_name.toLowerCase().includes(searchQuery));

                const matchesStatus = statusFilter === 'all' || tx.payment_status === statusFilter;

                return matchesSearch && matchesStatus;
            });

            // Sort
            if (dateSort === 'latest') {
                filtered.sort((a, b) => new Date(b.created_at || b.payment_date) - new Date(a.created_at || a.payment_date));
            } else if (dateSort === 'oldest') {
                filtered.sort((a, b) => new Date(a.created_at || a.payment_date) - new Date(b.created_at || b.payment_date));
            }

            renderTable(filtered);
        }

        function renderTable(transactions) {
            const tbody = $('#transactionsTableBody');
            tbody.empty();

            if (transactions.length === 0) {
                tbody.html('<tr><td colspan="8" class="text-center text-muted py-4">No transactions found</td></tr>');
                updatePagination(0);
                return;
            }

            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageTransactions = transactions.slice(start, end);

            pageTransactions.forEach(tx => {
                const statusBadge = tx.payment_status === 'Paid' 
                    ? '<span class="badge badge-paid">Paid</span>' 
                    : '<span class="badge badge-pending">Pending</span>';

                const row = `
                    <tr>
                        <td><strong>${tx.transaction_id || tx.id || 'N/A'}</strong></td>
                        <td>${tx.customer_name || 'N/A'}</td>
                        <td>${tx.appliance_name || 'N/A'}</td>
                        <td>₱${parseFloat(tx.total_amount || 0).toFixed(2)}</td>
                        <td>${statusBadge}</td>
                        <td>${tx.payment_date ? new Date(tx.payment_date).toLocaleDateString() : 'N/A'}</td>
                        <td>${tx.received_by || 'N/A'}</td>
                        <td class="no-print">
                            <button class="btn btn-sm btn-info view-transaction" data-id="${tx.transaction_id || tx.id}">
                                <i class="material-icons align-middle" style="font-size: 16px;">visibility</i> View
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            updatePagination(transactions.length);
        }

        function updatePagination(total) {
            const totalPages = Math.ceil(total / itemsPerPage);
            const paginationContainer = $('#pagination');
            paginationContainer.empty();

            $('#paginationInfo').text(`Showing ${total === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1} to ${Math.min(currentPage * itemsPerPage, total)} of ${total} entries`);

            if (totalPages <= 1) return;

            // Previous button
            const prevBtn = `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage - 1}); return false;">Previous</a>
                </li>
            `;
            paginationContainer.append(prevBtn);

            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                paginationContainer.append(`
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="goToPage(1); return false;">1</a>
                    </li>
                `);
                if (startPage > 2) {
                    paginationContainer.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationContainer.append(`
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
                    </li>
                `);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationContainer.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }
                paginationContainer.append(`
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a>
                    </li>
                `);
            }

            // Next button
            const nextBtn = `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${currentPage + 1}); return false;">Next</a>
                </li>
            `;
            paginationContainer.append(nextBtn);
        }

        function goToPage(page) {
            currentPage = page;
            filterAndRender();
            window.scrollTo(0, 0);
        }

        function showTransactionDetails(transactionId) {
            const transaction = allTransactions.find(t => (t.transaction_id || t.id) == transactionId);
            if (!transaction) {
                showAlert('danger', 'Transaction not found');
                return;
            }

            $('#modal_transaction_id').val(transaction.transaction_id || transaction.id || 'N/A');
            $('#modal_customer_name').val(transaction.customer_name || 'N/A');
            $('#modal_appliance_name').val(transaction.appliance_name || 'N/A');
            $('#modal_total_amount').val('₱' + parseFloat(transaction.total_amount || 0).toFixed(2));
            $('#modal_payment_status').val(transaction.payment_status || 'N/A');
            $('#modal_payment_date').val(transaction.payment_date ? new Date(transaction.payment_date).toLocaleDateString() : 'N/A');
            $('#modal_received_by').val(transaction.received_by || 'N/A');

            let serviceTypes = 'N/A';
            if (transaction.service_types) {
                if (Array.isArray(transaction.service_types)) {
                    serviceTypes = transaction.service_types.join(', ');
                } else if (typeof transaction.service_types === 'string') {
                    serviceTypes = transaction.service_types;
                }
            }
            $('#modal_service_types').val(serviceTypes);

            $('#transactionModal').modal('show');
        }

        function showAlert(type, message) {
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
                $('.alert-notification').fadeOut(() => $('.alert-notification').remove());
            }, 4000);
        }
    </script>
</body>

</html>
