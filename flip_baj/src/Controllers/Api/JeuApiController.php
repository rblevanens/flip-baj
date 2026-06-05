<?php

namespace App\Controllers\Api;

use App\Models\Jeu;

class JeuApiController {

    // =========================================================================
    // ROUTES JSON CLASSIQUES (AJOUT, MISE À JOUR, RESTITUTION, VÉRIFICATION)
    // =========================================================================

    /**
     * Ajoute un nouveau jeu au stock du vendeur (depuis la page réception)
     */
    public function addJeu() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? $_POST;

        $codeBarre = $data['code_barre'] ?? '';
        $nomJeu = $data['nom_jeu'] ?? '';
        $prix = (int)($data['prix'] ?? 0);
        $idVendeur = (int)($data['id_vendeur'] ?? 0);
        $vigilance = (int)($data['vigilance'] ?? 0);

        if (!$codeBarre || !$nomJeu || !$prix || !$idVendeur) {
            $this->sendJson(['success' => false, 'message' => 'Données incomplètes.']);
            return;
        }

        $jeuModel = new Jeu();
        // On s'assure que le code barre est bien formaté ("Festival_XXXX")
        $fullCodeBarre = Jeu::formatCodeBarre($codeBarre);

        if (empty($fullCodeBarre) || $jeuModel->isCodeBarrePris($fullCodeBarre)) {
            $this->sendJson(['success' => false, 'message' => 'Code-barre invalide ou déjà pris.']);
            return;
        }

        $resultat = $jeuModel->ajouterJeuReception($fullCodeBarre, $nomJeu, $prix, $idVendeur, $vigilance);
        $this->sendJson($resultat);
    }

    /**
     * Remet en stock un jeu existant (réception)
     */
    public function updateJeu() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? $_POST;

        $codeBarre = $data['code_barre'] ?? '';
        $prix = (int)($data['prix'] ?? 0);
        $idVendeur = (int)($data['id_vendeur'] ?? 0);
        $vigilance = (int)($data['vigilance'] ?? 0);

        $jeuModel = new Jeu();
        $fullCodeBarre = Jeu::formatCodeBarre($codeBarre);

        $resultat = $jeuModel->updateJeuReception($fullCodeBarre, $prix, $idVendeur, $vigilance);
        $this->sendJson($resultat);
    }

    /**
     * Restitue un jeu (Le vendeur le récupère)
     */
    public function restituerJeu() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? $_POST;

        $codeBarre = $data['code_barre'] ?? '';
        $idVendeur = (int)($data['id_vendeur'] ?? 0);

        $jeuModel = new Jeu();
        $resultat = $jeuModel->marquerCommeRendu($codeBarre, $idVendeur);
        $this->sendJson($resultat);
    }

    /**
     * Vérifie si un code barre est déjà pris (utilisé dans receptionjeux.js)
     */
    public function checkCodeBarre() {
        $codeBarreBrut = $_POST['CodeBarreAjout'] ?? '';

        $jeuModel = new Jeu();
        $fullCodeBarre = Jeu::formatCodeBarre($codeBarreBrut);

        if ($jeuModel->isCodeBarrePris($fullCodeBarre)) {
            $this->sendJson(['message2' => '0', 'message1' => '<p class="bg-danger">Ce code-barre est déjà pris.</p>']);
        } else {
            $this->sendJson(['message2' => '1', 'message1' => 'Code OK']);
        }
    }


    // =========================================================================
    // ROUTES JEDITABLE (BOSS FINAL) - ATTENTION: CES ROUTES RENVOIENT DU TEXTE !
    // =========================================================================

    /**
     * Modification rapide du Code-Barre via le double-clic dans le tableau
     */
    public function inlineCodeBarre() {
        $idJeu = (int)($_POST['id'] ?? 0);
        $codeBarreBrut = trim($_POST['value'] ?? '');
        $statut = (int)($_POST['statut'] ?? 2);

        if (!$idJeu || $codeBarreBrut === '') {
            echo "Erreur données";
            exit;
        }

        $jeuModel = new Jeu();
        $fullCodeBarre = Jeu::formatCodeBarre($codeBarreBrut);

        if (empty($fullCodeBarre)) {
            echo "Format invalide";
            exit;
        }

        $resultat = $jeuModel->updateCodeBarreInline($idJeu, $fullCodeBarre, $statut);

        if ($resultat['success']) {
            // Jeditable attend de recevoir la valeur exacte qu'il doit afficher dans la case
            echo $fullCodeBarre;
        } else {
            echo $resultat['message'] ?? "Erreur";
        }
        exit;
    }

    /**
     * Modification rapide du prix via le double-clic dans le tableau
     */
    public function inlinePrix() {
        $idJeu = (int)($_POST['id'] ?? 0);
        $prix = (int)($_POST['value'] ?? 0);

        // On récupère le type (vendu ou rendu) depuis l'URL de la requête AJAX
        $type = $_GET['type'] ?? 'vendu';

        if (!$idJeu || $prix < 0) {
            echo "Erreur Prix";
            exit;
        }

        $jeuModel = new Jeu();
        // On appelle la méthode avec le correctif que je t'ai donné tout à l'heure !
        $resultat = $jeuModel->updatePrixInline($idJeu, $prix, $type);

        if ($resultat['success']) {
            // On renvoie le prix avec le symbole € pour l'affichage dans le tableau
            echo $prix . " €";
        } else {
            echo "Erreur base";
        }
        exit;
    }

    // =========================================================================
    // UTILITAIRES
    // =========================================================================

    private function sendJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}