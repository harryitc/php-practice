<?php

require_once 'app/models/ProductModel.php';

class ProductController{
    private $products = [];

    public function __construct(){
        session_start();
        if (isset($_SESSION['products'])) {
            $this->products = $_SESSION['products'];
        } else {
            // Add sample products if none exist
            $this->addSampleProducts();
        }
    }

    private function addSampleProducts() {
        $sampleProducts = [
            new ProductModel(1, 'Hydrate replenish(body oil)', 'Hydrating body oil for dry skin', 29.99, 'Scoping', 45, 12, 11, 'A', 'https://via.placeholder.com/40'),
            new ProductModel(2, 'Hydrate replenish', 'Hydrating face cream', 24.99, 'Scoping', 45, 65, 11, 'A', 'https://via.placeholder.com/40'),
            new ProductModel(3, 'Illumination (mask)', 'Brightening face mask', 19.99, 'Quoting', 45, 35, 11, 'B', 'https://via.placeholder.com/40'),
            new ProductModel(4, 'Act+ acre hair mask', 'Nourishing hair mask', 34.99, 'Scoping', 45, 24, 11, 'A', 'https://via.placeholder.com/40'),
            new ProductModel(5, 'Mecca cosmetica', 'Luxury cosmetics set', 89.99, 'Production', 0, 22, 11, 'A', 'https://via.placeholder.com/40'),
            new ProductModel(6, 'Hylamide (Glow)', 'Illuminating serum', 39.99, 'Scoping', 45, 86, 11, 'B', 'https://via.placeholder.com/40'),
            new ProductModel(7, 'Mecca cosmetica(body oil)', 'Luxury body oil', 49.99, 'Scoping', 45, 68, 11, 'A', 'https://via.placeholder.com/40'),
            new ProductModel(8, 'Hydrate replenish(body oil)', 'Hydrating body oil for sensitive skin', 32.99, 'Production', 0, 70, 11, 'C', 'https://via.placeholder.com/40'),
            new ProductModel(9, 'Illumination (mask)', 'Overnight brightening mask', 29.99, 'Scoping', 45, 56, 11, 'A', 'https://via.placeholder.com/40'),
            new ProductModel(10, 'Mecca cosmetica(body oil)', 'Luxury body oil with shimmer', 54.99, 'Shipped', 0, 72, 11, 'A', 'https://via.placeholder.com/40'),
            new ProductModel(11, 'Hylamide (Glow)', 'Illuminating face drops', 44.99, 'Scoping', 45, 80, 11, 'B', 'https://via.placeholder.com/40')
        ];

        $this->products = $sampleProducts;
        $_SESSION['products'] = $this->products;
    }

    public function index(){
        $this->list();
    }

    public function list(){
        // Get query parameters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $grade = isset($_GET['grade']) ? $_GET['grade'] : '';
        $inventory = isset($_GET['inventory']) ? $_GET['inventory'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5; // Number of products per page

        // Filter products
        $filteredProducts = $this->filterProducts($search, $status, $grade, $inventory);

        // Get total number of filtered products
        $totalProducts = count($filteredProducts);
        $totalPages = ceil($totalProducts / $perPage);

        // Ensure page is within valid range
        if ($page < 1) $page = 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

        // Get products for current page
        $startIndex = ($page - 1) * $perPage;
        $paginatedProducts = array_slice($filteredProducts, $startIndex, $perPage);

        // Get unique statuses and grades for filter dropdowns
        $statuses = $this->getUniqueValues('Status');
        $grades = $this->getUniqueValues('Grade');

        // Make variables available to the view
        $products = $paginatedProducts;
        $currentPage = $page;
        $selectedStatus = $status;
        $selectedGrade = $grade;
        $selectedInventory = $inventory;

        include 'app/views/product/list.php';
    }

    private function filterProducts($search, $status, $grade, $inventory) {
        $filteredProducts = [];

        foreach ($this->products as $product) {
            // Apply search filter
            if (!empty($search)) {
                $nameMatch = stripos($product->getName(), $search) !== false;
                $descMatch = stripos($product->getDescription(), $search) !== false;
                if (!$nameMatch && !$descMatch) {
                    continue;
                }
            }

            // Apply status filter
            if (!empty($status) && $product->getStatus() != $status) {
                continue;
            }

            // Apply grade filter
            if (!empty($grade) && $product->getGrade() != $grade) {
                continue;
            }

            // Apply inventory filter
            if ($inventory === 'in_stock' && $product->getInventoryCount() == 0) {
                continue;
            } elseif ($inventory === 'out_of_stock' && $product->getInventoryCount() > 0) {
                continue;
            }

            $filteredProducts[] = $product;
        }

        return $filteredProducts;
    }

    private function getUniqueValues($property) {
        $values = [];
        $getter = 'get' . $property;

        foreach ($this->products as $product) {
            $value = $product->$getter();
            if (!in_array($value, $values)) {
                $values[] = $value;
            }
        }

        sort($values);
        return $values;
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
        $errors = [];
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
                $id = count($this->products) + 1;
                $product = new ProductModel($id, $name, $description, $price, $status, $inventoryCount, $incomingCount, $outOfStock, $grade, $image);
                $this->products[] = $product;
                $_SESSION['products'] = $this->products;

                // Set success message
                $_SESSION['success_message'] = 'Product "' . htmlspecialchars($name) . '" has been added successfully.';

                header('Location: /Product/list');
                exit();
            }
        }

        include 'app/views/product/add.php';
    }

    public function edit($id){
        $errors = [];
        $product = null;

        // Find the product
        foreach ($this->products as $p) {
            if ($p->getID() == $id) {
                $product = $p;
                break;
            }
        }

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
                foreach ($this->products as $key => $p) {
                    if ($p->getID() == $id) {
                        $this->products[$key]->setName($name);
                        $this->products[$key]->setDescription($description);
                        $this->products[$key]->setPrice($price);
                        $this->products[$key]->setStatus($status);
                        $this->products[$key]->setInventoryCount($inventoryCount);
                        $this->products[$key]->setIncomingCount($incomingCount);
                        $this->products[$key]->setOutOfStock($outOfStock);
                        $this->products[$key]->setGrade($grade);
                        $this->products[$key]->setImage($image);
                        break;
                    }
                }

                // Save to session and redirect
                $_SESSION['products'] = $this->products;
                header('Location: /Product/detail/' . $id);
                exit();
            }
        }

        // Display the edit form
        include 'app/views/product/edit.php';
    }

    public function detail($id){
        $product = null;

        // Find the product
        foreach ($this->products as $p) {
            if ($p->getID() == $id) {
                $product = $p;
                break;
            }
        }

        // If product not found, show error
        if (!$product) {
            include 'app/views/error/not_found.php';
            return;
        }

        // Display the product details
        include 'app/views/product/detail.php';
    }

    public function delete($id){
        $productFound = false;
        $productName = '';

        // Find and delete the product
        foreach ($this->products as $key => $product) {
            if ($product->getID() == $id) {
                $productName = $product->getName();
                unset($this->products[$key]);
                $productFound = true;
                break;
            }
        }

        // If product not found, show error
        if (!$productFound) {
            include 'app/views/error/not_found.php';
            return;
        }

        // Reindex the array and save to session
        $this->products = array_values($this->products);
        $_SESSION['products'] = $this->products;

        // Set success message in session
        $_SESSION['success_message'] = 'Product "' . htmlspecialchars($productName) . '" has been deleted successfully.';

        // Redirect to product list
        header('Location: /Product/list');
        exit();
    }
}

?>