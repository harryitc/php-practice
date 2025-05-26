<?php
require_once 'app/views/layouts/admin_header.php';
?>

<style>
.analytics-card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.analytics-card:hover {
    transform: translateY(-2px);
}

.metric-value {
    font-size: 2rem;
    font-weight: bold;
}

.chart-container {
    position: relative;
    height: 400px;
}

.trend-indicator {
    font-size: 0.875rem;
}

.kpi-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.performance-meter {
    height: 20px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.performance-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #ffc107, #dc3545);
    transition: width 0.3s ease;
}
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="text-primary">
                <i class="fas fa-chart-bar"></i> Order Analytics
            </h1>
            <p class="text-muted">Comprehensive analytics and insights for order management</p>
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

    <!-- KPI Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card analytics-card kpi-card text-center">
                <div class="card-body">
                    <h3 class="metric-value"><?= number_format($analyticsData['stats']['total_orders']) ?></h3>
                    <p class="mb-0">Total Orders</p>
                    <small class="trend-indicator">
                        <i class="fas fa-arrow-up"></i> +12% vs last period
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card analytics-card text-center">
                <div class="card-body">
                    <h3 class="metric-value text-success">$<?= number_format($analyticsData['stats']['total_revenue'], 0) ?></h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                    <small class="trend-indicator text-success">
                        <i class="fas fa-arrow-up"></i> +8% vs last period
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card analytics-card text-center">
                <div class="card-body">
                    <h3 class="metric-value text-info">$<?= number_format($analyticsData['stats']['avg_order_value'], 2) ?></h3>
                    <p class="text-muted mb-0">Avg Order Value</p>
                    <small class="trend-indicator text-info">
                        <i class="fas fa-arrow-up"></i> +3% vs last period
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card analytics-card text-center">
                <div class="card-body">
                    <h3 class="metric-value text-warning"><?= number_format($analyticsData['stats']['completion_rate'], 1) ?>%</h3>
                    <p class="text-muted mb-0">Completion Rate</p>
                    <small class="trend-indicator text-success">
                        <i class="fas fa-arrow-up"></i> +2% vs last period
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue and Order Trends -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card analytics-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Revenue & Order Trends
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card analytics-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-pie-chart"></i> Order Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card analytics-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tachometer-alt"></i> Performance Metrics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Order Fulfillment Rate</span>
                            <span class="fw-bold"><?= number_format($analyticsData['stats']['completion_rate'], 1) ?>%</span>
                        </div>
                        <div class="performance-meter">
                            <div class="performance-fill" style="width: <?= $analyticsData['stats']['completion_rate'] ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Customer Satisfaction</span>
                            <span class="fw-bold"><?= $analyticsData['performance']['customer_satisfaction_rate'] ?>%</span>
                        </div>
                        <div class="performance-meter">
                            <div class="performance-fill" style="width: <?= $analyticsData['performance']['customer_satisfaction_rate'] ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Processing Efficiency</span>
                            <span class="fw-bold">85%</span>
                        </div>
                        <div class="performance-meter">
                            <div class="performance-fill" style="width: 85%"></div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary"><?= $analyticsData['performance']['avg_processing_hours'] ?>h</h4>
                            <small class="text-muted">Avg Processing Time</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success"><?= number_format($analyticsData['stats']['cancellation_rate'], 1) ?>%</h4>
                            <small class="text-muted">Cancellation Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card analytics-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area"></i> Monthly Revenue Comparison
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card analytics-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-crown"></i> Top Customers
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Customer</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($analyticsData['top_customers'], 0, 10) as $index => $customer): ?>
                                <tr>
                                    <td>
                                        <?php if ($index < 3): ?>
                                        <i class="fas fa-medal text-<?= $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'warning') ?>"></i>
                                        <?php endif; ?>
                                        #<?= $index + 1 ?>
                                    </td>
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
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card analytics-card">
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
                                    <th>Rank</th>
                                    <th>Product</th>
                                    <th>Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($analyticsData['top_products'], 0, 10) as $index => $product): ?>
                                <tr>
                                    <td>
                                        <?php if ($index < 3): ?>
                                        <i class="fas fa-medal text-<?= $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'warning') ?>"></i>
                                        <?php endif; ?>
                                        #<?= $index + 1 ?>
                                    </td>
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

    <!-- Order Trends Analysis -->
    <div class="row">
        <div class="col-md-12">
            <div class="card analytics-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Detailed Order Trends Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="detailedTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/layouts/admin_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue & Order Trends Chart
const trendsCtx = document.getElementById('trendsChart').getContext('2d');
const trendsData = <?= json_encode($analyticsData['trends']) ?>;

new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: trendsData.map(item => item.date),
        datasets: [{
            label: 'Daily Revenue',
            data: trendsData.map(item => item.avg_value * item.total_orders),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            yAxisID: 'y',
            tension: 0.4
        }, {
            label: 'Order Count',
            data: trendsData.map(item => item.total_orders),
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            yAxisID: 'y1',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue ($)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Order Count'
                },
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
        datasets: [{
            data: [
                <?= $analyticsData['stats']['pending_orders'] ?>,
                <?= $analyticsData['stats']['confirmed_orders'] ?>,
                <?= $analyticsData['stats']['processing_orders'] ?>,
                <?= $analyticsData['stats']['shipped_orders'] ?>,
                <?= $analyticsData['stats']['delivered_orders'] ?>,
                <?= $analyticsData['stats']['cancelled_orders'] ?>
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

// Monthly Revenue Chart
const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
const monthlyData = <?= json_encode($analyticsData['revenue']['monthly']) ?>;

new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(item => item.month),
        datasets: [{
            label: 'Monthly Revenue',
            data: monthlyData.map(item => item.revenue),
            backgroundColor: 'rgba(0, 123, 255, 0.8)',
            borderColor: '#007bff',
            borderWidth: 1
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
        }
    }
});

// Detailed Trends Chart
const detailedCtx = document.getElementById('detailedTrendsChart').getContext('2d');
new Chart(detailedCtx, {
    type: 'line',
    data: {
        labels: trendsData.map(item => item.date),
        datasets: [{
            label: 'Total Orders',
            data: trendsData.map(item => item.total_orders),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            fill: true,
            tension: 0.4
        }, {
            label: 'Delivered Orders',
            data: trendsData.map(item => item.delivered),
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            fill: true,
            tension: 0.4
        }, {
            label: 'Cancelled Orders',
            data: trendsData.map(item => item.cancelled),
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
