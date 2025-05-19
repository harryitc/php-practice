<?php
require_once 'app/models/UserModel.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Statistics - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <h1 class="text-primary"><i class="bi bi-graph-up"></i> Revenue Statistics</h1>
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
                                    <a class="nav-link" href="/Order/customers"><i class="bi bi-people"></i> Customers</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active" href="/Order/revenue"><i class="bi bi-graph-up"></i> Revenue</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
