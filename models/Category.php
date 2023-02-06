<?php

require_once "class/DatabaseTools.php";

class Category extends DatabaseTools {
    
    private $dbTools;
    private $db;
    
    public function __construct(){
        $this->dbTools  = new DatabaseTools;
        $this->db = $this->dbTools->connexion;
    }
    
    public function getAllCategories(): array{
        $sql = "SELECT category_id,category_name FROM categories ORDER BY category_id";
        $categories = $this->dbTools->dbSelectAll($sql);
        return $categories;
    }

    /**
     * Ajoute une catégorie dans la bdd 
     *
     * @param string $category_name le nom de la categorie à ajouter 
     * @param int $category_author l'id de l'auteur de l'ajout 
     * @return bool `true` ,`false` sinon
    */
    public function addCategory($category_name,$category_author){
        $sql = $this->db->prepare("INSERT INTO categories (category_name, category_author) 
        VALUES (:category_name, :category_author)"); 
        $sql->bindValue('category_name', $category_name, PDO::PARAM_STR);
        $sql->bindValue('category_author', $category_author, PDO::PARAM_INT);
        return $sql->execute();
        
    }

    /**
     * Vérifie si une catégorie existe dans la table `categories` de la base de données.
     *
     * @param string $category_name nom de la catégorie à vérifier
     * @return bool `true` si elle existe, `false` sinon
    */
    public function categorieExist(string $category_name){
        $query = 'SELECT COUNT(*) FROM categories WHERE category_name = :catName';
        $sth = $this->db->prepare($query);
        $sth->bindValue(':catName', $category_name, PDO::PARAM_STR);
        $sth->execute();
        $count = (int) $sth->fetchColumn();

        return $count > 0;
    }


    /**
     * Supprime une catégorie à partir de son id 
     *
     * @param string $idCategory l'id de la catégorie à supprimer 
     * @return bool `true` ,`false` sinon
    */
    public function deleteCategory($idCategory): bool{

        $sql = "DELETE FROM categories WHERE category_id = :idCategory";
        $params = array("idCategory" =>  $idCategory);
        $execution = $this->dbTools->deleteInBdd($sql,$params);

        return $execution;
    }

}

