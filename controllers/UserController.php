<?php

require_once "models/Post.php";
require_once "models/User.php";
require_once "class/Tools.php";

class UserController extends AbstractController  {

    private $user;
    private $post;
    private $directory;

    public function __construct(){
        $this->user = new User;
        $this->post = new Post;
        $this->directory = "user";
    }

    /**
     * getEditInfosPage
     * 
     * Affiche la page de modification des informations de l'utilisateur connect
     * Traite le formulaire de changement de mdp tout en vérifiant si le mot de passe
     * saisie par l'utilisateur est le bon
     * 
     *  @return void   
    */
    public function getEditInfosPage(){
    
        // Vérifie si l'utilisateur est connecté 
        if (!$this->user->isConnected()) {
            $this->redirectTo(PAGE_CONNEXION);
            return;
        }

        $pageTitle = "Modifier vos infos";
        $pageDescription = "";
        $errors = []; 
        $user = $this->user->getUserById(intval($_SESSION['id']));
        
        $data = [
            "actualPassword" => "",
            "newPassword" => "",
            "newPasswordConfirm" => ""
        ];

        if(isset($_POST['user_password']) && isset($_POST['newPassword']) && isset($_POST['newPasswordConfirm'])){

            $data = [
                "actualPassword" => Tools::sanitize($_POST['user_password']),
                "newPassword" => Tools::sanitize($_POST['newPassword']),
                "newPasswordConfirm" => Tools::sanitize($_POST['newPasswordConfirm'])
            ];


            if($data['actualPassword'] == ''){ $errors[] = "Veuillez saisir votre mot de passe actuel !"; }            
            if($data['newPassword'] == ''){ $errors[] = "Veuillez saisir un nouveau mot de passe !"; }            
            if($data['newPasswordConfirm'] == ''){ $errors[] = "Veuillez confirmer votre  mot de passe !"; }
            if($data['newPassword'] !== $data['newPasswordConfirm']){ $errors[] = "Mot de passe non identique !"; }
    
            if(!empty($newPassword)) {
                $numberMinimalOfCaracters = 5;
                $uppercase = preg_match('@[A-Z]@', $newPassword);
                $lowercase = preg_match('@[a-z]@', $newPassword);
                $number    = preg_match('@[0-9]@', $newPassword);
    
                if(!$uppercase){ $errors[] = "Le mot de passe doit inclure au moins une lettre majuscule !"; }
                if(!$lowercase){ $errors[] = "Le mot de passe doit inclure au moins une lettre minuscule !"; }
                if(!$number){ $errors[] = "Le mot de passe doit inclure au moins un chiffre !"; }
                if(strlen($newPassword) < $numberMinimalOfCaracters){ $errors[] = "Minimum $numberMinimalOfCaracters caractères !"; }
            
            }   
            if(count($errors) == 0){
                if(password_verify($data['actualPassword'], $user['user_password']) == true) {
                    if($this->user->updatePassword($data['newPassword'],$user['user_id'])){
                        $this->deconnect(); 
                    }else{
                        $errors[] = "Une erreur est survenue";
                    }
                }else{
                    $errors[] = "Le mot de passe saisie n'est pas le bon";
                }
            }           
        }   
    
        
        $this->renderView($this->directory, "account", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            "errors" => $errors,
            "data" => $data,
            "user" => $user
        ]);
    }
    

    /**
     * Supprime un utilisateur.
     *
     * Cette fonction vérifie si l'utilisateur connecté est l'utilisateur dont le compte doit être supprimé ou un administrateur.
     * Si l'utilisateur connecté souhaite supprimer son propre compte, la fonction `deleteUser` du modèle `user` est appelée pour supprimer le compte,
     * puis l'utilisateur est déconnecté.
     * Si l'utilisateur connecté est un administrateur, la fonction `deleteUser` du modèle `user` est appelée pour supprimer le compte concerné,
     * et l'utilisateur est redirigé vers la page de gestion des utilisateurs avec un message de confirmation.
     * Si l'utilisateur n'est pas connecté, il est redirigé vers la page d'accueil.
     *
     * @param int $idUser L'ID de l'utilisateur à supprimer.
     * @return void
    */
    public function deleteUser(int $idUser){
        $idUser = intval($idUser);

        // Vérifie si l'utilisateur est connecté
        if (!$this->user->isConnected()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

    
        // Si l'utilisateur souhaite supprimer son compte
        if ($idUser == $_SESSION['id']) {
            if ($this->user->deleteUser($idUser)) {
                $this->deconnect();
                return;
            }
        }

        // Seul un administrateur peut supprimer le compte d'un autre utilisateur
        if ($this->user->isAdmin()) {
            if ($this->user->deleteUser($idUser)) {
                $this->redirectTo(PAGE_GESTION_UTILISATEURS,'Utilisateur supprimé !');
            }
        }
    }

    /**
     * 
     * Affiche la page de modification de l'avatar de l'utilisateur connecté
     *  @return void
    */
    public function getEditAvatarPage(){
      
        // Vérifie si l'utilisateur est connecté
        if (!$this->user->isConnected()) {
            $this->redirectTo(PAGE_ACCUEIL);
            return;
        }

        $pageTitle = "Changez votre avatar";
        $pageDescription = "";
        $errors = []; 
        $idUser = intval($_SESSION['id']);
        $user = $this->user->getUserById($idUser);
        
        if(isset($_POST['changeAvatar'])){
            if(isset($_FILES['user_avatar']) &&  $_FILES['user_avatar']['name'] !== '' ){

                //On upload celle que l'utilisateur nous envoie
                $newAvatar = Tools::uploadFile($_FILES['user_avatar'], $errors, UPLOADS_DIR.'avatars/');
    
                if(count($errors) == 0){
                    //On supprime l'ancienne photo de profil
                    $this->user->deleteAvatar($user['user_avatar']);
                    
                    if($this->user->updateAvatar($newAvatar,$idUser)){
                        unset($_SESSION['avatar']);
                        $_SESSION['avatar'] = $newAvatar;
                        $this->redirectTo(PAGE_MON_COMPTE,"Avatar changé !");
                    }
                }
            }else{
                $errors[] = 'Veuillez uploader un fichier';
            }
        }

        $this->renderView($this->directory, "account", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            "errors" => $errors,
            "user" => $user
        ]); 
    }

    /**
     * Déconnecte l'utilisateur en détruisant la session et en supprimant le cookie de "se souvenir de moi"
     *
     * @return void
    */
    public function deconnect() {
        // Suppression de toutes les variables de session
        $_SESSION = array();
      
        // Efface le cookie de session.
        if(ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // On détruit la session.
        session_destroy();
      
        // Expiration du cookie de "se souvenir de moi"
        setcookie('remember_me', '', time() - 3600, '/', '', true, true); 

        // Redirection vers la page d'accueil
        $this->redirectTo(PAGE_ACCUEIL);
    }
       
    /**
     * 
     * Affiche la page de connexion et gère la connexion de l'utilisateur.
     *
     * Si l'utilisateur est déjà connecté, il est redirigé vers la page "monCompte".
     * Si le cookie "remember me" existe, il est vérifié et l'utilisateur est automatiquement connecté s'il est valide.
     * Si le formulaire de connexion est soumis, la connexion de l'utilisateur est vérifiée et, si elle est réussie, l'utilisateur est connecté et redirigé vers la page "monCompte".
     * Si la connexion échoue, des erreurs sont affichées.
     */
    public function getLoginPage(){
        
        // Titre et description de la page
        $pageTitle = "Connectez-vous";
        $pageDescription = "";

        $errors = [];  
        
        if($this->user->isConnected()) {
            $this->redirectTo(PAGE_MON_COMPTE);
            return;
        }

        $login = [
            'email' => '',
            'password' => '',
            'lastConnexion' => time()
        ];

        // Vérification de l'existence du cookie "remember me"
        $this->user->checkRememberMeCookieAndLogin($login['lastConnexion']);


        if(isset($_POST['user_mail']) && !empty($_POST['user_mail']) && isset($_POST['user_password']) && !empty($_POST['user_password'])){

            $login = [
                'email' => Tools::sanitize($_POST['user_mail']),
                'password' => Tools::sanitize($_POST['user_password']),
                'lastConnexion' => time()
            ];

            //Gestion des erreurs
            if($login['email'] == ''){ $errors[] = "Veuillez remplir le champ 'Email' !"; }
            if(!filter_var($login['email'], FILTER_VALIDATE_EMAIL)){ $errors[] = "Veuillez renseigner une adresse email valide SVP !"; }
            if($login['password'] == ''){ $errors[] = "Veuillez remplir le champ 'Mot de passe' !"; }  
    
            if(!empty($user_password)) {

                $numberMinimalOfCaracters = 5;
                $uppercase = preg_match('@[A-Z]@', $user_password);
                $lowercase = preg_match('@[a-z]@', $user_password);
                $number    = preg_match('@[0-9]@', $user_password);
    
                if(!$uppercase){ $errors[] = "Le mot de passe doit inclure au moins une lettre majuscule !"; }
                if(!$lowercase){ $errors[] = "Le mot de passe doit inclure au moins une lettre minuscule !"; }
                if(!$number){ $errors[] = "Le mot de passe doit inclure au moins un chiffre !"; }
                if(strlen($user_password) < $numberMinimalOfCaracters){ $errors[] = "Minimum $numberMinimalOfCaracters caractères !"; }
            
            }   
            
            if(count($errors) == 0) {
                $user = $this->user->getUserByEmail($login['email']);
            
                if(empty($user)) {
                    $errors[] = 'Cette adresse email ne correspond à aucun compte !';
                    
                } else {
                    if(password_verify($login['password'], $user['user_password'])){

                        // Si l'option "se souvenir de moi" est activée, génération d'un jeton aléatoire et cryptage
                        if(isset($_POST['remember_me']) && $_POST['remember_me'] == 1){
                            $this->user->setRememberMeToken($user['user_id']);
                        }
                        $this->user->updateLastConnexionAndLogin($login['lastConnexion'], $login['email'],$user);

                    }else {
                        $errors[] = "Merci de vérifier vos identifiants";
                    }
                }
            }
   
        }
        
        $this->renderView($this->directory, "login", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            "login" => $login,
            "errors" => $errors
        ]); 
    }

    /**
     * Affiche la page d'isncription et gère l'inscription de l'utilisateur.
     * 
    * getRegisterPage() est une fonction qui permet à un utilisateur de s'enregistrer sur le site en remplissant un formulaire.
    * Elle vérifie que les informations saisies sont valides et enregistre le nouvel utilisateur en base de données.
    * Si l'enregistrement est réussi, un message de confirmation est affiché à l'utilisateur.
    */
    public function getRegisterPage(){ 
        $pageTitle = "Rejoignez Society !";
        $pageDescription = "";
        $errors = []; 
    
        if($this->user->isConnected()) {
            $this->redirectTo(PAGE_MON_COMPTE);
            return;
        }

        $register = [
            'pseudo' => '',
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'password' => '',
            'password_confirm' => '',
            'avatar' => 'silhouette.png',
            'date_create' => time()
        ];
            
        if(isset($_POST['user_mail']) && !empty($_POST['user_mail'])){

            $register = [
                'pseudo' => Tools::sanitize($_POST['user_pseudo']),
                'firstname' => ucfirst(Tools::sanitize($_POST['user_firstname'])),
                'lastname' => ucfirst(Tools::sanitize($_POST['user_lastname'])),
                'email' => Tools::sanitize($_POST['user_mail']),
                'password' => Tools::sanitize($_POST['user_password']),
                'avatar' => 'silhouette.png',
                'date_create' => time()
            ];
                
            //Gestion des erreurs
            if($register['pseudo'] == ''){ $errors[] = "Veuillez remplir le champ 'Pseudo' !"; }
            if($register['firstname']  == ''){ $errors[] = "Veuillez remplir le champ 'Prénom' !"; }
            if($register['lastname']  == ''){ $errors[] = "Veuillez remplir le champ 'Nom' !"; }
            if($register['email']  == ''){ $errors[] = "Veuillez remplir le champ 'Email' !"; }
            if($register['password']  == ''){ $errors[] = "Veuillez remplir le champ 'Mot de passe' !"; }
            if(!filter_var($register['email'], FILTER_VALIDATE_EMAIL)){ $errors[] = "Veuillez renseigner une adresse email valide SVP !"; }

            if(count($errors) == 0){
                if(!empty($register['firstname']) && !empty($register['lastname'])){ 
                    $numberMaximalOfCaracters = 50;
                    if(strlen($register['firstname']) > $numberMaximalOfCaracters || strlen($register['lastname']) > $numberMaximalOfCaracters ){ 
                        $errors[] = "Nom ou prénom trop long !";
                    }
                }
    
                if(!empty($register['pseudo'])) {
                    $numberMinimalOfCaracters = 3;
                    $numberMaximalOfCaracters = 10;
                    if(strlen($register['pseudo']) < $numberMinimalOfCaracters && strlen($register['pseudo']) > $numberMaximalOfCaracters ){ 
                        $errors[] = "Votre pseudo doit contenir entre $numberMinimalOfCaracters et $numberMaximalOfCaracters caractères !";
                    }
                }   
               
                if(!empty($register['password'])){
                    $numberMinimalOfCaracters = 5;
                    $uppercase = preg_match('@[A-Z]@', $register['password']);
                    $lowercase = preg_match('@[a-z]@', $register['password']);
                    $number    = preg_match('@[0-9]@', $register['password']);
    
                    if(!$uppercase){ $errors[] = "Le mot de passe doit inclure au moins une lettre majuscule !"; }
                    if(!$lowercase){ $errors[] = "Le mot de passe doit inclure au moins une lettre minuscule !"; }
                    if(!$number){ $errors[] = "Le mot de passe doit inclure au moins un chiffre !"; }
                    if(strlen($register['password']) < $numberMinimalOfCaracters){ $errors[] = "Minimum $numberMinimalOfCaracters caractères !"; }
                }         
                
                if($this->user->emailExist($register['email'])) {
                    $errors[] = "Cette adresse e-mail existe déjà !";
                }
    
                if($this->user->pseudoExist($register['pseudo'])) {
                    $errors[] = "Ce pseudo existe déjà !";
                }
    
            }
     
            if (count($errors) == 0) {
                if (isset($_FILES["user_avatar"]) && $_FILES["user_avatar"]["name"] !== "") {
                    $register['avatar'] = Tools::uploadFile($_FILES["user_avatar"], $errors, UPLOADS_DIR . "avatars/");
                }
        
                if (count($errors) == 0) {
                    if ($this->user->saveUser($register)) {
                        $this->redirectTo(PAGE_CONNEXION,"Inscription réussie, vous pouvez vous connecter!");
                        return;
                    }
                }
            
            }
        }

        $this->renderView($this->directory, "register", [
            "pageTitle" => $pageTitle,
            "pageDescription" => $pageDescription,
            'errors' => $errors,
            'register' => $register
        ]); 
    }

    /**
     * getAccountPage
     * 
     * Affiche la page de profil de l'utilisateur connecté.
     * 
     * Si l'utilisateur n'est pas connecté, redirige vers la page de connexion.
     * 
     * @return void
    */
    public function getAccountPage(){
        $pageTitle = "Mon profil";
        $pageDescription = "";

        // Vérifie si l'utilisateur est connecté et si le slug du post est valide
        if (!$this->user->isConnected()) {
            $this->redirectTo(PAGE_CONNEXION);
            return;
        }
        $idUser = intval($_SESSION['id']);
        $user = $this->user->getUserById($idUser);
       
        $this->renderView($this->directory, "account", [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'user' => $user
        ]); 
    }
    
    /**
     * getAccountUser
     * 
     * Affiche la page de profil d'un utilisateur.
     *
     * @param string $firstname Le prénom de l'utilisateur dont on veut afficher le profil.
     * @return void
     */
    public function getAccountUser(string $pseudo): void{
        $pseudo = Tools::sanitize($pseudo);

        $pageTitle = "Compte de $pseudo";
        $pageDescription = "";

        if ($this->user->isConnected() && isset($_SESSION['pseudo']) && $pseudo == $_SESSION['pseudo']) {
            $this->redirectTo(PAGE_MON_COMPTE);
            return;
        }

        $user = $this->user->getUserByPseudo($pseudo);
        $idUser = $user['user_id'];

        $postOfUser = $this->post->getInfosPostsOfUser($idUser);

        $this->renderView($this->directory, "userAccount", [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'postOfUser' => $postOfUser,
            'nbArticles' => count($postOfUser),
            'user' => $user
        ]); 
    }

    /**
     * getPostsOfUser
     * 
     * Affiche la page des posts de l'utilisateur connecté.
     *
     * Si l'utilisateur n'est pas connecté, redirige vers la page de connexion.
     *
     * @return void
    */
    public function getPostsOfUser(){
        $pageTitle = "Mes posts";
        $pageDescription = "";


        if (!$this->user->isConnected()) {
            $this->redirectTo(PAGE_CONNEXION);
        }
        
        $idUser = intval($_SESSION['id']);        

        // Récupère les informations sur les posts de l'utilisateur connecté
        $posts = $this->post->getInfosPostsOfUser($idUser);
        
        $this->renderView($this->directory, "myPosts", [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'posts' => $posts
        ]); 
    }



}









