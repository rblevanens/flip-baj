<?php

namespace App\Models;

use App\Utils\Database;
use PDO;
use Exception;

class Transaction {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère l'historique complet pour la page des ventes
     */
    public function getAllVentes() {
        $sql = "SELECT 
                    t.id AS id_transaction, 
                    t.date_transaction AS date, 
                    t.id_type_paiement AS paiement, 
                    t.id_acheteur, 
                    a.nom, 
                    a.prenom, 
                    a.email,
                    (SELECT COUNT(*) FROM al_bourse_transactions_liste tl WHERE tl.id_transaction = t.id) AS nbjeux,
                    GROUP_CONCAT(j.nom_jeu SEPARATOR ', ') AS jeux
                FROM al_bourse_transactions t
                LEFT JOIN al_bourse_acheteur a ON t.id_acheteur = a.id
                LEFT JOIN al_bourse_transactions_liste tl ON t.id = tl.id_transaction
                LEFT JOIN al_bourse_liste j ON tl.id_jeu = j.id
                GROUP BY t.id
                ORDER BY t.date_transaction DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Valide une nouvelle vente depuis la caisse (Endpoint API)
     */
    public function validerVente($idAcheteur, $typePaiement, $panierJeuxIds) {
        try {
            $this->db->beginTransaction();

            $sqlTransac = "INSERT INTO al_bourse_transactions (id_acheteur, date_transaction, id_type_paiement) 
                           VALUES (:id_acheteur, NOW(), :type_paiement)";
            $stmt = $this->db->prepare($sqlTransac);
            $stmt->execute([
                'id_acheteur' => $idAcheteur,
                'type_paiement' => $typePaiement
            ]);

            $idTransaction = $this->db->lastInsertId();

            $sqlVerif = "SELECT statut FROM al_bourse_liste WHERE id = :id_jeu FOR UPDATE";
            $sqlUpdate = "UPDATE al_bourse_liste SET statut = 3, date_sortie_stock = NOW() WHERE id = :id_jeu";
            $sqlInsert = "INSERT INTO al_bourse_transactions_liste (id_transaction, id_jeu) VALUES (:id_transaction, :id_jeu)";

            $stmtVerif = $this->db->prepare($sqlVerif);
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtInsert = $this->db->prepare($sqlInsert);

            foreach ($panierJeuxIds as $idJeu) {
                $stmtVerif->execute(['id_jeu' => $idJeu]);
                $jeu = $stmtVerif->fetch(PDO::FETCH_ASSOC);

                if (!$jeu || $jeu['statut'] != 2) {
                    throw new Exception("Le jeu ID $idJeu n'est plus en stock.");
                }

                $stmtUpdate->execute(['id_jeu' => $idJeu]);
                $stmtInsert->execute(['id_transaction' => $idTransaction, 'id_jeu' => $idJeu]);
            }

            $this->db->commit();
            return ['success' => true, 'id_transaction' => $idTransaction];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Clôture la restitution d'un vendeur : enregistre le don, le remboursement et met à jour les jeux en une seule transaction.
     */
    public function cloturerRestitution($idVendeur, $montantRemb, $typeRemb, $montantDon) {
        try {
            // On verrouille la base : si une requête échoue, tout s'annule
            $this->db->beginTransaction();

            // 1. Enregistrement du don (s'il y en a un)
            if ($montantDon > 0) {
                $sqlDon = "INSERT INTO al_bourse_dons (id_utilisateur, montant_don, date_don, type_don) 
                           VALUES (:id, :montant, NOW(), 'Non remboursement')";
                $stmtDon = $this->db->prepare($sqlDon);
                $stmtDon->execute([
                    'id' => $idVendeur,
                    'montant' => $montantDon
                ]);
            }

            // 2. Enregistrement du remboursement de la cagnotte
            if ($montantRemb > 0) {
                $sqlRemb = "INSERT INTO al_bourse_remboursements (id_utilisateur, montant_remb, date_remb, type_remb) 
                            VALUES (:id, :montant, NOW(), :type)";
                $stmtRemb = $this->db->prepare($sqlRemb);
                $stmtRemb->execute([
                    'id' => $idVendeur,
                    'montant' => $montantRemb,
                    'type' => $typeRemb
                ]);
            }

            // 3. On bascule les jeux qui étaient "Vendus" (statut 3) en "Remboursés/Clôturés" (souvent statut 5 dans ta base)
            $sqlStatut = "UPDATE al_bourse_liste 
                          SET statut = 5 
                          WHERE id_utilisateur = :id AND statut = 3";
            $stmtStatut = $this->db->prepare($sqlStatut);
            $stmtStatut->execute(['id' => $idVendeur]);

            // Tout est bon, on valide la transaction !
            $this->db->commit();
            return ['success' => true];

        } catch (\PDOException $e) {
            // Si la moindre erreur survient (plantage, rupture réseau), la caisse est protégée et aucun don/remboursement n'est enregistré.
            $this->db->rollBack();
            return ['success' => false, 'message' => "Erreur critique de la base de données : " . $e->getMessage()];
        }
    }
}