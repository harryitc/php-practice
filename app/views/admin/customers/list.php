<?php
require_once 'app/models/UserModel.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        .content-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .table-responsive {
            overflow-x: auto;
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
                        <li class="nav-item">
                            <a class="nav-link active" href="/Order/dashboard"><i class="bi bi-speedometer2"></i> Admin Dashboard</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/Auth/profile"><i class="bi bi-person"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/Auth/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Auth/login"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Auth/register"><i class="bi bi-person-plus"></i> Register</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container mb-5">
            <!-- Admin Dashboard Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-primary"><i class="bi bi-people"></i> Customer Management</h1>
            </div>

            <!-- Admin Navigation -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link" href="/Order/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/Order/list"><i class="bi bi-list-check"></i> Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active" href="/Order/customers"><i class="bi bi-people"></i> Customers</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/Order/revenue"><i class="bi bi-graph-up"></i> Revenue</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customers List -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Customers</h5>
                                <span class="badge bg-primary"><?php echo count($customers); ?> Customers</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Registered</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($customers) > 0): ?>
                                            <?php foreach ($customers as $customer): ?>
                                                <tr>
                                                    <td><?php echo $customer->getId(); ?></td>
                                                    <td><?php echo htmlspecialchars($customer->getName()); ?></td>
                                                    <td><?php echo htmlspecialchars($customer->getEmail()); ?></td>
                                                    <td>
                                                        <?php if ($customer->getRole() === 'admin'): ?>
                                                            <span class="badge bg-danger">Admin</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-info text-dark">Customer</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($customer->getCreatedAt())); ?></td>
                                                    <td>
                                                        <a href="/Order/customerDetail/<?php echo $customer->getId(); ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="bi bi-people text-muted" style="font-size: 2rem;"></i>
                                                    <p class="mt-2 mb-0">No customers found</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
