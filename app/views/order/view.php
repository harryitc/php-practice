<?php
// Set page variables
$pageTitle = 'Order Details - ShopEasy';
$currentPage = 'orders';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'My Orders', 'url' => '/Order/myOrders'],
    ['title' => 'Order #' . $order->getId(), 'url' => '']
];

// Include customer header
include 'app/views/layouts/customer_header.php';
?>

<div class="container">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary"><i class="bi bi-file-earmark-text"></i> Order Details</h1>
        <a href="/Order/myOrders" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back to My Orders
        </a>
    </div>

    <!-- Order Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Order Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 40%">Order ID:</th>
                            <td><strong>#<?php echo $order->getId(); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td><?php echo date('F d, Y H:i', strtotime($order->getCreatedAt())); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <?php
                                    $statusClass = '';
                                    $statusIcon = '';
                                    switch ($order->getStatus()) {
                                        case 'pending':
                                            $statusClass = 'bg-warning text-dark';
                                            $statusIcon = 'bi-clock';
                                            break;
                                        case 'processing':
                                            $statusClass = 'bg-info text-dark';
                                            $statusIcon = 'bi-gear';
                                            break;
                                        case 'shipped':
                                            $statusClass = 'bg-primary';
                                            $statusIcon = 'bi-truck';
                                            break;
                                        case 'delivered':
                                            $statusClass = 'bg-success';
                                            $statusIcon = 'bi-check-circle';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'bg-danger';
                                            $statusIcon = 'bi-x-circle';
                                            break;
                                    }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <i class="bi <?php echo $statusIcon; ?> me-1"></i><?php echo ucfirst($order->getStatus()); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Method:</th>
                            <td>
                                <?php
                                    $paymentIcon = '';
                                    switch($order->getPaymentMethod()) {
                                        case 'cod': $paymentIcon = 'bi-cash'; break;
                                        case 'bank_transfer': $paymentIcon = 'bi-bank'; break;
                                        case 'credit_card': $paymentIcon = 'bi-credit-card'; break;
                                    }
                                ?>
                                <i class="bi <?php echo $paymentIcon; ?> me-1"></i>
                                <?php echo ucwords(str_replace('_', ' ', $order->getPaymentMethod())); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td class="fw-bold text-success">$<?php echo number_format($order->getTotalAmount(), 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Shipping Information</h5>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        <strong>Delivery Address:</strong><br>
                        <?php echo htmlspecialchars($order->getShippingAddress()); ?><br>
                        <?php echo htmlspecialchars($order->getShippingCity()); ?>, 
                        <?php echo htmlspecialchars($order->getShippingState()); ?> 
                        <?php echo htmlspecialchars($order->getShippingZip()); ?><br>
                        <?php echo htmlspecialchars($order->getShippingCountry()); ?>
                    </address>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-bag"></i> Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $items = $order->getItems();
                                if (count($items) > 0):
                                    foreach ($items as $item):
                                        $product = $item->getProduct();
                                        $productName = $product ? $product->getName() : 'Unknown Product';
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($product && !empty($product->getImage())): ?>
                                                    <img src="<?php echo htmlspecialchars($product->getImage()); ?>" 
                                                         alt="<?php echo htmlspecialchars($productName); ?>" 
                                                         class="me-3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                <?php else: ?>
                                                    <img src="https://via.placeholder.com/50" alt="Product" 
                                                         class="me-3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                <?php endif; ?>
                                                <div>
                                                    <?php if ($product): ?>
                                                        <a href="/Product/detail/<?php echo $product->getID(); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($productName); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($productName); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($item->getPrice(), 2); ?></td>
                                        <td><?php echo $item->getQuantity(); ?></td>
                                        <td class="text-end">$<?php echo number_format($item->getSubtotal(), 2); ?></td>
                                    </tr>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0">No items found in this order</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-end">$<?php echo number_format($order->getTotalAmount(), 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/customer_footer.php'; ?>
