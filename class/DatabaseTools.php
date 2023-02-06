<?php

require_once "models/Model.php";

class DatabaseTools extends Model {

    public $connexion;
    
    public function __construct(){
        $this->connexion = $this->getDbConnexion();
    }

    /**
     * Exécute une requête préparée de selection  et retourne le résultat.
     * Les valeurs sont liées à la requête de manière sécurisée en utilisant la méthode bindValue.
     *
     * @param string $sql La requête SQL à exécuter.
     * @param array $params Un tableau associatif contenant les éléments à lier à la requête.
     * @param string $fetchMethod La méthode à utiliser pour récupérer les résultats de la requête.
     * Peut prendre les valeurs 'fetch' ou 'fetchAll'.
     *
     * @return object|bool Retourne l'objet PDO Statement si la requête a réussi, false sinon.
    */
    public function selectFromDb(string $sql, array $params = [], string $fetchMethod = 'fetchAll'){

        $sth = $this->connexion->prepare($sql);
        // foreach ($params as $key => $value) {
        //     $sth->bindValue(":$key", $value);
        // }

        if ($sth->execute($params)) {
            if ($fetchMethod === 'fetch') {
                return $sth->fetch(PDO::FETCH_ASSOC);
            } elseif ($fetchMethod === 'fetchAll') {
                return $sth->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return false;
    }

    /**
     *  Execute une requête SQL permettant de compter le nombre d'éléments dans une table
     * @param un objet PDO de connexion
     * @param string $table le nom de la table
     * 
     * @return array tableau contenant la chaine de caractères correspondant au nombre d'entrées dans la table
     * sous forme de chaîne de caractères
     */
    public function countEntry(string $table) : array {
        $sth = $this->connexion->prepare('SELECT COUNT(*) FROM ' . $table);
        $sth->execute();
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
    
    /**             
    * Execute une requête SQL et retourne un jeu d'enregistrement complet
    * @param un objet PDO de connexion
    * @param string $sql la requête a executé
    * @param array $params tableau contenant les éléments à binder dans la requête
    *
    * @return array jeu d'enregistrement
    */
    public function dbSelectAll( string $sql, array $params = []) : array{
       $sth = $this->connexion->prepare($sql);
       $sth->execute($params);
   
       return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
    * Met à jour une ou plusieurs lignes de la base de données en utilisant une requête SQL préparée.
    *
    * @param string $sql La requête SQL à exécuter.
    * @param array $params Un tableau contenant les éléments à lier à la requête.
    *
    * @return bool Retourne true si la mise à jour a été réussie, false sinon.
    */
    public function updateInBdd(string $sql, array $params = []): bool {
        $sth = $this->connexion->prepare($sql);
        $sth->execute($params);
        return $sth->rowCount() > 0;
    }

    /**
    * Suppression d'une out de plusieurs lignes de la base de données en utilisant une requête SQL préparée.
    *
    * @param string $sql La requête SQL à exécuter.
    * @param array $params Un tableau contenant les éléments à lier à la requête.
    *
    * @return bool Retourne true si la mise à jour a été réussie, false sinon.
    */
    public function deleteInBdd( string $sql, array $params = []){
        $sth = $this->connexion->prepare($sql);
        $sth->execute($params);
        $count = $sth->rowCount();
        return $sth->rowCount() > 0;
    }
 
   /** 
    * Execute une requête SQL et retourne une ligne du jeu d'enregistrement
    
    * @param un objet PDO de connexion
    * @param string $sql la requête a executé
    * @param array $params tableau contenant les éléments à binder dans la requête
    *
    * @return array jeu d'enregistrement
    */
    public function dbSelectOne(string $sql, array $params = []){
       $sth = $this->connexion->prepare($sql);
       $sth->execute($params);
       return $sth->fetch(PDO::FETCH_ASSOC);
    }
}