<?php

require_once "class/DatabaseTools.php";

class Contact extends DatabaseTools {

    private $dbTools;
    private $db;
    
    public function __construct(){
        $this->dbTools  = new DatabaseTools;
        $this->db = $this->dbTools->connexion;
    }



    
}