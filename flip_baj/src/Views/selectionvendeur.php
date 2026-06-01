<?php ob_start(); ?>

<script type="text/javascript" src="js/utils.js"></script>
<script type="text/javascript" src="js/selectionvendeur.js"></script>
<script type="text/javascript" src="js/modalevendeur.js"></script>

<ul class="filariane ms-2">
    <li><a href="index.php">Home</a></li>
    <li><a href="#">Sélection du vendeur</a></li>
</ul>

<main class="container">
    <h2 class="text-center"><?= htmlspecialchars($titreAction) ?> - choix du vendeur</h2>

    <div class="text-center">
        <button id="showModal" type="button" class="btn btn-primary">Création vendeur</button>
    </div>

    <div class="row">
        <div class="col">
            <table id="vendeurs" class="table">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Tel</th>
                    <th>Jeux pas reçus</th>
                    <th>Jeux en stock</th>
                    <th>Jeux vendu</th>
                    <th>Jeux rendus</th>
                    <th>Jeux donnés</th>
                    <th>Actions</th>
                    <th>Id</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
// Inclusion de la modale de création (à déplacer aussi dans src/Views/partials/ plus tard)
require __DIR__ . '/../../main/modalevendeur.php';
?>

<?php
$content = ob_get_clean();
// On appelle le template principal qui contient le vrai <header> et <footer>
require __DIR__ . '/layout.php';
?>
