<?php
require_once 'app/views/layouts/customer_header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-shipping-fast"></i> Track Your Order
                        </h4>
                        <a href="/Order/view/<?= $order->getId() ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Order
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Order Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Order Information</h6>
                            <div class="border-start border-primary border-3 ps-3">
                                <p class="mb-1"><strong>Order #:</strong> <?= htmlspecialchars($order->getOrderNumber()) ?></p>
                                <p class="mb-1"><strong>Status:</strong>
                                    <span class="badge bg-<?= getStatusColor($order->getStatus()) ?> fs-6">
                                        <?= $order->getStatusDisplayName() ?>
                                    </span>
                                </p>
                                <p class="mb-0"><strong>Order Date:</strong> <?= date('M d, Y', strtotime($order->getCreatedAt())) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Shipping Information</h6>
                            <div class="border-start border-success border-3 ps-3">
                                <?php if ($order->getTrackingNumber()): ?>
                                    <p class="mb-1"><strong>Tracking #:</strong>
                                        <code class="bg-light p-1 rounded"><?= htmlspecialchars($order->getTrackingNumber()) ?></code>
                                    </p>
                                <?php endif; ?>
                                <?php if ($order->getCarrier()): ?>
                                    <p class="mb-1"><strong>Carrier:</strong> <?= htmlspecialchars($order->getCarrier()) ?></p>
                                <?php endif; ?>
                                <?php if (isset($estimatedDelivery) && $estimatedDelivery): ?>
                                    <p class="mb-0"><strong>Estimated Delivery:</strong>
                                        <?= date('M d, Y', strtotime($estimatedDelivery)) ?>
                                        <?php if (isset($isDelayed) && $isDelayed): ?>
                                            <span class="badge bg-warning ms-2">Delayed</span>
                                        <?php endif; ?>
                                    </p>
                                <?php elseif ($order->getEstimatedDeliveryDate()): ?>
                                    <p class="mb-0"><strong>Estimated Delivery:</strong>
                                        <?= date('M d, Y', strtotime($order->getEstimatedDeliveryDate())) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Order Progress</h6>
                        <div class="progress-container">
                            <?php
                            $statusSteps = [
                                'pending' => ['label' => 'Order Placed', 'icon' => 'shopping-cart'],
                                'confirmed' => ['label' => 'Confirmed', 'icon' => 'check-circle'],
                                'processing' => ['label' => 'Processing', 'icon' => 'cogs'],
                                'packed' => ['label' => 'Packed', 'icon' => 'box'],
                                'shipped' => ['label' => 'Shipped', 'icon' => 'truck'],
                                'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'shipping-fast'],
                                'delivered' => ['label' => 'Delivered', 'icon' => 'home']
                            ];

                            $currentStatus = $order->getStatus();
                            $statusKeys = array_keys($statusSteps);
                            $currentIndex = array_search($currentStatus, $statusKeys);
                            $progressPercentage = $currentIndex !== false ? (($currentIndex + 1) / count($statusKeys)) * 100 : 0;
                            ?>

                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?= $progressPercentage ?? $progressPercentage ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <?php foreach ($statusSteps as $status => $step): ?>
                                    <?php
                                    $stepIndex = array_search($status, $statusKeys);
                                    $isCompleted = $stepIndex <= $currentIndex;
                                    $isCurrent = $status === $currentStatus;
                                    ?>
                                    <div class="text-center progress-step <?= $isCompleted ? 'completed' : '' ?> <?= $isCurrent ? 'current' : '' ?>">
                                        <div class="step-icon">
                                            <i class="fas fa-<?= $step['icon'] ?>"></i>
                                        </div>
                                        <small class="step-label"><?= $step['label'] ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Next Update Info -->
                    <?php if (isset($nextUpdateExpected) && $nextUpdateExpected): ?>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-clock"></i>
                                    <strong>Next Update Expected:</strong>
                                    <?= date('M d, Y g:i A', strtotime($nextUpdateExpected)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tracking Timeline -->
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3">Tracking Updates</h6>
                            <?php if (empty($trackingHistory)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Tracking information will be updated once your order is shipped.
                                </div>
                            <?php else: ?>
                                <div class="tracking-timeline">
                                    <?php foreach ($trackingHistory as $index => $tracking): ?>
                                        <div class="timeline-item <?= $index === 0 ? 'latest' : '' ?>">
                                            <div class="timeline-marker">
                                                <i class="fas fa-<?= getTrackingIcon($tracking->getStatus()) ?>"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 text-primary">
                                                            <?= htmlspecialchars($tracking->getStatusDisplayName()) ?>
                                                        </h6>
                                                        <?php if ($tracking->getLocation()): ?>
                                                            <p class="mb-1 text-muted">
                                                                <i class="fas fa-map-marker-alt"></i>
                                                                <?= htmlspecialchars($tracking->getLocation()) ?>
                                                            </p>
                                                        <?php endif; ?>
                                                        <?php if ($tracking->getDescription()): ?>
                                                            <p class="mb-0"><?= htmlspecialchars($tracking->getDescription()) ?></p>
                                                        <?php endif; ?>
                                                        <?php if ($tracking->getIsDelivered() && $tracking->getRecipientName()): ?>
                                                            <p class="mb-0 text-success">
                                                                <i class="fas fa-user-check"></i>
                                                                Received by: <?= htmlspecialchars($tracking->getRecipientName()) ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-muted">
                                                            <?= date('M d, Y', strtotime($tracking->getTrackingDate())) ?><br>
                                                            <?= date('g:i A', strtotime($tracking->getTrackingDate())) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Customer Notes -->
                    <?php if (!empty($customerNotes)): ?>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h6 class="text-muted mb-3">Order Notes</h6>
                                <?php foreach ($customerNotes as $note): ?>
                                    <div class="alert alert-light border-start border-info border-3">
                                        <?php if ($note->getTitle()): ?>
                                            <h6 class="alert-heading"><?= htmlspecialchars($note->getTitle()) ?></h6>
                                        <?php endif; ?>
                                        <p class="mb-1"><?= nl2br(htmlspecialchars($note->getContent())) ?></p>
                                        <small class="text-muted">
                                            <?= date('M d, Y g:i A', strtotime($note->getCreatedAt())) ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Shipping Address -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Shipping Address</h6>
                            <div class="border rounded p-3 bg-light">
                                <address class="mb-0">
                                    <?= nl2br(htmlspecialchars($order->getShippingAddress())) ?><br>
                                    <?= htmlspecialchars($order->getShippingCity()) ?>,
                                    <?= htmlspecialchars($order->getShippingState()) ?>
                                    <?= htmlspecialchars($order->getShippingZip()) ?><br>
                                    <?= htmlspecialchars($order->getShippingCountry()) ?>
                                </address>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Need Help?</h6>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-2">
                                    <i class="fas fa-phone text-primary"></i>
                                    <strong>Customer Service:</strong> 1-800-123-4567
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-envelope text-primary"></i>
                                    <strong>Email:</strong> support@yourstore.com
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-clock text-primary"></i>
                                    <strong>Hours:</strong> Mon-Fri 9AM-6PM
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress-container {
    position: relative;
}

.progress-step {
    flex: 1;
    position: relative;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    transition: all 0.3s ease;
}

.progress-step.completed .step-icon {
    background: #198754;
    color: white;
}

.progress-step.current .step-icon {
    background: #0d6efd;
    color: white;
    animation: pulse 2s infinite;
}

.step-label {
    display: block;
    font-size: 0.75rem;
    color: #6c757d;
}

.progress-step.completed .step-label,
.progress-step.current .step-label {
    color: #212529;
    font-weight: 600;
}

.tracking-timeline {
    position: relative;
    padding-left: 30px;
}

.tracking-timeline::before {
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
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
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

.timeline-item.latest .timeline-marker {
    background: #198754;
    animation: pulse 2s infinite;
}

.timeline-content {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
    100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
}
</style>

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

require_once 'app/views/layouts/customer_footer.php';
?>
