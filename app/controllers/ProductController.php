<?php

require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/controllers/AuthController.php';

class ProductController{
    private $authController;
    private $productModel;
    private $categoryModel;

    public function __construct(){
        // Only start session if one doesn't already exist
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->authController = new AuthController();
    }

    public function index(){
        $this->list();
    }

    public function list(){
        // Get query parameters
        $search = $_GET['search'] ?? '';
        $search = trim($search);
        $categoryId = $_GET['category'] ?? '';
        $sort = $_GET['sort'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        // Check if user is admin
        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

        if ($isAdmin) {
            // Admin view - show all products with admin filters
            $status = $_GET['status'] ?? '';
            $grade = $_GET['grade'] ?? '';
            $inventory = $_GET['inventory'] ?? '';
            $perPage = 10;

            $filters = [
                'search' => $search,
                'status' => $status,
                'grade' => $grade,
                'inventory' => $inventory,
                'category_id' => $categoryId
            ];
        } else {
            // Customer view - show all products (temporarily remove status filter for debugging)
            $perPage = 12;

            $filters = [
                'search' => $search,
                // 'status' => 'active', // Temporarily commented out to show all products
                'category_id' => $categoryId,
                'sort' => $sort
            ];
        }

        try {
            // Get total number of filtered products
            $totalProducts = $this->productModel->countAll($filters);
            $totalPages = ceil($totalProducts / $perPage);

            // Ensure page is within valid range
            if ($page < 1) $page = 1;
            if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

            // Calculate offset for pagination
            $offset = ($page - 1) * $perPage;

            // Get products for current page
            $products = $this->productModel->findAll($filters, $perPage, $offset);
        } catch (Exception $e) {
            // Log the error and show empty results
            error_log("Product listing error: " . $e->getMessage());
            $products = [];
            $totalProducts = 0;
            $totalPages = 0;
            $currentPage = 1;

            // Set error message for display
            $_SESSION['error_message'] = 'Unable to load products. Please try again later.';
        }

        // Get categories for filter dropdown
        $categories = $this->categoryModel->findAll();

        if ($isAdmin) {
            // Admin specific data
            $statuses = $this->productModel->getUniqueValues('status');
            $grades = $this->productModel->getUniqueValues('grade');

            $currentPage = $page;
            $selectedStatus = $status;
            $selectedGrade = $grade;
            $selectedInventory = $inventory;
            $selectedCategoryId = $categoryId;
        } else {
            // Customer specific data
            $currentPage = $page;
            $selectedCategoryId = $categoryId;
            $selectedSort = $sort;
        }

        include 'app/views/product/list.php';
    }

    public function removeQueryParam($param) {
        $params = $_GET;
        unset($params[$param]);

        if (empty($params)) {
            return '/Product/list';
        }

        return '/Product/list?' . http_build_query($params);
    }

    public function buildPaginationUrl($page) {
        $params = $_GET;
        $params['page'] = $page;

        return '/Product/list?' . http_build_query($params);
    }

    public function add(){
        // Require admin privileges
        $this->authController->requireAdmin();

        $errors = [];
        $categories = $this->categoryModel->findAll();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get form data
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $status = $_POST['status'] ?? 'Scoping';
            $inventoryCount = $_POST['inventory_count'] ?? 45;
            $incomingCount = $_POST['incoming_count'] ?? 0;
            $outOfStock = $_POST['out_of_stock'] ?? 11;
            $grade = $_POST['grade'] ?? 'A';
            $image = $_POST['image'] ?? '';
            $categoryId = $_POST['category_id'] ?? null;

            // Validation
            if (empty($name)) {
                $errors[] = 'Product name is required.';
            } elseif (strlen($name) < 10 || strlen($name) > 100) {
                $errors[] = 'Product name must be between 10 and 100 characters.';
            }

            if (empty($description)) {
                $errors[] = 'Product description is required.';
            }

            if (!is_numeric($price) || $price <= 0) {
                $errors[] = 'Price must be a positive number greater than 0.';
            }

            if (!in_array($status, ['Scoping', 'Quoting', 'Production', 'Shipped'])) {
                $errors[] = 'Invalid status selected.';
            }

            if (!is_numeric($inventoryCount) || $inventoryCount < 0) {
                $errors[] = 'Inventory count must be a non-negative number.';
            }

            if (!is_numeric($incomingCount) || $incomingCount < 0) {
                $errors[] = 'Incoming count must be a non-negative number.';
            }

            if (!is_numeric($outOfStock) || $outOfStock < 0) {
                $errors[] = 'Out of stock count must be a non-negative number.';
            }

            if (!in_array($grade, ['A', 'B', 'C'])) {
                $errors[] = 'Invalid grade selected.';
            }

            // If no errors, create the product
            if (empty($errors)) {
                $product = new ProductModel(
                    null,
                    $name,
                    $description,
                    $price,
                    $status,
                    $inventoryCount,
                    $incomingCount,
                    $outOfStock,
                    $grade,
                    $image,
                    $categoryId
                );

                if ($product->save()) {
                    // Set success message
                    $_SESSION['success_message'] = 'Product "' . htmlspecialchars($name) . '" has been added successfully.';
                    header('Location: /Product/list');
                    exit();
                } else {
                    $errors[] = 'Failed to save product. Please try again.';
                }
            }
        }

        include 'app/views/product/add.php';
    }

    public function edit($id){
        // Require admin privileges
        $this->authController->requireAdmin();

        $errors = [];
        $product = $this->productModel->findById($id);
        $categories = $this->categoryModel->findAll();

        // If product not found, show error
        if (!$product) {
            include 'app/views/error/not_found.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validate input
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $status = $_POST['status'] ?? $product->getStatus();
            $inventoryCount = $_POST['inventory_count'] ?? $product->getInventoryCount();
            $incomingCount = $_POST['incoming_count'] ?? $product->getIncomingCount();
            $outOfStock = $_POST['out_of_stock'] ?? $product->getOutOfStock();
            $grade = $_POST['grade'] ?? $product->getGrade();
            $image = $_POST['image'] ?? $product->getImage();
            $categoryId = $_POST['category_id'] ?? $product->getCategoryID();

            // Validation
            if (empty($name)) {
                $errors[] = 'Product name is required.';
            } elseif (strlen($name) < 10 || strlen($name) > 100) {
                $errors[] = 'Product name must be between 10 and 100 characters.';
            }

            if (!is_numeric($price) || $price <= 0) {
                $errors[] = 'Price must be a positive number greater than 0.';
            }

            if (!is_numeric($inventoryCount) || $inventoryCount < 0) {
                $errors[] = 'Inventory count must be a non-negative number.';
            }

            if (!is_numeric($incomingCount) || $incomingCount < 0) {
                $errors[] = 'Incoming count must be a non-negative number.';
            }

            if (!is_numeric($outOfStock) || $outOfStock < 0) {
                $errors[] = 'Out of stock count must be a non-negative number.';
            }

            // If no errors, update the product
            if (empty($errors)) {
                $product->setName($name);
                $product->setDescription($description);
                $product->setPrice($price);
                $product->setStatus($status);
                $product->setInventoryCount($inventoryCount);
                $product->setIncomingCount($incomingCount);
                $product->setOutOfStock($outOfStock);
                $product->setGrade($grade);
                $product->setImage($image);
                $product->setCategoryID($categoryId);

                if ($product->save()) {
                    // Set success message
                    $_SESSION['success_message'] = 'Product "' . htmlspecialchars($name) . '" has been updated successfully.';
                    header("Location: /Product/detail/{$id}");
                    exit();
                } else {
                    $errors[] = 'Failed to update product. Please try again.';
                }
            }
        }

        // Display the edit form
        include 'app/views/product/edit.php';
    }

    public function detail($id){
        $product = $this->productModel->findById($id);

        // If product not found, show error
        if (!$product) {
            include 'app/views/error/not_found.php';
            return;
        }

        // Get category if available
        $category = null;
        if ($product->getCategoryID()) {
            $category = $this->categoryModel->findById($product->getCategoryID());
        }

        // Display the product details
        include 'app/views/product/detail.php';
    }

    public function delete($id){
        // Require admin privileges
        $this->authController->requireAdmin();

        $product = $this->productModel->findById($id);

        // If product not found, show error
        if (!$product) {
            include 'app/views/error/not_found.php';
            return;
        }

        $productName = $product->getName();

        if ($product->delete()) {
            // Set success message in session
            $_SESSION['success_message'] = 'Product "' . htmlspecialchars($productName) . '" has been deleted successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to delete product. Please try again.';
        }

        // Redirect to product list
        header('Location: /Product/list');
        exit();
    }
}