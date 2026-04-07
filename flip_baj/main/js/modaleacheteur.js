/**
 * Ce script JavaScript gère la modification et la création d'acheteurs dans une application web.
 * Il inclut des fonctionnalités telles que la validation des champs, l'autocomplétion des données, 
 * et l'envoi de requêtes Ajax pour récupérer, mettre à jour ou créer des données d'acheteurs dans la base de données.
 */

// Attente du chargement du document
$(document).ready(function() {

	// Désactiver l'autocomplétion pour les champs de saisie
	$('input').attr('autocomplete', 'off');

	/**
	 * Fonction pour valider un champ avec une classe Bootstrap et afficher un message d'erreur si nécessaire.
	 * @param {jQuery} input - L'élément jQuery représentant le champ à valider.
	 * @param {Function} isValid - La fonction de validation à appliquer sur la valeur du champ.
	 * @param {string} errorMessageElement - L'identifiant de l'élément HTML où afficher le message d'erreur.
	 * @param {string} emptyMessage - Le message d'erreur à afficher si le champ est vide (par défaut : 'Ce champ est obligatoire.').
	 */
	function validateInput(input, isValid, errorMessageElement, emptyMessage = false) {
		var errorMessage = '';
		if (!input.val().trim()) {
			errorMessage = emptyMessage || 'Ce champ est obligatoire.';
		} else if (!isValid(input.val())) {
			errorMessage = 'Veuillez saisir une valeur valide.';
		}
		console.log(errorMessage + ' lieu ' + errorMessageElement);
		document.getElementById(errorMessageElement).innerHTML = errorMessage;
		input.toggleClass('is-invalid', !!errorMessage);
		input.toggleClass('is-valid', !errorMessage);
	}

	// Fonctions de validation
	function checkName(name) {
		var re = /^[a-zA-ZÀ-ÿ' ]+$/;
		return re.test(name);
	}

	function checkEmail(email) {
		var re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
		return re.test(email);
	}

	function checkAddress(address) {
		var re = /^[a-zA-Z0-9À-ÿ',.\s-]+$/;
		return re.test(address);
	}

	/*function checkPostalCode(codePostal) {
		var re = /^\d{5}$/;
		return re.test(codePostal);
	}*/
   // Il est commenté parce qu’il existe des SIRET belges.
   /*
	function checkSIRET(siret) {
		var re = /^\d{14}$/;
		return re.test(siret);
	}
  */
	/**
	 * Configuration de l'autocomplétion et de la validation des champs.
	 * @param {string} selector - Le sélecteur jQuery pour le champ à configurer.
	 * @param {string} requestDataKey - La clé des données à envoyer dans la requête Ajax.
	 * @param {Function} isValidFunction - La fonction de validation à appliquer sur les données.
	 */
	function configureAutocomplete(selector, requestDataKey, isValidFunction) {
		$(selector).autocomplete({
			source: function(request, response) {
				$.ajax({
					url: 'ajax/user-get-autocomplete.php',
					type: 'POST',
					data: { [requestDataKey]: request.term },
					dataType: 'json',
					success: function(data) {
						response($.map(data, function(item) {
							return {
								label: item.nom + ' ' + item.prenom + ' ' + item.email,
								value: item[requestDataKey],
								acheteur: item
							};
						}));
					},
					error: function(error) {
						console.log('Erreur Ajax lors de la recherche par ' + requestDataKey + '.');
					}
				});
			},
			minLength: 2,
			select: function(event, ui) {
				// Mettre à jour les champs avec les données sélectionnées
				$('#idAcheteurModification').val(ui.item.acheteur.id);
				$('#nomAcheteurAModifier').val(ui.item.acheteur.nom);
				$('#prenomAcheteurAModifier').val(ui.item.acheteur.prenom);
				$('#emailAcheteurAModifier').val(ui.item.acheteur.email);
				$('#adresseAcheteurAModifier').val(ui.item.acheteur.adresse);
				$('#codepostalAcheteurAModifier').val(ui.item.acheteur.code_postal);
				$('#villeAcheteurAModifier').val(ui.item.acheteur.ville);
				$('#raisonSocialeAcheteurAModifier').val(ui.item.acheteur.raison_sociale);
				$('#siretAcheteurAModifier').val(ui.item.acheteur.siret);

				// Mettre à jour les classes Bootstrap pour la validation
				validateInput($('#nomAcheteurAModifier'), checkName, 'nomAcheteurAModifier-feedback');
				validateInput($('#prenomAcheteurAModifier'), checkName, 'prenomAcheteurAModifier-feedback');
				validateInput($('#emailAcheteurAModifier'), checkEmail, 'emailAcheteurAModifier-feedback');
				validateInput($('#adresseAcheteurAModifier'), checkAddress, 'adresseAcheteurAModifier-feedback');
				validateInput($('#codepostalAcheteurAModifier'), checkPostalCode, 'codepostalAcheteurAModifier-feedback');
				validateInput($('#villeAcheteurAModifier'), checkName, 'villeAcheteurAModifier-feedback');
				validateInput($('#raisonSocialeAcheteurAModifier'), checkAddress, 'raisonSocialeAcheteurAModifier-feedback', 'Ce champ est vide, mais pas obligatoire.');
				//validateInput($('#siretAcheteurAModifier'), checkSIRET, 'siretAcheteurAModifier-feedback', 'Ce champ est vide, mais pas obligatoire.');
			}
		});
	}

	// Configuration des autocomplétions et de la validation des champs
	configureAutocomplete('#emailAcheteurAModifier', 'email', checkEmail);
	configureAutocomplete('#nomAcheteurAModifier', 'nom', checkName);
	configureAutocomplete('#prenomAcheteurAModifier', 'prenom', checkName);

	// Configuration des validations des champs lors de la frappe au clavier
	$('#nomAcheteurAModifier').on('keyup', function() {
		validateInput($('#nomAcheteurAModifier'), checkName, 'nomAcheteurAModifier-feedback');
	});

	$('#prenomAcheteurAModifier').on('keyup', function() {
		validateInput($('#prenomAcheteurAModifier'), checkName, 'prenomAcheteurAModifier-feedback');
	});

	$('#emailAcheteurAModifier').on('keyup', function() {
		validateInput($('#emailAcheteurAModifier'), checkEmail, 'emailAcheteurAModifier-feedback');
	});

	$('#adresseAcheteurAModifier').on('keyup', function() {
		validateInput($('#adresseAcheteurAModifier'), checkAddress, 'adresseAcheteurAModifier-feedback');
	});

	$('#codepostalAcheteurAModifier').removeClass('is-invalid is-valid'); 
	$('#codepostalAcheteurAModifier').on('keyup', function() {
	$('#codepostalAcheteurAModifier').removeClass('is-invalid').addClass('is-valid');
	document.getElementById('codepostalAcheteurAModifier-feedback').innerHTML = '';
});


	$('#villeAcheteurAModifier').on('keyup', function() {
		validateInput($('#villeAcheteurAModifier'), checkName, 'villeAcheteurAModifier-feedback');
	});

	$('#raisonSocialeAcheteurAModifier').on('keyup', function() {
		validateInput($('#raisonSocialeAcheteurAModifier'), checkAddress, 'raisonSocialeAcheteurAModifier-feedback', 'Ce champ est vide, mais pas obligatoire.');
	});

	$('#siretAcheteurAModifier').on('keyup', function() {
		validateInput($('#siretAcheteurAModifier'), checkSIRET, 'siretAcheteurAModifier-feedback', 'Ce champ est vide, mais pas obligatoire.');
	});

	// Bouton "Réinitialiser"
	$("#boutonreset").click(function(e) {
		e.preventDefault();
		$('#formModificationAcheteur').trigger("reset");
		$('#idAcheteurModification').val('');
		$('#messageerreurformulairemodale').html('');
	});

	// Bouton "Annuler"
	$("#boutoncancel").click(function(e) {
		e.preventDefault();
		$('#modalModificationAcheteur').modal('hide');
		$('#formModificationAcheteur').trigger("reset");
		$('#idAcheteurModification').val('');
		$('#idTransactionModification').val('');
		$('#messageerreurformulairemodale').html('');
	});

	// Bouton "Enregistrer"
	$('#boutonsaveacheteur').on('click', function(e) {
		e.preventDefault();
		var idAcheteur = $('#idAcheteurModification').val();
		var nom = $('#nomAcheteurAModifier').val();
		var	prenom = $('#prenomAcheteurAModifier').val();
		var	email = $('#emailAcheteurAModifier').val();
		$('#messageerreurformulairemodale').html('');
		var data = {
			id_acheteur: idAcheteur,
			id_transaction: $('#idTransactionModification').val(),
			nom: nom,
			prenom: prenom,
			email: email,
			adresse: $('#adresseAcheteurAModifier').val(),
			code_postal: $('#codepostalAcheteurAModifier').val(),
			ville: $('#villeAcheteurAModifier').val(),
			raison_sociale: $('#raisonSocialeAcheteurAModifier').val(),
			siret: $('#siretAcheteurAModifier').val()
		};
		console.log(data);

		// Validation des champs avant l'envoi
		var isValid = true;
		$('.require').each(function() {
			if (!$(this).val() || $(this).hasClass('is-invalid')) {
				isValid = false;
				return false;
			}
		});
		console.log(isValid);
		if (isValid) {
			// Effectuer la requête Ajax pour enregistrer les données
			$.ajax({
				url: idAcheteur ? 'ajax/acheteur-update.php' : 'ajax/acheteur-add.php',
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(response) {
					if (response.message2 == '1') {
						$('#formModificationAcheteur').trigger("reset");
						$('#messageerreurformulairemodale').html('<p class="valid-feedback">' + (idAcheteur ? 'Acheteur mis à jour' : 'Acheteur créé') + '</p>');

						// Rafraîchir la page si on est sur vente.php
						if (window.location.href.indexOf("vente.php") > -1) {
							location.reload();
						} else {
							$('#showModal').html('Modifier l\'acheteur '+prenom+' '+nom+' '+email);
							// Fermer la modale si on est sur une autre page
							$('#modalModificationAcheteur').modal('hide');
						}
					} else {
						$('#messageerreurformulairemodale').html('<p class="invalid-feedback">Erreur base de données.</p>');
					}
				},
				error: function(error) {
					$('#messageerreurformulairemodale').html('<p class="invalid-feedback">Erreur Ajax lors de l\'enregistrement des données de l\'acheteur.</p>');
					console.log(error)
				}
			});
		} else {
			$('#messageerreurformulairemodale').html('<p class="invalid-feedback">Veuillez remplir tous les champs correctement.</p>');
		}
	});
});

/**
 * Fonction pour ouvrir la modale de modification avec l'ID de l'acheteur.
 * @param {string} idAcheteur - L'identifiant de l'acheteur à modifier.
 * @param {string} idTransaction - L'identifiant de la transaction associée à l'acheteur.
 */
function openModificationAcheteurModal(idAcheteur, idTransaction) {
	console.log('Ouvrir la modale de modification pour l\'acheteur avec l\'ID ' + idAcheteur);
	$('#messageerreurformulairemodale').html('');
	// Requête Ajax pour récupérer les informations de l'acheteur
	$.ajax({
		url: 'ajax/acheteur-get.php',
		type: 'POST',
		data: { id: idAcheteur },
		dataType: 'json',
		success: function(response) {
			// Peupler la modal avec les données récupérées
			$('#idAcheteurModification').val(idAcheteur);
			$('#idTransactionModification').val(idTransaction);
			$('#nomAcheteurAModifier').val(response.nom);
			$('#prenomAcheteurAModifier').val(response.prenom);
			$('#emailAcheteurAModifier').val(response.email);
			$('#adresseAcheteurAModifier').val(response.adresse);
			$('#codepostalAcheteurAModifier').val(response.code_postal);
			$('#villeAcheteurAModifier').val(response.ville);
			$('#raisonSocialeAcheteurAModifier').val(response.raison_sociale);
			$('#siretAcheteurAModifier').val(response.siret);

			// Afficher la modal
			$('#modalModificationAcheteur').modal('show');
		},
		error: function(error) {
			console.log('Erreur Ajax lors de la récupération des données de l\'acheteur.');
		}
	});
}

/**
 * Fonction pour ouvrir la modale de création avec l'ID de la transaction.
 * @param {string} idTransaction - L'identifiant de la transaction associée à l'acheteur à créer.
 */
function openCreationAcheteurModal(idTransaction) {
	$('#idAcheteurModification').val('');
	$('#messageerreurformulairemodale').html('');
	$('#modal-titre').html("Création d'un acheteur");
	$('#idTransactionModification').val(idTransaction);
	$('#modalModificationAcheteur').modal('show');
	console.log('Ouvrir la modale de création d\'acheteur pour la transaction avec l\'ID ' + idTransaction);
}