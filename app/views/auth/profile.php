<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>My Profile - Product Inventory Management</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrapper {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
            margin-top: auto !important;
        }
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        .profile-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .profile-header {
            background-color: #0d6efd;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .profile-body {
            padding: 2rem;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: #6c757d;
        }
        .nav-tabs {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="content-wrapper">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <div class="container">
                <a class="navbar-brand" href="/">Product Inventory Management</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/Product/list"><i class="bi bi-list-ul"></i> Products</a>
                        </li>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Product/add"><i class="bi bi-plus-circle"></i> Add Product</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item active" href="/Auth/profile"><i class="bi bi-person me-2"></i>My Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/Auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container profile-container">
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

            <div class="card profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <h3 class="mb-0"><?php echo htmlspecialchars($user->getName()); ?></h3>
                    <p class="mb-0"><?php echo htmlspecialchars($user->getEmail()); ?></p>
                    <span class="badge bg-<?php echo $user->getRole() === 'admin' ? 'danger' : 'success'; ?>">
                        <?php echo ucfirst($user->getRole()); ?>
                    </span>
                </div>
                <div class="profile-body">
                    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">
                                <i class="bi bi-person me-2"></i>Profile
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="false">
                                <i class="bi bi-lock me-2"></i>Change Password
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="profileTabsContent">
                        <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            <form method="POST" action="/Auth/profile">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required value="<?php echo htmlspecialchars($user->getName()); ?>">
                                    <label for="name"><i class="bi bi-person me-2"></i>Full Name</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user->getEmail()); ?>" disabled>
                                    <label for="email"><i class="bi bi-envelope me-2"></i>Email address</label>
                                    <div class="form-text">Email address cannot be changed.</div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Update Profile
                                </button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                            <form method="POST" action="/Auth/profile" id="passwordForm">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Current Password">
                                    <label for="current_password"><i class="bi bi-lock me-2"></i>Current Password</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password">
                                    <label for="new_password"><i class="bi bi-lock-fill me-2"></i>New Password</label>
                                    <div class="form-text">Password must be at least 6 characters long.</div>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm New Password">
                                    <label for="confirm_password"><i class="bi bi-lock-fill me-2"></i>Confirm New Password</label>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-lock me-2"></i>Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- End of content-wrapper -->

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Product Inventory Management System</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script>
        // Client-side validation for password change
        document.getElementById('passwordForm').addEventListener('submit', function(event) {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Only validate if user is trying to change password
            if (newPassword || confirmPassword || currentPassword) {
                if (!currentPassword) {
                    event.preventDefault();
                    alert('Current password is required!');
                    return;
                }
                
                if (newPassword.length < 6) {
                    event.preventDefault();
                    alert('New password must be at least 6 characters long!');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    event.preventDefault();
                    alert('New passwords do not match!');
                    return;
                }
            } else {
                // If no password fields are filled, don't submit the password form
                const activeTab = document.querySelector('.tab-pane.active');
                if (activeTab.id === 'password') {
                    event.preventDefault();
                }
            }
        });
    </script>
</body>
</html>
