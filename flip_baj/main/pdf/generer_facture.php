<?php
namespace flip_baj\main\pdf;


use PDOException;

/**
 * Génère une facture pour un vendeur donné.
 * La facture est ensuite envoyée dans le dossier $rep_facture
 *
 * @param int $id_vendeur L'identifiant du vendeur pour lequel générer la facture.
 * @return void
 */
function GenererFacture($id_vendeur)
{
    // Récupération des données
    include ('../pdo_connect.php');
    include ('../constantes.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }
    try {
        $statement = $pdo->prepare($SQL_2_getvendeur);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $statement->execute([
        'idDuVendeur' => $id_vendeur
    ]);
    if (!$vendeur = $statement->fetch()){
        return ;
    }
    $vendeur = array_map(function($value) {
        return html_entity_decode($value ?? '', ENT_QUOTES, 'UTF-8');
    }, $vendeur);
    
    $nom = $vendeur["nom"];
    $prenom = $vendeur["prenom"];
    $code_postal = $vendeur["code_postal"];
    $ville = $vendeur["ville"];
    $adresse = $vendeur["adresse"];
    $adresseComplete = "Domicilié au " . $adresse . ", " . $code_postal . " " . $ville;
    $denomination_sociale = $vendeur["denomination_sociale"];
    $siege_social = $vendeur["siege_social"];

    $statement = NULL;
    // Initialisation des Tableaux et totaux
    $Tout_Les_Jeux = [];
    $Jeux_vendus = [];
    $Jeux_rendus = [];
    $Jeux_donnes = [];
    $Remboursements = [];
    $Dons = [];
    $TOTAL_COMMISSIONS_TTC = 0;
    $TOTAL_TVA_COMMISSIONS = 0;
    $TOTAL_PV_NV_VENTES = 0;
    $TOTAL_PV_NV_DEPOTS = 0;
    $TOTAL_PV_NV_INVENDUS = 0;
    $TOTAL_PV_NV_DONS_JEUX = 0;
    $TOTAL_PV_PUBLIC_VENTES = 0;
    $TOTAL_PV_NV_INVENDUS_DONS = 0;
    $TOTAL_MONTANTS_VERSEMENTS = 0;
    $TOTAL_MONTANTS_DONS = 0;

    $arrayStatuts = '2,3,4,5,6'; // Les autres statuts ne nous intéressent pas pour les factures
    try {
        $statement = $pdo->prepare($SQL_5_getlistejeux . $SQL_5_1_whereVendeur . " AND v_bourse_liste.id_statut IN ($arrayStatuts) AND" . $SQL_5_6_whereAnnee . $SQL_5_7_orderBy);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $statement->execute([
        'idVendeur' => $id_vendeur
    ]);
    while ($res = $statement->fetch()) {
        $res = array_map(function($value) {
            return html_entity_decode($value ?? '', ENT_QUOTES, 'UTF-8');
        }, $res);
        
        $commission = ceil($res["vendu"] * 1 / 6); // On caclule la commission : 1/6 arrondi au dessus du prix de vente
        $rendu = $res['vendu'] - $commission; // On calcule le prix rendu (Net vendeur)
        $Tout_Les_Jeux[] = [
            $res["code_barre"],
            $res["nj"],
            $res["date_reception"],
            $rendu . " €",
            $res["statut"],
            $res["date_sortie_stock"]
        ];
        $TOTAL_PV_NV_DEPOTS += $rendu;
        if ($res["id_statut"] == 2) {
            $TOTAL_PV_NV_INVENDUS_DONS += $rendu;
            $TOTAL_PV_NV_DONS_JEUX += $rendu;
        } elseif ($res["id_statut"] == 3) {
            $tva = round($commission * 1 / 6, 2); // On calcule la TVA : 1/6 du total arrondi au plus proche, au centime près
            $Jeux_vendus[] = [
                $res["code_barre"],
                $res["vendu"] . " €",
                ($commission - $tva) . " €",
                $tva . " €",
                $commission . " €",
                $rendu . " €"
            ];
            $TOTAL_COMMISSIONS_TTC += $commission;
            $TOTAL_TVA_COMMISSIONS += $tva;
            $TOTAL_PV_NV_VENTES += $rendu;
            $TOTAL_PV_PUBLIC_VENTES += $res["vendu"];
        } elseif ($res["id_statut"] == 4) {
            $Jeux_rendus[] = [
                $res["code_barre"],
                $rendu . " €",
                $res["date_sortie_stock"]
            ];
            $TOTAL_PV_NV_INVENDUS += $rendu;
        } elseif ($res["id_statut"] == 6) {
            $Jeux_donnes[] = [
                $res["code_barre"],
                $rendu . " €",
                $res["date_sortie_stock"]
            ];
            $TOTAL_PV_NV_DONS_JEUX += $rendu;
        } 
    }
    // On trie le tableau général en fonction du Statut :
    // Extraire le tableau des statuts
    $statuts = array_column($Tout_Les_Jeux, 4);

    // Trier les deux tableaux en fonction du tableau des statuts
    array_multisort($statuts, SORT_ASC, $Tout_Les_Jeux);

    // Réorganiser les clés du tableau trié
    $Tout_Les_Jeux = array_values($Tout_Les_Jeux);
    
    // On récupère les infos de remboursements
    try {
        $statement = $pdo->prepare($SQL_46_get_remboursement);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $statement->execute([
        'id' => $id_vendeur
    ]);
    while ($res = $statement->fetch()) {
        $TOTAL_MONTANTS_VERSEMENTS += $res["montant_remb"];
        $Remboursements[] = [
            $res["date_remb"],
            $res["type_remb"],
            $res["montant_remb"]." €"
        ];
    }

    // On récupère les infos de dons
    try {
        $statement = $pdo->prepare($SQL_47_get_dons);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $statement->execute([
        'id' => $id_vendeur,
        'type' => "Non remboursement"
    ]);
    while ($res = $statement->fetch()) {
        $TOTAL_MONTANTS_DONS += $res["montant_don"];
        $Dons[] = [
            $res["date_don"],
            $res["montant_don"]." €"
        ];
    }
    $TOTAL_COMMISSIONS_HT = $TOTAL_COMMISSIONS_TTC - $TOTAL_TVA_COMMISSIONS;
    $TOTAL_CREDIT = $TOTAL_PV_NV_DEPOTS + $TOTAL_PV_PUBLIC_VENTES;
    $TOTAL_DEBIT = $TOTAL_PV_NV_VENTES + $TOTAL_PV_NV_INVENDUS + $TOTAL_PV_NV_DONS_JEUX + $TOTAL_MONTANTS_VERSEMENTS + $TOTAL_COMMISSIONS_TTC;
    $SOLDE = $TOTAL_CREDIT - $TOTAL_DEBIT - $TOTAL_MONTANTS_DONS;

    if ($Tout_Les_Jeux!=[]){
        $Tout_Les_Jeux[] = ["Total","","",$TOTAL_PV_NV_DEPOTS." €","",""];
    }
    if ($Jeux_vendus!=[]){
        $Jeux_vendus[] = ["Total",$TOTAL_PV_PUBLIC_VENTES." €",$TOTAL_COMMISSIONS_HT." €",$TOTAL_TVA_COMMISSIONS." €",$TOTAL_COMMISSIONS_TTC." €",$TOTAL_PV_NV_VENTES." €"];
    }
    if ($Jeux_rendus!=[]){
        $Jeux_rendus[] = ["Total",$TOTAL_PV_NV_INVENDUS." €",""];
    }
    if ($Jeux_donnes!=[]){
        $Jeux_donnes[] = ["Total",$TOTAL_PV_NV_DONS_JEUX." €",""];
    }
    if ($Remboursements!=[]){
        $Remboursements[] = ["Total","",$TOTAL_MONTANTS_VERSEMENTS." €"];
    }
    if ($Dons!=[]){
        $Dons[] = ["Total",$TOTAL_MONTANTS_DONS." €"];
    }

    /////////////////////
    // Création du PDF //
    /////////////////////
    require_once ("PDF.php");
    
    
    // On détermine le numéro de la facture : 4 chiffres, que des 0 suivis par l'id vendeur :
    $NUMERO_FACTURE = $id_vendeur;
    while (strlen($NUMERO_FACTURE) < 4) {
        $NUMERO_FACTURE = "0" . $NUMERO_FACTURE;
    }
    
    // Création du PDF à partir de la classe dérivée
    $pdf = new PDF();
    $pdf->AliasNbPages();
    // Définition du titre de la page
    $pdf->title = mb_convert_encoding("Facture faite à Parthenay le ", 'ISO-8859-15', 'UTF-8') . date("d/m/Y");
    // Définition de l'auteur
    $pdf->SetAuthor("Association Woopy On Off");
    // Définition du titre du document
    $nomFichierSanitize = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower(str_replace(' ', '_', $nom)));
    $name = "facture_" . $nomFichierSanitize . "_" . $NUMERO_FACTURE . "_" . date('d_m_y');
    
    $pdf->SetTitle($name);
    $pdf->AddPage();
    $pdf->SetFont('Blogger', '', 11);
    // Création du cadre de gauche et de son entête
    $pdf->Cell(80, 8, mb_convert_encoding("Intermédiaire de vente :", 'ISO-8859-15', 'UTF-8'), 0, 2);
    $pdf->Cell(80, 36, "", 1, 2);

    // On enregistre la position de fin de cadre pour savoir se repositionner sur le cadre le plus bas
    $savey = $pdf->GetY();
    // On remonte dans le cadre
    $pdf->SetXY(15, 60);
    // On écrit les infos de l'intermédiaire de vente
    $pdf->Cell(70, 8, mb_convert_encoding("Association Woopy On Off", 'ISO-8859-15', 'UTF-8'), "B", 2);
    $pdf->Cell(70, 8, mb_convert_encoding("2 rue de la citadelle", 'ISO-8859-15', 'UTF-8'), "B", 2);
    $pdf->Cell(70, 8, mb_convert_encoding("79200 Parthenay", 'ISO-8859-15', 'UTF-8'), "B", 2);
    $pdf->Cell(70, 8, mb_convert_encoding("Siret 793 410 309 00017", 'ISO-8859-15', 'UTF-8'), "B", 1);
    // On remonte et on se décale pour faire le cadre de droite
    $pdf->SetXY(100, 50);
    // Création de l'entête du cadre de droite
    $pdf->Cell(100, 8, mb_convert_encoding("Déposant :", 'ISO-8859-15', 'UTF-8'), 0, 2);
    // On enregistre les coordonnées pour faire le cadre a posteriori
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    // On se positionne dans le cadre
    $pdf->SetXY(105, 60);
    $nb_lignes = 0; // Stock le nombre de lignes pour savoir la taille du cadre

    // On écrit les infos du vendeur dans le cadre de droite
    $nb_lignes = $pdf->EcrireSurPlusieursLignes($nom . ' ' . $prenom, 90, $nb_lignes);
    $nb_lignes = $pdf->EcrireSurPlusieursLignes($adresseComplete, 90, $nb_lignes);

    if ($denomination_sociale != "") {
        $nb_lignes = $pdf->EcrireSurPlusieursLignes("Pour " . $denomination_sociale, 90, $nb_lignes);
    }
    if ($siege_social != "") {
        $nb_lignes = $pdf->EcrireSurPlusieursLignes($siege_social, 90, $nb_lignes);
    }
    $pdf->SetXY($x, $y);
    $pdf->Cell(100, 8 * $nb_lignes + 4, "", 1, 1);

    // On se repositionne en fonction du cadre le plus grand
    if ($savey < $pdf->GetY()) {
        $pdf->SetY($pdf->GetY());
    } else {
        $pdf->SetY($savey);
    }
    $pdf->Ln(5);
    $pdf->Cell(0, 8, mb_convert_encoding('Bourse aux jeux - Vente au déballage du 09/07/25 au 20/07/25', 'ISO-8859-15', 'UTF-8'), 0, 1);
    $pdf->Cell(0, 8, mb_convert_encoding('A Parthenay (79200) organisé par Association Woopy On Off', 'ISO-8859-15', 'UTF-8'), 0, 1);
    $pdf->Cell(0, 8, mb_convert_encoding('TVA non applicable, article 293 B du CGI', 'ISO-8859-15', 'UTF-8'), 0, 1);
    $pdf->Cell(0, 8, mb_convert_encoding('Facture n°' . $NUMERO_FACTURE, 'ISO-8859-15', 'UTF-8'), 0, 1);
    $pdf->Cell(0, 8, mb_convert_encoding('Échéance de paiement : Acquitée', 'ISO-8859-15', 'UTF-8'), 0, 1);
    // On mets le tableau général de ce qu'on facture :
    $col_titles = array(
        "Objet",
        "Montant des ventes effectuées TTC",
        "Commission HT",
        "T.V.A (20%)",
        "Commission TTC"
    );
    $col_widths = array(
        50,
        50,
        30,
        30,
        30
    );
    $table_content = array(
        array(
            "Mise en dépôt-vente de jeux de société",
            $TOTAL_PV_NV_VENTES . "€",
            $TOTAL_COMMISSIONS_HT . "€",
            $TOTAL_TVA_COMMISSIONS . "€",
            $TOTAL_COMMISSIONS_TTC . "€"
        )
    );
    $pdf->Table($col_titles, $col_widths, $table_content);
    $pdf->Ln(5);

    $titre = 'État vendeur :';
    // On mets le tableau bilan :
    $col_titles = array(
        "",
        "CREDIT",
        "DEBIT"
    );
    $col_widths = array(
        90,
        50,
        50
    );
    $table_content = array(
        // [
        //     "Valorisation net vendeur des jeux de société déposés par $prenom $nom",
        //     $TOTAL_PV_NV_DEPOTS . "€",
        //     ""
        // ],
        // [
        //     "Valorisation net vendeur des jeux de $prenom $nom vendus",
        //     "",
        //     $TOTAL_PV_NV_VENTES . "€"
        // ],
        // [
        //     "Valorisation net vendeur des jeux de $prenom $nom invendus et rendus",
        //     "",
        //     $TOTAL_PV_NV_INVENDUS . "€"
        // ],
        // [
        //     "Valorisation des jeux de $prenom $nom déclarés comme don à l'association par $prenom $nom*",
        //     "",
        //     $TOTAL_PV_NV_DONS_JEUX . "€"
        // ],
        [
            "Sommes perçues par l'association Woopy On Off pour le compte de $prenom $nom au titre des ventes réalisées",
            $TOTAL_PV_PUBLIC_VENTES . "€",
            ""
        ],
        [
            "Sommes restituées par l'association Woopy On Off à $prenom $nom pendant le festival",
            "",
            $TOTAL_MONTANTS_VERSEMENTS . "€"
        ],
        [
            "Sommes prélevées sur les ventes réalisées pour le compte de $prenom $nom",
            "",
            $TOTAL_COMMISSIONS_TTC . "€"
        ],
        [
            "Sommes déclarées comme don à l'association par $prenom $nom",
            "",
            $TOTAL_MONTANTS_DONS . "€"
        ],
        [
            "Solde en euros",
            "",
            $SOLDE . "€"
        ]
    );
    $pdf->Table($col_titles, $col_widths, $table_content, $titre);
    $pdf->SetFont("Blogger", "I", 8);
    $pdf->MultiCell(0, 4, mb_convert_encoding("* dont " . $TOTAL_PV_NV_INVENDUS_DONS . "€ de jeux non récupérés en fin de festival. Conformément au règlement de la bourse aux jeux de l'association Woopy On Off, ces jeux sont considérés comme un don à l'association Woopy On Off.", 'ISO-8859-15', 'UTF-8'), 0, 1, "C");

    // On passe à la page suivante, en format paysage pour les grands tableaux de liste de jeux
    $pdf->AddPage("L");

    // Tableau avec l'ensemble des jeux et leur statut en fin de festival
    $titre = "Jeux mis en dépôt vente à l'association Woopy On Off par $prenom $nom";
    $col_titles = array(
        "Code-Barre",
        "Nom du jeu",
        "Reçu le",
        "Prix de vente net vendeur",
        "Statut du jeu en fin de festival",
        "Sortie de stock le"
    );
    $col_widths = array(
        40,
        65,
        55,
        30,
        30,
        55
    );
    $table_content = $Tout_Les_Jeux; // Contient l'ensemble des jeux du vendeur, récupérés en début via SQL
    $pdf->Table($col_titles, $col_widths, $table_content, $titre);
    $pdf->Ln(10);

    // Tableau avec l'ensemble des jeux vendus et le détail de la commission
    $titre = "Jeux mis en dépôt vente et vendus lors de l'association Woopy On Off par $prenom $nom";
    $col_titles = array(
        "Code-Barre",
        "Prix de vente public",
        "Commission HT**",
        "TVA (20%)",
        "Commission TTC*",
        "Prix de vente Net Vendeur"
    );
    $col_widths = array(
        40,
        30,
        30,
        30,
        30,
        30
    );
    $table_content = $Jeux_vendus; // Contient l'ensemble des jeux vendus du vendeur, récupérés en début via SQL
    $pdf->Table($col_titles, $col_widths, $table_content, $titre, 53.5); // On mets un décalage pour centrer le tableau
    $pdf->SetFont("Blogger", "I", 8);
    $pdf->MultiCell(0, 4, mb_convert_encoding("*La commission TTC est calculée sur chaque transaction comme 20 % du prix de vente net vendeur, arrondie à l'euro supérieur, soit 16,67% du prix de vente « public ».
**La commission HT est calculée comme 83,33% de la commission TTC", 'ISO-8859-15', 'UTF-8'), 0, 1, "C");
    $pdf->Ln(10);

    // Tableau avec l'ensemble des jeux invendus et donc rendus
    $titre = "Jeux mis en dépôt vente et invendus lors de l'association Woopy On Off, restitués à $prenom $nom";
    $col_titles = array(
        "Code-Barre",
        "Prix de vente Net Vendeur",
        "Date et heure de restitution"
    );
    $col_widths = array(
        40,
        30,
        60
    );
    $table_content = $Jeux_rendus; // Contient l'ensemble des jeux rendus du vendeur, récupérés en début via SQL
    $pdf->Table($col_titles, $col_widths, $table_content, $titre, 83.5); // On mets un décalage pour centrer le tableau

    $pdf->AddPage("L");
    $pdf->SetFont('Blogger', 'B', 13);
    // Tableau avec l'ensemble des jeux invendus et donc rendus
    $pdf->Cell(0, 0, mb_convert_encoding("Dons à l'association Woopy On Off", 'ISO-8859-15', 'UTF-8'), 0, 1, "C");
    $pdf->Ln(7);

    $y = $pdf->GetY();
    $pdf->SetX(19); // Permet d'harmoniser les écarts
    $titre = "En numéraire";
    $col_titles = array(
        "Date et heure du don",
        "Montant"
    );
    $col_widths = array(
        60,
        30
    );
    $table_content = $Dons; // Contient l'ensemble des dons du vendeur, récupérés en début via SQL
    $pdf->Table($col_titles, $col_widths, $table_content, $titre, 19); // On mets un décalage
    $savey = $pdf->GetY();
    $pdf->Ln(7);

    $pdf->SetY($y);
    $titre = "En boites de jeux";
    $col_titles = array(
        "Code-barre",
        "Prix de vente net vendeur",
        "Date et heure du don"
    );
    $col_widths = array(
        40,
        50,
        60
    );
    $table_content = $Jeux_donnes; // Contient l'ensemble des jeux donnés du vendeur, récupérés en début via SQL
    $pdf->Table($col_titles, $col_widths, $table_content, $titre, 129); // On mets un décalage
    if ($savey < $pdf->GetY()) {
        $pdf->SetY($pdf->GetY());
    } else {
        $pdf->SetY($savey);
    }
    $pdf->Ln(10);

    $titre = "Versements effectués à $prenom $nom";
    $col_titles = array(
        "Date et heure de remboursement",
        "Type de remboursement",
        "Montant du remboursement"
    );
    $col_widths = array(
        60,
        50,
        50
    );
    $table_content = $Remboursements; // Contient l'ensemble des remboursements du vendeur, récupérés en début via SQL
    $pdf->Table($col_titles, $col_widths, $table_content, $titre, 68.5); // On mets un décalage pour centrer le tableau
    $pdf->Ln(10);
    // $pdf->Output('F', $rep_root.$rep_facture."/".$name.'.pdf'); // Enregistrez le fichier PDF sur le serveur


    // C:\xampp\htdocs\bourse_flip\flip_baj\main\pdf\pdf\facture
    $directoryPath = $_SERVER['DOCUMENT_ROOT'] . '/FlipBAJ/flip_baj/main/pdf/pdf/facture/2025/';

    if (!file_exists($directoryPath)) {
        mkdir($directoryPath, 0777, true); // Create directory if not exists
    }
    $filePath = $directoryPath . $name.'.pdf';
    //ajout
    echo "<p>Chemin où le PDF est censé être enregistré : $filePath</p>";

    $pdf->Output('F', $filePath); // Enregistrez le fichier PDF sur le serveur




    try {
        $statement = $pdo->prepare($SQL_14_pdfadd);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $statement->execute(['id_utilisateur'=>$id_vendeur,'nom_fichier'=>$name,'type'=>'facture']);
    
    $statement=NULL;
    $pdo=NULL;
}
?>