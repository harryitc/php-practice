<?php
// Set page variables
$pageTitle = 'Change Password - ShopEasy';
$currentPage = 'complete_password_change';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'My Profile', 'url' => '/Auth/profile'],
    ['title' => 'Change Password', 'url' => '']
];

// Include customer header
include 'app/views/layouts/customer_header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="bi bi-key me-2"></i>Change Your Password</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Identity Verified!</strong> You can now set your new password.
                    </div>

                    <form method="POST" action="/Auth/completePasswordChange">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/Auth/profile" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check me-2"></i>Change Password
                            </button>
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
