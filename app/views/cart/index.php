<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Shopping Cart - E-commerce</title>
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
        .cart-item {
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 0;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .quantity-input {
            width: 80px;
        }
        .cart-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
        }
        .empty-cart {
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
                            <a class="nav-link active" href="/Cart"><i class="bi bi-cart"></i> Cart</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/Cart">
                                <i class="bi bi-cart"></i> Cart
                                <span class="badge bg-warning text-dark" id="cart-count"><?php echo $itemCount; ?></span>
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
            <!-- Page Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-primary"><i class="bi bi-cart"></i> Shopping Cart</h1>
                <a class="btn btn-outline-primary" href="/Product/list">
                    <i class="bi bi-arrow-left"></i> Continue Shopping
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
            <div class="alert alert-danger alert-dismissible fade show d-none" role="alert" id="error-alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><span id="error-message"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <?php if (!empty($cartItems)): ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Cart Items (<?php echo $itemCount; ?>)</h5>
                        </div>
                        <div class="card-body" id="cart-items">
                            <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <?php if (!empty($item['product']->getImage())): ?>
                                            <img src="<?php echo htmlspecialchars($item['product']->getImage()); ?>"
                                                 alt="<?php echo htmlspecialchars($item['product']->getName()); ?>"
                                                 class="product-image">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/80" alt="Product" class="product-image">
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product']->getName()); ?></h6>
                                        <small class="text-muted">Price: $<?php echo number_format($item['price'], 2); ?></small>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number" class="form-control quantity-input text-center"
                                                   value="<?php echo $item['quantity']; ?>"
                                                   min="1" max="<?php echo $item['product']->getInventoryCount(); ?>"
                                                   onchange="updateQuantity(<?php echo $item['product_id']; ?>, this.value)">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">Max: <?php echo $item['product']->getInventoryCount(); ?></small>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <strong class="item-subtotal">$<?php echo number_format($item['subtotal'], 2); ?></strong>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button class="btn btn-outline-danger btn-sm" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="/Cart/clear" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to clear your cart?')">
                                    <i class="bi bi-trash"></i> Clear Cart
                                </a>
                                <a href="/Product/list" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left"></i> Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h5 class="mb-3"><i class="bi bi-receipt"></i> Order Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items (<?php echo $itemCount; ?>):</span>
                            <span id="cart-total">$<?php echo number_format($totalAmount, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span class="text-success">Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong id="final-total">$<?php echo number_format($totalAmount, 2); ?></strong>
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/Order/checkout" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-credit-card"></i> Proceed to Checkout
                        </a>
                        <?php else: ?>
                        <a href="/Auth/login?redirect=/Cart" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-box-arrow-in-right"></i> Login to Checkout
                        </a>
                        <?php endif; ?>

                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Secure checkout with SSL encryption
                        </small>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                <h3 class="mt-3">Your cart is empty</h3>
                <p class="text-muted">Add some products to your cart to get started.</p>
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
        function updateQuantity(productId, quantity) {
            if (quantity < 1) {
                removeFromCart(productId);
                return;
            }

            fetch('/Cart/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    document.getElementById('cart-count').textContent = data.cart_count;

                    // Update item subtotal
                    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
                    cartItem.querySelector('.item-subtotal').textContent = '$' + data.item_subtotal;

                    // Update totals
                    document.getElementById('cart-total').textContent = '$' + data.cart_total;
                    document.getElementById('final-total').textContent = '$' + data.cart_total;

                    // Update quantity input
                    cartItem.querySelector('.quantity-input').value = quantity;
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('An error occurred while updating the cart');
            });
        }

        function removeFromCart(productId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            fetch('/Cart/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove item from DOM
                    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
                    cartItem.remove();

                    // Update cart count
                    document.getElementById('cart-count').textContent = data.cart_count;

                    // Update totals
                    document.getElementById('cart-total').textContent = '$' + data.cart_total;
                    document.getElementById('final-total').textContent = '$' + data.cart_total;

                    // Check if cart is empty
                    if (data.cart_count == 0) {
                        location.reload();
                    }
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('An error occurred while removing the item');
            });
        }

        function showError(message) {
            const errorAlert = document.getElementById('error-alert');
            const errorMessage = document.getElementById('error-message');
            errorMessage.textContent = message;
            errorAlert.classList.remove('d-none');
        }
    </script>
</body>
</html>
