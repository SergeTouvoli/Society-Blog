<?php
require_once "models/Post.php";
require_once "models/User.php";
require_once "models/Category.php";
require_once "models/Contact.php";
require_once "class/Tools.php";

class AdminController extends AbstractController  {

    private $users;
    private $posts;
    private $contact;
    private $categories;
    private $tools;
    private $directory;


    public function __construct(){
        $this->users = new User;
        $this->contact = new Contact;
        $this->posts = new Post;
        $this->categories = new Category;
        $this->tools = new Tools;
        $this->directory = "admin";
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
    public function changeRole(int $idUser): void{
  
        // Vérifie si l'utilisateur est connecté 
        if (!$this->users->isConnected()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }
        
        // Vérifie si l'utilisateur connecté est un administrateur
        if (!$this->users->isAdmin()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        $success = $this->users->changeRoleUser($idUser);
        if($success){
            $_SESSION['message'] = "Changement effectué !";
            $this->redirectTo(PAGE_GESTION_UTILISATEURS);
            return;
        }
        
    }


    /**
     * Affiche la page de gestion des posts pour l'administrateur.
     *
     * Si l'utilisateur actuellement connecté n'est pas un administrateur, il est redirigé vers la page d'accueil.
     * Sinon, la page de gestion des posts est chargée avec la liste de tous les posts enregistrés dans la base de données.
     *
     * @return void
    */
    public function getAdminGestionPostsPage(): void{

        if (!$this->users->isAdmin()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        $pageTitle = "Gestion des posts";
        $pageDescription = "";   

        $this->renderView($this->directory, "managePosts", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            "posts" => $this->posts->getAllPost(),
        ]); 
    }

    /**
     * Affiche la page de gestion des messages pour les administrateurs.
     * Redirige l'utilisateur non administrateur vers la page d'accueil.
     * @return void
     */
    public function getAdminGestionMessae(): void {
            
        if (!$this->users->isAdmin()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        $pageTitle = "Gestion des messages";
        $pageDescription = "";   


        $this->renderView($this->directory, "manageMessages", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            "messages" => $this->contact->getAllMessages(),
        ]); 
    }

    /**
     * Supprime un message spécifié par son identifiant.
     * Redirige l'utilisateur non administrateur vers la page d'accueil.
     * @param int $idContact L'identifiant du message à supprimer.
     * @return void
     */
    public function deleteMessage(int $idContact){
        if (!$this->users->isAdmin()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        $success = $this->contact->deleteMessage($idContact);
        if($success){
            $this->redirectTo(PAGE_GESTION_MESSAGES,"Message supprimé !");
            return;
        }
    }

    /**
     * Affiche un message spécifié par son identifiant.
     * Redirige l'utilisateur non administrateur vers la page d'accueil.
     * @param int $idContact L'identifiant du message à afficher.
     * @return void
     */
    public function viewMessage(int $idContact){
        if (!$this->users->isAdmin()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        $message = $this->contact->getMessage($idContact);
        $pageDescription = "";   
        $message = [
            "id" => intval($message['contact_id']),
            "author" => $message['author'],
            "subject" => $message['contact_subject'],
            "content" => $message['contact_content'],
            "date" => Tools::convertTimestampToFrenchDate($message['contact_date']),
        ];
        $pageTitle = "Recu le ".$message['date']." de ".$message['author']."";
   
        $this->renderView($this->directory, "viewMessage", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            "message" => $message,
        ]); 

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
    public function getAdminGestionUsersPage(): void{

        if (!$this->users->isAdmin()) {
          $this->redirectTo(PAGE_ACCUEIL);
          return;
        }
      
        $pageTitle = "Gestion des utilisateurs";
        $pageDescription = "";
                
        $this->renderView($this->directory, "manageUsers", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            "users" => $this->users->getAllUsers()
        ]); 
    }

    /**
     * Affiche la page de gestion des catégories pour l'administrateur.
     *
     * Si l'utilisateur actuellement connecté n'est pas un administrateur, il est redirigé vers la page d'accueil.
     * Sinon, la page de gestion des catégories est chargée avec la liste de tous les catégories enregistrés dans la base de données.
     *
     * @return void
    */
    public function getAdminGestionCategoriesPage(): void{

        if (!$this->users->isAdmin()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }
        
        $pageTitle = "Gestion des catégories";
        $pageDescription = "";
        
        $this->renderView($this->directory, "manageCategories", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            "categories" => $this->categories->getCategoriesWithAuthorAndPostCount(),
        ]); 
    }

    /** 
     * Affiche la page page d'ajout de catégorie pour l'administrateur.
     *
     * Vérifie si l'utilisateur connecté est un administrateur.
     * Traite le formulaire d'ajout de catégorie en vérifiant si la catégorie existe déjà.
     * En cas de succès, ajoute la catégorie à la base de données et redirige l'utilisateur vers la page de gestion des catégories avec un message de confirmation.
     * Affiche les erreurs ou les messages de succès.
     * @return void
    */
    public function getAdminAddCategory(): void{
      
        if (!$this->users->isAdmin()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        $pageTitle = "Ajouter une categorie";
        $pageDescription = "";
        $errors = []; 

        // Traitement de l'ajout de catégorie
        if(isset($_POST['category_name']) && !empty($_POST['category_name'])){ 
    
            $category_name = $this->tools->sanitize($_POST['category_name']);
            $category_author = intval($_SESSION['id']);
            
            if($category_name == ''){ $errors[] = "Veuillez remplir le champ 'Titre' !"; }
               
            if($this->categories->categorieExist($category_name)) {
                $errors[] = "Une catégorie porte déja ce nom !";
            }

            if(count($errors) == 0){
                if($this->categories->addCategory($category_name,$category_author)){
                    $this->redirectTo(PAGE_GESTION_CATEGORIES,"Catégorie ajoutée avec succès");
                    return;
                }
            }
         
        }

        $this->renderView($this->directory, "addCategory", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            "errors" => $errors,
            "categories" => $this->categories->getCategoriesWithAuthorAndPostCount(),
        ]); 
    }
    
    /**
     * Supprime une catégorie en fonction de son identifiant.
     *
     * Cette fonction vérifie d'abord si l'utilisateur est un administrateur. Si ce n'est pas le cas, l'utilisateur est redirigé vers la page d'accueil. 
     * Sinon, elle vérifie s'il existe des posts associés à la catégorie. Si tel est le cas, un message d'erreur est affiché et l'utilisateur est redirigé vers la page de gestion des catégories. 
     * Sinon, la catégorie est supprimée et l'utilisateur est redirigé vers la page de gestion des catégories avec un message de succès.
     *
     * @param int $idCategory L'identifiant de la catégorie à supprimer.
     * @return void
     */
    public function deleteCategory(int $idCategory): void {

        if (!$this->users->isAdmin()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        // Vérifie s'il existe des posts associés à la catégorie
        $nbPosts = $this->categories->getNbPostsOfCategory($idCategory);
        if($nbPosts > 0){
            $message = "Impossible de supprimer cette catégorie car elle contient des posts !";
            $this->redirectTo(PAGE_GESTION_CATEGORIES,$message,'error');
            return;
        } 

        // Supprime la catégorie et redirige vers la page de gestion des catégories avec un message de succès
        $this->categories->deleteCategory($idCategory);
        $this->redirectTo(PAGE_GESTION_CATEGORIES,'Suppression effectuée !');
    }
}