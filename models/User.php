<?php

require_once "class/DatabaseTools.php";

class User extends DatabaseTools {

    private $dbTools;
    private $db;
    
    public function __construct(){
        $this->dbTools  = new DatabaseTools;
        $this->db = $this->dbTools->connexion;
    }
    
    public function getnbUsers(){ 
        $nbUsers  = $this->dbTools->countEntry("users"); 
        return $nbUsers['COUNT(*)'];
    }

    public function getLastUser(){ 
        $sql ="SELECT * FROM users ORDER BY user_id DESC LIMIT 1";
        $lastUser = $this->dbTools->dbSelectOne($sql);
        return $lastUser ;
    }

    public function getAllUsers(){
        $sql ="SELECT * FROM users";
        $allUsers = $this->dbTools->dbSelectAll($sql);
        return $allUsers;
    }

    public function deleteUser(int $idUser){
        
        $sql = "DELETE FROM users WHERE user_id = :user_id";
        $params = array("user_id" =>  $idUser);
        $execution = $this->dbTools->deleteInBdd($sql,$params);

        return $execution;
    }

    public function getUserById(int $idUser){
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $params = array("user_id" =>  $idUser);
        $user = $this->dbTools->dbSelectOne($sql,$params);

        return $user;
    }

    public function getUserByEmail($user_mail){
        $sql = "SELECT * FROM users WHERE user_mail = :mail"; 
        $params = array("mail" =>  $user_mail);
        $result = $this->dbTools->dbSelectOne($sql,$params);
        
        return $result;
    }

    public function getUserByToken($token){
        $sql = "SELECT * FROM users WHERE remember_me_token = :token"; 
        $params = array("token" =>  $token);

        $result = $this->dbTools->dbSelectOne($sql,$params);

        return $result;
    }

    public function getUserByFirstName($firstName){
        $sql ="SELECT * FROM users WHERE user_firstname = :firstname"; 
        $params = array("firstname" =>  $firstName);

        $result =  $this->dbTools->dbSelectOne($sql,$params);
        
        return $result;
    }

    public function getUserByPseudo(string $pseudo){
        $sql = "SELECT * FROM users WHERE user_pseudo = :pseudo";
        $sth = $this->db->prepare($sql); 
        $sth->bindValue('pseudo', $pseudo, PDO::PARAM_STR);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        
        return $result;
    }

    public function getEmailById(int $idUser) {
        $sql = "SELECT user_mail FROM users WHERE user_id = :user_id";
        $params = array("user_id" =>  $idUser);
        $result = $this->dbTools->dbSelectOne($sql,$params);
        
        return $result['user_mail'];
    }

    public function updateRememberMeToken($token,$idUser){
        $sql = "UPDATE users SET remember_me_token = :token WHERE user_id = :idUser";
        $sth = $this->db->prepare($sql); 
        $sth->bindValue('token', $token, PDO::PARAM_STR);
        $sth->bindValue('idUser', $idUser, PDO::PARAM_STR);
        return $sth->execute();
    }

    public function updateAvatar($newAvatar,int $idUser){
        $sql = "UPDATE users SET user_avatar = :newAvatar WHERE user_id = :idUser";
        $params = array("newAvatar" =>  $newAvatar, "idUser" => $idUser);
        $execution = $this->dbTools->updateInBdd($sql,$params);

        return $execution;
    }

    /**
     * updateLastConnexionAndLogin()
     * 
     * Connecte l'utilisateur et met à jour la date de sa dernière connexion
     * @param int $user_last_connexion La date de la dernière connexion de l'utilisateur
     * @param string $user_mail L'adresse mail de l'utilisateur
     * @param array $user Les informations de l'utilisateur
     * 
     * @return void
     * 
    */
    public function updateLastConnexionAndLogin($user_last_connexion,$user_mail,$infosUser){

        $sql = "UPDATE users SET user_last_connexion = :user_last_connexion WHERE user_mail = :mail";
        $sth = $this->db->prepare($sql); 
        $sth->bindValue('user_last_connexion', $user_last_connexion, PDO::PARAM_INT);
        $sth->bindValue('mail', $user_mail, PDO::PARAM_STR);
        if($sth->execute()){
            header('Location: '.PAGE_MON_COMPTE);
            $_SESSION = [
                'firstName' => $infosUser['user_firstname'],
                'lastName'  => $infosUser['user_lastname'],
                'email'     => $infosUser['user_mail'],
                'avatar'     => $infosUser['user_avatar'],
                'pseudo'   => $infosUser['user_pseudo'],
                'role'   => $infosUser['user_role'],
                'id'        => $infosUser['user_id']
            ];  
        }
   
    }

    /**
     * checkRememberMeCookieAndLogin()
     * 
     * Vérifie le cookie "remember me" et connecte l'utilisateur si le jeton est valide
     * @param int $lastConnexion La date (timestamp) de la dernière connexion de l'utilisateur
     * @return void
     * 
    */
    public function checkRememberMeCookieAndLogin(int $lastConnexion): void{
        if(isset($_COOKIE['remember_me'])) {
            // Extraction du jeton et du hash du cookie
            list($token, $hash) = explode(':', $_COOKIE['remember_me']);
            
            // Vérification du hash du jeton
            if (password_verify($token, $hash)) {
                // Le jeton est valide, récupération de l'utilisateur dans la base de données
                $user = $this->getUserByToken($token);

                // Si l'utilisateur est trouvé, authentification
                if($user){
                    $this->updateLastConnexionAndLogin($lastConnexion, $user['user_mail'], $user);
                    return;
                }            
            }
            // Le hash du jeton est incorrect, suppression du cookie
            setcookie('remember_me', '', time() - 3600);
        }

    }


    function insertMsg(array $data) {
        $sql = "INSERT INTO contacts (contact_email_author, contact_subject, contact_content, contact_date) 
                            VALUES (:contact_author, :contact_subject, :contact_content, :contact_date)";
                    
        $sth = $this->db->prepare($sql); 
                    
        $sth->bindValue(':contact_author', $data['author'], PDO::PARAM_STR);
        $sth->bindValue(':contact_subject', $data['subject'], PDO::PARAM_STR);
        $sth->bindValue(':contact_content', $data['content'], PDO::PARAM_STR);
        $sth->bindValue(':contact_date', $data['date'], PDO::PARAM_INT);
        
        return $sth->execute();
    }

    /**
     * setRememberMeToken()
     * 
     * Génère et enregistre un jeton "remember me" pour l'utilisateur
     * @param int $user_id L'ID de l'utilisateur
     * @return void
     * 
    */
    public function setRememberMeToken(int $user_id): void{
        // Génération d'un jeton aléatoire et cryptage
        $token = bin2hex(random_bytes(32));
        $hash = password_hash($token, PASSWORD_DEFAULT);
        $saveToken = $this->updateRememberMeToken($token, $user_id);

        if($saveToken){
            // Enregistrement du cookie "remember me"
            setcookie('remember_me', $token . ':' . $hash, time() + (60 * 60 * 24 * 30), '/', '', true, true);// expire dans 30 jours
        }
    }


    public function saveUser(array $register){

       
        $sql = "INSERT INTO users (user_pseudo,user_firstname,user_lastname,user_mail, user_avatar, user_password,user_date_create) 
                VALUES (:user_pseudo,:user_firstname, :user_lastname, :user_mail,:user_avatar, :user_password,:user_date_create)";
        $sth = $this->db->prepare($sql); 
        
        $sth->bindValue('user_pseudo', $register['pseudo'], PDO::PARAM_STR);
        $sth->bindValue('user_firstname', $register['firstname'], PDO::PARAM_STR);
        $sth->bindValue('user_lastname', $register['lastname'], PDO::PARAM_STR);
        $sth->bindValue('user_mail', $register['email'], PDO::PARAM_STR);
        $sth->bindValue('user_avatar', $register['avatar'], PDO::PARAM_STR);
        $password = password_hash($register['password'], PASSWORD_DEFAULT);
        $sth->bindValue('user_password', $password, PDO::PARAM_STR);    
        $sth->bindValue('user_date_create', $register['date_create'], PDO::PARAM_INT);    
        $execution = $sth->execute();
                                
        return $execution;
        
    }

    /**
     * Modifie le mot de passe de l'utilisateur
     * @param string $newPassword Le nouveau mot de passe
     * @param int $idUser L'ID de l'utilisateur
     * @return bool TRUE si la mise à jour a réussi, FALSE sinon
    */
    public function updatePassword(string $newPassword, int $idUser) : bool {
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET user_password = :newPasswordHash WHERE user_id = :idUser";

        $sth = $this->db->prepare($sql);
        $sth->bindValue('newPasswordHash', $newPasswordHash, PDO::PARAM_STR);
        $sth->bindValue('idUser', $idUser, PDO::PARAM_INT);

        return $sth->execute();
    }

    /**
     * Récupère le rôle de l'utilisateur avec l'ID spécifié.
     *
     * @param int $idUser L'ID de l'utilisateur à chercher.
     *
     * @return string Le rôle de l'utilisateur.
     */
    public function getRoleOfUser(int $idUser): int{
        $sql = "SELECT user_role FROM users WHERE user_id = :user_id";
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':user_id', $idUser, PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result['user_role'];
    }

    /**
     * Change le rôle de l'utilisateur avec l'ID spécifié.
     * Si l'utilisateur est actuellement administrateur, son rôle sera mis à jour pour être un utilisateur normal.
     * Si l'utilisateur est actuellement un utilisateur normal, son rôle sera mis à jour pour être un administrateur.
     *
     * @param int $idUser L'ID de l'utilisateur dont le rôle doit être changé.
     *
     * @return bool True si le rôle de l'utilisateur a été mis à jour avec succès, false sinon.
     */
    public function changeRoleUser(int $idUser): bool{
        $userRole = $this->getRoleOfUser($idUser);

        $userNewRole = $userRole == 1 ? '0' : '1';

        $sql = "UPDATE users SET user_role = :user_new_role WHERE user_id = :user_id";
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':user_id', $idUser, PDO::PARAM_INT);
        $sth->bindValue(':user_new_role', $userNewRole, PDO::PARAM_INT);

        return $sth->execute();
     
    }

    /**
     * Supprime un avatar à partir de son nom de fichier
     *
     * @param string $filename le nom du fichier de l'avatar à supprimer
     *
     * @return void
    */
    public function deleteAvatar(string $filename) {
        if(isset($filename) && !empty($filename)) {
            unlink("public/images/avatars/".$filename);
        }
    }

    /**
     * Vérifie si un email existe dans la table `users` de la base de données.
     *
     * @param string $email l'email à vérifier
     * @return bool `true` si l'addresse email existe, `false` sinon
     */
    public function emailExist(string $email){
        $sql = "SELECT COUNT(*) FROM users WHERE user_mail = :email";
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':email', $email, PDO::PARAM_STR);
        $sth->execute();
        $count = (int) $sth->fetchColumn();

        return $count > 0;
    }


    /**
     * Vérifie si un pseudo existe dans la table `users` de la base de données.
     *
     * @param string $pseudo Le pseudo à vérifier
     * @return bool `true` si le pseudo existe, `false` sinon
     */
    public function pseudoExist(string $pseudo): bool{
        $sql = "SELECT COUNT(*) FROM users WHERE user_pseudo = :pseudo";
        $sth = $this->db->prepare($sql);
        $sth->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
        $sth->execute();
        $count = (int) $sth->fetchColumn();

        return $count > 0;
    }

 
    /**
    * Vérifie si un utilisateur est connecté.
    *
    * @return bool Vrai si un utilisateur est connecté, faux sinon.
    */
    public function isConnected() {
        return isset($_SESSION['id']);
    }
    
    /**
    * Vérifie l'utilisateur connecté est un administrateur.
    * @return bool
    */
    public function isAdmin() {
        return $this->isConnected() && $_SESSION['role'] == 1;
    }

    /**
     * Récupère l'ID de l'utilisateur connecté.
     * @return int L'ID de l'utilisateur connecté.
    */
    public function getId(){
        if($this->isConnected()){
            return $_SESSION['id'];
        }
    }
      







    
}

