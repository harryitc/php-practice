<?php
require_once 'app/views/layouts/admin_header.php';
?>

<style>
.filter-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

.order-row {
    transition: background-color 0.2s;
}

.order-row:hover {
    background-color: #f8f9fa;
}

.bulk-actions {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 1rem;
    display: none;
}

.status-badge {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.action-buttons .btn {
    margin-right: 0.25rem;
    margin-bottom: 0.25rem;
}
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="text-primary">
                <i class="fas fa-cogs"></i> Order Management
            </h1>
            <p class="text-muted">Advanced order management with bulk actions and filtering</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="/Order/adminDashboard" class="btn btn-outline-primary me-2">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="/Order/analytics" class="btn btn-info">
                <i class="fas fa-analytics"></i> Analytics
            </a>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="card filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="/Order/adminManage" class="row g-3">
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $status): ?>
                        <option value="<?= $status ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $status)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select class="form-select" id="customer_id" name="customer_id">
                        <option value="">All Customers</option>
                        <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>" <?= $filters['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($customer['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?= htmlspecialchars($filters['date_from']) ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?= htmlspecialchars($filters['date_to']) ?>">
                </div>
                <div class="col-md-1">
                    <label for="min_amount" class="form-label">Min $</label>
                    <input type="number" class="form-control" id="min_amount" name="min_amount" 
                           placeholder="0" step="0.01" value="<?= htmlspecialchars($filters['min_amount']) ?>">
                </div>
                <div class="col-md-1">
                    <label for="max_amount" class="form-label">Max $</label>
                    <input type="number" class="form-control" id="max_amount" name="max_amount" 
                           placeholder="1000" step="0.01" value="<?= htmlspecialchars($filters['max_amount']) ?>">
                </div>
                <div class="col-md-2">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Order, customer, product..." value="<?= htmlspecialchars($filters['search']) ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            <div class="row mt-3">
                <div class="col-md-12">
                    <a href="/Order/adminManage" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                    <span class="ms-3 text-muted">
                        Showing <?= count($result['orders']) ?> of <?= $result['total'] ?> orders
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions" id="bulkActions">
        <form method="POST" action="/Order/bulkAction" id="bulkForm">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <strong><span id="selectedCount">0</span> orders selected</strong>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <select class="form-select" name="bulk_action" required>
                            <option value="">Choose action...</option>
                            <option value="confirm">Confirm Orders</option>
                            <option value="process">Start Processing</option>
                            <option value="ship">Mark as Shipped</option>
                            <option value="cancel">Cancel Orders</option>
                            <option value="export">Export Selected</option>
                        </select>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-play"></i> Execute
                        </button>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                        <i class="fas fa-times"></i> Clear Selection
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Orders
                </h5>
                <div>
                    <div class="btn-group btn-group-sm">
                        <input type="checkbox" class="btn-check" id="selectAll" autocomplete="off">
                        <label class="btn btn-outline-primary" for="selectAll">Select All</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($result['orders'])): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="masterCheckbox" class="form-check-input">
                            </th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Products</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['orders'] as $order): ?>
                        <tr class="order-row">
                            <td>
                                <input type="checkbox" class="form-check-input order-checkbox" 
                                       name="order_ids[]" value="<?= $order['id'] ?>">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($order['order_number'] ?: '#' . $order['id']) ?></strong>
                                <br><small class="text-muted">ID: <?= $order['id'] ?></small>
                                <?php if ($order['item_count']): ?>
                                <br><small class="text-info"><?= $order['item_count'] ?> items</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($order['customer_email']) ?></small>
                            </td>
                            <td>
                                <?php if ($order['product_names']): ?>
                                <small><?= htmlspecialchars(substr($order['product_names'], 0, 50)) ?><?= strlen($order['product_names']) > 50 ? '...' : '' ?></small>
                                <?php else: ?>
                                <small class="text-muted">No products</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong>$<?= number_format($order['total_amount'], 2) ?></strong>
                                <br><small class="text-muted"><?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></small>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'processing' => 'primary',
                                    'packed' => 'primary',
                                    'shipped' => 'info',
                                    'out_for_delivery' => 'warning',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                    'returned' => 'secondary'
                                ];
                                $statusColor = $statusColors[$order['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $statusColor ?> status-badge">
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?= date('M j, Y', strtotime($order['created_at'])) ?>
                                <br><small class="text-muted"><?= date('g:i A', strtotime($order['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="/Order/detail/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/Order/adminTracking/<?= $order['id'] ?>" class="btn btn-sm btn-outline-info" title="Tracking">
                                        <i class="fas fa-truck"></i>
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php if ($order['status'] === 'pending'): ?>
                                            <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order['id'] ?>, 'confirmed')">
                                                <i class="fas fa-check"></i> Confirm Order
                                            </a></li>
                                            <?php endif; ?>
                                            <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                            <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order['id'] ?>, 'processing')">
                                                <i class="fas fa-cog"></i> Start Processing
                                            </a></li>
                                            <?php endif; ?>
                                            <?php if (in_array($order['status'], ['confirmed', 'processing', 'packed'])): ?>
                                            <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order['id'] ?>, 'shipped')">
                                                <i class="fas fa-shipping-fast"></i> Mark as Shipped
                                            </a></li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="/Order/adminTimeline/<?= $order['id'] ?>">
                                                <i class="fas fa-history"></i> View Timeline
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="addOrderNote(<?= $order['id'] ?>)">
                                                <i class="fas fa-sticky-note"></i> Add Note
                                            </a></li>
                                            <?php if (!in_array($order['status'], ['delivered', 'cancelled'])): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="cancelOrder(<?= $order['id'] ?>)">
                                                <i class="fas fa-times"></i> Cancel Order
                                            </a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($result['total_pages'] > 1): ?>
            <div class="card-footer">
                <nav aria-label="Orders pagination">
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($result['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $result['current_page'] - 1 ?>&<?= http_build_query($filters) ?>">
                                Previous
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $result['current_page'] - 2); $i <= min($result['total_pages'], $result['current_page'] + 2); $i++): ?>
                        <li class="page-item <?= $i == $result['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($filters) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($result['current_page'] < $result['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $result['current_page'] + 1 ?>&<?= http_build_query($filters) ?>">
                                Next
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Page <?= $result['current_page'] ?> of <?= $result['total_pages'] ?> 
                        (<?= $result['total'] ?> total orders)
                    </small>
                </div>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3">No orders found</h5>
                <p class="text-muted">No orders match your current filters.</p>
                <a href="/Order/adminManage" class="btn btn-primary">
                    <i class="fas fa-refresh"></i> Clear Filters
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'app/views/layouts/admin_footer.php'; ?>

<script>
// Checkbox management
document.addEventListener('DOMContentLoaded', function() {
    const masterCheckbox = document.getElementById('masterCheckbox');
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    const bulkForm = document.getElementById('bulkForm');

    // Master checkbox functionality
    masterCheckbox.addEventListener('change', function() {
        orderCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Individual checkbox functionality
    orderCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateMasterCheckbox();
            updateBulkActions();
        });
    });

    function updateMasterCheckbox() {
        const checkedCount = document.querySelectorAll('.order-checkbox:checked').length;
        masterCheckbox.checked = checkedCount === orderCheckboxes.length;
        masterCheckbox.indeterminate = checkedCount > 0 && checkedCount < orderCheckboxes.length;
    }

    function updateBulkActions() {
        const checkedCount = document.querySelectorAll('.order-checkbox:checked').length;
        selectedCount.textContent = checkedCount;
        
        if (checkedCount > 0) {
            bulkActions.style.display = 'block';
            // Add hidden inputs for selected orders
            const existingInputs = bulkForm.querySelectorAll('input[name="order_ids[]"]');
            existingInputs.forEach(input => input.remove());
            
            document.querySelectorAll('.order-checkbox:checked').forEach(checkbox => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'order_ids[]';
                hiddenInput.value = checkbox.value;
                bulkForm.appendChild(hiddenInput);
            });
        } else {
            bulkActions.style.display = 'none';
        }
    }

    // Bulk form submission
    bulkForm.addEventListener('submit', function(e) {
        const action = this.querySelector('select[name="bulk_action"]').value;
        const checkedCount = document.querySelectorAll('.order-checkbox:checked').length;
        
        if (!action) {
            e.preventDefault();
            alert('Please select an action');
            return;
        }
        
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Please select at least one order');
            return;
        }
        
        const confirmMessage = `Are you sure you want to ${action} ${checkedCount} order(s)?`;
        if (!confirm(confirmMessage)) {
            e.preventDefault();
        }
    });
});

function clearSelection() {
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('masterCheckbox').checked = false;
    document.getElementById('bulkActions').style.display = 'none';
}

function updateOrderStatus(orderId, newStatus) {
    if (confirm(`Are you sure you want to change this order status to ${newStatus}?`)) {
        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/Order/bulkAction';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'bulk_action';
        actionInput.value = newStatus === 'confirmed' ? 'confirm' : 
                           newStatus === 'processing' ? 'process' : 
                           newStatus === 'shipped' ? 'ship' : newStatus;
        
        const orderInput = document.createElement('input');
        orderInput.type = 'hidden';
        orderInput.name = 'order_ids[]';
        orderInput.value = orderId;
        
        form.appendChild(actionInput);
        form.appendChild(orderInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function addOrderNote(orderId) {
    const note = prompt('Enter note for this order:');
    if (note) {
        // Implementation for adding note
        alert('Note functionality will be implemented');
    }
}

function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        updateOrderStatus(orderId, 'cancel');
    }
}
</script>
