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
    <title>Customer</title>
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
</head>
<body>
    <div class="wrapper">
        <div class="body-overlay"></div>

        <!-- Include Sidebar -->
        <?php include 'staff_sidebar.php'; ?>
        
        <div id="content">
          <!-- Navbar -->
            <?php
              $pageTitle = "Customer information";
              $pageCrumb = "Customer";
              include 'staffnavbar.php'; 
              ?>
   <!-- Main Content -->
        <div class="main-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Manage Customers</h5>
                            <button type="button" class="btn btn-success d-flex align-items-center" data-toggle="modal" data-target="#addCustomerModal">
                                <i class="material-icons mr-2">&#xE147;</i> Add New Customer
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Rest of your content remains the same -->
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Full Name</th>
                                            <th>Address</th>
                                            <th>Phone Number</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customersTableBody">
                                        <!--data will be populate by javascript-->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                <div class="text-muted">
                                    <span id="custPaginationInfo">Showing 0 to 0 of 0 entries</span>
                                </div>
                                <nav aria-label="Customers navigation">
                                    <ul class="pagination pagination-sm mb-0" id="custPagination"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                           <!-- Add Customer Modal -->
                            <div class="modal fade" id="addCustomerModal">
                                <div class="modal-dialog" style="max-width: 1200px; width: 90%;">
                                    <div class="modal-content">
                                        <form method="post" id="addCustomerForm">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Add Customer</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <h6>Customers Info</h6>
                                                        <div class="form-group">
                                                            <label>First Name</label>
                                                            <input type="text" name="first_name" class="form-control" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Last Name</label>
                                                            <input type="text" name="last_name" class="form-control" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Address</label>
                                                            <textarea name="address" class="form-control" required></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Tell No.</label>
                                                            <input type="tel" name="phone_no" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6>Appliance Info</h6>
                                                        <div class="form-group">
                                                            <label>Brand</label>
                                                            <input type="text" name="brand" class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Product</label>
                                                            <input type="text" name="product" class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Serial Number</label>
                                                            <input type="text" name="serial_no" class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Model Number</label>
                                                            <input type="text" name="model_no" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Category</label>
                                                            <select name="category" class="form-control" required>
                                                                <option value="">Select Category</option>
                                                                <option value="Refrigerator">Refrigerator</option>
                                                                <option value="Washing Machine">Washing Machine</option>
                                                                <option value="Air Conditioner">Air Conditioner</option>
                                                                <option value="Oven">Oven</option>
                                                                <option value="Television">Television</option>
                                                                <option value="Microwave">Microwave</option>
                                                                <option value="Dishwasher">Dishwasher</option>
                                                                <option value="Other">Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Date In</label>
                                                            <input type="date" name="date_in" class="form-control" placeholder="mm / dd / yyyy">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Warranty End</label>
                                                            <input type="date" name="warranty_end" class="form-control" placeholder="mm / dd / yyyy">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-dark" data-dismiss="modal">Cancel</button>
                                                <button type="submit" name="add_customer" class="btn btn-success">Add</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Customer Modal -->
                         <div class="modal fade" id="editCustomerModal">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" id="editCustomerForm">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Edit Customer</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="customer_id" id="edit_customer_id">
                                                <div class="form-group">
                                                    <label>First Name</label>
                                                    <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Last Name</label>
                                                    <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Address</label>
                                                    <textarea name="address" id="edit_address" class="form-control" required></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label>Phone Number</label>
                                                    <input type="tel" name="phone_no" id="edit_phone_no" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" name="edit_customer" class="btn btn-primary">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <!-- Delete Customer Modal -->
                        <div class="modal fade" id="deleteCustomerModal">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post" id="deleteCustomerForm">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Delete Customer</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="customer_id" id="delete_customer_id">
                                            <p>Are you sure you want to archive this customer?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" name="delete_customer" class="btn btn-danger">archive</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

            <!-- View Appliances Modal -->
           <div class="modal fade" id="viewAppliancesModal" tabindex="-1" aria-labelledby="viewAppliancesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl" style="max-width: 1200px; width: 95%;">
                    <div class="modal-content" style="border-radius: 18px;">
                        <div class="modal-header justify-content-center position-relative">
                            <h4 class="modal-title w-100 text-center" id="viewAppliancesModalLabel">Appliances List</h4>
                            <button type="button" class="close position-absolute" style="right: 20px; top: 18px;" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center px-4 pt-2 pb-1">
                            <span id="current_customer_name" class="fw-bold" style="font-size: 1.1rem;"></span>
                            <button type="button" class="btn btn-success d-flex align-items-center" id="addNewApplianceBtn">
                                <i class="material-icons mr-1">add</i> Add Appliance
                            </button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0" style="font-family: 'Poppins', sans-serif;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Brand</th>
                                            <th>Product</th>
                                            <th>Model No</th>
                                            <th>Serial No</th>
                                            <th>Category</th>
                                            <th>Date In</th>
                                            <th>Warranty End</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="appliancesTableBody">
                                        <!-- Appliances will be rendered here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Appliance Modal -->
           <div class="modal fade" id="addApplianceModal">
                <div class="modal-dialog" style="max-width: 900px; width: 90%;">
                    <div class="modal-content">
                        <form method="post" id="addApplianceForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Add Appliance to Customer</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Customer</label>
                                            <input type="hidden" name="customer_id" id="add_appliance_customer_id">
                                            <input type="text" name="customer_name" id="add_appliance_customer_name" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Serial Number</label>
                                            <input type="text" name="serial_no" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Model Number</label>
                                            <input type="text" name="model_no" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Product</label>
                                            <input type="text" name="product" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Brand</label>
                                            <input type="text" name="brand" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Category</label>
                                            <select name="category" class="form-control" required>
                                                <option value="">Select Category</option>
                                                <option value="Refrigerator">Refrigerator</option>
                                                <option value="Washing Machine">Washing Machine</option>
                                                <option value="Air Conditioner">Air Conditioner</option>
                                                <option value="Oven">Oven</option>
                                                <option value="Television">Television</option>
                                                <option value="Microwave">Microwave</option>
                                                <option value="Dishwasher">Dishwasher</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Date In</label>
                                            <input type="date" name="date_in" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Warranty End</label>
                                            <input type="date" name="warranty_end" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" name="add_appliance" class="btn btn-success">Add Appliance</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Appliance Modal -->
            <div class="modal fade" id="editApplianceModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="post" id="editApplianceForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Appliance</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="appliance_id" id="edit_appliance_id">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Brand</label>
                                            <input type="text" name="brand" id="edit_brand" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Model Number</label>
                                            <input type="text" name="model_no" id="edit_model_no" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Serial Number</label>
                                            <input type="text" name="serial_no" id="edit_serial_no" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Date In</label>
                                            <input type="date" name="date_in" id="edit_date_in" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Product</label>
                                            <input type="text" name="product" id="edit_product" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Warranty End</label>
                                            <input type="date" name="warranty_end" id="edit_warranty_end" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label>Category</label>
                                            <select name="category" id="edit_category" class="form-control" required>
                                                <option value="">Select Category</option>
                                                <option value="Refrigerator">Refrigerator</option>
                                                <option value="Washing Machine">Washing Machine</option>
                                                <option value="Air Conditioner">Air Conditioner</option>
                                                <option value="Oven">Oven</option>
                                                <option value="Television">Television</option>
                                                <option value="Microwave">Microwave</option>
                                                <option value="Dishwasher">Dishwasher</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" id="edit_status" class="form-control" required>
                                                <option value="Active">Active</option>
                                                <option value="Inactive">Inactive</option>
                                                <option value="Under Repair">Under Repair</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" name="edit_appliance" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Appliance Modal -->
            <div class="modal fade" id="deleteApplianceModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" id="deleteApplianceForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Delete Appliance</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="appliance_id" id="delete_appliance_id">
                                <p>Are you sure you want to delete this appliance?</p>
                                <p class="text-warning"><small>This action cannot be undone.</small></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" name="delete_appliance" class="btn btn-danger">Delete</button>
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
        let currentCustomerContext = {
            id: null,
            name: null
        };

        //apply sorting and filtering
        //$('#dateSort, #roleSort, #filterBy').change(function() {
        //applySortingAndFiltering();
        //});

        //API Base URL
        const API_BASE_URL = '../backend/api/customer_appliance_api.php';

        //notification/alert helper function
        function showAlert(type, message) {
            // Remove any existing alerts first
            $('.alert-notification').remove();

            // Create alert HTML based on type (success, error, etc.)
            const alertHtml = `
                    <div class="alert-notification alert alert-${type} alert-dismissible fade show">
                        ${message}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                `;

            // Add to page (you can adjust the position as needed)
            $('body').prepend(alertHtml);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $('.alert-notification').alert('close');
            }, 5000);
        }

        $(document).ready(function() {
            let custCurrentPage = 1;
            let custPageSize = 10;
            let custSearchTerm = '';

            // Initialize all modals properly
            $('.modal').each(function() {
                $(this).modal({
                    backdrop: true,
                    keyboard: true,
                    show: false
                });
            });

            // Ensure modals are properly initialized when shown
            $('.modal').on('show.bs.modal', function(e) {
                // Remove any existing backdrops
                $('.modal-backdrop').remove();
                
                // Fix scrollbar shift issue
                const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
                if (scrollbarWidth > 0) {
                    $('body').css('padding-right', scrollbarWidth + 'px');
                }
                
                // Ensure modal is on top
                $(this).css('z-index', 1050);
                $(this).find('.modal-dialog').css('z-index', 1051);
            });

            // Clean up when modal is hidden
            $('.modal').on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                
                // Restore body padding
                $('body').css('padding-right', '');
            });

            // Ensure close buttons work properly
            $('.modal .close, .modal [data-dismiss="modal"]').on('click', function(e) {
                e.preventDefault();
                const modal = $(this).closest('.modal');
                modal.modal('hide');
            });

            // Allow backdrop click to close modals
            $('.modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).modal('hide');
                }
            });

            // Function to ensure modal is properly initialized
            function initializeModal(modalId) {
                const $modal = $('#' + modalId);
                if ($modal.length) {
                    // Remove any existing backdrops
                    $('.modal-backdrop').remove();
                    
                    // Reinitialize modal
                    $modal.modal({
                        backdrop: true,
                        keyboard: true,
                        show: false
                    });
                    
                    // Ensure all form elements are clickable
                    $modal.find('input, select, textarea, button').each(function() {
                        $(this).css({
                            'pointer-events': 'auto',
                            'position': 'relative',
                            'z-index': '1'
                        });
                    });
                }
            }

            loadCustomers(1);

            // Initialize search functionality (server-side)
            $('#searchInput').on('keyup', function() {
                custSearchTerm = ($(this).val() || '').toLowerCase().trim();
                custCurrentPage = 1;
                loadCustomers(1);
            });

            $(" .xp-menubar").on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            $(".xp-menubar,.body-overlay").on('click', function() {
                $('#sidebar,.body-overlay').toggleClass('show-nav');
            });

            //Add Customer along with appliance
            $('#addCustomerForm').submit(function(e) {
                e.preventDefault();

                //extract customer data
                const customerData = {
                    first_name: $('input[name="first_name"]').val(),
                    last_name: $('input[name="last_name"]').val(),
                    address: $('textarea[name="address"]').val(),
                    phone_no: $('input[name="phone_no"]').val()
                };

                //extract appliance data 
                const applianceData = {
                    brand: $('input[name="brand"]').val(),
                    product: $('input[name="product"]').val(),
                    model_no: $('input[name="model_no"]').val(),
                    serial_no: $('input[name="serial_no"]').val(),
                    category: $('select[name="category"]').val(),
                    warranty_end: $('input[name="warranty_end"]').val(),
                    date_in: $('input[name="date_in"]').val(),
                    status: 'Active'
                };


                $.ajax({
                    url: API_BASE_URL + '?action=addCustomer',
                    type: 'POST',
                    data: JSON.stringify(customerData),
                    contentType: 'application/json',
                    success: function(customerResponse) {
                        if (customerResponse && customerResponse.success) {
                            const customerId = customerResponse.data.id;

                            //only add appliance if theres data already
                            if (applianceData.brand && applianceData.product) {

                                //add appliance with new customer 
                                applianceData.customer_id = customerId;

                                $.ajax({
                                    url: API_BASE_URL + '?action=addAppliance',
                                    type: 'POST',
                                    data: JSON.stringify(applianceData),
                                    contentType: 'application/json',
                                    success: function(applianceResponse) {

                                        $('#addCustomerModal').modal('hide');
                                        $('#addCustomerForm')[0].reset();

                                        loadCustomers();
                                        showAlert('success', 'Customer and Appliance added successfully');
                                    },
                                    error: function(xhr) {

                                        //appliance failed but customer is registered
                                        $('#addCustomerModal').modal('hide');
                                        $('#addCustomerForm')[0].reset();
                                        loadCustomers();
                                        alert('Customer is added but appliance is failed' + xhr.responseText);
                                    }
                                });
                            } else {
                                //no appliance data to add
                                $('#addCustomerModal').modal('hide');
                                $('#addCustomerForm')[0].reset();
                                loadCustomers();
                                showAlert('success', 'Customer added successfully');
                            }
                        } else {
                            alert('Error adding customer: ' + (customerResponse?.message || 'Unkown error'));
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });

            });

            // Server-side search; no DOM-only filter

            //Load Customer Function
            function loadCustomers(page = 1) {
                console.log("Attempting to load customers from:", API_BASE_URL);

                $.ajax({
                    url: API_BASE_URL + '?action=getAllCustomers&page=' + page + '&itemsPerPage=' + custPageSize + '&search=' + encodeURIComponent(custSearchTerm),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Full API Response:", response);
                        if (response && response.success && response.data) {
                            const payload = response.data; // {customers, currentPage, totalPages, totalItems, itemsPerPage}
                            const customers = Array.isArray(payload.customers) ? payload.customers : [];
                            custCurrentPage = Number(payload.currentPage) || 1;
                            custPageSize = Number(payload.itemsPerPage) || custPageSize;
                            renderCustomers(customers);
                            const start = (custCurrentPage - 1) * custPageSize + 1;
                            const end = (custCurrentPage - 1) * custPageSize + customers.length;
                            updateCustPaginationInfo(payload.totalItems || 0, start, end);
                            renderCustPagination(payload.totalPages || 1);
                        } else {
                            const errorMsg = response?.message || 'Unknown error';
                            console.error("API Error:", errorMsg);
                            $('#customersTableBody').html('<tr><td colspan="5">Error: ' + errorMsg + '</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        $('#customersTableBody').html(
                            '<tr><td colspan="5">Failed to load data. Check console.</td></tr>'
                        )
                    }
                });
            }

            function updateCustPaginationInfo(totalItems, start, end) {
                const $info = $('#custPaginationInfo');
                if (totalItems === 0) {
                    $info.text('Showing 0 to 0 of 0 entries');
                    return;
                }
                $info.text(`Showing ${start} to ${Math.min(end, totalItems)} of ${totalItems} entries`);
            }

            function renderCustPagination(totalPages) {
                const $pagination = $('#custPagination');
                $pagination.empty();

                const prevDisabled = custCurrentPage === 1 ? 'disabled' : '';
                $pagination.append(`<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${custCurrentPage - 1}">Previous</a></li>`);

                const maxVisible = 5;
                let start = Math.max(1, custCurrentPage - Math.floor(maxVisible / 2));
                let end = Math.min(totalPages, start + maxVisible - 1);
                if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);

                if (start > 1) {
                    $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
                    if (start > 2) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }

                for (let i = start; i <= end; i++) {
                    const active = i === custCurrentPage ? 'active' : '';
                    $pagination.append(`<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
                }

                if (end < totalPages) {
                    if (end < totalPages - 1) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
                }

                if (totalPages < 1) totalPages = 1;
                const nextDisabled = custCurrentPage === totalPages ? 'disabled' : '';
                $pagination.append(`<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${custCurrentPage + 1}">Next</a></li>`);

                $pagination.find('.page-link').on('click', function(e) {
                    e.preventDefault();
                    const page = parseInt($(this).data('page'));
                    if (!isNaN(page) && page !== custCurrentPage) {
                        custCurrentPage = page;
                        loadCustomers(custCurrentPage);
                    }
                });
            }

            //load data when modal opens
            $('#viewAppliancesModal').on('show.bs.modal', function(e) {
                const button = $(e.relatedTarget);
                if (button.length) {
                    currentCustomerContext.id = button.data('id');
                    currentCustomerContext.name = button.closest('tr').find('td:nth-child(2)').text();
                    
                    $('#current_customer_name').text(currentCustomerContext.name + "'s Appliances");
                    loadAppliances(currentCustomerContext.id);
                }
            });

            $('#addNewApplianceBtn').click(function() {
                $('#add_appliance_customer_id').val(currentCustomerContext.id);
                $('#add_appliance_customer_name').val(currentCustomerContext.name);
                
                // Ensure modal is properly shown
                initializeModal('addApplianceModal');
                $('#addApplianceModal').modal('show');
            });

            function loadAppliances(customerId) {
                $('#appliancesTableBody').html('<tr><td colspan="7" class="text-center">Loading appliances...</td></tr>')

                $.ajax({
                    url: API_BASE_URL + '?action=getAppliancesByCustomerId&customerId=' + customerId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            renderAppliances(response.data);
                } else {
                            renderAppliances([]);
                        }
                    },
                    error: function() {
                        renderAppliances([]);
                    }
                });
            }

            //Render Customers to Table
            function renderCustomers(customers) {
                console.log("Rendering customers:", customers);

                const $tableBody = $('#customersTableBody');
                $tableBody.empty();

                if (!Array.isArray(customers)) {
                    console.error("Invalid customers data - expected array:", customers);
                    $tableBody.html('<tr><td colspan="5">Error: Invalid data format</td></tr>');
                    return;
                }

                if (customers.length === 0) {
                    $tableBody.html('<tr><td colspan="5" class="text-center">No customers found</td></tr>');
                    return;
                }

                try {
                    const html = customers.map(customer => {
                        return `<tr>
                                    <td>${customer.customer_id || ''}</td>
                                    <td>${customer.FullName || (customer.first_name + ' ' + customer.last_name) || ''}</td>
                                    <td>${customer.address || ''}</td>
                                    <td>${customer.phone_no || ''}</td>
                                    <td>
                                        <span class='d-inline-flex'>
                                            <a href='#' class='view-appliances mr-2' data-id='${customer.customer_id}' data-toggle='modal' data-target='#viewAppliancesModal' title='ViewAppliances'>
                                                <i class='material-icons'>devices_other</i>
                                            </a>
                                            <a href='#' class='edit-customer mr-2' data-id='${customer.customer_id}' title='Edit'>
                                                <i class='material-icons'>&#xE254;</i>    
                                            </a>
                                            <a href='#' class='archive-customer mr-2' data-id='${customer.customer_id}' data-toggle='modal' data-target='#deleteCustomerModal' title='Delete'>
                                                <i class='material-icons'>archive</i>
                                            </a>
                                        </span>
                                    </td>
                                </tr>`;
                    }).join('');

                    $('#customersTableBody').html(html);
                } catch (error) {
                    console.error("Error rendering customers:", error);
                    $tableBody.html('<tr><td colspan="5">Error displaying customer data</td></tr>');
                }
            }

            function renderAppliances(appliances) {
                const $tbody = $('#appliancesTableBody');

                if (!appliances || appliances.length === 0) {
                    $tbody.html('<tr><td colspan="8" class="text-center py-3">No appliances found</td></tr>');
                    return;
                }

                const rows = appliances.map(appliance => `
                                                                <tr data-appliance-id="${appliance.appliance_id}">
                                                                    <td>${appliance.brand || '-'}</td>
                                                                    <td>${appliance.product || '-'}</td>
                                                                    <td>${appliance.model_no || '-'}</td>
                                                                    <td>${appliance.serial_no || '-'}</td>
                                                                    <td>${appliance.category || '-'}</td>
                                                                    <td>${appliance.date_in || '-'}</td>
                                                                    <td>${appliance.warranty_end || '-'}</td>
                                                                    <td>
                                                                        <button class="btn btn-primary btn-sm edit-appliance-btn d-flex align-items-center justify-content-center" 
                                                                            data-id="${appliance.appliance_id}"
                                                                            data-brand="${appliance.brand}"
                                                                            data-product="${appliance.product}"
                                                                            data-model_no="${appliance.model_no}"
                                                                            data-serial_no="${appliance.serial_no}"
                                                                            data-date_in="${appliance.date_in}"
                                                                            data-warranty_end="${appliance.warranty_end}"
                                                                            data-category="${appliance.category}"
                                                                            data-status="${appliance.status || 'Active'}"
                                                                            title="Edit"
                                                                            style="width:32px; height:32px; padding:0; margin-right:4px;">
                                                                            <span class="material-icons" style="font-size: 1.2em;">edit</span>
                                                                        </button>
                                                                        <button class="btn btn-danger btn-sm delete-appliance-btn d-flex align-items-center justify-content-center" 
                                                                            data-id="${appliance.appliance_id}" title="Delete"
                                                                            style="width:32px; height:32px; padding:0;">
                                                                            <span class="material-icons" style="font-size: 1.2em;">delete</span>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                                `);

                $tbody.html(rows.join(''));
            }

            // Customer actions - event delegation
            $(document).on('click', '.view-appliances', function(e) {
                e.preventDefault();
                const customerId = $(this).data('id');
                currentCustomerContext.id = customerId;
                currentCustomerContext.name = $(this).closest('tr').find('td:nth-child(2)').text();
                
                $('#current_customer_name').text(currentCustomerContext.name + "'s Appliances");
                loadAppliances(customerId);
                $('#viewAppliancesModal').modal('show');
            });

            $(document).on('click', '.edit-customer', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Edit customer clicked'); // Debug log
                const customerId = $(this).data('id');
                console.log('Customer ID:', customerId); // Debug log

                $.ajax({
                    url: API_BASE_URL + '?action=getCustomerById&id=' + customerId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Customer data loaded:', response); // Debug log
                        if (response && response.success) {
                            $('#edit_customer_id').val(response.data.customer_id);
                            $('#edit_first_name').val(response.data.first_name);
                            $('#edit_last_name').val(response.data.last_name);
                            $('#edit_address').val(response.data.address);
                            $('#edit_phone_no').val(response.data.phone_no);
                            
                            // Ensure modal is properly shown
                            initializeModal('editCustomerModal');
                            $('#editCustomerModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading customer:', xhr);
                        alert('Error loading customer data');
                    }
                });
            });

            $(document).on('click', '.archive-customer', function(e) {
                e.preventDefault();
                $('#delete_customer_id').val($(this).data('id'));
                $('#deleteCustomerModal').modal('show');
            });

            // Appliance actions - event delegation  
            $(document).on('click', '.edit-appliance-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);

                $('#edit_appliance_id').val($btn.data('id'));
                $('#edit_brand').val($btn.data('brand'));
                $('#edit_product').val($btn.data('product'));
                $('#edit_model_no').val($btn.data('model_no'));
                $('#edit_serial_no').val($btn.data('serial_no'));
                $('#edit_date_in').val($btn.data('date_in'));
                $('#edit_warranty_end').val($btn.data('warranty_end'));
                $('#edit_category').val($btn.data('category'));
                $('#edit_status').val($btn.data('status'));

                $('#editApplianceModal').modal('show');
            });

            $(document).on('click', '.delete-appliance-btn', function(e) {
                e.preventDefault();
                $('#delete_appliance_id').val($(this).data('id'));
                $('#deleteApplianceModal').modal('show');
            });

            // Add modal cleanup
            $('.modal').on('hidden.bs.modal', function() {
                // Reset forms only if they exist
                const form = $(this).find('form')[0];
                if (form) {
                    form.reset();
                }
                
                // Clear any validation states
                $(this).find('.form-control').removeClass('is-invalid is-valid');
                $(this).find('.invalid-feedback').remove();
            });


            $('form').on('submit', function() {
                const $submitBtn = $(this).find('button[type="submit"]');
                $submitBtn.prop('disabled', true);
                
                // Re-enable after 3 seconds in case of error
                setTimeout(() => {
                    $submitBtn.prop('disabled', false);
                }, 3000);
            });

            //helper function to serialize form to object
            $.fn.serializeObject = function() {
                const arr = $(this).serializeArray();
                const obj = {};
                for (let item of arr) {
                    obj[item.name] = item.value;
                }
                return obj;
            }
            
            //edit customer form submission
            $('#editCustomerForm').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serializeArray().reduce((obj, item) => {
                    obj[item.name] = item.value;
                    return obj;
                }, {});

                $.ajax({
                    url: API_BASE_URL + '?action=updateCustomer',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#editCustomerModal').modal('hide');
                            loadCustomers();
                            showAlert('success', 'Customer updated successfully');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            });

            //delete customer form submission
            $('#deleteCustomerForm').submit(function(e) {
                e.preventDefault();
                const customerId = $('#delete_customer_id').val();

                $.ajax({
                    url: API_BASE_URL + '?action=deleteCustomer&id=' + customerId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#deleteCustomerModal').modal('hide');
                            loadCustomers();
                            showAlert('success', 'Customer deleted successfully');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            });

            //add appliance form submission
            $('#addApplianceForm').submit(function(e) {
                e.preventDefault();

                const formData = $(this).serializeObject();
                formData.customer_id = currentCustomerContext.id;
                formData.status = 'Active';

                $.ajax({
                    url: API_BASE_URL + '?action=addAppliance',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#addApplianceModal').modal('hide');
                            loadAppliances(currentCustomerContext.id);
                            showAlert('success', 'Appliance added successfully');
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            });

            //edit appliance form handler
            $('#editApplianceForm').submit(function(e) {
                e.preventDefault();

                // Explicitly collect the correct field values
                const formData = {
                    appliance_id: $('#edit_appliance_id').val(),
                    brand: $('#edit_brand').val(),
                    product: $('#edit_product').val(),
                    model_no: $('#edit_model_no').val(),
                    serial_no: $('#edit_serial_no').val(),
                    date_in: $('#edit_date_in').val(),
                    warranty_end: $('#edit_warranty_end').val(),
                    category: $('#edit_category').val(),
                    status: $('#edit_status').val()
                };

                $.ajax({
                    url: API_BASE_URL + '?action=updateAppliance',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#editApplianceModal').modal('hide');
                            $('#editApplianceForm')[0].reset();
                            loadAppliances(currentCustomerContext.id);
                            showAlert('success', 'Appliance updated successfully');
                        } else {
                            alert('Error: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            });

            //delete appliance form handler
            $('#deleteApplianceForm').submit(function(e) {
                e.preventDefault();

                const applianceId = $('#delete_appliance_id').val();

                $.ajax({
                    url: API_BASE_URL + '?action=deleteAppliance&id=' + applianceId,
                    type: 'GET',
                    success: function(response) {
                        if (response && response.success) {
                            $('#deleteApplianceModal').modal('hide');
                            loadAppliances(currentCustomerContext.id);
                            showAlert('success', 'Appliance deleted successfully');
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