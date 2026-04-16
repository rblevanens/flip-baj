<?php

namespace App\Controllers;

class VenteDesJeuxController {
    public function index() {
        ob_start();
        
        // J'inclus l'utilitaire qui était requis par la page d'origine
        require_once __DIR__ . '/../../main/utils.php';

        require __DIR__ . '/../Views/ventedesjeux.php';
        
        $content = ob_get_clean();
        
        require __DIR__ . '/../Views/layout.php';
    }
}