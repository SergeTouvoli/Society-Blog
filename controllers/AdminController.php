<?php
require_once "models/Post.php";
require_once "models/User.php";
require_once "models/Category.php";
require_once "class/Tools.php";

class AdminController  {

    private $users;
    private $posts;
    private $categories;
    private $tools;

    public function __construct(){
        $this->users = new User;
        $this->posts = new Post;
        $this->categories = new Category;
        $this->tools = new Tools;
    }

    /**
     * Modifie le rôle d'un utilisateur.
     *
     * Cette fonction vérifie si l'utilisateur connecté est un administrateur.
     * Si l'utilisateur connecté est un administrateur, la fonction `changeRoleUser` du modèle `users` est appelée pour changer le rôle de l'utilisateur,
     * puis l'utilisateur est redirigé vers la page de gestion des utilisateurs avec un message de confirmation.
     * Si l'utilisateur n'est pas connecté ou n'est pas un administrateur, il est redirigé vers la page d'accueil.
     *
     * @param int $idUser L'ID de l'utilisateur dont le rôle doit être modifié.
     * @return void
    */
    public function changeRole($idUser){
  
        $idUser = intval($idUser);
        // Vérifie si l'utilisateur est connecté 
        if (!$this->users->isConnected()) {
            $this->tools->redirectTo(PAGE_ACCUEIL);
            return;
        }
        
        // Vérifie si l'utilisateur connecté est un administrateur
        if (!$this->users->isAdmin()) {
            $this->tools->redirectTo(PAGE_ACCUEIL);
            return;
        }

        $success = $this->users->changeRoleUser($idUser);
        if($success){
            $_SESSION['message'] = "Changement effectué !";
            $this->tools->redirectTo(PAGE_GESTION_UTILISATEURS);
            return;
        }
        
    }


    /**
     * 
     * getAdminGestionPostsPage
     * 
     * Affiche la page de gestion des posts pour l'administrateur.
     *
     * Si l'utilisateur actuellement connecté n'est pas un administrateur, il est redirigé vers la page d'accueil.
     * Sinon, la page de gestion des posts est chargée avec la liste de tous les posts enregistrés dans la base de données.
     *
     * @return void
    */
    public function getAdminGestionPostsPage(){
        if (!$this->users->isAdmin()) {
            $this->tools->redirectTo(PAGE_ACCUEIL);
            return;
        }

        $user_role = $this->users->getRoleOfUser($_SESSION['id']);

        $pageTitle = "Gestion des posts";
        $pageDescription = "";        
        $posts = $this->posts->getAllPost();
        require_once "views/admin/managePosts.phtml";
    }


    /**
     * 
     * getAdminGestionUsersPage
     * 
     * Affiche la page de gestion des utilisateurs pour l'administrateur.
     *
     * Si l'utilisateur actuellement connecté n'est pas un administrateur, il est redirigé vers la page d'accueil.
     * Sinon, la page de gestion des utilisateurs est chargée avec la liste de tous les utilisateurs enregistrés dans la base de données.
     *
     * @return void
    */
    public function getAdminGestionUsersPage() {
        if (!$this->users->isAdmin() == true) {
          $this->tools->redirectTo(PAGE_ACCUEIL);
          return;
        }
      
        $pageTitle = "Gestion des utilisateurs";
        $pageDescription = "";
        $users = $this->users->getAllUsers();
        require_once "views/admin/manageUsers.phtml";
    }

    /**
     * 
     * getAdminAddCategory
     * 
     * Affiche la page page d'ajout de catégorie pour l'administrateur.
     *
     * Vérifie si l'utilisateur connecté est un administrateur.
     * Traite le formulaire d'ajout de catégorie en vérifiant si la catégorie existe déjà.
     * En cas de succès, ajoute la catégorie à la base de données.
     * Affiche les erreurs ou les messages de succès.
     * @return void
    */
    public function getAdminAddCategory(){
      
        // Vérifie si l'utilisateur connecté est un administrateur
        if (!$this->users->isAdmin()) {
            $this->tools->redirectTo(PAGE_ACCUEIL);
            return;
        }

        $title = "Ajouter une categorie";
        $description = "";
        $errors = []; 
        $valids = []; 

        // Traitement de l'ajout de catégorie
        if(isset($_POST['category_name']) && !empty($_POST['category_name'])){ 
    
            $category_name = $this->tools->sanitize($_POST['category_name']);
            $category_author = intval($_SESSION['id']);
            
            if($category_name == ''){ $errors[] = "Veuillez remplir le champ 'Titre' !"; }
               
            if($this->categories->categorieExist($category_name)) {
                $errors[] = "Une catégorie porte déja ce nom !";
            }

            if(count($errors) == 0){
                if($this->categories->addCategory($category_name,$category_author,$errors,$valids)== true){
                    $valids[] = "Catégorie ajoutée avec succès";
                }
            }
         
        }
    
        require_once "views/admin/addCategory.phtml";
    }

}