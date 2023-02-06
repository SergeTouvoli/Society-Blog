<?php
require_once "models/Post.php";
require_once "models/User.php";
require_once "models/Category.php";

class PageController extends AbstractController {
    
    private $users;
    private $posts;
    private $categories;
    private $directory;

    public function __construct(){
        $this->users = new User;
        $this->posts = new Post;
        $this->categories = new Category;
        $this->directory = "page";
    }

    /**
     * getHomePage
     * 
     * Affiche la page d'accueil du site.
     *
     * @param int $currentPage Numéro de la page courante (utilisé pour la pagination).
     * Valeur par défaut : 1.                         
    */
    public function getHomePage($currentPage = 1): void{
        $pageTitle = "Accueil";
        $pageDescription = "Page d'accueil";

        // On s'assure que la page courante est au moins égale à 1
        $currentPage = max(1, $currentPage);

        $nbPosts = $this->posts->getnbPosts();
        $parPage = 3;

        // On calcule le nombre de pages total
        $pagesTotal = ceil($nbPosts / $parPage);

        // Calcul de l'offset en fonction de la page courante et du nombre d'articles par page
        $premier = ($currentPage - 1) * $parPage;
        $posts = $this->posts->getPosts($premier,$parPage);

        $this->renderView($this->directory,"home", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            "posts" => $posts,
            "lastUser" => $this->users->getLastUser(),
            "nbUsers" => $this->users->getnbUsers(),
            "categories" => $this->categories->getAllCategories(),
            "recentsPosts" => $this->posts->getRecentsPosts(),
            "pageForPagination" => "accueil",
            "currentPage" => $currentPage,
            "pagesTotal" => $pagesTotal
        ]);

    }

    /**
     * getPostPage
     * 
     * Affichage de la page de contact. Si les variables POST 'contact_subject' et 'contact_content' sont définies, le message de contact est envoyé à la base de données.
     * Les erreurs sont vérifiées pour les champs 'subject', 'content' et 'author' : la longueur minimale et maximale, le format.
     * Si aucune erreur n'est détectée, le message est enregistré en base de données.
     * Une notification de réception de message est alors affichée.
     *
     * @return void
    */
    public function getContactPage(): void{
        $pageTitle = "Contactez-nous";
        $pageDescription = "Page de contact";
        $errors = [];  
        
        $data = [
            "author" => "",
            "subject" => "",
            "content" => "",
            "date" => ""
        ];

        // Si les variables POST 'contact_subject' et 'contact_content' sont définies
        if(isset($_POST['contact_content']) && isset($_POST['contact_subject'])) {

            $data = [
                "author" => $this->users->getEmailById($_SESSION['id']),
                "subject" => Tools::sanitize($_POST['contact_subject']),
                "content" => Tools::sanitize($_POST['contact_content']),
                "date" => time()
            ];


            if($data['author'] == ''){ $errors[] = "Veuillez saisir votre adresse email !"; }
            if($data['subject'] == ''){ $errors[] = "Veuillez saisir le sujet de votre message !"; }
            if($data['content'] == ''){ $errors[] = "Veuillez saisir le contenue de votre message !"; }

            if(!empty($data['content'])){
                $numberMinimalOfCaracters = 10;
                $numberMaximalOfCaracters = 20000;
                if(strlen($data['content']) < $numberMinimalOfCaracters ) { $errors[] = "Votre message doit contenir Minimum $numberMinimalOfCaracters caractères"; }
                if(strlen($data['content']) > $numberMaximalOfCaracters ) { $errors[] = "Votre message doit contenir Maximum $numberMaximalOfCaracters caractères" ; }
            }

            if(!empty($data['subject'])){
                $numberMinimalOfCaracters = 3;
                $numberMaximalOfCaracters = 45;
                if(strlen($data['subject']) < $numberMinimalOfCaracters ) { $errors[] = "Votre sujet doit contenir Minimum $numberMinimalOfCaracters caractères"; }
                if(strlen($data['subject']) > $numberMaximalOfCaracters ) { $errors[] = "Votre sujet doit contenir Maximum $numberMaximalOfCaracters caractères" ; }
            }

            // S'il n'y a pas d'erreurs
            if(count($errors) == 0) {

                $insertMsg = $this->users->insertMsg($data);  
                
                if($insertMsg) {
                    $_SESSION['message'] = "Nous avons bien reçu votre message. Nous vous répondrons dans les plus brefs délais.";
                    $this->redirectTo(PAGE_ACCUEIL);
                }
            }

        }

        $this->renderView($this->directory,"contact", [
                "pageTitle" => $pageTitle,
                "pageDescription" => $pageDescription,
                "errors" => $errors,
                "data" => $data
            ]
        );
    }
    
    /**
     * getCategoriesPage
     * 
     * Affiche la page des articles de la catégorie $categoryName.
     * 
     * @param string $categoryName Nom de la catégorie dont on veut afficher les articles.
     * @param int $currentPage Numéro de la page courante (utilisé pour la pagination).
     * Valeur par défaut : 1.
    */
    public function getCategoriesPage(string $categoryName,$currentPage){
        $pageTitle = "Catégorie";
        $category = Tools::sanitize($categoryName);
        $pageDescription = "Articles de la catégorie $category";

        // On s'assure que la page courante est au moins égale à 1
        $currentPage = max(1, $currentPage);

        $nbPostsOfCat = $this->posts->getnbPostsOfCatByName($category);
        $parPage = 3;
        
        // On calcule le nombre de pages total
        $pagesTotal = ceil($nbPostsOfCat / $parPage);

        // Calcul de l'offset en fonction de la page courante et du nombre d'articles par page
        $premier = ($currentPage - 1) * $parPage;      
        $postByCat = $this->posts->getPostsByCat($premier,$parPage,$category);
                
        $this->renderView($this->directory,"category", [
                "pageTitle" => $pageTitle,
                "pageDescription" => $pageDescription,
                "postByCat" => $postByCat,
                "lastUser" => $this->users->getLastUser(),
                "nbUsers" => $this->users->getnbUsers(),
                "categories" => $this->categories->getAllCategories(),
                "recentsPosts" => $this->posts->getRecentsPosts(),
                "pageForPagination" => "categories/".$category."",
                "currentPage" => $currentPage,
                "pagesTotal" => $pagesTotal
            ]
        );
 
    }
}