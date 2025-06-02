<?php
// Set page variables
$pageTitle = 'Verify Your Identity - ShopEasy';
$currentPage = 'verify_purchase_password_change';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'My Profile', 'url' => '/Auth/profile'],
    ['title' => 'Verify Identity', 'url' => '']
];

// Include customer header
include 'app/views/layouts/customer_header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Verify Your Identity</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Security Check:</strong> To change your password, please verify your identity by selecting a product you've purchased from our store.
                        <?php if (isset($attemptsLeft)): ?>
                            <div class="mt-2 small">Attempts remaining: <strong><?php echo $attemptsLeft; ?></strong></div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" action="/Auth/verifyPurchaseForPasswordChange">
                        <div class="mb-4">
                            <label class="form-label">Select a product you've purchased:</label>
                            
                            <?php if (empty($products)): ?>
                                <div class="alert alert-warning">
                                    No products available for verification. Please contact support.
                                </div>
                            <?php else: ?>
                                <div class="row row-cols-1 row-cols-md-2 g-4 mb-3">
                                    <?php foreach ($products as $product): ?>
                                    <div class="col">
                                        <div class="form-check product-selection-card">
                                            <input class="form-check-input" type="radio" name="product_id" 
                                                id="product_<?php echo $product['id']; ?>" 
                                                value="<?php echo $product['id']; ?>" required>
                                            <label class="form-check-label product-card" for="product_<?php echo $product['id']; ?>">
                                                <div class="d-flex">
                                                    <div class="product-image me-3">
                                                        <?php if (!empty($product['image'])): ?>
                                                            <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="no-image">
                                                                <i class="bi bi-box"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                                        <div class="product-price"><?php echo '$' . number_format($product['price'], 2); ?></div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!$hasPurchases): ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="product_id" id="no_purchase" value="0" required>
                                <label class="form-check-label" for="no_purchase">
                                    <strong>I haven't purchased anything yet</strong>
                                </label>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/Auth/profile" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Profile
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-check me-2"></i>Verify Identity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-selection-card {
    margin-bottom: 1rem;
}
.product-card {
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    cursor: pointer;
    width: 100%;
    transition: all 0.2s ease;
}
.product-card:hover {
    background-color: #f8f9fa;
    border-color: #adb5bd;
}
.form-check-input:checked + .product-card {
    background-color: #e9f0ff;
    border-color: #0d6efd;
}
.no-image {
    width: 60px;
    height: 60px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #6c757d;
    border-radius: 0.25rem;
}
</style>

<?php
// Include footer
include 'app/views/layouts/customer_footer.php';
?>
