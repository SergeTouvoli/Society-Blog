<?php
require_once "models/Comment.php";
require_once "models/Post.php";
require_once "models/User.php";

class CommentController extends AbstractController {

    private $comment;
    private $post;
    private $user;

    public function __construct() {
        $this->comment = new Comment();
        $this->post = new Post();
        $this->user = new User();
    }


    /**
     * 
     * Ajoute un commentaire à un post. 
     * Vérifie si l'utilisateur est connecté et si le contenu du commentaire est valide.
     * Si le commentaire est valide, il est ajouté à la base de données.
     * @return void
     */
    public function addComment() {
        
        if(isset($_POST['comment_content']) && !empty($_POST['comment_content'])){
            $response = array(
                'success' => false,
                'errors' => array()
            );

            if(!$this->user->isConnected()){
                $response['errors'][] = "Vous devez être connecté pour poster un commentaire";
            }else{

                $comment_author = intval($_SESSION['id']) ;
                $comment_content = Tools::sanitize(($_POST['comment_content']));
                $comment_date = time();
                $idPost = intval($_POST['idPost']);
                
                if($comment_content == ''){ 
                    $response['errors'] = "Veuillez saisir votre commentaire ";  
                }
    
                if(!empty($comment_content)){
                    $numberMinimalOfCaracters = 3;
                    $numberMaximalOfCaracters = 150;
                    if(strlen($comment_content) < $numberMinimalOfCaracters ) { 
                        $response['errors'][] = "Minimum $numberMinimalOfCaracters caractères"; 
                    }
                    if(strlen($comment_content) > $numberMaximalOfCaracters ) { 
                        $response['errors'][] = "Maximum $numberMaximalOfCaracters caractères";
                    }
                }
                
                if(count($response['errors']) == 0) {
                    if($this->post->commentPost($comment_date,$comment_author,$idPost,$comment_content)){
                        $response['success'] = true;
                    }else{
                        $response['errors'][] = "Une erreur est survenue lors de l'ajout du commentaire"; 
                    }
                }
            }

            echo json_encode($response);
        }
        
    }

    /**
     * 
     * Supprime un commentaire en fonction de son identifiant et du slug du post associé.
     * 
     * @param int $idComment L'identifiant du commentaire à supprimer.
     * @param string $slug Le slug du post associé au commentaire.
     * @return void
    */
    public function deleteComment(int $idComment, string $slug) {
        $deleteComment = false;
        if($this->user->isConnected() && ($this->user->isAdmin() || $this->comment->isAuthor($idComment, $this->user->getId()))) {
            $deleteComment = $this->comment->deleteComment($idComment);
        }
        
        // Si le commentaire a été supprimé, redirige vers la page du post avec un message de succès
        if ($deleteComment) {
            $this->redirectTo(PAGE_POST . '/' . $slug,'Le commentaire a bien été supprimé');
            exit();
        }
    }

    /**
     * Récupère tous les commentaires associés à un post et les affiche
     * Récupère l'id du post via une requête AJAX POST
     * Affiche l'avatar, le pseudo, la date et le contenu du commentaire
     * Affiche un bouton de suppression pour les commentaires de l'utilisateur connecté
     */
    public function getAllComments() {

        $output = "";
        if(isset($_POST['idPost']) && !empty($_POST['idPost'])){

            $idPost = intval($_POST['idPost']);

            $comments = $this->comment->getAllComments($idPost);

            foreach ($comments as $c) {
                $commentId = intval($c['comment_id']);
                $pseudo = htmlspecialchars($c['user_pseudo']);
                $avatar = htmlspecialchars($c['user_avatar'] );
                $createdDate = Tools::convertTimestampToFrenchDate($c['comment_created_date']);
                $slug = $this->post->getSlugById($idPost);

                $output .= '<div class="commentSection">';
                $output .= '<a href="' . URL . PAGE_COMPTE . '/' .$pseudo. '"><img src="' . URL . 'public/images/avatars/' . $avatar . '" alt="" ></a>';
                $output .= '<div class="commentContent">';
                $output .= '<p class="commentAuthor">';
                $output .= '<a href="' . URL . PAGE_COMPTE . '/' . $pseudo. '">' . $pseudo . ',' . '</a>';
                $output .= '<span style="font-size:2rem">' .$createdDate. '</span>';
                $output .= '</p>';
                $output .= '<p>' . htmlspecialchars($c['comment_content']) . '</p>';
                $output .= '</div>';
                // Vérifie si l'utilisateur est connecté et est l'auteur du commentaire ou s'il est administrateur afin d'afficher le bouton de suppression
                if($this->user->isConnected() && ($this->user->isAdmin() || $this->comment->isAuthor($commentId, $this->user->getId()))) {
                    $output .= '<a href="' . URL.DELETE_COMMENT . '/' . $commentId . '/' . $slug . '" class="deleteComment" title ="supprimer ce commentaire"><i class="fas fa-trash"></i></a>';
                }
                $output .= '</div>';
            }
            
        } 

        echo $output;

    }

}
