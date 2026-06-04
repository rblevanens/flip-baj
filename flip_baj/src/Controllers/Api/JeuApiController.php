<?php

namespace App\Controllers\Api;

use App\Models\Jeu;

class JeuApiController {

    public function checkCodeBarre() {
        $codeBarre = $_POST['code_barre'] ?? $_POST['CodeBarreAjout'] ?? null;

        if (!$codeBarre) {
            $this->sendJson(['success' => false, 'message' => 'Aucun code-barre fourni.']);
            return;
        }

        $jeuModel = new Jeu();
        $jeu = $jeuModel->findByCodeBarre($codeBarre);

        if (!$jeu) {
            $this->sendJson(['success' => false, 'message' => 'Ce jeu est introuvable dans la base.']);
            return;
        }

        if ($jeu['id_statut'] != 2) {
            $etat = $jeu['statut'] ?? 'inconnu';
            $this->sendJson(['success' => false, 'message' => "Action impossible : Ce jeu est '$etat'."]);
            return;
        }

        $this->sendJson([
            'success' => true,
            'data' => $jeu
        ]);
    }

    /**
     * Méthode utilitaire pour garantir un export propre au format JSON
     */
    private function sendJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function addJeu() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? $_POST;

        $codeBarre = $data['code_barre'] ?? null;
        $nomJeu = $data['nom_jeu'] ?? null;
        $prix = $data['prix'] ?? null;
        $idVendeur = $data['id_vendeur'] ?? null;
        $vigilance = $data['vigilance'] ?? 0;

        if (!$codeBarre || !$nomJeu || !$prix || !$idVendeur) {
            $this->sendJson(['success' => false, 'message' => 'Données incomplètes : le code-barre, le nom, le prix et le vendeur sont obligatoires.']);
            return;
        }

        $jeuModel = new Jeu();
        $resultat = $jeuModel->ajouterJeuReception($codeBarre, $nomJeu, $prix, $idVendeur, $vigilance);

        if ($resultat['success']) {
            $this->sendJson([
                'success' => true,
                'message' => 'Jeu ajouté au stock.',
                'id_jeu' => $resultat['id_jeu']
            ]);
        } else {
            $this->sendJson(['success' => false, 'message' => 'Erreur SQL : ' . $resultat['message']]);
        }
    }

    public function updateJeu() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? $_POST;

        $codeBarre = $data['code_barre'] ?? null;
        $prix = $data['prix'] ?? null;
        $idVendeur = $data['id_vendeur'] ?? null;
        $vigilance = $data['vigilance'] ?? 0;

        if (!$codeBarre || !$prix || !$idVendeur) {
            $this->sendJson(['success' => false, 'message' => 'Données incomplètes pour la mise à jour.']);
            return;
        }

        $jeuModel = new Jeu();
        $resultat = $jeuModel->updateJeuReception($codeBarre, $prix, $idVendeur, $vigilance);

        if ($resultat['success']) {
            $this->sendJson(['success' => true, 'message' => 'Jeu mis à jour et remis en stock.']);
        } else {
            $this->sendJson(['success' => false, 'message' => 'Erreur SQL : ' . $resultat['message']]);
        }
    }

    public function restituerJeu() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? $_POST;

        $codeBarre = $data['code_barre'] ?? null;
        $idVendeur = $data['id_vendeur'] ?? null;

        if (!$codeBarre || !$idVendeur) {
            $this->sendJson(['success' => false, 'message' => 'Code-barre ou ID vendeur manquant.']);
            return;
        }

        $jeuModel = new Jeu();
        $resultat = $jeuModel->marquerCommeRendu($codeBarre, $idVendeur);

        if ($resultat['success']) {
            $this->sendJson(['success' => true, 'message' => 'Jeu restitué avec succès.']);
        } else {
            $this->sendJson(['success' => false, 'message' => $resultat['message']]);
        }
    }
}