<?php

namespace App\Models;

use App\Utils\Database;
use PDO;

class Utilisateur {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère les informations complètes d'un vendeur via son ID
     * Remplace l'ancien ajax/user-get.php
     */
    public function getVendeurById($id) {
        $sql = "SELECT * FROM al_bourse_utilisateurs WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche des vendeurs pour l'autocomplétion (par nom ou prénom)
     */
    public function rechercherVendeurs($term) {
        $sql = "SELECT id, nom, prenom, email, telephone 
                FROM al_bourse_utilisateurs 
                WHERE nom LIKE :term OR prenom LIKE :term 
                ORDER BY nom ASC, prenom ASC 
                LIMIT 20";

        $stmt = $this->db->prepare($sql);
        // On ajoute les jokers % pour la recherche (ex: "Dup" trouvera "Dupont")
        $stmt->execute(['term' => '%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un vendeur avec ce nom et prénom existe déjà (pour éviter les doublons)
     */
    public function checkNameExists($nom, $prenom, $excludeId = null) {
        $sql = "SELECT id FROM al_bourse_utilisateurs WHERE nom = :nom AND prenom = :prenom";
        $params = ['nom' => $nom, 'prenom' => $prenom];

        if (!empty($excludeId)) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Enregistre ou met à jour un Vendeur
     */
    public function saveVendeur($data) {
        try {
            $params = [
                'nom' => $data['nomVendeurACreer'] ?? '',
                'prenom' => $data['prenomVendeurACreer'] ?? '',
                'email' => $data['emailVendeurACreer'] ?? '',
                'telephone' => $data['telephoneVendeurACreer'] ?? '',
                'adresse' => $data['adresseVendeurACreer'] ?? '',
                'codepostal' => $data['codepostalVendeurACreer'] ?? '',
                'ville' => $data['villeVendeurACreer'] ?? '',
                'denomination_sociale' => $data['denomination_socialeVendeurACreer'] ?? '',
                'siege_social' => $data['siege_socialVendeurACreer'] ?? '',
                'attestation_signee' => $data['attestation_signeeVendeurACreer'] ?? 'False'
            ];

            if (!empty($data['idVendeurEdition'])) {
                // MODIFICATION
                $params['id'] = $data['idVendeurEdition'];
                $sql = "UPDATE al_bourse_utilisateurs 
                        SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, 
                            adresse = :adresse, codepostal = :codepostal, ville = :ville, 
                            denomination_sociale = :denomination_sociale, siege_social = :siege_social, 
                            attestation_signee = :attestation_signee 
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                return ['success' => true, 'id' => $params['id']];
            } else {
                // CRÉATION
                $sql = "INSERT INTO al_bourse_utilisateurs (nom, prenom, email, telephone, adresse, codepostal, ville, denomination_sociale, siege_social, attestation_signee) 
                        VALUES (:nom, :prenom, :email, :telephone, :adresse, :codepostal, :ville, :denomination_sociale, :siege_social, :attestation_signee)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                return ['success' => true, 'id' => $this->db->lastInsertId()];
            }
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => "Erreur DB: " . $e->getMessage()];
        }
    }
}