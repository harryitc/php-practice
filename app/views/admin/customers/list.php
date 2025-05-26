<?php
require_once 'app/models/UserModel.php';

// Set page variables
$pageTitle = 'Customer Management';
$currentPage = 'customers';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/Order/dashboard'],
    ['title' => 'Customers', 'url' => '/Order/customers']
];

// Include admin header
include 'app/views/layouts/admin_header.php';
?>

            <!-- Customers List -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Customers</h5>
                                <span class="badge bg-primary"><?php echo count($customers); ?> Customers</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Registered</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($customers) > 0): ?>
                                            <?php foreach ($customers as $customer): ?>
                                                <tr>
                                                    <td><?php echo $customer->getId(); ?></td>
                                                    <td><?php echo htmlspecialchars($customer->getName()); ?></td>
                                                    <td><?php echo htmlspecialchars($customer->getEmail()); ?></td>
                                                    <td>
                                                        <?php if ($customer->getRole() === 'admin'): ?>
                                                            <span class="badge bg-danger">Admin</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-info text-dark">Customer</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($customer->getCreatedAt())); ?></td>
                                                    <td>
                                                        <a href="/Order/customerDetail/<?php echo $customer->getId(); ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="bi bi-people text-muted" style="font-size: 2rem;"></i>
                                                    <p class="mt-2 mb-0">No customers found</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php include 'app/views/layouts/admin_footer.php'; ?>
