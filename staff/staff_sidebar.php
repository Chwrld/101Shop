<nav id="sidebar">
    <div class="sidebar-header">
        <h3>
            <img src="../img/Repair.png" class="img-fluid" />
            Repair Service
        </h3>
    </div>
    <ul class="list-unstyled components">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'staff_dashboard.php' ? 'active' : ''; ?>">
            <a href="staff_dashboard.php"><i class="material-icons pb-3">dashboard</i><span>Dashboard</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'services_report.php' || basename($_SERVER['PHP_SELF']) == 'staff_service_report_new.php' || basename($_SERVER['PHP_SELF']) == 'staff_service_report.php' ? 'active' : ''; ?>">
            <a href="staff_service_report_new.php"><i class="material-icons pb-3">description</i><span>Service Report</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'parts_management.php' ? 'active' : ''; ?>">
            <a href="parts_management.php"><i class="material-icons pb-3">inventory_2</i><span>Parts Management</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'customers_info.php' ? 'active' : ''; ?>">
            <a href="customers_info.php"><i class="material-icons pb-3">people</i><span>Customer Info</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'transactions_view.php' ? 'active' : ''; ?>">
            <a href="transactions_view.php"><i class="material-icons pb-3">receipt</i><span>Transactions</span></a>
        </li>
    </ul>

    <div class="logout-container">
        <a href="#" class="btn-logout" id="logoutBtn">
            <span class="material-icons align-middle">logout</span>
            <span class="logout-text">Logout</span>
        </a>
    </div>
</nav>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="logout-icon mb-3">
                    <span class="material-icons" style="font-size: 48px; color: #dc3545;">logout</span>
                </div>
                <h6 class="mb-2">Are you sure you want to log out?</h6>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="../authentication/logout.php" class="btn btn-danger" id="confirmLogout">Logout</a>
            </div>
        </div>
    </div>
</div>


<style>
    #sidebar {
        background: #fff;
        min-height: 100vh;
        width: 250px;
        position: fixed;
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }


    #sidebar .components {
        padding: 0;
        margin: 0;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    #sidebar .components li {
        list-style: none;
    }

    #sidebar .components li a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        color: #222222ff;
        text-decoration: none;
        border-left: 4px solid transparent;
    }

    #sidebar .components li a:hover {
        background: #353535ff;
        color: #fff;
        border-left: 4px solid #00c851;
    }

    #sidebar .components li a:hover i {
        color: #fff;
    }

    #sidebar .components li.active a {
        background: #495057;
        color: #fff;
        border-left: 4px solid #00c851;
    }

    #sidebar .components li.active a i {
        color: #fff;
    }

    /* logout Container */
    .logout-container {
        margin-top: auto;
        padding: 20px;
        border-top: 1px solid #e2e8f0;
    }

    .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background-color: #961623ff;
        color: #fff;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;
        width: 100%;
        max-width: 200px;
        margin: 0 auto;
        text-decoration: none;
    }

    .btn-logout:hover {
        background-color: #dc3545;
        color: #fff;
    }

    .logout-text {
        font-weight: 500;
    }

    .material-icons.align-middle {
        vertical-align: middle;
        font-size: 18px;
    }

    .logout-icon {
        width: 60px;
        height: 60px;
        background: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .logout-icon .material-icons {
        font-size: 30px;
        color: #dc3545;
    }

    .modal-header {
        border-bottom: 1px solid #e9ecef;
        background: #333333;
    }

    .modal-title {
        font-weight: 600;
        color: #ffffff;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        display: flex;
        justify-content: center;
        gap: 15px;
        padding: 20px;
    }

    /* Fix modal backdrop */
    .modal-backdrop {
        z-index: 1040;
    }

    .modal {
        z-index: 1050;
    }

    /* Ensure modal is clickable */
    .modal-dialog {
        position: relative;
        pointer-events: auto;
    }

    /* Fix modal content styling */
    .modal-content {
        background: #ffffff;
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 500;
        min-width: 100px;
    }

    .btn-danger {
        background: #dc3545;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        font-weight: 500;
        min-width: 100px;
    }

    @media (max-width: 768px) {
        #sidebar {
            width: 250px;
        }

        .btn-logout {
            max-width: 180px;
            padding: 8px 12px;
        }

        .logout-text {
            display: inline !important;
        }

        .modal-dialog {
            margin: 20px;
        }

        .modal-footer {
            flex-direction: column;
            gap: 10px;
        }

        .modal-footer .btn {
            width: 100%;
            max-width: 200px;
            margin: 0 auto;
        }
    }

    @media (max-width: 576px) {
        .modal-dialog {
            margin: 10px;
        }

        .logout-icon {
            width: 50px;
            height: 50px;
        }

        .logout-icon .material-icons {
            font-size: 24px;
        }

        .modal-footer {
            padding: 15px;
        }

        .btn-secondary,
        .btn-danger {
            padding: 8px 20px;
            min-width: 80px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutModalElement = document.getElementById('logoutModal');
        const logoutModal = new bootstrap.Modal(logoutModalElement);

        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                logoutModal.show();
            });
        }
        const cancelButtons = logoutModalElement.querySelectorAll('[data-bs-dismiss="modal"]');
        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                logoutModal.hide();
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && logoutModalElement.classList.contains('show')) {
                logoutModal.hide();
            }
        });

        logoutModalElement.addEventListener('click', function(e) {
            if (e.target === this) {
                logoutModal.hide();
            }
        });
    });
</script>

<!-- Include Bootstrap JS if not already included -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Ensure pages restored from bfcache are reloaded to trigger server-side auth -->
<script>
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.replace(window.location.href);
        }
    });
</script>