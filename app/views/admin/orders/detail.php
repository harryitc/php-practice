<?php
require_once 'app/models/UserModel.php';
require_once 'app/models/ProductModel.php';

// Set page variables
$pageTitle = 'Order Details';
$currentPage = 'orders';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/Order/dashboard'],
    ['title' => 'Orders', 'url' => '/Order/list'],
    ['title' => 'Order #' . $order->getId(), 'url' => '']
];
$pageActions = '<a href="/Order/list" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-2"></i>Back to Orders</a>';

// Include admin header
include 'app/views/layouts/admin_header.php';
?>

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Order Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Order Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 40%">Order ID:</th>
                                    <td>#<?php echo $order->getId(); ?></td>
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
                                            switch ($order->getStatus()) {
                                                case 'pending':
                                                    $statusClass = 'bg-warning text-dark';
                                                    break;
                                                case 'processing':
                                                    $statusClass = 'bg-info text-dark';
                                                    break;
                                                case 'shipped':
                                                    $statusClass = 'bg-primary';
                                                    break;
                                                case 'delivered':
                                                    $statusClass = 'bg-success';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'bg-danger';
                                                    break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($order->getStatus()); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td><?php echo ucfirst($order->getPaymentMethod()); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td class="fw-bold">$<?php echo number_format($order->getTotalAmount(), 2); ?></td>
                                </tr>
                            </table>

                            <!-- Update Status Form -->
                            <form action="/Order/updateStatus/<?php echo $order->getId(); ?>" method="post" class="mt-3">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Update Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="pending" <?php echo $order->getStatus() === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order->getStatus() === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $order->getStatus() === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $order->getStatus() === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order->getStatus() === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Status</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($customer): ?>
                                <table class="table table-borderless">
                                    <tr>
                                        <th style="width: 40%">Name:</th>
                                        <td><?php echo htmlspecialchars($customer->getName()); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo htmlspecialchars($customer->getEmail()); ?></td>
                                    </tr>
                                </table>
                                <a href="/Order/customerDetail/<?php echo $customer->getId(); ?>" class="btn btn-outline-primary mt-2">
                                    <i class="bi bi-person"></i> View Customer Profile
                                </a>
                            <?php else: ?>
                                <p class="text-muted">Customer information not available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Shipping Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($order->getShippingAddress()); ?></p>
                                    <p><strong>City:</strong> <?php echo htmlspecialchars($order->getShippingCity()); ?></p>
                                    <p><strong>State:</strong> <?php echo htmlspecialchars($order->getShippingState()); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>ZIP Code:</strong> <?php echo htmlspecialchars($order->getShippingZip()); ?></p>
                                    <p><strong>Country:</strong> <?php echo htmlspecialchars($order->getShippingCountry()); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Order Items</h5>
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
                                                    <?php if ($product): ?>
                                                        <a href="/Product/detail/<?php echo $product->getID(); ?>"><?php echo htmlspecialchars($productName); ?></a>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($productName); ?>
                                                    <?php endif; ?>
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
                                        <tr>
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

<?php include 'app/views/layouts/admin_footer.php'; ?>
