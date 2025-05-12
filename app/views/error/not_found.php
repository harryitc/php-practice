<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Not Found</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrapper {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
            margin-top: auto !important;
        }
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
</head>
<body class="bg-light">
    <div class="content-wrapper">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <div class="container">
                <a class="navbar-brand" href="/">Product Inventory Management</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/Product/list"><i class="bi bi-list-ul"></i> Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Product/add"><i class="bi bi-plus-circle"></i> Add Product</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="error-container">
                <div class="error-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h1 class="error-title">404 - Not Found</h1>
                <p class="error-message">The product you are looking for does not exist or has been removed.</p>
                <a href="/Product/list" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-left me-2"></i>Back to Product List
                </a>
            </div>
        </div>
    </div><!-- End of content-wrapper -->

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Product Inventory Management System</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
