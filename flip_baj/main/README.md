## Synopsis

Projet BAJ (Bourse aux jeux) 2017. Version simplifiée qui tente de réduire les temps d'accès énormes rencontrés lors de l'édition 2016. Suppression des grosses page JS pour un découpage plus simple.
Projet relancé surtout en 2023 et 2024, avec de grandes améliorations pour s'adapter au nouveau règlement.

## Version

### 0.0.1
	- création des indexes sur toutes les tables al_bourse (voir repertoire sql)
		- il faudra refaire ce fichier et bien le bétonner en partant d'une base vierge. car je n'ai traiter pour le moment que le cas de al_bourse_users
	- liste de tous les vendeurs (pas de sélection sur l'année ni sur les listes)
	- formulaire de création de vendeur ok
	- je pense qu'il faudra faire une page d'update de vendeur, pour les vieux vendeurs qui n'ont pas de listes et qui ont leurs données incomplètes. Ceci pour éviter d'avoir à créer deux venderus identiques.
	- ne pas oublier d'exécuter le fichier SQL d'ajout des colonnes et indexes
	- créer un directoryindex pour apache pour le download des fichiers

	ScriptAlias /fichiers /home/alchimie/Documents/fdjtl/pdf
		<Directory "/home/alchimie/Documents/fdjtl/pdf">
			Require all granted
			Allow from all
			Options +Indexes
		</Directory>
	</VirtualHost>

### 2024
	- Présence des indexes.
	- Réfection des différentes pages.
	- Ajout d'une page Admin pour gérer l'import, l'export, les flux d'argents et les jeux supspects. 
	- Ajout d'une table des acheteurs, séparée de la table des vendeurs
	- Fusion des différentes tables de vendeurs
	- Changement de la liste des jeux en stock pour afficher l'ensemble des jeux et des champs de recherche
	- Changement du calcul des prix rendus pour s'adapter au règlement (rendu*1,2=vendu)
	- Changement de la page de remboursement pour pouvoir choisir le montant et le moyen de paiement
	- Changement de la gestion des jeux perdus : plus de changement de statut, mais l'ajout de la caractéristique "vigilance".
	- Ajout des scripts d'import de tables directement dans le logiciel.
	- Création d'une table des remboursements
	- Ajout d'une classe secondaire PDF de FPDF pour aider à la confection des PDF et en particulier des tableaux
	- Modification de la gestion des pdf : plus de règlement, édition en fin de festival.
	- Ajout de la notion de panier et du statut "Dans un panier"
	- Ajout de la doc via doxygen
	- Ajout de commentaires en syntaxe javaDoc
	- Création d'un repo github
	- Réfection du site en ligne utilisé : bourseauxjeux.alchimiedujeu.fr
	- Suppression des parties de code / fichiers inutilisés
	- Mise à jour Bootstrap (v5.1.3) + Jquery (v3.7.0) et les bibliothèques dépendantes
	- Elagage des CSS, uniformisation des pages
	- Suppression du maximum d'images : passage aux icones Bootstrap-icons

##Installation

### Première installation

	Il faut installer un serveur apache avec base de donnée mysql. Le site : https://doc.ubuntu-fr.org/lamp contient toutes les informations nécessaires. 
	Le fichier SQL : bdd.sql contient tout le code sql pour créer la base et les différentes tables nécessaires.
	Il faut modifier le fichier pdo_connect.php avec le nom de la base et les informations de connections.
	Les factures seront téléchargées dans le dossier main/pdf/pdf/facture. Il faut vérifier les autorisations d'écriture des dossiers. Une bonne méthode est d'éxécuter : 
	 "chown www-data -R chemin/racine/main/pdf/pdf"

### Mise en service à chaque édition

	Importer les tables via la page admin. Le script : 
	- Truncate la base de donnée des user et acheteurs. 
	- Mets l'id_vendeur de tous les anciens jeux à 0, ce qui anonymise mais permet de garder les stats. 

## Actions de fin de festival 
	
	Télécharger les stats de fin de festival et les factures via la page admin. 
	Il est conseillé de télécharger aussi la base de donnée (.zip) depuis la page admin.

## Contributors
	Inconnus
	Yanick Mescam
	Eric Piallat
	Mathieu Piallat

##Bibliothèques Externes

## Actions pour Le flip 
	- Désactivation de la fonction d'ajouts de sacs.
	- Changement de format pour la bdd, utf8mb3_unicode_ci remplace utf8mb4_0900_ai_ci
	-  


Ce projet utilise les bibliothèques externes suivantes :

    Bootstrap v5.1.3 - https://getbootstrap.com/
    Crypto-JS - https://github.com/brix/crypto-js
    jQuery UI v1.13.2 - https://jqueryui.com/
    jQuery v3.7.0 - https://jquery.com/
    DataTables v2.0.5 - https://datatables.net/
    KeyTable v2.12.0 - https://datatables.net/extensions/keytable/
    Responsive v3.0.2 - https://datatables.net/extensions/responsive/
    Scroller v2.4.1 - https://datatables.net/extensions/scroller/
    jquery.jeditable v1.7.1 - https://github.com/tuupola/jquery_jeditable

Les bibliothèques jQuery UI v1.13.2, jQuery 3 v3.7.0, DataTables v2.0.5, KeyTable v2.12.0, Responsive v3.0.2 et Scroller v2.4.1 sont situées dans le même fichier (datatables.min.js)
        
## License

Bénévole In Orange - Association loi 1901 -- Alchimie Du Jeu