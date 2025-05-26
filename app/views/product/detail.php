<?php
// Set page variables
$pageTitle = htmlspecialchars($product->getName());
$currentPage = 'products';

// Check if user is admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

if ($isAdmin) {
    // Admin breadcrumbs and layout
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => '/Order/dashboard'],
        ['title' => 'Products', 'url' => '/Product/list'],
        ['title' => $product->getName(), 'url' => '']
    ];
    $pageActions = '<a href="/Product/edit/' . $product->getID() . '" class="btn btn-warning"><i class="bi bi-pencil me-2"></i>Edit Product</a>';
    include 'app/views/layouts/admin_header.php';
} else {
    // Customer breadcrumbs and layout
    $breadcrumbs = [
        ['title' => 'Home', 'url' => '/'],
        ['title' => 'Products', 'url' => '/Product/list'],
        ['title' => $product->getName(), 'url' => '']
    ];
    include 'app/views/layouts/customer_header.php';
}
?>

<?php if ($isAdmin): ?>
<!-- ADMIN VIEW -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-box-seam me-2"></i>Product Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <?php if (!empty($product->getImage())): ?>
                            <img src="<?php echo htmlspecialchars($product->getImage()); ?>" 
                                 alt="<?php echo htmlspecialchars($product->getName()); ?>" 
                                 class="img-fluid rounded shadow-sm">
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <h3 class="mb-3"><?php echo htmlspecialchars($product->getName()); ?></h3>
                        
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong>Product ID:</strong> #<?php echo $product->getID(); ?>
                            </div>
                            <div class="col-sm-6">
                                <strong>Price:</strong> <span class="text-success fs-5">$<?php echo number_format($product->getPrice(), 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong>Status:</strong>
                                <?php if ($product->getStatus() === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-6">
                                <strong>Grade:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($product->getGrade()); ?></span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong>Inventory:</strong> 
                                <?php if ($product->getInventoryCount() > 0): ?>
                                    <span class="badge bg-success"><?php echo $product->getInventoryCount(); ?> in stock</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Out of stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-6">
                                <strong>Category:</strong> 
                                <?php echo $category ? htmlspecialchars($category->getName()) : 'Uncategorized'; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Description:</strong>
                            <p class="mt-2"><?php echo nl2br(htmlspecialchars($product->getDescription())); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Created:</strong> <?php echo date('F j, Y g:i A', strtotime($product->getCreatedAt())); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear me-2"></i>Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/Product/edit/<?php echo $product->getID(); ?>" class="btn btn-warning">
                        <i class="bi bi-pencil me-2"></i>Edit Product
                    </a>
                    <button class="btn btn-danger" onclick="deleteProduct(<?php echo $product->getID(); ?>)">
                        <i class="bi bi-trash me-2"></i>Delete Product
                    </button>
                    <a href="/Product/list" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Products
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Inventory Count:</span>
                        <strong><?php echo $product->getInventoryCount(); ?></strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Incoming Count:</span>
                        <strong><?php echo $product->getIncomingCount(); ?></strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Out of Stock:</span>
                        <strong><?php echo $product->getOutOfStock(); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- CUSTOMER VIEW -->
<div class="container">
    <div class="row">
        <div class="col-lg-6">
            <!-- Product Image -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <?php if (!empty($product->getImage())): ?>
                        <img src="<?php echo htmlspecialchars($product->getImage()); ?>" 
                             alt="<?php echo htmlspecialchars($product->getName()); ?>" 
                             class="img-fluid rounded">
                    <?php else: ?>
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 400px;">
                            <i class="bi bi-image text-muted" style="font-size: 6rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <!-- Product Info -->
            <div class="ps-lg-4">
                <h1 class="display-6 fw-bold mb-3"><?php echo htmlspecialchars($product->getName()); ?></h1>
                
                <div class="mb-4">
                    <span class="display-5 text-primary fw-bold">$<?php echo number_format($product->getPrice(), 2); ?></span>
                </div>
                
                <div class="mb-4">
                    <?php if ($product->getInventoryCount() > 0): ?>
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-check-circle me-2"></i>In Stock (<?php echo $product->getInventoryCount(); ?> available)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="bi bi-x-circle me-2"></i>Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <h5>Description</h5>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($product->getDescription())); ?></p>
                </div>
                
                <?php if ($category): ?>
                <div class="mb-4">
                    <h6>Category</h6>
                    <a href="/Product/list?category=<?php echo $category->getID(); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-tag me-2"></i><?php echo htmlspecialchars($category->getName()); ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- Add to Cart Section -->
                <div class="mb-4">
                    <?php if ($product->getInventoryCount() > 0): ?>
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label for="quantity" class="form-label">Quantity:</label>
                            </div>
                            <div class="col-auto">
                                <input type="number" class="form-control" id="quantity" value="1" min="1" max="<?php echo $product->getInventoryCount(); ?>" style="width: 80px;">
                            </div>
                            <div class="col">
                                <button class="btn btn-primary btn-lg" onclick="addToCartWithQuantity(<?php echo $product->getID(); ?>)">
                                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="bi bi-cart-x me-2"></i>Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Additional Actions -->
                <div class="d-flex gap-2">
                    <a href="/Product/list" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Products
                    </a>
                    <button class="btn btn-outline-secondary" onclick="shareProduct()">
                        <i class="bi bi-share me-2"></i>Share
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">You might also like</h3>
            <div class="row">
                <!-- This would be populated with related products -->
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Related products will be shown here</p>
                    <a href="/Product/list" class="btn btn-primary">
                        <i class="bi bi-grid me-2"></i>Browse All Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php 
// Include appropriate footer
if ($isAdmin) {
    include 'app/views/layouts/admin_footer.php';
} else {
    include 'app/views/layouts/customer_footer.php';
}
?>

<script>
<?php if ($isAdmin): ?>
// Admin specific scripts
function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        window.location.href = `/Product/delete/${productId}`;
    }
}
<?php else: ?>
// Customer specific scripts
function addToCartWithQuantity(productId) {
    const quantity = document.getElementById('quantity').value;
    
    fetch('/Cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount();
            showToast('successToast', data.message);
        } else {
            showToast('errorToast', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('errorToast', 'An error occurred while adding the product to cart');
    });
}

function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($product->getName()); ?>',
            text: 'Check out this product!',
            url: window.location.href
        });
    } else {
        // Fallback: copy URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            showToast('successToast', 'Product URL copied to clipboard!');
        });
    }
}
<?php endif; ?>
</script>
