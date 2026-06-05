<?php

namespace App\Models;

use App\Utils\Database;
use PDO;

class Jeu {
    private $db;

    /** Année courante du festival (même logique que annee_base dans constantes.php) */
    private $annee;

    public function __construct() {
        $this->db  = Database::getInstance();
        $this->annee = date('Y');
    }

    // -------------------------------------------------------------------------
    // Utilitaires métier (ex-utils.php)
    // -------------------------------------------------------------------------

    /**
     * Formate et valide un code-barre au format Festival_NNNN.
     * Retourne le code formaté, ou '' si invalide.
     */
    public static function formatCodeBarre(string $raw): string {
        $raw = trim($raw);
        $prefix = 'Festival_';
        $full_len = strlen($prefix) + 4; // Festival_NNNN = 13

        if (strlen($raw) <= 4) {
            $raw = str_pad($raw, 4, '0', STR_PAD_LEFT);
            $raw = $prefix . $raw;
        }

        if (strlen($raw) !== $full_len) return '';
        if (substr($raw, 0, strlen($prefix)) !== $prefix) return '';
        if (!preg_match('/^.+_[0-9]{4}$/', $raw)) return '';

        return $raw;
    }

    /**
     * Calcule le prix rendu à partir du prix vendu (prix - 1/6 arrondi sup).
     */
    public static function prixRendu(int $prixVendu): int {
        return $prixVendu - (int) ceil($prixVendu / 6.0);
    }

    /**
     * Calcule le prix vendu à partir du prix rendu (rendu + 20% arrondi sup).
     */
    public static function prixRendu2Prix(int $prixRendu): int {
        return $prixRendu + (int) ceil($prixRendu * 0.2);
    }

    // -------------------------------------------------------------------------
    // Référentiel des jeux (table al_bourse_jeux)
    // -------------------------------------------------------------------------

    /**
     * Recherche dans le référentiel de jeux pour l'autocomplete.
     * $exact = true → recherche exacte, false → LIKE %term%
     */
    public function searchByNom(string $term, bool $exact = false): array {
        $param = $exact ? $term : '%' . $term . '%';
        $sql = "SELECT id, nom AS label, nom AS value
                FROM al_bourse_jeux
                WHERE nom LIKE :nom
                ORDER BY nom ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nom' => $param]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insère un nouveau nom de jeu dans le référentiel.
     * Retourne ['success' => true, 'id' => X] ou ['success' => false, 'message' => '...']
     */
    public function insertNomJeu(string $nom): array {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO al_bourse_jeux (nom) VALUES (:nom_jeu)"
            );
            $stmt->execute(['nom_jeu' => $nom]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Vérification code-barre (unicité dans la liste courante)
    // -------------------------------------------------------------------------

    /**
     * Vérifie si un code-barre est déjà utilisé dans l'édition courante.
     * $excludeId permet d'exclure un jeu existant (édition inline).
     */
    public function isCodeBarrePris(string $codeBarre, ?int $excludeId = null): bool {
        $sql = "SELECT id FROM al_bourse_liste
                WHERE code_barre = :code_barre
                  AND annee = :annee";
        $params = ['code_barre' => $codeBarre, 'annee' => $this->annee];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------------------------
    // Lecture — liste par vendeur (page modalevendeur / jeuxliste-get)
    // -------------------------------------------------------------------------

    /**
     * Retourne la liste des jeux d'un vendeur pour DataTables.
     * Supporte un tri dynamique transmis par DataTables ($_POST['order']).
     *
     * $filters = [
     *   'idVendeur'  => int,
     *   'idStatut'   => int,
     *   'code_barre' => string|null,   // recherche colonne DataTables
     *   'order'      => [['column' => 0, 'dir' => 'asc']], // $_POST['order']
     *   'columns'    => [...],          // $_POST['columns']
     * ]
     */
    public function getListeParVendeur(array $filters): array {
        $allowedCols = [
            'code_barre', 'nom_jeu', 'statut', 'prix', 'date_reception', 'date_sortie_stock'
        ];

        $sql    = "SELECT DISTINCT
                       v_bourse_liste.id,
                       v_bourse_liste.id_utilisateur,
                       v_bourse_liste.nom,
                       v_bourse_liste.prenom,
                       v_bourse_liste.id_statut,
                       v_bourse_liste.statut,
                       CASE v_bourse_liste.vigilance WHEN 0 THEN 'Non' ELSE 'Oui' END AS vigilance,
                       v_bourse_liste.nom_jeu AS nj,
                       v_bourse_liste.vendu,
                       (SELECT MAX(vendu) FROM v_bourse_liste vx WHERE vx.nom_jeu = v_bourse_liste.nom_jeu) AS maxprix,
                       (SELECT MIN(vendu) FROM v_bourse_liste vx WHERE vx.nom_jeu = v_bourse_liste.nom_jeu) AS minprix,
                       v_bourse_liste.code_barre,
                       v_bourse_liste.date_reception,
                       v_bourse_liste.date_sortie_stock
                   FROM v_bourse_liste
                   WHERE v_bourse_liste.id_utilisateur = :idVendeur
                     AND v_bourse_liste.id_statut      = :idStatut
                     AND v_bourse_liste.annee          = :annee";

        $params = [
            'idVendeur' => $filters['idVendeur'],
            'idStatut'  => $filters['idStatut'],
            'annee'     => $this->annee,
        ];

        // Recherche par code-barre (colonne DataTables)
        if (!empty($filters['code_barre'])) {
            $sql .= " AND v_bourse_liste.code_barre LIKE CONCAT('%', :code_barre, '%')";
            $params['code_barre'] = $filters['code_barre'];
        }

        // Tri dynamique DataTables
        $orderBy = 'v_bourse_liste.nom_jeu ASC'; // défaut
        if (!empty($filters['order']) && !empty($filters['columns'])) {
            $colIdx = (int) $filters['order'][0]['column'];
            $dir    = strtoupper($filters['order'][0]['dir'] ?? 'ASC');
            $dir    = in_array($dir, ['ASC', 'DESC']) ? $dir : 'ASC';
            $colName = $filters['columns'][$colIdx]['name'] ?? '';
            if (in_array($colName, $allowedCols)) {
                $orderBy = $colName . ' ' . $dir;
            }
        }
        $sql .= " ORDER BY " . $orderBy;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $data = [];
        while ($jeu = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row              = [
                'Code'            => $jeu['code_barre'],
                'Jeu'             => $jeu['nj'],
                'Vendu'           => $jeu['vendu'],
                'Rendu'           => self::prixRendu((int) $jeu['vendu']),
                'Commission'      => (int) ceil($jeu['vendu'] / 6.0),
                'vigilance'       => $jeu['vigilance'],
                'DT_RowId'        => $jeu['id'],
                'date_reception'  => $jeu['date_reception'],
                'minprix'         => $jeu['minprix'],
                'maxprix'         => $jeu['maxprix'],
                'DateSortieStock' => $jeu['date_sortie_stock'],
            ];
            $data[] = $row;
        }

        return [
            'recordsTotal'    => count($data),
            'recordsFiltered' => count($data),
            'data'            => $data,
        ];
    }

    // -------------------------------------------------------------------------
    // Lecture — liste globale en stock (page admin/vente / jeuxliste-getenstockspeed)
    // -------------------------------------------------------------------------

    /**
     * Retourne la liste filtrée de tous les jeux (vue globale stock).
     *
     * $filters = [
     *   'idVendeur'  => int|null,
     *   'idStatut'   => int|null,
     *   'vigilance'  => int|null,
     *   'code_barre' => string|null,
     *   'nom_jeu'    => string|null,
     * ]
     */
    public function getListeStock(array $filters = []): array {
        $conditions = ["v_bourse_liste.annee = :annee"];
        $params     = ['annee' => $this->annee];

        if (isset($filters['idVendeur'])) {
            $conditions[] = "v_bourse_liste.id_utilisateur = :idVendeur";
            $params['idVendeur'] = $filters['idVendeur'];
        }
        if (isset($filters['idStatut'])) {
            $conditions[] = "v_bourse_liste.id_statut = :idStatut";
            $params['idStatut'] = $filters['idStatut'];
        }
        if (isset($filters['vigilance'])) {
            $conditions[] = "v_bourse_liste.vigilance = :vigilance";
            $params['vigilance'] = $filters['vigilance'];
        }
        if (!empty($filters['code_barre'])) {
            $conditions[] = "v_bourse_liste.code_barre LIKE CONCAT('%', :code_barre, '%')";
            $params['code_barre'] = $filters['code_barre'];
        }
        if (!empty($filters['nom_jeu'])) {
            $conditions[] = "v_bourse_liste.nom_jeu LIKE CONCAT('%', :nom_jeu, '%')";
            $params['nom_jeu'] = $filters['nom_jeu'];
        }

        $sql = "SELECT DISTINCT
                    v_bourse_liste.id,
                    v_bourse_liste.id_utilisateur,
                    v_bourse_liste.nom,
                    v_bourse_liste.prenom,
                    v_bourse_liste.id_statut,
                    v_bourse_liste.statut,
                    v_bourse_liste.vigilance,
                    v_bourse_liste.nom_jeu AS nj,
                    v_bourse_liste.vendu,
                    v_bourse_liste.code_barre,
                    v_bourse_liste.date_reception,
                    v_bourse_liste.date_sortie_stock
                FROM v_bourse_liste
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY v_bourse_liste.nom_jeu ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $data = [];
        while ($jeu = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row = [
                'Code'             => $jeu['code_barre'],
                'Vendu'            => $jeu['vendu'],
                'Rendu'            => self::prixRendu((int) $jeu['vendu']),
                'Vendeur'          => $jeu['nom'] . ' ' . $jeu['prenom'],
                'DT_RowId'         => $jeu['id'],
                'idstatut'         => $jeu['id_statut'],
                'statut'           => $jeu['statut'],
                'vigilance'        => $jeu['vigilance'],
                'date_reception'   => $jeu['date_reception'],
                'idvendeur'        => $jeu['id_utilisateur'],
                'Jeu'              => $jeu['nj'],
                'date_sortie_stock'=> $jeu['date_sortie_stock'],
            ];
            $data[] = $row;
        }

        return [
            'recordsTotal'    => count($data),
            'recordsFiltered' => count($data),
            'data'            => $data,
        ];
    }

    // -------------------------------------------------------------------------
    // Lecture — réception (findByCodeBarre déjà présent, on garde)
    // -------------------------------------------------------------------------

    public function findByCodeBarre(string $codeBarre): ?array {
        $stmt = $this->db->prepare(
            "SELECT l.id, l.code_barre, l.prix, l.nom_jeu AS nom,
                    l.statut AS id_statut, s.value AS statut, l.id_utilisateur
             FROM al_bourse_liste l
             LEFT JOIN al_bourse_status_jeux s ON l.statut = s.id
             WHERE l.code_barre = :code_barre"
        );
        $stmt->execute(['code_barre' => $codeBarre]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // -------------------------------------------------------------------------
    // Écriture — changement de statut + log journal
    // -------------------------------------------------------------------------

    /**
     * Met à jour le statut d'un jeu (et optionnellement code_barre, dates).
     * Écrit toujours dans le journal.
     *
     * $data = [
     *   'id'           => int,     // obligatoire
     *   'statut'       => int,     // obligatoire
     *   'old_statut'   => int,     // pour le journal (0 si inconnu)
     *   'code_barre'   => string|null,
     *   'date_sortie'  => bool,    // true → SET date_sortie_stock = NOW()
     *   'date_recep'   => bool,    // true → SET id_depot + date_reception
     * ]
     */
    public function updateStatut(array $data): array {
        try {
            $this->db->beginTransaction();

            $sets   = ['statut = :statut'];
            $params = [
                'statut' => $data['statut'],
                'id'     => $data['id'],
            ];

            if (!empty($data['code_barre'])) {
                $sets[]               = 'code_barre = :code_barre';
                $params['code_barre'] = $data['code_barre'];
            }
            if (!empty($data['date_sortie'])) {
                $sets[]              = 'date_sortie_stock = :date_sortie';
                $params['date_sortie'] = date('Y-m-d H:i:s');
            }
            if (!empty($data['date_recep'])) {
                $sets[]              = 'id_depot = :id_depot';
                $sets[]              = 'date_reception = :date_reception';
                $params['id_depot']       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $params['date_reception'] = date('Y-m-d H:i:s');
            }

            $sql  = "UPDATE al_bourse_liste SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $this->logJournal(
                (int) $data['id'],
                (int) ($data['old_statut'] ?? 0),
                (int) $data['statut']
            );

            $this->db->commit();
            return ['success' => true, 'rows' => $stmt->rowCount()];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Vérifie qu'un code-barre est libre, puis change le statut + code + log.
     * Utilisé par l'ancienne logique jeuxliste-checkandupdate.
     * Retourne ['success' => false, 'message' => 'Code déjà pris'] si doublon.
     */
    public function checkAndUpdateCodeBarre(int $id, string $codeBarre, int $statut, int $oldStatut = 0): array {
        if ($this->isCodeBarrePris($codeBarre, $id)) {
            return ['success' => false, 'message' => 'Code déjà pris'];
        }

        return $this->updateStatut([
            'id'         => $id,
            'statut'     => $statut,
            'old_statut' => $oldStatut,
            'code_barre' => $codeBarre,
            'date_recep' => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // Écriture — vigilance
    // -------------------------------------------------------------------------

    public function setVigilance(int $id): array {
        try {
            $stmt = $this->db->prepare(
                "UPDATE al_bourse_liste SET vigilance = 1 WHERE id = :id"
            );
            $stmt->execute(['id' => $id]);
            return ['success' => true, 'rows' => $stmt->rowCount()];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Écriture — prix
    // -------------------------------------------------------------------------

    /**
     * Met à jour le prix d'un jeu.
     * $type = 'vendu'  → valeur brute
     * $type = 'rendu'  → valeur convertie via prixRendu2Prix() avant stockage
     */
    public function updatePrix(int $id, int $valeur, string $type = 'vendu'): array {
        if ($type === 'rendu') {
            $prixAStocquer = self::prixRendu2Prix($valeur);
        } else {
            $prixAStocquer = $valeur;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE al_bourse_liste SET prix = :prix WHERE id = :id"
            );
            $stmt->execute(['prix' => $prixAStocquer, 'id' => $id]);
            return ['success' => true, 'prixStocke' => $prixAStocquer];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Écriture — suppression
    // -------------------------------------------------------------------------

    public function supprimer(int $id): array {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "DELETE FROM al_bourse_liste WHERE id = :id"
            );
            $stmt->execute(['id' => $id]);

            $this->logJournal($id, 1, 9); // 9 = statut "supprimé"

            $this->db->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Lecture — totaux remboursement
    // -------------------------------------------------------------------------

    /**
     * Retourne le total des prix rendus pour les jeux vendus d'un vendeur
     * (somme utilisée pour calculer ce qu'on lui doit).
     */
    public function getTotalVente(int $idVendeur): int {
        $stmt = $this->db->prepare(
            "SELECT SUM(rendu) AS rendu
             FROM v_bourse_liste
             WHERE id_utilisateur = :id
               AND id_statut = 3
               AND annee = :annee"
        );
        $stmt->execute(['id' => $idVendeur, 'annee' => $this->annee]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['rendu'] ?? 0);
    }

    /**
     * Retourne la somme déjà versée au vendeur (remboursements + dons).
     */
    public function getDejaRembourse(int $idVendeur): int {
        $total = 0;

        $stmt = $this->db->prepare(
            "SELECT SUM(montant_remb) AS remb
             FROM al_bourse_remboursements
             WHERE YEAR(date_remb) = :annee
               AND id_utilisateur  = :id"
        );
        $stmt->execute(['annee' => $this->annee, 'id' => $idVendeur]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total += (int) ($row['remb'] ?? 0);

        $stmt = $this->db->prepare(
            "SELECT SUM(montant_don) AS don
             FROM al_bourse_dons
             WHERE YEAR(date_don)   = :annee
               AND id_utilisateur   = :id"
        );
        $stmt->execute(['annee' => $this->annee, 'id' => $idVendeur]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total += (int) ($row['don'] ?? 0);

        return $total;
    }

    // -------------------------------------------------------------------------
    // Écriture — duplication pour festival (jeuxliste-updatefestival)
    // -------------------------------------------------------------------------

    /**
     * Duplique un jeu dans la liste de l'édition courante et marque l'original comme "donné".
     * L'original passe au statut 6 (don), la copie est créée avec statut 2 (en stock), vendeur = 1.
     */
    public function dupliquerPourFestival(int $id): array {
        try {
            $this->db->beginTransaction();

            // 1. Récupérer les infos de l'original
            $stmt = $this->db->prepare(
                "SELECT id, id_utilisateur, nom_jeu, prix AS vendu, code_barre, statut, vigilance
                 FROM al_bourse_liste
                 WHERE id = :id"
            );
            $stmt->execute(['id' => $id]);
            $original = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$original) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Jeu introuvable.'];
            }

            // 2. Insérer la copie (vendeur forcé à 1 = association)
            $stmtInsert = $this->db->prepare(
                "INSERT INTO al_bourse_liste
                     (id_utilisateur, nom_jeu, prix, code_barre, statut, vigilance, id_depot, date_reception, annee)
                 VALUES (:idVendeur, :nom_jeu, :vendu, :codebarre, 2, 0, :ip, :date_reception, :annee)"
            );
            $stmtInsert->execute([
                'idVendeur'       => 1,
                'nom_jeu'         => $original['nom_jeu'],
                'vendu'           => $original['vendu'],
                'codebarre'       => $original['code_barre'],
                'ip'              => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'date_reception'  => date('Y-m-d H:i:s'),
                'annee'           => $this->annee,
            ]);
            $newId = $this->db->lastInsertId();

            $this->logJournal((int) $newId, 11, 2);

            // 3. Marquer l'original comme donné (statut 6) + date_sortie_stock
            $stmtUpdate = $this->db->prepare(
                "UPDATE al_bourse_liste
                 SET statut = 6, date_sortie_stock = :don_le
                 WHERE id = :id"
            );
            $stmtUpdate->execute(['don_le' => date('Y-m-d H:i:s'), 'id' => $id]);

            $this->logJournal($id, 2, 6);

            $this->db->commit();
            return ['success' => true, 'new_id' => $newId];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Réception & Restitution (méthodes déjà existantes, conservées)
    // -------------------------------------------------------------------------

    public function ajouterJeuReception(string $codeBarre, string $nomJeu, int $prix, int $idVendeur, int $vigilance = 0): array {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO al_bourse_liste
                     (code_barre, nom_jeu, prix, id_utilisateur, statut, vigilance, date_reception, annee)
                 VALUES (:code_barre, :nom_jeu, :prix, :id_vendeur, 2, :vigilance, NOW(), :annee)"
            );
            $stmt->execute([
                'code_barre' => $codeBarre,
                'nom_jeu'    => $nomJeu,
                'prix'       => $prix,
                'id_vendeur' => $idVendeur,
                'vigilance'  => $vigilance,
                'annee'      => $this->annee,
            ]);
            return ['success' => true, 'id_jeu' => $this->db->lastInsertId()];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateJeuReception(string $codeBarre, int $prix, int $idVendeur, int $vigilance = 0): array {
        try {
            $stmt = $this->db->prepare(
                "UPDATE al_bourse_liste
                 SET statut = 2, prix = :prix, id_utilisateur = :id_vendeur,
                     vigilance = :vigilance, date_reception = NOW()
                 WHERE code_barre = :code_barre"
            );
            $stmt->execute([
                'prix'       => $prix,
                'id_vendeur' => $idVendeur,
                'vigilance'  => $vigilance,
                'code_barre' => $codeBarre,
            ]);
            return ['success' => true];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function marquerCommeRendu(string $codeBarre, int $idVendeur): array {
        try {
            $stmt = $this->db->prepare(
                "UPDATE al_bourse_liste
                 SET statut = 4, date_sortie_stock = NOW()
                 WHERE code_barre = :code_barre
                   AND id_utilisateur = :id_vendeur
                   AND statut = 2"
            );
            $stmt->execute(['code_barre' => $codeBarre, 'id_vendeur' => $idVendeur]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true];
            }
            return ['success' => false, 'message' => "Le jeu n'est pas en stock ou n'appartient pas à ce vendeur."];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Inline editing (déjà existants, conservés)
    // -------------------------------------------------------------------------

    public function updateCodeBarreInline(int $idJeu, string $codeBarre, int $statut = 2): array {
        if ($this->isCodeBarrePris($codeBarre, $idJeu)) {
            return ['success' => false, 'message' => 'Code déjà pris'];
        }
        $stmt = $this->db->prepare(
            "UPDATE al_bourse_liste SET code_barre = :code, statut = :statut WHERE id = :id"
        );
        $stmt->execute(['code' => $codeBarre, 'statut' => $statut, 'id' => $idJeu]);
        return ['success' => true];
    }

    /**
     * Met à jour le prix d'un jeu à la volée (Jeditable) en gérant le type de prix.
     */
    public function updatePrixInline(int $idJeu, int $nouveauPrix, string $type = 'vendu'): array {
        // Si c'est le prix que le vendeur reçoit, on doit recalculer le prix de vente brut pour la base
        if ($type === 'rendu') {
            $prixAStocker = self::prixRendu2Prix($nouveauPrix);
        } else {
            $prixAStocker = $nouveauPrix;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE al_bourse_liste SET prix = :prix WHERE id = :id"
            );
            $stmt->execute(['prix' => $prixAStocker, 'id' => $idJeu]);
            return ['success' => true, 'prixStocke' => $prixAStocker];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Privé — journal des statuts
    // -------------------------------------------------------------------------

    private function logJournal(int $idListe, int $oldStatut, int $newStatut): void {
        $stmt = $this->db->prepare(
            "INSERT INTO al_bourse_journal_statut (id_liste, old_id_statut, new_id_statut, ip, date)
             VALUES (:id_liste, :old_id_statut, :new_id_statut, :ip, :date)"
        );
        $stmt->execute([
            'id_liste'      => $idListe,
            'old_id_statut' => $oldStatut,
            'new_id_statut' => $newStatut,
            'ip'            => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'date'          => date('Y-m-d H:i:s'),
        ]);
    }
}