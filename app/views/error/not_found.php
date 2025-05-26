<?php
// Set page variables
$pageTitle = '404 - Not Found';
$currentPage = 'error';
$breadcrumbs = [
    ['title' => 'Home', 'url' => '/'],
    ['title' => '404 Error', 'url' => '']
];

// Include customer header (error pages are accessible to everyone)
include 'app/views/layouts/customer_header.php';
?>

<style>
.error-container {
    text-align: center;
    padding: 100px 0;
}
.error-icon {
    font-size: 5rem;
    color: #dc3545;
    margin-bottom: 20px;
}
.error-title {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: #343a40;
}
.error-message {
    font-size: 1.2rem;
    margin-bottom: 30px;
    color: #6c757d;
}
</style>

<div class="container">
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <h1 class="error-title">404 - Not Found</h1>
        <p class="error-message">The page you are looking for does not exist or has been removed.</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="/" class="btn btn-primary btn-lg">
                <i class="bi bi-house me-2"></i>Go Home
            </a>
            <a href="/Product/list" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-shop me-2"></i>Browse Products
            </a>
        </div>
    </div>
</div>

<?php include 'app/views/layouts/customer_footer.php'; ?>
