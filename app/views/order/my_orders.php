<?php
// Set page variables
$pageTitle = 'My Orders - ShopEasy';
$currentPage = 'my-orders';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'My Orders', 'url' => '']
];

// Include customer header (my orders is only for customers)
include 'app/views/layouts/customer_header.php';

// Get current filters
$currentStatus = $_GET['status'] ?? '';
$currentSearch = $_GET['search'] ?? '';
$currentDateFrom = $_GET['date_from'] ?? '';
$currentDateTo = $_GET['date_to'] ?? '';
$currentPage = $_GET['page'] ?? 1;
?>

<style>
.order-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 1rem;
    transition: box-shadow 0.15s ease-in-out;
}
.order-card:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}
.status-pending {
    background-color: #fff3cd;
    color: #856404;
}
.status-processing {
    background-color: #cce5ff;
    color: #004085;
}
.status-shipped {
    background-color: #d4edda;
    color: #155724;
}
.status-delivered {
    background-color: #d1ecf1;
    color: #0c5460;
}
.status-cancelled {
    background-color: #f8d7da;
    color: #721c24;
}
.empty-orders {
    text-align: center;
    padding: 3rem 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin: 2rem 0;
}
</style>
<div class="container">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="text-primary">
                <i class="bi bi-bag-check"></i> My Orders
            </h1>
            <p class="text-muted">Track and manage your orders</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="/Order/customerDashboard" class="btn btn-outline-primary me-2">
                <i class="bi bi-graph-up"></i> Dashboard
            </a>
            <a href="/Product/list" class="btn btn-primary">
                <i class="bi bi-shop"></i> Continue Shopping
            </a>
        </div>
    </div>

    <!-- Order Statistics -->
    <?php if (isset($orderStats)): ?>
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-primary"><?= $orderStats['total_orders'] ?></h4>
                    <small class="text-muted">Total Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-success">$<?= number_format($orderStats['total_spent'], 2) ?></h4>
                    <small class="text-muted">Total Spent</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-warning"><?= $orderStats['pending_orders'] ?></h4>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-info"><?= $orderStats['processing_orders'] ?></h4>
                    <small class="text-muted">Processing</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-primary"><?= $orderStats['shipped_orders'] ?></h4>
                    <small class="text-muted">Shipped</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-success"><?= $orderStats['delivered_orders'] ?></h4>
                    <small class="text-muted">Delivered</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/Order/myOrders" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $currentStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="processing" <?= $currentStatus === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="shipped" <?= $currentStatus === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="delivered" <?= $currentStatus === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="cancelled" <?= $currentStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                           placeholder="Order number or product..." value="<?= htmlspecialchars($currentSearch) ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from"
                           value="<?= htmlspecialchars($currentDateFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to"
                           value="<?= htmlspecialchars($currentDateTo) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['error_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); endif; ?>

    <?php if (!empty($orders)): ?>
        <!-- Orders List -->
        <div class="row">
            <?php foreach ($orders as $order): ?>
            <div class="col-12 mb-3">
                <div class="order-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <h6 class="mb-1">
                                    <?= htmlspecialchars($order->getOrderNumber() ?: 'Order #' . $order->getId()) ?>
                                </h6>
                                <small class="text-muted"><?= date('M j, Y', strtotime($order->getCreatedAt())) ?></small>
                            </div>
                            <div class="col-md-2">
                                <?php
                                    $statusClass = 'status-pending';
                                    $statusIcon = 'clock';
                                    switch ($order->getStatus()) {
                                        case 'confirmed':
                                            $statusClass = 'status-confirmed';
                                            $statusIcon = 'check-circle';
                                            break;
                                        case 'processing':
                                            $statusClass = 'status-processing';
                                            $statusIcon = 'gear';
                                            break;
                                        case 'shipped':
                                            $statusClass = 'status-shipped';
                                            $statusIcon = 'truck';
                                            break;
                                        case 'delivered':
                                            $statusClass = 'status-delivered';
                                            $statusIcon = 'check-circle-fill';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'status-cancelled';
                                            $statusIcon = 'x-circle';
                                            break;
                                    }
                                ?>
                                <span class="status-badge <?= $statusClass ?>">
                                    <i class="bi bi-<?= $statusIcon ?>"></i>
                                    <?= htmlspecialchars($order->getStatusDisplayName()) ?>
                                </span>
                            </div>
                            <div class="col-md-2">
                                <strong>$<?= number_format($order->getTotalAmount(), 2) ?></strong>
                                <?php if (isset($order->itemCount)): ?>
                                <br><small class="text-muted"><?= $order->itemCount ?> item(s)</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    Payment: <?= ucwords(str_replace('_', ' ', $order->getPaymentMethod())) ?>
                                    <?php if ($order->getTrackingNumber()): ?>
                                    <br>Tracking: <?= htmlspecialchars($order->getTrackingNumber()) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="btn-group" role="group">
                                    <?php if ($order->getTrackingNumber()): ?>
                                    <a href="/Order/tracking/<?= $order->getId() ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-geo-alt"></i> Track
                                    </a>
                                    <?php endif; ?>
                                    <a href="/Order/view/<?= $order->getId() ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Details
                                    </a>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="/Order/downloadInvoice/<?= $order->getId() ?>">
                                                <i class="bi bi-download"></i> Download Invoice
                                            </a></li>
                                            <?php if (in_array($order->getStatus(), ['delivered'])): ?>
                                            <li><a class="dropdown-item" href="/Order/requestReturn/<?= $order->getId() ?>">
                                                <i class="bi bi-arrow-return-left"></i> Request Return
                                            </a></li>
                                            <?php endif; ?>
                                            <li><a class="dropdown-item" href="/Order/reorder/<?= $order->getId() ?>">
                                                <i class="bi bi-arrow-repeat"></i> Reorder
                                            </a></li>
                                            <?php if (in_array($order->getStatus(), ['pending', 'confirmed'])): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="cancelOrder(<?= $order->getId() ?>)">
                                                <i class="bi bi-x-circle"></i> Cancel Order
                                            </a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                                <!-- Order Items Preview -->
                                <?php
                                $order->loadItems();
                                $items = $order->getItems();
                                if (!empty($items)):
                                ?>
                                <hr class="my-3">
                                <div class="row">
                                    <div class="col-12">
                                        <small class="text-muted">Items:</small>
                                        <div class="d-flex flex-wrap mt-1">
                                            <?php
                                            $itemCount = 0;
                                            foreach ($items as $item):
                                                if ($itemCount >= 3) break;
                                                $product = new ProductModel();
                                                $product = $product->findById($item->getProductId());
                                                $itemCount++;
                                            ?>
                                            <span class="badge bg-light text-dark me-2 mb-1">
                                                <?php echo $product ? htmlspecialchars($product->getName()) : 'Product not found'; ?>
                                                (<?php echo $item->getQuantity(); ?>)
                                            </span>
                                            <?php endforeach; ?>
                                            <?php if (count($items) > 3): ?>
                                            <span class="badge bg-secondary">+<?php echo count($items) - 3; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <nav aria-label="Orders pagination">
            <ul class="pagination justify-content-center">
                <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>&status=<?= urlencode($currentStatus) ?>&search=<?= urlencode($currentSearch) ?>&date_from=<?= urlencode($currentDateFrom) ?>&date_to=<?= urlencode($currentDateTo) ?>">
                        Previous
                    </a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($currentStatus) ?>&search=<?= urlencode($currentSearch) ?>&date_from=<?= urlencode($currentDateFrom) ?>&date_to=<?= urlencode($currentDateTo) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>&status=<?= urlencode($currentStatus) ?>&search=<?= urlencode($currentSearch) ?>&date_from=<?= urlencode($currentDateFrom) ?>&date_to=<?= urlencode($currentDateTo) ?>">
                        Next
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Results info -->
        <div class="text-center text-muted mt-3">
            Showing <?= count($orders) ?> of <?= $totalOrders ?? 0 ?> orders
            <?php if (isset($totalPages)): ?>
            (Page <?= $currentPage ?> of <?= $totalPages ?>)
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Empty Orders -->
        <div class="empty-orders text-center">
            <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
            <h3 class="mt-3">No orders found</h3>
            <?php if (!empty($currentStatus) || !empty($currentSearch) || !empty($currentDateFrom) || !empty($currentDateTo)): ?>
            <p class="text-muted">No orders match your current filters. Try adjusting your search criteria.</p>
            <a href="/Order/myOrders" class="btn btn-outline-primary mt-2">
                <i class="bi bi-arrow-clockwise"></i> Clear Filters
            </a>
            <?php else: ?>
            <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here.</p>
            <a href="/Product/list" class="btn btn-primary mt-2">
                <i class="bi bi-shop"></i> Start Shopping
            </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/layouts/customer_footer.php'; ?>

<script>
function cancelOrder(orderId) {
    // Create modal for cancellation reason
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/Order/cancelOrder/${orderId}" method="POST">
                    <div class="modal-body">
                        <p>Are you sure you want to cancel this order?</p>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for cancellation:</label>
                            <select class="form-select" name="reason" required>
                                <option value="">Select a reason</option>
                                <option value="changed_mind">Changed my mind</option>
                                <option value="found_better_price">Found better price elsewhere</option>
                                <option value="ordered_by_mistake">Ordered by mistake</option>
                                <option value="delivery_too_long">Delivery taking too long</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Order</button>
                        <button type="submit" class="btn btn-danger">Cancel Order</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();

    // Remove modal from DOM when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

// Auto-refresh order status every 30 seconds if there are active orders
document.addEventListener('DOMContentLoaded', function() {
    const activeOrders = document.querySelectorAll('.status-badge:not(.status-delivered):not(.status-cancelled)');

    if (activeOrders.length > 0) {
        setInterval(function() {
            // Check for status updates (simplified version)
            console.log('Checking for order updates...');
        }, 30000);
    }
});
</script>
