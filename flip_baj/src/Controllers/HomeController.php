<?php

namespace App\Controllers;

class HomeController {
    public function index() {
        // Démarre la capture de la vue (pour injecter dans layout.php)
        ob_start();
        
        // Inclure la nouvelle vue nettoyée
        require __DIR__ . '/../Views/home.php';
        
        // Récupère le contenu généré par l'include
        $content = ob_get_clean();
        
        // Appelle le layout qui affichera le contenu généré
        require __DIR__ . '/../Views/layout.php';
    }
}