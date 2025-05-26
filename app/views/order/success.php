<?php
// Set page variables
$pageTitle = 'Order Success - ShopEasy';
$currentPage = 'order-success';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'Cart', 'url' => '/Cart'],
    ['title' => 'Order Success', 'url' => '']
];

// Include customer header (order success is only for customers)
include 'app/views/layouts/customer_header.php';
?>

<style>
.success-container {
    text-align: center;
    padding: 3rem 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin: 2rem 0;
}
.success-icon {
    font-size: 4rem;
    color: #28a745;
    margin-bottom: 1rem;
}
.order-details {
    background-color: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-top: 2rem;
}
</style>
<div class="container">

            <!-- Success Container -->
            <div class="success-container">
                <i class="bi bi-check-circle-fill success-icon"></i>
                <h1 class="text-success mb-3">Order Placed Successfully!</h1>
                <p class="lead">Thank you for your order. We've received your order and will process it shortly.</p>

                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="order-details">
                            <h4 class="mb-3"><i class="bi bi-receipt"></i> Order Details</h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Order ID:</strong> #<?php echo $order->getId(); ?></p>
                                    <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order->getCreatedAt())); ?></p>
                                    <p><strong>Status:</strong> <span class="badge bg-warning text-dark"><?php echo ucfirst($order->getStatus()); ?></span></p>
                                    <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order->getPaymentMethod())); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total Amount:</strong> $<?php echo number_format($order->getTotalAmount(), 2); ?></p>
                                    <p><strong>Shipping Address:</strong><br>
                                    <?php echo htmlspecialchars($order->getShippingAddress()); ?><br>
                                    <?php echo htmlspecialchars($order->getShippingCity()); ?>, <?php echo htmlspecialchars($order->getShippingState()); ?> <?php echo htmlspecialchars($order->getShippingZip()); ?><br>
                                    <?php echo htmlspecialchars($order->getShippingCountry()); ?></p>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <hr>
                            <h5 class="mb-3">Order Items</h5>
                            <?php
                            $order->loadItems();
                            $items = $order->getItems();
                            ?>
                            <?php if (!empty($items)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                                <?php
                                                $product = new ProductModel();
                                                $product = $product->findById($item->getProductId());
                                                ?>
                                                <tr>
                                                    <td><?php echo $product ? htmlspecialchars($product->getName()) : 'Product not found'; ?></td>
                                                    <td><?php echo $item->getQuantity(); ?></td>
                                                    <td>$<?php echo number_format($item->getPrice(), 2); ?></td>
                                                    <td>$<?php echo number_format($item->getPrice() * $item->getQuantity(), 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3">Total:</th>
                                                <th>$<?php echo number_format($order->getTotalAmount(), 2); ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4">
                    <a href="/Order/myOrders" class="btn btn-primary me-2">
                        <i class="bi bi-bag"></i> View My Orders
                    </a>
                    <a href="/Product/list" class="btn btn-outline-primary">
                        <i class="bi bi-shop"></i> Continue Shopping
                    </a>
                </div>

                <!-- Payment Instructions -->
                <?php if ($order->getPaymentMethod() === 'bank_transfer'): ?>
                <div class="alert alert-info mt-4">
                    <h6><i class="bi bi-info-circle"></i> Bank Transfer Instructions</h6>
                    <p class="mb-0">Please transfer the amount to our bank account:</p>
                    <p class="mb-0"><strong>Bank:</strong> ABC Bank</p>
                    <p class="mb-0"><strong>Account Number:</strong> 1234567890</p>
                    <p class="mb-0"><strong>Account Name:</strong> E-commerce Store</p>
                    <p class="mb-0"><strong>Reference:</strong> Order #<?php echo $order->getId(); ?></p>
                </div>
                <?php elseif ($order->getPaymentMethod() === 'cod'): ?>
                <div class="alert alert-info mt-4">
                    <h6><i class="bi bi-info-circle"></i> Cash on Delivery</h6>
                    <p class="mb-0">Please have the exact amount ready when your order arrives.</p>
                    <p class="mb-0">Our delivery person will collect $<?php echo number_format($order->getTotalAmount(), 2); ?> upon delivery.</p>
                </div>
                <?php endif; ?>

                <!-- Contact Information -->
                <div class="mt-4">
                    <small class="text-muted">
                        <i class="bi bi-envelope"></i> Questions about your order? Contact us at support@ecommerce.com
                    </small>
                </div>
            </div>
    </div>
</div>

<?php include 'app/views/layouts/customer_footer.php'; ?>
