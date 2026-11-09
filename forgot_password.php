<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Forgot Password - Repair Service</title>
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
                            <h3 class="mb-1" style="font-family: 'Poppins', sans-serif; font-weight: 600;">Forgot Password</h3>
                            <div class="text-muted">Enter your email to reset your password</div>
                        </div>
                        <form id="forgotPasswordForm" autocomplete="off">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="e.g. email@domain.com" required>
                            </div>
                            <div id="messageAlert" class="alert mt-3" style="display: none;"></div>
                            <button type="submit" class="btn btn-block mt-3" style="background: rgb(53, 59, 72); color: #fff;">Send Reset Link</button>
                        </form>
                        <script>
                            document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
                                e.preventDefault();

                                const email = document.getElementById('email').value;
                                const messageDiv = document.getElementById('messageAlert');

                                // Send request to reset password API
                                const submitButton = this.querySelector('button[type="submit"]');
                                const originalButtonText = submitButton.textContent;
                                submitButton.disabled = true;
                                submitButton.textContent = 'Sending...';
                                messageDiv.style.display = 'none';

                                console.log('Sending request with email:', email);
                                fetch('backend/api/reset_password_api.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            email: email
                                        })
                                    })
                                    .then(response => {
                                        console.log('Response status:', response.status);
                                        if (!response.ok) {
                                            return response.json().then(err => {
                                                throw new Error(err.message || `HTTP error! status: ${response.status}`);
                                            });
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        console.log('Response data:', data);
                                        messageDiv.className = 'alert mt-3 ' + (data.success ? 'alert-success' : 'alert-danger');
                                        messageDiv.textContent = data.message || (data.success ? 'Success!' : 'An error occurred');
                                        messageDiv.style.display = 'block';

                                        if (data.success) {
                                            document.getElementById('email').value = '';
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error details:', error);
                                        messageDiv.className = 'alert mt-3 alert-danger';
                                        messageDiv.textContent = error.message || 'An error occurred while processing your request. Please try again.';
                                        messageDiv.style.display = 'block';
                                    })
                                    .finally(() => {
                                        submitButton.disabled = false;
                                        submitButton.textContent = originalButtonText;
                                    });
                            });
                        </script>
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
</body>

</html>