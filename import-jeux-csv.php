<?php
namespace flip_baj\main\ajax;

include('../pdo_connect.php');
include('../constantes.php');
require_once __DIR__ . '/../pdf/fpdf/fpdf_extended.php';

header('Content-Type: application/json');
$response = ['message1' => '', 'message2' => '0'];

function getNomPrenomUtilisateur(\PDO $pdo, $id) {
    $stmt = $pdo->prepare("SELECT nom, prenom FROM al_bourse_users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['nom' => 'Inconnu', 'prenom' => ''];
}

function genererCodeBarreUnique(\PDO $pdo): string {
    $depart = 6001;
    $num = $depart;
    while (true) {
        $code = (string)$num;
        $sql = 'SELECT code_barre FROM v_bourse_liste WHERE code_barre = :code_barre AND annee = ' . annee_base;
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':code_barre', 'Festival_' . $code);
        $stmt->execute();
        if (!$stmt->fetchColumn()) return $code;
        $num++;
        if ($num > 99999) throw new \Exception("Aucun code-barre dispo");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileCSV']) && isset($_POST['idVendeurImport'])) {
    $idVendeur = intval($_POST['idVendeurImport']);
    $annee = annee_base;
    $fileTmpPath = $_FILES['fileCSV']['tmp_name'];

    if (($handle = fopen($fileTmpPath, 'r')) !== false) {
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $inserted = 0;
        $ignored = 0;
        $etiquettes = [];

        fgetcsv($handle, 1000, ';'); 

        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            $data = array_map(function ($v) {
                return mb_convert_encoding($v, 'UTF-8', mb_detect_encoding($v, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true));
            }, $data);


            if (count($data) >= 3) {
                $nom_jeu = $data[1];
                $prix = floatval(str_replace(',', '.', $data[2]));

                if ($nom_jeu === '' || $prix <= 0) {
                    $ignored++;
                    continue;
                }

                try {
                    $code_num = genererCodeBarreUnique($pdo);
                    $code_barre = 'Festival_' . $code_num;

                    // Vérification si ce code existe déjà
                    $SQL_4_checkcodebarre = '
                        SELECT code_barre 
                        FROM v_bourse_liste 
                        WHERE code_barre = :code_barre 
                          AND annee = ' . annee_base;
                    $checkStmt = $pdo->prepare($SQL_4_checkcodebarre);
                    $checkStmt->bindValue(':code_barre', $code_barre);
                    $checkStmt->execute();
                    if ($checkStmt->fetchColumn()) {
                        $ignored++;
                        continue;
                    }

                } catch (\Exception $e) {
                    $response['message1'] = $e->getMessage();
                    echo json_encode($response);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO al_bourse_liste (
                    id_utilisateur, nom_jeu, prix, code_barre, statut, vigilance, id_depot, date_reception, annee
                ) VALUES (?, ?, ?, ?, 2, 0, '', NOW(), ?)");
                $stmt->execute([$idVendeur, $nom_jeu, $prix, $code_barre, $annee]);
                $inserted++;

                $etiquettes[] = [
                    'nom' => $nom_jeu,
                    'prix' => $prix,
                    'code' => $code_num, 
                ];
            } else {
                $ignored++;
            }
        }

        fclose($handle);

        $response['message1'] = $inserted > 0
            ? "$inserted jeux importés. $ignored ligne(s) ignorée(s)."
            : "Aucun jeu importé. $ignored ligne(s) incorrecte(s).";
        $response['message2'] = $inserted > 0 ? '1' : '0';

        if (!empty($etiquettes)) {
            $pdf = new \FPDF_Extended('P', 'mm', 'A4');
            $pdf->SetFont('Arial', '', 8);
            $pdf->AddPage();

            $labelWidth = 40;
            $labelHeight = 23;
            $cols = 4;
            $rows = 8;
            $marginX = 10;
            $marginY = 25;
            $spaceX = 9;
            $spaceY = 9;
            $i = 0;

            foreach ($etiquettes as $jeu) {
                $col = $i % $cols;
                $row = floor($i / $cols) % $rows;

                $x = $marginX + $col * ($labelWidth + $spaceX);
                $y = $marginY + $row * ($labelHeight + $spaceY);

                if ($col === 0 && $row === 0 && $i !== 0) $pdf->AddPage();

                // Nom du jeu (16 caractères MAX, au-dessus du cadre)
                $pdf->SetXY($x, $y - 5);
                $pdf->SetFont('Arial', 'B', 6.5);
                $nom_formate = strtoupper(mb_substr($jeu['nom'], 0, 16));
                $pdf->Cell($labelWidth, 3, $nom_formate, 0, 0, 'C');

                //  Cadre
                $pdf->RoundedRect($x, $y, $labelWidth, $labelHeight, 2, 'D');

                // Code-barre
                $pdf->SetXY($x, $y + 4);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell($labelWidth, 5, $jeu['code'], 0, 0, 'C');

                //  Prix
                $pdf->SetXY($x, $y + 10);
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell($labelWidth, 5, number_format($jeu['prix'], 2, ',', ' ') . ' EUR', 0, 0, 'C');

                $i++;
            }

            $infos = getNomPrenomUtilisateur($pdo, $idVendeur);
            $nom_sanitized = strtolower(str_replace(' ', '_', $infos['prenom'] . '_' . $infos['nom']));
            $pdfName = $nom_sanitized . '_etiquetage.pdf';
            $pdfPath = __DIR__ . '/../tmp/' . $pdfName;

            if (!is_dir(dirname($pdfPath))) mkdir(dirname($pdfPath), 0777, true);
            $pdf->Output('F', $pdfPath);
            $response['pdf'] = $pdfName;
        }
    } else {
        $response['message1'] = "Erreur d'ouverture du fichier.";
    }
} else {
    $response['message1'] = "Fichier ou vendeur manquant.";
}

echo json_encode($response);
exit;



/* 
foreach ($etiquettes as $jeu) {
    $col = $i % $cols;
    $row = floor($i / $cols) % $rows;

    $x = $marginX + $col * ($labelWidth + $spaceX);
    $y = $marginY + $row * ($labelHeight + $spaceY);

    if ($col === 0 && $row === 0 && $i !== 0) $pdf->AddPage();

    // Cadre
    $pdf->RoundedRect($x, $y, $labelWidth, $labelHeight, 2, 'D');

    // Nom du jeu 
    $pdf->SetXY($x, $y + 1.5);
    $pdf->SetFont('Arial', 'B', 6.5);
    $nom_formate = strtoupper(mb_substr($jeu['nom'], 0, 16));
    $pdf->Cell($labelWidth, 3, $nom_formate, 0, 0, 'C');

    // Code-barre 
    $pdf->SetXY($x, $y + 7);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($labelWidth, 5, $jeu['code'], 0, 0, 'C');

    // Prix 
    $pdf->SetXY($x, $y + 13);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($labelWidth, 5, number_format($jeu['prix'], 2, ',', ' ') . ' EUR', 0, 0, 'C');

    $i++;
}
 */