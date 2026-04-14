<?php
namespace flip_baj\main;

include ('header.php');
include ('utils.php');

$user = array(
    "message1" => '',
    "message2" => ''
);
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $userarray = getUser($id);
    if ($userarray['message2'] == '1') {
        $user = $userarray['message1'];
        // error_log("userarray".print_R($user,true), 0);
    }
}
?>

<script type="text/javascript" src="../public/js/datatables.min.js"></script>
<script type="text/javascript" src="../public/js/jquery.jeditable.js"></script>
<script type="text/javascript" src="../public/js/bootstrap.min.js"></script>
<script type="text/javascript" src="../public/js/utils.js"></script>
<script type="text/javascript" src="../public/js/modalevendeur.js"></script>
<script type="text/javascript" src="../public/js/restitutiondesjeux.js"></script>

<!-- Navigation -->
<ul class="filariane ms-2">
	<li><a href="index.php">Home</a></li>
	<li><a href="selectionvendeur.php?t=restitution">Selection du vendeur</a></li>
	<li><a href="#">Gestion de <?php echo $user['nom'].' '.$user['prenom'] ?></a></li>
</ul>

<main class="container">
	<h2 class="text-center mb-4">Restitution des jeux</h2>

	<div class="row">
		<div class="col-sm-5">

			<!-- Vendeur Card -->
			<div class="card">
				<div class="card-header bg-primary">
					<h4 class="card-title text-white">Vendeur</h4>
				</div>
				<div class="card-body">
					<div class="d-flex align-items-center">
						<img src="../public/img/g6895.png" class="me-3" width="30" height="50"
                             alt="...">
						<div class="flex-grow-1">
							<input id="idVendeurEdition" type="hidden"
								value="<?php echo $user['id'] ?>" />
							<h5 class="mb-0"><?php echo $user['nom'].' '.$user['prenom'] ?></h5>
							<div id="email"><?php echo $user['email'] ?></div>
							<div><?php echo $user['telephone'] ?></div>
                <?php
                if (isset($user['denomination_sociale']) && $user["denomination_sociale"] != '') {
                    echo "<div>" . $user['denomination_sociale'] . "</div>";
                }
                if (isset($user['siege_social']) && $user["siege_social"] != '') {
                    echo "<div>" . $user['siege_social'] . "</div>";
                }
                ?>
            </div>
						<div class="ms-auto">
							<button id="showModal" type="button" class="btn btn-primary">
								Modifier<br>Coordonnées
							</button>
						</div>
					</div>
					<ul class="list-group">
						<li
							class="list-group-item d-flex justify-content-between align-items-center">Jeux
							en stock<span class="badge bg-secondary rounded-pill"
							id='nbJeuxStockVendeurSelectionne'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">Jeux
							vendus<span class="badge bg-secondary rounded-pill"
							id='nbJeuxVendusVendeurSelectionne'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">Jeux
							rendus<span class="badge bg-secondary rounded-pill"
							id='nbJeuxRendusVendeurSelectionne'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">Jeux
							donnés<span class="badge bg-secondary rounded-pill"
							id='nbJeuxDonnesVendeurSelectionne'></span>
						</li>
					</ul>
					<ul class="list-group mt-2">
						<li
							class="list-group-item d-flex justify-content-between align-items-center">Total
							jeux vendus<span class="badge bg-secondary rounded-pill"
							id='TotalJeuxVendusVendeurSelectionne'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">Montant
							déjà remboursé / donné<span
							class="badge bg-secondary rounded-pill"
							id='DejaPayeVendeurSelectionne'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">Montant
							restant dû<span class="badge bg-secondary rounded-pill"
							id='ResteVendeurSelectionne'></span>
						</li>
					</ul>
					<div id=ul_remb>
						<ul class="list-group mt-2" id=form_remb>
							<li
								class="list-group-item d-flex justify-content-between align-items-center"><select class="form-select" name="Moyen-remboursement" id="mode_remb">
								<option value="0" selected disabled>-- Choisir le mode de remboursement --</option>
								<option value="1">Espèces</option>
								<option value="2">Chèque</option>
								<option value="3">PayPal</option>
								<option value="4">Virement</option>
							 </select>
							</li>
							<li
								class="list-group-item d-flex justify-content-between align-items-center">Montant
								restitué <span class="input_montant"> <input
									id='montant_remboursement'></input>&nbsp;€
							</span>
							</li>
							<li
								class="list-group-item d-flex justify-content-between align-items-center">Don à l’association <span class="input_montant"> <input
									id='montant_don'></input>&nbsp;€
							</span>
							</li>
						</ul>
					</div>
					<div class="row mt-2 text-center">
						<form id="remboursement" class="form-inline" target="_blank">
							<button id="valider_remboursement_bouton" type="button"
								class="btn btn-success">Valider le remboursement</button>
						</form>
					</div>
					<div id="listeremb">
              <?php echo AfficherRemb($id);?></div>
					<div id="listedons">
              <?php echo AfficherDons($id);?>
          </div>
				</div>
			</div>
		</div>
		<div class="col-sm-7">
			<div class="card">
				<div class="card-header bg-primary">
					<h3 class="card-title text-white">Liste des jeux</h3>
				</div>
				<input type="hidden" id="ip"
					value="<?php echo $_SERVER['REMOTE_ADDR'] ?>" />
				<div class="card-body">
					<div class="d-flex justify-content-end align-items-center mb-3">
						<div>
							<button id="tout-reduire" class="btn btn-outline-warning btn-sm">
								Réduire tous les tableaux <span class="bi bi-arrows-collapse"></span>
							</button>
						</div>
					</div>
					<div class="table-responsive">
						<div class="d-flex">
							<div class="col-sm-4"></div>
							<div class="col-sm-4 text-center">
								<h5>Jeux en stock</h5>
							</div>
							<div class="col-sm-4 text-end">
								<i class="bi bi-arrows-collapse reduire"
									data-table-id="jeuxenstock"></i>
							</div>
						</div>
						<table id="jeuxenstock"
							class="table table-bordered table-striped display">
							<thead>
								<tr class="bg-info text-white">
									<th>Code</th>
									<th>Jeu</th>
									<th>Vendu</th>
									<th>Rendu</th>
									<th>vigilance</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody class="reductible align-middle">
								<!-- Lignes ajoutées via le js-->
							</tbody>
						</table>
					</div>
					<div class="table-responsive">
						<div class="d-flex">
							<div class="col-sm-4 text-begin"></div>
							<div class="col-sm-4 text-center">
								<h5>Jeux vendus</h5>
							</div>
							<div class="col-sm-4 text-end">
								<i class="bi bi-arrows-collapse reduire"
									data-table-id="jeuxvendus"></i>
							</div>
						</div>
						<table id="jeuxvendus"
							class="table table-bordered table-striped display">
							<thead>
								<tr class="bg-info text-white">
									<th>Code</th>
									<th>Jeu</th>
									<th>Vendu</th>
									<th>Rendu</th>
									<th>vigilance</th>
									<th>Vente</th>
								</tr>
							</thead>
							<tbody class="reductible align-middle">
								<!-- Lignes ajoutées via le js-->
							</tbody>
						</table>
					</div>
					<div class="table-responsive">
						<div class="d-flex">
							<div class="col-sm-4 text-begin"></div>
							<div class="col-sm-4 text-center">
								<h5>Jeux rendus</h5>
							</div>
							<div class="col-sm-4 text-end">
								<i class="bi bi-arrows-collapse reduire"
									data-table-id="jeuxrendus"></i>
							</div>
						</div>
						<table id="jeuxrendus"
							class="table table-bordered table-striped display">
							<thead>
								<tr class="bg-info text-white">
									<th>Code</th>
									<th>Jeu</th>
									<th>Vendu</th>
									<th>Rendu</th>
									<th>vigilance</th>
									<th>Restitution</th>
								</tr>
							</thead>
							<tbody class="reductible align-middle">
								<!-- Lignes ajoutées via le js-->
							</tbody>
						</table>
					</div>
					<div class="table-responsive">
						<div class="d-flex">
							<div class="col-sm-4 text-begin"></div>
							<div class="col-sm-4 text-center">
								<h5>Jeux donnés</h5>
							</div>
							<div class="col-sm-4 text-end">
								<i class="bi bi-arrows-collapse reduire"
									data-table-id="jeuxdonnes"></i>
							</div>
						</div>
						<table id="jeuxdonnes"
							class="table table-bordered table-striped display">
							<thead>
								<tr class="bg-info text-white">
									<th>Code</th>
									<th>Jeu</th>
									<th>Vendu</th>
									<th>Rendu</th>
									<th>vigilance</th>
									<th>Don</th>
								</tr>
							</thead>
							<tbody class="reductible align-middle">
								<!-- Lignes ajoutées via le js-->
							</tbody>
						</table>
					</div>
					<p>Un jeu en italique est un jeu suspect.</p>
					<p>Donner un jeu au festival : cliquer sur l'icone cadeau dans la
						colonne action.</p>
					<p>Rendre un jeu au vendeur : double cliquer sur la ligne du
						tableau des jeux encore en stock.</p>
					<p>Remettre en stock un jeu rendu : double cliquer sur la ligne
						du tableau des jeux rendus.</p>
					<p>Flagger un jeu comme suspect : cliquer sur l'icone ! dans la
						colonne action.</p>
					<p>Réduire un tableau trop grand en cliquant sur l'icone reduction.</p>
				</div>
			</div>
		</div>
	</div>
</main>
<?php
// fenetre modal de creation d'un vendeur
include ('modalevendeur.php');
include ('footer.php');
?>
