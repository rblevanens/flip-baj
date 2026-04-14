<!-- Modal de Modification d'Acheteur -->
<div class="modal fade" id="modalModificationAcheteur" tabindex="-1"
	role="dialog" aria-labelledby="modalModificationAcheteurLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content ui-front">
			<div class="modal-header justify-content-center">
				<h2 id="modal-titre" class="modal-title">Modification d'un acheteur</h2>
			</div>
			<form id="formModificationAcheteur" class="form-horizontal"
				autocomplete="off">
				<div class="modal-body">
					<input id="idAcheteurModification" type="hidden"> <input
						id="idTransactionModification" type="hidden">
					<div class="mb-3 row">
						<div class="text-center">
						Identité<br>
						<small class="text-muted">Tous les champs sont obligatoires, sauf les champ "Informations supplémentaires"</small>
						</div>

					</div>
					<div class="mb-3 row">
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="nomAcheteurAModifier" type="text"
									class="form-control require" placeholder=" "
									aria-required="true" required> <label
									for="nomAcheteurAModifier">Nom</label>
							</div>
							<div id="nomAcheteurAModifier-feedback" class="invalid-feedback"></div>
						</div>
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="prenomAcheteurAModifier" type="text"
									class="form-control require" placeholder=" "
									aria-required="true" required> <label
									for="prenomAcheteurAModifier">Prénom</label>
							</div>
							<div id="prenomAcheteurAModifier-feedback"
								class="invalid-feedback"></div>
						</div>
					</div>
					<div class="mb-3 row">
						<div class="text-center">Contact</div>
					</div>
					<div class="mb-3 row">
						<div class="col-sm-6">
							<div class="form-floating">
								<input type="email" class="form-control require"
									id="emailAcheteurAModifier" placeholder=" "
									aria-required="true" required> <label
									for="emailAcheteurAModifier">Email</label>
							</div>
							<div id="emailAcheteurAModifier-feedback"
								class="invalid-feedback"></div>
						</div>
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="adresseAcheteurAModifier" type="text"
									class="form-control require" placeholder=" "
									aria-required="true" required> <label
									for="adresseAcheteurAModifier">Adresse</label>
							</div>
							<div id="adresseAcheteurAModifier-feedback"
								class="invalid-feedback"></div>
						</div>
					</div>
					<div class="mb-3 row">
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="codepostalAcheteurAModifier" type="text"
									class="form-control require" placeholder=" "
									aria-required="true" required> <label
									for="codepostalAcheteurAModifier">Code postal</label>
							</div>
							<div id="codepostalAcheteurAModifier-feedback"
								class="invalid-feedback"></div>
						</div>
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="villeAcheteurAModifier" type="text"
									class="form-control require" placeholder=" "
									aria-required="true" required> <label
									for="villeAcheteurAModifier">Ville</label>
							</div>
							<div id="villeAcheteurAModifier-feedback"
								class="invalid-feedback"></div>
						</div>
					</div>
					<div class="mb-3 row">
						<div class="text-center">Informations supplémentaires</div>
					</div>
					<div class="mb-3 row">
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="raisonSocialeAcheteurAModifier" type="text"
									class="form-control"> <label
									for="raisonSocialeAcheteurAModifier">Raison Sociale</label>
							</div>
							<div id="raisonSocialeAcheteurAModifier-feedback"
								class="invalid-feedback"></div>
						</div>
						<div class="col-sm-6">
							<div class="form-floating">
								<input id="siretAcheteurAModifier" type="text"
									class="form-control"> <label for="siretAcheteurAModifier">SIRET</label>
							</div>
							<div id="siretAcheteurAModifier-feedback"
								class="invalid-feedback"></div>
						</div>
					</div>
				</div>
				<div id="messageerreurformulairemodale" class="invalid-feedback"></div>
				<div id="messageformulairemodale"></div>
				<div class="modal-footer">
					<button id="boutonreset" type="button" class="btn btn-warning">Réinitialiser</button>
					<button id="boutoncancel" type="button" class="btn btn-default"
						data-dismiss="modal">Annuler</button>
					<button id="boutonsaveacheteur" type="submit"
						class="btn btn-success">Enregistrer</button>
				</div>
			</form>
		</div>
	</div>
</div>