<?php
// Set page variables
$pageTitle = 'Order Dashboard - ShopEasy';
$currentPage = 'order-dashboard';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'My Orders', 'url' => '/Order/myOrders'],
    ['title' => 'Dashboard', 'url' => '']
];

// Include customer header
include 'app/views/layouts/customer_header.php';
?>

<style>
.dashboard-card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.dashboard-card:hover {
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 2rem;
    opacity: 0.8;
}

.chart-container {
    position: relative;
    height: 300px;
}

.recent-order {
    border-left: 4px solid #007bff;
    padding-left: 15px;
    margin-bottom: 15px;
}

.spending-chart {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
</style>

<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="text-primary">
                <i class="bi bi-graph-up"></i> Order Dashboard
            </h1>
            <p class="text-muted">Overview of your shopping activity</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="/Order/myOrders" class="btn btn-outline-primary">
                <i class="bi bi-list-ul"></i> View All Orders
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card dashboard-card text-center">
                <div class="card-body">
                    <i class="bi bi-bag-check stat-icon text-primary"></i>
                    <h3 class="mt-2"><?= $dashboardData['stats']['total_orders'] ?></h3>
                    <p class="text-muted mb-0">Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card text-center">
                <div class="card-body">
                    <i class="bi bi-currency-dollar stat-icon text-success"></i>
                    <h3 class="mt-2">$<?= number_format($dashboardData['stats']['total_spent'], 2) ?></h3>
                    <p class="text-muted mb-0">Total Spent</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card text-center">
                <div class="card-body">
                    <i class="bi bi-graph-up stat-icon text-info"></i>
                    <h3 class="mt-2">$<?= number_format($dashboardData['stats']['avg_order_value'], 2) ?></h3>
                    <p class="text-muted mb-0">Avg Order Value</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card text-center">
                <div class="card-body">
                    <i class="bi bi-truck stat-icon text-warning"></i>
                    <h3 class="mt-2"><?= $dashboardData['stats']['shipped_orders'] + $dashboardData['stats']['processing_orders'] ?></h3>
                    <p class="text-muted mb-0">Active Orders</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Status Chart -->
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart"></i> Orders by Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Spending -->
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card spending-chart">
                <div class="card-header border-0">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-bar-chart"></i> Monthly Spending
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="spendingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row">
        <div class="col-md-8">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Recent Orders
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($dashboardData['recent_orders'])): ?>
                        <?php foreach ($dashboardData['recent_orders'] as $order): ?>
                        <div class="recent-order">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <?= htmlspecialchars($order->getOrderNumber() ?: 'Order #' . $order->getId()) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?= date('M j, Y', strtotime($order->getCreatedAt())) ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= $order->getStatus() === 'delivered' ? 'success' : ($order->getStatus() === 'cancelled' ? 'danger' : 'primary') ?>">
                                        <?= htmlspecialchars($order->getStatusDisplayName()) ?>
                                    </span>
                                    <div class="mt-1">
                                        <strong>$<?= number_format($order->getTotalAmount(), 2) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="/Order/view/<?= $order->getId() ?>" class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                                <?php if ($order->getTrackingNumber()): ?>
                                <a href="/Order/tracking/<?= $order->getId() ?>" class="btn btn-sm btn-outline-success">
                                    Track Order
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="/Order/myOrders" class="btn btn-primary">
                                View All Orders
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-bag-x text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">No orders yet</h5>
                            <p class="text-muted">Start shopping to see your orders here</p>
                            <a href="/Product/list" class="btn btn-primary">
                                <i class="bi bi-shop"></i> Start Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/Product/list" class="btn btn-primary">
                            <i class="bi bi-shop"></i> Continue Shopping
                        </a>
                        <a href="/Order/myOrders?status=shipped" class="btn btn-outline-info">
                            <i class="bi bi-truck"></i> Track Shipments
                        </a>
                        <a href="/Order/myOrders?status=delivered" class="btn btn-outline-success">
                            <i class="bi bi-star"></i> Review Orders
                        </a>
                        <a href="/Cart/view" class="btn btn-outline-warning">
                            <i class="bi bi-cart"></i> View Cart
                        </a>
                    </div>
                </div>
            </div>

            <!-- Order Status Summary -->
            <div class="card dashboard-card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> Status Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="text-warning">
                                <i class="bi bi-clock"></i>
                                <div class="mt-1">
                                    <strong><?= $dashboardData['stats']['pending_orders'] ?></strong>
                                    <br><small>Pending</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-info">
                                <i class="bi bi-gear"></i>
                                <div class="mt-1">
                                    <strong><?= $dashboardData['stats']['processing_orders'] ?></strong>
                                    <br><small>Processing</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-primary">
                                <i class="bi bi-truck"></i>
                                <div class="mt-1">
                                    <strong><?= $dashboardData['stats']['shipped_orders'] ?></strong>
                                    <br><small>Shipped</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-success">
                                <i class="bi bi-check-circle"></i>
                                <div class="mt-1">
                                    <strong><?= $dashboardData['stats']['delivered_orders'] ?></strong>
                                    <br><small>Delivered</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/customer_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Order Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusData = <?= json_encode($dashboardData['status_data']) ?>;

new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
        datasets: [{
            data: [
                statusData.pending,
                statusData.processing,
                statusData.shipped,
                statusData.delivered,
                statusData.cancelled
            ],
            backgroundColor: [
                '#ffc107',
                '#17a2b8',
                '#007bff',
                '#28a745',
                '#dc3545'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Monthly Spending Chart
const spendingCtx = document.getElementById('spendingChart').getContext('2d');
const monthlyData = <?= json_encode($dashboardData['monthly_spending']) ?>;

const months = Object.keys(monthlyData).reverse();
const amounts = Object.values(monthlyData).reverse();

new Chart(spendingCtx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Monthly Spending',
            data: amounts,
            borderColor: 'rgba(255, 255, 255, 0.8)',
            backgroundColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: 'white'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: 'white'
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                }
            },
            y: {
                ticks: {
                    color: 'white',
                    callback: function(value) {
                        return '$' + value;
                    }
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                }
            }
        }
    }
});
</script>
