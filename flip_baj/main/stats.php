<?php
namespace flip_baj\main;
include ('header.php');

?>

<script type="text/javascript" src="../public/js/datatables.min.js"></script>
<script type="text/javascript" src="../public/js/utils.js"></script>
<script type="text/javascript" src="../public/js/stats.js"></script>
<ul class="filariane ms-2">
	<li><a href="index.php">Home</a></li>
	<li><a href="#">Stats</a></li>
</ul>
<main class="container">
	<div class="row justify-content-center">
		<div class="col-sm-5">
			<div class="card">
				<div class="card-header bg-primary">
					<h2 class="card-title text-white" style="text-align: center">Statistiques</h2>
				</div>
				<div class="card-body">
					<form id="form-group">
						<div class="form-floating">
							<select id="annee" name="annee" class="form-select"></select><label
								for="annee">Année</label>
						</div>
					</form>
					<ul class="list-group mt-2">
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Jeux enregistrés non-réceptionnés<span
							class="badge bg-secondary rounded-pill" id='nbJeuxEnregistres'></span>
						</li>
                        
						


						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Jeux en stock<span class="badge bg-secondary rounded-pill"
							id='nbJeuxStock'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Jeux vendus<span class="badge bg-secondary rounded-pill"
							id='nbJeuxVendus'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Jeux donnés<span class="badge bg-secondary rounded-pill"
							id='nbJeuxDonnés'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Sacs vendus<span class="badge bg-secondary rounded-pill"
							id='nbSacsVendus'></span>
						</li>
					</ul>
					<ul class="list-group mt-2">
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant total perçu<span class="badge bg-secondary rounded-pill"
							id='totalVentes'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant perçu en CB<span class="badge bg-secondary rounded-pill"
							id='VentesCB'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant perçu en espèces<span
							class="badge bg-secondary rounded-pill" id='VentesEspeces'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant perçu en chèque<span
							class="badge bg-secondary rounded-pill" id='VentesCheque'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Valorisation du stock<span
							class="badge bg-secondary rounded-pill" id='totalStockPrixVendu'></span>
						</li>
					</ul>
					<ul class="list-group mt-2">
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant total restitué<span
							class="badge bg-secondary rounded-pill" id='totalRemb'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant restitué en espèces<span
							class="badge bg-secondary rounded-pill" id='RembEspeces'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant restitué en chèques<span
							class="badge bg-secondary rounded-pill" id='RembCheques'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant restitué par Paypal<span
							class="badge bg-secondary rounded-pill" id='RembPaypal'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant restant à restituer<span
							class="badge bg-secondary rounded-pill" id='RembRestant'></span>
						</li>
					</ul>
					<ul class="list-group mt-2">
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant des dons(Total)<span class="badge bg-secondary rounded-pill"
							id='totalDons'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Don (par les vendeurs)<span
							class="badge bg-secondary rounded-pill" id='totalDonsNonRemb'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant des commissions HT<span
							class="badge bg-secondary rounded-pill" id='totalCommissionsHT'></span>
						</li>
						<li
							class="list-group-item d-flex justify-content-between align-items-center">
							Montant des commissions TTC<span
							class="badge bg-secondary rounded-pill" id='totalCommissionsTTC'></span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</main>



<?php
include ('footer.php');
?>
