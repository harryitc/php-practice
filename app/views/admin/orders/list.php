<?php
require_once 'app/models/UserModel.php';

// Set page variables
$pageTitle = 'Order Management';
$currentPage = 'orders';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/Order/dashboard'],
    ['title' => 'Orders', 'url' => '/Order/list']
];

// Include admin header
include 'app/views/layouts/admin_header.php';
?>

            <!-- Order Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Filter Orders</h5>
                        </div>
                        <div class="card-body">
                            <form action="/Order/list" method="get" class="row g-3">
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo $status === $selectedStatus ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($status); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-filter"></i> Apply Filters
                                    </button>
                                    <?php if (!empty($selectedStatus)): ?>
                                        <a href="/Order/list" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Clear Filters
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders List -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Orders</h5>
                                <span class="badge bg-primary"><?php echo $totalOrders; ?> Orders</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($orders) > 0): ?>
                                            <?php foreach ($orders as $order): ?>
                                                <?php
                                                    // Get customer name
                                                    $userId = $order->getUserId();
                                                    $customerName = 'Unknown';
                                                    if ($userId) {
                                                        $customer = (new UserModel())->findById($userId);
                                                        if ($customer) {
                                                            $customerName = $customer->getName();
                                                        }
                                                    }

                                                    // Determine status badge class
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
                                                <tr>
                                                    <td>#<?php echo $order->getId(); ?></td>
                                                    <td><?php echo htmlspecialchars($customerName); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($order->getCreatedAt())); ?></td>
                                                    <td>$<?php echo number_format($order->getTotalAmount(), 2); ?></td>
                                                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($order->getStatus()); ?></span></td>
                                                    <td>
                                                        <a href="/Order/detail/<?php echo $order->getId(); ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                                    <p class="mt-2 mb-0">No orders found</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="/Order/list?page=1<?php echo !empty($selectedStatus) ? '&status=' . urlencode($selectedStatus) : ''; ?>" aria-label="First">
                                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="/Order/list?page=<?php echo $currentPage - 1; ?><?php echo !empty($selectedStatus) ? '&status=' . urlencode($selectedStatus) : ''; ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php
                                        // Calculate range of page numbers to display
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $startPage + 4);

                                        if ($endPage - $startPage < 4) {
                                            $startPage = max(1, $endPage - 4);
                                        }

                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                                <a class="page-link" href="/Order/list?page=<?php echo $i; ?><?php echo !empty($selectedStatus) ? '&status=' . urlencode($selectedStatus) : ''; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="/Order/list?page=<?php echo $currentPage + 1; ?><?php echo !empty($selectedStatus) ? '&status=' . urlencode($selectedStatus) : ''; ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="/Order/list?page=<?php echo $totalPages; ?><?php echo !empty($selectedStatus) ? '&status=' . urlencode($selectedStatus) : ''; ?>" aria-label="Last">
                                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php include 'app/views/layouts/admin_footer.php'; ?>
