<?php

require_once "config/config.php";
require_once "controllers/AbstractController.php";
require_once "controllers/UserController.php";
require_once "controllers/PostController.php";
require_once "controllers/CommentController.php";
require_once "controllers/AdminController.php";
require_once "controllers/PageController.php";

class Router {
    
    private $userController;
    private $postController;
    private $adminController;
    private $pageController;
    private $commentController;

    public function __construct()
    {
        $this->userController = new UserController();
        $this->postController = new PostController();
        $this->adminController = new AdminController();
        $this->commentController = new CommentController();
        $this->pageController = new PageController();
    }

    public function run(){
        try {
            if (isset($_GET['page']) && !empty($_GET['page'])) {
                // Explose l'URL en un tableau à partir du caractère '/'
                $url = explode("/", filter_input(INPUT_GET, 'page', FILTER_SANITIZE_URL));

                // Récupère la première partie de l'URL (la page demandée)
                $page = $url[0];

                switch ($page) {
                    case PAGE_ACCUEIL:
                        $this->pageController->getHomePage(isset($url[1]) ? intval($url[1]) : 1);
                        break;
                    case PAGE_CONNEXION:
                        $this->userController->getLoginPage();
                        break;
                    case PAGE_INSCRIPTION:
                        $this->userController->getRegisterPage();
                        break;
                    case PAGE_DECONNEXION:
                        $this->userController->deconnect();
                        break;
                    case PAGE_CONTACT:
                        $this->pageController->getContactPage();
                        break;
                    case PAGE_RECHERCHE:
                        $this->postController->getSearchPage();
                        break;
                    case PAGE_GESTION_CATEGORIES:
                        $this->adminController->getAdminGestionCategoriesPage();
                        break;
                    case PAGE_POST:
                        if (isset($url[1])) {
                            $this->postController->getPostPage($url[1]);
                        } else {
                            $this->pageController->getHomePage(1);
                        }
                        break;
                    case PAGE_CATEGORIES:
                        if (!isset($url[1])) {
                            $this->pageController->getHomePage(1);
                        } else {
                            //Si on a $url[1] (le nom de la catégorie) et $url[2] (le numero de page) on affiche la page coresspondante sinon on affiche la première page
                            (isset($url[2])) ? $this->pageController->getCategoriesPage($url[1],$url[2]) : $this->pageController->getCategoriesPage($url[1],1);
                        }
                        break;
                    case PAGE_GESTION_POSTS:
                        $this->adminController->getAdminGestionPostsPage();
                        break;
                    case PAGE_SEARCH_POST:
                        $this->postController->liveSearchPost();
                        break;
                    case PAGE_GESTION_UTILISATEURS:
                        $this->adminController->getAdminGestionUsersPage();
                        break;
                    case PAGE_MON_COMPTE:
                        $this->userController->getAccountPage();
                        break;
                    case PAGE_COMPTE:
                        if (!isset($url[1])) {
                            $this->pageController->getHomePage(1);
                        } else {
                            $this->userController->getAccountUser($url[1]);                        
                        }
                        break;
                    case PAGE_EDIT_COMPTE:
                        $this->userController->getEditInfosPage();
                        break;
                    case PAGE_EDIT_AVATAR:
                        $this->userController->getEditAvatarPage();
                        break;
                    case PAGE_MES_POSTS:
                        $this->userController->getPostsOfUser();
                        break;
                    case PAGE_AJOUT_POST:
                        $this->postController->getAddPostPage();
                        break;
                    case PAGE_AJOUT_CATEGORIE:
                        $this->adminController->getAdminAddCategory();
                        break;
                    case DELETE_POST:
                        if (!isset($url[1])) {
                            $this->pageController->getHomePage(1);
                        } else {
                            $this->postController->deletePost($url[1]);                      
                        }
                        break;
                    case DELETE_USER:
                        if (!isset($url[1])) {
                            $this->pageController->getHomePage(1);
                        } else {
                            $this->userController->deleteUser($url[1]);                      
                        }
                        break;
                    case DELETE_CATEGORIE:
                        if (!isset($url[1])) {
                            $this->pageController->getHomePage(1);
                        } else {
                            $this->adminController->deleteCategory($url[1]);                      
                        }
                        break;
                    case CHANGE_ROLE:
                        if (isset($url[1])) {
                            $this->adminController->changeRole($url[1]);
                        } else {
                            $this->pageController->getHomePage(1);                       
                        }
                        break;
                    case PAGE_EDIT_POST:
                        if(isset($url[1])){ 
                            $this->postController->getEditPostPage($url[1]);
                        }else{
                            $this->pageController->getHomePage(1);
                        }
                        break;
                    case ADD_COMMENT :
                        $this->commentController->addComment();
                        break;
                    case LOAD_COMMENT :
                        $this->commentController->getAllComments();
                        break;
                    case DELETE_COMMENT :
                        if(isset($url[1]) && isset($url[2])){ 
                            $this->commentController->deleteComment($url[1],$url[2]);
                        }
                        break;
                    case ERROR_301: 
                    case ERROR_302: 
                    case ERROR_400: 
                    case ERROR_401: 
                    case ERROR_402: 
                    case ERROR_405: 
                    case ERROR_500: 
                    case ERROR_505: throw new Exception("Error de type : "+$page);
                    break;
                    case ERROR_403: throw new Exception("vous n'avez pas le droit d'accéder à ce dossier");
                    break;
                    case ERROR_404:
                    default: throw new Exception("La page n'existe pas");
                }
            } else {
                $this->pageController->getHomePage(1);
            }
        } catch (Exception $e) {
            $pageTitle = "Error";
            $pageDescription = "Page de gestion des erreurs";
            $errorMessage = $e->getMessage();
            require "views/commons/errorPage.phtml";
        }
    }
}