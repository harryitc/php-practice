<?php

require_once 'app/core/Database.php';

class OrderStatusHistoryModel
{
    private $id;
    private $orderId;
    private $oldStatus;
    private $newStatus;
    private $changedBy;
    private $changeReason;
    private $notes;
    private $createdAt;

    private $db;

    public function __construct($id = null, $orderId = null, $oldStatus = null, $newStatus = null, $changedBy = null, $changeReason = null, $notes = null, $createdAt = null)
    {
        $this->db = Database::getInstance();

        $this->id = $id;
        $this->orderId = $orderId;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
        $this->changeReason = $changeReason;
        $this->notes = $notes;
        $this->createdAt = $createdAt;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getOrderId() { return $this->orderId; }
    public function getOldStatus() { return $this->oldStatus; }
    public function getNewStatus() { return $this->newStatus; }
    public function getChangedBy() { return $this->changedBy; }
    public function getChangeReason() { return $this->changeReason; }
    public function getNotes() { return $this->notes; }
    public function getCreatedAt() { return $this->createdAt; }

    // Setters
    public function setOrderId($orderId) { $this->orderId = $orderId; }
    public function setOldStatus($oldStatus) { $this->oldStatus = $oldStatus; }
    public function setNewStatus($newStatus) { $this->newStatus = $newStatus; }
    public function setChangedBy($changedBy) { $this->changedBy = $changedBy; }
    public function setChangeReason($changeReason) { $this->changeReason = $changeReason; }
    public function setNotes($notes) { $this->notes = $notes; }

    /**
     * Save status history record
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
     * Insert new status history record
     */
    private function insert()
    {
        $sql = "INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, change_reason, notes)
                VALUES (:order_id, :old_status, :new_status, :changed_by, :change_reason, :notes)";

        $result = $this->db->query($sql)->bind([
            'order_id' => $this->orderId,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'changed_by' => $this->changedBy,
            'change_reason' => $this->changeReason,
            'notes' => $this->notes
        ])->execute();

        if ($result) {
            $this->id = $this->db->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Update existing status history record
     */
    private function update()
    {
        $sql = "UPDATE order_status_history
                SET order_id = :order_id, old_status = :old_status, new_status = :new_status,
                    changed_by = :changed_by, change_reason = :change_reason, notes = :notes
                WHERE id = :id";

        return $this->db->query($sql)->bind([
            'id' => $this->id,
            'order_id' => $this->orderId,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'changed_by' => $this->changedBy,
            'change_reason' => $this->changeReason,
            'notes' => $this->notes
        ])->execute();
    }

    /**
     * Get status history for an order
     */
    public static function getByOrderId($orderId)
    {
        $db = Database::getInstance();
        $sql = "SELECT osh.*, u.name as changed_by_name
                FROM order_status_history osh
                LEFT JOIN users u ON osh.changed_by = u.id
                WHERE osh.order_id = :order_id
                ORDER BY osh.created_at DESC";

        $results = $db->query($sql)->fetchAll(['order_id' => $orderId]);

        $history = [];
        foreach ($results as $row) {
            $item = new self(
                $row['id'],
                $row['order_id'],
                $row['old_status'],
                $row['new_status'],
                $row['changed_by'],
                $row['change_reason'],
                $row['notes'],
                $row['created_at']
            );
            $item->changedByName = $row['changed_by_name'];
            $history[] = $item;
        }

        return $history;
    }

    /**
     * Create status change record
     */
    public static function createStatusChange($orderId, $oldStatus, $newStatus, $changedBy = null, $reason = null, $notes = null)
    {
        $history = new self(null, $orderId, $oldStatus, $newStatus, $changedBy, $reason, $notes);
        return $history->save();
    }
}
