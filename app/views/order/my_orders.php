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
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary"><i class="bi bi-bag"></i> My Orders</h1>
        <a class="btn btn-outline-primary" href="/Product/list">
            <i class="bi bi-shop"></i> Continue Shopping
        </a>
    </div>


            <!-- Error Message -->
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
                unset($_SESSION['error_message']);
            endif; ?>

            <?php if (!empty($orders)): ?>
                <!-- Orders List -->
                <div class="row">
                    <?php foreach ($orders as $order): ?>
                    <div class="col-12">
                        <div class="order-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <h6 class="mb-1">Order #<?php echo $order->getId(); ?></h6>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($order->getCreatedAt())); ?></small>
                                    </div>
                                    <div class="col-md-2">
                                        <?php
                                            $statusClass = 'status-pending';
                                            if ($order->getStatus() == 'processing') {
                                                $statusClass = 'status-processing';
                                            } elseif ($order->getStatus() == 'shipped') {
                                                $statusClass = 'status-shipped';
                                            } elseif ($order->getStatus() == 'delivered') {
                                                $statusClass = 'status-delivered';
                                            } elseif ($order->getStatus() == 'cancelled') {
                                                $statusClass = 'status-cancelled';
                                            }
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($order->getStatus()); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>$<?php echo number_format($order->getTotalAmount(), 2); ?></strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Payment: <?php echo ucwords(str_replace('_', ' ', $order->getPaymentMethod())); ?></small>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="/Order/view/<?php echo $order->getId(); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                        <?php if ($order->getStatus() === 'pending'): ?>
                                        <button class="btn btn-sm btn-outline-danger ms-1" onclick="cancelOrder(<?php echo $order->getId(); ?>)">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                        <?php endif; ?>
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
            <?php else: ?>
                <!-- Empty Orders -->
                <div class="empty-orders">
                    <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No orders yet</h3>
                    <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                    <a href="/Product/list" class="btn btn-primary mt-2">
                        <i class="bi bi-shop"></i> Start Shopping
                    </a>
                </div>
            <?php endif; ?>
    </div>
</div>

<?php include 'app/views/layouts/customer_footer.php'; ?>

<script>
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        // You can implement order cancellation logic here
        alert('Order cancellation feature will be implemented soon.');
    }
}
</script>
