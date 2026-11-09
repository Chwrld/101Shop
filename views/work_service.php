<?php
require_once __DIR__ . '/../bootstrap.php';
session_start();
require __DIR__ . '/../backend/handlers/authHandler.php';
$auth = new AuthHandler();
$userSession = $auth->requireAuth('admin');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charser="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Work Servivce</title>
    <link rel="shortcut Icon" href="../img/Repair.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
</head>
<style>
    .actions-col {
        min-width: 180px;
        /* Adjust as needed */
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

    /* Pagination footer styling copied from archive_history.php */
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
</style>

<body>
    <div class="wrapper">
        <div class="body-overlay"></div>
        <!-- Sidebar -->
        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <div id="content">
            <!-- Top Navbar -->
            <?php
            $pageTitle = 'Work Service';
            $breadcrumb = 'Work Service';
            include __DIR__ . '/../layout/navbar.php';
            ?>
            <!-- Main Content -->
            <div class="main-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Service Price</h5>
                                    <button type="button" id="addWorkServiceBtn" class="btn btn-success d-flex align-items-center" data-toggle="modal" data-target="#addWorkServiceModal">
                                        <i class="material-icons mr-2">&#xE147;</i> Add New Item
                                    </button>
                                </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Service Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="workServiceTableBody">
                                            <!--Data will be displayed here from the database-->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                    <div class="text-muted">
                                        <span id="wsPaginationInfo">Showing 0 to 0 of 0 entries</span>
                                    </div>
                                    <nav aria-label="Service price navigation">
                                        <ul class="pagination pagination-sm mb-0" id="wsPagination"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Work Service Modal -->
            <div class="modal fade" id="addWorkServiceModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" id="addWorkServiceForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Add Service Work</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Service Name</label>
                                    <input type="text" name="service_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Service Price</label>
                                    <input type="number" name="service_price" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Work Service Modal -->
            <div class="modal fade" id="editWorkServiceModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" id="editWorkServiceForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Service Price</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="service_id" id="edit_service_id">
                                <div class="form-group">
                                    <label>Service Name</label>
                                    <input type="text" name="service_name" id="edit_service_name" class="form-control" required readonly>
                                </div>
                                <div class="form-group">
                                    <label>Service Price</label>
                                    <input type="number" name="service_price" id="edit_service_price" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Work Service Modal -->
            <div class="modal fade" id="deleteWorkServiceModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" id="deleteWorkServiceForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Delete Service Price</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="service_id" id="delete_service_id">
                                <p>Are you sure you want to archive this service price?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">archive</button>
                            </div>
                        </form>
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
            // Sidebar collapse/expand logic (copied from parts.php)
            $(".xp-menubar").on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });
            $(".xp-menubar,.body-overlay").on('click', function() {
                $('#sidebar,.body-overlay').toggleClass('show-nav');
            });

            // Show modal on button click (redundant, but ensures click works)
            $('#addWorkServiceBtn').on('click', function() {
                $('#addWorkServiceModal').modal('show');
            });

            // Edit work service sample
            $('.edit-workservice').on('click', function() {
                var id = $(this).data('id');
                $('#edit_workservice_id').val(id);
                $('#edit_service_work').val('');
                $('#edit_labor_price').val('');
            });

            // Delete work service sample
            $('.delete-workservice').on('click', function() {
                var id = $(this).data('id');
                $('#delete_workservice_id').val(id);
            });
        });
    </script>

    <script type="text/javascript">
        const API_BASE_URL = '../backend/api/service_price_api.php';
        let wsCurrentPage = 1;
        let wsPageSize = 10;
        let wsSearchTerm = '';

        function showAlert(type, message) {
            $('.alert-notification').remove();

            const alertHtml = `
                <div class="alert-notification alert alert-${type} alert-dismissible fade show">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            `;

            $('body').prepend(alertHtml);;

            setTimeout(() => {
                $('.alert-notification').alert('close');
            }, 5000);
        }

        $(document).ready(function() {
            loadServicePrices(1);

            // Initialize search functionality
            $('#searchInput').on('keyup', function() {
                wsSearchTerm = ($(this).val() || '').toLowerCase().trim();
                wsCurrentPage = 1;
                loadServicePrices(1);
            });

            $(".xp-menubar").on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            // Server-side searching; no DOM-only filter

            $(".xp-menubar, .body-overlay").on("click", function() {
                $('#sidebar, .body-overlay').toggleClass('show-nav');
            });

            // load service prices (server-paginated)
            function loadServicePrices(page = 1) {
                const params = new URLSearchParams({
                    action: 'getAllPaginated',
                    page: String(page),
                    itemsPerPage: String(wsPageSize),
                    search: wsSearchTerm || ''
                });
                $.ajax({
                    url: API_BASE_URL + '?' + params.toString(),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success && response.data) {
                            const payload = response.data; // {services, currentPage, totalPages, totalItems, itemsPerPage}
                            wsCurrentPage = Number(payload.currentPage) || 1;
                            wsPageSize = Number(payload.itemsPerPage) || wsPageSize;
                            renderServicePrices(payload.services || []);
                            updateWsPaginationInfo(payload.totalItems || 0, (wsCurrentPage - 1) * wsPageSize + 1, (wsCurrentPage - 1) * wsPageSize + (payload.services ? payload.services.length : 0));
                            renderWsPagination(payload.totalPages || 1);
                        } else {
                            $('#workServiceTableBody').html(
                                '<tr><td colspan="3" class="text-center">Error loading service prices</td></tr>'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: ", status, error);
                        $('#workServiceTableBody').html(
                            '<tr><td colspan="3" class="text-center">Failed to load data</td></tr>'
                        );
                    }
                });
            }

            function renderServicePrices(services) {
                const $tableBody = $('#workServiceTableBody');
                $tableBody.empty();

                if (!Array.isArray(services) || services.length === 0) {
                    $tableBody.html('<tr><td colspan="3" class="text-center">No service prices found</td></tr>');
                    return;
                }

                const html = services.map(service => {
                    return `
                    <tr>
                        <td>${service.service_id}</td>
                        <td>${service.service_name.charAt(0).toUpperCase() + service.service_name.slice(1)}</td>
                        
                        <td class="actions-col">
                            <span class='d-inline-flex'>
                                <a href='#' class='edit-work-service mr-2'
                                    data-id='${service.service_id}'
                                    data-name='${service.service_name}'
                                    data-price='${service.service_price}'
                                    data-toggle='modal' data-target='#editWorkServiceModal'>
                                <i class="material-icons" title="Edit">&#xE254;</i>
                                </a>
                                <a href='#' class='delete-work-service mr-2'
                                    data-id='${service.service_id}'
                                    data-toggle='modal' data-target='#deleteWorkServiceModal'>
                                
                                <i class="material-icons" title="Archive">archive</i>
                                </a>
                            </span>
                        </td>
                    </tr>
                    `;
                }).join('');

                $tableBody.html(html);
                bindServiceEvents();
            }

            function updateWsPaginationInfo(totalItems, start, end) {
                const $info = $('#wsPaginationInfo');
                if (totalItems === 0) {
                    $info.text('Showing 0 to 0 of 0 entries');
                    return;
                }
                $info.text(`Showing ${start} to ${Math.min(end, totalItems)} of ${totalItems} entries`);
            }

            function renderWsPagination(totalPages) {
                const $pagination = $('#wsPagination');
                $pagination.empty();

                const prevDisabled = wsCurrentPage === 1 ? 'disabled' : '';
                $pagination.append(`<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${wsCurrentPage - 1}">Previous</a></li>`);

                const maxVisible = 5;
                let start = Math.max(1, wsCurrentPage - Math.floor(maxVisible / 2));
                let end = Math.min(totalPages, start + maxVisible - 1);
                if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);

                if (start > 1) {
                    $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
                    if (start > 2) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }

                for (let i = start; i <= end; i++) {
                    const active = i === wsCurrentPage ? 'active' : '';
                    $pagination.append(`<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
                }

                if (end < totalPages) {
                    if (end < totalPages - 1) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
                }

                if (totalPages < 1) totalPages = 1;
                const nextDisabled = wsCurrentPage === totalPages ? 'disabled' : '';
                $pagination.append(`<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${wsCurrentPage + 1}">Next</a></li>`);

                $pagination.find('.page-link').on('click', function(e) {
                    e.preventDefault();
                    const page = parseInt($(this).data('page'));
                    if (!isNaN(page) && page !== wsCurrentPage) {
                        wsCurrentPage = page;
                        loadServicePrices(wsCurrentPage);
                    }
                });
            }

            function bindServiceEvents() {
                // edit service 
                $('.edit-work-service').click(function() {
                    const serviceId = $(this).data('id');
                    const serviceName = $(this).data('name');
                    const servicePrice = $(this).data('price');

                    $('#edit_service_id').val(serviceId);
                    $('#edit_service_name').val(serviceName);
                    $('#edit_service_price').val(servicePrice);
                });

                // delete service 
                $('.delete-work-service').click(function() {
                    $('#delete_service_id').val($(this).data('id'));
                });
            }

            // add service form submission 
            $('#addWorkServiceForm').submit(function(e) {
                e.preventDefault();

                const formData = {
                    service_name: $('input[name="service_name"]').val(),
                    service_price: parseFloat($('input[name="service_price"]').val())
                };

                $.ajax({
                    url: API_BASE_URL + '?action=addService',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#addWorkServiceModal').modal('hide');
                            $('#addWorkServiceForm')[0].reset();
                            loadServicePrices();
                            showAlert('success', 'Service price added successfully');
                        } else {
                            showAlert('danger', response.message || 'Failed to add service price');
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Error: ' + xhr.responseText);
                    }
                });
            });

            // edit service form submission 
            $('#editWorkServiceForm').submit(function(e) {
                e.preventDefault();

                const formData = {
                    service_id: $('#edit_service_id').val(),
                    service_price: parseFloat($('#edit_service_price').val())
                };

                $.ajax({
                    url: API_BASE_URL + '?action=updateService',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#editWorkServiceModal').modal('hide');
                            loadServicePrices();
                            showAlert('success', 'Service price updated successfully');
                        } else {
                            showAlert('danger', response.message || 'Failed to update service price');
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Error: ' + xhr.responseText);
                    }
                });
            });

            // delete service form submission 
            $('#deleteWorkServiceForm').submit(function(e) {
                e.preventDefault();
                const serviceId = $('#delete_service_id').val();

                $.ajax({
                    url: API_BASE_URL + '?action=deleteService&id=' + serviceId,
                    type: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#deleteWorkServiceModal').modal('hide');
                            loadServicePrices();
                            showAlert('success', 'Service price archived successfully');
                        } else {
                            showAlert('danger', response.message || 'Failed to delete service price');
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Error: ' + xhr.responseText);
                    }
                });
            });
        });
    </script>
</body>

</html>