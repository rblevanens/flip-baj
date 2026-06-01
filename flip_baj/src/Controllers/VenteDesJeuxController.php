<?php

namespace App\Controllers;

use App\Models\Jeu;

class VenteDesJeuxController {
    public function index() {
        $jeuModel = new Jeu();
        $jeux = $jeuModel->getAllJeuxEnVente();

        ob_start();

        require __DIR__ . '/../Views/ventedesjeux.php';

        $content = ob_get_clean();

        require __DIR__ . '/../Views/layout.php';
    }
}