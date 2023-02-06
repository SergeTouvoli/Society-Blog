<?php
require_once "class/DatabaseTools.php";

class Post extends DatabaseTools {
    
    private $dbTools;
    private $db;
    
    public function __construct(){
        $this->dbTools  = new DatabaseTools;
        $this->db = $this->dbTools->connexion;
    }
    
    
    public function getnbPosts(){ 
        $nbPosts = $this->dbTools->countEntry("posts"); 
        return $nbPosts['COUNT(*)'];
    }

    public function getRecentsPosts(){
        $sql = "SELECT post_id,post_title,post_slug FROM posts ORDER BY post_created_date DESC LIMIT 3";
        $recentsPosts = $this->dbTools->dbSelectAll($sql);        
        return $recentsPosts;
    }

    public function liveSearch($recherche){
        $sql = "SELECT post_title,post_slug
        FROM posts
        WHERE LOWER(post_title) LIKE :string OR LOWER(post_content) LIKE :string";

        $params = array(':string' => '%'.$recherche.'%');
        return $this->dbTools->dbSelectAll($sql,$params);
    }

    public function getPosts($premier,$parPage){

        $sql = "SELECT 
                p.post_title, 
                p.post_slug, 
                p.post_image, 
                p.post_created_date, 
                p.post_content, 
                u.user_pseudo, 
                u.user_avatar, 
                COUNT(c.comment_id) AS comment_count 
            FROM posts p 
            LEFT JOIN users u 
                ON u.user_id = p.post_author 
            LEFT JOIN comments c 
                ON c.comment_post = p.post_id 
            GROUP BY p.post_id 
            ORDER BY p.post_created_date DESC 
            LIMIT :premier, :parpage;
            ";

        $sth = $this->db->prepare($sql);  

        $sth->bindValue(':premier',$premier, PDO::PARAM_INT);
        $sth->bindValue(':parpage',$parPage, PDO::PARAM_INT);
        $sth->execute();    
        
        $posts = $sth->fetchAll(PDO::FETCH_ASSOC);
    
        return $posts;
    }

    
    public function getPostsByCat($premier,$parPage,$categoryName){

        $sql = "SELECT
                p.post_title,
                p.post_slug,
                p.post_image,
                p.post_created_date,
                p.post_content,
                u.user_pseudo,
                c.category_name,
                COUNT(com.comment_id) AS comment_count
            FROM posts AS p
            LEFT JOIN users AS u
                ON u.user_id = p.post_author
            JOIN categories AS c
                ON c.category_id = p.post_category
            LEFT JOIN comments AS com
                ON com.comment_post = p.post_id
            WHERE c.category_name = :categoryName
            GROUP BY p.post_id
            LIMIT :premier, :parpage";
        
        $sth = $this->db->prepare($sql);  
        $sth->bindValue(':categoryName', $categoryName, PDO::PARAM_STR);
        $sth->bindValue(':premier', $premier, PDO::PARAM_INT);
        $sth->bindValue(':parpage', $parPage, PDO::PARAM_INT);
        $sth->execute();
        
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }



    public function searchPost($recherche){
        $sql = "SELECT
            p.post_title,
            p.post_slug,
            p.post_image,
            p.post_created_date,
            p.post_content,
            u.user_pseudo
        FROM posts AS p
        LEFT JOIN users AS u
            ON u.user_id = p.post_author
        WHERE p.post_title LIKE :string OR p.post_content LIKE :string";
        
        $sth = $this->db->prepare($sql);  
        $sth->bindValue(':string', '%' . $recherche . '%', PDO::PARAM_STR);
        $sth->execute();    
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    
    
    public function getnbPostsOfCat(int $id): int{ 
        $sql = "SELECT COUNT(*) FROM posts WHERE post_category = :id";
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        $sth -> execute();
        $nbPostsOfCat = $sth->fetch(PDO::FETCH_ASSOC);
        
        return $nbPostsOfCat['COUNT(*)'];   
    }


    public function getnbPostsOfCatByName(string $catName): int{ 
        
        $sth = $this->db-> prepare ('SELECT COUNT(*) FROM posts LEFT JOIN categories ON categories.category_id = posts.post_category  WHERE categories.category_name = :catName');
        $sth->bindValue(':catName', $catName, PDO::PARAM_INT);
        $sth -> execute();
        $nbPostsOfCat = $sth->fetch(PDO::FETCH_ASSOC);
        
        return $nbPostsOfCat['COUNT(*)'];
        
    }


    public function getnbLikes(int $id): int{ 
        
        $sth = $this->db-> prepare('SELECT post_like FROM posts WHERE post_id = :id');
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        $sth -> execute();
        $nbLikes = $sth->fetch(PDO::FETCH_ASSOC);
        
        return $nbLikes;
        
    }

    public function isAuthorOfPost(int $idPost, int $idUser): bool{
        $sql = "SELECT COUNT(*) FROM posts WHERE post_id = :idPost AND post_author = :idUser";
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':idPost', $idPost, PDO::PARAM_INT);
        $sth->bindValue(':idUser', $idUser, PDO::PARAM_INT);
        $sth -> execute();
        $isAuthorOfPost = $sth->fetch(PDO::FETCH_ASSOC);
        
        return $isAuthorOfPost['COUNT(*)'] > 0;
    }

    public function getAllPost(): array {    

        $sql = "SELECT p.*, u.user_id, u.user_pseudo, c.category_name
        FROM posts AS p
        LEFT JOIN users AS u
            ON u.user_id = p.post_author
        JOIN categories AS c
            ON c.category_id = p.post_category
        ORDER BY p.post_created_date DESC";
        $allPosts = $this->dbTools ->dbSelectAll($sql);        
        return $allPosts;
    }
    

    public function getAllComments(int $idPost): array{

        $sql = "SELECT c.comment_content, c.comment_created_date,u.user_pseudo,u.user_avatar
        FROM comments AS c
        LEFT JOIN users AS u
            ON u.user_id = c.comment_author
        JOIN posts AS p
            ON p.post_id = c.comment_post
        WHERE p.post_id = :post_id";
        $params = array("post_id" =>  $idPost);

        $allComments = $this->dbTools ->dbSelectAll($sql,$params);
    
        return $allComments;
    }
    
    public function getInfosPost($idPost){

        $sql = "SELECT p.*, u.user_id,u.user_firstname,c.category_name
        FROM posts AS p
        LEFT JOIN users AS u
            ON u.user_id = p.post_author
        JOIN categories AS c
            ON c.category_id = p.post_category
        WHERE p.post_id = :post_id";
   
        $params = array("post_id" =>  $idPost);
        $post = $this->dbTools ->dbSelectOne($sql,$params);

        return $post;
    }
    
    public function getPostBySlug($slug){

        $sql = "SELECT p.*, u.user_id,u.user_pseudo,c.category_name
        FROM posts AS p
        LEFT JOIN users AS u
            ON u.user_id = p.post_author
        JOIN categories AS c
            ON c.category_id = p.post_category
        WHERE p.post_slug = :slug";
   
        $params = array("slug" =>  $slug);
        $post = $this->dbTools ->dbSelectOne($sql,$params);

        return $post;
    }  
    
    public function getPostOfUser(int $idUser): array{

        $sql = "SELECT * FROM posts WHERE post_author = :idUser";

        $params = array("post_author" =>  $idUser);
        $post = $this->dbTools ->dbSelectAll($sql,$params);

        return $post;
    }
    
    public function getInfosPostByCategoryId(int $idCategory): array{

        $sql = "SELECT p.*, u.user_firstname, c.category_name
        FROM posts AS P
        LEFT JOIN users AS u
            ON u.user_id = p.post_author
        JOIN categories AS c
            ON c.category_id = p.post_category
        WHERE p.post_category = :category_id";

        $params = array("category_id" =>  $idCategory);
        $postByCat = $this->dbTools ->dbSelectAll($sql,$params);
    
        return $postByCat;
    }
    
    public function getInfosPostsOfUser(int $idUser): array{
    
        $sql = "SELECT p.*, u.user_firstname, c.category_name
        FROM posts AS p
        LEFT JOIN users AS u
            ON u.user_id = p.post_author
        JOIN categories AS c
            ON c.category_id = p.post_category
        WHERE u.user_id = :id ORDER BY p.post_id DESC";

        $params = array("id" =>  $idUser);
        $postOfUser = $this->dbTools ->dbSelectAll($sql,$params);

        return $postOfUser;
    }
        

    /**
     * Fonction permettant d'ajouter un nouveau post dans la base de données.
     *
     * @param array $addPost Tableau associatif contenant les données du post à ajouter.
     * Les clés du tableau doivent être les suivantes :
     * - 'title' : le titre du post
     * - 'slug' : l'identifiant unique du post
     * - 'image' : l'URL de l'image associée au post
     * - 'content' : le contenu du post
     * - 'category' : l'ID de la catégorie du post
     * - 'author' : l'ID de l'auteur du post
     * - 'createdAt' : la date de création du post
     * @return bool true si l'ajout a réussi, false en cas d'échec.
     */
    public function addPost(array $addPost){
        $sql = "INSERT INTO posts (post_title, post_slug, post_image, post_content, post_category, post_author, post_created_date) VALUES (:post_title, :post_slug, :post_image, :post_content, :post_category, :post_author, :post_created_date)";
    
        $sth = $this->db->prepare($sql);
    
        $sth->bindValue(':post_title', $addPost['title'], PDO::PARAM_STR);
        $sth->bindValue(':post_slug', $addPost['slug'], PDO::PARAM_STR);
        $sth->bindValue(':post_image', $addPost['image'], PDO::PARAM_STR);
        $sth->bindValue(':post_content', $addPost['content'], PDO::PARAM_STR);
        $sth->bindValue(':post_category', $addPost['category'], PDO::PARAM_INT);
        $sth->bindValue(':post_author', $addPost['author'], PDO::PARAM_INT);
        $sth->bindValue(':post_created_date', $addPost['createdAt'], PDO::PARAM_INT);
    
        return $sth->execute();
    }
    
    
    public function commentPost($comment_date,$comment_author,$comment_post,$comment_content): bool{
    
        $sth = $this->db->prepare("INSERT INTO comments (comment_author, comment_post, comment_content,comment_created_date) 
        VALUES (:comment_author, :comment_post,:comment_content,:comment_created_date)"); 

        $sth->bindValue('comment_author', $comment_author, PDO::PARAM_STR);
        $sth->bindValue('comment_post', $comment_post, PDO::PARAM_STR);
        $sth->bindValue('comment_content', $comment_content, PDO::PARAM_STR);
        $sth->bindValue('comment_created_date', $comment_date, PDO::PARAM_INT);
                                            
        return $sth->execute();
    
    }


    public function getPostByTitle(string $post_title, int $post_id): bool{
        $sth = $this->db->prepare('SELECT post_title FROM posts WHERE post_title = :post_title AND post_id != :post_id'); 
        $sth->bindValue('post_title', $post_title, PDO::PARAM_STR);
        $sth->bindValue('post_id', $post_id, PDO::PARAM_INT);
        $sth->execute();
        
        $result = $sth->fetch(PDO::FETCH_ASSOC);

        return !empty($result['post_title']);
    }

    
    /**
     * Vérifie si un titre de post existe déjà dans la base de données.
     *
     * @param string $title Le titre à vérifier
     * @return bool True si le titre existe, false sinon
     * @throws PDOException Si une erreur se produit lors de la récupération des titres
    */
    function titleExist(string $title): bool{
        $sql = "SELECT post_title FROM posts";

        $titles = $this->dbTools ->dbSelectAll($sql);
        // Vérifie si le titre passé en paramètre existe dans le tableau de titres
        return in_array($title, $titles);
    }


    /**
     * Met à jour un post dans la base de données.
     *
     * @param array $data Les données du post à mettre à jour
     * @param bool $newImg Indique si une nouvelle image a été fournie
     * @return bool True si la mise à jour a réussi, false sinon
     * @throws PDOException Si une erreur se produit lors de la mise à jour du post
    */
    public function updatePost(array $data, bool $newImg): bool{
        $sql = "UPDATE posts SET 
            post_title = :post_title, 
            post_slug = :post_slug, 
            post_content = :post_content, 
            post_category = :post_category,
            post_updated_date = :post_updated_date";

        if ($newImg) {
            $sql .= ", post_image = :post_image";
        }

        $sql .= " WHERE post_id = :post_id";

        $sth = $this->db->prepare($sql);
        $sth->bindParam('post_id', $data['id'], PDO::PARAM_INT);
        $sth->bindParam('post_title', $data['title'], PDO::PARAM_STR);
        $sth->bindParam('post_slug', $data['slug'], PDO::PARAM_STR);
        $sth->bindParam('post_content', $data['content'], PDO::PARAM_STR);
        $sth->bindParam('post_category', $data['category'], PDO::PARAM_INT);
        $sth->bindParam('post_updated_date', $data['updatedAt'], PDO::PARAM_INT);
        
        if ($newImg) {
            $sth->bindParam('post_image', $data['image'], PDO::PARAM_STR);
        }

        return $sth->execute();
    }


    /**
     * Supprime un article de la base de données.
     * @param int $post_id - L'ID de l'article à supprimer.
     * @return bool - True si la suppression a été réussie, false sinon.
    */
    public function deletePost(int $post_id): bool{

        $imageDeleted = $this->deleteImgPost($post_id);
        
        if(!$imageDeleted){
            throw(new Exception('Ce post n\'existe pas'));
        }
        
        $sth = $this->db->prepare('DELETE FROM posts WHERE post_id = :post_id'); 
        $sth->bindValue('post_id', $post_id, PDO::PARAM_INT);
        
        return $sth->execute();
    }
    

    /**
     * Supprime l'image associée à un article de la base de données.
     * @param int $post_id - L'ID de l'article pour lequel supprimer l'image.
    */
    public function deleteImgPost(int $post_id): bool{
        
        $sth = $this->db->prepare('SELECT post_image FROM posts WHERE post_id = :post_id'); 
        $sth->bindValue('post_id', $post_id, PDO::PARAM_STR);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
    
        if(!empty($result["post_image"])){
            if(file_exists('public/images/imgPost/'.$result["post_image"].'')){
                return unlink('public/images/imgPost/'.$result["post_image"].'');
            }
        }

        return false;
    }

}