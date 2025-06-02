<?php
// Set page variables
$pageTitle = 'Verify Your Identity - ShopEasy';
$currentPage = 'verify_purchase';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'Forgot Password', 'url' => '/Auth/forgotPassword'],
    ['title' => 'Verify Identity', 'url' => '']
];

// Include customer header
include 'app/views/layouts/customer_header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Security Verification</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <p class="mb-4">For security purposes, please select the product you have purchased from our store. If you haven't made any purchases yet, select the appropriate option.</p>

                    <form action="/Auth/verifyPurchase" method="post">
                        <div class="row row-cols-1 row-cols-md-2 g-4 mb-4">
                            <?php foreach ($productOptions as $index => $product): ?>
                                <div class="col">
                                    <div class="card h-100">
                                        <div class="form-check product-selection">
                                            <input class="form-check-input" type="radio" name="product_id" 
                                                id="product_<?= $index; ?>" value="<?= $product['id']; ?>" required>
                                            <label class="form-check-label w-100" for="product_<?= $index; ?>">
                                                <?php if ($product['id'] !== 'no_purchase'): ?>
                                                    <?php if (!empty($product['image'])): ?>
                                                        <img src="<?= $product['image']; ?>" class="card-img-top product-image" alt="<?= $product['name']; ?>">
                                                    <?php else: ?>
                                                        <div class="card-img-top product-image-placeholder">
                                                            <i class="fas fa-box fa-3x text-secondary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="card-img-top product-image-placeholder">
                                                        <i class="fas fa-shopping-cart fa-3x text-secondary"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="card-body">
                                                    <h5 class="card-title"><?= $product['name']; ?></h5>
                                                    <?php if (!empty($product['price'])): ?>
                                                        <p class="card-text text-primary"><?= number_format($product['price'], 2); ?> VND</p>
                                                    <?php endif; ?>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Continue</button>
                            <a href="/Auth/forgotPassword" class="btn btn-outline-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-selection {
    margin: 0;
    padding: 0;
}

.product-selection input[type="radio"] {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
}

.product-selection label {
    cursor: pointer;
    margin: 0;
    padding: 0;
}

.product-image {
    height: 150px;
    object-fit: contain;
    padding: 10px;
}

.product-image-placeholder {
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}

.product-selection input[type="radio"]:checked + label .card-body {
    background-color: #e9f5ff;
}
</style>

<?php
// Include footer
include 'app/views/layouts/customer_footer.php';
?>
