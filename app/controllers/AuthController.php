<?php

require_once 'app/models/UserModel.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/ProductModel.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        // Only start session if one doesn't already exist
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
    }

    /**
     * Display login form
     */
    public function login()
    {
        // If user is already logged in, redirect to home page
        if ($this->isLoggedIn()) {
            header('Location: /');
            exit();
        }

        $errors = [];

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']) ? true : false;

            // Validate input
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }

            if (empty($password)) {
                $errors[] = 'Password is required';
            }

            // If no validation errors, attempt to login
            if (empty($errors)) {
                $user = $this->userModel->findByEmail($email);

                if ($user && $user->verifyPassword($password)) {
                    // Set user session
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['user_name'] = $user->getName();
                    $_SESSION['user_email'] = $user->getEmail();
                    $_SESSION['user_role'] = $user->getRole();

                    // Set remember me cookie if requested
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/');
                        // In a real application, you would store this token in the database
                    }

                    // Set success message
                    $_SESSION['success_message'] = 'Login successful. Welcome back, ' . $user->getName() . '!';

                    // Redirect admin users to admin dashboard, others to home page or intended page
                    if ($user->getRole() === 'admin') {
                        $redirect = '/Order/dashboard';
                    } else {
                        $redirect = $_SESSION['redirect_after_login'] ?? '/';
                        unset($_SESSION['redirect_after_login']);
                    }

                    header('Location: ' . $redirect);
                    exit();
                } else {
                    $errors[] = 'Invalid email or password';
                }
            }
        }

        // Display login form
        include 'app/views/auth/login.php';
    }

    /**
     * Display registration form
     */
    public function register()
    {
        // If user is already logged in, redirect to home page
        if ($this->isLoggedIn()) {
            header('Location: /');
            exit();
        }

        $errors = [];

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate input
            if (empty($name)) {
                $errors[] = 'Name is required';
            } elseif (strlen($name) < 3 || strlen($name) > 50) {
                $errors[] = 'Name must be between 3 and 50 characters';
            }

            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            } elseif ($this->userModel->findByEmail($email)) {
                $errors[] = 'Email is already registered';
            }

            if (empty($password)) {
                $errors[] = 'Password is required';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }

            $hash_password = password_hash($password, PASSWORD_DEFAULT);

            // If no validation errors, create user
            if (empty($errors)) {
                $user = new UserModel(null, $name, $email, $hash_password, 'customer');

                if ($user->save()) {
                    // Set success message
                    $_SESSION['success_message'] = 'Registration successful. You can now log in.';

                    // Redirect to login page
                    header('Location: /Auth/login');
                    exit();
                } else {
                    $errors[] = 'Failed to create account. Please try again.';
                }
            }
        }

        // Display registration form
        include 'app/views/auth/register.php';
    }

    /**
     * Log out user
     */
    public function logout()
    {
        // Clear session data
        session_unset();
        session_destroy();

        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Redirect to login page
        header('Location: /Auth/login');
        exit();
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    private function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Redirect to login page if not logged in
     *
     * @param string $redirect Redirect URL after login
     */
    public function requireLogin($redirect = null)
    {
        if (!$this->isLoggedIn()) {
            if ($redirect) {
                $_SESSION['redirect_after_login'] = $redirect;
            }

            header('Location: /Auth/login');
            exit();
        }
    }

    /**
     * Redirect to home page if not admin
     */
    public function requireAdmin()
    {
        $this->requireLogin();

        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
            header('Location: /');
            exit();
        }
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        // Require login
        $this->requireLogin();

        // Get user data
        $user = $this->userModel->findById($_SESSION['user_id']);

        if (!$user) {
            $_SESSION['error_message'] = 'User not found';
            header('Location: /');
            exit();
        }

        $errors = [];

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $name = $_POST['name'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate input
            if (empty($name)) {
                $errors[] = 'Name is required';
            } elseif (strlen($name) < 3 || strlen($name) > 50) {
                $errors[] = 'Name must be between 3 and 50 characters';
            }

            // If changing password
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    $errors[] = 'Current password is required';
                } elseif (!$user->verifyPassword($currentPassword)) {
                    $errors[] = 'Current password is incorrect';
                }

                if (strlen($newPassword) < 6) {
                    $errors[] = 'New password must be at least 6 characters';
                }

                if ($newPassword !== $confirmPassword) {
                    $errors[] = 'New passwords do not match';
                }
            }

            // If no validation errors, update user
            if (empty($errors)) {
                $user->setName($name);

                if (!empty($newPassword)) {
                    $user->setPassword($newPassword);
                }

                if ($user->save()) {
                    // Update session data
                    $_SESSION['user_name'] = $user->getName();

                    // Set success message
                    $_SESSION['success_message'] = 'Profile updated successfully';

                    // Redirect to profile page
                    header('Location: /Auth/profile');
                    exit();
                } else {
                    $errors[] = 'Failed to update profile. Please try again.';
                }
            }
        }

        // Display profile form
        include 'app/views/auth/profile.php';
    }

    /**
     * Update user profile information
     */
    public function updateProfile()
    {
        // Require login
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Auth/profile');
            exit();
        }

        // Get user data
        $user = $this->userModel->findById($_SESSION['user_id']);

        if (!$user) {
            $_SESSION['error_message'] = 'User not found';
            header('Location: /Auth/profile');
            exit();
        }

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $errors = [];

        // Validate input
        if (empty($name)) {
            $errors[] = 'Name is required';
        } elseif (strlen($name) < 2 || strlen($name) > 100) {
            $errors[] = 'Name must be between 2 and 100 characters';
        }

        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        } else {
            // Check if email is already taken by another user
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $errors[] = 'Email address is already taken';
            }
        }

        // If no validation errors, update user
        if (empty($errors)) {
            $user->setName($name);
            $user->setEmail($email);

            if ($user->save()) {
                // Update session data
                $_SESSION['user_name'] = $user->getName();

                // Set success message
                $_SESSION['success_message'] = 'Profile updated successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to update profile. Please try again.';
            }
        } else {
            $_SESSION['error_message'] = implode('<br>', $errors);
        }

        // Redirect back to profile page
        header('Location: /Auth/profile');
        exit();
    }

    /**
     * Initiate password change process
     */
    public function changePassword()
    {
        // Require login
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Auth/profile');
            exit();
        }

        // Get user data
        $user = $this->userModel->findById($_SESSION['user_id']);

        if (!$user) {
            $_SESSION['error_message'] = 'User not found';
            header('Location: /Auth/profile');
            exit();
        }

        // Get form data
        $currentPassword = $_POST['current_password'] ?? '';

        $errors = [];

        // Validate input
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required';
        } elseif (!$user->verifyPassword($currentPassword)) {
            $errors[] = 'Current password is incorrect';
        }

        // If no validation errors, proceed to product verification
        if (empty($errors)) {
            // Store user ID in session for the password change process
            $_SESSION['password_change_user_id'] = $user->getId();
            
            // Redirect to product verification page
            header('Location: /Auth/verifyPurchaseForPasswordChange');
            exit();
        } else {
            $_SESSION['error_message'] = implode('<br>', $errors);
            header('Location: /Auth/profile');
            exit();
        }
    }
    
    /**
     * Verify user's purchase for password change security
     */
    public function verifyPurchaseForPasswordChange()
    {
        // Require login
        $this->requireLogin();
        
        // Check if user ID is set in session
        if (!isset($_SESSION['password_change_user_id'])) {
            $_SESSION['error_message'] = 'Invalid password change request';
            header('Location: /Auth/profile');
            exit();
        }
        
        $userId = $_SESSION['password_change_user_id'];
        
        // Initialize OrderModel to get purchased products
        $orderModel = new OrderModel();
        
        // Get up to 4 products the user has purchased
        $purchasedProducts = $orderModel->getUserPurchasedProducts($userId, 4);
        
        // Get all product IDs the user has purchased (for verification)
        $purchasedProductIds = array_column($purchasedProducts, 'id');
        
        // Store purchased product IDs in session for verification
        $_SESSION['password_change_purchased_product_ids'] = $purchasedProductIds;
        
        // Initialize attempt counter if not set
        if (!isset($_SESSION['password_change_verification_attempts'])) {
            $_SESSION['password_change_verification_attempts'] = 0;
        }
        
        // Check if we need to show random products to fill up to 5 options
        $randomProductsNeeded = 5 - count($purchasedProducts);
        $randomProducts = [];
        
        if ($randomProductsNeeded > 0 && !empty($purchasedProductIds)) {
            // Get random products that the user has NOT purchased
            $productModel = new ProductModel();
            $allProducts = $productModel->findAll();
            
            // Filter out products the user has already purchased
            $unpurchasedProducts = array_filter($allProducts, function($product) use ($purchasedProductIds) {
                return !in_array($product['id'], $purchasedProductIds);
            });
            
            // Randomly select products
            shuffle($unpurchasedProducts);
            $randomProducts = array_slice($unpurchasedProducts, 0, $randomProductsNeeded);
        }
        
        // Combine purchased and random products
        $allProducts = array_merge($purchasedProducts, $randomProducts);
        
        // Shuffle the products to randomize the order
        shuffle($allProducts);
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Increment attempt counter
            $_SESSION['password_change_verification_attempts']++;
            
            // Get the selected product ID
            $selectedProductId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            
            // Check if the selected product is valid
            $isValidSelection = ($selectedProductId === 0 && empty($purchasedProductIds)) || 
                               in_array($selectedProductId, $purchasedProductIds);
            
            if ($isValidSelection) {
                // Mark as verified
                $_SESSION['password_change_verified'] = true;
                
                // Redirect to complete password change
                header('Location: /Auth/completePasswordChange');
                exit();
            } else {
                // Check if max attempts reached
                if ($_SESSION['password_change_verification_attempts'] >= 3) {
                    // Clear session variables
                    unset($_SESSION['password_change_user_id']);
                    unset($_SESSION['password_change_purchased_product_ids']);
                    unset($_SESSION['password_change_verification_attempts']);
                    
                    $_SESSION['error_message'] = 'Too many failed verification attempts. Please try again later.';
                    header('Location: /Auth/profile');
                    exit();
                }
                
                $_SESSION['error_message'] = 'Incorrect selection. Please try again.';
            }
        }
        
        // Load the view
        $data = [
            'products' => $allProducts,
            'hasPurchases' => !empty($purchasedProductIds),
            'attemptsLeft' => 3 - $_SESSION['password_change_verification_attempts'],
            'errors' => isset($_SESSION['error_message']) ? [$_SESSION['error_message']] : []
        ];
        
        // Clear any error message after using it
        if (isset($_SESSION['error_message'])) {
            unset($_SESSION['error_message']);
        }
        
        // Load the view
        include 'app/views/auth/verify_purchase_password_change.php';
    }
    
    /**
     * Complete password change after verification
     */
    public function completePasswordChange()
    {
        // Require login
        $this->requireLogin();
        
        // Check if user is verified
        if (!isset($_SESSION['password_change_verified']) || 
            !$_SESSION['password_change_verified'] || 
            !isset($_SESSION['password_change_user_id'])) {
            
            $_SESSION['error_message'] = 'You must verify your identity before changing your password';
            header('Location: /Auth/profile');
            exit();
        }
        
        $userId = $_SESSION['password_change_user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            $_SESSION['error_message'] = 'User not found';
            header('Location: /Auth/profile');
            exit();
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $errors = [];
            
            // Validate input
            if (empty($newPassword)) {
                $errors[] = 'New password is required';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters long';
            }
            
            if (empty($confirmPassword)) {
                $errors[] = 'Password confirmation is required';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New password and confirmation do not match';
            }
            
            // If no validation errors, update password
            if (empty($errors)) {
                $user->setPassword($newPassword);
                
                if ($user->save()) {
                    // Clear session variables
                    unset($_SESSION['password_change_user_id']);
                    unset($_SESSION['password_change_purchased_product_ids']);
                    unset($_SESSION['password_change_verification_attempts']);
                    unset($_SESSION['password_change_verified']);
                    
                    $_SESSION['success_message'] = 'Password changed successfully';
                    header('Location: /Auth/profile');
                    exit();
                } else {
                    $_SESSION['error_message'] = 'Failed to change password. Please try again.';
                }
            } else {
                $_SESSION['error_message'] = implode('<br>', $errors);
            }
        }
        
        // Load the view
        $data = [
            'errors' => isset($_SESSION['error_message']) ? [$_SESSION['error_message']] : []
        ];
        
        // Clear any error message after using it
        if (isset($_SESSION['error_message'])) {
            unset($_SESSION['error_message']);
        }
        
        // Load the view
        include 'app/views/auth/complete_password_change.php';
    }
    
    /**
     * Display forgot password form
     */
    public function forgotPassword()
    {
        // If user is already logged in, redirect to home page
        if ($this->isLoggedIn()) {
            header('Location: /');
            exit();
        }
        
        $errors = [];
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $email = $_POST['email'] ?? '';
            
            // Validate input
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
            
            // If no validation errors, check if email exists
            if (empty($errors)) {
                $user = $this->userModel->findByEmail($email);
                
                if ($user) {
                    // Store user ID in session for security verification
                    $_SESSION['reset_password_user_id'] = $user->getId();
                    
                    // Redirect to product verification page
                    header('Location: /Auth/verifyPurchase');
                    exit();
                } else {
                    $errors[] = 'Email not found in our records';
                }
            }
        }
        
        // Display forgot password form
        include 'app/views/auth/forgot_password.php';
    }
    
    /**
     * Verify user's purchase for password reset security
     */
    public function verifyPurchase()
    {
        // Check if reset_password_user_id exists in session
        if (!isset($_SESSION['reset_password_user_id'])) {
            header('Location: /Auth/forgotPassword');
            exit();
        }
        
        $userId = $_SESSION['reset_password_user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            unset($_SESSION['reset_password_user_id']);
            $_SESSION['error_message'] = 'Invalid user session. Please try again.';
            header('Location: /Auth/forgotPassword');
            exit();
        }
        
        $errors = [];
        
        // Get user's purchased products
        require_once 'app/models/OrderModel.php';
        $orderModel = new OrderModel();
        $purchasedProducts = $orderModel->getUserPurchasedProducts($userId, 4); // Get 4 products max
        
        // Create a list of products to display, including the option for "I haven't purchased anything"
        $productOptions = $purchasedProducts;
        
        // Add some random products if we don't have enough purchased products
        $productModel = new ProductModel();
        $randomProducts = $productModel->findAll([], 5 - count($productOptions));
        
        foreach ($randomProducts as $product) {
            if (count($productOptions) < 4) {
                $productOptions[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'image' => $product->getImage()
                ];
            }
        }
        
        // Shuffle the products to randomize the position of the actual purchased product
        shuffle($productOptions);
        
        // Add the "I haven't purchased anything" option
        $productOptions[] = [
            'id' => 'no_purchase',
            'name' => 'I haven\'t purchased anything yet',
            'price' => '',
            'image' => ''
        ];
        
        // Store the correct answer in session
        $_SESSION['purchased_product_ids'] = array_column($purchasedProducts, 'id');
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $selectedProductId = $_POST['product_id'] ?? '';
            
            // Validate input
            if (empty($selectedProductId)) {
                $errors[] = 'Please select an option';
            }
            
            // Check if the selected product is correct
            $correctAnswer = false;
            
            if ($selectedProductId === 'no_purchase' && empty($_SESSION['purchased_product_ids'])) {
                // User correctly selected they haven't purchased anything
                $correctAnswer = true;
            } elseif (in_array($selectedProductId, $_SESSION['purchased_product_ids'])) {
                // User correctly selected a product they purchased
                $correctAnswer = true;
            }
            
            if ($correctAnswer) {
                // Set verification flag in session
                $_SESSION['purchase_verified'] = true;
                
                // Redirect to reset password page
                header('Location: /Auth/resetPassword');
                exit();
            } else {
                $errors[] = 'Incorrect selection. Please try again.';
                
                // Increment failed attempts
                $_SESSION['verification_attempts'] = ($_SESSION['verification_attempts'] ?? 0) + 1;
                
                // If too many failed attempts, redirect to login
                if ($_SESSION['verification_attempts'] >= 3) {
                    unset($_SESSION['reset_password_user_id']);
                    unset($_SESSION['purchased_product_ids']);
                    unset($_SESSION['verification_attempts']);
                    
                    $_SESSION['error_message'] = 'Too many failed attempts. Please try again later.';
                    header('Location: /Auth/login');
                    exit();
                }
            }
        }
        
        // Display product verification form
        include 'app/views/auth/verify_purchase.php';
    }
    
    /**
     * Reset password form
     */
    public function resetPassword()
    {
        // Check if user is verified
        if (!isset($_SESSION['reset_password_user_id']) || !isset($_SESSION['purchase_verified']) || $_SESSION['purchase_verified'] !== true) {
            header('Location: /Auth/forgotPassword');
            exit();
        }
        
        $userId = $_SESSION['reset_password_user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            unset($_SESSION['reset_password_user_id']);
            unset($_SESSION['purchase_verified']);
            $_SESSION['error_message'] = 'Invalid user session. Please try again.';
            header('Location: /Auth/forgotPassword');
            exit();
        }
        
        $errors = [];
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validate input
            if (empty($newPassword)) {
                $errors[] = 'New password is required';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters long';
            }
            
            if (empty($confirmPassword)) {
                $errors[] = 'Password confirmation is required';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New password and confirmation do not match';
            }
            
            // If no validation errors, update password
            if (empty($errors)) {
                $user->setPassword($newPassword);
                
                if ($user->save()) {
                    // Clear reset password session data
                    unset($_SESSION['reset_password_user_id']);
                    unset($_SESSION['purchase_verified']);
                    unset($_SESSION['purchased_product_ids']);
                    unset($_SESSION['verification_attempts']);
                    
                    $_SESSION['success_message'] = 'Password has been reset successfully. You can now log in with your new password.';
                    header('Location: /Auth/login');
                    exit();
                } else {
                    $errors[] = 'Failed to reset password. Please try again.';
                }
            }
        }
        
        // Display reset password form
        include 'app/views/auth/reset_password.php';
    }
}
