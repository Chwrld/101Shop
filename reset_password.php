<?php
require_once 'backend/handlers/Database.php';

// Set timezone to match MySQL server
date_default_timezone_set('Asia/Manila');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'reset_password_errors.log');

// Verify token
$token = $_GET['token'] ?? '';
$valid = false;
$message = '';

error_log("Received token: " . $token);

if (!empty($token)) {
    try {
        $db = new Database();
        $conn = $db->getConnection();

        $query = "SELECT staff_id, email, reset_token, reset_token_expiry FROM staffs WHERE reset_token = ? AND UNIX_TIMESTAMP(reset_token_expiry) > UNIX_TIMESTAMP()";
        error_log("Query: " . $query);

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $token);
        error_log("Checking token: " . $token);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $valid = true;
            $staff = $result->fetch_assoc();
            error_log("Found valid token for staff: " . $staff['email']);
            error_log("Token expiry: " . $staff['reset_token_expiry']);
        } else {
            // Check if token exists but is expired
            $stmt->close();
            $stmt = $conn->prepare("SELECT reset_token_expiry FROM staffs WHERE reset_token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $expiry_result = $stmt->get_result();

            if ($expiry_result->num_rows > 0) {
                $expiry_data = $expiry_result->fetch_assoc();
                error_log("Token found but expired. Expiry was: " . $expiry_data['reset_token_expiry']);
                $message = 'Reset token has expired';
            } else {
                error_log("No matching token found in database");
                $message = 'Invalid reset token';
            }
        }
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        $message = "An error occurred while validating your reset token";
        $valid = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reset Password - Repair Service</title>
    <link rel="shortcut Icon" href="img/Repair.png" type="image/x-icon">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body class="d-flex align-items-center" style="min-height: 100vh; background: rgb(74, 82, 99);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-5">
                <div class="card shadow-sm border-0 mt-5" style="border-radius: 1.5rem;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="img/Repair.png" alt="Repair Service Logo" width="60" class="mb-2">
                            <h3 class="mb-1" style="font-family: 'Poppins', sans-serif; font-weight: 600;">Reset Password</h3>
                            <?php if (!$valid): ?>
                                <div class="alert alert-danger"><?php echo $message ?: 'Invalid reset link'; ?></div>
                            <?php else: ?>
                                <div class="text-muted">Enter your new password</div>
                            <?php endif; ?>
                        </div>
                        <?php if ($valid): ?>
                            <form id="resetPasswordForm" method="POST">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                </div>
                                <div class="form-group mt-3">
                                    <label for="confirmPassword">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required minlength="8">
                                </div>
                                <div id="passwordError" class="text-danger mt-2" style="display: none;"></div>
                                <button type="submit" class="btn btn-block mt-4" style="background: rgb(53, 59, 72); color: #fff;">Reset Password</button>
                            </form>
                        <?php endif; ?>
                        <div class="mt-3 text-center">
                            <a href="index.php" class="text-muted small">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <?php if ($valid): ?>
        <script>
            document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                const errorDiv = document.getElementById('passwordError');

                if (password !== confirmPassword) {
                    errorDiv.textContent = 'Passwords do not match';
                    errorDiv.style.display = 'block';
                    return;
                }

                if (password.length < 8) {
                    errorDiv.textContent = 'Password must be at least 8 characters long';
                    errorDiv.style.display = 'block';
                    return;
                }

                // Submit the form data
                const requestData = {
                    token: document.querySelector('input[name="token"]').value,
                    password: password
                };
                console.log('Sending request:', requestData);

                const form = this; // Get reference to the form

                // Disable submit button and show loading state
                const submitButton = form.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Resetting Password...';
                errorDiv.style.display = 'none';

                fetch('backend/api/update_password_api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(requestData)
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json().then(data => {
                            if (!response.ok) {
                                throw new Error(data.message || `HTTP error! status: ${response.status}`);
                            }
                            return data;
                        });
                    })
                    .then(data => {
                        console.log('Success:', data);
                        if (data.success) {
                            // Show success message
                            errorDiv.className = 'alert alert-success';
                            errorDiv.textContent = data.message;
                            errorDiv.style.display = 'block';

                            // Disable the form
                            const formElements = form.elements;
                            for (let i = 0; i < formElements.length; i++) {
                                formElements[i].disabled = true;
                            }

                            // Redirect after a delay
                            setTimeout(() => {
                                window.location.href = 'index.php';
                            }, 2000);
                        } else {
                            errorDiv.className = 'alert alert-danger';
                            errorDiv.textContent = data.message || 'An error occurred';
                            errorDiv.style.display = 'block';
                            submitButton.disabled = false;
                            submitButton.textContent = originalButtonText;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        errorDiv.className = 'alert alert-danger';
                        errorDiv.textContent = error.message || 'An error occurred. Please try again later.';
                        errorDiv.style.display = 'block';
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                    });
            });
        </script>
    <?php endif; ?>
</body>

</html>