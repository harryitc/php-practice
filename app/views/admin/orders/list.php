<?php
require_once 'app/models/UserModel.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin</title>
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
        .pagination {
            justify-content: center;
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
                <h1 class="text-primary"><i class="bi bi-list-check"></i> Order Management</h1>
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
                                    <a class="nav-link active" href="/Order/list"><i class="bi bi-list-check"></i> Orders</a>
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

            <!-- Order Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Filter Orders</h5>
                        </div>
                        <div class="card-body">
                            <form action="/Order/list" method="get" class="row g-3">
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo $status === $selectedStatus ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($status); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-filter"></i> Apply Filters
                                    </button>
                                    <?php if (!empty($selectedStatus)): ?>
                                        <a href="<?php echo $this->removeQueryParam('status'); ?>" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Clear Filters
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders List -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Orders</h5>
                                <span class="badge bg-primary"><?php echo $totalOrders; ?> Orders</span>
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
                                        <?php if (count($orders) > 0): ?>
                                            <?php foreach ($orders as $order): ?>
                                                <?php
                                                    // Get customer name
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

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo $this->buildPaginationUrl(1); ?>" aria-label="First">
                                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo $this->buildPaginationUrl($currentPage - 1); ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php
                                        // Calculate range of page numbers to display
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $startPage + 4);

                                        if ($endPage - $startPage < 4) {
                                            $startPage = max(1, $endPage - 4);
                                        }

                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                                <a class="page-link" href="<?php echo $this->buildPaginationUrl($i); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo $this->buildPaginationUrl($currentPage + 1); ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo $this->buildPaginationUrl($totalPages); ?>" aria-label="Last">
                                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
