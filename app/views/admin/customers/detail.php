<?php
require_once 'app/models/UserModel.php';

// Set page variables
$pageTitle = 'Customer Details';
$currentPage = 'customers';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/Order/dashboard'],
    ['title' => 'Customers', 'url' => '/Order/customers'],
    ['title' => $customer->getName(), 'url' => '']
];
$pageActions = '<a href="/Order/customers" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-2"></i>Back to Customers</a>';

// Include admin header
include 'app/views/layouts/admin_header.php';
?>

            <!-- Customer Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 40%">ID:</th>
                                            <td><?php echo $customer->getId(); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Name:</th>
                                            <td><?php echo htmlspecialchars($customer->getName()); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?php echo htmlspecialchars($customer->getEmail()); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 40%">Role:</th>
                                            <td>
                                                <?php if ($customer->getRole() === 'admin'): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info text-dark">Customer</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Registered:</th>
                                            <td><?php echo date('F d, Y', strtotime($customer->getCreatedAt())); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated:</th>
                                            <td><?php echo date('F d, Y', strtotime($customer->getUpdatedAt())); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Customer Orders</h5>
                                <span class="badge bg-primary"><?php echo count($orders); ?> Orders</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
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
                                                <td colspan="5" class="text-center py-4">
                                                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                                    <p class="mt-2 mb-0">No orders found for this customer</p>
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

<?php include 'app/views/layouts/admin_footer.php'; ?>
