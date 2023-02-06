<?php

class Tools  {

    /**
     * Affiche une variable de manière lisible et arrête l'exécution du script
     *
     * @param mixed $data Les données à afficher
     *
     * @return void
     */
    public static function debug(mixed $data) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit;
    }

    /**
     * Concatène le prénom et le nom de l'utilisateur et échappe les caractères spéciaux HTML.
     *
     * @param string $prenom 
     * @param string $nom 
     * 
     * @return string
     */
    public static function concatPrenomNom(string $prenom, string $nom): string{
        return htmlspecialchars("$prenom $nom");
    }


    /**
     * Vérifie si la page spécifiée est active(la page sur laquelle on se trouve)
     *
     * @param string $nomPage Le nom de la page à vérifier
     *
     * @return string La classe "active" si la page est active, sinon une chaîne vide
    */
    public static function verifActivePage(string $nomPage) {
        $classe = '';
        // Vérifie si la variable $_GET['page'] existe et si elle est égale au nom de la page spécifiée
        if(isset($_GET['page']) && $_GET['page'] === $nomPage){
            $classe = 'active';
        }
            
        return $classe;
    }

    /**
     * Convertit un timestamp en date au format français spécifié.
     *
     * @param int $timestamp Le timestamp à convertir.
     * @param string $format Le format de la date à renvoyer. Peut prendre les valeurs 'full' (par défaut), 'year', 'month' ou 'day'.
     *
     * @return string La date au format français spécifié.
    */
    public static function convertTimestampToFrenchDate($timestamp, $format = 'full') {

        $date = getdate($timestamp);
        $hour = str_pad($date['hours'], 2, '0', STR_PAD_LEFT);
        $minute = str_pad($date['minutes'], 2, '0', STR_PAD_LEFT);
      
        $frenchMonths = array(
          "janvier", "février", "mars", "avril", "mai", "juin",
          "juillet", "août", "septembre", "octobre", "novembre", "décembre"
        );
      
        $frenchWeekdays = array(
          "dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"
        );
      
        // Détermine le format de la date en fonction de la valeur du paramètre $format
        switch ($format) {
            case 'year':
                // Retourne seulement l'année
                return $date['year'];
            case 'month':
                // Retourne seulement le mois
                return $frenchMonths[$date['mon']-1];
            case 'day':
                // Retourne seulement le jour
                return $date['mday'];
            case 'full':
            default:
                // Retourne la date au format "jour mois année heure:minute" en français
                return $frenchWeekdays[$date['wday']] . " " . $date['mday'] . " " . $frenchMonths[$date['mon']-1] . " " . $date['year'] . " à " . $hour . ":" . $minute;
        }
    }

    /**
     * Cette fonction vérifie si le type MIME d'un fichier est autorisé
     *
     * @param string $file Le chemin vers le fichier à vérifier
     *
     * @return bool true si le type MIME est autorisé, false sinon
    */
    public static function checkMIME($file) {
        $allowedTypes = array("image/jpeg", "image/png", "image/gif","image/svg+xml");
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file);
        finfo_close($finfo);
        if (in_array($mime, $allowedTypes)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Cette fonction permet de télécharger un fichier sur le serveur et de vérifier les erreurs
     * 
     * @param array $file Le fichier à télécharger
     * @param array &$errors Un tableau pour stocker les messages d'erreur
     * @param string $folder Le dossier où le fichier doit être téléchargé (par défaut UPLOADS_DIR)
     * @param array $fileExtensions Les extensions de fichiers autorisées (par défaut FILE_EXT_IMG)
     * 
     * @return mixed Le nom du fichier si le téléchargement est réussi, un tableau de messages d'erreur sinon
     */
    public static function uploadFile(array $file, array &$errors, string $folder = UPLOADS_DIR, array $fileExtensions = FILE_EXT_IMG){
        $filename = '';
        $errors = array();
        // Vérifie s'il y a des erreurs lors du téléchargement du fichier
        if ($file["error"] === UPLOAD_ERR_OK) {
            // Vérifie si le fichier a un type MIME valide
            if(Tools::checkMIME($file["tmp_name"])){
                // Vérifie si l'extension de fichier est autorisée
                $tmpNameArray = explode(".", $file["name"]);
                $tmpExt = end($tmpNameArray);
                if(in_array($tmpExt,$fileExtensions)){
                    // Crée un nouveau nom de fichier unique
                    $filename = uniqid().'-'.basename($file["name"]);
                    // Déplace le fichier vers le dossier cible
                    if(!move_uploaded_file($file["tmp_name"], $folder.$filename)){
                        $errors[] = 'Le fichier n\'a pas été enregistré correctement';
                    }
                }else {
                    $errors[] = 'Ce type de fichier n\'est pas autorisé !';
                }
            }else {
                $errors[] = 'Ce type de fichier n\'est pas autorisé !';
            }
        }else if($file["error"] == UPLOAD_ERR_INI_SIZE || $file["error"] == UPLOAD_ERR_FORM_SIZE) {
            $errors[] = 'Le fichier est trop volumineux';
        }
        else {
            $errors[] = 'Une erreur a eu lieu lors du téléchargement';
        }

        if(count($errors) == 0){ return $filename; } else{ return $errors; }
    }

    /**
     * Redirige l'utilisateur vers une url spécifiée
     *
     * @param string $url L'url vers laquelle rediriger l'utilisateur
     * @param string $message (optionnel) Le message à afficher avant la redirection. Si aucun message n'est spécifié, l'utilisateur sera redirigé sans affichage de message
     *
     * @return void
     */
    public function redirectTo(string $url, string $message = '') {
        if(!empty(trim($url))) {
            if(!empty(trim($message))) {
                $_SESSION['message'] = $message;
            }
            header('Location: ' . URL . $url);
            exit;
        }
    }


    /**
    * Filtre une chaîne de caractères pour éviter les injections XSS
    *
    * @param string $data La chaîne à filtrer
    * @return string La chaîne filtrée
    */
    public static function sanitize($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /**
     * Retourne une chaîne formatée représentant la date et l'heure actuelles.
     *
     * @return string La date et l'heure actuelles au format 'YYYY-MM-DD HH:MM:SS'.
     */
    public static function currentTime(): string {
        // Génère une chaîne de date et d'heure formatée à l'aide de l'heure actuelle.
        $dateTime = date('Y-m-d H:i:s');

        // Retourne la chaîne générée.
        return $dateTime;
    }

    /**
     *
     * Fonction permettant de supprimer les accents d'une chaîne de caractères
     *
     * @param string $str La chaîne de caractères à traiter
     * @return string La chaîne de caractères sans accents
    */
    public function remove_accent($str){
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
                        'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã',
                        'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ',
                        'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ',
                        'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę',
                        'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī',
                        'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ',
                        'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ',
                        'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 
                        'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 
                        'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ',
                        'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');

        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O',
                        'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c',
                        'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u',
                        'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D',
                        'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g',
                        'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K',
                        'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o',
                        'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S',
                        's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W',
                        'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i',
                        'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        return str_replace($a, $b, $str);
    }


    /**
    * La fonction Slug génère une URL conviviale à partir d'un titre en enlevant les accents et en remplaçant les espaces par des tirets.
    * @param string $str Le titre à convertir en URL conviviale.
    * @return string L'URL conviviale générée.
    */
    public function Slug($str){
        return mb_strtolower(preg_replace(array('/[^a-zA-Z0-9 \'-]/', '/[ -\']+/', '/^-|-$/'),
        array('', '-', ''), $this->remove_accent($str)));
    }


}