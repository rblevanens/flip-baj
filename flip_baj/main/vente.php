<?php
namespace flip_baj\main;
include ('header.php');
?>



<script type="text/javascript" src="js/datatables.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/utils.js"></script>
<script type="text/javascript" src="js/vente.js"></script>
<script type="text/javascript" src="js/modaleacheteur.js"></script>

<ul class="filariane ms-2">
	<li><a href="index.php">Home</a></li>
	<li><a href="#">Liste des ventes</a></li>
</ul>
<main class="container">
	<div class="row">
		<h2 class="text-center mb-4">Liste des ventes</h2>
	</div>
	<div class="row text-center">
		<div>
			<button id="showVente" class="btn btn-primary">Ajouter des jeux au panier</button>
		</div>
	</div>
	<div class="row">
		<table id="vendeurs" class="table table-striped table-hover">
			<thead>
				<tr class="bg-info text-white">
					<th>Date</th>
					<th>Jeux</th>
					<th>Nb jeux vendus</th>
					<th>Total</th>
					<th>Percu</th>
					<th>Rendu</th>
					<th>Paiement</th>
					<th>Acheteur</th>
				</tr>
			</thead>
			<tbody class="align-middle">
			</tbody>
		</table>
	</div>
</main>
<?php
include ('modaleacheteur.php');
include ('footer.php');
?>
