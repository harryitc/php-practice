<?php
// Set page variables
$pageTitle = 'Welcome to ShopEasy - Your Online Store';
$currentPage = 'home';

// Include customer header
include 'app/views/layouts/customer_header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">
                    Welcome to <span class="text-white">ShopEasy</span>
                </h1>
                <p class="lead mb-4">
                    Discover amazing products at unbeatable prices. Shop with confidence and enjoy fast, secure delivery to your doorstep.
                </p>
                <div class="d-flex gap-3">
                    <a href="/Product/list" class="btn btn-light btn-lg">
                        <i class="bi bi-shop me-2"></i>Shop Now
                    </a>
                    <a href="#featured-products" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-arrow-down me-2"></i>Explore
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://via.placeholder.com/500x400/667eea/ffffff?text=ShopEasy" 
                     alt="Shopping" class="img-fluid rounded-3 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0">
                    <div class="card-body">
                        <div class="text-primary mb-3">
                            <i class="bi bi-truck" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">Free Shipping</h5>
                        <p class="card-text text-muted">
                            Free shipping on all orders over $50. Fast and reliable delivery to your doorstep.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0">
                    <div class="card-body">
                        <div class="text-primary mb-3">
                            <i class="bi bi-shield-check" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">Secure Payment</h5>
                        <p class="card-text text-muted">
                            Your payment information is secure with our SSL encryption and trusted payment gateways.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0">
                    <div class="card-body">
                        <div class="text-primary mb-3">
                            <i class="bi bi-arrow-clockwise" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="card-title">Easy Returns</h5>
                        <p class="card-text text-muted">
                            Not satisfied? Return your items within 30 days for a full refund, no questions asked.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section id="featured-products" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Featured Products</h2>
            <p class="lead text-muted">Check out our most popular items</p>
        </div>
        
        <div class="row">
            <?php if (!empty($featuredProducts)): ?>
                <?php foreach ($featuredProducts as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card product-card h-100">
                        <?php if (!empty($product->getImage())): ?>
                            <img src="<?php echo htmlspecialchars($product->getImage()); ?>" 
                                 class="card-img-top product-image" 
                                 alt="<?php echo htmlspecialchars($product->getName()); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x200/f8f9fa/6c757d?text=No+Image" 
                                 class="card-img-top product-image" 
                                 alt="No Image">
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product->getName()); ?></h5>
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo htmlspecialchars(substr($product->getDescription(), 0, 100)); ?>
                                <?php if (strlen($product->getDescription()) > 100): ?>...<?php endif; ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="price-tag">$<?php echo number_format($product->getPrice(), 2); ?></span>
                                <?php if ($product->getInventoryCount() > 0): ?>
                                    <span class="status-badge status-in-stock">In Stock</span>
                                <?php else: ?>
                                    <span class="status-badge status-out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <?php if ($product->getInventoryCount() > 0): ?>
                                    <button class="btn btn-primary" onclick="addToCart(<?php echo $product->getID(); ?>)">
                                        <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="bi bi-cart-x me-2"></i>Out of Stock
                                    </button>
                                <?php endif; ?>
                                <a href="/Product/detail/<?php echo $product->getID(); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">No featured products available</h4>
                    <p class="text-muted">Check back later for exciting new products!</p>
                    <a href="/Product/list" class="btn btn-primary mt-3">
                        <i class="bi bi-shop me-2"></i>Browse All Products
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($featuredProducts)): ?>
        <div class="text-center mt-4">
            <a href="/Product/list" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-grid me-2"></i>View All Products
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Shop by Category</h2>
            <p class="lead text-muted">Find exactly what you're looking for</p>
        </div>
        
        <div class="row">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-primary mb-3">
                                <i class="bi bi-tag" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title"><?php echo htmlspecialchars($category->getName()); ?></h5>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($category->getDescription()); ?>
                            </p>
                            <a href="/Product/list?category=<?php echo $category->getID(); ?>" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-right me-2"></i>Browse Category
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-tags text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">No categories available</h4>
                    <p class="text-muted">Categories will be added soon!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h3 class="fw-bold mb-3">Stay Updated</h3>
                <p class="mb-0">Subscribe to our newsletter and get the latest deals and updates delivered to your inbox.</p>
            </div>
            <div class="col-lg-6">
                <form class="d-flex gap-2">
                    <input type="email" class="form-control" placeholder="Enter your email address" required>
                    <button type="submit" class="btn btn-light">
                        <i class="bi bi-envelope me-2"></i>Subscribe
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'app/views/layouts/customer_footer.php'; ?>
