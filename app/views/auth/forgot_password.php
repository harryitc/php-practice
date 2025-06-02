<?php
// Set page variables
$pageTitle = 'Forgot Password - ShopEasy';
$currentPage = 'forgot_password';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'Forgot Password', 'url' => '']
];

// Include customer header
include 'app/views/layouts/customer_header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Forgot Password</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error_message']; ?>
                            <?php unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success_message']; ?>
                            <?php unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>

                    <p class="mb-4">Enter your email address and we'll help you reset your password.</p>

                    <form action="/Auth/forgotPassword" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Continue</button>
                            <a href="/Auth/login" class="btn btn-outline-secondary">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'app/views/layouts/customer_footer.php';
?>
