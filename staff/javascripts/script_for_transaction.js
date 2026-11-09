// staff/javascripts/script_for_transaction.js - COMPLETE FIXED VERSION

// Global variables
let txCurrentPage = 1;
let txPageSize = 10;
let txSearchTerm = '';
let allTransactions = [];
let isEditing = false;
let originalFormData = {};
// Ensure a single global servicePrices store so multiple scripts don't redeclare it
window.servicePrices = window.servicePrices || {};
let txInitialized = false;

$(document).ready(function() {
    // Initialize transaction functionality if on transactions tab
    if ($('#transactionsTab').hasClass('active')) {
        initializeTransactionTab();
        txInitialized = true;
    }

    // Initialize when the tab is shown (Bootstrap tabs) or when the custom tab is clicked
    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function(e) {
        const target = $(e.target).attr('href');
        if ((target === '#transactionsTab' || target === ' #transactionsTab') && !txInitialized) {
            initializeTransactionTab();
            txInitialized = true;
        }
    });

    // For the custom tab UI used in staff/services_report.php: initialize when the transactions tab is clicked
    $(document).on('click', '.tab[data-tab="transactions"]', function() {
        if (!txInitialized) {
            initializeTransactionTab();
            txInitialized = true;
        }
    });
});

function initializeTransactionTab() {
    loadServicePrices();
    loadTransactions();
    loadStaffForPaymentModal();
    bindTransactionEvents();
}

function bindTransactionEvents() {
    // Set transaction ID for payment update
    $(document).on('click', '.update-payment', function() {
        $('#update_transaction_id').val($(this).data('id'));
    });

    $(document).on('click', '.view-transaction', function() {
        const transactionId = $(this).data('id');
        loadTransactionData(transactionId);
    });

    // Print for transaction form
    $('.print-btn').click(function() {
        window.print();
        // cleanup potential leftover modal backdrops after printing
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }, 500);
    });
    
    // Print for transaction list
    $('.print-transactions-btn').click(function() {
        prepareTransactionListPrint();
        window.print();
        // cleanup potential leftover modal backdrops after printing list
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        }, 500);
    });

    // Update payment form submission
    $('#updatePaymentForm').submit(function(e) {
        e.preventDefault();
        updatePaymentStatus();
    });
    
    // Initialize sorting and filtering
    $('#dateSort, #amountSort, #filterBy').change(function() {
        applySortingAndFiltering();
    });

    // Edit buttons
    $('.edit-btn').click(function() {
        enableFormEditing();
    });

    $('.finalize-edit-btn').click(function() {
        finalizeEdit();
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        txSearchTerm = ($(this).val() || '').toLowerCase().trim();
        txCurrentPage = 1;
        loadTransactions(1);
    });
}

// Load staff for payment modal
function loadStaffForPaymentModal() {
    $.ajax({
    url: '../backend/api/staff_api.php?action=getStaffsByRole',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Staff API Response:', response);

            const $staffSelect = $('#updatePaymentForm select[name="received_by"]');
            $staffSelect.empty().append('<option value="">Select Staff</option>');

            if (!response.success) {
                console.error('API returned failure:', response.message);
                $staffSelect.append('<option value="">Error: ' + response.message + '</option>');
                return;
            }

            let staffArray = null;

            if (response.data) {
                if (response.data.staffs && Array.isArray(response.data.staffs)) {
                    staffArray = response.data.staffs;
                } else if (Array.isArray(response.data)) {
                    staffArray = response.data;
                } else if (response.data.data && Array.isArray(response.data.data)) {
                    staffArray = response.data.data;
                }
            }

            if (staffArray && Array.isArray(staffArray)) {
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
            } else {
                $staffSelect.append('<option value="">No staff data available</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading staff:', error);
            const $staffSelect = $('#updatePaymentForm select[name="received_by"]');
            $staffSelect.append('<option value="">Error loading staff list</option>');
        }
    });
}

// Update payment status
function updatePaymentStatus() {
    const formData = {
        transaction_id: $('#update_transaction_id').val(),
        payment_status: $('select[name="payment_status"]').val(),
        received_by: $('select[name="received_by"]').val()
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
            showAlert('danger', 'Failed to update payment status: ' + (xhr.responseJSON?.message || error));
        },
        complete: function() {
            showLoading(false, '#updatePaymentModal .modal-body');
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
                const payload = response.data;
                const transactions = Array.isArray(payload.transactions) ? payload.transactions : [];
                allTransactions = transactions;
                txCurrentPage = Number(payload.currentPage) || 1;
                txPageSize = Number(payload.itemsPerPage) || txPageSize;
                applySortingAndFiltering();

                const start = (txCurrentPage - 1) * txPageSize + 1;
                const end = (txCurrentPage - 1) * txPageSize + transactions.length;
                updateTxPaginationInfo(payload.totalItems || 0, start, end);
                renderTxPagination(payload.totalPages || 1);
            } else {
                throw new Error(response.message || 'Failed to load transactions');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading transactions:', error);
            showAlert('danger', 'Failed to load transactions: ' + (xhr.responseJSON?.message || error));
            $('#transactionsTableBody').html('<tr><td colspan="8" class="text-center">Error loading transactions</td></tr>');
        },
        complete: function() {
            showLoading(false, '.card-body');
        }
    });
}

// Prepare transaction list for printing
function prepareTransactionListPrint() {
    const now = new Date();
    $('#print-date').text(now.toLocaleDateString() + ' ' + now.toLocaleTimeString());
    
    const $printBody = $('#transactionsPrintTableBody');
    $printBody.empty();
    
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

// Apply sorting and filtering
function applySortingAndFiltering() {
    let filteredTransactions = [...allTransactions];
    
    const filterValue = $('#filterBy').val();
    if (filterValue !== 'all') {
        filteredTransactions = filteredTransactions.filter(transaction => 
            transaction.payment_status === filterValue
        );
    }
    
    const dateSortValue = $('#dateSort').val();
    const amountSortValue = $('#amountSort').val();
    
    if (dateSortValue === 'latest') {
        filteredTransactions.sort((a, b) => new Date(b.payment_date) - new Date(a.payment_date));
    } else if (dateSortValue === 'oldest') {
        filteredTransactions.sort((a, b) => new Date(a.payment_date) - new Date(b.payment_date));
    }
    
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
        $tableBody.html('<tr><td colspan="8" class="text-center">No transactions found</td></tr>');
        return;
    }
    
    const html = transactions.map(transaction => {
        const statusClass = transaction.payment_status === 'Paid' ? 'status-paid' : 'status-pending';
        const paymentDate = transaction.payment_date || '-';
        
        return `
            <tr>
                <td>${transaction.id}</td>
                <td>${transaction.customer_name}</td>
                <td>${transaction.appliance_name}</td>
                <td>₱${parseFloat(transaction.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td><span class="status-badge ${statusClass}">${transaction.payment_status}</span></td>
                <td>${paymentDate}</td>
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
}

// Update pagination info
function updateTxPaginationInfo(totalItems, start, end) {
    const $info = $('#txPaginationInfo');
    if (totalItems === 0) {
        $info.text('Showing 0 to 0 of 0 entries');
        return;
    }
    $info.text(`Showing ${start} to ${Math.min(end, totalItems)} of ${totalItems} entries`);
}

// Render pagination
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

// Load transaction data
function loadTransactionData(transactionId) {
    showLoading(true, '#transactionFormModal .modal-body');

    $.ajax({
    url: '../backend/api/transaction_api.php?action=getById&id=' + transactionId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                let transactionData;
                if (Array.isArray(response.data)) {
                    transactionData = response.data[0];
                } else {
                    transactionData = response.data;
                }
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

// Populate transaction form
function populateTransactionForm(data) {
    console.log('Populating transaction form with: ', data);

    const $modal = $('#transactionFormModal');

    const totalAmount = parseFloat(data.total_amount || 0);

    // Basic fields (scoped to modal)
    $modal.find('input[name="customer"]').val(data.customer_name || '');
    $modal.find('input[name="appliance"]').val(data.appliance_name || '');
    $modal.find('input[name="date_in"]').val(data.date_in || '');
    $modal.find('#status-field').val(data.status || '');
    $modal.find('input[name="dealer"]').val(data.dealer || '');
    $modal.find('input[name="dop"]').val(data.dop || '');
    $modal.find('input[name="date_pulled_out"]').val(data.date_pulled_out || '');
    $modal.find('#findings-field').val(data.findings || '');
    $modal.find('input[name="remarks"]').val(data.remarks || '');

    // Location checkboxes
    const location = Array.isArray(data.location) ? data.location : JSON.parse(data.location || '[]');
    $modal.find('#shop-field').prop('checked', location.includes('shop'));
    $modal.find('#field-field').prop('checked', location.includes('field'));
    $modal.find('#out_wty-field').prop('checked', location.includes('out_wty'));

    // Service type checkboxes
    let serviceTypes = [];
    if (data.service_types) {
        serviceTypes = Array.isArray(data.service_types) ? data.service_types : JSON.parse(data.service_types || '[]');
    } else if (data.service_type) {
        serviceTypes = Array.isArray(data.service_type) ? data.service_type : JSON.parse(data.service_type || '[]');
    }
    $modal.find('#installation-field').prop('checked', serviceTypes.includes('installation'));
    $modal.find('#repair-field').prop('checked', serviceTypes.includes('repair'));
    $modal.find('#cleaning-field').prop('checked', serviceTypes.includes('cleaning'));
    $modal.find('#checkup-field').prop('checked', serviceTypes.includes('checkup'));

    // Dates and other fields
    $modal.find('input[name="date_repaired"]').val(data.date_repaired || '');
    $modal.find('input[name="date_delivered"]').val(data.date_delivered || '');
    $modal.find('input[name="complaint"]').val(data.complaint || '');

    // Staff fields
    $modal.find('input[name="receptionist"]').val(data.receptionist || '');
    $modal.find('input[name="manager"]').val(data.manager || '');
    $modal.find('input[name="technician"]').val(data.technician || '');
    $modal.find('input[name="released_by"]').val(data.released_by || '');

    $modal.find('#update_report_id').val(data.report_id);

    // DIRECTLY USE THE VALUES FROM DATABASE - NO RECALCULATION NEEDED
    // These values were already calculated when the service report was created
    $modal.find('#labor-amount').val(parseFloat(data.labor || 0).toFixed(2));
    $modal.find('#pullout-delivery').val(parseFloat(data.pullout_delivery || 0).toFixed(2));
    $modal.find('#total-serviceCharge').val(parseFloat(data.service_charge || 0).toFixed(2));
    $modal.find('#parts-charge').val(parseFloat(data.parts_total_charge || 0).toFixed(2));

    // Display the exact total amount from the database
    $modal.find('input[name="total_amount"]').val(parseFloat(data.total_amount || 0).toFixed(2));

    // Display the service charge in the Total Service Charge field (same as service report)
    $modal.find('#total-serviceCharge-display').val(parseFloat(data.service_charge || 0).toFixed(2));

    // Populate parts (this should not affect the total amount calculation)
    populatePartsUsed.call(null, data.parts || []);

    console.log('Database values used (no recalculation):', {
        labor: data.labor,
        pullout_delivery: data.pullout_delivery,
        service_charge: data.service_charge,
        parts_total_charge: data.parts_total_charge,
        total_amount: data.total_amount
    });

    disableFormEditing();
}

// Populate parts used
function populatePartsUsed(parts) {
    console.log('Populating parts with data:', parts);

    const $modal = $('#transactionFormModal');
    const $container = $modal.find('#parts-container');

    // Clear all existing parts rows except the first one inside the modal
    $container.find('.parts-row:not(:first)').remove();

    // Clear the first row
    const $firstRow = $container.find('.parts-row:first');
    $firstRow.find('input[name="part_name[]"]').val('');
    $firstRow.find('input[name="quantity[]"]').val('');
    $firstRow.find('input[name="part_amount[]"]').val('');

    if (parts && parts.length > 0) {
        console.log('Parts data received:', parts);

        parts.forEach((part, index) => {
            let $row;

            if (index === 0) {
                $row = $firstRow;
            } else {
                // Create new row for additional parts, cloned from first row
                $row = $firstRow.clone(true, true);
                $container.append($row);
            }

            // Populate part data (scope to row)
            $row.find('input[name="part_name[]"]').val(part.part_name || '');
            $row.find('input[name="quantity[]"]').val(part.quantity || '');

            // Calculate the total amount for this part (quantity * unit_price)
            const quantity = parseFloat(part.quantity || 0);
            const unitPrice = parseFloat(part.unit_price || part.unit_price || 0);
            const totalAmount = (quantity * unitPrice).toFixed(2);

            $row.find('input[name="part_amount[]"]').val(totalAmount);

            // Ensure cloned inputs remain readonly in the modal
            $row.find('input').prop('readonly', true);

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

// Add part row
function addPartRow() {
    const newRow = $('.parts-row').first().clone(true, true);
    newRow.find('input').val('');
    $('#parts-container').append(newRow);
    return newRow;
}

// Show alert
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

// Show loading
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

// Enable form editing
function enableFormEditing() {
    if (isEditing) return;

    isEditing = true;
    originalFormData = gatherFormData();

    $('#transactionForm input:not([type="hidden"])').prop('readonly', false);
    $('#transactionForm select').prop('disabled', false);
    $('#transactionForm textarea').prop('readonly', false);
    $('#shop-field, #field-field, #out_wty-field').prop('disabled', false);
    $('#installation-field, #repair-field, #cleaning-field, #checkup-field').prop('disabled', false);

    $('.edit-btn').hide();
    $('.finalize-edit-btn').show();

    $('#transactionForm input, #transactionForm select, #transactionForm textarea')
        .not('[type="hidden"]')
        .css('background-color', '#fff');

    showAlert('info', 'You can now edit the service report. Click "Finalize Edit" to save changes.');
}

// Disable form editing
function disableFormEditing() {
    isEditing = false;

    $('#labor-amount, #pullout-delivery').off('input');
    $(document).off('input', 'input[name="part_amount[]"]');
    $(document).off('input', 'input[name="quantity[]"], input[name="part_amount[]"]');

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

// Finalize edit
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

// Reload service report data
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
        },
        complete: function() {
            showLoading(false, '#transactionFormModal .modal-body');
        }
    });
}

// Gather form data
function gatherFormData() {
    const formatDateForPHP = (dateStr) => {
        if (!dateStr) return null;
        return new Date(dateStr).toISOString().split('T')[0];
    };

    const $modal = $('#transactionFormModal');

    const formData = {
        customer_name: $modal.find('#customer-field').val(),
        appliance_name: $modal.find('#appliance-field').val(),
        date_in: formatDateForPHP($modal.find('#date-in-field').val()),
        status: $modal.find('#status-field').val(),
        dealer: $modal.find('input[name="dealer"]').val(),
        dop: formatDateForPHP($modal.find('input[name="dop"]').val()),
        date_pulled_out: formatDateForPHP($modal.find('input[name="date_pulled_out"]').val()),
        findings: $modal.find('#findings-field').val(),
        remarks: $modal.find('input[name="remarks"]').val(),
        location: [],

        service_types: [],
        date_repaired: formatDateForPHP($modal.find('input[name="date_repaired"]').val()),
        date_delivered: formatDateForPHP($modal.find('input[name="date_delivered"]').val()),
        complaint: $modal.find('input[name="complaint"]').val(),
        labor: parseFloat($modal.find('#labor-amount').val()) || 0,
        pullout_delivery: parseFloat($modal.find('#pullout-delivery').val()) || 0,
        service_charge: parseFloat($modal.find('#total-serviceCharge').val()) || 0,
        parts_total_charge: parseFloat($modal.find('#parts-charge').val()) || 0,
        total_amount: parseFloat($modal.find('input[name="total_amount"]').val()) || 0,
        receptionist: $modal.find('input[name="receptionist"]').val(),
        manager: $modal.find('input[name="manager"]').val(),
        technician: $modal.find('input[name="technician"]').val(),
        released_by: $modal.find('input[name="released_by"]').val(),

        parts: []
    };


    // Capture location checkboxes (scoped)
    if ($modal.find('#shop-field').is(':checked')) formData.location.push('shop');
    if ($modal.find('#field-field').is(':checked')) formData.location.push('field');
    if ($modal.find('#out_wty-field').is(':checked')) formData.location.push('out_wty');

    // Capture service type checkboxes (scoped)
    if ($modal.find('#installation-field').is(':checked')) formData.service_types.push('installation');
    if ($modal.find('#repair-field').is(':checked')) formData.service_types.push('repair');
    if ($modal.find('#cleaning-field').is(':checked')) formData.service_types.push('cleaning');
    if ($modal.find('#checkup-field').is(':checked')) formData.service_types.push('checkup');

    // Capture parts data (scoped to modal container)
    $modal.find('.parts-row').each(function() {
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

// Load service prices
async function loadServicePrices() {
    try {
        const response = await $.ajax({
            url: '../backend/api/service_price_api.php?action=getAll',
            type: 'GET',
            dataType: 'json'
        });

        if (response && response.success) {
            // update both local and global references so other scripts can use the same data
            servicePrices = response.data;
            window.servicePrices = response.data;
            console.log('Loaded service prices: ', servicePrices);
        } else {
            servicePrices = {
                installation: 500,
                repair: 300,
                cleaning: 200,
                checkup: 150
            };
            window.servicePrices = servicePrices;
            console.warn('Using fallback service prices');
        }

    } catch (error) {
        console.error('Failed to load service prices: ', error);
        servicePrices = {
            installation: 500,
            repair: 300,
            cleaning: 200,
            checkup: 150
        };
        window.servicePrices = servicePrices;
        console.warn('Using fallback service prices');
    }
}

// Calculate transaction total
function calculateTransactionTotal() {
    const $modal = $('#transactionFormModal');
    const laborCharge = parseFloat($modal.find('#labor-amount').val()) || 0;
    const deliveryCharge = parseFloat($modal.find('#pullout-delivery').val()) || 0;
    const partsTotal = parseFloat($modal.find('#parts-charge').val()) || 0;
    const serviceCharge = calculateServiceCharge();
    const totalServiceCharge = serviceCharge.toFixed(2);
    const grandTotal = (
        parseFloat(laborCharge.toFixed(2)) +
        parseFloat(deliveryCharge.toFixed(2)) +
        parseFloat(serviceCharge.toFixed(2)) +
        parseFloat(partsTotal.toFixed(2))
    ).toFixed(2);

    $modal.find('#total-serviceCharge-display').val(totalServiceCharge);
    $modal.find('input[name="total_amount"]').val(grandTotal);
    $modal.find('#total-serviceCharge').val(grandTotal);
}

// Calculate service charge
function calculateServiceCharge() {
    let total = 0;
    const $modal = $('#transactionFormModal');

    const prices = window.servicePrices || {};
    if ($modal.find('#installation-field').is(':checked')) total += parseFloat(prices.installation || 0);
    if ($modal.find('#repair-field').is(':checked')) total += parseFloat(prices.repair || 0);
    if ($modal.find('#cleaning-field').is(':checked')) total += parseFloat(prices.cleaning || 0);
    if ($modal.find('#checkup-field').is(':checked')) total += parseFloat(prices.checkup || 0);

    return parseFloat(total.toFixed(2));
}

// Validate edit form
function validateEditForm(data) {
    if (!data.customer_name) {
        showAlert('danger', 'Customer name is required');
    }
    if (!data.appliance_name) {
        showAlert('danger', 'Appliance is required');
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