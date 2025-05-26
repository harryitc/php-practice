<?php
require_once 'app/views/layouts/admin_header.php';
?>

<style>
.customer-card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.customer-card:hover {
    transform: translateY(-2px);
}

.customer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
}

.loyalty-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.customer-stats {
    background: #f8f9fa;
    border-radius: 0.375rem;
    padding: 1rem;
}

.stat-item {
    text-align: center;
    padding: 0.5rem;
}

.customer-tier {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
}
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="text-primary">
                <i class="fas fa-users"></i> Customer Management
            </h1>
            <p class="text-muted">Manage customers and analyze their order behavior</p>
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

    <!-- Customer Overview Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card customer-card text-center">
                <div class="card-body">
                    <i class="fas fa-users text-primary" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?= count($topCustomers) ?></h3>
                    <p class="text-muted mb-0">Active Customers</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card customer-card text-center">
                <div class="card-body">
                    <i class="fas fa-crown text-warning" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?= count(array_filter($topCustomers, function($c) { return $c['total_spent'] > 1000; })) ?></h3>
                    <p class="text-muted mb-0">VIP Customers</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card customer-card text-center">
                <div class="card-body">
                    <i class="fas fa-shopping-cart text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?= !empty($topCustomers) ? number_format(array_sum(array_column($topCustomers, 'order_count')) / count($topCustomers), 1) : 0 ?></h3>
                    <p class="text-muted mb-0">Avg Orders per Customer</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card customer-card text-center">
                <div class="card-body">
                    <i class="fas fa-dollar-sign text-info" style="font-size: 2rem;"></i>
                    <h3 class="mt-2">$<?= !empty($topCustomers) ? number_format(array_sum(array_column($topCustomers, 'total_spent')) / count($topCustomers), 0) : 0 ?></h3>
                    <p class="text-muted mb-0">Avg Customer Value</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers Grid -->
    <div class="row">
        <?php foreach ($topCustomers as $index => $customer): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card customer-card position-relative">
                <!-- Loyalty Badge -->
                <?php
                $tier = 'Bronze';
                $tierColor = 'secondary';
                if ($customer['total_spent'] > 2000) {
                    $tier = 'Platinum';
                    $tierColor = 'dark';
                } elseif ($customer['total_spent'] > 1000) {
                    $tier = 'Gold';
                    $tierColor = 'warning';
                } elseif ($customer['total_spent'] > 500) {
                    $tier = 'Silver';
                    $tierColor = 'light';
                }
                ?>
                <span class="badge bg-<?= $tierColor ?> loyalty-badge customer-tier"><?= $tier ?></span>
                
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="customer-avatar me-3">
                            <?= strtoupper(substr($customer['name'], 0, 2)) ?>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($customer['name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($customer['email']) ?></small>
                            <?php if ($index < 3): ?>
                            <br><small class="text-warning">
                                <i class="fas fa-medal"></i> Top <?= $index + 1 ?> Customer
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="customer-stats mb-3">
                        <div class="row">
                            <div class="col-4 stat-item">
                                <h6 class="text-primary mb-1"><?= $customer['order_count'] ?></h6>
                                <small class="text-muted">Orders</small>
                            </div>
                            <div class="col-4 stat-item">
                                <h6 class="text-success mb-1">$<?= number_format($customer['total_spent'], 0) ?></h6>
                                <small class="text-muted">Total Spent</small>
                            </div>
                            <div class="col-4 stat-item">
                                <h6 class="text-info mb-1">$<?= number_format($customer['avg_order_value'], 0) ?></h6>
                                <small class="text-muted">Avg Order</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Last Order:</small>
                        <br><strong><?= date('M j, Y', strtotime($customer['last_order_date'])) ?></strong>
                        <small class="text-muted">(<?= date('g:i A', strtotime($customer['last_order_date'])) ?>)</small>
                    </div>

                    <!-- Customer Actions -->
                    <div class="d-flex justify-content-between">
                        <div class="btn-group btn-group-sm">
                            <a href="/Order/adminManage?customer_id=<?= $customer['id'] ?>" class="btn btn-outline-primary" title="View Orders">
                                <i class="fas fa-shopping-cart"></i>
                            </a>
                            <button class="btn btn-outline-info" onclick="viewCustomerDetails(<?= $customer['id'] ?>)" title="Customer Details">
                                <i class="fas fa-user"></i>
                            </button>
                            <button class="btn btn-outline-success" onclick="sendCustomerEmail(<?= $customer['id'] ?>)" title="Send Email">
                                <i class="fas fa-envelope"></i>
                            </button>
                        </div>
                        
                        <!-- Customer Score -->
                        <div class="text-end">
                            <?php
                            $score = min(100, ($customer['order_count'] * 10) + ($customer['total_spent'] / 50));
                            $scoreColor = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'secondary');
                            ?>
                            <small class="text-muted">Customer Score</small>
                            <br><span class="badge bg-<?= $scoreColor ?>"><?= number_format($score, 0) ?>/100</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Customer Analytics -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card customer-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Customer Distribution by Tier
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="customerTierChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card customer-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Customer Value Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="customerValueChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Customer Table -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card customer-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table"></i> Detailed Customer Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Customer</th>
                                    <th>Tier</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                    <th>Avg Order Value</th>
                                    <th>Last Order</th>
                                    <th>Customer Score</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topCustomers as $index => $customer): ?>
                                <tr>
                                    <td>
                                        <?php if ($index < 3): ?>
                                        <i class="fas fa-medal text-<?= $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'warning') ?>"></i>
                                        <?php endif; ?>
                                        #<?= $index + 1 ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="customer-avatar me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                <?= strtoupper(substr($customer['name'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($customer['name']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($customer['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $tier = 'Bronze';
                                        $tierColor = 'secondary';
                                        if ($customer['total_spent'] > 2000) {
                                            $tier = 'Platinum';
                                            $tierColor = 'dark';
                                        } elseif ($customer['total_spent'] > 1000) {
                                            $tier = 'Gold';
                                            $tierColor = 'warning';
                                        } elseif ($customer['total_spent'] > 500) {
                                            $tier = 'Silver';
                                            $tierColor = 'light';
                                        }
                                        ?>
                                        <span class="badge bg-<?= $tierColor ?> customer-tier"><?= $tier ?></span>
                                    </td>
                                    <td><?= $customer['order_count'] ?></td>
                                    <td>$<?= number_format($customer['total_spent'], 2) ?></td>
                                    <td>$<?= number_format($customer['avg_order_value'], 2) ?></td>
                                    <td><?= date('M j, Y', strtotime($customer['last_order_date'])) ?></td>
                                    <td>
                                        <?php
                                        $score = min(100, ($customer['order_count'] * 10) + ($customer['total_spent'] / 50));
                                        $scoreColor = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'secondary');
                                        ?>
                                        <span class="badge bg-<?= $scoreColor ?>"><?= number_format($score, 0) ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/Order/adminManage?customer_id=<?= $customer['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-shopping-cart"></i>
                                            </a>
                                            <button class="btn btn-outline-info btn-sm" onclick="viewCustomerDetails(<?= $customer['id'] ?>)">
                                                <i class="fas fa-user"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/layouts/admin_footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Customer Tier Distribution Chart
const tierCtx = document.getElementById('customerTierChart').getContext('2d');
const customers = <?= json_encode($topCustomers) ?>;

const tierCounts = {
    'Bronze': 0,
    'Silver': 0,
    'Gold': 0,
    'Platinum': 0
};

customers.forEach(customer => {
    if (customer.total_spent > 2000) {
        tierCounts.Platinum++;
    } else if (customer.total_spent > 1000) {
        tierCounts.Gold++;
    } else if (customer.total_spent > 500) {
        tierCounts.Silver++;
    } else {
        tierCounts.Bronze++;
    }
});

new Chart(tierCtx, {
    type: 'doughnut',
    data: {
        labels: ['Bronze', 'Silver', 'Gold', 'Platinum'],
        datasets: [{
            data: [tierCounts.Bronze, tierCounts.Silver, tierCounts.Gold, tierCounts.Platinum],
            backgroundColor: ['#6c757d', '#e9ecef', '#ffc107', '#343a40']
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

// Customer Value Distribution Chart
const valueCtx = document.getElementById('customerValueChart').getContext('2d');
const valueRanges = {
    '$0-$100': 0,
    '$100-$500': 0,
    '$500-$1000': 0,
    '$1000-$2000': 0,
    '$2000+': 0
};

customers.forEach(customer => {
    const spent = customer.total_spent;
    if (spent >= 2000) {
        valueRanges['$2000+']++;
    } else if (spent >= 1000) {
        valueRanges['$1000-$2000']++;
    } else if (spent >= 500) {
        valueRanges['$500-$1000']++;
    } else if (spent >= 100) {
        valueRanges['$100-$500']++;
    } else {
        valueRanges['$0-$100']++;
    }
});

new Chart(valueCtx, {
    type: 'bar',
    data: {
        labels: Object.keys(valueRanges),
        datasets: [{
            label: 'Number of Customers',
            data: Object.values(valueRanges),
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
                beginAtZero: true
            }
        }
    }
});

function viewCustomerDetails(customerId) {
    // Implementation for viewing customer details
    alert('Customer details functionality will be implemented');
}

function sendCustomerEmail(customerId) {
    // Implementation for sending customer email
    alert('Email functionality will be implemented');
}
</script>
