<?php

namespace App\Models;

use App\Utils\Database;
use PDO;

class Jeu {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère la liste des jeux avec des filtres optionnels
     */
    public function getJeuxFiltres($filters = []) {
        $sql = "SELECT 
                    l.id, 
                    l.code_barre, 
                    l.prix AS vendu, 
                    l.nom_jeu AS nj, 
                    l.statut AS id_statut, 
                    s.value AS statut, 
                    l.vigilance, 
                    l.date_reception, 
                    l.date_sortie_stock, 
                    u.id AS id_utilisateur, 
                    u.nom, 
                    u.prenom 
                FROM al_bourse_liste l
                LEFT JOIN al_bourse_users u ON l.id_utilisateur = u.id 
                LEFT JOIN al_bourse_status_jeux s ON l.statut = s.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['idStatut'])) {
            $sql .= " AND l.statut = :idStatut";
            $params['idStatut'] = $filters['idStatut'];
        }

        if (!empty($filters['idVendeur'])) {
            $sql .= " AND l.id_utilisateur = :idVendeur";
            $params['idVendeur'] = $filters['idVendeur'];
        }

        if (isset($filters['vigilance']) && $filters['vigilance'] !== '') {
            $sql .= " AND l.vigilance = :vigilance";
            $params['vigilance'] = $filters['vigilance'];
        }

        // $sql .= " ORDER BY l.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function findByCodeBarre($codeBarre) {
        $sql = "SELECT 
                    l.id, 
                    l.code_barre, 
                    l.prix, 
                    l.nom_jeu AS nom, 
                    l.statut AS id_statut, 
                    s.value AS statut, 
                    l.id_utilisateur 
                FROM al_bourse_liste l
                LEFT JOIN al_bourse_status_jeux s ON l.statut = s.id
                WHERE l.code_barre = :code_barre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code_barre' => $codeBarre]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Ajoute un nouveau jeu dans le stock lors de la réception
     */
    public function ajouterJeuReception($codeBarre, $nomJeu, $prix, $idVendeur, $vigilance = 0) {
        $sql = "INSERT INTO al_bourse_liste (code_barre, nom_jeu, prix, id_utilisateur, statut, vigilance, date_reception) 
                VALUES (:code_barre, :nom_jeu, :prix, :id_vendeur, 2, :vigilance, NOW())";

        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute([
                'code_barre' => $codeBarre,
                'nom_jeu' => $nomJeu,
                'prix' => $prix,
                'id_vendeur' => $idVendeur,
                'vigilance' => $vigilance
            ]);

            return ['success' => true, 'id_jeu' => $this->db->lastInsertId()];

        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Met à jour un jeu existant pour le remettre en stock lors de la réception
     */
    public function updateJeuReception($codeBarre, $prix, $idVendeur, $vigilance = 0) {
        $sql = "UPDATE al_bourse_liste 
                SET statut = 2, 
                    prix = :prix, 
                    id_utilisateur = :id_vendeur, 
                    vigilance = :vigilance, 
                    date_reception = NOW() 
                WHERE code_barre = :code_barre";

        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute([
                'prix' => $prix,
                'id_vendeur' => $idVendeur,
                'vigilance' => $vigilance,
                'code_barre' => $codeBarre
            ]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true];
            } else {
                return ['success' => true, 'message' => 'Aucune modification nécessaire ou jeu introuvable.'];
            }

        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Marque un jeu comme "Rendu" (invendu récupéré par le vendeur)
     */
    public function marquerCommeRendu($codeBarre, $idVendeur) {
        // Sécurité : on vérifie que le jeu appartient bien au vendeur et qu'il est en stock (statut 2)
        // Le statut 4 correspond généralement à "Rendu"
        $sql = "UPDATE al_bourse_liste 
                SET statut = 4, 
                    date_sortie_stock = NOW() 
                WHERE code_barre = :code_barre 
                AND id_utilisateur = :id_vendeur 
                AND statut = 2";

        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute([
                'code_barre' => $codeBarre,
                'id_vendeur' => $idVendeur
            ]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => "Le jeu n'est pas en stock ou n'appartient pas à ce vendeur."];
            }

        } catch (\PDOException $e) {
            return ['success' => false, 'message' => "Erreur base de données : " . $e->getMessage()];
        }
    }
}