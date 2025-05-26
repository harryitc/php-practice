<?php
// Set page variables
$pageTitle = 'Products';
$currentPage = 'products';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'Products', 'url' => '/Product/list']
];

// Check if user is admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Include appropriate header
if ($isAdmin) {
    $pageTitle = 'Product Management';
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => '/Order/dashboard'],
        ['title' => 'Products', 'url' => '/Product/list']
    ];
    $pageActions = '<a href="/Product/add" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Product</a>';
    include 'app/views/layouts/admin_header.php';
} else {
    include 'app/views/layouts/customer_header.php';
}
?>

<?php if ($isAdmin): ?>
<!-- ADMIN VIEW -->

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-funnel me-2"></i>Filters & Search
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="/Product/list" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Products</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                               placeholder="Search by name or description...">
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category->getID(); ?>"
                                            <?php echo (($_GET['category'] ?? '') == $category->getID()) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category->getName()); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?php echo (($_GET['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (($_GET['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Admin Products Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-box-seam me-2"></i>Products (<?php echo count($products); ?>)
        </h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="selectAll()">
                <i class="bi bi-check-all me-1"></i>Select All
            </button>
            <button class="btn btn-outline-danger btn-sm" onclick="deleteSelected()">
                <i class="bi bi-trash me-1"></i>Delete Selected
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($products)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="3%"><input type="checkbox" class="form-check-input" id="selectAllCheckbox"></th>
                        <th width="20%">Product</th>
                        <th width="10%">Category</th>
                        <th width="10%">Price</th>
                        <th width="10%">Inventory</th>
                        <th width="10%">Status</th>
                        <th width="12%">Created</th>
                        <th width="15%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input product-checkbox" value="<?php echo $product->getID(); ?>"></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if (!empty($product->getImage())): ?>
                                    <img src="<?php echo htmlspecialchars($product->getImage()); ?>"
                                         alt="<?php echo htmlspecialchars($product->getName()); ?>"
                                         class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($product->getName()); ?></h6>
                                    <small class="text-muted">ID: <?php echo $product->getID(); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php
                            $category = null;
                            if (!empty($categories)) {
                                foreach ($categories as $cat) {
                                    if ($cat->getID() == $product->getCategoryID()) {
                                        $category = $cat;
                                        break;
                                    }
                                }
                            }
                            echo $category ? htmlspecialchars($category->getName()) : 'Uncategorized';
                            ?>
                        </td>
                        <td><strong>$<?php echo number_format($product->getPrice(), 2); ?></strong></td>
                        <td>
                            <?php if ($product->getInventoryCount() > 0): ?>
                                <span class="badge bg-success"><?php echo $product->getInventoryCount(); ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($product->getStatus() === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            // For now, show product ID as created date is not available
                            echo "ID: " . $product->getID();
                            ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="/Product/detail/<?php echo $product->getID(); ?>" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/Product/edit/<?php echo $product->getID(); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="deleteProduct(<?php echo $product->getID(); ?>)" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">No products found</h4>
            <p class="text-muted">Start by adding your first product to the inventory.</p>
            <a href="/Product/add" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>Add First Product
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<!-- CUSTOMER VIEW -->
<div class="container">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <!-- Search and Filters -->
    <div class="category-filter">
        <form method="GET" action="/Product/list" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control search-box" name="search"
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                           placeholder="Search products...">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category->getID(); ?>"
                                    <?php echo (($_GET['category'] ?? '') == $category->getID()) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category->getName()); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="sort" onchange="this.form.submit()">
                    <option value="">Sort by</option>
                    <option value="name_asc" <?php echo (($_GET['sort'] ?? '') === 'name_asc') ? 'selected' : ''; ?>>Name A-Z</option>
                    <option value="name_desc" <?php echo (($_GET['sort'] ?? '') === 'name_desc') ? 'selected' : ''; ?>>Name Z-A</option>
                    <option value="price_asc" <?php echo (($_GET['sort'] ?? '') === 'price_asc') ? 'selected' : ''; ?>>Price Low-High</option>
                    <option value="price_desc" <?php echo (($_GET['sort'] ?? '') === 'price_desc') ? 'selected' : ''; ?>>Price High-Low</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <?php if (!empty($products)): ?>
    <div class="row">
        <?php foreach ($products as $product): ?>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
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
    </div>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
        <h4 class="mt-3 text-muted">No products found</h4>
        <p class="text-muted">Try adjusting your search criteria or browse all products.</p>
        <a href="/Product/list" class="btn btn-primary mt-3">
            <i class="bi bi-grid me-2"></i>View All Products
        </a>
    </div>
    <?php endif; ?>
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
function selectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.product-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function deleteSelected() {
    const selectedProducts = [];
    document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
        selectedProducts.push(checkbox.value);
    });

    if (selectedProducts.length === 0) {
        alert('Please select products to delete');
        return;
    }

    if (confirm(`Are you sure you want to delete ${selectedProducts.length} product(s)?`)) {
        // Implement bulk delete functionality
        console.log('Delete products:', selectedProducts);
    }
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        window.location.href = `/Product/delete/${productId}`;
    }
}

// Handle select all checkbox
document.getElementById('selectAllCheckbox').addEventListener('change', selectAll);
<?php endif; ?>
</script>
