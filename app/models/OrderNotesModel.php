<?php

require_once 'app/core/Database.php';

class OrderNotesModel
{
    private $id;
    private $orderId;
    private $userId;
    private $noteType;
    private $title;
    private $content;
    private $isVisibleToCustomer;
    private $priority;
    private $createdAt;
    private $updatedAt;

    private $db;

    public function __construct($id = null, $orderId = null, $userId = null, $noteType = 'internal', $title = null, $content = null, $isVisibleToCustomer = false, $priority = 'normal')
    {
        $this->db = Database::getInstance();

        $this->id = $id;
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->noteType = $noteType;
        $this->title = $title;
        $this->content = $content;
        $this->isVisibleToCustomer = $isVisibleToCustomer;
        $this->priority = $priority;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getOrderId() { return $this->orderId; }
    public function getUserId() { return $this->userId; }
    public function getNoteType() { return $this->noteType; }
    public function getTitle() { return $this->title; }
    public function getContent() { return $this->content; }
    public function getIsVisibleToCustomer() { return $this->isVisibleToCustomer; }
    public function getPriority() { return $this->priority; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }

    // Setters
    public function setOrderId($orderId) { $this->orderId = $orderId; }
    public function setUserId($userId) { $this->userId = $userId; }
    public function setNoteType($noteType) { $this->noteType = $noteType; }
    public function setTitle($title) { $this->title = $title; }
    public function setContent($content) { $this->content = $content; }
    public function setIsVisibleToCustomer($isVisibleToCustomer) { $this->isVisibleToCustomer = $isVisibleToCustomer; }
    public function setPriority($priority) { $this->priority = $priority; }

    /**
     * Save note
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
     * Insert new note
     */
    private function insert()
    {
        $sql = "INSERT INTO order_notes (order_id, user_id, note_type, title, content, is_visible_to_customer, priority)
                VALUES (:order_id, :user_id, :note_type, :title, :content, :is_visible_to_customer, :priority)";

        $result = $this->db->query($sql)->bind([
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'note_type' => $this->noteType,
            'title' => $this->title,
            'content' => $this->content,
            'is_visible_to_customer' => $this->isVisibleToCustomer ? 1 : 0,
            'priority' => $this->priority
        ])->execute();

        if ($result) {
            $this->id = $this->db->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Update existing note
     */
    private function update()
    {
        $sql = "UPDATE order_notes
                SET order_id = :order_id, user_id = :user_id, note_type = :note_type,
                    title = :title, content = :content, is_visible_to_customer = :is_visible_to_customer,
                    priority = :priority
                WHERE id = :id";

        return $this->db->query($sql)->bind([
            'id' => $this->id,
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'note_type' => $this->noteType,
            'title' => $this->title,
            'content' => $this->content,
            'is_visible_to_customer' => $this->isVisibleToCustomer ? 1 : 0,
            'priority' => $this->priority
        ])->execute();
    }

    /**
     * Delete note
     */
    public function delete()
    {
        if (!$this->id) {
            return false;
        }

        $sql = "DELETE FROM order_notes WHERE id = :id";
        return $this->db->query($sql)->bind(['id' => $this->id])->execute();
    }

    /**
     * Get notes for an order
     */
    public static function getByOrderId($orderId, $visibleToCustomer = null)
    {
        $db = Database::getInstance();

        $sql = "SELECT on.*, u.name as author_name
                FROM order_notes on
                LEFT JOIN users u ON on.user_id = u.id
                WHERE on.order_id = :order_id";

        $params = ['order_id' => $orderId];

        if ($visibleToCustomer !== null) {
            $sql .= " AND on.is_visible_to_customer = :visible_to_customer";
            $params['visible_to_customer'] = $visibleToCustomer ? 1 : 0;
        }

        $sql .= " ORDER BY on.created_at DESC";

        $results = $db->query($sql)->fetchAll($params);

        $notes = [];
        foreach ($results as $row) {
            $note = new self(
                $row['id'],
                $row['order_id'],
                $row['user_id'],
                $row['note_type'],
                $row['title'],
                $row['content'],
                $row['is_visible_to_customer'],
                $row['priority']
            );
            $note->createdAt = $row['created_at'];
            $note->updatedAt = $row['updated_at'];
            $note->authorName = $row['author_name'];
            $notes[] = $note;
        }

        return $notes;
    }

    /**
     * Get customer visible notes for an order
     */
    public static function getCustomerNotes($orderId)
    {
        return self::getByOrderId($orderId, true);
    }

    /**
     * Get internal notes for an order
     */
    public static function getInternalNotes($orderId)
    {
        return self::getByOrderId($orderId, false);
    }

    /**
     * Add a quick note
     */
    public static function addNote($orderId, $content, $userId = null, $noteType = 'internal', $title = null, $isVisibleToCustomer = false, $priority = 'normal')
    {
        $note = new self(null, $orderId, $userId, $noteType, $title, $content, $isVisibleToCustomer, $priority);
        return $note->save();
    }
}
