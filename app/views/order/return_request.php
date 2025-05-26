<?php
// Set page variables
$pageTitle = 'Request Return - ShopEasy';
$currentPage = 'return-request';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => 'My Orders', 'url' => '/Order/myOrders'],
    ['title' => 'Request Return', 'url' => '']
];

// Include customer header
include 'app/views/layouts/customer_header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="bi bi-arrow-return-left"></i> Request Return/Refund
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Order Information -->
                    <div class="alert alert-info">
                        <h5>Order Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Order Number:</strong> <?= htmlspecialchars($order->getOrderNumber() ?: 'Order #' . $order->getId()) ?><br>
                                <strong>Order Date:</strong> <?= date('M j, Y', strtotime($order->getCreatedAt())) ?><br>
                                <strong>Delivery Date:</strong> <?= $order->getActualDeliveryDate() ? date('M j, Y', strtotime($order->getActualDeliveryDate())) : 'N/A' ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Total Amount:</strong> $<?= number_format($order->getTotalAmount(), 2) ?><br>
                                <strong>Payment Method:</strong> <?= ucwords(str_replace('_', ' ', $order->getPaymentMethod())) ?><br>
                                <strong>Status:</strong> <?= htmlspecialchars($order->getStatusDisplayName()) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Return Form -->
                    <form method="POST" action="/Order/requestReturn/<?= $order->getId() ?>" enctype="multipart/form-data">
                        <!-- Return Reason -->
                        <div class="mb-4">
                            <label for="reason" class="form-label">
                                <strong>Reason for Return <span class="text-danger">*</span></strong>
                            </label>
                            <select class="form-select" id="reason" name="reason" required>
                                <option value="">Please select a reason</option>
                                <option value="defective">Product is defective/damaged</option>
                                <option value="wrong_item">Wrong item received</option>
                                <option value="not_as_described">Item not as described</option>
                                <option value="size_issue">Size/fit issue</option>
                                <option value="quality_issue">Quality not as expected</option>
                                <option value="changed_mind">Changed my mind</option>
                                <option value="duplicate_order">Duplicate order</option>
                                <option value="other">Other reason</option>
                            </select>
                        </div>

                        <!-- Detailed Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                <strong>Detailed Description <span class="text-danger">*</span></strong>
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Please provide detailed information about the issue..." required></textarea>
                            <div class="form-text">Please describe the issue in detail to help us process your return faster.</div>
                        </div>

                        <!-- Items to Return -->
                        <div class="mb-4">
                            <label class="form-label">
                                <strong>Items to Return <span class="text-danger">*</span></strong>
                            </label>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">Return</th>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Return Qty</th>
                                            <th>Refund Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $items = $order->getItems();
                                        $totalRefund = 0;
                                        foreach ($items as $index => $item):
                                            require_once 'app/models/ProductModel.php';
                                            $productModel = new ProductModel();
                                            $product = $productModel->findById($item->getProductId());
                                        ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input item-checkbox" 
                                                       name="items[]" value="<?= $item->getId() ?>" 
                                                       data-price="<?= $item->getPrice() ?>" 
                                                       data-max-qty="<?= $item->getQuantity() ?>">
                                            </td>
                                            <td>
                                                <?= $product ? htmlspecialchars($product->getName()) : 'Product not found' ?>
                                                <?php if ($product && $product->getImageUrl()): ?>
                                                <br><img src="<?= htmlspecialchars($product->getImageUrl()) ?>" 
                                                         alt="Product" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $item->getQuantity() ?></td>
                                            <td>$<?= number_format($item->getPrice(), 2) ?></td>
                                            <td>
                                                <input type="number" class="form-control return-qty" 
                                                       name="return_qty[<?= $item->getId() ?>]" 
                                                       min="0" max="<?= $item->getQuantity() ?>" 
                                                       value="0" disabled>
                                            </td>
                                            <td class="refund-amount">$0.00</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <th colspan="5">Total Refund Amount:</th>
                                            <th id="total-refund">$0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Return Policy -->
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-info-circle"></i> Return Policy</h6>
                            <ul class="mb-0">
                                <li>Items must be returned within 30 days of delivery</li>
                                <li>Items must be in original condition with tags attached</li>
                                <li>Refunds will be processed within 5-7 business days after we receive the items</li>
                                <li>Return shipping costs may apply depending on the reason</li>
                                <li>Some items may not be eligible for return (perishables, personalized items, etc.)</li>
                            </ul>
                        </div>

                        <!-- Photo Upload -->
                        <div class="mb-4">
                            <label for="photos" class="form-label">
                                <strong>Upload Photos (Optional)</strong>
                            </label>
                            <input type="file" class="form-control" id="photos" name="photos[]" 
                                   multiple accept="image/*">
                            <div class="form-text">Upload photos of the item(s) to support your return request (max 5 photos).</div>
                        </div>

                        <!-- Preferred Resolution -->
                        <div class="mb-4">
                            <label for="preferred_resolution" class="form-label">
                                <strong>Preferred Resolution</strong>
                            </label>
                            <select class="form-select" id="preferred_resolution" name="preferred_resolution">
                                <option value="refund">Full refund to original payment method</option>
                                <option value="exchange">Exchange for same item</option>
                                <option value="store_credit">Store credit</option>
                                <option value="replacement">Replacement item</option>
                            </select>
                        </div>

                        <!-- Confirmation -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirm_return" required>
                            <label class="form-check-label" for="confirm_return">
                                I confirm that the information provided is accurate and I agree to the return policy terms
                            </label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="/Order/view/<?= $order->getId() ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Order
                            </a>
                            <div>
                                <a href="/Order/myOrders" class="btn btn-outline-secondary me-2">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-warning" id="submit-btn" disabled>
                                    <i class="bi bi-arrow-return-left"></i> Submit Return Request
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/customer_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const returnQtyInputs = document.querySelectorAll('.return-qty');
    const confirmCheckbox = document.getElementById('confirm_return');
    const submitBtn = document.getElementById('submit-btn');
    
    // Handle item selection
    itemCheckboxes.forEach(function(checkbox, index) {
        checkbox.addEventListener('change', function() {
            const qtyInput = returnQtyInputs[index];
            const refundCell = this.closest('tr').querySelector('.refund-amount');
            
            if (this.checked) {
                qtyInput.disabled = false;
                qtyInput.value = this.dataset.maxQty;
                updateRefundAmount(this.closest('tr'));
            } else {
                qtyInput.disabled = true;
                qtyInput.value = 0;
                refundCell.textContent = '$0.00';
            }
            
            updateTotalRefund();
            updateSubmitButton();
        });
    });
    
    // Handle quantity changes
    returnQtyInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            updateRefundAmount(this.closest('tr'));
            updateTotalRefund();
        });
    });
    
    // Handle confirmation checkbox
    confirmCheckbox.addEventListener('change', updateSubmitButton);
    
    function updateRefundAmount(row) {
        const checkbox = row.querySelector('.item-checkbox');
        const qtyInput = row.querySelector('.return-qty');
        const refundCell = row.querySelector('.refund-amount');
        
        if (checkbox.checked && qtyInput.value > 0) {
            const price = parseFloat(checkbox.dataset.price);
            const qty = parseInt(qtyInput.value);
            const refund = price * qty;
            refundCell.textContent = '$' + refund.toFixed(2);
        } else {
            refundCell.textContent = '$0.00';
        }
    }
    
    function updateTotalRefund() {
        let total = 0;
        document.querySelectorAll('.refund-amount').forEach(function(cell) {
            const amount = parseFloat(cell.textContent.replace('$', ''));
            total += amount;
        });
        
        document.getElementById('total-refund').textContent = '$' + total.toFixed(2);
    }
    
    function updateSubmitButton() {
        const hasSelectedItems = Array.from(itemCheckboxes).some(cb => cb.checked);
        const isConfirmed = confirmCheckbox.checked;
        
        submitBtn.disabled = !(hasSelectedItems && isConfirmed);
    }
    
    // Form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        const selectedItems = Array.from(itemCheckboxes).filter(cb => cb.checked);
        
        if (selectedItems.length === 0) {
            e.preventDefault();
            alert('Please select at least one item to return.');
            return;
        }
        
        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Submitting...';
    });
});
</script>
