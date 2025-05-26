<?php
// Set page variables
$pageTitle = 'Cancel Order - ShopEasy';
$currentPage = 'cancel-order';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'My Orders', 'url' => '/Order/myOrders'],
    ['title' => 'Cancel Order', 'url' => '']
];

// Include customer header
include 'app/views/layouts/customer_header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-x-circle"></i> Cancel Order
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Order Information -->
                    <div class="alert alert-info">
                        <h5>Order Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Order Number:</strong> <?= htmlspecialchars($order->getOrderNumber() ?: 'Order #' . $order->getId()) ?><br>
                                <strong>Order Date:</strong> <?= date('M j, Y', strtotime($order->getCreatedAt())) ?><br>
                                <strong>Total Amount:</strong> $<?= number_format($order->getTotalAmount(), 2) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong> <?= htmlspecialchars($order->getStatusDisplayName()) ?><br>
                                <strong>Payment Method:</strong> <?= ucwords(str_replace('_', ' ', $order->getPaymentMethod())) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation Form -->
                    <form method="POST" action="/Order/cancelOrder/<?= $order->getId() ?>">
                        <div class="mb-4">
                            <label for="reason" class="form-label">
                                <strong>Reason for Cancellation <span class="text-danger">*</span></strong>
                            </label>
                            <select class="form-select" id="reason" name="reason" required>
                                <option value="">Please select a reason</option>
                                <option value="changed_mind">Changed my mind</option>
                                <option value="found_better_price">Found better price elsewhere</option>
                                <option value="ordered_by_mistake">Ordered by mistake</option>
                                <option value="delivery_too_long">Delivery taking too long</option>
                                <option value="payment_issues">Payment issues</option>
                                <option value="product_unavailable">Product no longer needed</option>
                                <option value="other">Other reason</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="additional_comments" class="form-label">
                                <strong>Additional Comments (Optional)</strong>
                            </label>
                            <textarea class="form-control" id="additional_comments" name="additional_comments" 
                                      rows="3" placeholder="Please provide any additional details..."></textarea>
                        </div>

                        <!-- Cancellation Policy -->
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-info-circle"></i> Cancellation Policy</h6>
                            <ul class="mb-0">
                                <li>Orders can only be cancelled if they haven't been processed yet</li>
                                <li>Refunds will be processed within 3-5 business days</li>
                                <li>Original payment method will be credited</li>
                                <li>Cancellation is final and cannot be undone</li>
                            </ul>
                        </div>

                        <!-- Order Items -->
                        <div class="mb-4">
                            <h6>Items to be Cancelled:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $order->loadItems();
                                        $items = $order->getItems();
                                        foreach ($items as $item):
                                            require_once 'app/models/ProductModel.php';
                                            $productModel = new ProductModel();
                                            $product = $productModel->findById($item->getProductId());
                                        ?>
                                        <tr>
                                            <td>
                                                <?= $product ? htmlspecialchars($product->getName()) : 'Product not found' ?>
                                            </td>
                                            <td><?= $item->getQuantity() ?></td>
                                            <td>$<?= number_format($item->getPrice(), 2) ?></td>
                                            <td>$<?= number_format($item->getSubtotal(), 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <th colspan="3">Total Refund Amount:</th>
                                            <th>$<?= number_format($order->getTotalAmount(), 2) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirm_cancellation" required>
                            <label class="form-check-label" for="confirm_cancellation">
                                I understand that this action cannot be undone and I want to cancel this order
                            </label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="/Order/view/<?= $order->getId() ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Order
                            </a>
                            <div>
                                <a href="/Order/myOrders" class="btn btn-outline-secondary me-2">
                                    Keep Order
                                </a>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-x-circle"></i> Cancel Order
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/customer_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show confirmation dialog
        if (confirm('Are you absolutely sure you want to cancel this order? This action cannot be undone.')) {
            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Cancelling...';
            
            // Submit the form
            form.submit();
        }
    });
    
    // Enable/disable submit button based on checkbox
    const confirmCheckbox = document.getElementById('confirm_cancellation');
    confirmCheckbox.addEventListener('change', function() {
        submitBtn.disabled = !this.checked;
    });
    
    // Initially disable submit button
    submitBtn.disabled = true;
});
</script>
