<?php

namespace App\Controllers;

use App\Utils\Database;
use PDO;

class ListeJeuxController {

    public function index() {
        $db = Database::getInstance();

        // La requête rapatriée de l'ancien fichier
        $sql = "SELECT id, value, 
                       (SELECT COUNT(id) FROM v_bourse_liste where id_statut=b.id) as nbr_status 
                FROM al_bourse_statuts_jeux b 
                ORDER BY id ASC";
        $stmt = $db->query($sql);

        $tab_categorie = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['nbr_status'] > 0) {
                $tab_categorie[] = $row;
            }
        }

        require __DIR__ . '/../Views/listejeux.php';
    }
}