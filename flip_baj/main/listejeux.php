<?php

namespace flip_baj\main;
include ('header.php');
include ('./pdo_connect.php');
include ('constantes.php');


$results = $pdo->query($sql_54_GET_LISTE_STATUS);
$tab_categorie = array();

while ($row = $results->fetch(\PDO::FETCH_ASSOC)) {
    if ($row['nbr_status'] > 0) {
        $tab_categorie[] = $row;
    }
}


?>
<script type="text/javascript" src="js/datatables.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/crypto-js.min.js"></script>
<script type="text/javascript" src="js/utils.js"></script>
<script type="text/javascript" src="js/listejeux.js"></script>


<ul class="filariane ms-2">
	<li><a href="index.php">Home</a></li>
	<li><a href="#">Liste des jeux</a></li>
</ul>
<input type="hidden" id="ip"
	value="<?php echo $_SERVER['REMOTE_ADDR'] ?>" />
<main class="container">
	<div class="row">
		<div class="col">
			<h2>Recherche de jeux</h2>
			<form id="searchForm" class="row g-3">
				<div class="col-md-3">
					<div class="form-floating">
						<input type="text" class="form-control search" id="searchNom"
							placeholder="Rechercher par nom"> <label for="searchNom">Nom :</label>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-floating">
						<input type="text" class="form-control search" id="searchCode"
							placeholder="Rechercher par code"> <label for="searchCode">Code :</label>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-floating">
						<select class="form-select search" id="searchVigilance">
							<option value="">Non précisé</option>
							<option value="1">Oui</option>
							<option value="0">Non</option>
						</select> <label for="searchVigilance">Vigilance :</label>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-floating">
						<select class="form-select search" id="searchStatut">
						<?php
						foreach($tab_categorie as $tbC)
						{
							$id = htmlspecialchars($tbC['id']);
							$value = htmlspecialchars($tbC['value']);
							echo "<option value=\"$id\" > $value</option>";
						} 
						?>
						</select> <label for="searchStatut">Statut :</label>
					</div>
				</div>
			</form>
			<div class="mt-2 table-responsive">
				<table id="jeuxenstock" class="table table-bordered table-striped">
					<thead class="table-info">
						<tr>
							<th>Jeu</th>
							<th>Code</th>
							<th>Vendu</th>
							<th>Vendeur</th>
							<th>date_reception</th>
							<th>vigilance</th>
							<th>idstatut</th>
							<th>statut</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</main>

<!-- Fenêtre modale modification de statut -->
<div class="modal fade" id="editStatutModal" tabindex="-1" role="dialog"
	aria-labelledby="editStatutModalLabel">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close"></button>
				<h4 class="modal-title text-center w-100" id="editStatutModalLabel">Modifier
					le statut</h4>
			</div>
			<form id="editStatutForm">
				<div class="modal-body">
					<div class="mb-3">
						<div class="form-floating">
							<select id="statutSelect" class="form-select"></select> <label
								for="statutSelect" class="form-label">Nouveau statut</label>
						</div>
					</div>
					<div class="mb-3">
						<div class="form-floating">
							<input type="password" id="mdpInput" class="form-control"
								placeholder="Entrez votre mot de passe"> <label for="mdpInput"
								class="form-label">Mot de passe</label>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"
						id="editStatutModalConfirm">Confirmer</button>
					<button type="button" class="btn btn-secondary"
						data-bs-dismiss="modal">Annuler</button>
				</div>
			</form>
		</div>
	</div>
</div>


<?php
include ('footer.php');
?>
