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
        $sql = "SELECT * FROM products
                WHERE status = 'active' AND inventory_count > 0
                ORDER BY created_at DESC
                LIMIT ?";

        $results = $this->db->query($sql)->bind([$limit])->fetchAll();

        $products = [];
        foreach ($results as $row) {
            $product = new ProductModel(
                $row['id'],                    // ID
                $row['name'],                  // Name
                $row['description'],           // Description
                $row['price'],                 // Price
                $row['status'],                // Status
                $row['inventory_count'],       // InventoryCount
                $row['incoming_count'],        // IncomingCount
                $row['out_of_stock'] ?? 0,     // OutOfStock
                $row['grade'],                 // Grade
                $row['image'],                 // Image
                $row['category_id']            // CategoryID
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
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $results = $this->db->query($sql)->fetchAll();

        $categories = [];
        foreach ($results as $row) {
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
