<?php
require_once 'app/views/layouts/admin_header.php';
?>

<style>
.metric-card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.metric-card:hover {
    transform: translateY(-2px);
}

.metric-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

.chart-container {
    position: relative;
    height: 300px;
}

.activity-item {
    border-left: 3px solid #007bff;
    padding-left: 15px;
    margin-bottom: 15px;
}

.performance-badge {
    font-size: 1.2rem;
    padding: 0.5rem 1rem;
}
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="text-primary">
                <i class="fas fa-chart-line"></i> Order Management Dashboard
            </h1>
            <p class="text-muted">Comprehensive overview of order operations and performance</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar"></i> Last <?= $dateRange ?> days
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?range=7">Last 7 days</a></li>
                    <li><a class="dropdown-item" href="?range=30">Last 30 days</a></li>
                    <li><a class="dropdown-item" href="?range=90">Last 90 days</a></li>
                    <li><a class="dropdown-item" href="?range=365">Last year</a></li>
                </ul>
            </div>
            <a href="/Order/adminManage" class="btn btn-primary ms-2">
                <i class="fas fa-cogs"></i> Manage Orders
            </a>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card metric-card text-center">
                <div class="card-body">
                    <i class="fas fa-shopping-cart metric-icon text-primary"></i>
                    <h3 class="mt-2"><?= number_format($dashboardData['stats']['total_orders']) ?></h3>
                    <p class="text-muted mb-0">Total Orders</p>
                    <small class="text-success">
                        <i class="fas fa-arrow-up"></i> Active: <?= $dashboardData['stats']['processing_orders'] + $dashboardData['stats']['shipped_orders'] ?>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card metric-card text-center">
                <div class="card-body">
                    <i class="fas fa-dollar-sign metric-icon text-success"></i>
                    <h3 class="mt-2">$<?= number_format($dashboardData['stats']['total_revenue'], 2) ?></h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                    <small class="text-info">
                        Avg: $<?= number_format($dashboardData['stats']['avg_order_value'], 2) ?>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card metric-card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle metric-icon text-success"></i>
                    <h3 class="mt-2"><?= number_format($dashboardData['stats']['completion_rate'], 1) ?>%</h3>
                    <p class="text-muted mb-0">Completion Rate</p>
                    <small class="text-success">
                        <?= $dashboardData['stats']['delivered_orders'] ?> delivered
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card metric-card text-center">
                <div class="card-body">
                    <i class="fas fa-clock metric-icon text-warning"></i>
                    <h3 class="mt-2"><?= $dashboardData['performance']['avg_processing_hours'] ?>h</h3>
                    <p class="text-muted mb-0">Avg Processing Time</p>
                    <small class="text-info">
                        <?= $dashboardData['performance']['customer_satisfaction_rate'] ?>% satisfaction
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Order Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="text-center">
                                        <h4 class="text-warning"><?= $dashboardData['stats']['pending_orders'] ?></h4>
                                        <small>Pending</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center">
                                        <h4 class="text-info"><?= $dashboardData['stats']['confirmed_orders'] ?></h4>
                                        <small>Confirmed</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center">
                                        <h4 class="text-primary"><?= $dashboardData['stats']['processing_orders'] ?></h4>
                                        <small>Processing</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="text-center">
                                        <h4 class="text-info"><?= $dashboardData['stats']['shipped_orders'] ?></h4>
                                        <small>Shipped</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <h4 class="text-success"><?= $dashboardData['stats']['delivered_orders'] ?></h4>
                                        <small>Delivered</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <h4 class="text-danger"><?= $dashboardData['stats']['cancelled_orders'] ?></h4>
                                        <small>Cancelled</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tachometer-alt"></i> Performance Metrics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Completion Rate</span>
                            <span class="badge bg-success performance-badge">
                                <?= number_format($dashboardData['stats']['completion_rate'], 1) ?>%
                            </span>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-success" style="width: <?= $dashboardData['stats']['completion_rate'] ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Cancellation Rate</span>
                            <span class="badge bg-danger performance-badge">
                                <?= number_format($dashboardData['stats']['cancellation_rate'], 1) ?>%
                            </span>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-danger" style="width: <?= $dashboardData['stats']['cancellation_rate'] ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Customer Satisfaction</span>
                            <span class="badge bg-info performance-badge">
                                <?= $dashboardData['performance']['customer_satisfaction_rate'] ?>%
                            </span>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-info" style="width: <?= $dashboardData['performance']['customer_satisfaction_rate'] ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Trends -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Revenue Trends
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers and Products -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> Top Customers
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($dashboardData['top_customers'], 0, 5) as $customer): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($customer['name']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($customer['email']) ?></small>
                                    </td>
                                    <td><?= $customer['order_count'] ?></td>
                                    <td>$<?= number_format($customer['total_spent'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        <a href="/Order/customerManagement" class="btn btn-outline-primary btn-sm">
                            View All Customers
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-star"></i> Top Products
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($dashboardData['top_products'], 0, 5) as $product): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                                        <br><small class="text-muted">$<?= number_format($product['price'], 2) ?></small>
                                    </td>
                                    <td><?= $product['total_sold'] ?></td>
                                    <td>$<?= number_format($product['total_revenue'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Activities
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach (array_slice($dashboardData['recent_activities'], 0, 10) as $activity): ?>
                        <div class="col-md-6 mb-3">
                            <div class="activity-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($activity['description']) ?></strong>
                                        <br><small class="text-muted">
                                            Order <?= htmlspecialchars($activity['order_number'] ?: '#' . $activity['order_id']) ?> 
                                            by <?= htmlspecialchars($activity['customer_name']) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">
                                            <?= date('M j, g:i A', strtotime($activity['activity_date'])) ?>
                                        </small>
                                        <br><strong>$<?= number_format($activity['total_amount'], 2) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/layouts/admin_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
        datasets: [{
            data: [
                <?= $dashboardData['stats']['pending_orders'] ?>,
                <?= $dashboardData['stats']['confirmed_orders'] ?>,
                <?= $dashboardData['stats']['processing_orders'] ?>,
                <?= $dashboardData['stats']['shipped_orders'] ?>,
                <?= $dashboardData['stats']['delivered_orders'] ?>,
                <?= $dashboardData['stats']['cancelled_orders'] ?>
            ],
            backgroundColor: [
                '#ffc107',
                '#17a2b8',
                '#007bff',
                '#6f42c1',
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

// Revenue Trends Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueData = <?= json_encode($dashboardData['revenue']['daily']) ?>;

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueData.map(item => item.date),
        datasets: [{
            label: 'Daily Revenue',
            data: revenueData.map(item => item.revenue),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Revenue: $' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
