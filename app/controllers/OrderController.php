<?php

require_once 'app/models/OrderModel.php';
require_once 'app/models/OrderItemModel.php';
require_once 'app/models/UserModel.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CartModel.php';
require_once 'app/controllers/AuthController.php';
require_once 'app/core/Database.php';

class OrderController
{
    private $authController;
    private $orderModel;
    private $orderItemModel;
    private $userModel;
    private $productModel;
    private $db;

    public function __construct()
    {
        // Only start session if one doesn't already exist
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getInstance();
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
        $this->authController = new AuthController();
    }

    /**
     * Admin dashboard with order statistics
     */
    public function dashboard()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        // Get order statistics
        $totalOrders = $this->countOrders();
        $pendingOrders = $this->countOrdersByStatus('pending');
        $processingOrders = $this->countOrdersByStatus('processing');
        $shippedOrders = $this->countOrdersByStatus('shipped');
        $deliveredOrders = $this->countOrdersByStatus('delivered');
        $cancelledOrders = $this->countOrdersByStatus('cancelled');

        // Get revenue statistics
        $totalRevenue = $this->calculateTotalRevenue();
        $monthlyRevenue = $this->calculateMonthlyRevenue();
        $dailyRevenue = $this->calculateDailyRevenue();

        // Get customer statistics
        $totalCustomers = $this->countCustomers();

        // Get recent orders
        $recentOrders = $this->getRecentOrders(5);

        // Include the dashboard view
        include 'app/views/admin/dashboard.php';
    }

    /**
     * List all orders with pagination and filtering
     */
    public function list()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        // Get query parameters
        $status = $_GET['status'] ?? '';
        $userId = $_GET['user_id'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10; // Number of orders per page

        // Create filters array
        $filters = [
            'status' => $status,
            'user_id' => $userId
        ];

        // Get total number of filtered orders
        $totalOrders = $this->countOrdersWithFilters($filters);
        $totalPages = ceil($totalOrders / $perPage);

        // Ensure page is within valid range
        if ($page < 1) $page = 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

        // Calculate offset for pagination
        $offset = ($page - 1) * $perPage;

        // Get orders for current page
        $orders = $this->getOrdersWithFilters($filters, $perPage, $offset);

        // Get all order statuses for filter dropdown
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        // Make variables available to the view
        $currentPage = $page;
        $selectedStatus = $status;
        $selectedUserId = $userId;

        include 'app/views/admin/orders/list.php';
    }

    /**
     * View order details
     *
     * @param int $id Order ID
     */
    public function detail($id)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        $order = $this->orderModel->findById($id);

        // If order not found, show error
        if (!$order) {
            include 'app/views/error/not_found.php';
            return;
        }

        // Get customer information
        $customer = $this->userModel->findById($order->getUserId());

        // Include the order detail view
        include 'app/views/admin/orders/detail.php';
    }

    /**
     * Update order status
     *
     * @param int $id Order ID
     */
    public function updateStatus($id)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        $order = $this->orderModel->findById($id);

        // If order not found, show error
        if (!$order) {
            include 'app/views/error/not_found.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $status = $_POST['status'] ?? '';

            if (in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
                $order->setStatus($status);

                if ($order->save()) {
                    $_SESSION['success_message'] = 'Order status updated successfully.';
                } else {
                    $_SESSION['error_message'] = 'Failed to update order status.';
                }
            } else {
                $_SESSION['error_message'] = 'Invalid order status.';
            }

            header("Location: /Order/detail/{$id}");
            exit();
        }
    }

    /**
     * List all customers
     */
    public function customers()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        // Get all customers
        $customers = $this->userModel->findAll();

        // Include the customers view
        include 'app/views/admin/customers/list.php';
    }

    /**
     * View customer details and orders
     *
     * @param int $id Customer ID
     */
    public function customerDetail($id)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        $customer = $this->userModel->findById($id);

        // If customer not found, show error
        if (!$customer) {
            include 'app/views/error/not_found.php';
            return;
        }

        // Get customer's orders
        $orders = $this->getOrdersByUserId($id);

        // Include the customer detail view
        include 'app/views/admin/customers/detail.php';
    }

    /**
     * Revenue statistics page
     */
    public function revenue()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        // Get revenue statistics
        $totalRevenue = $this->calculateTotalRevenue();
        $monthlyRevenue = $this->calculateMonthlyRevenue();
        $dailyRevenue = $this->calculateDailyRevenue();

        // Include the revenue statistics view
        include 'app/views/admin/revenue.php';
    }

    /**
     * Count all orders
     *
     * @return int
     */
    private function countOrders()
    {
        $sql = "SELECT COUNT(*) as count FROM orders";
        $result = $this->db->query($sql)->fetch();
        return $result['count'];
    }

    /**
     * Count orders by status
     *
     * @param string $status
     * @return int
     */
    private function countOrdersByStatus($status)
    {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE status = :status";
        $result = $this->db->query($sql)->fetch(['status' => $status]);
        return $result['count'];
    }

    /**
     * Count orders with filters
     *
     * @param array $filters
     * @return int
     */
    private function countOrdersWithFilters($filters)
    {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        $result = $this->db->query($sql)->fetch($params);
        return $result['count'];
    }

    /**
     * Get orders with filters and pagination
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    private function getOrdersWithFilters($filters, $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM orders WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        $sql .= " ORDER BY created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;

            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }

        $results = $this->db->query($sql)->fetchAll($params);

        $orders = [];
        foreach ($results as $row) {
            $order = new OrderModel(
                $row['id'],
                $row['user_id'],
                $row['total_amount'],
                $row['status'],
                $row['shipping_address'],
                $row['shipping_city'],
                $row['shipping_state'],
                $row['shipping_zip'],
                $row['shipping_country'],
                $row['payment_method'],
                $row['created_at'],
                $row['updated_at']
            );
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Get recent orders
     *
     * @param int $limit
     * @return array
     */
    private function getRecentOrders($limit)
    {
        $sql = "SELECT * FROM orders ORDER BY created_at DESC LIMIT " . (int)$limit;
        $results = $this->db->query($sql)->fetchAll();

        $orders = [];
        foreach ($results as $row) {
            $order = new OrderModel(
                $row['id'],
                $row['user_id'],
                $row['total_amount'],
                $row['status'],
                $row['shipping_address'],
                $row['shipping_city'],
                $row['shipping_state'],
                $row['shipping_zip'],
                $row['shipping_country'],
                $row['payment_method'],
                $row['created_at'],
                $row['updated_at']
            );
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Get orders by user ID
     *
     * @param int $userId
     * @return array
     */
    private function getOrdersByUserId($userId)
    {
        $filters = ['user_id' => $userId];
        return $this->getOrdersWithFilters($filters);
    }

    /**
     * Count all customers
     *
     * @return int
     */
    private function countCustomers()
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'customer'";
        $result = $this->db->query($sql)->fetch();
        return $result['count'];
    }

    /**
     * Calculate total revenue
     *
     * @return float
     */
    private function calculateTotalRevenue()
    {
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
        $result = $this->db->query($sql)->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Calculate monthly revenue
     *
     * @return float
     */
    private function calculateMonthlyRevenue()
    {
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $result = $this->db->query($sql)->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Calculate daily revenue
     *
     * @return float
     */
    private function calculateDailyRevenue()
    {
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled' AND DATE(created_at) = CURRENT_DATE()";
        $result = $this->db->query($sql)->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Helper method to build pagination URL
     *
     * @param int $page
     * @return string
     */
    public function buildPaginationUrl($page)
    {
        $params = $_GET;
        $params['page'] = $page;

        return '/Order/list?' . http_build_query($params);
    }

    /**
     * Helper method to remove a query parameter from URL
     *
     * @param string $param
     * @return string
     */
    public function removeQueryParam($param)
    {
        $params = $_GET;
        unset($params[$param]);

        if (empty($params)) {
            return '/Order/list';
        }

        return '/Order/list?' . http_build_query($params);
    }

    /**
     * Checkout page - display cart items and shipping form
     */
    public function checkout()
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'Please login to proceed with checkout';
            header('Location: /Auth/login?redirect=/Cart');
            exit();
        }

        // Check if cart is not empty
        $cart = new CartModel();

        if ($cart->isEmpty()) {
            $_SESSION['error_message'] = 'Your cart is empty';
            header('Location: /Cart');
            exit();
        }

        $cartItems = $cart->getCartItemsWithDetails();
        $totalAmount = $cart->getTotalAmount();
        $itemCount = $cart->getItemCount();

        // Get user information for pre-filling form
        $user = $this->userModel->findById($_SESSION['user_id']);

        include 'app/views/order/checkout.php';
    }

    /**
     * Process checkout form and create order
     */
    public function processCheckout()
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'Please login to proceed with checkout';
            header('Location: /Auth/login?redirect=/Cart');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Order/checkout');
            exit();
        }

        // Check if cart is not empty
        $cart = new CartModel();

        if ($cart->isEmpty()) {
            $_SESSION['error_message'] = 'Your cart is empty';
            header('Location: /Cart');
            exit();
        }

        $errors = [];

        // Validate form data
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $shippingCity = trim($_POST['shipping_city'] ?? '');
        $shippingState = trim($_POST['shipping_state'] ?? '');
        $shippingZip = trim($_POST['shipping_zip'] ?? '');
        $shippingCountry = trim($_POST['shipping_country'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? '';

        // Validation
        if (empty($shippingAddress)) {
            $errors[] = 'Shipping address is required';
        }
        if (empty($shippingCity)) {
            $errors[] = 'City is required';
        }
        if (empty($shippingState)) {
            $errors[] = 'State is required';
        }
        if (empty($shippingZip)) {
            $errors[] = 'ZIP code is required';
        }
        if (empty($shippingCountry)) {
            $errors[] = 'Country is required';
        }
        if (!in_array($paymentMethod, ['cod', 'bank_transfer', 'credit_card'])) {
            $errors[] = 'Please select a valid payment method';
        }

        if (!empty($errors)) {
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_data'] = $_POST;
            header('Location: /Order/checkout');
            exit();
        }

        // Create order
        $cartItems = $cart->getCartItemsWithDetails();
        $totalAmount = $cart->getTotalAmount();

        $order = new OrderModel(
            null,
            $_SESSION['user_id'],
            $totalAmount,
            'pending',
            $shippingAddress,
            $shippingCity,
            $shippingState,
            $shippingZip,
            $shippingCountry,
            $paymentMethod
        );

        // Save order and order items
        if ($order->save()) {
            $orderId = $order->getId();

            // Add order items
            $allItemsSaved = true;
            foreach ($cartItems as $item) {
                $orderItem = new OrderItemModel(
                    null,
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                );

                if (!$orderItem->save()) {
                    $allItemsSaved = false;
                    break;
                }

                // Update product inventory
                $product = $item['product'];
                $newInventory = $product->getInventoryCount() - $item['quantity'];
                $product->setInventoryCount($newInventory);
                $product->save();
            }

            if ($allItemsSaved) {
                // Clear cart
                $cart->clearCart();

                $_SESSION['success_message'] = 'Order placed successfully! Order ID: ' . $orderId;
                header('Location: /Order/success/' . $orderId);
                exit();
            } else {
                // Delete the order if items couldn't be saved
                $order->delete();
                $_SESSION['error_message'] = 'Failed to create order items. Please try again.';
                header('Location: /Order/checkout');
                exit();
            }
        } else {
            $_SESSION['error_message'] = 'Failed to create order. Please try again.';
            header('Location: /Order/checkout');
            exit();
        }
    }

    /**
     * Order success page
     */
    public function success($orderId = null)
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        if (!$orderId) {
            header('Location: /Product/list');
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        // Check if order exists and belongs to current user
        if (!$order || $order->getUserId() != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Product/list');
            exit();
        }

        include 'app/views/order/success.php';
    }

    /**
     * User's order history
     */
    public function myOrders()
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        $orders = $this->getOrdersByUserId($_SESSION['user_id']);

        include 'app/views/order/my_orders.php';
    }

    /**
     * View specific order details for current user
     */
    public function view($orderId)
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        // Check if order exists and belongs to current user
        if (!$order || $order->getUserId() != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/myOrders');
            exit();
        }

        include 'app/views/order/view.php';
    }
}
