<?php
namespace flip_baj\main;

/**
 * Contenu de la fenêtre modale "créer/modifier vendeur
 *
 * le <form> est à autocomplete = "off" côté festival pour éviter qu'un nouveau vendeur en écrase un ancien/récupère des données d'un ancien
 */
?>
<div class="modal fade" id="modal" tabindex="-1" role="dialog"
	aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header justify-content-center">
				<h2 id="modal-titre" class="text-center modal-title">Création d'un
					vendeur</h2>
			</div>
			<form id="formulaire" class="form-horizontal" autocomplete="off">
				<div id="modal-body" class="modal-body">
					<input id="idVendeurEdition" type="hidden">
					<div class="mb-3 row">
						<div class="text-center">Identité</div>
					</div>
					<div class="mb-3 row">
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="nomVendeurACreer" type="text" class="form-control"
									placeholder=" " aria-required="true">
								<label for="nomVendeurACreer">Nom <span class="text-danger">*</span></label>
							</div>
							<div class="invalid-feedback" id="nom-result"></div>
						</div>
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="prenomVendeurACreer" type="text" class="form-control"
									placeholder=" " aria-required="true">
								<label for="prenomVendeurACreer">Prénom <span class="text-danger">*</span></label>
							</div>
							<div class="invalid-feedback" id="prenom-result"></div>
						</div>
					</div>
					<div class="mb-3 row">
						<div class="text-center">Contact</div>
					</div>
					<div class="mb-3 row">
						<div class="col-sm-8">
							<div class="form-floating">
								<input type="email" class="form-control" id="emailVendeurACreer"
									placeholder=" " aria-required="true">
								<label for="emailVendeurACreer">Email <span class="text-danger">*</span></label>
							</div>
							<div class="invalid-feedback" id="mail-result"></div>
						</div>
						<div class="col-sm-4">
							<div class="form-floating">
								
									<input id="telephoneVendeurACreer" type="text" class="form-control" maxlength="25" >

								<small class="form-text text-muted">
									Format : 06 12 34 56 78 ou +33 6 12 34 56 78
								</small>
								<label for="telephoneVendeurACreer">Téléphone</label>
							</div>
						  </div>

					   
					<div class="mb-3 row">
						<div class="text-center">Adresse de facturation</div>
					</div>
					<div class="mb-3 row">
						<div class="col-sm-12">
							<div class="form-floating">
								<input id="adresseVendeurACreer" type="text"
									class="form-control" placeholder=" " aria-required="true">
								<label for="adresseVendeurACreer">Adresse <span class="text-danger">*</span></label>
							</div>
							<div class="invalid-feedback" id="adresse-result"></div>
						</div>

					</div>
					<div class="mb-3 row">
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="codepostalVendeurACreer" type="text" 
									class="form-control" placeholder=" " aria-required="true">
								<label for="codepostalVendeurACreer">Code postal <span class="text-danger">*</span></label>
							</div>
							<div class="invalid-feedback" id="code-postal-result"></div>
						</div>
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="villeVendeurACreer" type="text" class="form-control"
									placeholder=" " aria-required="true">
								<label for="villeVendeurACreer">Ville <span class="text-danger">*</span></label>
							</div>
							<div class="invalid-feedback" id="ville-result"></div>
						</div>
					</div>
					<div class="mb-3 row">
						<div class="text-center">Statut</div>
					</div>
					<div class="mb-3 row">
						<div class="col-sm-3">
						</div>
						<div class="col-sm-6">
							<select name="Liste_statut" id="Liste_statut" class="form-select"
								aria-required="true">
								<option id="0" value="0">Choisissez le statut</option>
								<option id="1" value="1">Particulier</option>
								<option id="2" value="2">Représentant d'association</option>
							</select>
							<div class="invalid-feedback" id="statut-result"></div>
						</div>
					</div>
					<div class="mb-3 row champ justify-content-center" id="champ1">
						<div class="col-sm-3"></div>
						<div class="col-sm-4">
							Attestation signée
						</div>
						<div class="col-sm-2">
							<select name="attestation_signeeVendeurACreer"
								id="attestation_signeeVendeurACreer" class="form-select"
								aria-required="true">
								<option id="0" value="0">Non</option>
								<option id="1" value="1">Oui</option>
							</select>
						</div>
						<div class="col-sm-3"></div>
						<div class="col-sm-5">
						<div class="invalid-feedback" id="attestation-result"></div></div>
					</div>
					<div class="champ mb-3 row" id="champ2">
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="denomination_socialeVendeurACreer" type="text"
									class="form-control" placeholder=" " aria-required="true">
								<label for="denomination_socialeVendeurACreer">Dénomination sociale</label>
							</div>
							<div class="invalid-feedback" id="denomination-result"></div>
						</div>
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="siege_socialVendeurACreer" type="text"
									class="form-control" placeholder=" " aria-required="true">
								<label for="siege_socialVendeurACreer">Siège social</label>
							</div>
							<div class="invalid-feedback" id="siege-result"></div>
						</div>
					</div>
				</div>
				<div id="messageerreurformulairemodale" style="margin-top: 10px;"></div>
				<div id="messageformulairemodale"></div>
				<div id="modal-footer" class="modal-footer">
					<div class="mb-2 row"><div class="form-check">
						<input class="form-check-input" type="checkbox" id="forcesave"> <label
							class="form-check-label" for="forcesave"> Enregistrer le vendeur
							malgré un homonyme dans la base </label>
					</div></div>
					<button id="boutoncancel" type="button" class="btn btn-secondary">Annuler</button>
					<button id="boutonsavevendeur" type="button"
						class="btn btn-success">Enregistrer</button>
				</div>
			</form>
		</div>
	</div>
</div>
