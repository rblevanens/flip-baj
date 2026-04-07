<?php
namespace flip_baj\main\pdf;

use FPDF;
require ('fpdf/fpdf.php');

class PDF extends FPDF
{

    public $title = '';

    // En-tête
    function Header()
    {
        if ($this->PageNo() == 1) {
            // Première page : On paramètre le doc + Entête
            // On rajoute la police de la charte du festival
            $this->AddFont('Blogger', '', 'Blogger Sans.php');
            $this->AddFont('Blogger', 'B', 'Blogger Sans-Bold.php');
            $this->AddFont('Blogger', 'I', 'Blogger Sans-Italic.php');
            $this->AddFont('Blogger', 'BI', 'Blogger Sans-Bold Italic.php');

            // Banière
            // $this->Image("../img/entete_facture.jpg", 10, 10, 190, 30);
            // Saut de ligne
            $this->Ln(30);

            // Police en Blogger Bold 15
            $this->SetFont('Blogger', 'B', 15);
            // Calcul de la largeur du titre et positionnement
            $w = $this->GetStringWidth($this->title) + 6;
            $this->SetX((210 - $w) / 2);
            // Couleurs du texte
            $this->SetTextColor(13, 110, 253);
            // Titre
            $this->Cell($w, 9, $this->title, 0, 0, 'C');
            // Saut de ligne
            $this->Ln(10);
        } else {
            // Pages suivantes
        }
    }

    // Pied de page
    function Footer()
    {
        // Positionnement à 1,5 cm du bas
        $this->SetY(- 15);
        // Police Arial italique 8
        $this->SetFont('Blogger', 'I', 8);
        // Numéro de page
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    /**
     * Divise une entrée en plusieurs lignes si sa largeur dépasse la taille donnée.
     *
     * @param string $entree
     *            L'entrée à diviser en lignes.
     * @param integer $taille
     *            La taille maximale des lignes.
     * @param integer $nb_lignes
     *            Le nombre de lignes avant l'écriture.
     * @return int Le nombre de lignes après l'écriture.
     */
    function EcrireSurPlusieursLignes($entree, $taille, $nb_lignes, $cadre="B")
    {
        if ($this->GetStringWidth($entree) > $taille) {
            // Divisez l'adresse en mots
            $mots = explode(' ', $entree);

            // Initialise une variable pour contenir les lignes de l'adresse
            $ligne = '';
            // Parcourez chaque mot
            foreach ($mots as $mot) {
                // Vérifiez si l'ajout du mot à la ligne actuelle dépasse la longueur maximale
                if ($this->GetStringWidth($ligne . ' ' . $mot) <= $taille) {
                    // Ajoutez le mot à la ligne actuelle
                    $ligne .= ($ligne ? ' ' : '') . $mot;
                } else {
                    // Si l'ajout du mot dépasse la longueur maximale, ajoutez la ligne actuelle à l'adresse courte
                    $this->Cell($taille, 8, mb_convert_encoding($ligne, 'ISO-8859-15', 'UTF-8'), $cadre, 2);
                    $nb_lignes += 1;
                    // Réinitialisez la ligne avec le mot actuel
                    $ligne = $mot;
                }
            }
            $this->Cell($taille, 8, mb_convert_encoding($ligne, 'ISO-8859-15', 'UTF-8'), $cadre, 2);
            $nb_lignes += 1;
        } else {
            // Si l'adresse est déjà courte, utilisez-la telle quelle
            $this->Cell($taille, 8, mb_convert_encoding($entree, 'ISO-8859-15', 'UTF-8'), $cadre, 2);
            $nb_lignes += 1;
        }
        return $nb_lignes;
    }

    /**
     * Calcule le nombre de lignes nécessaires pour afficher un texte donné dans une cellule.
     *
     * @param float $w
     *            La largeur de la cellule.
     * @param string $txt
     *            Le texte à afficher dans la cellule.
     * @return int Le nombre de lignes nécessaires pour afficher le texte.
     */
    function NbLines($w, $txt)
    {
        // Diviser le texte en mots
        $words = explode(" ", $txt);

        // Initialiser les variables
        $line_width = - $this->GetStringWidth(" "); // On enlève le premier espace
        $nb_lines = 1;

        // Calculer la largeur de chaque mot en comptant l'espace et distribuer les mots sur plusieurs lignes
        foreach ($words as $word) {
            $word_width = $this->GetStringWidth($word) + $this->GetStringWidth(" ");
            // Vérifier si le mot dépasse la largeur de la cellule - les marges
            if ($line_width + $word_width > $w - 2 * $this->cMargin) {
                $nb_lines ++;
                $line_width = - $this->GetStringWidth(" "); // Réinitialiser la largeur de la ligne, on considère qu'il y a 1 espace de -
            }
            $line_width += $word_width;
        }

        return $nb_lines;
    }

    /**
     * Crée un tableau avec des en-têtes de colonnes personnalisés et des données pour chaque ligne.
     * Les couleurs et la police sont définies pour chaque élément du tableau.
     *
     * @param array $col_titles
     *            un tableau contenant les en-têtes pour chaque colonne du tableau
     * @param array $col_widths
     *            un tableau contenant les largeurs de colonne pour chaque colonne
     * @param array $table_content
     *            un tableau multidimensionnel contenant les données à afficher dans le tableau
     * @param string $titre
     *            une chaine de caractères correspondant au titre à mettre sur le tableau (facultatif, par défaut '')
     * @param int $decalage
     *            un décalage optionnel pour aligner le tableau différemment (facultatif, par défaut 10)
     * @return void
     */
    function Table($col_titles, $col_widths, $table_content, $titre = '', $decalage = 10)
    {
        // On se mets en police standard
        $this->SetFont("Blogger", "", 11);
        // Calculer la hauteur maximale d'une cellule
        $line_height = $this->FontSize * 1.5;

        // Nombre de colonnes
        $nb_cols = count($col_titles);
        $max_lines_title = 1; // On initialise la variable

        // On repère la taille des cellules de l'entête du tableau
        for ($i = 0; $i < $nb_cols; $i ++) {
            // Vérifie si la cellule a besoin de plusieurs lignes, et stocke le nombre max de lignes
            $nb_lines = $this->NbLines($col_widths[$i], $col_titles[$i]);
            if ($nb_lines > $max_lines_title) {
                $max_lines_title = $nb_lines;
            }
        }

        // On vérifie d'abord si le tableau entier s'adaptera sur la page actuelle
        $current_y = $this->GetY();
        if (($current_y + ($nb_lines + 3) * $line_height + 25) > $this->GetPageHeight()) {
            if (round($this->GetPageHeight()) == 210) {
                $this->AddPage("L"); // Ajouter une nouvelle page
            } else {
                $this->AddPage("P");
            }

            $current_y = $this->GetY(); // Mettre à jour la position Y actuelle
        }

        $this->SetX($decalage);
        // On écrit le titre du tableau s'il est défini
        if ($titre != '') {
            $this->SetFont('Blogger', 'B', 13);
            $this->Cell(array_sum($col_widths), $line_height, mb_convert_encoding($titre, 'ISO-8859-15', 'UTF-8'), 0, 1, "C");
            $this->SetFont('Blogger', '', 11);
            $this->Ln(5);
            $this->SetX($decalage);
        }

        // Si on veut que le tableau ne soit pas aligné à gauche, on met un décalage en début

        // Dessiner les titres de colonnes
        $this->SetFont("Blogger", "B", 11);
        $this->SetFillColor(13, 110, 253);
        $this->SetTextColor(255, 255, 255);
        for ($i = 0; $i < $nb_cols; $i ++) {
            // Récupérer la position X et Y pour la cellule actuelle, elles permettront de se replacer pour la cellule suivante
            $pos_x = $this->GetX();
            $pos_y = $this->GetY();
            // Récupérer le nombre de lignes nécessaires pour la case actuelle pour savoir quelle hauteur mettre sur chaque ligne
            $nb_lines_loc = $this->NbLines($col_widths[$i], $col_titles[$i]);

            // Dessiner la cellule, la hauteur dépend du nombre de lignes dans la case qui en a le plus et dans la case actuelle
            $this->MultiCell($col_widths[$i], $line_height * ($max_lines_title / $nb_lines_loc), (mb_convert_encoding($col_titles[$i], 'ISO-8859-15', 'UTF-8')), 1, 'C', true);

            // Se replacer en suivant :
            $this->SetXY($pos_x + $col_widths[$i], $pos_y);
        }

        // On reporte le décalage, et on descend d'autant de lignes que ce qu'on a écrit
        $this->SetXY($decalage, $this->GetY() + $max_lines_title * $line_height);

        // Dessiner le contenu du tableau
        $this->SetFont("Blogger", "", 11);
        $this->SetFillColor(159, 248, 276);
        $this->SetTextColor(0, 0, 0);
        $fill = FALSE;

        if ($table_content == []) {
            // S'il n'y a pas de contenu à afficher, écrire "Aucun résultat"
            $this->Cell(array_sum($col_widths), $line_height, mb_convert_encoding("Aucun résultat", 'ISO-8859-15', 'UTF-8'), 1, 0, "C");
            $this->Ln();
        } else {
            foreach ($table_content as $row) {
                $max_lines_per_row = 1; // Initialiser à une ligne pour chaque ligne
                                        // Calculer le nombre maximal de lignes nécessaires pour cette ligne
                $i = 0;
                foreach ($row as $cell) {
                    // Calculer le nombre de lignes nécessaires pour cette cellule
                    $nb_lines = $this->NbLines($col_widths[$i], $row[$i]);
                    // Mettre à jour le nombre maximal de lignes pour cette ligne si nécessaire
                    if ($nb_lines > $max_lines_per_row) {
                        $max_lines_per_row = $nb_lines;
                    }
                    $i += 1;
                }
                // Mettre à jour la position Y actuelle
                $current_y = $this->GetY();
                // Vérifier si le contenu du tableau s'adaptera sur la page actuelle
                if (($current_y + $max_lines_per_row * $line_height + 20) > $this->GetPageHeight()) {
                    if (round($this->GetPageHeight()) == 210) {
                        $this->AddPage("L"); // Ajouter une nouvelle page
                    } else {
                        $this->AddPage("P");
                    }
                    $this->SetX($decalage);
                    $current_y = $this->GetY(); // Mettre à jour la position Y actuelle

                    // Redessiner les en-têtes de colonne (comme au début)
                    $this->SetFont("Blogger", "B");
                    $this->SetFillColor(13, 110, 253);
                    $this->SetTextColor(255, 255, 255);

                    $max_lines_title = 1; // On initialise la variable
                                          // On repère la taille des cellules de l'entête du tableau
                    for ($i = 0; $i < $nb_cols; $i ++) {
                        // Vérifie si la cellule a besoin de plusieurs lignes, et stocke le nombre max de lignes
                        $nb_lines = $this->NbLines($col_widths[$i], $col_titles[$i]);
                        if ($nb_lines > $max_lines_title) {
                            $max_lines_title = $nb_lines;
                        }
                    }
                    for ($i = 0; $i < $nb_cols; $i ++) {
                        // Récupérer la position X et Y pour la cellule actuelle, elles permettront de se replacer pour la cellule suivante
                        $pos_x = $this->GetX();
                        $pos_y = $this->GetY();
                        // Récupérer le nombre de lignes nécessaires pour la case actuelle pour savoir quelle hauteur mettre sur chaque ligne
                        $nb_lines_loc = $this->NbLines($col_widths[$i], $col_titles[$i]);
                        // Si la case est vide, on écrit quand même une ligne, donc le nombre de ligne minimum est 1
                        if ($nb_lines_loc == 0) {
                            $nb_lines_loc = 1;
                        }
                        // Dessiner la cellule, la hauteur dépend du nombre de lignes dans la case qui en a le plus et dans la case actuelle
                        $this->MultiCell($col_widths[$i], $line_height * ($max_lines_title / $nb_lines_loc), (mb_convert_encoding($col_titles[$i], 'ISO-8859-15', 'UTF-8')), 1, 'C', true);

                        // Se replacer en suivant :
                        $this->SetXY($pos_x + $col_widths[$i], $pos_y);
                    }
                    // On reporte le décalage, et on descend d'autant de lignes que ce qu'on a écrit
                    $this->SetXY($decalage, $this->GetY() + $max_lines_title * $line_height);
                    // Dessiner le contenu du tableau
                    $this->SetFont("Blogger", "");
                    $this->SetFillColor(159, 248, 276);
                    $this->SetTextColor(0, 0, 0);
                    $fill = FALSE;
                }
                $i = 0;
                // Dessiner le contenu de la ligne du tableau
                foreach ($row as $cell) {
                    // Calculer le nombre de lignes nécessaires pour cette cellule
                    $nb_lines = $this->NbLines($col_widths[$i], $row[$i]);

                    // Récupérer la position X et Y pour la cellule actuelle, elles permettront de se replacer pour la cellule suivante
                    $pos_x = $this->GetX();
                    $pos_y = $this->GetY();

                    // Si la case est vide, on écrit quand même une ligne, donc le nombre de ligne minimum est 1
                    if ($nb_lines == 0) {
                        $nb_lines = 1;
                    }
                    // Dessiner la cellule, la hauteur dépend du nombre de lignes dans la case qui en a le plus et dans la case actuelle
                    $this->MultiCell($col_widths[$i], $line_height * ($max_lines_per_row / $nb_lines), (mb_convert_encoding($row[$i], 'ISO-8859-15', 'UTF-8')), 1, 'C', $fill);

                    // Se replacer en suivant :
                    $this->SetXY($pos_x + $col_widths[$i], $pos_y);
                    $i += 1;
                }
                // On reporte le décalage, et on descend d'autant de lignes que ce qu'on a écrit
                $this->SetXY($decalage, $this->GetY() + $max_lines_per_row * $line_height);
                $fill = ! $fill;
            }
        }
        // Réinitialiser la couleur de fond
        $this->SetFillColor(255, 255, 255);
    }

    /**
     * Affiche un texte avec une rotation spécifiée.
     *
     * @param float $x
     *            la position horizontale du texte
     * @param float $y
     *            la position verticale du texte
     * @param string $txt
     *            le texte à afficher
     * @param float $txt_angle
     *            l'angle de rotation du texte en degrés
     * @param float $font_angle
     *            l'angle de rotation de la police du texte en degrés (facultatif, par défaut 0)
     * @return void
     */
    function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle = 0)
    {
        $font_angle += 90 + $txt_angle;
        $txt_angle *= M_PI / 180;
        $font_angle *= M_PI / 180;

        $txt_dx = cos($txt_angle);
        $txt_dy = sin($txt_angle);
        $font_dx = cos($font_angle);
        $font_dy = sin($font_angle);

        $s = sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET', $txt_dx, $txt_dy, $font_dx, $font_dy, $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        if ($this->ColorFlag)
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        $this->_out($s);
    }

    /**
     * Dessine un histogramme avec des données fournies.
     * Les données sont représentées sous forme de barres verticales.
     *
     * @param int $w
     *            largeur de l'histogramme
     * @param int $h
     *            hauteur de l'histogramme
     * @param array $data
     *            un tableau associatif contenant les données pour l'histogramme, où chaque clé représente une catégorie et chaque valeur est un tableau de valeurs pour cette catégorie
     * @param string $titre
     *            le titre de l'histogramme (facultatif, par défaut '')
     * @param int $decalage
     *            un décalage optionnel pour aligner l'histogramme différemment (facultatif, par défaut 10)
     * @return void
     */
    function DessinerHistogramme($w, $h, $data, $legende=[], $titre = '', $decalage = 10)
    {
        // Titre de l'histogramme
        $this->SetFont('Blogger', 'B', 14);
        $this->Cell(0, 10, $titre, 0, 2, 'C');

        // Enregistrer les coordonnées actuelles
        $xInit = $this->GetX() + $decalage;
        $yInit = $this->GetY() + $h - 20;

        $this->SetLineWidth(0.2); // Épaisseur des lignes
        $this->SetFont('Blogger', '', 10); // Police des libellés

        // Dessiner les axes X et Y
        $this->SetDrawColor(0, 0, 0);
        $this->Line($xInit, $yInit, $xInit, $yInit - $h + 20); // Axe Y
        $this->Line($xInit, $yInit, $xInit + $w - 10, $yInit); // Axe X

        // Flèche axe X
        $this->Line($xInit + $w - 10, $yInit, $xInit + $w - 12, $yInit + 2); // Barre de la flèche
        $this->Line($xInit + $w - 10, $yInit, $xInit + $w - 12, $yInit - 2); // Autre barre de la flèche
        
        // Flèche axe y
        $this->Line($xInit, $yInit - $h + 20, $xInit - 2, $yInit - $h + 22); // Barre de la flèche
        $this->Line($xInit, $yInit - $h + 20, $xInit + 2, $yInit - $h + 22); // Autre barre de la flèche

        // Graduations sur l'axe des Y
        $maxVal = max(array_map('max', $data));
        $hScale = ($h - 30) / $maxVal;
        $graduationStep = $maxVal / 5; // 5 graduations
        $graduationHeight = $hScale * $graduationStep;

        for ($i = 0; $i <= 5; $i ++) {
            $graduationY = $yInit - $graduationHeight * $i;
            $this->Line($xInit - 2, $graduationY, $xInit + 2, $graduationY); // Ligne de graduation
            $this->Text($xInit - $this->GetStringWidth(round($graduationStep * $i)) - 3, $graduationY + 1.5, round($graduationStep * $i)); // Valeur de la graduation
        }

        // Couleurs pour les barres
        $colors = array(
            array(
                255,
                0,
                0
            ), // Rouge
            array(
                0,
                255,
                0
            ), // Vert
            array(
                0,
                0,
                255
            ), // Bleu
            array(
                255,
                255,
                0
            ), // Jaune
            array(
                255,
                0,
                255
            ) // Magenta
        );

        // Calcul de la largeur des barres et de l'espacement
        $nbGroup = count($data);
        $groupWidth = ($w - $decalage - 10) / $nbGroup;

        // Dessiner les barres
        $i = 0;
        $labelDisplayed = false;
        foreach ($data as $label => $values) {
            $x = $xInit + $i * $groupWidth + 5; // Utiliser la position initiale X
            $y = $yInit; // Utiliser la position initiale Y
            $hBar = 0;
            $colorIndex = 0;
            $barWidth = $groupWidth / (count($values) + 1);
            foreach ($values as $val) {
                $this->SetFillColor($colors[$colorIndex][0], $colors[$colorIndex][1], $colors[$colorIndex][2]); // Couleur pour cette série
                $hBar = ceil($val * $hScale);
                $this->Rect($x, $y, $barWidth, - $hBar, 'F');
                $x += $barWidth;
                $colorIndex += 1;
            }
            $label = mb_convert_encoding($label, 'ISO-8859-15', 'UTF-8');
            if ($this->FontSize>$groupWidth) {
                if ($labelDisplayed){
                    $this->TextWithRotation($x - (2 * $groupWidth - $this->FontSize) / 2, $y + $this->GetStringWidth($label) + 2, $label, 90); // Libellé oblique
                }
                $labelDisplayed = !$labelDisplayed;
            }
            else {
                $this->TextWithRotation($x - ($groupWidth - $this->FontSize) / 2, $y + $this->GetStringWidth($label) + 2, $label, 90); // Libellé oblique
            }
            
            $i ++;
        }
        // Légende
        $legendX = $xInit + 10;
        $legendY = $yInit - $h + 20;
        $legendSpacing = 15;
        foreach ($legende as $key=> $value) {
            $value = mb_convert_encoding($value, 'ISO-8859-15', 'UTF-8');
            $this->SetFillColor($colors[$key][0], $colors[$key][1], $colors[$key][2]);
            $this->Rect($legendX, $legendY, 5, 5, 'F');
            $this->Text($legendX + 10, $legendY + 3, $value);
            $legendX += $this->GetStringWidth($value) + $legendSpacing;
        }
        $this->SetY($yInit + $h);
    }
}