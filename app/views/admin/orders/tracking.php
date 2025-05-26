<?php
require_once 'app/views/layouts/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Order Tracking - <?= htmlspecialchars($order->getOrderNumber()) ?></h4>
                    <div>
                        <a href="/Order/detail/<?= $order->getId() ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Order
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTrackingModal">
                            <i class="fas fa-plus"></i> Add Tracking Update
                        </button>
                        <a href="/Order/simulateTracking/<?= $order->getId() ?>" class="btn btn-warning">
                            <i class="fas fa-play"></i> Simulate Update
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Order Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p><strong>Order Number:</strong> <?= htmlspecialchars($order->getOrderNumber()) ?></p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-<?= getStatusColor($order->getStatus()) ?>">
                                    <?= $order->getStatusDisplayName() ?>
                                </span>
                            </p>
                            <p><strong>Customer:</strong> <?= htmlspecialchars($customer->getName() ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Shipping Information</h6>
                            <p><strong>Tracking Number:</strong> <?= htmlspecialchars($order->getTrackingNumber() ?: 'Not assigned') ?></p>
                            <p><strong>Carrier:</strong> <?= htmlspecialchars($order->getCarrier() ?: 'Not assigned') ?></p>
                            <p><strong>Estimated Delivery:</strong> <?= $order->getEstimatedDeliveryDate() ? date('M d, Y', strtotime($order->getEstimatedDeliveryDate())) : 'Not set' ?></p>
                        </div>
                    </div>

                    <!-- Tracking Timeline -->
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Tracking Timeline</h6>
                            <?php if (empty($trackingHistory)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No tracking updates available yet.
                                </div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($trackingHistory as $index => $tracking): ?>
                                        <div class="timeline-item <?= $index === 0 ? 'active' : '' ?>">
                                            <div class="timeline-marker">
                                                <i class="fas fa-<?= getTrackingIcon($tracking->getStatus()) ?>"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <h6 class="mb-1"><?= htmlspecialchars($tracking->getStatusDisplayName()) ?></h6>
                                                    <small class="text-muted">
                                                        <?= date('M d, Y g:i A', strtotime($tracking->getTrackingDate())) ?>
                                                    </small>
                                                </div>
                                                <?php if ($tracking->getLocation()): ?>
                                                    <p class="mb-1">
                                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                                        <?= htmlspecialchars($tracking->getLocation()) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if ($tracking->getDescription()): ?>
                                                    <p class="mb-0"><?= htmlspecialchars($tracking->getDescription()) ?></p>
                                                <?php endif; ?>
                                                <?php if ($tracking->getIsDelivered()): ?>
                                                    <div class="mt-2">
                                                        <span class="badge bg-success">Delivered</span>
                                                        <?php if ($tracking->getRecipientName()): ?>
                                                            <small class="text-muted">
                                                                Received by: <?= htmlspecialchars($tracking->getRecipientName()) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Tracking Update Modal -->
<div class="modal fade" id="addTrackingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/Order/addTracking/<?= $order->getId() ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Tracking Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tracking_number" class="form-label">Tracking Number</label>
                        <input type="text" class="form-control" id="tracking_number" name="tracking_number"
                               value="<?= htmlspecialchars($order->getTrackingNumber() ?: '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="carrier" class="form-label">Carrier</label>
                        <select class="form-control" id="carrier" name="carrier">
                            <option value="">Select Carrier</option>
                            <option value="FedEx" <?= $order->getCarrier() === 'FedEx' ? 'selected' : '' ?>>FedEx</option>
                            <option value="UPS" <?= $order->getCarrier() === 'UPS' ? 'selected' : '' ?>>UPS</option>
                            <option value="DHL" <?= $order->getCarrier() === 'DHL' ? 'selected' : '' ?>>DHL</option>
                            <option value="USPS" <?= $order->getCarrier() === 'USPS' ? 'selected' : '' ?>>USPS</option>
                            <option value="Local Delivery" <?= $order->getCarrier() === 'Local Delivery' ? 'selected' : '' ?>>Local Delivery</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="picked_up">Picked Up</option>
                            <option value="in_transit">In Transit</option>
                            <option value="sorting_facility">At Sorting Facility</option>
                            <option value="departed_facility">Departed Facility</option>
                            <option value="customs_clearance">Customs Clearance</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="exception">Exception</option>
                            <option value="delayed">Delayed</option>
                            <option value="returned">Returned</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location"
                               placeholder="e.g., New York, NY">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Additional details about this update"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="tracking_date" class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="tracking_date" name="tracking_date"
                               value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                    <div class="mb-3" id="delivery_fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="recipient_name" class="form-label">Recipient Name</label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="signature_obtained" name="signature_obtained">
                                    <label class="form-check-label" for="signature_obtained">
                                        Signature Obtained
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.timeline-item.active .timeline-marker {
    background: #0d6efd;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #dee2e6;
}

.timeline-item.active .timeline-content {
    border-left-color: #0d6efd;
}
</style>

<script>
document.getElementById('status').addEventListener('change', function() {
    const deliveryFields = document.getElementById('delivery_fields');
    if (this.value === 'delivered') {
        deliveryFields.style.display = 'block';
    } else {
        deliveryFields.style.display = 'none';
    }
});
</script>

<?php
function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'processing' => 'primary',
        'packed' => 'primary',
        'shipped' => 'info',
        'out_for_delivery' => 'warning',
        'delivered' => 'success',
        'cancelled' => 'danger',
        'returned' => 'secondary',
        'refunded' => 'dark'
    ];
    return $colors[$status] ?? 'secondary';
}

function getTrackingIcon($status) {
    $icons = [
        'picked_up' => 'box',
        'in_transit' => 'truck',
        'sorting_facility' => 'warehouse',
        'departed_facility' => 'arrow-right',
        'customs_clearance' => 'passport',
        'out_for_delivery' => 'shipping-fast',
        'delivered' => 'check-circle',
        'exception' => 'exclamation-triangle',
        'delayed' => 'clock',
        'returned' => 'undo'
    ];
    return $icons[$status] ?? 'circle';
}

require_once 'app/views/layouts/admin_footer.php';
?>
