<?php
// Set page variables
$pageTitle = 'Checkout - ShopEasy';
$currentPage = 'checkout';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'Products', 'url' => '/Product/list'],
    ['title' => 'Cart', 'url' => '/Cart'],
    ['title' => 'Checkout', 'url' => '']
];

// Include customer header (checkout is only for customers)
include 'app/views/layouts/customer_header.php';
?>

<style>
.checkout-step {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}
.order-summary {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    position: sticky;
    top: 20px;
}
</style>
<div class="container">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary"><i class="bi bi-credit-card"></i> Checkout</h1>
        <a class="btn btn-outline-primary" href="/Cart">
            <i class="bi bi-arrow-left"></i> Back to Cart
        </a>
    </div>

    <!-- Error Messages -->
    <?php if (isset($_SESSION['checkout_errors'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($_SESSION['checkout_errors'] as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php
        unset($_SESSION['checkout_errors']);
    endif; ?>

            <div class="row">
                <!-- Checkout Form -->
                <div class="col-lg-8">
                    <form action="/Order/processCheckout" method="POST" id="checkoutForm">
                        <!-- Shipping Information -->
                        <div class="checkout-step">
                            <h4 class="mb-3"><i class="bi bi-truck"></i> Shipping Information</h4>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="shipping_address" class="form-label">Address *</label>
                                    <input type="text" class="form-control" id="shipping_address" name="shipping_address"
                                           value="<?php echo htmlspecialchars($_SESSION['checkout_data']['shipping_address'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="shipping_city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="shipping_city" name="shipping_city"
                                           value="<?php echo htmlspecialchars($_SESSION['checkout_data']['shipping_city'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="shipping_state" class="form-label">State *</label>
                                    <input type="text" class="form-control" id="shipping_state" name="shipping_state"
                                           value="<?php echo htmlspecialchars($_SESSION['checkout_data']['shipping_state'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="shipping_zip" class="form-label">ZIP Code *</label>
                                    <input type="text" class="form-control" id="shipping_zip" name="shipping_zip"
                                           value="<?php echo htmlspecialchars($_SESSION['checkout_data']['shipping_zip'] ?? ''); ?>" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="shipping_country" class="form-label">Country *</label>
                                    <select class="form-select" id="shipping_country" name="shipping_country" required>
                                        <option value="">Select Country</option>
                                        <option value="Vietnam" <?php echo (($_SESSION['checkout_data']['shipping_country'] ?? '') === 'Vietnam') ? 'selected' : ''; ?>>Vietnam</option>
                                        <option value="United States" <?php echo (($_SESSION['checkout_data']['shipping_country'] ?? '') === 'United States') ? 'selected' : ''; ?>>United States</option>
                                        <option value="Canada" <?php echo (($_SESSION['checkout_data']['shipping_country'] ?? '') === 'Canada') ? 'selected' : ''; ?>>Canada</option>
                                        <option value="United Kingdom" <?php echo (($_SESSION['checkout_data']['shipping_country'] ?? '') === 'United Kingdom') ? 'selected' : ''; ?>>United Kingdom</option>
                                        <option value="Australia" <?php echo (($_SESSION['checkout_data']['shipping_country'] ?? '') === 'Australia') ? 'selected' : ''; ?>>Australia</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="checkout-step">
                            <h4 class="mb-3"><i class="bi bi-credit-card"></i> Payment Method</h4>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod"
                                               <?php echo (($_SESSION['checkout_data']['payment_method'] ?? '') === 'cod') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="cod">
                                            <i class="bi bi-cash-coin me-2"></i>Cash on Delivery (COD)
                                            <small class="text-muted d-block">Pay when you receive your order</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer"
                                               <?php echo (($_SESSION['checkout_data']['payment_method'] ?? '') === 'bank_transfer') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="bank_transfer">
                                            <i class="bi bi-bank me-2"></i>Bank Transfer
                                            <small class="text-muted d-block">Transfer money to our bank account</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card"
                                               <?php echo (($_SESSION['checkout_data']['payment_method'] ?? '') === 'credit_card') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="credit_card">
                                            <i class="bi bi-credit-card me-2"></i>Credit Card
                                            <small class="text-muted d-block">Pay securely with your credit card</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Place Order ($<?php echo number_format($totalAmount, 2); ?>)
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h5 class="mb-3"><i class="bi bi-receipt"></i> Order Summary</h5>

                        <!-- Cart Items -->
                        <div class="mb-3">
                            <?php foreach ($cartItems as $item): ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3">
                                    <?php if (!empty($item['product']->getImage())): ?>
                                        <img src="<?php echo htmlspecialchars($item['product']->getImage()); ?>"
                                             alt="<?php echo htmlspecialchars($item['product']->getName()); ?>"
                                             class="product-image">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/60" alt="Product" class="product-image">
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['product']->getName()); ?></h6>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?></small>
                                </div>
                                <div>
                                    <strong>$<?php echo number_format($item['subtotal'], 2); ?></strong>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <hr>

                        <!-- Totals -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal (<?php echo $itemCount; ?> items):</span>
                            <span>$<?php echo number_format($totalAmount, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span class="text-success">Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span>$0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong>$<?php echo number_format($totalAmount, 2); ?></strong>
                        </div>

                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Your order is protected by SSL encryption
                        </small>
                    </div>
                </div>
            </div>
    </div>
</div>

<?php include 'app/views/layouts/customer_footer.php'; ?>

<script>
// Clear checkout data from session after displaying
<?php
if (isset($_SESSION['checkout_data'])) {
    unset($_SESSION['checkout_data']);
}
?>
</script>
