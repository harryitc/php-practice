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
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="/Product/list"><i class="bi bi-list-ul"></i> Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Product/add"><i class="bi bi-plus-circle"></i> Add Product</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
        <!-- Page Title and Add Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary"><i class="bi bi-list-ul"></i> Products</h1>
            <a class="btn btn-success" href="/Product/add">
                <i class="bi bi-plus-circle"></i> Add New Product
            </a>
        </div>

        <!-- Search and Filter Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group search-box">
                            <input type="text" class="form-control" placeholder="Search products..." aria-label="Search">
                            <button class="btn btn-outline-primary" type="button">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary">
                                <i class="bi bi-sort-alpha-down"></i> Name
                            </button>
                            <button type="button" class="btn btn-outline-primary">
                                <i class="bi bi-sort-numeric-down"></i> Status
                            </button>
                            <button type="button" class="btn btn-outline-primary">
                                <i class="bi bi-sort-numeric-down"></i> Inventory
                            </button>
                        </div>
                    </div>
                </div>
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
                                    <th scope="col" width="25%">Product</th>
                                    <th scope="col" width="12%">Status</th>
                                    <th scope="col" width="12%">Price</th>
                                    <th scope="col" width="12%">Inventory (count)</th>
                                    <th scope="col" width="12%">Incoming (count)</th>
                                    <th scope="col" width="12%">Out of Stock</th>
                                    <th scope="col" width="8%">Grade</th>
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
                                            <div class="btn-group" role="group">
                                                <a href="/Product/detail/<?php echo $product->getID(); ?>" class="btn btn-sm btn-info me-1" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="/Product/edit/<?php echo $product->getID(); ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $product->getID(); ?>" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
                            <p class="text-muted">Add a new product to get started.</p>
                            <a href="/Product/add" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-circle"></i> Add New Product
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if (!empty($products) && count($products) > 10): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
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
</body>
</html>