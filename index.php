<?php
require_once __DIR__ . '/bootstrap.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Repair Service</title>
    <link rel="shortcut Icon" href="img/Repair.png" type="image/x-icon">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body class="d-flex align-items-center" style="min-height: 100vh; position: relative; background: rgb(74, 82, 99);">
    <div style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 0;">
        <img src="img/101_repairshop_image.jpg" alt="Background" style="width: 100vw; height: 100vh; object-fit: cover; opacity: 0.5;">
        <div style="position: absolute; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(74, 82, 99, 0.85);"></div>
    </div>


    <div class="container" style="position: relative; z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-5">
                <div class="card shadow-sm border-0 mt-5" style="border-radius: 1.5rem;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="img/Repair.png" alt="Repair Service Logo" width="60" class="mb-2">
                            <h3 class="mb-1" style="font-family: 'Poppins', sans-serif; font-weight: 600;">Repair Service</h3>

                            <div class="text-muted">User Login</div>

                            <?php if (isset($_SESSION['login_error'])): ?>
                                <div class="alert alert-danger mt-3">
                                    <?php
                                    echo $_SESSION['login_error']; // allow HTML for line breaks
                                    unset($_SESSION['login_error']);
                                    ?>
                                </div>
                            <?php endif; ?>

                        </div>

                        <form action="./authentication/auth.php" method="POST" autocomplete="off">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required
                                    value="<?php
                                            $username = '';
                                            if (isset($_SESSION['attempted_username'])) {
                                                $username = htmlspecialchars($_SESSION['attempted_username']);
                                                unset($_SESSION['attempted_username']);
                                            }
                                            echo $username;
                                            ?>">
                            </div>
                            <div class="form-group position-relative">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                <span class="material-icons position-absolute" id="togglePassword" style="top: 38px; right: 16px; cursor: pointer; user-select: none; color: #888;">visibility_off</span>
                            </div>
                            <div class="d-flex justify-content-end mb-2">
                                <a href="forgot_password.php" class="text-muted small" style="text-decoration: underline;">Forgot password?</a>
                            </div>
                            <button type="submit" class="btn btn-block mt-3" style="background: rgb(53, 59, 72); color: #fff;">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            if (passwordInput && togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.type === 'password' ? 'text' : 'password';
                    passwordInput.type = type;
                    this.textContent = type === 'password' ? 'visibility_off' : 'visibility';
                });
            }
        });
    </script>


</body>

</html>