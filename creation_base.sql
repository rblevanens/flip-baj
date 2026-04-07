-- Généré le : jeu. 18 avr. 2024 à 15:24
-- Version du serveur : 8.0.36-0ubuntu0.22.04.1
-- Version de PHP : 8.1.2-1ubuntu2.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `Baj`
--
CREATE DATABASE IF NOT EXISTS `Baj` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `Baj`;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_acheteur`
--

CREATE TABLE `al_bourse_acheteur` (
  `id` int NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(200) NOT NULL,
  `adresse` varchar(200) NOT NULL,
  `code_postal` int NOT NULL,
  `ville` varchar(100) NOT NULL,
  `raison_sociale` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `siret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_dons`
--

CREATE TABLE `al_bourse_dons` (
  `id` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `montant_don` float NOT NULL,
  `date_don` datetime NOT NULL,
  `type_don` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_jeux`
--

CREATE TABLE `al_bourse_jeux` (
  `Id` int NOT NULL,
  `Nom` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_journal_statut`
--

CREATE TABLE `al_bourse_journal_statut` (
  `id` int NOT NULL,
  `id_liste` int NOT NULL,
  `old_id_statut` int NOT NULL,
  `new_id_statut` int NOT NULL,
  `ip` varchar(100) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_liste`
--

CREATE TABLE `al_bourse_liste` (
  `id` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `nom_jeu` varchar(500) NOT NULL,
  `prix` int NOT NULL,
  `code_barre` varchar(20) NOT NULL,
  `statut` int NOT NULL,
  `vigilance` tinyint(1) NOT NULL DEFAULT '0',
  `id_depot` varchar(100) NOT NULL,
  `date_reception` datetime DEFAULT NULL,
  `date_sortie_stock` datetime DEFAULT NULL,
  `annee` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_pdf`
--

CREATE TABLE `al_bourse_pdf` (
  `id` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `nom_fichier` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `annee` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_remboursements`
--

CREATE TABLE `al_bourse_remboursements` (
  `id` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `montant_remb` float NOT NULL,
  `date_remb` datetime NOT NULL,
  `type_remb` varchar(20) CHARACTER SET utf16 COLLATE utf16_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_statuts_jeux`
--

CREATE TABLE `al_bourse_statuts_jeux` (
  `id` int NOT NULL,
  `value` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_transactions`
--

CREATE TABLE `al_bourse_transactions` (
  `id` int NOT NULL,
  `id_acheteur` int NOT NULL DEFAULT '0',
  `type` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `montantTotal` float NOT NULL,
  `montantPercu` float NOT NULL,
  `montantDon` float NOT NULL DEFAULT '0',
  `montantRendu` float NOT NULL,
  `paiement` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_transaction_liste`
--

CREATE TABLE `al_bourse_transaction_liste` (
  `id` int NOT NULL,
  `id_transaction` int NOT NULL,
  `id_bourse_liste` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `al_bourse_users`
--

CREATE TABLE `al_bourse_users` (
  `id` int NOT NULL,
  `nom` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `prenom` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `telephone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `adresse` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `code_postal` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ville` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `denomination_sociale` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `siege_social` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `attestation_signee` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'False'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_bourse_jeux_a_restituer`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_bourse_jeux_a_restituer` (
`code_barre` varchar(20)
,`email` varchar(100)
,`id_phpbb` int
,`nom` varchar(50)
,`nom_jeu` varchar(500)
,`prenom` varchar(50)
,`statut` varchar(20)
,`telephone` varchar(20)
,`vendu` int
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_bourse_liste`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_bourse_liste` (
`annee` int
,`code_barre` varchar(20)
,`date_reception` datetime
,`date_sortie_stock` datetime
,`id` int
,`id_statut` int
,`id_utilisateur` int
,`nom` varchar(50)
,`nom_jeu` varchar(500)
,`prenom` varchar(50)
,`rendu` bigint
,`statut` varchar(20)
,`vendu` int
,`vigilance` tinyint(1)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_bourse_liste_jeux_vendus`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_bourse_liste_jeux_vendus` (
`ANNEE` int
,`CODE_BARRE` varchar(20)
,`ID_LISTE` int
,`ID_TRANSACTION` int
,`NOM_JEU` varchar(500)
,`NOM_VENDEUR` varchar(50)
,`PRENOM_VENDEUR` varchar(50)
,`PRIX_JEU` int
,`STATUT` varchar(20)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_bourse_montant_rendus`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_bourse_montant_rendus` (
`ARendre` decimal(43,0)
,`nom` varchar(50)
,`prenom` varchar(50)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_bourse_stats_devenir_h_by_h`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_bourse_stats_devenir_h_by_h` (
`heure` varchar(18)
,`nb_jeux_donne` decimal(23,0)
,`nb_jeux_receptionne` bigint
,`nb_jeux_rendu` decimal(23,0)
,`nb_jeux_vendu` decimal(23,0)
,`nombre_jeux_en_stock` bigint
,`somme_jeux_donne` decimal(32,0)
,`somme_jeux_receptionne` decimal(32,0)
,`somme_jeux_rendu` decimal(32,0)
,`somme_jeux_vendu` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_bourse_stats_receptions`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_bourse_stats_receptions` (
`heure` datetime
,`heure_formatée` varchar(19)
,`montant_jeux_receptionnes` decimal(32,0)
,`nb_jeux_receptionnes` bigint
,`nb_vendeur` bigint
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_bourse_stock`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_bourse_stock` (
`heure` varchar(19)
,`nombre_jeux_en_stock` bigint
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_bourse_transactions`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_bourse_transactions` (
`date` datetime
,`id` int
,`id_acheteur` int
,`mail_Acheteur` varchar(200)
,`montantDon` float
,`montantPercu` float
,`montantRendu` float
,`montantTotal` float
,`nom_Acheteur` varchar(100)
,`nom_jeu` varchar(500)
,`nom_Vendeur` varchar(50)
,`paiement` varchar(10)
,`prenom_Acheteur` varchar(100)
,`prenom_Vendeur` varchar(50)
,`vendu` int
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_bourse_vendeurs_tout_vendu`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_bourse_vendeurs_tout_vendu` (
`email` varchar(100)
,`NOM` varchar(50)
,`PRENOM` varchar(50)
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_bourse_jeux_a_restituer`
--
DROP TABLE IF EXISTS `v_bourse_jeux_a_restituer`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bourse_jeux_a_restituer`  AS SELECT `v_bourse_liste`.`id_utilisateur` AS `id_phpbb`, `v_bourse_liste`.`nom` AS `nom`, `v_bourse_liste`.`prenom` AS `prenom`, `al_bourse_users`.`telephone` AS `telephone`, `al_bourse_users`.`email` AS `email`, `v_bourse_liste`.`nom_jeu` AS `nom_jeu`, `v_bourse_liste`.`statut` AS `statut`, `v_bourse_liste`.`vendu` AS `vendu`, `v_bourse_liste`.`code_barre` AS `code_barre` FROM (`v_bourse_liste` left join `al_bourse_users` on((`al_bourse_users`.`id` = `v_bourse_liste`.`id_utilisateur`))) WHERE ((`v_bourse_liste`.`annee` = year(curdate())) AND (`v_bourse_liste`.`id_utilisateur` <> 1) AND (`v_bourse_liste`.`id_statut` = 2) AND exists(select 1 from `v_bourse_liste` `vbl` where ((`vbl`.`id_utilisateur` = `v_bourse_liste`.`id_utilisateur`) AND (`vbl`.`id_statut` in (2,3)) AND (`vbl`.`date_sortie_stock` is null)))) ORDER BY `v_bourse_liste`.`nom` ASC, `v_bourse_liste`.`prenom` ASC, `v_bourse_liste`.`statut` ASC, `v_bourse_liste`.`nom_jeu` ASC ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_bourse_liste`
--
DROP TABLE IF EXISTS `v_bourse_liste`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bourse_liste`  AS SELECT `al_bourse_liste`.`id` AS `id`, `al_bourse_liste`.`id_utilisateur` AS `id_utilisateur`, `al_bourse_users`.`nom` AS `nom`, `al_bourse_users`.`prenom` AS `prenom`, `al_bourse_liste`.`nom_jeu` AS `nom_jeu`, `al_bourse_liste`.`prix` AS `vendu`, (`al_bourse_liste`.`prix` - ceiling((`al_bourse_liste`.`prix` / 6.0))) AS `rendu`, `al_bourse_liste`.`code_barre` AS `code_barre`, `al_bourse_liste`.`statut` AS `id_statut`, `al_bourse_liste`.`vigilance` AS `vigilance`, `al_bourse_statuts_jeux`.`value` AS `statut`, `al_bourse_liste`.`date_reception` AS `date_reception`, `al_bourse_liste`.`date_sortie_stock` AS `date_sortie_stock`, `al_bourse_liste`.`annee` AS `annee` FROM ((`al_bourse_liste` left join `al_bourse_users` on((`al_bourse_users`.`id` = `al_bourse_liste`.`id_utilisateur`))) left join `al_bourse_statuts_jeux` on((`al_bourse_statuts_jeux`.`id` = `al_bourse_liste`.`statut`))) ORDER BY `al_bourse_liste`.`annee` DESC, `al_bourse_liste`.`id_utilisateur` ASC ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_bourse_liste_jeux_vendus`
--
DROP TABLE IF EXISTS `v_bourse_liste_jeux_vendus`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bourse_liste_jeux_vendus`  AS SELECT `al_bourse_transactions`.`id` AS `ID_TRANSACTION`, `al_bourse_transaction_liste`.`id_bourse_liste` AS `ID_LISTE`, `v_bourse_liste`.`nom` AS `NOM_VENDEUR`, `v_bourse_liste`.`prenom` AS `PRENOM_VENDEUR`, `v_bourse_liste`.`nom_jeu` AS `NOM_JEU`, `v_bourse_liste`.`vendu` AS `PRIX_JEU`, `v_bourse_liste`.`code_barre` AS `CODE_BARRE`, `v_bourse_liste`.`statut` AS `STATUT`, `v_bourse_liste`.`annee` AS `ANNEE` FROM ((`al_bourse_transactions` left join `al_bourse_transaction_liste` on((`al_bourse_transaction_liste`.`id_transaction` = `al_bourse_transactions`.`id`))) left join `v_bourse_liste` on((`v_bourse_liste`.`id` = `al_bourse_transaction_liste`.`id_bourse_liste`))) WHERE ((`v_bourse_liste`.`annee` = year(curdate())) AND (`v_bourse_liste`.`statut` = 'vendu')) ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_bourse_montant_rendus`
--
DROP TABLE IF EXISTS `v_bourse_montant_rendus`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bourse_montant_rendus`  AS SELECT `v_bourse_liste`.`nom` AS `nom`, `v_bourse_liste`.`prenom` AS `prenom`, sum(`v_bourse_liste`.`rendu`) AS `ARendre` FROM `v_bourse_liste` WHERE ((`v_bourse_liste`.`id_statut` = 3) AND (`v_bourse_liste`.`date_sortie_stock` is not null) AND (`v_bourse_liste`.`date_sortie_stock` <> 0)) GROUP BY `v_bourse_liste`.`nom`, `v_bourse_liste`.`prenom` ORDER BY `ARendre` ASC ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_bourse_stats_devenir_h_by_h`
--
DROP TABLE IF EXISTS `v_bourse_stats_devenir_h_by_h`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bourse_stats_devenir_h_by_h`  AS SELECT `dates`.`heure` AS `heure`, ifnull(`sortie`.`nb_jeux_vendu`,0) AS `nb_jeux_vendu`, ifnull(`sortie`.`somme_jeux_vendu`,0) AS `somme_jeux_vendu`, ifnull(`sortie`.`nb_jeux_rendu`,0) AS `nb_jeux_rendu`, ifnull(`sortie`.`somme_jeux_rendu`,0) AS `somme_jeux_rendu`, ifnull(`sortie`.`nb_jeux_donne`,0) AS `nb_jeux_donne`, ifnull(`sortie`.`somme_jeux_donne`,0) AS `somme_jeux_donne`, ifnull(`entree`.`nb_jeux_receptionne`,0) AS `nb_jeux_receptionne`, ifnull(`entree`.`somme_jeux_receptionne`,0) AS `somme_jeux_receptionne`, ifnull(`stock`.`nombre_jeux_en_stock`,0) AS `nombre_jeux_en_stock` FROM ((((select distinct date_format(`al_bourse_liste`.`date_sortie_stock`,'%Y/%m/%d %H') AS `heure` from `al_bourse_liste` union select distinct date_format(`al_bourse_liste`.`date_reception`,'%Y/%m/%d %H') AS `heure` from `al_bourse_liste`) `dates` left join (select date_format(`al_bourse_liste`.`date_sortie_stock`,'%Y/%m/%d %H') AS `heure`,sum((case when (`al_bourse_liste`.`statut` = 3) then 1 else 0 end)) AS `nb_jeux_vendu`,sum((case when (`al_bourse_liste`.`statut` = 3) then `al_bourse_liste`.`prix` else 0 end)) AS `somme_jeux_vendu`,sum((case when (`al_bourse_liste`.`statut` = 4) then 1 else 0 end)) AS `nb_jeux_rendu`,sum((case when (`al_bourse_liste`.`statut` = 4) then `al_bourse_liste`.`prix` else 0 end)) AS `somme_jeux_rendu`,sum((case when (`al_bourse_liste`.`statut` = 6) then 1 else 0 end)) AS `nb_jeux_donne`,sum((case when (`al_bourse_liste`.`statut` = 6) then `al_bourse_liste`.`prix` else 0 end)) AS `somme_jeux_donne` from `al_bourse_liste` group by `heure`) `sortie` on((`dates`.`heure` = `sortie`.`heure`))) left join (select date_format(`al_bourse_liste`.`date_reception`,'%Y/%m/%d %H') AS `heure`,count(0) AS `nb_jeux_receptionne`,sum(`al_bourse_liste`.`prix`) AS `somme_jeux_receptionne` from `al_bourse_liste` group by `heure`) `entree` on((`dates`.`heure` = `entree`.`heure`))) left join (select date_format(`hourly`.`hour`,'%Y-%m-%d %H') AS `heure`,coalesce(count(0),0) AS `nombre_jeux_en_stock` from ((select distinct date_format(`al_bourse_liste`.`date_reception`,'%Y-%m-%d %H') AS `hour` from `al_bourse_liste` union select distinct date_format(`al_bourse_liste`.`date_sortie_stock`,'%Y-%m-%d %H') AS `hour` from `al_bourse_liste`) `hourly` left join `al_bourse_liste` `al` on((((date_format(`al`.`date_reception`,'%Y-%m-%d %H') <= `hourly`.`hour`) and (`al`.`date_reception` is not null)) or (date_format(`al`.`date_sortie_stock`,'%Y-%m-%d %H') >= `hourly`.`hour`) or (`al`.`date_sortie_stock` is null)))) group by `hourly`.`hour`) `stock` on((`stock`.`heure` = `dates`.`heure`))) ORDER BY `dates`.`heure` DESC ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_bourse_stats_receptions`
--
DROP TABLE IF EXISTS `v_bourse_stats_receptions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bourse_stats_receptions`  AS SELECT count(0) AS `nb_jeux_receptionnes`, sum(`al_bourse_liste`.`prix`) AS `montant_jeux_receptionnes`, count(distinct `al_bourse_liste`.`id_utilisateur`) AS `nb_vendeur`, str_to_date(`al_bourse_liste`.`date_reception`,'%Y-%m-%d %H') AS `heure`, date_format(`al_bourse_liste`.`date_reception`,'%d/%m/%Y %Hh') AS `heure_formatée` FROM `al_bourse_liste` WHERE ((`al_bourse_liste`.`date_reception` is not null) AND (year(`al_bourse_liste`.`date_reception`) = 2023)) GROUP BY `heure` ORDER BY `heure` ASC ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_bourse_stock`
--
DROP TABLE IF EXISTS `v_bourse_stock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bourse_stock`  AS SELECT date_format(`hourly`.`hour`,'%Y-%m-%d %Hh') AS `heure`, coalesce(count(0),0) AS `nombre_jeux_en_stock` FROM ((select distinct date_format(`al_bourse_liste`.`date_reception`,'%Y-%m-%d %Hh') AS `hour` from `al_bourse_liste` union select distinct date_format(`al_bourse_liste`.`date_sortie_stock`,'%Y-%m-%d %Hh') AS `hour` from `al_bourse_liste`) `hourly` left join `al_bourse_liste` `al` on(((date_format(`al`.`date_reception`,'%Y-%m-%d %Hh') <= `hourly`.`hour`) and (`al`.`date_reception` is not null) and ((date_format(`al`.`date_sortie_stock`,'%Y-%m-%d %Hh') >= `hourly`.`hour`) or (`al`.`date_sortie_stock` is null))))) GROUP BY date_format(`hourly`.`hour`,'%Y-%m-%d %Hh') ORDER BY `heure` ASC ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_bourse_transactions`
--
DROP TABLE IF EXISTS `v_bourse_transactions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`baj`@`localhost` SQL SECURITY DEFINER VIEW `v_bourse_transactions`  AS SELECT `al_bourse_transactions`.`id` AS `id`, `al_bourse_transactions`.`montantTotal` AS `montantTotal`, `al_bourse_transactions`.`montantPercu` AS `montantPercu`, `al_bourse_transactions`.`montantDon` AS `montantDon`, `al_bourse_transactions`.`montantRendu` AS `montantRendu`, `al_bourse_transactions`.`paiement` AS `paiement`, `al_bourse_transactions`.`date` AS `date`, `al_bourse_transactions`.`id_acheteur` AS `id_acheteur`, `al_bourse_acheteur`.`nom` AS `nom_Acheteur`, `al_bourse_acheteur`.`prenom` AS `prenom_Acheteur`, `al_bourse_acheteur`.`email` AS `mail_Acheteur`, `v_bourse_liste`.`nom_jeu` AS `nom_jeu`, `v_bourse_liste`.`vendu` AS `vendu`, `v_bourse_liste`.`nom` AS `nom_Vendeur`, `v_bourse_liste`.`prenom` AS `prenom_Vendeur` FROM (((`al_bourse_transactions` left join `al_bourse_acheteur` on((`al_bourse_acheteur`.`id` = `al_bourse_transactions`.`id_acheteur`))) left join `al_bourse_transaction_liste` on((`al_bourse_transaction_liste`.`id_transaction` = `al_bourse_transactions`.`id`))) left join `v_bourse_liste` on((`v_bourse_liste`.`id` = `al_bourse_transaction_liste`.`id_bourse_liste`))) WHERE (`al_bourse_transactions`.`type` = 'vente') ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_bourse_vendeurs_tout_vendu`
--
DROP TABLE IF EXISTS `v_bourse_vendeurs_tout_vendu`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bourse_vendeurs_tout_vendu`  AS SELECT DISTINCT `v_bourse_liste`.`nom` AS `NOM`, `v_bourse_liste`.`prenom` AS `PRENOM`, `al_bourse_users`.`email` AS `email` FROM (`v_bourse_liste` left join `al_bourse_users` on((`al_bourse_users`.`id` = `v_bourse_liste`.`id_utilisateur`))) WHERE (exists(select 1 from `v_bourse_liste` `vbl` where ((`vbl`.`id_utilisateur` = `v_bourse_liste`.`id_utilisateur`) AND (`vbl`.`id_statut` = 3))) AND exists(select 1 from `v_bourse_liste` `vbl` where ((`vbl`.`id_utilisateur` = `v_bourse_liste`.`id_utilisateur`) AND (`vbl`.`id_statut` = 2))) is false) ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `al_bourse_acheteur`
--
ALTER TABLE `al_bourse_acheteur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Index pour la table `al_bourse_dons`
--
ALTER TABLE `al_bourse_dons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `al_bourse_jeux`
--
ALTER TABLE `al_bourse_jeux`
  ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `al_bourse_journal_statut`
--
ALTER TABLE `al_bourse_journal_statut`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_liste` (`id_liste`);

--
-- Index pour la table `al_bourse_liste`
--
ALTER TABLE `al_bourse_liste`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `nom_jeu` (`nom_jeu`),
  ADD KEY `code_barre` (`code_barre`),
  ADD KEY `statut` (`statut`),
  ADD KEY `vigilance` (`vigilance`),
  ADD KEY `annee` (`annee`);

--
-- Index pour la table `al_bourse_pdf`
--
ALTER TABLE `al_bourse_pdf`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `al_bourse_remboursements`
--
ALTER TABLE `al_bourse_remboursements`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `al_bourse_transactions`
--
ALTER TABLE `al_bourse_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_phpbb` (`id_acheteur`),
  ADD KEY `type` (`type`),
  ADD KEY `date` (`date`);

--
-- Index pour la table `al_bourse_transaction_liste`
--
ALTER TABLE `al_bourse_transaction_liste`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_transaction` (`id_transaction`);

--
-- Index pour la table `al_bourse_users`
--
ALTER TABLE `al_bourse_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nom` (`nom`),
  ADD KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `al_bourse_acheteur`
--
ALTER TABLE `al_bourse_acheteur`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `al_bourse_dons`
--
ALTER TABLE `al_bourse_dons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `al_bourse_jeux`
--
ALTER TABLE `al_bourse_jeux`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `al_bourse_journal_statut`
--
ALTER TABLE `al_bourse_journal_statut`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `al_bourse_liste`
--
ALTER TABLE `al_bourse_liste`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `al_bourse_pdf`
--
ALTER TABLE `al_bourse_pdf`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `al_bourse_remboursements`
--
ALTER TABLE `al_bourse_remboursements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `al_bourse_transactions`
--
ALTER TABLE `al_bourse_transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `al_bourse_transaction_liste`
--
ALTER TABLE `al_bourse_transaction_liste`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `al_bourse_users`
--
ALTER TABLE `al_bourse_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- INSERT INTO `al_bourse_users` (`id`, `nom`, `prenom`, `telephone`, `email`, `adresse`, `code_postal`, `ville`, `denomination_sociale`, `siege_social`, `attestation_signee`) 
-- VALUES (1, 'Woopy', 'OnOFF', '06 74 93 45 85', 'boursejeuxflip@gmail.com', ' 2 rue de la Citadelle ', '79200', 'Parthenay', 'WOOPY ON OFF', 'Parthenay', 'False');

INSERT INTO `al_bourse_statuts_jeux` (`id`, `value`) 
VALUES                                  ('0', 'Non précisé'), 
                                        ('1', 'Pas reçu'), 
                                        ('2', 'En stock'), 
                                        ('3', 'Vendu'), 
                                        ('4', 'Rendu'), 
                                        ('5', 'Dans un panier'), 
                                        ('6', 'Donné'), 
                                        ('7', 'Au box'); 