<?php
// Set page variables
$pageTitle = 'My Profile';
$currentPage = 'profile';

// Check if user is admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

if ($isAdmin) {
    // Admin breadcrumbs and layout
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => '/Order/dashboard'],
        ['title' => 'My Profile', 'url' => '']
    ];
    include 'app/views/layouts/admin_header.php';
} else {
    // Customer breadcrumbs and layout
    $breadcrumbs = [
        ['title' => 'Home', 'url' => '/'],
        ['title' => 'My Profile', 'url' => '']
    ];
    include 'app/views/layouts/customer_header.php';
}
?>
<?php if ($isAdmin): ?>
<!-- ADMIN PROFILE VIEW -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person me-2"></i>Profile Information
                </h5>
            </div>
            <div class="card-body">
                <form action="/Auth/updateProfile" method="POST" id="profileForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?php echo htmlspecialchars($user->getName()); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($user->getEmail()); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" value="Administrator" readonly>
                            <small class="text-muted">Role cannot be changed</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="created_at" class="form-label">Member Since</label>
                            <input type="text" class="form-control"
                                   value="<?php echo date('F j, Y', strtotime($user->getCreatedAt())); ?>" readonly>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check me-2"></i>Update Profile
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-lock me-2"></i>Change Password
                </h5>
            </div>
            <div class="card-body">
                <form action="/Auth/changePassword" method="POST" id="passwordForm">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password *</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-key me-2"></i>Change Password
                    </button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>Account Info
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>User ID:</strong> #<?php echo $user->getId(); ?>
                </div>
                <div class="mb-2">
                    <strong>Role:</strong> <span class="badge bg-primary">Administrator</span>
                </div>
                <div class="mb-2">
                    <strong>Status:</strong> <span class="badge bg-success">Active</span>
                </div>
                <div class="mb-2">
                    <strong>Last Login:</strong> <?php echo date('M j, Y g:i A'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- CUSTOMER PROFILE VIEW -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Profile Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-person-fill text-white" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    <h2 class="mb-1"><?php echo htmlspecialchars($user->getName()); ?></h2>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($user->getEmail()); ?></p>
                    <small class="text-muted">Member since <?php echo date('F Y', strtotime($user->getCreatedAt())); ?></small>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person me-2"></i>Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/Auth/updateProfile" method="POST" id="profileForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?php echo htmlspecialchars($user->getName()); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($user->getEmail()); ?>" required>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check me-2"></i>Update Profile
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-lock me-2"></i>Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/Auth/changePassword" method="POST" id="passwordForm">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="current_password" class="form-label">Current Password *</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="new_password" class="form-label">New Password *</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="/Order/myOrders" class="btn btn-outline-primary w-100">
                                <i class="bi bi-bag me-2"></i>My Orders
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/Cart" class="btn btn-outline-primary w-100">
                                <i class="bi bi-cart me-2"></i>Shopping Cart
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/Product/list" class="btn btn-outline-primary w-100">
                                <i class="bi bi-shop me-2"></i>Browse Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Include appropriate footer
if ($isAdmin) {
    include 'app/views/layouts/admin_footer.php';
} else {
    include 'app/views/layouts/customer_footer.php';
}
?>

<script>
// Form validation and handling
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();

    if (!name || !email) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return;
    }

    if (!isValidEmail(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return;
    }
});

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (!currentPassword || !newPassword || !confirmPassword) {
        e.preventDefault();
        alert('Please fill in all password fields');
        return;
    }

    if (newPassword.length < 6) {
        e.preventDefault();
        alert('New password must be at least 6 characters long');
        return;
    }

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New password and confirmation do not match');
        return;
    }
});

function resetForm() {
    document.getElementById('profileForm').reset();
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
</script>
