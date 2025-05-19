<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Register - Product Inventory Management</title>
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
        .register-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        .register-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .register-header {
            background-color: #198754;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .register-body {
            padding: 2rem;
        }
        .register-footer {
            background-color: #f8f9fa;
            padding: 1rem;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .btn-register {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
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
                            <a class="nav-link" href="/Auth/login"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/Auth/register"><i class="bi bi-person-plus"></i> Register</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container register-container">
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card register-card">
                <div class="register-header">
                    <h3 class="mb-0"><i class="bi bi-person-plus me-2"></i>Register</h3>
                    <p class="mb-0">Create a new account</p>
                </div>
                <div class="register-body">
                    <form method="POST" action="/Auth/register" id="registerForm">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            <label for="name"><i class="bi bi-person me-2"></i>Full Name</label>
                        </div>
                        <div class="form-floating">
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <label for="email"><i class="bi bi-envelope me-2"></i>Email address</label>
                        </div>
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                            <label for="confirm_password"><i class="bi bi-lock-fill me-2"></i>Confirm Password</label>
                        </div>
                        <button type="submit" class="btn btn-success btn-register">
                            <i class="bi bi-person-plus me-2"></i>Register
                        </button>
                    </form>
                </div>
                <div class="register-footer">
                    <p class="mb-0">Already have an account? <a href="/Auth/login">Login here</a></p>
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
    <script>
        // Client-side validation
        document.getElementById('registerForm').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                event.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
