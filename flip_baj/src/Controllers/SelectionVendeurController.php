<?php

namespace App\Controllers;

class SelectionVendeurController
{
    public function index() {
        $type = $_GET['t'] ?? 'reception';
        $titreAction = ($type === 'restitution') ? 'Restitution des jeux' : 'Réception des jeux';
        require __DIR__ . '/../Views/selectionvendeur.php';
    }
}