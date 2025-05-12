<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Chỉnh Sửa Sản Phẩm</title>
    <style>
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
            let errors = [];

            // Reset previous error messages
            document.querySelectorAll('.error-message').forEach(el => el.remove());

            if (name.length < 10 || name.length > 100) {
                displayError('name', 'Tên sản phẩm phải có từ 10 đến 100 ký tự.');
                errors.push('Tên sản phẩm phải có từ 10 đến 100 ký tự.');
            }

            if (price <= 0 || isNaN(price)) {
                displayError('price', 'Giá phải là một số dương lớn hơn 0.');
                errors.push('Giá phải là một số dương lớn hơn 0.');
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
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">Quản Lý Sản Phẩm</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/Product/list"><i class="bi bi-list-ul"></i> Danh Sách Sản Phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Product/add"><i class="bi bi-plus-circle"></i> Thêm Sản Phẩm</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container form-container">
        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="text-center text-primary"><i class="bi bi-pencil-square"></i> Chỉnh Sửa Sản Phẩm</h1>
            </div>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Lỗi:</strong>
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
                <h5 class="card-title mb-0"><i class="bi bi-pencil-square"></i> Thông Tin Sản Phẩm #<?php echo $product->getID(); ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/Product/edit/<?php echo $product->getID();?>" onsubmit="return validateForm();" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="name" class="form-label fw-bold">Tên Sản Phẩm:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?php echo htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8'); ?>"
                                       required placeholder="Nhập tên sản phẩm (10-100 ký tự)">
                            </div>
                            <div class="form-text">Tên sản phẩm phải có từ 10 đến 100 ký tự</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="description" class="form-label fw-bold">Mô Tả:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          required placeholder="Nhập mô tả chi tiết về sản phẩm"><?php echo htmlspecialchars($product->getDescription(), ENT_QUOTES, 'UTF-8');?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="price" class="form-label fw-bold">Giá (VNĐ):</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                <input type="number" class="form-control" id="price" name="price" step="1000" min="0"
                                       value="<?php echo htmlspecialchars($product->getPrice(), ENT_QUOTES, 'UTF-8'); ?>"
                                       required placeholder="Nhập giá sản phẩm">
                                <span class="input-group-text">VNĐ</span>
                            </div>
                            <div class="form-text">Giá phải là số dương lớn hơn 0</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/Product/list" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay Lại
                        </a>
                        <div>
                            <a href="/Product/delete/<?php echo $product->getID(); ?>" class="btn btn-danger me-2"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                <i class="bi bi-trash"></i> Xóa Sản Phẩm
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Lưu Thay Đổi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Info Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Thông Tin Bổ Sung</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="bi bi-key"></i> ID Sản Phẩm:</strong> <?php echo $product->getID(); ?></p>
                        <!-- Thêm các thông tin khác nếu có -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Hệ Thống Quản Lý Sản Phẩm</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>