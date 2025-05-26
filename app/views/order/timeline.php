<?php
require_once 'app/views/layouts/customer_header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">
                            <i class="fas fa-route"></i> Order Timeline
                        </h3>
                        <div>
                            <span class="badge bg-light text-dark fs-6">
                                Order #<?= htmlspecialchars($order->getOrderNumber() ?: $order->getId()) ?>
                            </span>
                            <a href="/Order/view/<?= $order->getId() ?>" class="btn btn-light btn-sm ms-2">
                                <i class="fas fa-arrow-left"></i> Back to Order
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Order Summary Header -->
                    <div class="bg-light p-4 border-bottom">
                        <div class="row">
                            <div class="col-md-3">
                                <h6 class="text-muted mb-1">Current Status</h6>
                                <span class="badge bg-<?= getStatusColor($order->getStatus()) ?> fs-6">
                                    <?= $order->getStatusDisplayName() ?>
                                </span>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted mb-1">Order Date</h6>
                                <p class="mb-0"><?= date('M d, Y g:i A', strtotime($order->getCreatedAt())) ?></p>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted mb-1">Total Amount</h6>
                                <p class="mb-0 fw-bold text-success">$<?= number_format($order->getTotalAmount(), 2) ?></p>
                            </div>
                            <div class="col-md-3">
                                <?php if ($order->getEstimatedDeliveryDate()): ?>
                                    <h6 class="text-muted mb-1">Estimated Delivery</h6>
                                    <p class="mb-0"><?= date('M d, Y', strtotime($order->getEstimatedDeliveryDate())) ?></p>
                                <?php else: ?>
                                    <h6 class="text-muted mb-1">Payment Method</h6>
                                    <p class="mb-0"><?= ucwords(str_replace('_', ' ', $order->getPaymentMethod())) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Timeline -->
                    <div class="timeline-container p-4">
                        <div class="timeline-wrapper">
                            <?php
                            // Define all possible order stages
                            $orderStages = [
                                'pending' => [
                                    'title' => 'Order Placed',
                                    'description' => 'Your order has been received and is being reviewed',
                                    'icon' => 'shopping-cart',
                                    'color' => 'warning'
                                ],
                                'confirmed' => [
                                    'title' => 'Order Confirmed',
                                    'description' => 'Your order has been confirmed and payment verified',
                                    'icon' => 'check-circle',
                                    'color' => 'info'
                                ],
                                'processing' => [
                                    'title' => 'Processing',
                                    'description' => 'Your order is being prepared for shipment',
                                    'icon' => 'cogs',
                                    'color' => 'primary'
                                ],
                                'packed' => [
                                    'title' => 'Packed',
                                    'description' => 'Your order has been packed and ready for pickup',
                                    'icon' => 'box',
                                    'color' => 'primary'
                                ],
                                'shipped' => [
                                    'title' => 'Shipped',
                                    'description' => 'Your order is on its way to you',
                                    'icon' => 'truck',
                                    'color' => 'info'
                                ],
                                'out_for_delivery' => [
                                    'title' => 'Out for Delivery',
                                    'description' => 'Your order is out for delivery and will arrive soon',
                                    'icon' => 'shipping-fast',
                                    'color' => 'warning'
                                ],
                                'delivered' => [
                                    'title' => 'Delivered',
                                    'description' => 'Your order has been successfully delivered',
                                    'icon' => 'home',
                                    'color' => 'success'
                                ]
                            ];

                            $currentStatus = $order->getStatus();
                            $statusKeys = array_keys($orderStages);
                            $currentIndex = array_search($currentStatus, $statusKeys);
                            
                            // Get status history and tracking history
                            $order->loadStatusHistory();
                            $statusHistory = $order->getStatusHistory();
                            $trackingHistory = OrderTrackingModel::getByOrderId($order->getId());
                            
                            // Create timeline events
                            $timelineEvents = [];
                            
                            // Add status history events
                            foreach ($statusHistory as $history) {
                                $timelineEvents[] = [
                                    'type' => 'status',
                                    'status' => $history->getNewStatus(),
                                    'title' => $orderStages[$history->getNewStatus()]['title'] ?? ucfirst($history->getNewStatus()),
                                    'description' => $history->getChangeReason() ?: ($orderStages[$history->getNewStatus()]['description'] ?? ''),
                                    'icon' => $orderStages[$history->getNewStatus()]['icon'] ?? 'circle',
                                    'color' => $orderStages[$history->getNewStatus()]['color'] ?? 'secondary',
                                    'timestamp' => $history->getCreatedAt(),
                                    'notes' => $history->getNotes(),
                                    'changed_by' => $history->changedByName ?? 'System'
                                ];
                            }
                            
                            // Add tracking events
                            foreach ($trackingHistory as $tracking) {
                                $timelineEvents[] = [
                                    'type' => 'tracking',
                                    'status' => $tracking->getStatus(),
                                    'title' => ucfirst(str_replace('_', ' ', $tracking->getStatus())),
                                    'description' => $tracking->getDescription(),
                                    'location' => $tracking->getLocation(),
                                    'icon' => getTrackingIcon($tracking->getStatus()),
                                    'color' => 'info',
                                    'timestamp' => $tracking->getTrackingDate(),
                                    'carrier' => $tracking->getCarrier(),
                                    'tracking_number' => $tracking->getTrackingNumber()
                                ];
                            }
                            
                            // Sort events by timestamp
                            usort($timelineEvents, function($a, $b) {
                                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
                            });
                            ?>

                            <!-- Progress Bar -->
                            <div class="progress-section mb-5">
                                <div class="progress mb-3" style="height: 8px;">
                                    <?php 
                                    $progressPercentage = $currentIndex !== false ? (($currentIndex + 1) / count($statusKeys)) * 100 : 0;
                                    if ($currentStatus === 'cancelled' || $currentStatus === 'returned') {
                                        $progressPercentage = 100;
                                    }
                                    ?>
                                    <div class="progress-bar bg-<?= $currentStatus === 'delivered' ? 'success' : ($currentStatus === 'cancelled' ? 'danger' : 'primary') ?>" 
                                         style="width: <?= $progressPercentage ?>%"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between progress-steps">
                                    <?php foreach ($orderStages as $status => $stage): ?>
                                        <?php 
                                        $stepIndex = array_search($status, $statusKeys);
                                        $isCompleted = $stepIndex <= $currentIndex;
                                        $isCurrent = $status === $currentStatus;
                                        $isSkipped = !$isCompleted && $currentIndex !== false && $stepIndex < $currentIndex;
                                        ?>
                                        <div class="progress-step <?= $isCompleted ? 'completed' : '' ?> <?= $isCurrent ? 'current' : '' ?>">
                                            <div class="step-circle">
                                                <i class="fas fa-<?= $stage['icon'] ?>"></i>
                                            </div>
                                            <div class="step-label">
                                                <small><?= $stage['title'] ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Timeline Events -->
                            <div class="timeline-events">
                                <h5 class="mb-4">
                                    <i class="fas fa-history"></i> Order History
                                </h5>
                                
                                <?php if (empty($timelineEvents)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-clock text-muted fa-3x mb-3"></i>
                                        <h6 class="text-muted">No tracking information available yet</h6>
                                        <p class="text-muted">Updates will appear here as your order progresses</p>
                                    </div>
                                <?php else: ?>
                                    <div class="timeline">
                                        <?php foreach ($timelineEvents as $index => $event): ?>
                                            <div class="timeline-item <?= $index === 0 ? 'latest' : '' ?>">
                                                <div class="timeline-marker bg-<?= $event['color'] ?>">
                                                    <i class="fas fa-<?= $event['icon'] ?>"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="timeline-header">
                                                        <h6 class="mb-1"><?= htmlspecialchars($event['title']) ?></h6>
                                                        <small class="text-muted">
                                                            <?= date('M d, Y g:i A', strtotime($event['timestamp'])) ?>
                                                        </small>
                                                    </div>
                                                    
                                                    <?php if (!empty($event['description'])): ?>
                                                        <p class="mb-2"><?= htmlspecialchars($event['description']) ?></p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($event['location'])): ?>
                                                        <p class="mb-1">
                                                            <i class="fas fa-map-marker-alt text-muted"></i>
                                                            <strong>Location:</strong> <?= htmlspecialchars($event['location']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($event['carrier'])): ?>
                                                        <p class="mb-1">
                                                            <i class="fas fa-truck text-muted"></i>
                                                            <strong>Carrier:</strong> <?= htmlspecialchars($event['carrier']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($event['tracking_number'])): ?>
                                                        <p class="mb-1">
                                                            <i class="fas fa-barcode text-muted"></i>
                                                            <strong>Tracking:</strong> 
                                                            <code class="bg-light p-1 rounded"><?= htmlspecialchars($event['tracking_number']) ?></code>
                                                        </p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($event['notes'])): ?>
                                                        <div class="alert alert-light mt-2 mb-0">
                                                            <small><strong>Note:</strong> <?= htmlspecialchars($event['notes']) ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($event['type'] === 'status' && !empty($event['changed_by'])): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-user"></i> Updated by: <?= htmlspecialchars($event['changed_by']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items Summary -->
                    <div class="bg-light p-4 border-top">
                        <h6 class="mb-3">
                            <i class="fas fa-box"></i> Items in this Order
                        </h6>
                        <div class="row">
                            <?php 
                            $order->loadItems();
                            $items = $order->getItems();
                            foreach ($items as $item): 
                                $product = $item->getProduct();
                            ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="d-flex align-items-center">
                                        <?php if ($product && $product->getImage()): ?>
                                            <img src="<?= htmlspecialchars($product->getImage()) ?>" 
                                                 alt="<?= htmlspecialchars($product->getName()) ?>" 
                                                 class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($product ? $product->getName() : 'Unknown Product') ?></h6>
                                            <small class="text-muted">
                                                Qty: <?= $item->getQuantity() ?> × $<?= number_format($item->getPrice(), 2) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.progress-section {
    position: relative;
}

.progress-steps {
    position: relative;
    margin-top: -4px;
}

.progress-step {
    text-align: center;
    flex: 1;
}

.step-circle {
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
    border: 3px solid #e9ecef;
}

.progress-step.completed .step-circle {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.progress-step.current .step-circle {
    background: #007bff;
    color: white;
    border-color: #007bff;
    animation: pulse 2s infinite;
}

.step-label {
    font-size: 0.75rem;
    color: #6c757d;
}

.progress-step.completed .step-label,
.progress-step.current .step-label {
    color: #212529;
    font-weight: 600;
}

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
    background: linear-gradient(to bottom, #007bff, #e9ecef);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-item.latest .timeline-marker {
    animation: pulse 2s infinite;
    box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
}

.timeline-content {
    background: white;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.timeline-content:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.timeline-header {
    display: flex;
    justify-content: between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.timeline-header h6 {
    color: #007bff;
    margin-bottom: 0;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
}

@media (max-width: 768px) {
    .progress-steps {
        flex-wrap: wrap;
    }
    
    .progress-step {
        flex: 0 0 50%;
        margin-bottom: 20px;
    }
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -15px;
        width: 24px;
        height: 24px;
        font-size: 10px;
    }
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
        'out_for_delivery' => 'shipping-fast',
        'delivered' => 'check-circle',
        'exception' => 'exclamation-triangle',
        'returned' => 'undo',
        'departed' => 'plane-departure',
        'arrived' => 'plane-arrival',
        'customs' => 'passport',
        'sorting' => 'sort'
    ];
    return $icons[$status] ?? 'circle';
}

require_once 'app/views/layouts/customer_footer.php';
?>
