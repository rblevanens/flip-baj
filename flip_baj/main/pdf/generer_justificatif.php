<?php 
namespace flip_baj\main\pdf;

use PDOException;

function GenererJustificatif($id_acheteur)
{
    include ('../pdo_connect.php');
    include ('../constantes.php');
    if (is_null($pdo)) {
        die('Could not connect to database!');
    }

    try {
        $statement = $pdo->prepare($SQL_45_get_infos_acheteur);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $statement->execute(['id_acheteur' => $id_acheteur]);
    if (!$acheteur = $statement->fetch()){
        echo("Die");
        return;
    }

    $acheteur = array_map('html_entity_decode', $acheteur);
    $nom = $acheteur["nom"];
    $prenom = $acheteur["prenom"];
    $code_postal = $acheteur["code_postal"];
    $ville = $acheteur["ville"];
    $adresse = $acheteur["adresse"];
    $adresseComplete = "Domicilié au " . $adresse . ", " . $code_postal . " " . $ville;
    $raison_sociale = $acheteur["raison_sociale"];
    $siret = $acheteur["siret"];
    
    $JEUX_ACHETES = [];
    $TOTAL_PRIX = 0;
    try {
        $statement = $pdo->prepare($SQL_44_getlistejeuxtransaction);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $statement->execute(['id_acheteur' => $id_acheteur]);
    while ($jeux = $statement->fetch()){
        $TOTAL_PRIX += $jeux["vendu"];
        $JEUX_ACHETES[] = [
            mb_convert_encoding($jeux["date_paiement"], 'ISO-8859-15', 'UTF-8'),
            mb_convert_encoding($jeux["code_barre"], 'ISO-8859-15', 'UTF-8'),
            mb_convert_encoding($jeux["nom_jeu"], 'ISO-8859-15', 'UTF-8'),
            utf8_decode($jeux["type_paiement"]),
            mb_convert_encoding($jeux["vendu"] . " EUR", 'ISO-8859-15', 'UTF-8')
        ];
    }
    $JEUX_ACHETES[] = [
        mb_convert_encoding("Total TTC", 'ISO-8859-15', 'UTF-8'),
        mb_convert_encoding("", 'ISO-8859-15', 'UTF-8'),
        mb_convert_encoding("", 'ISO-8859-15', 'UTF-8'),
        mb_convert_encoding("", 'ISO-8859-15', 'UTF-8'),
        mb_convert_encoding($TOTAL_PRIX . " EUR", 'ISO-8859-15', 'UTF-8')
    ];

    $DONS = [];
    $TOTAL_DONS = 0;
    try {
        $statement = $pdo->prepare($SQL_47_get_dons);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $statement->execute([
        'id' => $id_acheteur,
        'type' => "vente"
    ]);
    while ($dons = $statement->fetch()){
        $TOTAL_DONS += $dons["montant_don"];
        $DONS[] = [
            mb_convert_encoding($dons["date_don"], 'ISO-8859-15', 'UTF-8'),
            mb_convert_encoding($dons["montant_don"] . " EUR", 'ISO-8859-15', 'UTF-8')
        ];
    }
    if ($DONS != []) {
        $DONS[] = [
            mb_convert_encoding("Total", 'ISO-8859-15', 'UTF-8'),
            mb_convert_encoding($TOTAL_DONS . " EUR", 'ISO-8859-15', 'UTF-8')
        ];
    }

    require_once ("PDF.php");

    $NUMERO_JUSTIFICATIF = str_pad($id_acheteur, 4, "0", STR_PAD_LEFT);

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->title = mb_convert_encoding("Justificatif fait à Parthenay le ".date("d/m/Y"), 'ISO-8859-15', 'UTF-8');
    $pdf->SetAuthor("Association Woopy On Off");
    $name = "justificatif_" . $NUMERO_JUSTIFICATIF . "_" . date('d_m_y');
    $pdf->SetTitle($name);
    $pdf->AddPage();
    $pdf->SetFont('Blogger', '', 11);

    $pdf->Cell(100, 8, mb_convert_encoding("Acheteur :", 'ISO-8859-15', 'UTF-8'), 0, 2);
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $pdf->SetX(15);
    $nb_lignes = 0;
    $nb_lignes = $pdf->EcrireSurPlusieursLignes($nom . ' ' . $prenom, 90, $nb_lignes);
    $nb_lignes = $pdf->EcrireSurPlusieursLignes($adresseComplete, 90, $nb_lignes);
    if ($raison_sociale != "") {
        $nb_lignes = $pdf->EcrireSurPlusieursLignes("Pour " . $raison_sociale, 90, $nb_lignes);
    }
    if ($siret != "") {
        $nb_lignes = $pdf->EcrireSurPlusieursLignes("Siret : " . $siret, 90, $nb_lignes);
    }

    $pdf->SetXY($x, $y);
    $pdf->Cell(100, 8 * $nb_lignes + 4, "", 1, 1);
    $savey = $pdf->GetY();

    $pdf->SetXY(120, 50);
    $pdf->Cell(80, 8, mb_convert_encoding("Intermédiaire de vente :", 'ISO-8859-15', 'UTF-8'), 0, 2);
    $pdf->Cell(80, 36, "", 1, 2);
    $pdf->SetXY(125, 60);
    $pdf->Cell(70, 8, mb_convert_encoding("Association Woopy On Off", 'ISO-8859-15', 'UTF-8'), "B", 2);
    $pdf->Cell(70, 8, mb_convert_encoding("2 rue de la citadelle", 'ISO-8859-15', 'UTF-8'), "B", 2);
    $pdf->Cell(70, 8, mb_convert_encoding("79200 Parthenay", 'ISO-8859-15', 'UTF-8'), "B", 2);
    $pdf->Cell(70, 8, mb_convert_encoding("Siret 793 410 309 00017", 'ISO-8859-15', 'UTF-8'), "B", 1);

    $pdf->SetY(max($savey, $pdf->GetY()));
    $pdf->Ln(5);
    $pdf->Cell(0, 8, mb_convert_encoding("Bourse aux jeux - Vente au déballage du 09/07/25 au 20/07/25", 'ISO-8859-15', 'UTF-8'), 0, 1);
    $pdf->Cell(0, 8, mb_convert_encoding("A Parthenay (79200) organisé par Association Woopy On Off", 'ISO-8859-15', 'UTF-8'), 0, 1);
    $pdf->Cell(0, 8, mb_convert_encoding("Justificatif n°" . $NUMERO_JUSTIFICATIF, 'ISO-8859-15', 'UTF-8'), 0, 1);

    $titre = "Achats";
    $col_titles = ["Date", "Code-barre", "Nom du jeu", "Moyen de paiement", "Prix*"];
    $col_widths = [50, 25, 65, 30, 20];
    $table_content = $JEUX_ACHETES;
    $pdf->Table($col_titles, $col_widths, $table_content, $titre);

    $pdf->SetFont("Blogger", "I", 8);
    $pdf->MultiCell(0, 4, mb_convert_encoding("* s'agissant d'un dépôt vente de jeux d'occasion, ces transactions ne sont pas soumises à TVA.", 'ISO-8859-15', 'UTF-8'), 0, 1, "C");

    if ($DONS != []) {
        $titre = "Dons";
        $col_titles = ["Date et heure", "Montant"];
        $col_widths = [60, 30];
        $table_content = $DONS;
        $pdf->Table($col_titles, $col_widths, $table_content, $titre, 60);
    }

    // Répertoire 2025
$directoryPath = $_SERVER['DOCUMENT_ROOT'] . '/FlipBAJ/flip_baj/main/pdf/pdf/justificatif/2025/';
if (!file_exists($directoryPath)) {
    mkdir($directoryPath, 0777, true);
}

// Création du nom du fichier : justificatif_nom_0004_29_07_24.pdf
$nom_sans_espace = preg_replace('/\s+/', '_', strtolower($nom));
$date_string = date('d_m_y');
$filename = "justificatif_" . $nom_sans_espace . "_" . $NUMERO_JUSTIFICATIF . "_" . $date_string . ".pdf";

// Chemin complet
$filePath = $directoryPath . $filename;

// Générer le PDF
$pdf->Output('F', $filePath);


    try {
        $statement = $pdo->prepare($SQL_14_pdfadd);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $statement->execute([
        'id_utilisateur' => $id_acheteur,
        'nom_fichier' => $filename, 
        'type' => 'justificatif'
    ]);

    $statement = NULL;
    $pdo = NULL;
}
?>
