<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Add New Product</title>
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
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .error-message {
            color: #dc3545;
            margin-top: 5px;
            font-size: 0.9rem;
        }
    </style>
    <script>
        function validateForm() {
            let name = document.getElementById('name').value;
            let price = document.getElementById('price').value;
            let inventoryCount = document.getElementById('inventory_count').value;
            let incomingCount = document.getElementById('incoming_count').value;
            let outOfStock = document.getElementById('out_of_stock').value;
            let errors = [];

            // Reset previous error messages
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            if (name.length < 10 || name.length > 100) {
                displayError('name', 'Product name must be between 10 and 100 characters.');
                errors.push('Product name must be between 10 and 100 characters.');
            }

            if (price <= 0 || isNaN(price)) {
                displayError('price', 'Price must be a positive number greater than 0.');
                errors.push('Price must be a positive number greater than 0.');
            }

            if (inventoryCount < 0 || isNaN(inventoryCount)) {
                displayError('inventory_count', 'Inventory count must be a non-negative number.');
                errors.push('Inventory count must be a non-negative number.');
            }

            if (incomingCount < 0 || isNaN(incomingCount)) {
                displayError('incoming_count', 'Incoming count must be a non-negative number.');
                errors.push('Incoming count must be a non-negative number.');
            }

            if (outOfStock < 0 || isNaN(outOfStock)) {
                displayError('out_of_stock', 'Out of stock count must be a non-negative number.');
                errors.push('Out of stock count must be a non-negative number.');
            }

            if (errors.length > 0) {
                return false;
            }
            return true;
        }

        function displayError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerText = message;
            field.parentNode.appendChild(errorDiv);
            field.classList.add('is-invalid');
        }
    </script>
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
                            <a class="nav-link active" href="/Product/add"><i class="bi bi-plus-circle"></i> Add Product</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container form-container">
        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="text-center text-primary"><i class="bi bi-plus-circle"></i> Add New Product</h1>
            </div>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/Product/list">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New Product</li>
            </ol>
        </nav>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Error:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Product Form Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="bi bi-pencil-square"></i> Product Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/Product/add" onsubmit="return validateForm();" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="name" class="form-label fw-bold">Product Name:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" id="name" name="name" required
                                       placeholder="Enter product name (10-100 characters)">
                            </div>
                            <div class="form-text">Product name must be between 10 and 100 characters</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="description" class="form-label fw-bold">Description:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          required placeholder="Enter detailed product description"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price" class="form-label fw-bold">Price (USD):</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
                                       required placeholder="Enter product price">
                                <span class="input-group-text">USD</span>
                            </div>
                            <div class="form-text">Price must be a positive number greater than 0</div>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-bold">Status:</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Scoping" selected>Scoping</option>
                                <option value="Quoting">Quoting</option>
                                <option value="Production">Production</option>
                                <option value="Shipped">Shipped</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="inventory_count" class="form-label fw-bold">Inventory Count:</label>
                            <input type="number" class="form-control" id="inventory_count" name="inventory_count" min="0"
                                   value="45" required placeholder="Enter inventory count">
                        </div>

                        <div class="col-md-4">
                            <label for="incoming_count" class="form-label fw-bold">Incoming Count:</label>
                            <input type="number" class="form-control" id="incoming_count" name="incoming_count" min="0"
                                   value="0" required placeholder="Enter incoming count">
                        </div>

                        <div class="col-md-4">
                            <label for="out_of_stock" class="form-label fw-bold">Out of Stock Count:</label>
                            <input type="number" class="form-control" id="out_of_stock" name="out_of_stock" min="0"
                                   value="11" required placeholder="Enter out of stock count">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="grade" class="form-label fw-bold">Grade:</label>
                            <select class="form-select" id="grade" name="grade" required>
                                <option value="A" selected>A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="image" class="form-label fw-bold">Image URL:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-image"></i></span>
                                <input type="text" class="form-control" id="image" name="image"
                                       placeholder="Enter image URL (optional)">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/Product/list" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Save Product
                        </button>
                    </div>
                </form>
            </div>
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