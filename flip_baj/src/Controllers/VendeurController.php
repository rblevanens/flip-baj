<?php

namespace App\Controllers;

use App\Models\Vendeur;

class VendeurController {

    public function getVendeursAjax() {
        header('Content-Type: application/json; charset=utf-8');

        $vendeurModel = new Vendeur();
        $vendeurs = $vendeurModel->getAllVendeurs();

        echo json_encode([
            "data" => $vendeurs
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}