<?php
namespace flip_baj\main;

include ('header.php');
?>
<main>
	<div class="container">
		<h2 class="text-center my-4">Bienvenu à la bourse !</h2>
		<div class="row">
			<div class="col">
				<div class="card text-center">
					<i class="bi bi-box-seam" style="font-size: 4em; color: blue;"></i>
					<div class="card-body">
						<h5 class="card-title">Réceptionner les jeux</h5>
						<a href="selectionvendeur.php?t=reception" class="btn btn-primary">Réception</a>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card text-center">
					<i class="bi bi-currency-euro" style="font-size: 4em; color: green;"></i>
					<div class="card-body">
						<h5 class="card-title">Vendre les jeux</h5>
						<a href="vente.php" class="btn btn-primary">Vente</a>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card text-center">
					<i class="bi bi-arrow-repeat" style="font-size: 4em; color: orange;"></i>
					<div class="card-body">
						<h5 class="card-title">Restituer les jeux</h5>
						<a href="selectionvendeur.php?t=restitution" class="btn btn-primary">Restitution</a>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card text-center">
					<i class="bi bi-list" style="font-size: 4em; color: red;"></i>
					<div class="card-body">
						<h5 class="card-title">Chercher un jeu</h5>
						<a href="listejeux.php" class="btn btn-primary">Rechercher</a>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card text-center">
					<i class="bi bi-bar-chart" style="font-size: 4em; color: purple;"></i>
					<div class="card-body">
						<h5 class="card-title">Voir les stats</h5>
						<a href="stats.php" class="btn btn-primary">Statistiques</a>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card text-center">
					<i class="bi bi-gear" style="font-size: 4em; color: black;"></i>
					<div class="card-body">
						<h5 class="card-title">Gérer le site</h5>
						<a href="admin.php" class="btn btn-primary">Admin</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
<?php
include ('footer.php');
?>
