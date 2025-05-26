<?php
require_once 'app/models/UserModel.php';

// Set page variables
$pageTitle = 'Revenue Statistics';
$currentPage = 'revenue';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/Order/dashboard'],
    ['title' => 'Revenue', 'url' => '/Order/revenue']
];

// Include admin header
include 'app/views/layouts/admin_header.php';
?>

<style>
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
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <!-- Revenue Overview -->
            <div class="row mb-4">
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

            <!-- Revenue Charts -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Monthly Revenue</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Revenue by Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Analysis -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Revenue Analysis</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-4">
                                The revenue statistics provide insights into your business performance. Here's a summary of the key findings:
                            </p>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold"><i class="bi bi-check-circle text-success"></i> Revenue Highlights</h6>
                                    <ul class="list-unstyled ps-4">
                                        <li><i class="bi bi-dot"></i> Total revenue to date: <strong>$<?php echo number_format($totalRevenue, 2); ?></strong></li>
                                        <li><i class="bi bi-dot"></i> Current month revenue: <strong>$<?php echo number_format($monthlyRevenue, 2); ?></strong></li>
                                        <li><i class="bi bi-dot"></i> Today's revenue: <strong>$<?php echo number_format($dailyRevenue, 2); ?></strong></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold"><i class="bi bi-lightbulb text-warning"></i> Recommendations</h6>
                                    <ul class="list-unstyled ps-4">
                                        <li><i class="bi bi-dot"></i> Monitor daily revenue trends</li>
                                        <li><i class="bi bi-dot"></i> Analyze customer purchasing patterns</li>
                                        <li><i class="bi bi-dot"></i> Identify top-selling products</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php include 'app/views/layouts/admin_footer.php'; ?>

<script>
        // Sample data for charts
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Revenue Chart
            const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
            const monthlyRevenueChart = new Chart(monthlyRevenueCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Monthly Revenue',
                        data: [1200, 1900, 3000, 5000, 2000, 3000, 4500, 3800, 4200, 5500, 6500, <?php echo $monthlyRevenue; ?>],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });

            // Revenue by Status Chart
            const statusRevenueCtx = document.getElementById('statusRevenueChart').getContext('2d');
            const statusRevenueChart = new Chart(statusRevenueCtx, {
                type: 'pie',
                data: {
                    labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
                    datasets: [{
                        label: 'Revenue by Status',
                        data: [1500, 3000, 4500, <?php echo $totalRevenue - 9500; ?>, 500],
                        backgroundColor: [
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(40, 167, 69, 0.7)',
                            'rgba(220, 53, 69, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 206, 86, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += '$' + context.raw;
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
