<?php
namespace flip_baj\main;

use Nelexa\Zip\ZipFile;

use PDO;

require '../../vendor/autoload.php';
include 'pdo_connect.php';

try {
    $pdo->exec("SET sql_mode='NO_ZERO_DATE,NO_ZERO_IN_DATE'");
} catch (PDOException $e) {
    echo 'SQL Mode setting failed: ' . $e->getMessage();
    exit();
}

$zipFileName = 'export_base_'.date("Y_m_d_H_i_s").'.zip';

$tempZipFile = tempnam(sys_get_temp_dir(), 'exported_data_');
$zip = new ZipFile();
try {
    // Récupérer la liste des tables dans la base de données
    $query = $pdo->prepare("SHOW TABLES");
    $query->execute();
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);
    
    // Exporter chaque table en CSV
    foreach ($tables as $table) {
        $selectQuery = $pdo->prepare("SELECT * FROM $table");
        $selectQuery->execute();
        $result = $selectQuery->fetchAll(PDO::FETCH_ASSOC);
        $zip->addFromString("$table.csv", csvFromArray($result));
    }

    $zip->saveAsFile($tempZipFile)->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    readfile($tempZipFile);
    
    unlink($tempZipFile);
} catch (Exception $e) {
    echo 'Erreur lors de la création du fichier ZIP: ',  $e->getMessage();
}

function csvFromArray($data) {
    $output = fopen('php://temp', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) {
            foreach ($row as &$value) {
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            }
            fputcsv($output, $row);
        }
    }
    rewind($output);
    return stream_get_contents($output);
}
?>
