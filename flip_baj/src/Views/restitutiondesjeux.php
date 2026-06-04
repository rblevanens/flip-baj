<?php ob_start(); ?>

    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/restitutiondesjeux.js"></script>

    <ul class="filariane ms-2">
        <li><a href="index.php">Home</a></li>
        <li><a href="index.php?page=selectionvendeur&t=restitution">Sélection du vendeur</a></li>
        <li><a href="#">Restitution des jeux</a></li>
    </ul>

    <input type="hidden" id="id_vendeur" value="<?= htmlspecialchars($id_vendeur) ?>" />

    <main class="container">
        <div class="row">
            <div class="col">
                <h2><?= htmlspecialchars($titrePage) ?></h2>

            </div>
        </div>
    </main>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>