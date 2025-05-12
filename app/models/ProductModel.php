<?php

class ProductModel{
    private $ID;
    private $Name;
    private $Description;
    private $Price;
    private $Status;
    private $InventoryCount;
    private $IncomingCount;
    private $OutOfStock;
    private $Grade;
    private $Image;

    public function __construct($ID, $Name, $Description, $Price, $Status = 'Scoping', $InventoryCount = 45, $IncomingCount = 0, $OutOfStock = 11, $Grade = 'A', $Image = ''){
        $this->ID = $ID;
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

    public function getID(){
        return $this->ID;
    }

    public function setID($ID){
        $this->ID = $ID;
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
            return $this->InventoryCount . " in stock";
        }
    }
}

?>