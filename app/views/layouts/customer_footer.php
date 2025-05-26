    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-shop me-2"></i>ShopEasy
                    </h5>
                    <p class="text-light">Your one-stop destination for quality products at affordable prices. We're committed to providing the best shopping experience.</p>
                    <div class="d-flex">
                        <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="/" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="/Product/list" class="text-light text-decoration-none">Products</a></li>
                        <li><a href="/Cart" class="text-light text-decoration-none">Cart</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/Order/myOrders" class="text-light text-decoration-none">My Orders</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Account</h6>
                    <ul class="list-unstyled">
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/Auth/profile" class="text-light text-decoration-none">My Profile</a></li>
                        <li><a href="/Auth/logout" class="text-light text-decoration-none">Logout</a></li>
                        <?php else: ?>
                        <li><a href="/Auth/login" class="text-light text-decoration-none">Login</a></li>
                        <li><a href="/Auth/register" class="text-light text-decoration-none">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h6 class="mb-3">Contact Info</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt me-2"></i>
                            123 Shopping Street, City, State 12345
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            +1 (555) 123-4567
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            support@shopeasy.com
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> ShopEasy. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-light">
                        <i class="bi bi-shield-check me-1"></i>
                        Secure Shopping | 
                        <i class="bi bi-truck me-1"></i>
                        Free Shipping | 
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Easy Returns
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Success Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-message">
                Action completed successfully!
            </div>
        </div>
    </div>

    <!-- Error Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="error-toast-message">
                An error occurred. Please try again.
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    
    <!-- Customer specific scripts -->
    <script>
        // Load cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });

        // Update cart count
        function updateCartCount() {
            fetch('/Cart/count')
            .then(response => response.json())
            .then(data => {
                document.getElementById('cart-count').textContent = data.count;
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
        }

        // Add to cart function
        function addToCart(productId, quantity = 1) {
            fetch('/Cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount();
                    showToast('successToast', data.message);
                } else {
                    showToast('errorToast', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('errorToast', 'An error occurred while adding the product to cart');
            });
        }

        // Show toast notification
        function showToast(toastId, message) {
            const toast = document.getElementById(toastId);
            const messageElement = toastId === 'successToast' ? 
                document.getElementById('toast-message') : 
                document.getElementById('error-toast-message');
            
            messageElement.textContent = message;
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        }

        // Smooth scroll to top
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>
