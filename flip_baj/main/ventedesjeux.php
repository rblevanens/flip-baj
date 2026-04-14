<?php
namespace flip_baj\main;
include ('header.php');
include ('utils.php');

?>

<script type="text/javascript" src="../public/js/datatables.min.js"></script>
<script type="text/javascript" src="../public/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../public/js/utils.js"></script>
<script type="text/javascript" src="../public/js/ventedesjeux.js"></script>
<script type="text/javascript" src="../public/js/modaleacheteur.js"></script>
<script type="text/javascript" src="../public/js/selectionacheteur.js"></script>



<ul class="filariane ms-2">
	<li><a href="index.php">Home</a></li>
	<li><a href="vente.php">Liste des ventes</a></li>
	<li><a href="#">Faire une vente</a></li>
</ul>

<main class="container">
	<h2 class="text-center mb-4">Faire une vente</h2>
	<div class="row">
		<div class="col-sm-3">
			<ul class="list-group mt-2">
				<li
					class="col-sm-12 list-group-item d-flex justify-content-between align-items-center"><p class="my-2">
					Nombre de jeux</p><span class="badge bg-secondary rounded-pill"
					id='NbJeuxVendus'></span>
				</li>
				<li
					class="col-sm-12  list-group-item d-flex justify-content-between align-items-center"><p class="my-2">
					Montant total</p><span class="badge bg-secondary rounded-pill"
					id='TotalJeuxVendus' data-total=""></span>
				</li>
			</ul>
		</div>
		<div class="col-sm-9">
			<form id="choixreglement" target="_blank">
				<input type="hidden" id="ip"
					value="<?php echo $_SERVER['REMOTE_ADDR'] ?>" />
				<div class="row">
					<div class="col-md-4">
						<div class="form-floating mb-3">
							<select class="form-select" id="type_transaction"
								name="type_transaction">
								<option value="0">Moyen de paiement</option>
								<option value="1">Espèces</option>
								<option value="2">CB</option>
								<option value="3">Chèque déconseillé</option>
							</select> <label for="type_transaction">Type de transaction</label>
						</div>
						<div class="form-floating mb-3">
							<input type="text" class="form-control" id="MontantDon"
								placeholder=" "> <label for="MontantDon">Montant du don</label>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-floating mb-3">
							<input type="text" class="form-control" id="MontantPercu"
								placeholder=" "> <label for="MontantPercu">Montant perçu</label>
						</div>
						<div class="form-floating mb-3">
							<input type="text" class="form-control" id="MontantRendu"
								placeholder=" " readonly> <label for="MontantRendu">Montant à
								rendre</label>
						</div>
					</div>
					<div class="col-md-4">
						<div class="row">
							<div class="col-md-12 mb-3">
								<button id="showModal" type="button" class="btn btn-warning">Ajouter
									un acheteur</button>
							</div>
							<!-- <div class="col-md-12 mb-3">
								<button id="RechercheAcheteur" type="button" class="btn btn-warning">Rechercher
									un acheteur</button>
							</div>  -->
						</div>
						<div class="row">
							<div class="col-md-12">
								<button id="finaliserbutton" type="button"
									class="btn btn-success">Finaliser la vente</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
			<div class="card">
				<div class="card-header bg-primary">
					<h3 class="card-title text-white">Liste des jeux en stock</h3>
				</div>
				<div class="card-body">
					<table id="jeuxenstock" class="display">
						<thead>
							<tr>
								<th>Jeu</th>
								<th>Code barre</th>
								<th>Prix</th>
								<th>Vendeur</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
					<!-- <p>
						Cliquer sur le sac pour ajouter un sac &nbsp;<i id="ajoutersac" class="bi bi-bag-plus"  style="font-size: 1.5em; color: orange;"></i><br> 
					</p>
					-->
					<p>
						Passer un jeu du stock à vendu : double cliquer sur la ligne
					</p>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="card">
				<div class="card-header bg-primary">
					<h3 class="card-title text-white">Panier</h3>
				</div>
				<div class="card-body">
					<table id="jeuxenvente" class="display">
						<thead>
							<tr>
								<th>Jeu</th>
								<th>Code</th>
								<th>Prix</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="2">Total :</th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
					</table>
					<p>Ne plus vendre un jeu : cliquer sur l'icone <i class="bi bi-cart-x" style="font-size: 1em; color: black;"></i> dans la colonne
						action</p>
				</div>
			</div>
		</div>
	</div>

<div class="alert alert-danger" role="alert">
    ⚠️ <strong>Attention</strong> : Si vous quittez ou rafraîchissez la page, vos modifications risquent de ne pas être enregistrées.<br>
    Si un jeu se trouve dans le panier au moment du rafraîchissement, il ne réapparaîtra pas dans la liste des jeux en stock après.<br><br>
    👉 <strong>Pour corriger cela</strong> : avant de quitter la page, s’il y a des jeux dans le panier, cliquez sur l’icône <i class="bi bi-cart-x" style="font-size: 1em; color: black;"></i> dans la colonne « Action » pour les remettre dans la liste des jeux en stock.<br>
    Sinon, rendez-vous sur <a href="http://10.0.2.151/FlipBAJ/flip_baj/main/listejeux.php" target="_blank">cette page</a>, recherchez les jeux avec le statut <em>« en panier »</em>, et remettez-les en <em>« en stock »</em> pour qu’ils réapparaissent dans la liste des jeux disponibles.
</div>

</div>
</main>
<?php
include ('modaleacheteur.php');
include ('footer.php');
?>
