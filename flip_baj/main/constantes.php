<?php
namespace flip_baj\main;


date_default_timezone_set('Europe/Paris');
if (!defined('annee_base')) {
    define('annee_base', date("Y"));
    //define('annee_base', '2024');
}

// repertoires
$rep_root = "FLIPBAJ/flip_baj/main";
$rep_facture = "/pdf/pdf/facture";
$rep_justificatif = "/pdf/pdf/justificatif";

$id_sac = "-1";

// requetes SQL

/*Sélection des informations sur les vendeurs avec le nombre de jeux vendus, rendus, en stock, etc.
 Les résultats sont ordonnés par nom et prénom.*/
$SQL_1_selectionvendeur = 
'SELECT distinct
    al_bourse_users.id as idDuVendeur,
    al_bourse_users.nom as nom, 
    al_bourse_users.prenom as prenom,
    al_bourse_users.email as email,
    al_bourse_users.telephone as telephone,
    t.nbjeuxrendus,
    t.nbjeuxvendus,
    t.nbjeuxstock,
    t.nbjeuxdonnes,
    t.nbjeuxpasrecus
FROM al_bourse_users
    /* recup de la somme des jeux par type */
	LEFT JOIN (select v_bourse_liste.id_utilisateur as iduser,
                      /*ANY_VALUE (v_bourse_liste.id_statut),
                      v_bourse_liste.annee,
                      
                      Suppression de ces deux lignes
                      */
                      
			          SUM(CASE when v_bourse_liste.id_statut="4" then 1 else 0 end) as nbjeuxrendus,   /*statut 4 : rendu*/
			          SUM(CASE when v_bourse_liste.id_statut="3" then 1 else 0 end) as nbjeuxvendus,   /*statut 3 : vendu*/
 			          SUM(CASE when v_bourse_liste.id_statut="2" then 1 else 0 end) as nbjeuxstock,    /*statut 2 : en stock*/
 			          SUM(CASE when v_bourse_liste.id_statut="6" then 1 else 0 end) as nbjeuxdonnes,   /*statut 6 : donné*/
  			          SUM(CASE when v_bourse_liste.id_statut="1" then 1 else 0 end) as nbjeuxpasrecus  /*statut 1 : non reçu*/
  			   FROM v_bourse_liste
               WHERE v_bourse_liste.annee = '.annee_base.'
               GROUP BY v_bourse_liste.id_utilisateur
      		    ) as t on al_bourse_users.id=t.iduser
ORDER BY nom, prenom';

// Récupération des informations détaillées sur un vendeur en fonction de son ID
$SQL_2_getvendeur = 
'SELECT
    `id`                    as idDuVendeur,
    `nom`                   as nom,
    `prenom`                as prenom,
    `email`                 as email,
    `telephone`             as telephone,
    `adresse`               as adresse,
    `code_postal`           as code_postal,
    `ville`                 as ville,
    `denomination_sociale`  as denomination_sociale,
    `siege_social`          as siege_social,
    `attestation_signee`    as attestation_signee
FROM al_bourse_users
WHERE `id`=:idDuVendeur';

//Récupération des jeux en fonction d'un nom partiel donné
$SQL_3_getjeux = 
'SELECT 
   al_bourse_jeux.id  as id,
   al_bourse_jeux.nom as nom
FROM al_bourse_jeux 
WHERE nom LIKE :nom 
ORDER BY al_bourse_jeux.nom ASC';

//Vérification de la présence d'un code-barre spécifique dans la liste des jeux
$SQL_4_checkcodebarre = 
'SELECT 
    code_barre 
FROM v_bourse_liste 
WHERE code_barre = :code_barre
    AND annee ='.annee_base;

//Récupération de la liste des jeux vendus par un vendeur donné, avec des détails supplémentaires
$SQL_5_getlistejeux = 
'SELECT distinct 
    v_bourse_liste.id,
    v_bourse_liste.id_utilisateur,
    v_bourse_liste.nom,
    v_bourse_liste.prenom,
    v_bourse_liste.id_statut,
    v_bourse_liste.statut,
    CASE v_bourse_liste.vigilance WHEN 0 THEN "Non" ELSE "Oui" END as vigilance,
    v_bourse_liste.nom_jeu as nj,
    v_bourse_liste.vendu,
    (SELECT max(vendu) FROM v_bourse_liste WHERE nom_jeu = nj ORDER BY vendu asc) as maxprix, 
    (SELECT min(vendu) FROM v_bourse_liste WHERE nom_jeu = nj order by vendu asc) as minprix, 
    code_barre,
    date_reception, 
    date_sortie_stock
FROM v_bourse_liste 
WHERE ';
$SQL_5_1_whereVendeur   = ' v_bourse_liste.id_utilisateur = :idVendeur';
$SQL_5_2_whereStatut    = ' v_bourse_liste.id_statut = :idStatut';
$SQL_5_3_whereVigilance = ' v_bourse_liste.vigilance = :vigilance';
$SQL_5_4_whereCode      = ' v_bourse_liste.code_barre LIKE CONCAT("%",:code_barre,"%")';
$SQL_5_5_whereNom       = ' v_bourse_liste.nom_jeu LIKE CONCAT("%",:nom_jeu,"%")';
$SQL_5_6_whereAnnee     = ' v_bourse_liste.annee= '.annee_base;
$SQL_5_7_orderBy        = ' ORDER BY v_bourse_liste.nom_jeu';

$SQL_6_setvigilance = 
'UPDATE al_bourse_liste
SET vigilance=1 
WHERE id=:id';
//Mise à jour du prix d'un jeu spécifique
$SQL_7_updateprixjeu = 
'UPDATE al_bourse_liste
SET prix=:vendu 
WHERE id=:id';

// Récupération de la somme en caisse (Total transaction + don (qui ne sont pas des ventes) - remboursements)
$SQL_8_argentencaisse_get = 
"SELECT
    (
        SELECT COALESCE(SUM(montantTotal), 0)
        FROM al_bourse_transactions
        WHERE paiement = 'espèces'
        AND type IN ('vente', 'gestion')
        AND YEAR(date) = ".annee_base."
    )
    -
    (
        SELECT COALESCE(SUM(montant_remb), 0)
        FROM al_bourse_remboursements
        WHERE YEAR(date_remb) = ".annee_base."
        AND type_remb = 'espèces'
    ) AS Total";

$SQL_9_acheteurget = 
"SELECT * FROM al_bourse_acheteur WHERE id = :acheteurId";

//Insertion d'un nouveau jeu dans la table des jeux
$SQL_10_insertjeu = 
'INSERT INTO al_bourse_jeux (nom) 
VALUES (:nom_jeu)';

//Insertion d'une nouvelle entrée dans la liste des jeux    
$SQL_11_insertlistejeu = 
'INSERT INTO al_bourse_liste (id_utilisateur, nom_jeu, prix, code_barre, statut, vigilance, id_depot, date_reception, annee) 
 VALUES (:idVendeurEdition,:nom_jeu,:vendu,:codebarre,:statut,:vigilance,:ip,:date_reception,:annee)';

//Suppression d'un jeu de la liste des jeux
$SQL_12_dellistejeu = 
'DELETE 
FROM al_bourse_liste 
WHERE id=:id';

//Mise à jour du statut et du code-barre d'un jeu spécifique dans la liste 
//Utile pour la réception d'un jeu déjà enregistré
$SQL_13_updatestatutlistejeu =
'UPDATE al_bourse_liste
SET statut = :statut';
$SQL_13_01_code         =', code_barre=:code_barre';
$SQL_13_02_iddepot      =', id_depot=:id_depot, date_reception= :date_reception';
$SQL_13_04_datesortie   =', date_sortie_stock= :date_sortie';
$SQL_13_05_whereid      =' WHERE id=:id';

$SQL_14_pdfadd = "INSERT INTO `al_bourse_pdf`(`id_utilisateur`, `nom_fichier`, `type`, `annee`) 
                                        VALUES (:id_utilisateur,:nom_fichier,:type,".annee_base.")";
//Récupération des informations sur un exemplaire en fonction de son ID
//Utilisé pour la gestion des dons

$SQL_15_01_get_infos_exemplaire = 
"SELECT
`id`,
`id_utilisateur`,
`nom_jeu`,
`prix` as vendu,
`code_barre`,
`statut`,
`vigilance`,
`date_reception`,
`annee`
FROM
`al_bourse_liste`
WHERE
`id` = :id";

//Mise à jour du statut de l'exemplaire pour indiquer qu'il a été donné + cloture
$SQL_15_02_update_liste =
"UPDATE
    `al_bourse_liste`
SET
    `statut` = '6',
    `date_sortie_stock` = :don_le
WHERE
    `id` = :id";

$SQL_16_getminpricelistejeux = 
'SELECT 
    min(vendu) as vendu 
FROM v_bourse_liste 
WHERE nom_jeu = :nom_jeu 
ORDER BY vendu asc';

//Calcul de l'argent déjà payé par un utilisateur en termes de dons et de remboursements
//Utilisé sur la page des stats
$SQL_17A_getargentdejapaye = 
'SELECT
        id_utilisateur,
        SUM(montant_remb) AS remb
    FROM
        al_bourse_remboursements
    WHERE 
        YEAR(al_bourse_remboursements.date_remb)='.annee_base.'
        AND al_bourse_remboursements.id_utilisateur = :id';
$SQL_17B_getargentdejapaye = 
    'SELECT
        id_utilisateur,
        SUM(montant_don) AS don
    FROM
        al_bourse_dons
    WHERE
        YEAR(al_bourse_dons.date_don)='.annee_base.'
    AND
    al_bourse_dons.id_utilisateur = :id';

$SQL_18_getlistejeuxtotalvente = 
'SELECT 
    SUM(rendu) as rendu 
FROM v_bourse_liste 
WHERE id_utilisateur= :id 
    AND id_statut = 3 
    AND annee= :annee';

//Ajout d'un don dans la table des dons
$SQL_19_donadd = 
"INSERT INTO al_bourse_dons(
    id_utilisateur,
    montant_don,
    date_don,
    type_don
)
VALUES(
    :idDuVendeur,
    :montant,
    :date,
    :type
)";

//Récupération des détails d'un vendeur via son adresse e-mail
$SQL_20_getvendeurbyemail = 
'SELECT 
    `id`                    as idDuVendeur,
    `nom`                   as nom,
    `prenom`                as prenom,
    `email`                 as email,
    `telephone`             as telephone,
    `adresse`               as adresse,
    `code_postal`           as code_postal,
    `ville`                 as ville,
    `denomination_sociale`  as denomination_sociale,
    `siege_social`          as siege_social,
    `attestation_signee`    as attestation_signee
FROM al_bourse_users 
WHERE email=:email';

$SQL_20_01_getvendeurbyemail = 
'SELECT 
    id,
    email 
FROM al_bourse_users 
WHERE email LIKE :email';

$SQL_20_02_getvendeurbynom = 
'SELECT 
    id,
    nom 
FROM al_bourse_users 
WHERE nom LIKE :nom';

$SQL_21_getlistejeuxstock = 
'SELECT distinct 
    v_bourse_liste.id, 
    v_bourse_liste.id_utilisateur, 
    v_bourse_liste.nom, 
    v_bourse_liste.prenom, 
    v_bourse_liste.nom_jeu as nj, 
    v_bourse_liste.vendu as Vendu, 
    (SELECT max(vendu) 
        FROM v_bourse_liste 
        WHERE nom_jeu = nj 
        ORDER BY vendu asc) as maxprix, 
    (SELECT min(vendu) 
        FROM v_bourse_liste 
        WHERE nom_jeu = nj 
        ORDER BY vendu asc) as minprix, 
    code_barre,
    id_statut,
    statut,
    date_reception, 
    date_sortie_stock as date_restitution, 
    a1.id_transaction as idtransaction, 
    a2.date as datedevente 
FROM (v_bourse_liste 
        LEFT JOIN al_bourse_transaction_liste as a1 
        ON v_bourse_liste.id=a1.id_bourse_liste) 
    LEFT JOIN v_bourse_transactions as a2 
    ON a2.id=a1.id_transaction 
WHERE   v_bourse_liste.id_statut = :statut 
    AND v_bourse_liste.annee= :annee 
ORDER BY nj';

$SQL_21_getlistejeuxstockspeed = 
'SELECT distinct 
    v_bourse_liste.id, 
    v_bourse_liste.id_utilisateur, 
    v_bourse_liste.nom, 
    v_bourse_liste.prenom , 
    v_bourse_liste.nom_jeu as nj, 
    v_bourse_liste.vendu as Vendu, 
    code_barre,
    id_statut,
    statut,
    date_reception, 
    date_sortie_stock as date_restitution 
FROM v_bourse_liste 
WHERE v_bourse_liste.id_statut =:statut 
    AND v_bourse_liste.annee=:annee 
ORDER BY nj';

//Ajout d'une transaction avec ou sans acheteur
$SQL_22_transactionadd =
'INSERT INTO al_bourse_transactions(
    type,
    montantTotal,
    montantPercu,
    montantRendu,
    paiement,
    date,
    ip,
    id_acheteur
)
VALUES(
    :type,
    :montantTotal,
    :montantPercu,
    :montantRendu,
    :paiement,
    :date,
    :ip,
    :id_acheteur
)';

$SQL_23_transactionUpdate = 
"UPDATE
    `al_bourse_transactions`
SET
    `type`         = :type,
    `montantTotal` = :montantTotal,
    `montantPercu` = :montantPercu,
    `montantRendu` = :montantRendu,
    `montantDon`   = :montantDon,
    `paiement`     = :paiement,
    `date`         = :date,
    `ip`           = :ip
WHERE
    `id` = :id_transaction";

$SQL_24_transactionlisteadd = 
'INSERT INTO al_bourse_transaction_liste (id_transaction, id_bourse_liste) 
    VALUES (:id_transaction, :id_bourse_liste)';

$SQL_24B_transactionlistedel = 
"DELETE
FROM
    al_bourse_transaction_liste
WHERE
    id IN(
    SELECT
        id
    FROM
        al_bourse_transaction_liste
    WHERE
        al_bourse_transaction_liste.id_transaction = :id_transaction
     AND 
        al_bourse_transaction_liste.id_bourse_liste = :id_bourse_liste
    LIMIT 1
)";

$SQL_25_getlistevente = "
SELECT 
    t.id AS id_transaction,
    t.montantTotal,
    t.montantPercu,
    t.montantRendu,
    t.paiement,
    t.date,
    a.nom,
    a.prenom,
    a.email,
    a.id AS id_acheteur,
    (
        SELECT COUNT(*) 
        FROM al_bourse_transaction_liste tl2
        JOIN al_bourse_liste bl2 ON tl2.id_bourse_liste = bl2.id
        WHERE tl2.id_transaction = t.id
        AND bl2.statut = 3
    ) AS nbjeux,
    GROUP_CONCAT(DISTINCT CASE WHEN bl.statut = 3 THEN bl.nom_jeu ELSE NULL END ORDER BY bl.nom_jeu SEPARATOR ', ') AS jeux
FROM 
    al_bourse_transactions t
LEFT JOIN 
    al_bourse_acheteur a ON t.id_acheteur = a.id
LEFT JOIN 
    al_bourse_transaction_liste tl ON t.id = tl.id_transaction
LEFT JOIN 
    al_bourse_liste bl ON tl.id_bourse_liste = bl.id
WHERE 
    t.type = 'vente'
    AND YEAR(t.date) = " . annee_base . "
GROUP BY 
    t.id,
    t.montantTotal,
    t.montantPercu,
    t.montantRendu,
    t.paiement,
    t.date,
    a.nom,
    a.prenom,
    a.email,
    a.id
ORDER BY 
    t.date DESC
";







//Récupération des informations sur les jeux selon leur statut pour une année spécifique
//Utilisée dans les stats
$SQL_26_getInfosJeux =
"SELECT
    COUNT(
        CASE WHEN `id_statut` = 1 THEN 1
    END
) AS nbEnregistres,
COUNT(
    CASE WHEN `id_statut` = 2 THEN 1
END
) AS nbEnStock,
COUNT(
    CASE WHEN `id_statut` = 3 THEN 1
END
) AS nbVendu,
COUNT(
    CASE WHEN `id_statut` = 6 THEN 1
END
) AS nbDonnes,
SUM(
    CASE WHEN `id_statut` = 2 THEN `vendu`
END
) AS ResteAVendre,
SUM(
    CASE WHEN `id_statut` = 3 THEN `rendu`
END
) AS TotalARendre,
SUM(
    CASE WHEN `id_statut` = 3 THEN (`vendu`-`rendu`)
END
) AS CommissionTTC,
SUM(
    CASE WHEN `id_statut` = 3 THEN ROUND((`vendu`-`rendu`) * 5 / 6 , 2)
END
) AS CommissionHT,(
        SELECT COALESCE(SUM(montant_don), 0)
        FROM al_bourse_dons
        WHERE type_don = 'Non remboursement'
        AND YEAR(date_don) = :annee
    ) AS totalDonsNonRemb
FROM
    `v_bourse_liste`
WHERE 
    annee= :annee";

//Récupération des informations sur les ventes selon le moyen de paiement pour une année spécifique
//Utilisée dans les stats
$SQL_27_getInfosVentes =
"SELECT
    SUM(CASE WHEN paiement = 'cb' THEN montantTotal ELSE 0 END) AS TotalCB,
    SUM(CASE WHEN paiement = 'espèces' THEN montantTotal ELSE 0 END) AS TotalEspeces,
    SUM(CASE WHEN paiement = 'chèque' THEN montantTotal ELSE 0 END) AS TotalCheque,
    (
        SELECT COUNT(*)
        FROM al_bourse_transaction_liste AS l
        JOIN al_bourse_transactions AS t ON l.id_transaction = t.id
        WHERE l.id_bourse_liste = -1
        AND t.type = 'vente'
        AND YEAR(t.date) = :annee
    ) AS nbSacs
FROM
    al_bourse_transactions
WHERE
    type = 'vente' AND YEAR(date) = :annee";

//Récupération des informations sur les remboursements selon le type pour une année spécifique
//Utilisée dans les stats
$SQL_28_getInfosRemb =
"SELECT
    SUM(
        CASE WHEN `type_remb` = 'espèces' THEN `montant_remb`
    END
) AS rembEspeces,
SUM(
    CASE WHEN `type_remb` = 'chèque' THEN `montant_remb`
END
) AS rembCheque,
SUM(
    CASE WHEN `type_remb` = 'paypal' THEN `montant_remb`
END
) AS rembPaypal,
SUM(`montant_remb`) AS totalRemb
FROM
    `al_bourse_remboursements`
WHERE
    YEAR(`date_remb`) = :annee";

//Récupération du total des dons pour une année spécifique
$SQL_29_getInfosDons = 
"SELECT
    SUM(`montant_don`) AS totalDons
FROM
    `al_bourse_dons`
WHERE
    YEAR(`date_don`) = :annee";

$SQL_30_journalStatut = 
"INSERT INTO al_bourse_journal_statut (id_liste, old_id_statut, new_id_statut, ip, date)
                             VALUES (:id_liste, :old_id_statut, :new_id_statut, :ip, :date)";

$SQL_31_getacheteur = 
"SELECT * FROM al_bourse_acheteur WHERE";
$SQL_31_byemail = " email LIKE :email";
$SQL_31_bynom = " nom LIKE :nom";
$SQL_31_byprenom = " prenom LIKE :prenom";

$SQL_32_acheteuradd = 
"INSERT INTO `al_bourse_acheteur`(`nom`, `prenom`, `email`, `adresse`, `code_postal`, `ville`, `raison_sociale`, `siret`) 
                            VALUES (:nom, :prenom, :email, :adresse, :code_postal, :ville, :raison_sociale, :siret)";

$SQL_33_acheteurupdate = 
"UPDATE
    `al_bourse_acheteur`
SET
    `nom` = :nom,
    `prenom` = :prenom,
    `email` = :email,
    `adresse` = :adresse,
    `code_postal` = :code_postal,
    `ville` = :ville,
    `raison_sociale` = :raison_sociale,
    `siret` = :siret
WHERE
    `id`= :id_acheteur";

$SQL_34_acheteurassoc = 
"UPDATE
    `al_bourse_transactions`
SET
    `id_acheteur` = :id_acheteur
WHERE
    `id` = :id_transaction";

$SQL_36_remboursement_add = 
'INSERT INTO al_bourse_remboursements (id_utilisateur, montant_remb, date_remb, type_remb) 
    VALUES (:id_utilisateur, :montant_remb, :date_remb, :type_remb)';

$SQL_37_user_add = 
"INSERT into al_bourse_users (nom, prenom, telephone, email, adresse, code_postal, ville, denomination_sociale, siege_social, attestation_signee) 
    VALUES (:nom, :prenom, :telephone, :email, :adresse, :code_postal, :ville, :denomination_sociale, :siege_social, :attestation_signee)";

$SQL_37_01_al_bourse_users_update =
"UPDATE al_bourse_users 
    SET nom= :nom, 
        prenom= :prenom, 
        email= :email, 
        telephone= :telephone, 
        adresse= :adresse, 
        code_postal= :code_postal, 
        ville= :ville, 
        denomination_sociale= :denomination_sociale, 
        siege_social= :siege_social, 
        attestation_signee= :attestation_signee
WHERE id= :id_vendeur";

$SQL_38_unser_name_prenom_checker = 
"SELECT nom, prenom, telephone
 FROM al_bourse_users
 WHERE nom = :nom 
   AND prenom = :prenom
   AND telephone = :telephone
   AND id != :id";


$SQL_39_creation_acheteur = 
"INSERT INTO al_bourse_users (nom, prenom, email, adresse, code_postal, ville)
                VALUES (:nom, :prenom, :email, :adresse, :code_postal, :ville)";

$SQL_40_modification_acheteur =
"UPDATE al_bourse_users
SET nom = ':nom',
    prenom = ':prenom',
    email = ':email',
    adresse = ':adresse',
    code_postal = ':code_postal',
    ville = ':ville',
WHERE id = :id_acheteur";

$SQL_41_attribution_acheteur_transaction = 
"UPDATE al_bourse_transactions
        SET id_acheteur= :idAcheteur
        WHERE id= :id_transaction";

$SQL_42_check_doublon_nom = 
"SELECT
  nom
FROM al_bourse_users
WHERE nom= :nom";

$SQL_43_getTrans =
"SELECT
    `montantTotal` as montant_total,
    `date`
FROM
    `al_bourse_transactions`
WHERE
    `type` = :type 
    AND YEAR(`date`) =".annee_base;

$SQL_44_getlistejeuxtransaction =
"SELECT
    v_bourse_liste.code_barre AS code_barre,
    v_bourse_liste.nom_jeu AS nom_jeu,
    v_bourse_liste.vendu AS vendu,
    al_bourse_transactions.id AS id_transaction,
    DATE_FORMAT(al_bourse_transactions.date,'%d/%m/%Y %T') AS date_paiement,
    al_bourse_transactions.paiement AS type_paiement
FROM
            al_bourse_transactions
    INNER JOIN
            al_bourse_transaction_liste 
        ON al_bourse_transactions.id = al_bourse_transaction_liste.id_transaction
    INNER JOIN
            v_bourse_liste
        ON v_bourse_liste.id = al_bourse_transaction_liste.id_bourse_liste
WHERE
al_bourse_transactions.id_acheteur = :id_acheteur
AND al_bourse_transactions.type = 'vente'
AND YEAR(al_bourse_transactions.date) =".annee_base;

$SQL_45_get_infos_acheteur = //Pour créer le justificatif
"SELECT
        al_bourse_acheteur.prenom,
        al_bourse_acheteur.nom,
        al_bourse_acheteur.adresse,
        al_bourse_acheteur.code_postal,
        al_bourse_acheteur.ville,
        al_bourse_acheteur.raison_sociale,
        al_bourse_acheteur.siret
FROM
        al_bourse_acheteur
WHERE
    al_bourse_acheteur.id = :id_acheteur";

$SQL_46_get_remboursement =
"SELECT
    `montant_remb`,
    `date_remb`,
    `type_remb`
FROM
    `al_bourse_remboursements`
WHERE
    `id_utilisateur` = :id
    AND YEAR(`date_remb`) = ".annee_base;

$SQL_47_get_dons =
"SELECT
    `montant_don`,
    `date_don`,
    `type_don`
FROM
    `al_bourse_dons`
WHERE
    `id_utilisateur` = :id 
    AND `type_don` = :type
    AND YEAR(`date_don`) = ".annee_base;

//Les requêtes suivantes permettent de réaliser les stats de fin de festival
$SQL_48_get_stats_jeux_byHour = 
"SELECT
    DATE_FORMAT(`Creneau`, '%w %H:00') AS `Creneau`,
    `NB_Jeux_receptionnes`,
    `Montant_Jeux_receptionnes`,
    `NB_Jeux_vendus`,
    `Montant_Jeux_vendus`,
    `NB_Jeux_rendus`,
    `Montant_Jeux_rendus`,
    `NB_Jeux_donnes`,
    `Montant_Jeux_donnes`,
    `NB_Jeux_stock`,
    `Montant_Jeux_stock`
FROM
    `v_bourse_jeux_H_by_H`
WHERE
    NOT ISNULL(Creneau)
  AND
    date(`Creneau`)>=date(:date_debut)
  AND
    date(`Creneau`)<=date(:date_fin)";

$SQL_48B_get_stats_jeux_byDay =
"SELECT
    DATE_FORMAT(`Creneau`, '%d / %m') AS Creneau,
    MAX(`NB_Jeux_stock`) AS NB_Jeux_stock,
    MAX(`Montant_Jeux_stock`) AS Montant_Jeux_stock
FROM
    `v_bourse_jeux_H_by_H`
WHERE
    date(`Creneau`)>=date(:date_debut)
  AND
    date(`Creneau`)<=date(:date_fin)
GROUP BY DATE_FORMAT(`Creneau`, '%d / %m')";

$SQL_49_get_stats_trans_byHour = 
"SELECT
    DATE_FORMAT(DATE, '%w %H:00') AS Creneau,
    COUNT(*) AS NB_transaction,
    SUM(montantTotal) AS Montant_transaction_total,
    SUM(montantPercu) AS Montant_transaction_percu,
    SUM(montantRendu) AS Montant_transaction_rendu,
    SUM(
        CASE WHEN paiement = 'CB' THEN 1 ELSE 0
    END
) AS NB_transaction_CB,
SUM(
    CASE WHEN paiement = 'CB' THEN montantTotal ELSE 0
END
) AS Montant_transaction_total_CB,
SUM(
    CASE WHEN paiement = 'espèces' THEN 1 ELSE 0
END
) AS NB_transaction_ES,
SUM(
    CASE WHEN paiement = 'espèces' THEN montantTotal ELSE 0
END
) AS Montant_transaction_total_ES,
SUM(
    CASE WHEN paiement = 'cheque' THEN 1 ELSE 0
END
) AS NB_transaction_CH,
SUM(
    CASE WHEN paiement = 'cheque' THEN montantTotal ELSE 0
END
) AS Montant_transaction_total_CH
FROM
    al_bourse_transactions
WHERE
    YEAR(DATE) = YEAR(CURRENT_DATE())
GROUP BY
    DATE_FORMAT(DATE, '%w %H:00')";

$SQL_50_get_stats_vendeurs = 
"SELECT 
    AVG(prix) AS montant_moyen_par_vendeur,
    AVG(benefice) AS benefice_moyen_par_vendeur,
    MAX(prix) AS montant_max_par_vendeur,
    MAX(benefice) AS benefice_max_par_vendeur,
    AVG(nombre_de_jeux) AS moyenne_nombre_jeux_par_vendeur,
    MAX(nombre_de_jeux) AS max_nombre_jeux_par_vendeur
FROM (
    SELECT 
        id_utilisateur, 
        COUNT(*) AS nombre_de_jeux,
        SUM(prix) AS prix,
        SUM(prix- CEIL(prix/6)) AS benefice
    FROM al_bourse_liste
    WHERE statut = 3 AND annee = YEAR(CURRENT_DATE()) AND id_utilisateur != 0
    GROUP BY id_utilisateur
) AS jeux_par_vendeur";

$SQL_51_get_stats_duree_stock = 
"SELECT 
    CONCAT(
        FLOOR(MIN(duree_stock) / (24 * 60)), ' jours ',
        FLOOR((MIN(duree_stock) % (24 * 60)) / 60), ' heures ',
        CEIL(MIN(duree_stock) % 60), ' minutes'
    ) AS duree_min_stock,
    CONCAT(
        FLOOR(MAX(duree_stock) / (24 * 60)), ' jours ',
        FLOOR((MAX(duree_stock) % (24 * 60)) / 60), ' heures ',
        CEIL(MAX(duree_stock) % 60), ' minutes'
    ) AS duree_max_stock,
    CONCAT(
        FLOOR(AVG(duree_stock) / (24 * 60)), ' jours ',
        FLOOR((AVG(duree_stock) % (24 * 60)) / 60), ' heures ',
        CEIL(AVG(duree_stock) % 60), ' minutes'
    ) AS duree_moyenne_stock
FROM (
    SELECT TIMESTAMPDIFF(MINUTE, CASE WHEN date_reception < :date_debut OR ISNULL(date_reception) THEN :date_debut ELSE date_reception END, 
                                CASE WHEN date_sortie_stock > :date_fin OR ISNULL(date_sortie_stock) THEN :date_fin ELSE date_sortie_stock END) AS duree_stock
    FROM al_bourse_liste
    WHERE annee = YEAR(CURRENT_DATE())
        AND statut IN (2,3,4,6,8)
) AS duree_stock";

$SQL_52_get_stats_trans_fin = 
"SELECT 
    AVG(nombre_de_jeux) AS moyenne_nombre_jeux_par_transaction,
    AVG(prix) AS montant_moyen_transaction,
    SUM(nombre_sacs_achetes) AS nombre_sacs_achetes,
    MAX(prix) AS montant_plus_grande_transaction,
    MAX(nombre_de_jeux) AS max_jeux_transaction
FROM (
    SELECT 
        id_transaction, 
        COUNT(*) AS nombre_de_jeux,
        SUM(CASE WHEN id_bourse_liste = '-1' THEN 1 ELSE 0 END) AS nombre_sacs_achetes,
        SUM(prix) AS prix
    FROM al_bourse_transaction_liste
    LEFT JOIN al_bourse_transactions ON al_bourse_transaction_liste.id_transaction = al_bourse_transactions.id
    LEFT JOIN al_bourse_liste ON al_bourse_transaction_liste.id_bourse_liste = al_bourse_liste.id
    WHERE YEAR(al_bourse_transactions.date) = YEAR(CURRENT_DATE) AND al_bourse_transactions.type = 'vente'
    GROUP BY id_transaction
) AS jeux_par_transaction";

$SQL_53_get_stats_jeux_fin = 
"SELECT
    SUM(
        CASE WHEN `statut` IN(2, 3, 4, 8) THEN 1 ELSE 0
    END
) AS total_reception,
SUM(
    CASE WHEN `statut` IN(2, 3, 4, 8) AND DATE(date_reception) < DATE(:date_debut) THEN 1 ELSE 0
END
) AS total_pre_depot,
SUM(
    CASE WHEN `statut` = 3 THEN 1 ELSE 0
END
) AS total_vente,
SUM(
    CASE WHEN `statut` = 6 THEN prix ELSE 0
END
) AS montant_don,
SUM(
    CASE WHEN `statut` = 3 THEN prix ELSE 0
END
) AS montant_vente
FROM
    al_bourse_liste
WHERE
    annee = YEAR(CURRENT_DATE())";


//Traitement des données de la base de donnée afin de récupérer les status des jeux et les afficher dans un menu deroulant 
$sql_54_GET_LISTE_STATUS = "SELECT id, value, count(*) AS nbr_status FROM al_bourse_statuts_jeux GROUP BY id, value";

//Recuperation de tout les acheteurs enregistrer dans la base de donnee 
$SQL_55_selectionacheteur = "SELECT distinct * FROM  al_bourse_acheteur ";
//Requete qui change le statut
$SQL_56_ChangeStatut = "UPDATE al_bourse_liste bl join al_bourse_statuts_jeux bj on bl.statut = bj.id SET bl.statut = 7 where bl.id_utilisateur = :jeuId and bl.statut = 3";

$SQL_57_selectionAcheteurs = "SELECT * FROM `al_bourse_acheteur` "

?>

