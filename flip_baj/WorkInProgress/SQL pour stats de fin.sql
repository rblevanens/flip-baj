SELECT al_bourse_transaction_liste.id_transaction, SUM(prix), al_bourse_transactions.montantTotal, SUM(prix) - al_bourse_transactions.montantTotal AS diff FROM `al_bourse_transaction_liste` LEFT JOIN al_bourse_liste ON al_bourse_transaction_liste.id_bourse_liste = al_bourse_liste.id LEFT JOIN al_bourse_transactions ON al_bourse_transaction_liste.id_transaction=al_bourse_transactions.id WHERE al_bourse_transaction_liste.id_transaction>2007 GROUP BY al_bourse_transaction_liste.id_transaction;

/* Est-ce que tu pourrais me fournir le code qui recupererais toutes les stats suivantes sous forme d un seul fichier CSV :
- le nombre de jeux receptionnes h/h + montant correspondant --OK
- le nombre de jeux rendus h/h + montant correspondant --OK
- le nombre de jeux donnes h/h + montant correspondant --OK
- le nombre de jeux en stock h/h + montant correspondant --OK
- le nombre de jeux vendus h/h --OK
- le nombre de vendeur ayant depose au moins 1 jeu durant l heure h/h -- NON
- la repartition des transactions h/h, montant (+ nb_transaction) par moyen de paiement et total  --OK
- la moyenne du nombre de jeux vendus par vendeur (à la fin)  --OK
- le benefice et le montant moyen vendus par vendeur (à la fin)  --OK
- le benefice et le montant maximal vendus par vendeur (à la fin)  --OK
- le plus grand nombre de jeux vendus par le meme vendeur (à la fin)  --OK
- le nombre moyen de jeux par transactions (à la fin) --OK
- le montant moyen des transactions (à la fin) --OK
- le nombre de sacs achetes (à la fin) --OK
- le montant de la plus grande transaction (à la fin) --OK
- duree min max et moyenne de presences en stock (à la fin) --OK
*/
-- Requête jeux H/H :
-- Pour faire un FULL JOIN en mysql, on est obligé de faire l'union d'un right et d'un left join
SELECT
   COALESCE

   (
      `d`.`Creneau`,
      `r`.`Creneau`
   ) AS `Creneau`,
   COALESCE

   (
      `r`.`NB_Jeux_receptionnes`,
      0
   ) AS `NB_Jeux_receptionnes`,
   COALESCE

   (
      `r`.`Montant_Jeux_receptionnes`,
      0
   ) AS `Montant_Jeux_receptionnes`,
   COALESCE

   (
      `d`.`NB_Jeux_vendus`,
      0
   ) AS `NB_Jeux_vendus`,
   COALESCE

   (
      `d`.`Montant_Jeux_vendus`,
      0
   ) AS `Montant_Jeux_vendus`,
   COALESCE

   (
      `d`.`NB_Jeux_rendus`,
      0
   ) AS `NB_Jeux_rendus`,
   COALESCE

   (
      `d`.`Montant_Jeux_rendus`,
      0
   ) AS `Montant_Jeux_rendus`,
   COALESCE

   (
      `d`.`NB_Jeux_donnes`,
      0
   ) AS `NB_Jeux_donnes`,
   COALESCE

   (
      `d`.`Montant_Jeux_donnes`,
      0
   ) AS `Montant_Jeux_donnes`,

   (
      SUM
      (
         COALESCE
         (
            `r`.`NB_Jeux_receptionnes`,
            0
         )
      )
      OVER
      (
         ORDER BY
            COALESCE
         (
            `d`.`Creneau`,
            `r`.`Creneau`
         )
      )

      (
         (
            SUM
            (
               COALESCE
               (
                  `d`.`NB_Jeux_vendus`,
                  0
               )
            )
            OVER
            (
               ORDER BY
                  COALESCE
               (
                  `d`.`Creneau`,
                  `r`.`Creneau`
               )
            )
            + SUM
            (
               COALESCE
               (
                  `d`.`NB_Jeux_rendus`,
                  0
               )
            )
            OVER
            (
               ORDER BY
                  COALESCE
               (
                  `d`.`Creneau`,
                  `r`.`Creneau`
               )
            )
         )
         + SUM
         (
            COALESCE
            (
               `d`.`NB_Jeux_donnes`,
               0
            )
         )
         OVER
         (
            ORDER BY
               COALESCE
            (
               `d`.`Creneau`,
               `r`.`Creneau`
            )
         )
      )
   ) AS `NB_Jeux_stock`,
   -- On prend les cumulés de réception - les cumulés des ventes / restitutions / dons

   (
      SUM
      (
         COALESCE
         (
            `r`.`Montant_Jeux_receptionnes`,
            0
         )
      )
      OVER
      (
         ORDER BY
            COALESCE
         (
            `d`.`Creneau`,
            `r`.`Creneau`
         )
      )

      (
         (
            SUM
            (
               COALESCE
               (
                  `d`.`Montant_Jeux_vendus`,
                  0
               )
            )
            OVER
            (
               ORDER BY
                  COALESCE
               (
                  `d`.`Creneau`,
                  `r`.`Creneau`
               )
            )
            + SUM
            (
               COALESCE
               (
                  `d`.`Montant_Jeux_rendus`,
                  0
               )
            )
            OVER
            (
               ORDER BY
                  COALESCE
               (
                  `d`.`Creneau`,
                  `r`.`Creneau`
               )
            )
         )
         + SUM
         (
            COALESCE
            (
               `d`.`Montant_Jeux_donnes`,
               0
            )
         )
         OVER
         (
            ORDER BY
               COALESCE
            (
               `d`.`Creneau`,
               `r`.`Creneau`
            )
         )
      )
   ) AS `Montant_Jeux_stock`
   -- On prend les cumulés de réception - les cumulés des ventes / restitutions / dons
FROM
(
   (
      SELECT
         DATE_FORMAT

         (
            `Baj`.`al_bourse_liste`.`date_sortie_stock`,
            '%Y-%m-%d %H:00'
         ) AS `Creneau`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 3
               )
                  THEN 1 ELSE
               0 END
            )
         ) AS `NB_Jeux_vendus`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 3
               )
                  THEN `Baj`.`al_bourse_liste`.`prix` ELSE
               0 END
            )
         ) AS `Montant_Jeux_vendus`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 4
               )
                  THEN 1 ELSE
               0 END
            )
         ) AS `NB_Jeux_rendus`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 4
               )
                  THEN `Baj`.`al_bourse_liste`.`prix` ELSE
               0 END
            )
         ) AS `Montant_Jeux_rendus`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 6
               )
                  THEN 1 ELSE
               0 END
            )
         ) AS `NB_Jeux_donnes`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 6
               )
                  THEN `Baj`.`al_bourse_liste`.`prix` ELSE
               0 END
            )
         ) AS `Montant_Jeux_donnes`
      FROM
         `Baj`.`al_bourse_liste`
      WHERE
      (
         `Baj`.`al_bourse_liste`.`annee` = YEAR (CURDATE ())
      )
      GROUP BY
         DATE_FORMAT
      (
         `Baj`.`al_bourse_liste`.`date_sortie_stock`,
         '%Y-%m-%d %H:00'
      )
   )
   `d`
   LEFT
   JOIN
   (
      SELECT
         DATE_FORMAT

         (
            `Baj`.`al_bourse_liste`.`date_reception`,
            '%Y-%m-%d %H:00'
         ) AS `Creneau`,
      COUNT (0) AS `NB_Jeux_receptionnes`,
      SUM (`Baj`.`al_bourse_liste`.`prix`) AS `Montant_Jeux_receptionnes`
      FROM
         `Baj`.`al_bourse_liste`
      WHERE
      (
         (
            `Baj`.`al_bourse_liste`.`statut` NOT IN
            (
               1,
               9,
               11
            )
         )
         AND
         (
            `Baj`.`al_bourse_liste`.`annee` = YEAR (CURDATE ())
         )
      )
      GROUP BY
         DATE_FORMAT
      (
         `Baj`.`al_bourse_liste`.`date_reception`,
         '%Y-%m-%d %H:00'
      )
   )
   `r` ON
   (
      (
         `d`.`Creneau` = `r`.`Creneau`
      )
   )
)
UNION SELECT
   COALESCE

   (
      `d`.`Creneau`,
      `r`.`Creneau`
   ) AS `Creneau`,
   COALESCE

   (
      `r`.`NB_Jeux_receptionnes`,
      0
   ) AS `NB_Jeux_receptionnes`,
   COALESCE

   (
      `r`.`Montant_Jeux_receptionnes`,
      0
   ) AS `Montant_Jeux_receptionnes`,
   COALESCE

   (
      `d`.`NB_Jeux_vendus`,
      0
   ) AS `NB_Jeux_vendus`,
   COALESCE

   (
      `d`.`Montant_Jeux_vendus`,
      0
   ) AS `Montant_Jeux_vendus`,
   COALESCE

   (
      `d`.`NB_Jeux_rendus`,
      0
   ) AS `NB_Jeux_rendus`,
   COALESCE

   (
      `d`.`Montant_Jeux_rendus`,
      0
   ) AS `Montant_Jeux_rendus`,
   COALESCE

   (
      `d`.`NB_Jeux_donnes`,
      0
   ) AS `NB_Jeux_donnes`,
   COALESCE

   (
      `d`.`Montant_Jeux_donnes`,
      0
   ) AS `Montant_Jeux_donnes`,

   (
      SUM
      (
         COALESCE
         (
            `r`.`NB_Jeux_receptionnes`,
            0
         )
      )
      OVER
      (
         ORDER BY
            COALESCE
         (
            `d`.`Creneau`,
            `r`.`Creneau`
         )
      )

      (
         (
            SUM
            (
               COALESCE
               (
                  `d`.`NB_Jeux_vendus`,
                  0
               )
            )
            OVER
            (
               ORDER BY
                  COALESCE
               (
                  `d`.`Creneau`,
                  `r`.`Creneau`
               )
            )
            + SUM
            (
               COALESCE
               (
                  `d`.`NB_Jeux_rendus`,
                  0
               )
            )
            OVER
            (
               ORDER BY
                  COALESCE
               (
                  `d`.`Creneau`,
                  `r`.`Creneau`
               )
            )
         )
         + SUM
         (
            COALESCE
            (
               `d`.`NB_Jeux_donnes`,
               0
            )
         )
         OVER
         (
            ORDER BY
               COALESCE
            (
               `d`.`Creneau`,
               `r`.`Creneau`
            )
         )
      )
   ) AS `NB_Jeux_stock`,
   -- On prend les cumulés de réception - les cumulés des ventes / restitutions / dons

   (
      SUM
      (
         COALESCE
         (
            `r`.`Montant_Jeux_receptionnes`,
            0
         )
      )
      OVER
      (
         ORDER BY
            COALESCE
         (
            `d`.`Creneau`,
            `r`.`Creneau`
         )
      )

      (
         (
            SUM
            (
               COALESCE
               (
                  `d`.`Montant_Jeux_vendus`,
                  0
               )
            )
            OVER
            (
               ORDER BY
                  COALESCE
               (
                  `d`.`Creneau`,
                  `r`.`Creneau`
               )
            )
            + SUM
            (
               COALESCE
               (
                  `d`.`Montant_Jeux_rendus`,
                  0
               )
            )
            OVER
            (
               ORDER BY
                  COALESCE
               (
                  `d`.`Creneau`,
                  `r`.`Creneau`
               )
            )
         )
         + SUM
         (
            COALESCE
            (
               `d`.`Montant_Jeux_donnes`,
               0
            )
         )
         OVER
         (
            ORDER BY
               COALESCE
            (
               `d`.`Creneau`,
               `r`.`Creneau`
            )
         )
      )
   ) AS `Montant_Jeux_stock`
   -- On prend les cumulés de réception - les cumulés des ventes / restitutions / dons
FROM
(
   (
      SELECT
         DATE_FORMAT

         (
            `Baj`.`al_bourse_liste`.`date_reception`,
            '%Y-%m-%d %H:00'
         ) AS `Creneau`,
      COUNT (0) AS `NB_Jeux_receptionnes`,
      SUM (`Baj`.`al_bourse_liste`.`prix`) AS `Montant_Jeux_receptionnes`
      FROM
         `Baj`.`al_bourse_liste`
      WHERE
      (
         (
            `Baj`.`al_bourse_liste`.`statut` NOT IN
            (
               1,
               9,
               11
            )
         )
         AND
         (
            `Baj`.`al_bourse_liste`.`annee` = YEAR (CURDATE ())
         )
      )
      GROUP BY
         DATE_FORMAT
      (
         `Baj`.`al_bourse_liste`.`date_reception`,
         '%Y-%m-%d %H:00'
      )
   )
   `r`
   LEFT
   JOIN
   (
      SELECT
         DATE_FORMAT

         (
            `Baj`.`al_bourse_liste`.`date_sortie_stock`,
            '%Y-%m-%d %H:00'
         ) AS `Creneau`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 3
               )
                  THEN 1 ELSE
               0 END
            )
         ) AS `NB_Jeux_vendus`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 3
               )
                  THEN `Baj`.`al_bourse_liste`.`prix` ELSE
               0 END
            )
         ) AS `Montant_Jeux_vendus`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 4
               )
                  THEN 1 ELSE
               0 END
            )
         ) AS `NB_Jeux_rendus`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 4
               )
                  THEN `Baj`.`al_bourse_liste`.`prix` ELSE
               0 END
            )
         ) AS `Montant_Jeux_rendus`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 6
               )
                  THEN 1 ELSE
               0 END
            )
         ) AS `NB_Jeux_donnes`,
      SUM
         (
            (
               CASE
                  WHEN
               (
                  `Baj`.`al_bourse_liste`.`statut` = 6
               )
                  THEN `Baj`.`al_bourse_liste`.`prix` ELSE
               0 END
            )
         ) AS `Montant_Jeux_donnes`
      FROM
         `Baj`.`al_bourse_liste`
      WHERE
      (
         `Baj`.`al_bourse_liste`.`annee` = YEAR (CURDATE ())
      )
      GROUP BY
         DATE_FORMAT
      (
         `Baj`.`al_bourse_liste`.`date_sortie_stock`,
         '%Y-%m-%d %H:00'
      )
   )
   `d` ON
   (
      (
         `d`.`Creneau` = `r`.`Creneau`
      )
   )
)
WHERE
(
   `d`.`Creneau` IS NULL
)
ORDER BY
   `Creneau`;
   
   
-- requete transaction : 
SELECT
   DATE_FORMAT

   (
      date,
      '%Y-%m-%d %H:00'
   ) AS Creneau,
COUNT (*) AS NB_transaction,
SUM (montantTotal) AS Montant_transaction_total,
SUM (montantPercu) AS Montant_transaction_percu,
SUM (montantRendu) AS Montant_transaction_rendu,
SUM
   (
      CASE
         WHEN paiement = 'CB'    THEN 1 ELSE
      0 END
   ) AS NB_transaction_CB,
SUM
   (
      CASE
         WHEN paiement = 'CB'    THEN montantTotal ELSE
      0 END
   ) AS Montant_transaction_total_CB,
SUM
   (
      CASE
         WHEN paiement = 'especes'    THEN 1 ELSE
      0 END
   ) AS NB_transaction_ES,
SUM
   (
      CASE
         WHEN paiement = 'especes'    THEN montantTotal ELSE
      0 END
   ) AS Montant_transaction_total_ES,
SUM
   (
      CASE
         WHEN paiement = 'cheque'    THEN 1 ELSE
      0 END
   ) AS NB_transaction_CH,
SUM
   (
      CASE
         WHEN paiement = 'cheque'    THEN montantTotal ELSE
      0 END
   ) AS Montant_transaction_total_CH
FROM
   al_bourse_transactions
WHERE
   YEAR (date) = YEAR(CURRENT_DATE())
GROUP BY
   DATE_FORMAT
(
   date,
   '%Y-%m-%d %H:00'
);
-- Moyenne / min / max nb jeux, et montant jeux par vendeur
SELECT 
    AVG(prix) AS montant_moyen_par_vendeur,
    AVG(benefice) AS benefice_moyen_par_vendeur,
    MAX(prix) AS montant_max_par_vendeur,
    MAX(benefice) AS benefice_max_par_vendeur,
    AVG(nombre_de_jeux) AS moyenne_nombre_jeux_par_vendeur
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
) AS jeux_par_vendeur;

-- Moyenne, min et max temps de présence des jeux : 
SELECT 
    CONCAT(
        FLOOR(MIN(duree_stock) / (24 * 60)), ' jours ',
        FLOOR((MIN(duree_stock) % (24 * 60)) / 60), ' heures ',
        MIN(duree_stock) % 60, ' minutes'
    ) AS duree_min_stock,
    CONCAT(
        FLOOR(MAX(duree_stock) / (24 * 60)), ' jours ',
        FLOOR((MAX(duree_stock) % (24 * 60)) / 60), ' heures ',
        MAX(duree_stock) % 60, ' minutes'
    ) AS duree_max_stock,
    CONCAT(
        FLOOR(AVG(duree_stock) / (24 * 60)), ' jours ',
        FLOOR((AVG(duree_stock) % (24 * 60)) / 60), ' heures ',
        AVG(duree_stock) % 60, ' minutes'
    ) AS duree_moyenne_stock
FROM (
    SELECT TIMESTAMPDIFF(MINUTE, date_reception, date_sortie_stock) AS duree_stock
    FROM al_bourse_liste
) AS duree_stock;

-- Moyenne jeux, montant des transaction + max montant transaction + nb sacs
SELECT 
    AVG(nombre_de_jeux) AS moyenne_nombre_jeux_par_transaction,
    AVG(prix) AS montant_moyen_transaction,
    SUM(nombre_sacs_achetes) AS nombre_sacs_achetes,
    MAX(prix) AS montant_plus_grande_transaction
FROM (
    SELECT 
        id_transaction, 
        COUNT(*) AS nombre_de_jeux,
        SUM(CASE WHEN id_bourse_liste = '-1' THEN 1 ELSE 0 END) AS nombre_sacs_achetes,
        SUM(prix) AS prix
    FROM al_bourse_transaction_liste
    LEFT JOIN al_bourse_transactions ON al_bourse_transaction_liste.id_transaction = al_bourse_transactions.id
    LEFT JOIN al_bourse_liste ON al_bourse_transaction_liste.id_bourse_liste = al_bourse_liste.id
    WHERE YEAR(al_bourse_transactions.date) = YEAR(CURRENT_DATE)
    GROUP BY id_transaction
) AS jeux_par_transaction;