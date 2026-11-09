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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Staff Management</title>
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

    .password-field {
        transition: border 0.3s ease;
    }

    .password-field.success {
        border: 2px solid #4CAF50 !important;
    }

    .password-field.error {
        border: 2px solid #f44336 !important;
    }

    .password-error-message {
        color: #f44336;
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: none;
    }


    .status-badge {
    padding: 0.25em 1em;
    border-radius: 999px;
    font-size: 0.95em;
    font-weight: 500;
        color: #fff;
    min-width: 70px;
    text-align: center;
    letter-spacing: 1px;
    }

    .status-active {
        color: #28a745 !important;
    }

    .status-inactive {
        color: #dc3545 !important;
    }

    

    .filter-controls {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 15px;
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

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
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
            $pageTitle = 'Staff Management';
            $breadcrumb = 'Staff';
            include __DIR__ . '/../layout/navbar.php';
            ?>

            <!-- Main Content -->
            <div class="main-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Manage Staff</h5>
                                <button type="button" class="btn btn-success d-flex align-items-center" data-toggle="modal" data-target="#addStaffModal">
                                    <span class="material-icons mr-2">person_add</span> Add New Staff
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Sorting and Filtering Controls -->
                                <div class="filter-controls">
                                    <!-- Sort Controls -->
                                    <div class="sort-controls">
                                        <div class="form-group d-flex align-items-center">
                                            <label for="dateSort">Sort by:</label>
                                            <select id="dateSort" class="form-control w-auto">
                                                <option value="">All</option>
                                                <option value="latest">Latest</option>
                                                <option value="oldest">Oldest</option>
                                            </select>
                                        </div>
                                        <div class="form-group d-flex align-items-center">
                                            <label for="roleSort">Role:</label>
                                            <select id="roleSort" class="form-control">
                                                <option value="">All</option>
                                                <option value="manager">Manager</option>
                                                <option value="technician">Technician</option>
                                                <option value="secretary">Secretary</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Filter By Status -->
                                    <select id="filterBy" class="form-control w-auto">
                                        <label for="statusSort">status</label>
                                        <option value="all">All</option>
                                        <option value="Active">Active Only</option>
                                        <option value="Inactive">Inactive Only</option>
                                    </select>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Full Name</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Date Created</th>
                                                <th>Last Login</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="staffsTableBody">

                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                    <div class="text-muted">
                                        <span id="staffPaginationInfo">Showing 0 to 0 of 0 entries</span>
                                    </div>
                                    <nav aria-label="Staff navigation">
                                        <ul class="pagination pagination-sm mb-0" id="staffPagination"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Staff Modal -->
            <div class="modal fade" id="addStaffModal">
                <div class="modal-dialog" style="max-width: 900px; width: 90%;">
                    <div class="modal-content">
                        <form method="post" id="addStaffForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Add Staff Member</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" name="fullname" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Username</label>
                                                <input type="text" name="username" class="form-control" required>
                                                <div id="usernameError" class="invalid-feedback" style="display:none;"></div>
                                                <small class="form-text text-muted">Must be unique and at least 4 characters</small>
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Role</label>
                                                <select name="role" class="form-control" required>
                                                    <option value="">Select Role</option>
                                                    <option value="Technician">Technician</option>
                                                    <option value="Secretary">Secretary</option>
                                                    <option value="Manager">Manager</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Password</label>
                                                <input type="password" name="password1" id="passwordInput" class="form-control password-field" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Confirm Password</label>
                                                <input type="password" name="password" id="confirmPasswordInput" class="form-control password-field" required>
                                                <div id="passwordError" class="password-error-message">Password do not match!</div>
                                            </div>
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" name="add_staff" class="btn btn-success">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Staff Modal -->
            <div class="modal fade" id="editStaffModal">
                <div class="modal-dialog" style="max-width: 900px; width: 90%;">
                    <div class="modal-content">
                        <form method="post" id="editStaffForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Staff Member</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="container-fluid">
                                    <input type="hidden" name="staff_id" id="edit_staff_id">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" name="fullname" id="edit_full_name" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Username</label>
                                                <input type="text" name="username" id="edit_username" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" id="edit_email" class="form-control">
                                            </div>
                                            <div class="form-group">
                                                <label>Role</label>
                                                <select name="role" id="edit_role" class="form-control">
                                                    <option value="">Select Role</option>
                                                    <option value="Technician">Technician</option>
                                                    <option value="Secretary">Secretary</option>
                                                    <option value="Manager">Manager</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 d-flex flex-column justify-content-start">
                                            <div style="height: 34px;"></div>
                                            <div class="form-group">
                                                <div class="form-check d-flex align-items-center" style="min-height: 40px;">
                                                    <input type="checkbox" id="changePasswordCheckbox" class="form-check-input" style="margin-bottom:0; margin-right:10px;">
                                                    <label for="changePasswordCheckbox" class="form-check-label mb-0" style="min-width: 120px;">Change Password</label>
                                                </div>
                                            </div>
                                            <div class="form-group" id="passwordFieldGroup" style="display: none;">
                                                <div class="form-group">
                                                    <label>Current Password</label>
                                                    <input type="password" name="current_password" id="edit_current_password" class="form-control password-field">
                                                    <div id="editCurrentPasswordError" class="password-error-message" style="display:none;"></div>
                                                </div>
                                                <div class="form-group">
                                                    <label>New Password</label>
                                                    <input type="password" name="password1" id="edit_password1" class="form-control password-field">
                                                </div>
                                                <div class="form-group">
                                                    <label>Confirm New Password</label>
                                                    <input type="password" name="password" id="edit_password" class="form-control password-field">
                                                    <div id="editPasswordError" class="password-error-message">Password do not match!</div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" id="edit_status" class="form-control">
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Staff Comments Section -->
                                    <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #e0e0e0;">
                                        <div class="row">
                                            <div class="col-12">
                                                <h6 style="font-weight: 600; margin-bottom: 12px;">
                                                    <span class="material-icons" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">comment</span>
                                                    Staff Notes & Comments
                                                </h6>
                                                
                                                <div class="form-group">
                                                    <label for="edit_staff_comment">Add Comment</label>
                                                    <textarea id="edit_staff_comment" class="form-control" rows="3" placeholder="Add notes or comments about this staff member..."></textarea>
                                                </div>
                                                
                                                <button type="button" class="btn btn-sm btn-info" onclick="saveStaffComment()">
                                                    <span class="material-icons" style="font-size: 14px; vertical-align: middle; margin-right: 4px;">add_comment</span>
                                                    Add Comment
                                                </button>
                                                
                                                <!-- Comments Display -->
                                                <div id="staff-comments-container" style="margin-top: 12px; max-height: 200px; overflow-y: auto;">
                                                    <div style="font-size: 12px; color: #999; padding: 8px; text-align: center; font-style: italic;">No comments yet</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" name="edit_staff" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Staff Modal -->
            <div class="modal fade" id="deleteStaffModal">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <form method="post" id="deleteStaffForm">
                            <div class="modal-header">
                                <h4 class="modal-title">Delete Staff Member</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="staff_id" id="delete_staff_id">
                                <p>Are you sure you want to archive this staff member?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" name="delete_staff" class="btn btn-danger">Archive</button>
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

    <!--validate password-->
    <script>
        const PasswordValidator = {
            init(formSelector, options = {}) {
                this.form = document.querySelector(formSelector);
                if (!this.form) return;

                const defaults = {
                    passwordField: 'input[name="password1"]',
                    confirmField: 'input[name="password"]',
                    errorElementId: 'passwordError',
                    isOptional: false,
                    checkboxSelector: null
                };

                this.config = {
                    ...defaults,
                    ...options
                };

                this.passwordInput = this.form.querySelector(this.config.passwordField);
                this.confirmPasswordInput = this.form.querySelector(this.config.confirmField);
                this.errorElement = document.getElementById(this.config.errorElementId);

                if (this.config.checkboxSelector) {
                    this.checkbox = document.querySelector(this.config.checkboxSelector);
                    this.passwordFieldGroup = document.querySelector('#passwordFieldGroup');

                    this.checkbox.addEventListener('change', () => {
                        this.togglePasswordFields();
                    });
                }

                this.setupEvents();
            },

            setupEvents() {
                [this.passwordInput, this.confirmPasswordInput].forEach(input => {
                    if (input) {
                        input.addEventListener('input', () => this.checkLiveMatch());
                    }
                });

                this.form.addEventListener('submit', (e) => {
                    if (!this.validate()) {
                        e.preventDefault();
                        this.showError();
                    }
                });

            },

            togglePasswordFields() {
                const isChecked = this.checkbox.checked;
                this.passwordFieldGroup.style.display = isChecked ? 'block' : 'none';

                if (this.passwordInput) this.passwordInput.required = isChecked;
                if (this.confirmPasswordInput) this.confirmPasswordInput.required = isChecked;

                if (!isChecked) {
                    if (this.passwordInput) this.passwordInput.value = '';
                    if (this.confirmPasswordInput) this.confirmPasswordInput = '';

                    this.resetValidationState();
                }
            },

            validate() {
                if (this.config.isOptional && this.checkbox && !this.checkbox.checked) {
                    return true;
                }

                if (!this.passwordInput || !this.confirmPasswordInput) {
                    return true;
                }

                if (this.config.isOptional && this.passwordInput.value === '' && this.confirmPasswordInput.value === '') {
                    return true;
                }

                const isValid = this.passwordInput.value === this.confirmPasswordInput.value;
                this.setValidState(isValid);
                return isValid;

            },

            checkLiveMatch() {
                if (!this.passwordInput || !this.confirmPasswordInput || (this.config.isOptional && this.checkbox && !this.checkbox.checked)) {
                    return;
                }

                const password1 = this.passwordInput.value;
                const password2 = this.confirmPasswordInput.value;

                if (this.config.isOptional && password1 === '' && password2 === '') {
                    this.errorElement.style.display = 'none';
                    this.setValidState(true);
                    return;
                }

                const isValid = password1 === password2;
                this.setValidState(isValid);
                if (this.errorElement) {
                    this.errorElement.style.display = isValid ? 'none' : 'block';
                }
            },

            showError() {
                if (this.errorElement) {
                    this.errorElement.style.display = 'block';
                }
                this.setValidState(false);
            },

            setValidState(isValid) {
                [this.passwordInput, this.confirmPasswordInput].forEach(input => {
                    if (input) {
                        input.classList.toggle('error', !isValid);
                        input.classList.toggle('success', isValid);
                    }
                });
            },

            resetValidationState() {
                [this.passwordInput, this.confirmPasswordInput].forEach(input => {
                    if (input) {
                        input.classList.remove('error', 'success');
                    }
                });
                if (this.errorElement) {
                    this.errorElement.style.display = 'none';
                }
            }
        };

        const usernameValidator = {
            init() {
                this.errorElement = $('#usernameError');
                $('input[name="username"]').on('input', this.validate.bind(this));
            },

            validate() {
                const username = $('input[name="username"]').val();
                if (username.length < 4) {
                    this.errorElement.text('Username must be at least 4 characters').show();
                    return false;
                }
                this.errorElement.hide();
                return true;
            }
        };

        // Staff Comments Functions
        let currentStaffId = null;

        function saveStaffComment() {
            const staffId = $('#edit_staff_id').val();
            const commentText = $('#edit_staff_comment').val().trim();
            const createdBy = 1; // Would come from session in real app

            if (!staffId) {
                alert('Please save the staff member first');
                return;
            }

            if (!commentText) {
                alert('Please enter a comment');
                return;
            }

            $.ajax({
                url: API_BASE_URL + '?action=addStaffComment',
                type: 'POST',
                data: JSON.stringify({
                    staff_id: staffId,
                    comment_text: commentText,
                    created_by: createdBy
                }),
                contentType: 'application/json',
                success: function(response) {
                    if (response && response.success) {
                        $('#edit_staff_comment').val('');
                        loadStaffComments(staffId);
                        alert('Comment added successfully');
                    } else {
                        alert('Error: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    alert('Error adding comment: ' + xhr.responseText);
                }
            });
        }

        function displayStaffComments(comments) {
            const container = $('#staff-comments-container');

            if (!comments || comments.length === 0) {
                container.html('<div style="font-size: 12px; color: #999; padding: 8px; text-align: center; font-style: italic;">No comments yet</div>');
                return;
            }

            let html = '';
            comments.forEach(comment => {
                html += `
                    <div style="background: #f9f9f9; padding: 10px; margin-bottom: 8px; border-radius: 4px; border-left: 3px solid #17a2b8;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-weight: 600; font-size: 11px; color: #333;">${comment.author}</span>
                            <span style="font-size: 10px; color: #999;">${comment.timestamp}</span>
                        </div>
                        <div style="font-size: 11px; color: #555; line-height: 1.4; margin-bottom: 6px;">${comment.text}</div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteStaffComment(${comment.id}, '${currentStaffId}')" style="font-size: 10px; padding: 2px 6px;">Delete</button>
                    </div>
                `;
            });

            container.html(html);
        }

        function loadStaffComments(staffId) {
            currentStaffId = staffId;
            
            $.ajax({
                url: API_BASE_URL + '?action=getStaffComments&staff_id=' + staffId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        displayStaffComments(response.data.comments);
                    } else {
                        $('#staff-comments-container').html('<div style="font-size: 12px; color: #999; padding: 8px; text-align: center; font-style: italic;">No comments yet</div>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading comments:', xhr);
                    $('#staff-comments-container').html('<div style="font-size: 12px; color: #999; padding: 8px; text-align: center; font-style: italic;">No comments yet</div>');
                }
            });
        }

        function deleteStaffComment(commentId, staffId) {
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }

            $.ajax({
                url: API_BASE_URL + '?action=deleteStaffComment&id=' + commentId,
                type: 'GET',
                success: function(response) {
                    if (response && response.success) {
                        loadStaffComments(staffId);
                        alert('Comment deleted successfully');
                    } else {
                        alert('Error: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    alert('Error deleting comment');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const addStaffValidator = Object.create(PasswordValidator);
            const editStaffValidator = Object.create(PasswordValidator);

            addStaffValidator.init('#addStaffForm', {
                passwordField: '#addStaffForm input[name="password1"]',
                confirmField: '#addStaffForm input[name="password"]',
                errorElementId: 'passwordError',
                isOptional: false
            });

            editStaffValidator.init('#editStaffForm', {
                passwordField: '#editStaffForm input[name="password1"]',
                confirmField: '#editStaffForm input[name="password"]',
                errorElementId: 'editPasswordError',
                isOptional: true,
                checkboxSelector: '#changePasswordCheckbox'
            });

            $('#editStaffModal').on('show.bs.modal', function() {
                $('#changePasswordCheckbox').prop('checked', false);
                $('#passwordFieldGroup').hide();
                $('#edit_password1, #edit_password').val('').removeClass('error success');
                $('#editPasswordError').hide();
            });
        });
    </script>

    <script type="text/javascript">
        const API_BASE_URL = '../backend/api/staff_api.php';

        // Store all staff for filtering/sorting
        let allStaffs = [];
        // Pagination state
        let staffCurrentPage = 1;
        let staffPageSize = 10;
        // Search state
        let staffSearchTerm = '';

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
            loadStaffs();

            $(".xp-menubar").on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            $(".xp-menubar,.body-overlay").on('click', function() {
                $('#sidebar,.body-overlay').toggleClass('show-nav');
            });

            // Search functionality integrated with server-side pagination
            const $searchInput = $('#searchInput');
            $searchInput.on('keyup', function() {
                staffSearchTerm = ($(this).val() || '').toLowerCase().trim();
                staffCurrentPage = 1;
                loadStaffs(1);
            });
            // Prevent Enter from submitting/reloading; trigger search instead
            $searchInput.on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    staffSearchTerm = ($(this).val() || '').toLowerCase().trim();
                    staffCurrentPage = 1;
                    loadStaffs(1);
                }
            });
            // If input is inside a form, prevent default submit and search
            const $searchForm = $searchInput.closest('form');
            if ($searchForm.length) {
                $searchForm.on('submit', function(e) {
                    e.preventDefault();
                    staffSearchTerm = ($searchInput.val() || '').toLowerCase().trim();
                    staffCurrentPage = 1;
                    loadStaffs(1);
                });
            }
            // Optional search button support if present
            $('#searchBtn, #searchButton').on('click', function(e) {
                e.preventDefault();
                staffSearchTerm = ($searchInput.val() || '').toLowerCase().trim();
                staffCurrentPage = 1;
                loadStaffs(1);
            });

            // Sort and filter event listeners
            $('#dateSort, #roleSort, #filterBy').change(function() {
                staffCurrentPage = 1;
                applySortingAndFiltering();
            });
            
            // Removed DOM-only filter; search is handled in applySortingAndFiltering

            //checkbox change password
            $('#changePasswordCheckbox').change(function() {
                $('#passwordFieldGroup').toggle(this.checked);
            });

            $('#editStaffModal').on('show.bs.modal', function() {
                $('#changePasswordCheckbox').prop('checked', false);
                $('#passwordFieldGroup').hide();
            });


            function loadStaffs(page = 1) {
                console.log("Attempting to load staffs from:", API_BASE_URL);

                const params = new URLSearchParams({
                    action: 'getAllStaffs',
                    page: String(page),
                    itemsPerPage: String(staffPageSize),
                    search: staffSearchTerm || ''
                });

                $.ajax({
                    url: API_BASE_URL + '?' + params.toString(),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Full API Response:", response);

                        if (response && response.success && response.data) {
                            const payload = response.data; 
                            allStaffs = Array.isArray(payload.staffs) ? payload.staffs : [];
                            staffCurrentPage = Number(payload.currentPage) || 1;
                            staffPageSize = Number(payload.itemsPerPage) || staffPageSize;
                            renderStaffs(allStaffs);
                            updateStaffPaginationInfo(payload.totalItems || allStaffs.length, (staffCurrentPage - 1) * staffPageSize + 1, (staffCurrentPage - 1) * staffPageSize + allStaffs.length);
                            renderStaffPagination(payload.totalPages || 1);
                            } else {
                            const errorMsg = response?.message || 'Unknown error';
                            console.error("API Error:", errorMsg);
                            $('#staffsTableBody').html('<tr><td colspan="10">Error: ' + errorMsg + '</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        $('#staffsTableBody').html('<tr><td colspan="10">Failed to load data. Check console.</td></tr>');
                    }
                });
            }

            // Apply sorting and filtering
            function applySortingAndFiltering() {
                let filteredStaffs = [...allStaffs];

                // Apply filter
                const filterValue = $('#filterBy').val();
                if (filterValue !== 'all') {
                    filteredStaffs = filteredStaffs.filter(staff =>
                        staff.status && staff.status.toLowerCase() === filterValue.toLowerCase()
                    );
                }

                // Apply sorting
                const dateSortValue = $('#dateSort').val();
                const roleSortValue = $('#roleSort').val();

                // Date sorting
                if (dateSortValue === 'latest') {
                    filteredStaffs.sort((a, b) => new Date(b.date_created) - new Date(a.date_created));
                } else if (dateSortValue === 'oldest') {
                    filteredStaffs.sort((a, b) => new Date(a.date_created) - new Date(b.date_created));
                }

                // Role sorting
                if (roleSortValue) {
                    filteredStaffs = filteredStaffs.filter(staff =>
                        staff.role && staff.role.toLowerCase() === roleSortValue.toLowerCase()
                    );
                }

                renderStaffs(filteredStaffs);
            }

            function renderStaffs(staffs) {
    console.log("Rendering staffs:", staffs);

    const $tableBody = $('#staffsTableBody');
    $tableBody.empty();

    if (!Array.isArray(staffs)) {
        console.error("Invalid staff data - expected array:", staffs);
        $tableBody.html('<tr><td colspan="10">Error: Invalid data format</td></tr>');
        return;
    }

    if (staffs.length === 0) {
        $tableBody.html('<tr><td colspan="10" class="text-center">No staffs found</td></tr>');
        updateStaffPaginationInfo(0, 0, 0);
        $('#staffPagination').empty();
        return;
    }

    try {
        const html = staffs.map(staff => {
            let statusText = staff.status || '';
            let statusSpan = '';
            if (statusText.toLowerCase() === 'active') {
                statusSpan = '<span class="status-active">Active</span>';
            } else if (statusText.toLowerCase() === 'inactive') {
                statusSpan = '<span class="status-inactive">Inactive</span>';
            } else {
                statusSpan = `<span>${statusText}</span>`;
            }

            // Format dates for better display
            const dateCreated = staff.date_created || '';
            const lastLogin = staff.last_login || '';

            return `
                <tr>
                    <td>${staff.staff_id || ''}</td>
                    <td>${staff.full_name || ''}</td>
                    <td>${staff.username || ''}</td>
                    <td>${staff.email || ''}</td>
                    <td>${staff.role || ''}</td>
                    <td>${statusSpan}</td>
                    <td>${dateCreated}</td>
                    <td>${lastLogin}</td>
                    <td>
                        <span>
                            <a href="#" class="edit-staff" data-id="${staff.staff_id}" data-toggle="modal" data-target="#editStaffModal">
                                <i class="material-icons" data-toggle="tooltip" title="Edit">&#xE254;</i>
                            </a>
                            <a href="#" class="delete-staff" data-id="${staff.staff_id}" data-toggle="modal" data-target="#deleteStaffModal">
                                <i class="material-icons" data-toggle="tooltip" title="Archive">archive</i>
                            </a>
                        </span>
                    </td>
                </tr>
            `;
        }).join('');

        $('#staffsTableBody').html(html);
        bindStaffsEvents();
    } catch (error) {
        console.error("Error rendering staffs:", error);
        $tableBody.html('<tr><td colspan="10">Error displaying staff data</td></tr>');
    }
}
            
            function updateStaffPaginationInfo(totalItems, start, end) {
                const $info = $('#staffPaginationInfo');
                if (totalItems === 0) {
                    $info.text('Showing 0 to 0 of 0 entries');
                    return;
                }
                $info.text(`Showing ${start} to ${end} of ${totalItems} entries`);
            }

            function renderStaffPagination(totalPages) {
                const $pagination = $('#staffPagination');
                $pagination.empty();
                
                const prevDisabled = staffCurrentPage === 1 ? 'disabled' : '';
                $pagination.append(`<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${staffCurrentPage - 1}">Previous</a></li>`);

                // windowed page numbers (max 5)
                const maxVisible = 5;
                let start = Math.max(1, staffCurrentPage - Math.floor(maxVisible / 2));
                let end = Math.min(totalPages, start + maxVisible - 1);
                if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);

                if (start > 1) {
                    $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
                    if (start > 2) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                }

                for (let i = start; i <= end; i++) {
                    const active = i === staffCurrentPage ? 'active' : '';
                    $pagination.append(`<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
                }

                if (end < totalPages) {
                    if (end < totalPages - 1) $pagination.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
                    $pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
                }

                // When totalPages is 0 (no data), treat as 1 to render disabled controls consistently
                if (totalPages < 1) totalPages = 1;
                const nextDisabled = staffCurrentPage === totalPages ? 'disabled' : '';
                $pagination.append(`<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${staffCurrentPage + 1}">Next</a></li>`);

                $pagination.find('.page-link').on('click', function(e) {
                    e.preventDefault();
                    const page = parseInt($(this).data('page'));
                    if (!isNaN(page) && page !== staffCurrentPage) {
                        staffCurrentPage = page;
                        loadStaffs(staffCurrentPage);
                    }
                });
            }

            function bindStaffsEvents() {
                //edit staffs
                $('.edit-staff').click(function() {
                    const staffId = $(this).data('id');

                    $.ajax({
                        url: API_BASE_URL + '?action=getStaffsById&id=' + staffId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.success) {
                                $('#edit_staff_id').val(response.data.staff_id);
                                $('#edit_full_name').val(response.data.full_name);
                                $('#edit_username').val(response.data.username);
                                $('#edit_email').val(response.data.email);
                                $('#edit_role').val(response.data.role);
                                $('#edit_status').val(response.data.status);
                                
                                // Load comments for this staff member
                                loadStaffComments(staffId);
                            }
                        }
                    });
                });

                //delete staff
                $('.delete-staff').click(function() {
                    $('#delete_staff_id').val($(this).data('id'));
                    $('#deleteStaffModal').modal('show');
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

            //username validate
            $('input[name="username"]').on('blur', function() {
                const username = $(this).val();
                if (username.length < 3) return;

                $.get(API_BASE_URL + '?action=checkUsername&username=' + encodeURIComponent(username)).done(function(response) {
                    if (response.exists) {
                        $('#usernameError').text('Username already taken').show();
                        $(this).addClass('is-invalid');
                    } else {
                        $('#usernameError').hide();
                        $(this).removeClass('is-invalid');
                    }
                });
            });

            //add staff
            $('#addStaffForm').submit(function(e) {
                e.preventDefault();

                const formData = $(this).serializeArray().reduce((obj, item) => {
                    obj[item.name] = item.value;
                    return obj;
                }, {});

                $.ajax({
                    url: API_BASE_URL + '?action=addStaff',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#addStaffModal').modal('hide');
                            $('#addStaffForm')[0].reset();
                            loadStaffs();
                            showAlert('success', 'Staff added successfully');
                        } else if (response && response.message && response.message.toLowerCase().includes('username')) {
                            $('#usernameError').text('That username is already taken. Please choose another.').show();
                            $('input[name="username"]').addClass('is-invalid');
                        } else {
                            alert('Error adding staff: ' + (response?.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Error: ' + xhr.responseText;
                        if (xhr.responseText && xhr.responseText.includes('Username')) {
                            $('#usernameError').text('That username is already taken. Please choose another.').show();
                            $('input[name="username"]').addClass('is-invalid');
                        } else {
                            alert(msg);
                        }
                    }
                });
            });

            //edit staff
            $('#editStaffForm').submit(function(e) {
                e.preventDefault();

                // Reset error states
                $('#edit_current_password').removeClass('error');
                $('#editCurrentPasswordError').hide();
                $('#editPasswordError').hide();

                if ($('#changePasswordCheckbox').is(':checked')) {
                    const newPassword1 = $('#edit_password1').val();
                    const newPassword2 = $('#edit_password').val();
                    const currentPassword = $('#edit_current_password').val();

                    //verify current password via API
                    $.ajax({
                        url: API_BASE_URL + '?action=verifyCurrentPassword',
                        type: 'POST',
                        data: JSON.stringify({
                            staff_id: $('#edit_staff_id').val(),
                            currentPassword: currentPassword
                        }),
                        contentType: 'application/json',
                        success: function(response) {
                            if (response && response.success) {
                                if (newPassword1 !== newPassword2) {
                                    $('#editPasswordError').text('Passwords do not match!').show();
                                    $('#edit_password1, #edit_password').addClass('error').removeClass('success');
                                    return;
                                }
                                submitStaffUpdate();
                            } else {
                                $('#edit_current_password').addClass('error').removeClass('success');
                                $('#editCurrentPasswordError').text('Current password is incorrect').show();
                                $('#editPasswordError').hide();
                                $('#edit_current_password').focus();
                            }
                        },
                        error: function(xhr) {
                            $('#edit_current_password').addClass('error').removeClass('success');
                            $('#editCurrentPasswordError').text('Current password is incorrect').show();
                            $('#editPasswordError').hide();
                        }
                    });
                } else {
                    //no password change requested proceed with update
                    submitStaffUpdate();
                }
            });

            function submitStaffUpdate() {
                let formData = {};

                formData.staff_id = $('#edit_staff_id').val();
                formData.fullname = $('#edit_full_name').val();
                formData.username = $('#edit_username').val();
                formData.email = $('#edit_email').val();
                formData.role = $('#edit_role').val();
                formData.status = $('#edit_status').val();

                //add password data if checkbox is checked
                if ($('#changePasswordCheckbox').is(':checked')) {
                    formData.currentPassword = $('#edit_current_password').val();
                    formData.password = $('#edit_password').val(); //this becomes the new password
                }

                $.ajax({
                    url: API_BASE_URL + '?action=updateStaff',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#editStaffModal').modal('hide');
                            $('#editStaffForm')[0].reset();
                            $('#changePasswordCheckbox').prop('checked', false);
                            $('#passwordFieldGroup').hide();
                            loadStaffs();
                            showAlert('success', 'Staff updated successfully');
                        } else {
                            alert('Error: ' + (response.message) || 'Unkown error');
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                }); 
            }

            //delete staff
            $('#deleteStaffForm').submit(function(e) {
                e.preventDefault();
                const staffId = $('#delete_staff_id').val();

                $.ajax({
                    url: API_BASE_URL + '?action=deleteStaff&id=' + staffId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            $('#deleteStaffModal').modal('hide');
                            $('#deleteStaffForm')[0].reset();
                            loadStaffs();
                            showAlert('success', 'Staff archived successfully');
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