<?php

require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/core/Database.php';

class HomeController
{
    private $db;
    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = Database::getInstance();
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Display home page
     */
    public function index()
    {
        // Get featured products (latest 8 products)
        $featuredProducts = $this->getFeaturedProducts(8);
        
        // Get all categories
        $categories = $this->getAllCategories();

        include 'app/views/customer/home.php';
    }

    /**
     * Get featured products
     *
     * @param int $limit
     * @return array
     */
    private function getFeaturedProducts($limit = 8)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM products 
            WHERE status = 'active' AND inventory_count > 0 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $product = new ProductModel(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['price'],
                $row['category_id'],
                $row['inventory_count'],
                $row['incoming_count'],
                $row['image'],
                $row['status'],
                $row['grade'],
                $row['created_at'],
                $row['updated_at']
            );
            $products[] = $product;
        }
        
        return $products;
    }

    /**
     * Get all categories
     *
     * @return array
     */
    private function getAllCategories()
    {
        $stmt = $this->db->prepare("SELECT * FROM categories ORDER BY name ASC");
        $stmt->execute();
        
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $category = new CategoryModel(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['created_at'],
                $row['updated_at']
            );
            $categories[] = $category;
        }
        
        return $categories;
    }
}
