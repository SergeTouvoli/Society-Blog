<?php


abstract class AbstractController {
    protected $template;


    /**
     * 
     * Affiche un fichier de vue à partir du répertoire spécifié.
     * @param string $directory Le nom du répertoire où se trouve le fichier de vue.
     * @param string $viewName Le nom du fichier de vue, sans l'extension '.phtml'.
     * @param array $variables Un tableau de variables à extraire et rendre disponibles pour la vue.
     * 
     * @return void
    */
    public function renderView(string $directory,string $viewName, array $variables = []): void {
        extract($variables);
        require_once "views/$directory/$viewName.phtml";
    }

    /**
     * Redirige l'utilisateur vers une url spécifiée
     *
     * @param string $url L'url vers laquelle rediriger l'utilisateur
     * @param string $message (optionnel) Le message à afficher avant la redirection. Si aucun message n'est spécifié, l'utilisateur sera redirigé sans affichage de message
     *
     * @return void
     */
    public function redirectTo(string $url, string $message = ''): void {
        if(!empty(trim($url))) {
            if(!empty(trim($message))) {
                $_SESSION['message'] = $message;
            }
            header('Location: ' . URL . $url);
            exit;
        }
    }
      
}
