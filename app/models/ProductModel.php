<?php

require_once 'app/core/Database.php';

class ProductModel{
    private $ID;
    private $CategoryID;
    private $Name;
    private $Description;
    private $Price;
    private $Status;
    private $InventoryCount;
    private $IncomingCount;
    private $OutOfStock;
    private $Grade;
    private $Image;
    private $CreatedAt;
    private $UpdatedAt;

    private $db;

    public function __construct($ID = null, $Name = '', $Description = '', $Price = 0, $Status = 'Scoping', $InventoryCount = 45, $IncomingCount = 0, $OutOfStock = 11, $Grade = 'A', $Image = '', $CategoryID = null){
        $this->db = Database::getInstance();

        $this->ID = $ID;
        $this->CategoryID = $CategoryID;
        $this->Name = $Name;
        $this->Description = $Description;
        $this->Price = $Price;
        $this->Status = $Status;
        $this->InventoryCount = $InventoryCount;
        $this->IncomingCount = $IncomingCount;
        $this->OutOfStock = $OutOfStock;
        $this->Grade = $Grade;
        $this->Image = $Image;
    }

    /**
     * Find all products
     *
     * @param array $filters Optional filters
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array
     */
    public function findAll($filters = [], $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];

        // Apply filters
        if (!empty($filters['search'])) {
            $searchTerm = "%" . $filters['search'] . "%";
            $sql .= " AND (name LIKE :search_name OR description LIKE :search_desc)";
            $params['search_name'] = $searchTerm;
            $params['search_desc'] = $searchTerm;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['grade'])) {
            $sql .= " AND grade = :grade";
            $params['grade'] = $filters['grade'];
        }

        if (isset($filters['inventory']) && $filters['inventory'] === 'in_stock') {
            $sql .= " AND inventory_count > 0";
        } elseif (isset($filters['inventory']) && $filters['inventory'] === 'out_of_stock') {
            $sql .= " AND inventory_count = 0";
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        // Add sorting for customer view
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'name_asc':
                    $sql .= " ORDER BY name ASC";
                    break;
                case 'name_desc':
                    $sql .= " ORDER BY name DESC";
                    break;
                case 'price_asc':
                    $sql .= " ORDER BY price ASC";
                    break;
                case 'price_desc':
                    $sql .= " ORDER BY price DESC";
                    break;
                case 'newest':
                    $sql .= " ORDER BY created_at DESC";
                    break;
                case 'oldest':
                    $sql .= " ORDER BY created_at ASC";
                    break;
                default:
                    $sql .= " ORDER BY id DESC";
            }
        } else {
            $sql .= " ORDER BY id DESC";
        }

        // Add limit and offset
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;

            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }

        $results = $this->db->query($sql)->fetchAll($params);

        $products = [];
        foreach ($results as $row) {
            $product = new ProductModel(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['price'],
                $row['status'],
                $row['inventory_count'],
                $row['incoming_count'],
                $row['out_of_stock'],
                $row['grade'],
                $row['image'],
                $row['category_id']
            );
            $products[] = $product;
        }

        return $products;
    }

    /**
     * Count all products with filters
     *
     * @param array $filters Optional filters
     * @return int
     */
    public function countAll($filters = [])
    {
        $sql = "SELECT COUNT(*) as count FROM products WHERE 1=1";
        $params = [];

        // Apply filters
        if (!empty($filters['search'])) {
            $searchTerm = "%" . $filters['search'] . "%";
            $sql .= " AND (name LIKE :search_name OR description LIKE :search_desc)";
            $params['search_name'] = $searchTerm;
            $params['search_desc'] = $searchTerm;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['grade'])) {
            $sql .= " AND grade = :grade";
            $params['grade'] = $filters['grade'];
        }

        if (isset($filters['inventory']) && $filters['inventory'] === 'in_stock') {
            $sql .= " AND inventory_count > 0";
        } elseif (isset($filters['inventory']) && $filters['inventory'] === 'out_of_stock') {
            $sql .= " AND inventory_count = 0";
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        $result = $this->db->query($sql)->fetch($params);
        return $result['count'];
    }

    /**
     * Find product by ID
     *
     * @param int $id
     * @return ProductModel|null
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM products WHERE id = :id";
        $result = $this->db->query($sql)->fetch(['id' => $id]);

        if (!$result) {
            return null;
        }

        return new ProductModel(
            $result['id'],
            $result['name'],
            $result['description'],
            $result['price'],
            $result['status'],
            $result['inventory_count'],
            $result['incoming_count'],
            $result['out_of_stock'],
            $result['grade'],
            $result['image'],
            $result['category_id']
        );
    }

    /**
     * Save product (insert or update)
     *
     * @return bool
     */
    public function save()
    {
        if ($this->ID) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * Insert new product
     *
     * @return bool
     */
    private function insert()
    {
        $sql = "INSERT INTO products (category_id, name, description, price, status, inventory_count, incoming_count, out_of_stock, grade, image)
                VALUES (:category_id, :name, :description, :price, :status, :inventory_count, :incoming_count, :out_of_stock, :grade, :image)";

        $result = $this->db->query($sql)->bind([
            'category_id' => $this->CategoryID,
            'name' => $this->Name,
            'description' => $this->Description,
            'price' => $this->Price,
            'status' => $this->Status,
            'inventory_count' => $this->InventoryCount,
            'incoming_count' => $this->IncomingCount,
            'out_of_stock' => $this->OutOfStock,
            'grade' => $this->Grade,
            'image' => $this->Image
        ])->execute();

        if ($result) {
            $this->ID = $this->db->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Update existing product
     *
     * @return bool
     */
    private function update()
    {
        $sql = "UPDATE products
                SET category_id = :category_id,
                    name = :name,
                    description = :description,
                    price = :price,
                    status = :status,
                    inventory_count = :inventory_count,
                    incoming_count = :incoming_count,
                    out_of_stock = :out_of_stock,
                    grade = :grade,
                    image = :image
                WHERE id = :id";

        return $this->db->query($sql)->bind([
            'id' => $this->ID,
            'category_id' => $this->CategoryID,
            'name' => $this->Name,
            'description' => $this->Description,
            'price' => $this->Price,
            'status' => $this->Status,
            'inventory_count' => $this->InventoryCount,
            'incoming_count' => $this->IncomingCount,
            'out_of_stock' => $this->OutOfStock,
            'grade' => $this->Grade,
            'image' => $this->Image
        ])->execute();
    }

    /**
     * Delete product
     *
     * @return bool
     */
    public function delete()
    {
        if (!$this->ID) {
            return false;
        }

        $sql = "DELETE FROM products WHERE id = :id";
        return $this->db->query($sql)->bind(['id' => $this->ID])->execute();
    }

    /**
     * Get unique values for a column
     *
     * @param string $column
     * @return array
     */
    public function getUniqueValues($column)
    {
        $validColumns = ['status', 'grade'];

        if (!in_array(strtolower($column), $validColumns)) {
            return [];
        }

        $sql = "SELECT DISTINCT " . strtolower($column) . " FROM products ORDER BY " . strtolower($column);
        $results = $this->db->query($sql)->fetchAll();

        $values = [];
        foreach ($results as $row) {
            $values[] = $row[strtolower($column)];
        }

        return $values;
    }

    public function getID(){
        return $this->ID;
    }

    public function setID($ID){
        $this->ID = $ID;
    }

    public function getCategoryID(){
        return $this->CategoryID;
    }

    public function setCategoryID($CategoryID){
        $this->CategoryID = $CategoryID;
    }

    public function getName(){
        return $this->Name;
    }

    public function setName($Name){
        $this->Name = $Name;
    }

    public function getDescription(){
        return $this->Description;
    }

    public function setDescription($Description){
        $this->Description = $Description;
    }

    public function getPrice(){
        return $this->Price;
    }

    public function setPrice($Price){
        $this->Price = $Price;
    }

    public function getStatus(){
        return $this->Status;
    }

    public function setStatus($Status){
        $this->Status = $Status;
    }

    public function getInventoryCount(){
        return $this->InventoryCount;
    }

    public function setInventoryCount($InventoryCount){
        $this->InventoryCount = $InventoryCount;
    }

    public function getIncomingCount(){
        return $this->IncomingCount;
    }

    public function setIncomingCount($IncomingCount){
        $this->IncomingCount = $IncomingCount;
    }

    public function getOutOfStock(){
        return $this->OutOfStock;
    }

    public function setOutOfStock($OutOfStock){
        $this->OutOfStock = $OutOfStock;
    }

    public function getGrade(){
        return $this->Grade;
    }

    public function setGrade($Grade){
        $this->Grade = $Grade;
    }

    public function getImage(){
        return $this->Image;
    }

    public function setImage($Image){
        $this->Image = $Image;
    }

    public function getInventoryStatus(){
        if ($this->InventoryCount == 0) {
            return "0 in stock";
        } else {
            return "{$this->InventoryCount} in stock";
        }
    }
}