<?php

class ProductModel{
    private $ID;
    private $Name;
    private $Description;
    private $Price;

    public function __construct($ID, $Name, $Description, $Price){
        $this->ID = $ID;
        $this->Name = $Name;
        $this->Description = $Description;
        $this->Price = $Price;
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
}

?>