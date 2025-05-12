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
        $products = $this->products;
        include 'app/views/product/list.php';
    }

    public function add(){
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];

            if (empty($name)) {
                $errors[] = 'Tên sản phẩm là bắt buộc.';
            }
            elseif (strlen($name) < 10 || strlen($name) > 100) {
            $errors[] = 'Tên sản phẩm phải có từ 10 đến 100 ký tự.';
            }

            if (!is_numeric($price) || $price <= 0) {
                $errors[] = 'Giá phải là một số dương lớn hơn 0.';
            }

            $status = $_POST['status'] ?? 'Scoping';
            $inventoryCount = $_POST['inventory_count'] ?? 45;
            $incomingCount = $_POST['incoming_count'] ?? 0;
            $outOfStock = $_POST['out_of_stock'] ?? 11;
            $grade = $_POST['grade'] ?? 'A';
            $image = $_POST['image'] ?? '';

            if (empty($errors)) {
                $id = count($this->products) + 1;
                $product = new ProductModel($id, $name, $description, $price, $status, $inventoryCount, $incomingCount, $outOfStock, $grade, $image);
                $this->products[] = $product;
                $_SESSION['products'] = $this->products;
                header('Location: /Product/list');
                exit();
            }
        }

        include 'app/views/product/add.php';
    }

    public function edit($id){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            foreach ($this->products as $key => $product) {
                if ($product->getID() == $id) {
                    $this->products[$key]->setName($_POST['name']);
                    $this->products[$key]->setDescription($_POST['description']);
                    $this->products[$key]->setPrice($_POST['price']);
                    $this->products[$key]->setStatus($_POST['status'] ?? $product->getStatus());
                    $this->products[$key]->setInventoryCount($_POST['inventory_count'] ?? $product->getInventoryCount());
                    $this->products[$key]->setIncomingCount($_POST['incoming_count'] ?? $product->getIncomingCount());
                    $this->products[$key]->setOutOfStock($_POST['out_of_stock'] ?? $product->getOutOfStock());
                    $this->products[$key]->setGrade($_POST['grade'] ?? $product->getGrade());
                    $this->products[$key]->setImage($_POST['image'] ?? $product->getImage());
                    break;
                }
            }
            $_SESSION['products'] = $this->products;
            header('Location: /Product/list');
            exit();
        }
        foreach ($this->products as $product){
            if ($product->getID() == $id){
                include 'app/views/product/edit.php';
                return;
            }
        }
        die('Product not found');
    }

    public function detail($id){
        foreach ($this->products as $product){
            if ($product->getID() == $id){
                include 'app/views/product/detail.php';
                return;
            }
        }
        die('Product not found');
    }

    public function delete($id){
        foreach ($this->products as $key => $product) {
            if ($product->getID() == $id) {
                unset($this->products[$key]);
                break;
            }
        }
        $this->products = array_values($this->products);
        $_SESSION['products'] = $this->products;

        header('Location: /Product/list');
        exit();
    }
}

?>