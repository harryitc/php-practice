<?php

require_once 'app/core/Database.php';

class CategoryModel
{
    private $id;
    private $name;
    private $description;
    private $createdAt;
    private $updatedAt;
    
    private $db;
    
    public function __construct($id = null, $name = '', $description = '')
    {
        $this->db = Database::getInstance();
        
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }
    
    /**
     * Find all categories
     * 
     * @return array
     */
    public function findAll()
    {
        $sql = "SELECT * FROM categories ORDER BY name";
        $results = $this->db->query($sql)->fetchAll();
        
        $categories = [];
        foreach ($results as $row) {
            $category = new CategoryModel(
                $row['id'],
                $row['name'],
                $row['description']
            );
            $categories[] = $category;
        }
        
        return $categories;
    }
    
    /**
     * Find category by ID
     * 
     * @param int $id
     * @return CategoryModel|null
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $result = $this->db->query($sql)->fetch(['id' => $id]);
        
        if (!$result) {
            return null;
        }
        
        return new CategoryModel(
            $result['id'],
            $result['name'],
            $result['description']
        );
    }
    
    /**
     * Save category (insert or update)
     * 
     * @return bool
     */
    public function save()
    {
        if ($this->id) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }
    
    /**
     * Insert new category
     * 
     * @return bool
     */
    private function insert()
    {
        $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
        
        $result = $this->db->query($sql)->bind([
            'name' => $this->name,
            'description' => $this->description
        ])->execute();
        
        if ($result) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Update existing category
     * 
     * @return bool
     */
    private function update()
    {
        $sql = "UPDATE categories SET name = :name, description = :description WHERE id = :id";
        
        return $this->db->query($sql)->bind([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description
        ])->execute();
    }
    
    /**
     * Delete category
     * 
     * @return bool
     */
    public function delete()
    {
        if (!$this->id) {
            return false;
        }
        
        $sql = "DELETE FROM categories WHERE id = :id";
        return $this->db->query($sql)->bind(['id' => $this->id])->execute();
    }
    
    /**
     * Get category ID
     * 
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set category ID
     * 
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Get category name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set category name
     * 
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Get category description
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Set category description
     * 
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
