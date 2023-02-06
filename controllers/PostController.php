<?php
require_once "models/Post.php";
require_once "models/User.php";
require_once "models/Category.php";
require_once "class/Tools.php";

class PostController extends AbstractController  {

    private $user;
    private $post;
    private $categorie;
    private $tools;

    public function __construct(){
        $this->user = new User;
        $this->post = new Post;
        $this->categorie = new Category;
        $this->tools = new Tools;
    }


    /**
     * Supprime un post.
     *
     * Cette fonction vérifie si l'utilisateur connecté est l'auteur du post ou un administrateur,
     * puis appelle la fonction `deletePost` du modèle `post` pour supprimer le post.
     * Si l'utilisateur n'est pas connecté ou n'est pas l'auteur du post ou un administrateur,
     * l'utilisateur est redirigé vers la page d'accueil.
     * Si la suppression du post a réussi, l'utilisateur est redirigé vers la page "Mes posts" avec un message de confirmation.
     *
     * @param int $idPost L'ID du post à supprimer.
     * @return void
    */
    public function deletePost(int $idPost): void{

        $idPost = intval($idPost);

        // Vérifie si l'utilisateur est connecté
        if (!$this->user->isConnected()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        if($this->post->isAuthorOfPost($idPost,$_SESSION['id']) || $this->user->isAdmin()){
            if ($this->post->deletePost($idPost)) {
                $this->redirectTo(PAGE_MES_POSTS,'Post supprimé !');
                return;
            }
        }else{
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }
    }


    /**
     * liveSearchPost
     * 
     * Effectue une recherche de posts en fonction de la chaîne de caractères spécifiée en entrée.
     * Affiche les résultats de la recherche sous forme de liens vers les pages de chaque post.
     * 
    */
    public function liveSearchPost(){
        if(isset($_POST['string'])){
            $recherche = Tools::sanitize(strtolower($_POST['string']));
            
            $posts = $this->post->liveSearch($recherche);

            $output = "";
            if(!empty($posts)) {

                foreach($posts as $post){

                    $name = Tools::sanitize($post['post_title']);
                    $slug = Tools::sanitize($post['post_slug']);
        
                    $output .= '<a href="'.URL.'post/'.$slug.'">';
                    $output .= '    <li>';
                    $output .= '        <div id="listPostSearch">';
                    $output .= '            <span class="namepost">'.$name.'</span>';
                    $output .= '        </div>';  
                    $output .= '    </li>';
                    $output .= '</a>';

                }
                
            }else{
                $output .= '<li class="noResult"> Aucun résultat pour votre recherche</li>';

            }            

            echo $output;
        }else{
            $this->redirectTo(PAGE_ACCUEIL);
        }

    }


    /**
     * 
     * getAddPostPage
     * 
     * Fonction qui affiche la page d'ajout de post
     * La fonction vérifie d'abord si l'utilisateur est connecté. Si ce n'est pas le cas,
     * l'utilisateur est redirigé vers la page de connexion. Ensuite, elle récupère les
     * catégories de la base de données et traite le formulaire d'ajout de post si celui-ci
     * a été soumis. Si le formulaire a été soumis, la fonction vérifie si tous les champs
     * sont remplis et s'il n'y a pas de titre de post déjà existant. Si toutes ces vérifications
     * sont passées, la fonction tente d'ajouter le post à la base de données et redirige
     * l'utilisateur vers la page du post ajouté.
     * 
    */
    public function getAddPostPage(){
        
        // Initialisation des variables
        $pageTitle = "Ajoutez un article";
        $pageDescription = "";
        $errors = []; 
        $valids = [];
        $addPost = [
            'title' => '',
            'slug' => '',
            'image' => '',
            'content' => '',
            'category' => '',
            'author' => '',
            'createdAt' => ''
        ];

        // Vérification de la connexion de l'utilisateur
        if(!$this->user->isConnected()) {
            $this->redirectTo(PAGE_CONNEXION);
            return;
        }
    
        // Récupération des catégories
        $categories = $this->categorie->getAllCategories();
    
        // Traitement de l'ajout d'un post
        if(isset($_POST['addPost']) && !empty($_POST['addPost'])){ 

            $addPost['title'] = Tools::sanitize($_POST['post_title']);
            $addPost['slug'] = $this->tools->Slug($addPost['title']);
            $addPost['content'] = Tools::sanitize($_POST['post_content']);
            $addPost['category'] = intval($_POST['post_category']);
            $addPost['author'] = intval($_SESSION['id']);
            $addPost['createdAt'] = time();

            if($addPost['title'] == ''){ $errors[] = "Veuillez remplir le champ 'Titre' !"; }
            if($addPost['content'] == ''){ $errors[] = "Veuillez remplir le champ 'Contenue' !"; }

            if($this->post->titleExist($addPost['title'])){ $errors[] = 'Un article possède déja ce titre'; }

            if(!empty($addPost['content'])){ 
                $numberMinimalOfCaracters = 50;
                $numberMaximalOfCaracters = 3000;
                if(strlen($addPost['content']) < $numberMinimalOfCaracters ) { $errors[] = "Minimum ".$numberMinimalOfCaracters."caractères"; }
                if(strlen($addPost['content']) > $numberMaximalOfCaracters ) { $errors[] = "Maximum ".$numberMaximalOfCaracters."caractères"; }
            }

            if(count($errors) == 0){
                if(isset($_FILES['post_image']) &&  $_FILES['post_image']['name'] !== '' ){
                    $uploadResult = Tools::uploadFile($_FILES['post_image'], $errors, UPLOADS_DIR.'imgPost/');
                    if (count($errors) == 0) {
                        $addPost['image'] = $uploadResult;
                    }
                }
            
        
                if(count($errors) == 0){
                    if($this->post->addPost($addPost)){
                        $_SESSION['message'] = "Post ajouté avec succès !";
                        header('Location: '.PAGE_POST.'/'.$addPost['slug'].'');
                    }
                }
            
            }
       
            
        }
    
        require_once "views/post/addPost.phtml";
    }


    
    #TODO refactoriser cette fonction , retirer le traitement de l'ajout d'un comment  
    ## une deuxième fonction(addComment) est à faire 
    public function getPostPage($slug){
        
        $slug = htmlspecialchars($slug);
        //Recup des infos sur le post 
        $post = $this->post->getPostBySlug($slug); 
        
        if(empty($post)) { 
            throw new Exception("L'article que vous souhaitez consulter n'existe pas");
        }

        $idPost = $post['post_id'];   
        $pageTitle = htmlspecialchars($post['post_title']);
        $pageDescription ="Vous lisez l'article ". htmlspecialchars($post['post_title']);

        //Recup des autres infos 
        $recentsPosts = $this->post->getRecentsPosts();
        $comments = $this->post->getAllComments($idPost);
        $lastUser = $this->user->getLastUser();   
        $nbUsers = $this->user->getnbUsers();
        $categories = $this->categorie->getAllCategories();

        if(isset($_POST['comment_content']) && !empty($_POST['comment_content'])){
            $errors = [];

            $comment_author = intval($_SESSION['id']) ;
            $comment_content = Tools::sanitize(($_POST['comment_content']));
            $comment_date = time();
            
            if($comment_content == ''){ 
                $errors[] = "Veuillez saisir votre commentaire "; echo "<script>alert('Veuillez saisir votre commentaire !');</script>";  
            }

            if(!empty($comment_content)){
                $numberMinimalOfCaracters = 3;
                $numberMaximalOfCaracters = 150;
                if(strlen($comment_content) < $numberMinimalOfCaracters ) { 
                    $errors[] = "Minimum $numberMinimalOfCaracters caractères"; echo "<script>alert('Minimum $numberMinimalOfCaracters caractères');</script>"; 
                }
                if(strlen($comment_content) > $numberMaximalOfCaracters ) { 
                    $errors[] = "Maximum $numberMaximalOfCaracters caractères" ; echo "<script>alert('Maximum $numberMaximalOfCaracters caractères');</script>";
                }
            }
            
            if(count($errors) == 0) {
                if($this->post->commentPost($comment_date,$comment_author,$idPost,$comment_content)){
                    $_SESSION['message'] = "Commentaire ajouté avec succès !";
                    $this->redirectTo(PAGE_POST."/".$slug);
                    return;
                }else{
                    echo "<script>alert('Une erreur est survenue !');</script>";
                }
            }
        }
        require_once "views/post/post.phtml"; 
    }

 
    /**
     * 
     * getEditPostPage
     * 
     * Affiche la page de modification d'un post
     *
     * Cette fonction vérifie si l'utilisateur est connecté et si le slug du post est valide. Si ce n'est pas le cas, l'utilisateur est redirigé vers la page d'accueil.
     * Si l'utilisateur est l'auteur du post ou s'il est administrateur, la page de modification du post est affichée.
     * Si l'utilisateur n'est ni l'auteur du post ni administrateur, il est également redirigé vers la page d'accueil.
     *
     * @param string $slug Le slug du post à modifier
     *
     * @throws Exception Si l'article à modifier n'existe pas
    */
    public function getEditPostPage($slug){

        $slug = htmlspecialchars($slug);

        // Vérifie si l'utilisateur est connecté et si le slug du post est valide
        if (!$this->user->isConnected() || $slug == "") {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        //Recup des infos sur le post 
        $post = $this->post->getPostBySlug($slug); 
        $post_id = $post['post_id'];
        
        // Vérifie si l'utilisateur est l'auteur du post ou si c'est un administrateur
        if (!$this->post->isAuthorOfPost($post_id, $_SESSION['id']) && !$this->user->isAdmin()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        //Traitement edit post page
        if(empty($post)) { throw new Exception("L'article que vous souhaitez modifier n'existe pas"); }

        $title = "Modifier l'article ".htmlspecialchars($post['post_title']);
        $description = "";
        $errors = []; 
        $data = [
            'id' => '',
            'title' => '',
            'slug' => '',
            'content' => '',
            'category' => '',
            'updatedAt' => ''
        ];

        //Recup des categories
        $categories = $this->categorie->getAllCategories();

        if(isset($_POST['editPost']) && !empty($_POST['editPost'])){ 
                        
            $data['id'] = $post_id;
            $data['title'] = Tools::sanitize($_POST['post_title']);
            $data['slug'] = $this->tools->Slug($data['title']);
            $data['content'] = Tools::sanitize($_POST['post_content']);
            $data['category'] = intval($_POST['post_category']);
            $data['updatedAt'] = time();
            
            if($data['title']== ''){ $errors[] = "Veuillez remplir le champ 'Titre' !"; }
            if($data['content'] == ''){ $errors[] = "Veuillez remplir le champ 'Contenu' !"; }
            if($titleExist = $this->post->getPostByTitle($data['content'],$post_id)){ $errors[] = 'Un article possède déja ce titre'; }
            
            if(!empty($editPost['content'] )){ 
                $numberMinimalOfCaracters = 50;
                $numberMaximalOfCaracters = 10000;
                if(strlen($editPost['content'] ) < $numberMinimalOfCaracters) { $errors[] = "Minimum ".$numberMinimalOfCaracters."caractères"; }
                if(strlen($editPost['content'] ) > $numberMaximalOfCaracters) { $errors[] = "Maximum ".$numberMaximalOfCaracters."caractères"; }
            }

            // Télécharge l'image si elle a été envoyée
            if(isset($_FILES['post_image']) && $_FILES['post_image']['name'] !== '') {
                $uploadResult = Tools::uploadFile($_FILES['post_image'], $errors, UPLOADS_DIR.'imgPost/');
                if (count($errors) == 0) { 
                    $this->post->deleteImgPost($post_id);
                    $data['image'] = $uploadResult; 
                }

            }

            if (count($errors) == 0) {
                // Détermine si l'image doit être mise à jour
                $updateImage = isset($data['image']);
                
                // Met à jour le post
                $updateResult = $this->post->updatePost($data, $updateImage);
                
                // Affiche un message de confirmation
                if ($updateResult) {
                    $newSlug = $data['slug'];
                    $_SESSION['message'] = "Modification effectuée avec succès !";
                    $this->redirectTo(PAGE_POST."/".$newSlug);
                    return;
                } else {
                    $errors[] = "Une erreur est survenue !";
                }
            } else {
                $errors[] = "Une erreur est survenue !";
            }
            
        
        }

        require_once "views/post/editPost.phtml";
    }


    /**
     * getSearchPage
     * 
     * Affiche la page de résultats de la recherche.
     *
     * La recherche est effectuée à partir de la variable POST 'string'. Si cette variable
     * n'est pas définie ou vide, l'utilisateur est redirigé vers la page d'accueil.
    */
    public function getSearchPage(): void{
        
        if(isset($_POST['string']) && !empty($_POST['string'])){
            $recherche = Tools::sanitize($_POST['string']);

            $result = $this->post->searchPost($recherche);

            $title = count($result)." résultat(s) pour votre recherche";

            //Recup des infos principales
            $lastUser = $this->user->getLastUser();   
            $nbUsers = $this->user->getnbUsers();
            $categories = $this->categorie->getAllCategories();

            require_once "views/post/search.phtml";
            
        }else{
            $this->redirectTo(PAGE_ACCUEIL);
        }
    }
}








