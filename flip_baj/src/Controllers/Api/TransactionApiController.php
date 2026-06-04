<?php

namespace App\Controllers\Api;

use App\Models\Transaction;

class TransactionApiController {

    /**
     * Valide l'encaissement d'une vente (Caisse)
     */
    public function checkout() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $idAcheteur = $data['id_acheteur'] ?? 1;
        $typePaiement = $data['type_paiement'] ?? null;
        $panierJeuxIds = $data['jeux_ids'] ?? [];

        if (!$typePaiement || empty($panierJeuxIds)) {
            $this->sendJson(['success' => false, 'message' => 'Panier vide ou moyen de paiement manquant.']);
            return;
        }

        $transactionModel = new Transaction();
        $resultat = $transactionModel->validerVente($idAcheteur, $typePaiement, $panierJeuxIds);

        if ($resultat['success']) {
            $this->sendJson([
                'success' => true,
                'message' => 'Vente validée avec succès !',
                'id_transaction' => $resultat['id_transaction']
            ]);
        } else {
            $this->sendJson(['success' => false, 'message' => $resultat['message']]);
        }
    }

    /**
     * Valide la fin de la restitution (Remboursement, Don et changement de statut)
     */
    public function cloturerRestitution() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $idVendeur = $data['id_vendeur'] ?? null;
        $montantRemb = $data['montant_remboursement'] ?? 0;
        $typeRemb = $data['type_remboursement'] ?? null;
        $montantDon = $data['montant_don'] ?? 0;

        if (!$idVendeur) {
            $this->sendJson(['success' => false, 'message' => 'Vendeur non identifié.']);
            return;
        }

        $transactionModel = new Transaction();
        $resultat = $transactionModel->cloturerRestitution($idVendeur, $montantRemb, $typeRemb, $montantDon);

        if ($resultat['success']) {
            $this->sendJson(['success' => true, 'message' => 'Restitution validée avec succès.']);
        } else {
            $this->sendJson(['success' => false, 'message' => $resultat['message']]);
        }
    }

    /**
     * Fonction utilitaire pour renvoyer proprement du JSON
     */
    private function sendJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}