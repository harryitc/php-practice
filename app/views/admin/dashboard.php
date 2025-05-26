<?php
// Set page variables
$pageTitle = 'Admin Dashboard';
$currentPage = 'dashboard';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/Order/dashboard']
];

// Include admin header
include 'app/views/layouts/admin_header.php';
?>
<!-- Dashboard Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">Total Orders</h6>
                        <h2 class="mb-0"><?php echo number_format($totalOrders); ?></h2>
                    </div>
                    <div class="text-white-50">
                        <i class="bi bi-bag-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">Total Revenue</h6>
                        <h2 class="mb-0">$<?php echo number_format($totalRevenue, 2); ?></h2>
                    </div>
                    <div class="text-white-50">
                        <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">Total Customers</h6>
                        <h2 class="mb-0"><?php echo number_format($totalCustomers); ?></h2>
                    </div>
                    <div class="text-white-50">
                        <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-1">Pending Orders</h6>
                        <h2 class="mb-0"><?php echo number_format($pendingOrders); ?></h2>
                    </div>
                    <div class="text-white-50">
                        <i class="bi bi-clock-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Status Overview -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart me-2"></i>Order Status Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2">
                        <div class="mb-3">
                            <h4 class="text-warning"><?php echo $pendingOrders; ?></h4>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <h4 class="text-info"><?php echo $processingOrders; ?></h4>
                            <small class="text-muted">Processing</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <h4 class="text-primary"><?php echo $shippedOrders; ?></h4>
                            <small class="text-muted">Shipped</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <h4 class="text-success"><?php echo $deliveredOrders; ?></h4>
                            <small class="text-muted">Delivered</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <h4 class="text-danger"><?php echo $cancelledOrders; ?></h4>
                            <small class="text-muted">Cancelled</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>Revenue Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Today</span>
                        <strong>$<?php echo number_format($dailyRevenue, 2); ?></strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>This Month</span>
                        <strong>$<?php echo number_format($monthlyRevenue, 2); ?></strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Total</span>
                        <strong class="text-success">$<?php echo number_format($totalRevenue, 2); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>Recent Orders
                </h5>
                <a href="/Order/list" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-eye me-1"></i>View All
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentOrders)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <?php
                            $customer = new UserModel();
                            $customer = $customer->findById($order->getUserId());
                            ?>
                            <tr>
                                <td><strong>#<?php echo $order->getId(); ?></strong></td>
                                <td><?php echo $customer ? htmlspecialchars($customer->getName()) : 'Unknown'; ?></td>
                                <td><strong>$<?php echo number_format($order->getTotalAmount(), 2); ?></strong></td>
                                <td>
                                    <?php
                                    $statusClass = 'secondary';
                                    switch($order->getStatus()) {
                                        case 'pending': $statusClass = 'warning'; break;
                                        case 'processing': $statusClass = 'info'; break;
                                        case 'shipped': $statusClass = 'primary'; break;
                                        case 'delivered': $statusClass = 'success'; break;
                                        case 'cancelled': $statusClass = 'danger'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <?php echo ucfirst($order->getStatus()); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order->getCreatedAt())); ?></td>
                                <td>
                                    <a href="/Order/detail/<?php echo $order->getId(); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No orders yet</h5>
                    <p class="text-muted">Orders will appear here once customers start placing them.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/admin_footer.php'; ?>
