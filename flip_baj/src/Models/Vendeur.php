<?php

namespace App\Models;

use App\Utils\Database;
use PDO;

class Vendeur {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllVendeurs() {
        $sql = "SELECT id, nom, prenom, email FROM al_bourse_users ORDER BY nom ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWithStats($annee) {
        $sql = "SELECT distinct
                    u.id as idDuVendeur,
                    u.nom as nom, 
                    u.prenom as prenom,
                    u.email as email,
                    u.telephone as telephone,
                    t.nbjeuxrendus,
                    t.nbjeuxvendus,
                    t.nbjeuxstock,
                    t.nbjeuxdonnes,
                    t.nbjeuxpasrecus
                FROM al_bourse_users u
                LEFT JOIN (
                    SELECT id_utilisateur as iduser,
                    SUM(CASE when id_statut='4' then 1 else 0 end) as nbjeuxrendus,
                    SUM(CASE when id_statut='3' then 1 else 0 end) as nbjeuxvendus,
                    SUM(CASE when id_statut='2' then 1 else 0 end) as nbjeuxstock,
                    SUM(CASE when id_statut='6' then 1 else 0 end) as nbjeuxdonnes,
                    SUM(CASE when id_statut='1' then 1 else 0 end) as nbjeuxpasrecus
                    FROM v_bourse_liste
                    WHERE annee = :annee
                    GROUP BY id_utilisateur
                ) as t on u.id = t.iduser
                ORDER BY nom, prenom";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['annee' => $annee]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}