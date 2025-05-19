<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purple Shop - Your Online Shopping Destination</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Swiper CSS for carousel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <!-- Custom Purple Theme CSS -->
    <link rel="stylesheet" href="/public/css/purple-theme.css">
    <!-- Additional custom styles -->
    <style>
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-700), var(--primary-500));
            color: white;
            padding: 5rem 0;
            margin-bottom: 3rem;
        }
        
        .hero-content h1 {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1.5rem;
        }
        
        .hero-content p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .hero-image {
            max-width: 100%;
            height: auto;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }
        
        /* Featured Categories */
        .category-card {
            border-radius: var(--radius-md);
            overflow: hidden;
            position: relative;
            height: 200px;
            margin-bottom: 1.5rem;
        }
        
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .category-card:hover img {
            transform: scale(1.05);
        }
        
        .category-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            padding: 1.5rem;
            color: white;
        }
        
        .category-overlay h3 {
            margin: 0;
            font-size: 1.5rem;
            color: white;
        }
        
        /* Featured Products */
        .featured-products {
            padding: 3rem 0;
            background-color: var(--bg-light-purple);
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 2.5rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-500);
        }
        
        /* Testimonials */
        .testimonial-card {
            padding: 2rem;
            border-radius: var(--radius-lg);
            background-color: white;
            box-shadow: var(--shadow-md);
            height: 100%;
        }
        
        .testimonial-card .quote {
            font-size: 1.25rem;
            font-style: italic;
            margin-bottom: 1.5rem;
            color: var(--text-secondary);
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .testimonial-author img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }
        
        .author-info h5 {
            margin: 0;
            color: var(--primary-700);
        }
        
        .author-info p {
            margin: 0;
            color: var(--text-tertiary);
            font-size: 0.875rem;
        }
        
        /* Newsletter */
        .newsletter-section {
            background-color: var(--primary-800);
            color: white;
            padding: 4rem 0;
        }
        
        .newsletter-form .form-control {
            height: 3.5rem;
            border-radius: var(--radius-md) 0 0 var(--radius-md);
        }
        
        .newsletter-form .btn {
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
            height: 3.5rem;
            background-color: var(--primary-500);
            border-color: var(--primary-500);
        }
        
        .newsletter-form .btn:hover {
            background-color: var(--primary-600);
            border-color: var(--primary-600);
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <!-- Header/Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <i class="bi bi-bag-heart-fill me-2"></i>Purple Shop
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="/"><i class="bi bi-house"></i> Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Product/list"><i class="bi bi-grid"></i> Products</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-list-nested"></i> Categories
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                                <li><a class="dropdown-item" href="#">Electronics</a></li>
                                <li><a class="dropdown-item" href="#">Clothing</a></li>
                                <li><a class="dropdown-item" href="#">Home & Kitchen</a></li>
                                <li><a class="dropdown-item" href="#">Beauty & Personal Care</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">All Categories</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="bi bi-info-circle"></i> About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="bi bi-telephone"></i> Contact</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="bi bi-search"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="bi bi-heart"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="bi bi-cart3"></i> <span class="badge bg-light text-dark">0</span></a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/Auth/profile"><i class="bi bi-person me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-bag me-2"></i>My Orders</a></li>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="/Order/dashboard"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/Auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Auth/login"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Auth/register"><i class="bi bi-person-plus"></i> Register</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 hero-content">
                        <h1>Welcome to Purple Shop</h1>
                        <p>Discover amazing products with great deals. Shop now and enjoy exclusive offers on our wide range of products.</p>
                        <a href="/Product/list" class="btn btn-light btn-lg">Shop Now <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block">
                        <img src="https://via.placeholder.com/600x400" alt="Hero Image" class="hero-image">
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Categories -->
        <section class="container mb-5">
            <h2 class="section-title">Shop by Category</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="category-card">
                        <img src="https://via.placeholder.com/400x300" alt="Electronics">
                        <div class="category-overlay">
                            <h3>Electronics</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="category-card">
                        <img src="https://via.placeholder.com/400x300" alt="Fashion">
                        <div class="category-overlay">
                            <h3>Fashion</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="category-card">
                        <img src="https://via.placeholder.com/400x300" alt="Home & Kitchen">
                        <div class="category-overlay">
                            <h3>Home & Kitchen</h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h4 class="text-white">Purple Shop</h4>
                    <p class="text-white-50">Your one-stop destination for all your shopping needs. We offer quality products at competitive prices.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="me-2"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="me-2"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="me-2"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white">Shop</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">All Products</a></li>
                        <li><a href="#">New Arrivals</a></li>
                        <li><a href="#">Best Sellers</a></li>
                        <li><a href="#">Deals & Offers</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white">Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Returns</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5 class="text-white">Newsletter</h5>
                    <p class="text-white-50">Subscribe to our newsletter for updates and exclusive offers.</p>
                    <div class="input-group mt-3">
                        <input type="email" class="form-control" placeholder="Your email address">
                        <button class="btn btn-light" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-white-50">&copy; <?php echo date('Y'); ?> Purple Shop. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 text-white-50">
                        <a href="#" class="text-white-50 me-3">Privacy Policy</a>
                        <a href="#" class="text-white-50 me-3">Terms of Service</a>
                        <a href="#" class="text-white-50">Sitemap</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-J0QzBtKrKOlEn4A2YjRbU4A/6UEtKnNp7fVl9KAKkQZpEAIZqTU6LQKl0AsOx9+" crossorigin="anonymous"></script>
</body>
</html>
