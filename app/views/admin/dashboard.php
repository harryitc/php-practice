<?php
require_once 'app/models/UserModel.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        .content-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .dashboard-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .stat-label {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
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
                <h1 class="text-primary"><i class="bi bi-speedometer2"></i> Admin Dashboard</h1>
            </div>

            <!-- Admin Navigation -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" href="/Order/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/Order/list"><i class="bi bi-list-check"></i> Orders</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/Order/customers"><i class="bi bi-people"></i> Customers</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/Order/revenue"><i class="bi bi-graph-up"></i> Revenue</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Statistics -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h4 mb-3">Order Statistics</h2>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card dashboard-card shadow-sm bg-primary text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value"><?php echo $totalOrders; ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-cart4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card dashboard-card shadow-sm bg-warning text-dark">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value"><?php echo $pendingOrders; ?></div>
                                <div class="stat-label">Pending Orders</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card dashboard-card shadow-sm bg-success text-white">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value"><?php echo $deliveredOrders; ?></div>
                                <div class="stat-label">Delivered Orders</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Statistics -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h4 mb-3">Revenue Statistics</h2>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card dashboard-card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value">$<?php echo number_format($totalRevenue, 2); ?></div>
                                <div class="stat-label">Total Revenue</div>
                            </div>
                            <div class="stat-icon text-success">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card dashboard-card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value">$<?php echo number_format($monthlyRevenue, 2); ?></div>
                                <div class="stat-label">Monthly Revenue</div>
                            </div>
                            <div class="stat-icon text-primary">
                                <i class="bi bi-calendar-month"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card dashboard-card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value">$<?php echo number_format($dailyRevenue, 2); ?></div>
                                <div class="stat-label">Daily Revenue</div>
                            </div>
                            <div class="stat-icon text-info">
                                <i class="bi bi-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Statistics -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h4 mb-3">Customer Statistics</h2>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card dashboard-card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value"><?php echo $totalCustomers; ?></div>
                                <div class="stat-label">Total Customers</div>
                            </div>
                            <div class="stat-icon text-primary">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="h5 mb-0">Recent Orders</h2>
                                <a href="/Order/list" class="btn btn-sm btn-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($recentOrders) > 0): ?>
                                            <?php foreach ($recentOrders as $order): ?>
                                                <?php
                                                    // Get customer name from the controller
                                                    $userId = $order->getUserId();
                                                    $customerName = 'Unknown';
                                                    if ($userId) {
                                                        $customer = (new UserModel())->findById($userId);
                                                        if ($customer) {
                                                            $customerName = $customer->getName();
                                                        }
                                                    }

                                                    // Determine status badge class
                                                    $statusClass = '';
                                                    switch ($order->getStatus()) {
                                                        case 'pending':
                                                            $statusClass = 'bg-warning text-dark';
                                                            break;
                                                        case 'processing':
                                                            $statusClass = 'bg-info text-dark';
                                                            break;
                                                        case 'shipped':
                                                            $statusClass = 'bg-primary';
                                                            break;
                                                        case 'delivered':
                                                            $statusClass = 'bg-success';
                                                            break;
                                                        case 'cancelled':
                                                            $statusClass = 'bg-danger';
                                                            break;
                                                    }
                                                ?>
                                                <tr>
                                                    <td>#<?php echo $order->getId(); ?></td>
                                                    <td><?php echo htmlspecialchars($customerName); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($order->getCreatedAt())); ?></td>
                                                    <td>$<?php echo number_format($order->getTotalAmount(), 2); ?></td>
                                                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($order->getStatus()); ?></span></td>
                                                    <td>
                                                        <a href="/Order/detail/<?php echo $order->getId(); ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                                    <p class="mt-2 mb-0">No orders found</p>
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
