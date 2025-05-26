<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Product Inventory Management</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrapper {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
            margin-top: auto !important;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        .product-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 500;
            border-bottom: 1px solid #dee2e6;
        }
        .product-table .description-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        .status-scoping {
            background-color: #e6f0ff;
            color: #0d6efd;
        }
        .status-quoting {
            background-color: #e6fff0;
            color: #00b44e;
        }
        .status-production {
            background-color: #fff8e6;
            color: #ffa500;
        }
        .status-shipped {
            background-color: #e6f2ff;
            color: #0077cc;
        }
        .product-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }
        .product-name {
            display: flex;
            align-items: center;
        }
        .inventory-count {
            font-weight: normal;
        }
        .zero-inventory {
            color: #dc3545;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 2rem 0;
        }
        .search-box {
            max-width: 400px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="content-wrapper">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <div class="container">
                <a class="navbar-brand" href="/">Product Inventory Management</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="/Product/list"><i class="bi bi-list-ul"></i> Products</a>
                        </li>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Product/add"><i class="bi bi-plus-circle"></i> Add Product</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Order/dashboard"><i class="bi bi-speedometer2"></i> Admin Dashboard</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/Cart">
                                <i class="bi bi-cart"></i> Cart
                                <span class="badge bg-warning text-dark" id="cart-count">0</span>
                            </a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="/Auth/profile"><i class="bi bi-person me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="/Order/myOrders"><i class="bi bi-bag me-2"></i>My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/Auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Auth/login"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Auth/register"><i class="bi bi-person-plus"></i> Register</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
        <!-- Page Title and Add Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary"><i class="bi bi-list-ul"></i> Products</h1>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <a class="btn btn-success" href="/Product/add">
                <i class="bi bi-plus-circle"></i> Add New Product
            </a>
            <?php endif; ?>
        </div>

        <!-- Success Message -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
            // Clear the message after displaying it
            unset($_SESSION['success_message']);
        endif; ?>

        <!-- Search and Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="/Product/list" method="GET" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group search-box">
                                <input type="text" class="form-control" name="search" placeholder="Search products..."
                                       value="<?php echo htmlspecialchars((string)($search ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <select class="form-select" name="status" id="statusFilter" onchange="document.getElementById('filterForm').submit()">
                                        <option value="">All Statuses</option>
                                        <?php foreach ($statuses as $statusOption): ?>
                                            <option value="<?php echo htmlspecialchars((string)$statusOption, ENT_QUOTES, 'UTF-8'); ?>"
                                                <?php echo ($selectedStatus == $statusOption) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string)$statusOption, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" name="grade" id="gradeFilter" onchange="document.getElementById('filterForm').submit()">
                                        <option value="">All Grades</option>
                                        <?php foreach ($grades as $gradeOption): ?>
                                            <option value="<?php echo htmlspecialchars((string)$gradeOption, ENT_QUOTES, 'UTF-8'); ?>"
                                                <?php echo ($selectedGrade == $gradeOption) ? 'selected' : ''; ?>>
                                                Grade <?php echo htmlspecialchars((string)$gradeOption, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" name="inventory" id="inventoryFilter" onchange="document.getElementById('filterForm').submit()">
                                        <option value="">All Inventory</option>
                                        <option value="in_stock" <?php echo ($selectedInventory == 'in_stock') ? 'selected' : ''; ?>>In Stock</option>
                                        <option value="out_of_stock" <?php echo ($selectedInventory == 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($search) || !empty($selectedStatus) || !empty($selectedGrade) || !empty($selectedInventory)): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <span class="me-2">Active filters:</span>
                                <?php if (!empty($search)): ?>
                                    <span class="badge bg-primary me-2">
                                        Search: <?php echo htmlspecialchars((string)$search, ENT_QUOTES, 'UTF-8'); ?>
                                        <a href="<?php echo $this->removeQueryParam('search'); ?>" class="text-white ms-1"><i class="bi bi-x-circle"></i></a>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($selectedStatus)): ?>
                                    <span class="badge bg-primary me-2">
                                        Status: <?php echo htmlspecialchars((string)$selectedStatus, ENT_QUOTES, 'UTF-8'); ?>
                                        <a href="<?php echo $this->removeQueryParam('status'); ?>" class="text-white ms-1"><i class="bi bi-x-circle"></i></a>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($selectedGrade)): ?>
                                    <span class="badge bg-primary me-2">
                                        Grade: <?php echo htmlspecialchars((string)$selectedGrade, ENT_QUOTES, 'UTF-8'); ?>
                                        <a href="<?php echo $this->removeQueryParam('grade'); ?>" class="text-white ms-1"><i class="bi bi-x-circle"></i></a>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($selectedInventory)): ?>
                                    <span class="badge bg-primary me-2">
                                        Inventory: <?php echo $selectedInventory == 'in_stock' ? 'In Stock' : 'Out of Stock'; ?>
                                        <a href="<?php echo $this->removeQueryParam('inventory'); ?>" class="text-white ms-1"><i class="bi bi-x-circle"></i></a>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($search) || !empty($selectedStatus) || !empty($selectedGrade) || !empty($selectedInventory)): ?>
                                    <a href="/Product/list" class="btn btn-sm btn-outline-secondary ms-auto">Clear All Filters</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <?php if (!empty($products)): ?>
                        <table class="table table-hover mb-0 product-table">
                            <thead>
                                <tr>
                                    <th scope="col" width="3%"><input type="checkbox" class="form-check-input"></th>
                                    <th scope="col" width="20%">Product</th>
                                    <th scope="col" width="10%">Status</th>
                                    <th scope="col" width="10%">Price</th>
                                    <th scope="col" width="10%">Inventory</th>
                                    <th scope="col" width="10%">Incoming</th>
                                    <th scope="col" width="10%">Out of Stock</th>
                                    <th scope="col" width="7%">Grade</th>
                                    <th scope="col" width="10%" class="text-center">Add to Cart</th>
                                    <th scope="col" width="10%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input"></td>
                                        <td class="product-name">
                                            <?php if (!empty($product->getImage())): ?>
                                                <img src="<?php echo htmlspecialchars((string)$product->getImage(), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string)$product->getName(), ENT_QUOTES, 'UTF-8'); ?>" class="product-image">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/40" alt="Product" class="product-image">
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars((string)$product->getName(), ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td>
                                            <?php
                                                $statusClass = 'status-scoping';
                                                if ($product->getStatus() == 'Quoting') {
                                                    $statusClass = 'status-quoting';
                                                } elseif ($product->getStatus() == 'Production') {
                                                    $statusClass = 'status-production';
                                                } elseif ($product->getStatus() == 'Shipped') {
                                                    $statusClass = 'status-shipped';
                                                }
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars((string)$product->getStatus(), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars((string)$product->getPrice(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="inventory-count <?php echo ($product->getInventoryCount() == 0) ? 'zero-inventory' : ''; ?>">
                                            <?php echo htmlspecialchars((string)$product->getInventoryStatus(), ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars((string)$product->getIncomingCount(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$product->getOutOfStock(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string)$product->getGrade(), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center">
                                            <?php if ($product->getInventoryCount() > 0): ?>
                                                <button class="btn btn-sm btn-success" onclick="addToCart(<?php echo $product->getID(); ?>)" title="Add to Cart">
                                                    <i class="bi bi-cart-plus"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled title="Out of Stock">
                                                    <i class="bi bi-cart-x"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="/Product/detail/<?php echo $product->getID(); ?>" class="btn btn-sm btn-info me-1" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                                <a href="/Product/edit/<?php echo $product->getID(); ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $product->getID(); ?>" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Delete Confirmation Modal -->
                                            <div class="modal fade" id="deleteModal<?php echo $product->getID(); ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $product->getID(); ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title" id="deleteModalLabel<?php echo $product->getID(); ?>">
                                                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Delete
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="text-center mb-3">
                                                                <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                                                            </div>
                                                            <p class="text-center fs-5">Are you sure you want to delete this product:</p>
                                                            <p class="text-center fw-bold fs-4">"<?php echo htmlspecialchars((string)$product->getName(), ENT_QUOTES, 'UTF-8'); ?>"</p>
                                                            <p class="text-center text-muted">This action cannot be undone!</p>
                                                        </div>
                                                        <div class="modal-footer justify-content-center">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <i class="bi bi-x-circle me-2"></i>Cancel
                                                            </button>
                                                            <a href="/Product/delete/<?php echo $product->getID(); ?>" class="btn btn-danger">
                                                                <i class="bi bi-trash me-2"></i>Confirm Delete
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">No products found</h4>
                            <p class="text-muted">
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                Add a new product to get started.
                                <?php else: ?>
                                No products are available at this time.
                                <?php endif; ?>
                            </p>
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <a href="/Product/add" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-circle"></i> Add New Product
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pagination and Results Summary -->
        <?php if ($totalProducts > 0): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <p class="mb-0 text-muted">
                    Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products
                    <?php if (!empty($search) || !empty($selectedStatus) || !empty($selectedGrade) || !empty($selectedInventory)): ?>
                        (filtered)
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $this->buildPaginationUrl($currentPage - 1); ?>" tabindex="-1" aria-disabled="<?php echo ($currentPage <= 1) ? 'true' : 'false'; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $startPage + 4);
                    if ($endPage - $startPage < 4 && $totalPages > 4) {
                        $startPage = max(1, $endPage - 4);
                    }
                    ?>

                    <?php if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo $this->buildPaginationUrl(1); ?>">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $this->buildPaginationUrl($i); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo $this->buildPaginationUrl($totalPages); ?>"><?php echo $totalPages; ?></a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $this->buildPaginationUrl($currentPage + 1); ?>" aria-disabled="<?php echo ($currentPage >= $totalPages) ? 'true' : 'false'; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    </div><!-- End of content-wrapper -->

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Product Inventory Management System</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>

    <!-- Success Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-message">
                Product added to cart successfully!
            </div>
        </div>
    </div>

    <!-- Error Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="error-toast-message">
                Failed to add product to cart.
            </div>
        </div>
    </div>

    <script>
        // Load cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });

        function addToCart(productId) {
            fetch('/Cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    document.getElementById('cart-count').textContent = data.cart_count;

                    // Show success toast
                    showToast('successToast', data.message);
                } else {
                    // Show error toast
                    showToast('errorToast', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('errorToast', 'An error occurred while adding the product to cart');
            });
        }

        function updateCartCount() {
            fetch('/Cart/count')
            .then(response => response.json())
            .then(data => {
                document.getElementById('cart-count').textContent = data.count;
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
        }

        function showToast(toastId, message) {
            const toast = document.getElementById(toastId);
            const messageElement = toastId === 'successToast' ?
                document.getElementById('toast-message') :
                document.getElementById('error-toast-message');

            messageElement.textContent = message;

            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
    </script>
</body>
</html>