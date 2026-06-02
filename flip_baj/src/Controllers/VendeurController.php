<?php

namespace App\Controllers;

use App\Models\Vendeur;

class VendeurController {

    public function getVendeursAjax() {
        header('Content-Type: application/json; charset=utf-8');
        $annee = date("Y");

        $vendeurModel = new Vendeur();
        $data = $vendeurModel->getAllWithStats($annee);

        $response = [
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
            "recordsTotal" => count($data),
            "data" => $data
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}