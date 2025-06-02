<?php
// Set page variables
$pageTitle = 'Login - ShopEasy';
$currentPage = 'login';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'Login', 'url' => '']
];

// Include customer header (login is for customers, admins can also use it)
include 'app/views/layouts/customer_header.php';
?>

<style>
.login-container {
    max-width: 450px;
    margin: 0 auto;
    padding: 2rem 0;
}
.login-card {
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}
.login-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    text-align: center;
}
.login-body {
    padding: 2rem;
}
.login-footer {
    background-color: #f8f9fa;
    padding: 1rem;
    text-align: center;
    border-top: 1px solid #dee2e6;
}
.form-floating {
    margin-bottom: 1rem;
}
.btn-login {
    width: 100%;
    padding: 0.75rem;
    font-size: 1.1rem;
}
</style>

<div class="container login-container">
            <!-- Success Message -->
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
                // Clear the message after displaying it
                unset($_SESSION['success_message']);
            endif; ?>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card login-card">
                <div class="login-header">
                    <h3 class="mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Login</h3>
                    <p class="mb-0">Sign in to your account</p>
                </div>
                <div class="login-body">
                    <form method="POST" action="/Auth/login">
                        <div class="form-floating">
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <label for="email"><i class="bi bi-envelope me-2"></i>Email address</label>
                        </div>
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <div>
                                <a href="/Auth/forgotPassword">Forgot password?</a>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </button>
                    </form>
                </div>
                <div class="login-footer">
                    <p class="mb-0">Don't have an account? <a href="/Auth/register">Register here</a></p>
                </div>
            </div>
        </div>
    </div>

<?php include 'app/views/layouts/customer_footer.php'; ?>
