<?php

namespace App\Controllers;

class VenteController {
    public function index() {
        // Démarre la capture de la vue
        ob_start();
        
        // On inclut la nouvelle vue nettoyée
        require __DIR__ . '/../Views/vente.php';
        
        // Récupère le contenu généré par l'include
        $content = ob_get_clean();
        
        // Appelle le layout global
        require __DIR__ . '/../Views/layout.php';
    }
}