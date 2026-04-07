/*-------------------------------------------------------------
------------- Ajouter un jeu a la liste --------------
----------------------------------------
-----------------------------
*/

/* 
INSERT INTO `al_bourse_liste`(`id`, `id_utilisateur`, `nom_jeu`, `prix`, `code_barre`, `statut`, `vigilance`, `id_depot`, `date_reception`, `date_sortie_stock`, `annee`) 
 VALUES                      ('6' , '1'             ,   'Conex',   '70',       '0006',      '2',         '1',         '',     '03/06/2024',                  '','2024'  ); 
    
    */ 

/*-------------------------------------------------------------
------------- Ajouter un utilisateur --------------
----------------------------------------
-----------------------------
*/

/* 
    INSERT INTO `al_bourse_users`(`id`,     `nom`,  `prenom`,  `telephone`,  `email`,  `adresse`,  `code_postal`,  `ville`,  `denomination_sociale`,  `siege_social`,  `attestation_signee`) 
    VALUES                       ('[id-1]','[nom]','[prenom]','[telephone]','[email]','[adresse]','[code_postal]','[ville]','[denomination_sociale]','[siege_social]','[attestation_signee]')
    */ 

INSERT INTO `al_bourse_users` (`id`, `nom`, `prenom`, `telephone`, `email`, `adresse`, `code_postal`, `ville`, `denomination_sociale`, `siege_social`, `attestation_signee`) VALUES (1, 'Woopy', 'OnOFF', '06 74 93 45 85', 'boursejeuxflip@gmail.com', ' 2 rue de la Citadelle ', '79200', 'Parthenay', 'WOOPY ON OFF', 'Parthenay', 'False') 
/*-------------------------------------------------------------
------------- Ajouter un statut aux jeux --------------
----------------------------------------
-----------------------------
*/

/* INSERT INTO `al_bourse_statuts_jeux` (`id`, `value`) 
VALUES                                  ('0', 'Non précisé'), 
                                        ('1', 'Pas reçu'), 
                                        ('2', 'En stock'), 
                                        ('3', 'Vendu'), 
                                        ('4', 'Rendu'), 
                                        ('5', 'Dans un panier'), 
                                        ('6', 'Donné'), 
                                        ('7', 'Au box'); 
*/