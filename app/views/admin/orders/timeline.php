<?php
// Set page variables
$pageTitle = 'Order Timeline - Admin';
$currentPage = 'orders';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/Order/dashboard'],
    ['title' => 'Orders', 'url' => '/Order/list'],
    ['title' => 'Order #' . $order->getId(), 'url' => '/Order/detail/' . $order->getId()],
    ['title' => 'Timeline', 'url' => '']
];

// Include admin header
include 'app/views/layouts/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-route"></i> Order Timeline - Admin View
                        </h4>
                        <div>
                            <span class="badge bg-light text-dark fs-6">
                                Order #<?= htmlspecialchars($order->getOrderNumber() ?: $order->getId()) ?>
                            </span>
                            <a href="/Order/detail/<?= $order->getId() ?>" class="btn btn-light btn-sm ms-2">
                                <i class="fas fa-arrow-left"></i> Back to Order
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="btn-toolbar" role="toolbar">
                                <div class="btn-group me-2" role="group">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTrackingModal">
                                        <i class="fas fa-plus"></i> Add Tracking Update
                                    </button>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                        <i class="fas fa-edit"></i> Update Status
                                    </button>
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                        <i class="fas fa-sticky-note"></i> Add Note
                                    </button>
                                </div>
                                <div class="btn-group" role="group">
                                    <a href="/Order/adminTracking/<?= $order->getId() ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-truck"></i> Tracking View
                                    </a>
                                    <a href="/Order/generateReport?order_id=<?= $order->getId() ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-file-export"></i> Export
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Current Status</h6>
                                    <span class="badge bg-<?= getStatusColor($order->getStatus()) ?> fs-6">
                                        <?= $order->getStatusDisplayName() ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Customer</h6>
                                    <p class="mb-0"><?= htmlspecialchars($customer->getName() ?? 'Guest') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Total Amount</h6>
                                    <p class="mb-0 fw-bold text-success">$<?= number_format($order->getTotalAmount(), 2) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-1">Priority</h6>
                                    <span class="badge bg-<?= getPriorityColor($order->getPriority()) ?>">
                                        <?= ucfirst($order->getPriority()) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mb-4">
                                <i class="fas fa-history"></i> Complete Order Timeline
                            </h5>

                            <?php
                            // Load all timeline data
                            $order->loadStatusHistory();
                            $statusHistory = $order->getStatusHistory();
                            $trackingHistory = OrderTrackingModel::getByOrderId($order->getId());
                            $orderNotes = OrderNotesModel::getByOrderId($order->getId());

                            // Combine all events
                            $allEvents = [];

                            // Add status history
                            foreach ($statusHistory as $history) {
                                $allEvents[] = [
                                    'type' => 'status',
                                    'timestamp' => $history->getCreatedAt(),
                                    'title' => 'Status Changed',
                                    'description' => ($history->getOldStatus() ? ucfirst($history->getOldStatus()) . ' → ' : '') . ucfirst($history->getNewStatus()),
                                    'details' => $history->getChangeReason() ?: $history->getNotes(),
                                    'user' => $history->changedByName ?? 'System',
                                    'icon' => 'exchange-alt',
                                    'color' => 'primary'
                                ];
                            }

                            // Add tracking events
                            foreach ($trackingHistory as $tracking) {
                                $allEvents[] = [
                                    'type' => 'tracking',
                                    'timestamp' => $tracking->getTrackingDate(),
                                    'title' => 'Tracking Update',
                                    'description' => ucfirst(str_replace('_', ' ', $tracking->getStatus())),
                                    'details' => $tracking->getDescription(),
                                    'location' => $tracking->getLocation(),
                                    'carrier' => $tracking->getCarrier(),
                                    'tracking_number' => $tracking->getTrackingNumber(),
                                    'icon' => getTrackingIcon($tracking->getStatus()),
                                    'color' => 'info'
                                ];
                            }

                            // Add notes
                            foreach ($orderNotes as $note) {
                                $allEvents[] = [
                                    'type' => 'note',
                                    'timestamp' => $note->getCreatedAt(),
                                    'title' => $note->getTitle() ?: 'Order Note',
                                    'description' => $note->getContent(),
                                    'note_type' => $note->getNoteType(),
                                    'visible_to_customer' => $note->getIsVisibleToCustomer(),
                                    'priority' => $note->getPriority(),
                                    'user' => $note->authorName ?? 'System',
                                    'icon' => 'sticky-note',
                                    'color' => $note->getNoteType() === 'internal' ? 'warning' : 'success'
                                ];
                            }

                            // Sort by timestamp (newest first)
                            usort($allEvents, function($a, $b) {
                                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
                            });
                            ?>

                            <?php if (empty($allEvents)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-clock text-muted fa-3x mb-3"></i>
                                    <h6 class="text-muted">No timeline events yet</h6>
                                    <p class="text-muted">Events will appear here as the order progresses</p>
                                </div>
                            <?php else: ?>
                                <div class="admin-timeline">
                                    <?php foreach ($allEvents as $index => $event): ?>
                                        <div class="timeline-item <?= $index === 0 ? 'latest' : '' ?>">
                                            <div class="timeline-marker bg-<?= $event['color'] ?>">
                                                <i class="fas fa-<?= $event['icon'] ?>"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1"><?= htmlspecialchars($event['title']) ?></h6>
                                                            <p class="mb-1 text-primary"><?= htmlspecialchars($event['description']) ?></p>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="text-muted">
                                                                <?= date('M d, Y', strtotime($event['timestamp'])) ?><br>
                                                                <?= date('g:i A', strtotime($event['timestamp'])) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php if (!empty($event['details'])): ?>
                                                    <p class="mb-2"><?= htmlspecialchars($event['details']) ?></p>
                                                <?php endif; ?>

                                                <?php if ($event['type'] === 'tracking'): ?>
                                                    <div class="row">
                                                        <?php if (!empty($event['location'])): ?>
                                                            <div class="col-md-6">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-map-marker-alt"></i>
                                                                    <strong>Location:</strong> <?= htmlspecialchars($event['location']) ?>
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($event['carrier'])): ?>
                                                            <div class="col-md-6">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-truck"></i>
                                                                    <strong>Carrier:</strong> <?= htmlspecialchars($event['carrier']) ?>
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($event['type'] === 'note'): ?>
                                                    <div class="mt-2">
                                                        <span class="badge bg-<?= $event['note_type'] === 'internal' ? 'secondary' : 'info' ?>">
                                                            <?= ucfirst($event['note_type']) ?>
                                                        </span>
                                                        <?php if ($event['visible_to_customer']): ?>
                                                            <span class="badge bg-success">Visible to Customer</span>
                                                        <?php endif; ?>
                                                        <span class="badge bg-<?= getPriorityColor($event['priority']) ?>">
                                                            <?= ucfirst($event['priority']) ?> Priority
                                                        </span>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i>
                                                        <?= htmlspecialchars($event['user'] ?? 'System') ?>
                                                        <span class="badge bg-light text-dark ms-2"><?= ucfirst($event['type']) ?></span>
                                                    </small>
                                                </div>
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

<!-- Add Tracking Modal -->
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
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="picked_up">Picked Up</option>
                            <option value="in_transit">In Transit</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="exception">Exception</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/Order/updateStatusWithHistory/<?= $order->getId() ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_status" class="form-label">New Status</label>
                        <select class="form-control" id="new_status" name="status" required>
                            <option value="pending" <?= $order->getStatus() === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $order->getStatus() === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="processing" <?= $order->getStatus() === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="packed" <?= $order->getStatus() === 'packed' ? 'selected' : '' ?>>Packed</option>
                            <option value="shipped" <?= $order->getStatus() === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order->getStatus() === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order->getStatus() === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Change</label>
                        <input type="text" class="form-control" id="reason" name="reason">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/Order/addNote/<?= $order->getId() ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Order Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note_title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="note_title" name="title">
                    </div>
                    <div class="mb-3">
                        <label for="note_content" class="form-label">Content</label>
                        <textarea class="form-control" id="note_content" name="content" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="note_type" class="form-label">Type</label>
                        <select class="form-control" id="note_type" name="note_type">
                            <option value="internal">Internal Note</option>
                            <option value="customer">Customer Note</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="visible_to_customer" name="visible_to_customer">
                            <label class="form-check-label" for="visible_to_customer">
                                Visible to Customer
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.admin-timeline {
    position: relative;
    padding-left: 30px;
}

.admin-timeline::before {
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
}

.timeline-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
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

function getPriorityColor($priority) {
    $colors = [
        'low' => 'success',
        'normal' => 'primary',
        'high' => 'warning',
        'urgent' => 'danger'
    ];
    return $colors[$priority] ?? 'primary';
}

function getTrackingIcon($status) {
    $icons = [
        'picked_up' => 'box',
        'in_transit' => 'truck',
        'out_for_delivery' => 'shipping-fast',
        'delivered' => 'check-circle',
        'exception' => 'exclamation-triangle',
        'returned' => 'undo'
    ];
    return $icons[$status] ?? 'circle';
}

include 'app/views/layouts/admin_footer.php';
?>
