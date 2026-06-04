<?php

namespace App\Controllers;

class RestitutionController {

    public function index() {
        $id_vendeur = $_GET['id'] ?? null;

        if (!$id_vendeur) {
            header('Location: index.php?page=selectionvendeur&t=restitution');
            exit;
        }

        $titrePage = "Restitution des jeux - Vendeur #" . htmlspecialchars($id_vendeur);

        require __DIR__ . '/../Views/restitutiondesjeux.php';
    }
}