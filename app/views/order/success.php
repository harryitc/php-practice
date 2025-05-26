<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Order Success - E-commerce</title>
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
        .success-container {
            text-align: center;
            padding: 3rem 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 2rem 0;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .order-details {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 2rem;
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
                                <li><a class="dropdown-item" href="/Order/myOrders"><i class="bi bi-bag me-2"></i>My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/Auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
            <!-- Success Message -->
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
                unset($_SESSION['success_message']);
            endif; ?>

            <!-- Success Container -->
            <div class="success-container">
                <i class="bi bi-check-circle-fill success-icon"></i>
                <h1 class="text-success mb-3">Order Placed Successfully!</h1>
                <p class="lead">Thank you for your order. We've received your order and will process it shortly.</p>
                
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="order-details">
                            <h4 class="mb-3"><i class="bi bi-receipt"></i> Order Details</h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Order ID:</strong> #<?php echo $order->getId(); ?></p>
                                    <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order->getCreatedAt())); ?></p>
                                    <p><strong>Status:</strong> <span class="badge bg-warning text-dark"><?php echo ucfirst($order->getStatus()); ?></span></p>
                                    <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order->getPaymentMethod())); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total Amount:</strong> $<?php echo number_format($order->getTotalAmount(), 2); ?></p>
                                    <p><strong>Shipping Address:</strong><br>
                                    <?php echo htmlspecialchars($order->getShippingAddress()); ?><br>
                                    <?php echo htmlspecialchars($order->getShippingCity()); ?>, <?php echo htmlspecialchars($order->getShippingState()); ?> <?php echo htmlspecialchars($order->getShippingZip()); ?><br>
                                    <?php echo htmlspecialchars($order->getShippingCountry()); ?></p>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <hr>
                            <h5 class="mb-3">Order Items</h5>
                            <?php 
                            $order->loadItems();
                            $items = $order->getItems();
                            ?>
                            <?php if (!empty($items)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                                <?php 
                                                $product = new ProductModel();
                                                $product = $product->findById($item->getProductId());
                                                ?>
                                                <tr>
                                                    <td><?php echo $product ? htmlspecialchars($product->getName()) : 'Product not found'; ?></td>
                                                    <td><?php echo $item->getQuantity(); ?></td>
                                                    <td>$<?php echo number_format($item->getPrice(), 2); ?></td>
                                                    <td>$<?php echo number_format($item->getPrice() * $item->getQuantity(), 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3">Total:</th>
                                                <th>$<?php echo number_format($order->getTotalAmount(), 2); ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4">
                    <a href="/Order/myOrders" class="btn btn-primary me-2">
                        <i class="bi bi-bag"></i> View My Orders
                    </a>
                    <a href="/Product/list" class="btn btn-outline-primary">
                        <i class="bi bi-shop"></i> Continue Shopping
                    </a>
                </div>

                <!-- Payment Instructions -->
                <?php if ($order->getPaymentMethod() === 'bank_transfer'): ?>
                <div class="alert alert-info mt-4">
                    <h6><i class="bi bi-info-circle"></i> Bank Transfer Instructions</h6>
                    <p class="mb-0">Please transfer the amount to our bank account:</p>
                    <p class="mb-0"><strong>Bank:</strong> ABC Bank</p>
                    <p class="mb-0"><strong>Account Number:</strong> 1234567890</p>
                    <p class="mb-0"><strong>Account Name:</strong> E-commerce Store</p>
                    <p class="mb-0"><strong>Reference:</strong> Order #<?php echo $order->getId(); ?></p>
                </div>
                <?php elseif ($order->getPaymentMethod() === 'cod'): ?>
                <div class="alert alert-info mt-4">
                    <h6><i class="bi bi-info-circle"></i> Cash on Delivery</h6>
                    <p class="mb-0">Please have the exact amount ready when your order arrives.</p>
                    <p class="mb-0">Our delivery person will collect $<?php echo number_format($order->getTotalAmount(), 2); ?> upon delivery.</p>
                </div>
                <?php endif; ?>

                <!-- Contact Information -->
                <div class="mt-4">
                    <small class="text-muted">
                        <i class="bi bi-envelope"></i> Questions about your order? Contact us at support@ecommerce.com
                    </small>
                </div>
            </div>
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
    </script>
</body>
</html>
