<?php
namespace flip_baj\main;

include ('header.php');

define("TYPE_RECEPTION", "reception");
define("TYPE_RESTITUTION", "restitution");
if (isset($_GET['t'])) {
    $type = $_GET['t'];
}
?>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/datatables.min.js"></script>
<script type="text/javascript" src="js/utils.js"></script>

<script type="text/javascript" src="js/selectionvendeur.js"></script>
<script type="text/javascript" src="js/modalevendeur.js"></script>

<ul class="filariane ms-2">
	<li><a href="index.php">Home</a></li>
	<li><a href="#">Selection du vendeur</a></li>
</ul>

<main class="container">

	<h2 class="text-center"><?php
if ($type == TYPE_RECEPTION)
    echo 'Reception des jeux';
if ($type == TYPE_RESTITUTION)
    echo 'Restitution des jeux';
?> - choix du vendeur</h2>
<div class="text-center">
		<button id="showModal" type="button" class="btn btn-primary">Création
			vendeur</button>
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
// fenetre modal de creation d'un vendeur
include ('modalevendeur.php');
include ('footer.php');
?>