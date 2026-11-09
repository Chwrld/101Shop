<?php
require_once __DIR__ . '/../bootstrap.php';
session_start();
require __DIR__ . '/../backend/handlers/authHandler.php';
$auth = new AuthHandler();
$userSession = $auth->requireAuth('staff');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parts Management</title>
    <link rel="shortcut Icon" href="../img/Repair.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
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

        .table td {
            font-size: 14px; /* Or whatever size matches your other columns */
            font-family: inherit; /* Ensure it uses the same font family */
            font-weight: normal; /* Ensure consistent weight */
        }
        
        /* Pagination footer styling copied from archive_history.php  */
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
</head>
<body>
    <div class="wrapper">
        <div class="body-overlay"></div>
        <!-- include Sidebar -->
        <?php include 'staff_sidebar.php'; ?>

        <div id="content">
            <!-- staffnavbar -->
              <?php
              $pageTitle = "Parts Management";
              include 'staffnavbar.php'; 
              ?>

            <!-- Main Content -->
             <div class="main-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Manage Parts</h5>
                            </div>
                            <div class="card-body">
                                <!-- Rest of your content remains the same -->
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Part Name</th>
                                                            <th>Description</th>
                                                            <th>Price</th>
                                                            <th>Stock</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="partsTableBody">
                                                        <!-- Add more static rows as needed -->
                                                    </tbody>
                                             </table>
                                            </div>
                                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                    <div class="text-muted">
                                        <span id="partsPaginationInfo">Showing 0 to 0 of 0 entries</span>
                                    </div>
                                    <nav aria-label="Parts navigation">
                                        <ul class="pagination pagination-sm mb-0" id="partsPagination"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Part Modal -->
            <div class="modal fade" id="addPartModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" id="addPartsForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Add New Part</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Part Name</label>
                                    <input type="text" name="parts_no" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" name="price" class="form-control" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" name="quantity_stock" class="form-control" required>
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

            <!-- Edit Part Modal -->
            <div class="modal fade" id="editPartModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" id="editPartsForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Part</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="part_id" id="edit_part_id">
                                <div class="form-group">
                                    <label>Part Name</label>
                                    <input type="text" name="parts_no" id="edit_part_no" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" id="edit_description" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" name="price" id="edit_price" class="form-control" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" name="quantity_stock" id="edit_qty" class="form-control" required>
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

            <!-- Delete Part Modal -->
            <div class="modal fade" id="deletePartModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" id="deletePartsForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Archive Part</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="part_id" id="delete_part_id">
                                <p>Are you sure you want to archive this part?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Archive</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
   
    <script src="../js/jquery-3.3.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script type="text/javascript">
        const API_BASE_URL = '../backend/api/parts_api.php';
        let partsCurrentPage = 1;
        let partsPageSize = 10;
        let partsSearchTerm = '';

        function showAlert(type, message) {
            $('.alert-notification').remove();

            const alertHtml = `
                <div class="alert-notification alert alert-${type} alert-dismissible fade show">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            `;

            $('body').prepend(alertHtml);

            setTimeout(() => {
                $('.alert-notification').alert('close');
            }, 5000);
        }

        $(document).ready(function() {
            loadParts(1);

            $('#searchInput').on('keyup', function() {
                partsSearchTerm = ($(this).val() || '').toLowerCase().trim();
                partsCurrentPage = 1;
                loadParts(1);
            });

            $(".xp-menubar").on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            
            $(".xp-menubar,.body-overlay").on('click', function() {
                $('#sidebar,.body-overlay').toggleClass('show-nav');
            });

            function loadParts(page = 1) {
                console.log("Attempting to load parts from:", API_BASE_URL);

                const params = new URLSearchParams({
                    action: 'getAllParts',
                    page: String(page),
                    itemsPerPage: String(partsPageSize),
                    search: partsSearchTerm || ''
                });

                $.ajax({
                    url: API_BASE_URL + '?' + params.toString(),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Full API Response:", response);
                        if (response && response.success && response.data) {
                            const payload = response.data; // {parts, currentPage, totalPages, totalItems, itemsPerPage}
                            partsCurrentPage = Number(payload.currentPage) || 1;
                            partsPageSize = Number(payload.itemsPerPage) || partsPageSize;
                            const parts = Array.isArray(payload.parts) ? payload.parts : [];
                            renderParts(parts);
                            updatePartsPaginationInfo(payload.totalItems || 0, (partsCurrentPage - 1) * partsPageSize + 1, (partsCurrentPage - 1) * partsPageSize + parts.length);
                            renderPartsPagination(payload.totalPages || 1);
                        } else {
                            const errorMsg = response?.message || 'Unknown error';
                            console.error("API Error:", errorMsg);
                            $('#partsTableBody').html('<tr><td colspan="5">Error: ' + errorMsg + '</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        $('#partsTableBody').html(
                            '<tr><td colspan="5">Failed to load data. Check console.</td></tr>'
                        );
                    }
                });
            }

            function renderParts(parts) {
                console.log("Rendering parts:", parts);

                const $tableBody = $('#partsTableBody');
                $tableBody.empty();

                if (!Array.isArray(parts)) {
                    console.error("Invalid parts data - expected array:", parts);
                    $tableBody.html('<tr><td colspan="5">Error: Invalid data format</td></tr>');
                    return;
                }

                if (parts.length === 0) {
                    $tableBody.html('<tr><td colspan="6" class="text-center">No parts found</td></tr>');
                    return;
                }

                try {
                    const html = parts.map(part => {
                        return `<tr>
                                    <td>${part.part_id || ''}</td>
                                    <td>${part.part_no || ''}</td>
                                    <td>${part.description || ''}</td>
                                    <td>${part.price || ''}</td>
                                    <td>${part.quantity_stock || ''}</td>
                                </tr>`;
                    }).join('');

                    $('#partsTableBody').html(html);
                    bindPartsEvents();
                } catch (error) {
                    console.error("Error rendering parts:", error);
                    $tableBody.html('<tr><td colspan="5">Error displaying part data</td></tr>');
                }
            }

            function updatePartsPaginationInfo(totalItems, start, end) {
                const $info = $('#partsPaginationInfo');
                if (totalItems === 0) {
                    $info.text('Showing 0 to 0 of 0 entries');
                    return;
                }
                $info.text(`Showing ${start} to ${Math.min(end, totalItems)} of ${totalItems} entries`);
            }

            function renderPartsPagination(totalPages) {
                const $pagination = $('#partsPagination');
                $pagination.empty();

                const prevDisabled = partsCurrentPage === 1 ? 'disabled' : '';
                $pagination.append(`<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${partsCurrentPage - 1}">Previous</a></li>`);

                const maxVisible = 5;
                let start = Math.max(1, partsCurrentPage - Math.floor(maxVisible / 2));
                let end = Math.min(totalPages, start + maxVisible - 1);
                if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);

                if (start > 1) {
                    $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
                    if (start > 2) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }

                for (let i = start; i <= end; i++) {
                    const active = i === partsCurrentPage ? 'active' : '';
                    $pagination.append(`<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
                }

                if (end < totalPages) {
                    if (end < totalPages - 1) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
                }

                if (totalPages < 1) totalPages = 1;
                const nextDisabled = partsCurrentPage === totalPages ? 'disabled' : '';
                $pagination.append(`<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${partsCurrentPage + 1}">Next</a></li>`);

                $pagination.find('.page-link').on('click', function(e) {
                    e.preventDefault();
                    const page = parseInt($(this).data('page'));
                    if (!isNaN(page) && page !== partsCurrentPage) {
                        partsCurrentPage = page;
                        loadParts(partsCurrentPage);
                    }
                });
            }

            function bindPartsEvents() {
                //edit parts
                $('.edit-part').click(function() {
                    const partId = $(this).data('id');

                    $.ajax({
                        url: API_BASE_URL + '?action=getPartsById&id=' + partId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.success) {
                                $('#edit_part_id').val(response.data.part_id);
                                $('#edit_part_no').val(response.data.part_no);
                                $('#edit_description').val(response.data.description);
                                $('#edit_price').val(response.data.price);
                                $('#edit_qty').val(response.data.quantity_stock);
                            }
                        }
                    });
                });

                //delete part
                $('.delete-part').click(function() {
                    $('#delete_part_id').val($(this).data('id'));
                    $('#deletePartModal').modal('show');
                });
            }

            $.fn.serializeObject = function() {
                const arr = $(this).serializeArray();
                const obj = {};
                for (let item of arr) {
                    obj[item.name] = item.value;
                }
                return obj;
            }

            //add parts submit get and data
            $('#addPartsForm').submit(function(e) {
                e.preventDefault();

                const partsData = {
                    parts_no: $('input[name="parts_no"]').val(),
                    description: $('textarea[name="description"]').val(),
                    price: $('input[name="price"]').val(),
                    quantity_stock: $('input[name="quantity_stock"]').val()
                };

                $.ajax({
                    url: API_BASE_URL + '?action=addPart',
                    type: 'POST',
                    data: JSON.stringify(partsData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#addPartModal').modal('hide');
                            $('#addPartsForm')[0].reset();
                            loadParts();
                            showAlert('success', 'Parts added successfully');
                        } else {
                            alert('Error adding parts: ' + (response?.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            });

            $('#editPartsForm').submit(function(e) {
                e.preventDefault();

                const formData = $(this).serializeArray().reduce((obj, item) => {
                    obj[item.name] = item.value;
                    return obj;
                }, {});

                $.ajax({
                    url: API_BASE_URL + '?action=updatePart',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#editPartModal').modal('hide');
                            $('#editPartsForm')[0].reset();
                            loadParts();
                            showAlert('success', 'Part updated successfully');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            });

            $('#deletePartsForm').submit(function(e) {
                e.preventDefault();
                const partId = $('#delete_part_id').val();

                $.ajax({
                    url: API_BASE_URL + '?action=deletePart&id=' + partId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#deletePartModal').modal('hide');
                            $('#deletePartsForm')[0].reset();
                            loadParts();
                            showAlert('success', 'Part deleted successfully');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            });

        });
    </script>
</body>
</html>