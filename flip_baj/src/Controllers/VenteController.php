<?php

namespace App\Controllers;

use App\Models\Transaction;

class VenteController {

    public function index() {
        ob_start();
        require __DIR__ . '/../Views/vente.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layout.php';
    }

    public function getVentesAjax() {
        header('Content-Type: application/json; charset=utf-8');

        $transactionModel = new Transaction();
        $ventes = $transactionModel->getAllVentes();

        echo json_encode($ventes);
        exit;
    }
}