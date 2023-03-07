<?php

require_once "class/DatabaseTools.php";

class Contact extends DatabaseTools {

    private $dbTools;
    private $db;
    
    public function __construct(){
        $this->dbTools  = new DatabaseTools;
        $this->db = $this->dbTools->connexion;
    }

    /**
     * Récupère tous les messages de la table contacts avec l'adresse email de l'auteur
     *
     * @return array Tableau associatif contenant les informations de tous les messages avec en plus l'adresse email de l'auteur 
     */
    public function getAllMessages(){
        $sql = "SELECT c.*, u.user_mail AS author
                FROM contacts AS c
                INNER JOIN users AS u ON c.contact_author = u.user_id
                ORDER BY c.contact_date DESC";

        $messages = $this->dbTools->dbSelectAll($sql);
        return $messages;
    }

    /**
     * Récupère un message de la table contacts avec l'adresse email de l'auteur
     *
     * @param int $idContact Identifiant du message à récupérer
     *
     * @return array|bool Tableau associatif contenant les informations du message ou FALSE si aucun message n'a été trouvé
     */
    public function getMessage(int $idContact){
        $sql = "SELECT c.*, u.user_mail AS author
                FROM contacts AS c
                INNER JOIN users AS u ON c.contact_author = u.user_id
                WHERE c.contact_id = :contact_id";

        $params = [":contact_id" => $idContact];
        $message = $this->dbTools->dbSelectOne($sql, $params);
        return $message;
    }
    
 
    /**
     * Ajoute un message dans la table contacts
     *
     * @param array $data Tableau associatif contenant les informations du message à ajouter
     *
     * @return bool Retourne TRUE si l'ajout a été effectué avec succès ou FALSE en cas d'erreur
    */
    public function addMessage(array $data) {
        $sql = "INSERT INTO contacts (contact_author, contact_subject, contact_content, contact_date) 
                            VALUES (:contact_author, :contact_subject, :contact_content, :contact_date)";
                    
        $sth = $this->db->prepare($sql); 
                    
        $sth->bindValue(':contact_author', $data['author'], PDO::PARAM_INT);
        $sth->bindValue(':contact_subject', $data['subject'], PDO::PARAM_STR);
        $sth->bindValue(':contact_content', $data['content'], PDO::PARAM_STR);
        $sth->bindValue(':contact_date', $data['date'], PDO::PARAM_INT);
        
        return $sth->execute();
    }
    
    /**
     * Supprime un message de la table contacts
     *
     * @param int $id Identifiant du message à supprimer
     *
     * @return bool Retourne TRUE si la suppression a été effectuée avec succès ou FALSE en cas d'erreur
     */
    public function deleteMessage(int $id){
        $sql = "DELETE FROM contacts WHERE contact_id = :contact_id";
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':contact_id', $id, PDO::PARAM_INT);
        return $sth->execute();
    }




    
}