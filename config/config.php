<?php 

setlocale(LC_TIME, 'fr_FR.UTF8', 'fr.UTF8', 'fr_FR.UTF-8', 'fr.UTF-8');
date_default_timezone_set('Europe/Paris');

// Définition de l'URL de base du site
define("URL", str_replace("index.php","",(isset($_SERVER['HTTPS']) ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]"));

/**
 * Répertoire de base du site sur le disque du serveur
 * @var string
 */
define('BASE_DIR', realpath(dirname(__FILE__) . "/../"));

/**
 * Extensions acceptées pour les images
 * @var array
 */
const FILE_EXT_IMG = ['jpg','jpeg','gif','png','svg','JPG','JPEG','GIF','PNG','SVG'];

/**
 * Répertoire où seront téléchargés les fichiers
 * @var string
 */
const UPLOADS_DIR = BASE_DIR.'/public/images/';

// Définition des routes
const PAGE_ACCUEIL = "accueil";
const PAGE_CONNEXION = "connexion";
const PAGE_INSCRIPTION = "inscription";
const DECONNEXION = "deconnexion";
const PAGE_CONTACT = "contact";
const PAGE_RECHERCHE = "recherche";
const PAGE_POST = "post";
const PAGE_CATEGORIES = "categories";
const PAGE_GESTION_POSTS = "gestionPosts";
const PAGE_SEARCH_POST = "recherchePost";
const PAGE_GESTION_UTILISATEURS = "gestionUtilisateurs";
const PAGE_GESTION_CATEGORIES = "gestionCategories";
const PAGE_GESTION_MESSAGES = "gestionMessages";
const PAGE_VIEW_MESSAGE = "message";
const DELETE_MESSAGE = "suppressionMessage";
const PAGE_MON_COMPTE = "monCompte";
const PAGE_COMPTE = "compte";
const PAGE_EDIT_COMPTE = "modifierMesInfos";
const PAGE_EDIT_AVATAR = "modifierAvatar";
const PAGE_MES_POSTS = "mesPosts";
const PAGE_AJOUT_POST = "ajoutPost";
const PAGE_AJOUT_CATEGORIE = "ajoutCategorie";
const PAGE_EDIT_POST = "modifierPost";
const ADD_COMMENT = "ajoutCommentaire";
const LOAD_COMMENT = "chargerCommentaire";
const DELETE_POST = "suppressionPost";
const DELETE_USER = "suppressionUser";
const DELETE_CATEGORIE = "suppressionCategory";
const DELETE_COMMENT = "suppressionCommentaire";
const CHANGE_ROLE = "changerRole";

// Définition des erreurs
const ERROR_301 = "error301";
const ERROR_302 = "error302";
const ERROR_400 = "error400";
const ERROR_401 = "error401";
const ERROR_402 = "error402";
const ERROR_405 = "error405";
const ERROR_500 = "error500";
const ERROR_505 = "error505";
const ERROR_403 = "error403";
const ERROR_404 = "error404";
?>