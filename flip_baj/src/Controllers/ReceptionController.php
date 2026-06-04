<?php

namespace App\Controllers;

class ReceptionController
{
    public function index() {
        $id_vendeur = $_GET['id'] ?? null;
        if(!$id_vendeur) {
            header('Location: index.php?page=selectionvendeur');
            exit;
        }
        $titrePage = "Réception des jeux - Vendeur #" . htmlspecialchars($id_vendeur);
        require __DIR__ . '/../Views/receptionjeux.php';
    }
}