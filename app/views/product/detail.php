<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Product Details</title>
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
        .product-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .product-info-card {
            border-radius: 8px;
            overflow: hidden;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
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
        .inventory-count {
            font-weight: normal;
        }
        .zero-inventory {
            color: #dc3545;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .detail-value {
            font-weight: 500;
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
                            <a class="nav-link" href="/Product/list"><i class="bi bi-list-ul"></i> Products</a>
                        </li>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Product/add"><i class="bi bi-plus-circle"></i> Add Product</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="/Auth/profile"><i class="bi bi-person me-2"></i>My Profile</a></li>
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
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/Product/list">Products</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Product Details</li>
                </ol>
            </nav>

            <!-- Product Details -->
            <div class="row">
                <!-- Product Image -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body text-center p-4">
                            <?php if (!empty($product->getImage())): ?>
                                <img src="<?php echo htmlspecialchars((string)$product->getImage(), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string)$product->getName(), ENT_QUOTES, 'UTF-8'); ?>" class="product-image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x400" alt="Product" class="product-image">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="col-md-8">
                    <div class="card shadow-sm product-info-card mb-4">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h1 class="card-title h3 mb-0"><?php echo htmlspecialchars((string)$product->getName(), ENT_QUOTES, 'UTF-8'); ?></h1>
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
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h5 class="mb-3">Description</h5>
                                    <p class="card-text"><?php echo htmlspecialchars((string)$product->getDescription(), ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Inventory Information</h5>
                                            <div class="mt-3">
                                                <div class="row mb-2">
                                                    <div class="col-6 detail-label">Inventory:</div>
                                                    <div class="col-6 detail-value <?php echo ($product->getInventoryCount() == 0) ? 'zero-inventory' : ''; ?>">
                                                        <?php echo htmlspecialchars((string)$product->getInventoryStatus(), ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6 detail-label">Incoming:</div>
                                                    <div class="col-6 detail-value"><?php echo htmlspecialchars((string)$product->getIncomingCount(), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6 detail-label">Out of Stock:</div>
                                                    <div class="col-6 detail-value"><?php echo htmlspecialchars((string)$product->getOutOfStock(), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Product Details</h5>
                                            <div class="mt-3">
                                                <div class="row mb-2">
                                                    <div class="col-6 detail-label">Product ID:</div>
                                                    <div class="col-6 detail-value"><?php echo htmlspecialchars((string)$product->getID(), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6 detail-label">Price:</div>
                                                    <div class="col-6 detail-value"><?php echo number_format($product->getPrice(), 2, '.', ','); ?> USD</div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6 detail-label">Grade:</div>
                                                    <div class="col-6 detail-value"><?php echo htmlspecialchars((string)$product->getGrade(), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="/Product/list" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to List
                                </a>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <div>
                                    <a href="/Product/edit/<?php echo $product->getID(); ?>" class="btn btn-warning me-2">
                                        <i class="bi bi-pencil-square me-2"></i>Edit
                                    </a>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="bi bi-trash me-2"></i>Delete
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- End of content-wrapper -->

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
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

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Product Inventory Management System</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
