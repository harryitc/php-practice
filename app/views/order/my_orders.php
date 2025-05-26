<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>My Orders - E-commerce</title>
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
        .order-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: box-shadow 0.15s ease-in-out;
        }
        .order-card:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-shipped {
            background-color: #d4edda;
            color: #155724;
        }
        .status-delivered {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .empty-orders {
            text-align: center;
            padding: 3rem 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 2rem 0;
        }
    </style>
</head>
<body class="bg-light">
    <div class="content-wrapper">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <div class="container">
                <a class="navbar-brand" href="/">E-commerce Store</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/Product/list"><i class="bi bi-list-ul"></i> Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Cart"><i class="bi bi-cart"></i> Cart</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/Cart">
                                <i class="bi bi-cart"></i> Cart 
                                <span class="badge bg-warning text-dark" id="cart-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="/Auth/profile"><i class="bi bi-person me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item active" href="/Order/myOrders"><i class="bi bi-bag me-2"></i>My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/Auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
            <!-- Page Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-primary"><i class="bi bi-bag"></i> My Orders</h1>
                <a class="btn btn-outline-primary" href="/Product/list">
                    <i class="bi bi-shop"></i> Continue Shopping
                </a>
            </div>

            <!-- Success Message -->
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
                unset($_SESSION['success_message']);
            endif; ?>

            <!-- Error Message -->
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
                unset($_SESSION['error_message']);
            endif; ?>

            <?php if (!empty($orders)): ?>
                <!-- Orders List -->
                <div class="row">
                    <?php foreach ($orders as $order): ?>
                    <div class="col-12">
                        <div class="order-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <h6 class="mb-1">Order #<?php echo $order->getId(); ?></h6>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($order->getCreatedAt())); ?></small>
                                    </div>
                                    <div class="col-md-2">
                                        <?php
                                            $statusClass = 'status-pending';
                                            if ($order->getStatus() == 'processing') {
                                                $statusClass = 'status-processing';
                                            } elseif ($order->getStatus() == 'shipped') {
                                                $statusClass = 'status-shipped';
                                            } elseif ($order->getStatus() == 'delivered') {
                                                $statusClass = 'status-delivered';
                                            } elseif ($order->getStatus() == 'cancelled') {
                                                $statusClass = 'status-cancelled';
                                            }
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($order->getStatus()); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>$<?php echo number_format($order->getTotalAmount(), 2); ?></strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Payment: <?php echo ucwords(str_replace('_', ' ', $order->getPaymentMethod())); ?></small>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="/Order/view/<?php echo $order->getId(); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                        <?php if ($order->getStatus() === 'pending'): ?>
                                        <button class="btn btn-sm btn-outline-danger ms-1" onclick="cancelOrder(<?php echo $order->getId(); ?>)">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Order Items Preview -->
                                <?php 
                                $order->loadItems();
                                $items = $order->getItems();
                                if (!empty($items)): 
                                ?>
                                <hr class="my-3">
                                <div class="row">
                                    <div class="col-12">
                                        <small class="text-muted">Items:</small>
                                        <div class="d-flex flex-wrap mt-1">
                                            <?php 
                                            $itemCount = 0;
                                            foreach ($items as $item): 
                                                if ($itemCount >= 3) break;
                                                $product = new ProductModel();
                                                $product = $product->findById($item->getProductId());
                                                $itemCount++;
                                            ?>
                                            <span class="badge bg-light text-dark me-2 mb-1">
                                                <?php echo $product ? htmlspecialchars($product->getName()) : 'Product not found'; ?> 
                                                (<?php echo $item->getQuantity(); ?>)
                                            </span>
                                            <?php endforeach; ?>
                                            <?php if (count($items) > 3): ?>
                                            <span class="badge bg-secondary">+<?php echo count($items) - 3; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty Orders -->
                <div class="empty-orders">
                    <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No orders yet</h3>
                    <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                    <a href="/Product/list" class="btn btn-primary mt-2">
                        <i class="bi bi-shop"></i> Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> E-commerce Store</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    
    <script>
        // Update cart count
        fetch('/Cart/count')
        .then(response => response.json())
        .then(data => {
            document.getElementById('cart-count').textContent = data.count;
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });

        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                // You can implement order cancellation logic here
                alert('Order cancellation feature will be implemented soon.');
            }
        }
    </script>
</body>
</html>
