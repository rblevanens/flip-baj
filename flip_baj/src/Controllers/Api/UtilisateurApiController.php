<?php

namespace App\Controllers\Api;

use App\Models\Utilisateur;

class UtilisateurApiController {

    /**
     * Endpoint pour récupérer un utilisateur précis
     */
    public function getUser() {
        // Supporte à la fois le JSON et le form-data classique
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? $_POST;

        $id = $data['id'] ?? null;

        if (!$id) {
            $this->sendJson(['message2' => '0', 'message1' => 'ID manquant']);
            return;
        }

        $userModel = new Utilisateur();
        $vendeur = $userModel->getVendeurById($id);

        if ($vendeur) {
            // On garde la structure message1/message2 pour ne pas casser ton vieux JavaScript tout de suite
            $this->sendJson(['message2' => '1', 'message1' => $vendeur]);
        } else {
            $this->sendJson(['message2' => '0', 'message1' => 'Vendeur introuvable']);
        }
    }

    /**
     * Fonction utilitaire pour le formatage JSON
     */
    private function sendJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Endpoint de vérification (Anti-doublon)
     */
    public function checkVendeur() {
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $id = $_POST['id'] ?? null;

        $userModel = new Utilisateur();
        if ($userModel->checkNameExists($nom, $prenom, $id)) {
            $this->sendJson(['message2' => '0', 'message1' => 'Attention : Ce vendeur existe déjà !']);
        } else {
            $this->sendJson(['message2' => '1']);
        }
    }

    /**
     * Endpoint d'enregistrement (Création & Modification)
     */
    public function saveVendeur() {
        $userModel = new Utilisateur();
        $resultat = $userModel->saveVendeur($_POST);

        if ($resultat['success']) {
            $this->sendJson(['message2' => '1', 'message1' => $resultat['id']]);
        } else {
            $this->sendJson(['message2' => '0', 'message1' => $resultat['message']]);
        }
    }
}