<?php

require_once "class/DatabaseTools.php";

class Comment extends DatabaseTools {

    private $dbTools;
    private $db;
    
    public function __construct(){
        $this->dbTools  = new DatabaseTools;
        $this->db = $this->dbTools->connexion;
    }
    
    /**
     * Insère un nouveau commentaire dans la base de données en utilisant une requête SQL préparée.
     *
     * @param int $commentDate La date de création du commentaire format timestamp
     * @param int $commentAuthor L'id de l'auteur du commentaire 
     * @param int $idPost L'id du post sur lequel le commentaire est posté
     * @param string $commentContent Le contenu du commentaire
     *
     * @return bool Renvoie true si l'insertion a réussi, sinon false
    */
    public function insertComment(int $commentDate, int $commentAuthor, int $idPost, string $commentContent): bool{
        $sql = "INSERT INTO comments (comment_author, comment_post, comment_content, comment_created_date) 
                VALUES (:comment_author, :comment_post, :comment_content, :comment_created_date)";

        $sth = $this->db->prepare($sql); 

        $sth->bindValue(':comment_author', $commentAuthor, PDO::PARAM_INT);
        $sth->bindValue(':comment_post', $idPost, PDO::PARAM_INT);
        $sth->bindValue(':comment_content', $commentContent, PDO::PARAM_STR);
        $sth->bindValue(':comment_created_date', $commentDate, PDO::PARAM_INT);
                                                
        return $sth->execute();
    }


    /**
     * Vérifie si l'utilisateur connecté est l'auteur d'un commentaire donné
     * 
     * @param int $commentId : l'id du commentaire à vérifier
     * @param int $userId : l'id de l'utilisateur connecté
     * @return bool : true si l'utilisateur est l'auteur, false sinon
     */
    public function isAuthor(int $commentId, int $userId) {
        $sql = "SELECT COUNT(*) FROM comments WHERE comment_id = :commentId AND user_id = :userId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":commentId", $commentId, PDO::PARAM_INT);
        $stmt->bindValue(":userId", $userId, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        return ($count > 0);
    }


    
    /**
     * Récupère tous les commentaires d'un post grâce à son id.
     *
     * @param int $idPost L'id du post pour lequel récupérer les commentaires
     * @return array Un tableau contenant tous les commentaires du post
     */
    public function getAllComments(int $idPost): array{

        $sql = "SELECT c.comment_id, c.comment_content, c.comment_created_date, u.user_pseudo, u.user_avatar
                FROM comments AS c
                LEFT JOIN users AS u
                    ON u.user_id = c.comment_author
                JOIN posts AS p
                    ON p.post_id = c.comment_post
                WHERE p.post_id = :post_id";

        $params = array("post_id" => $idPost);

        $allComments = $this->dbTools->dbSelectAll($sql,$params);

        return $allComments;
    }


    
    /**
     * Supprime un commentaire à partir de son identifiant en utilisant une requête SQL préparée.
     *
     * @param int $idComment L'identifiant du commentaire à supprimer
     * @return bool Renvoie true si la suppression a réussi, sinon false
     */
    public function deleteComment(int $idComment){
        $sql = "DELETE FROM comments WHERE comment_id = :idComment";
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':idComment', $idComment, PDO::PARAM_INT);
        
        return $sth->execute();
    }
    

    
}

