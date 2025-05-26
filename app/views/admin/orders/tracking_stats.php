<?php
require_once 'app/views/layouts/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Tracking Statistics</h2>
                <div>
                    <a href="/Order/exportTracking" class="btn btn-success">
                        <i class="fas fa-download"></i> Export Data
                    </a>
                    <a href="/Order/list" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['total_shipments'] ?></h4>
                                    <p class="mb-0">Total Shipments</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-shipping-fast fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['delivered_count'] ?></h4>
                                    <p class="mb-0">Delivered</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['exception_count'] ?></h4>
                                    <p class="mb-0">Exceptions</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['delivery_rate'] ?>%</h4>
                                    <p class="mb-0">Delivery Rate</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Delivery Time -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Performance Metrics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <h3 class="text-primary"><?= $stats['avg_delivery_days'] ?></h3>
                                        <p class="text-muted">Average Delivery Days</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <h3 class="text-success"><?= $stats['delivery_rate'] ?>%</h3>
                                        <p class="text-muted">Success Rate</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="refreshAllTracking()">
                                    <i class="fas fa-sync"></i> Refresh All Tracking
                                </button>
                                <button class="btn btn-warning" onclick="checkDelayedOrders()">
                                    <i class="fas fa-clock"></i> Check Delayed Orders
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Needing Updates -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Orders Needing Tracking Updates</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($ordersNeedingUpdates)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> All orders have recent tracking updates!
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order Number</th>
                                        <th>Tracking Number</th>
                                        <th>Carrier</th>
                                        <th>Last Update</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ordersNeedingUpdates as $orderData): ?>
                                        <tr>
                                            <td>
                                                <a href="/Order/detail/<?= $orderData['id'] ?>">
                                                    <?= htmlspecialchars($orderData['order_number']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($orderData['tracking_number'] ?: 'Not assigned') ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($orderData['carrier'] ?: 'Not assigned') ?>
                                            </td>
                                            <td>
                                                <span class="text-warning">
                                                    <i class="fas fa-clock"></i> Needs update
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/Order/adminTracking/<?= $orderData['id'] ?>" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-edit"></i> Update
                                                    </a>
                                                    <a href="/Order/simulateTracking/<?= $orderData['id'] ?>" 
                                                       class="btn btn-warning">
                                                        <i class="fas fa-play"></i> Simulate
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bulk Actions -->
                        <div class="mt-3">
                            <button class="btn btn-info" onclick="simulateAllUpdates()">
                                <i class="fas fa-play-circle"></i> Simulate Updates for All
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshAllTracking() {
    if (confirm('This will refresh tracking for all active shipments. Continue?')) {
        // Implementation for bulk refresh
        alert('Feature coming soon!');
    }
}

function checkDelayedOrders() {
    // Implementation for checking delayed orders
    alert('Feature coming soon!');
}

function simulateAllUpdates() {
    if (confirm('This will simulate tracking updates for all orders needing updates. Continue?')) {
        const orders = <?= json_encode(array_column($ordersNeedingUpdates, 'id')) ?>;
        let completed = 0;
        
        orders.forEach(orderId => {
            fetch(`/Order/simulateTracking/${orderId}`, {
                method: 'GET'
            }).then(() => {
                completed++;
                if (completed === orders.length) {
                    alert('All tracking updates simulated successfully!');
                    location.reload();
                }
            }).catch(error => {
                console.error('Error simulating tracking for order:', orderId, error);
                completed++;
                if (completed === orders.length) {
                    alert('Simulation completed with some errors. Please check the console.');
                    location.reload();
                }
            });
        });
    }
}
</script>

<?php
require_once 'app/views/layouts/admin_footer.php';
?>
