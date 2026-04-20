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
}