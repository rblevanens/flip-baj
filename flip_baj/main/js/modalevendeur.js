/** Ce fichier définit 4 fonctions pour gérer la modale de création/modification de vendeurs :
 * Les choses à charger au lancement de la page qui la contient, et les fonctions d'affichage de la fenêtre
 * 
 * Nécessite :
 * * bootstrap.min.js pour l'objet Modal, 
 * 
 * @see documentReadyDeLaModale
 * @see ouvreModaleCreation
 * @see ouvreModaleModification
 * @see windowOnloadDeLaModale
 */

/* regexp global : lecture des dates au format français j/m/aaaa ou international aaaa-mm-jj */
var dateRegex = new RegExp('(([0-3]?[0-9])\/([0-1]?[0-9])\/([0-9]{4}))|(([0-9]{4})-([0-9]{2})-([0-9]{2}))');

/**
 * Cette fonction liste tout ce qui doit être dans le $(document).ready(function() {...}); pour utiliser la modale :
 *   * check automatique des champs
 *	 * affichage différentiel des champs en fonction de la liste déroulante
 *   * boutons fermer/sauvegarder...
 * Exception : le lancement qui est dépendant de la page qui lance la modale, dans deux fonctions séparées
 *  
 * @see ouvreModaleCreation
 * @see ouvreModaleModification
 */
function documentReadyDeLaModale() {
	// Bloque l'autocomplete pour les input
	$('input').attr('autocomplete', 'off');

	// Vérifie que le mail est un mail valide.
	function check_mail(mail) {
		var re = new RegExp(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/);
		return re.test(mail);
	}

	// Pas Vérifie que le code postal est valide (5 chiffres).
	/*function checkCodePostal(codePostal) {
		var re = /^\d{5}$/;
		return re.test(codePostal);
	}*/

	// Vérifie que l'adresse est valide (chiffres, lettres, caractères accentués, apostrophe).
	function checkAdresse(adresse) {
		var re = /^[a-zA-Z0-9À-ÿ',.\s-]+$/;
		return re.test(adresse);
	}

	// Vérifie que le nom, le prénom et la dénomination sociale sont valides (lettres, apostrophe et espaces).
	function checkNomPrenomDeno(nom) {
		var re = /^[a-zA-ZÀ-ÿ' -]+$/; // Ajout de l'espace à la classe de caractères
		return re.test(nom);
	}

	// Pas Vérifie que le numéro de téléphone est valide.
	/*function checkPhoneNumber(phoneNumber) {
		// Expression régulière pour vérifier le format du numéro de téléphone français
		// Autorise les numéros avec ou sans indicatif pays (+33 ou 0), avec ou sans espaces ou tirets
		var re = /^(?:(?:\+|00)33|0)[1-9](?:[\s.-]?\d{2}){4}$/;
        return re.test(phoneNumber);
	}*/

	// Timer pour la vérification de l'email
	var emailCheckTimer;
	$("#emailVendeurACreer").keyup(function(e) {
		clearTimeout(emailCheckTimer);
		var mail = $(this).val();
		emailCheckTimer = setTimeout(function() {
			if (check_mail(mail)) {
				// Si l'email est valide, retirer la classe is-invalid et le message d'erreur
				$("#emailVendeurACreer").removeClass("is-invalid");
				$("#mail-result").html("");
			} else {
				// Si l'email n'est pas valide, ajouter la classe is-invalid et afficher le message d'erreur
				$("#emailVendeurACreer").addClass("is-invalid");
				$("#mail-result").html("L'adresse email n'est pas valide.");
			}
		}, 500);
	});

	// Fonction pour la vérification du code postal
	var codePostalTimer;
	$("#codepostalVendeurACreer").keyup(function(e) {
		clearTimeout(codePostalTimer);
		var codePostal = $(this).val();
		codePostalTimer = setTimeout(function() {
				$("#codepostalVendeurACreer").removeClass("is-invalid");
				$("#code-postal-result").html("");
		}, 500);
	});


	// Fonction pour la vérification du code postal
	var villeTimer;
	$("#villeVendeurACreer").keyup(function(e) {
		clearTimeout(villeTimer);
		var ville = $(this).val();
		villeTimer = setTimeout(function() {
			if (checkNomPrenomDeno(ville)) {
				// Si le code postal est valide, retirer la classe is-invalid et le message d'erreur
				$("#villeVendeurACreer").removeClass("is-invalid");
				$("#ville-result").html("");
			} else {
				// Si le code postal n'est pas valide, ajouter la classe is-invalid et afficher le message d'erreur
				$("#villeVendeurACreer").addClass("is-invalid");
				$("#ville-result").html("Le nom de ville n'est pas valide.");
			}
		}, 500);
	});

	// Fonction pour la vérification de l'adresse
	var adresseTimer;
	$("#adresseVendeurACreer").keyup(function(e) {
		clearTimeout(adresseTimer);
		var adresse = $(this).val();
		adresseTimer = setTimeout(function() {
			if (checkAdresse(adresse)) {
				// Si l'adresse est valide, retirer la classe is-invalid et le message d'erreur
				$("#adresseVendeurACreer").removeClass("is-invalid");
				$("#adresse-result").html("");
			} else {
				// Si l'adresse n'est pas valide, ajouter la classe is-invalid et afficher le message d'erreur
				$("#adresseVendeurACreer").addClass("is-invalid");
				$("#adresse-result").html("L'adresse n'est pas valide.");
			}
		}, 500);
	});

	// Fonction pour la vérification du nom
	var nomTimer;
	$("#nomVendeurACreer").keyup(function(e) {
		clearTimeout(nomTimer);
		var nom = $(this).val();
		nomTimer = setTimeout(function() {
			if (checkNomPrenomDeno(nom)) {
				// Si le nom est valide, retirer la classe is-invalid et le message d'erreur
				$("#nomVendeurACreer").removeClass("is-invalid");
				$("#nom-result").html("");
			} else {
				// Si le nom n'est pas valide, ajouter la classe is-invalid et afficher le message d'erreur
				$("#nomVendeurACreer").addClass("is-invalid");
				$("#nom-result").html("Le nom n'est pas valide.");
			}
		}, 500);
	});

	// Fonction pour la vérification du prénom, et l'unicité du couple nom-prenom
	var prenomTimer;
	$("#prenomVendeurACreer").keyup(function(e) {
		clearTimeout(prenomTimer);
		var prenom = $(this).val();
		var nom = $('#nomVendeurACreer').val();
		prenomTimer = setTimeout(function() {
			if (checkNomPrenomDeno(prenom)) {
				if (nom != "") {
					$.post('ajax/usernameprenom-checker.php', { 'nom': nom, 'prenom': prenom, 'id': $("#idVendeurEdition").val() }, function(data) {
						if (data.message2 == '0') {
							$("#prenomVendeurACreer").addClass("is-invalid");
							$("#prenom-result").html(data.message1);
						}
						if (data.message2 == '1') {
							// Si le prénom est valide et absent de la base, retirer la classe is-invalid et le message d'erreur
							$("#prenomVendeurACreer").removeClass("is-invalid");
							$("#prenom-result").html("");
						}
					}, 'json');
				}
			} else {
				$("#prenomVendeurACreer").removeClass("is-invalid");
				$("#prenom-result").html("");
			}
		}, 500);
	});

	// Fonction pour la vérification du numéro de téléphone
	var telephoneTimer;
	$("#telephoneVendeurACreer").keyup(function(e) {
		clearTimeout(telephoneTimer);
		var telephone = $(this).val();
		
		telephoneTimer = setTimeout(function() {
			// Retirer toute classe d'erreur
			$("#telephoneVendeurACreer").removeClass("is-invalid");
			// Vider le message d'erreur si présent
			$("#telephone-result").html("");
		}, 500);
	});
	


	// Fonction pour la vérification de la dénomination sociale
	var denoTimer;
	$("#denomination_socialeVendeurACreer").keyup(function(e) {
		clearTimeout(denoTimer);
		var deno = $("#denomination_socialeVendeurACreer").val();
		denoTimer = setTimeout(function() {
			if (checkNomPrenomDeno(deno)) {
				// Si la dénomination sociale est valide, retirer la classe is-invalid et le message d'erreur
				$("#denomination_socialeVendeurACreer").removeClass("is-invalid");
				$("#denomination-result").html("");
			} else {
				// Si la dénomination sociale n'est pas valide, ajouter la classe is-invalid et afficher le message d'erreur
				$("#denomination_socialeVendeurACreer").addClass("is-invalid");
				$("#denomination-result").html("Le numéro de téléphone n'est pas valide.");
			}
		}, 500);
	});

	// Fonction pour la vérification du siège social
	var siegeTimer;
	$("#siege_socialVendeurACreer").keyup(function(e) {
		clearTimeout(siegeTimer);
		var siege = $(this).val();
		siegeTimer = setTimeout(function() {
			if (checkAdresse(siege)) {
				// Si le siège social est valide, retirer la classe is-invalid et le message d'erreur
				$("#siege_socialVendeurACreer").removeClass("is-invalid");
				$("#siege-result").html("");
			} else {
				// Si le siège social n'est pas valide, ajouter la classe is-invalid et afficher le message d'erreur
				$("#siege_socialVendeurACreer").addClass("is-invalid");
				$("#siege-result").html("Le numéro de téléphone n'est pas valide.");
			}
		}, 500);
	});

	// Gérer l'affichage différentiel des champs en fonction de la sélection
	$('#Liste_statut').change(function() {
		var selectedOptionId = $(this).find('option:selected').attr('id');
		$('.champ').hide(); // Cacher tous les champs d'abord

		// Afficher les champs appropriés en fonction de l'option sélectionnée
		if (selectedOptionId === '1') {
			$("#Liste_statut").removeClass("is-invalid");
			$("#statut-result").html("");
			$("#champ1").show();
		} else if (selectedOptionId === '2') {
			$("#Liste_statut").removeClass("is-invalid");
			$("#statut-result").html("");
			$("#champ2").show();
		}
	});

	// activation de la case du force save
	$("#forcesave").click(function() {
		$("#prenomVendeurACreer").removeClass("is-invalid");
		$("#prenom-result").html("");
	});

	// click sur bouton cancel creation vendeur
	$("#boutoncancel").click(function() {
		$('#modal').modal('hide');
		$('#formulaire').trigger("reset");
		$('#messageerreurformulairemodale').html('');
	});


	// click sur bouton save creation vendeur
	$("#boutonsavevendeur").click(function() {
		var statut = ($('#Liste_statut').val());
		if (statut == '1') {
			if ($('#attestation_signeeVendeurACreer').val() != '1') {
				$("#attestation_signeeVendeurACreer").addClass("is-invalid");
				$('#attestation-result').html('Vous devez valider la signature de l\'attestation.');
				return;
			}
			$("#attestation_signeeVendeurACreer").removeClass("is-invalid");
			var denomination_socialeVendeurACreer = '';
			var siege_socialVendeurACreer = '';
			var attestation_signeeVendeurACreer = 'True';
		}
		else {
			if (statut == '2') {
				var ok = true
				if ($('#denomination_socialeVendeurACreer').val() == '') {
					$("#denomination_socialeVendeurACreer").addClass("is-invalid");
					$("#denomination-result").html("Ce champ est obligatoire.");
					ok = false;
				}
				if ($('#siege_socialVendeurACreer').val() == '') {
					$("#siege_socialVendeurACreer").addClass("is-invalid");
					$("#siege-result").html("Ce champ est obligatoire.");
					ok = false;
				}
				if (!ok) {
					return;
				}
				var denomination_socialeVendeurACreer = $('#denomination_socialeVendeurACreer').val();
				var siege_socialVendeurACreer = $('#siege_socialVendeurACreer').val();
				var attestation_signeeVendeurACreer = 'False';
			}
			else {
				$("#Liste_statut").addClass("is-invalid");
				$("#statut-result").html("Veuillez choisir un statut.");
			}
		}
		var validated = true;
		// Parcours de tous les champs du formulaire
		$('#formulaire').find('input, select, textarea').each(function() {
			// Vérifie si le champ actuel a la classe is-invalid
			if ($(this).hasClass('is-invalid')) {
				validated = false;
				return; // Sort du parcours dès qu'un champ non valide est trouvé
			}
		});
		if (validated) {

			var sdata = {
				'nomVendeurACreer': $('#nomVendeurACreer').val(),
				'prenomVendeurACreer': $('#prenomVendeurACreer').val(),
				'emailVendeurACreer': $('#emailVendeurACreer').val(),
				'telephoneVendeurACreer': $('#telephoneVendeurACreer').val(),
				'adresseVendeurACreer': $('#adresseVendeurACreer').val(),
				'codepostalVendeurACreer': $('#codepostalVendeurACreer').val(),
				'villeVendeurACreer': $('#villeVendeurACreer').val(),
				'denomination_socialeVendeurACreer': denomination_socialeVendeurACreer,
				'siege_socialVendeurACreer': siege_socialVendeurACreer,
				'attestation_signeeVendeurACreer': attestation_signeeVendeurACreer,
				'idVendeurEdition': $('#idVendeurEdition').val()
			}
			// Récupération du paramètre d'url
			var tParam;
			if (window.URLSearchParams) {
				const params = new URLSearchParams(window.location.search);
				tParam = params.get('t');
			} else {
				// Alternative pour les navigateurs qui ne prennent pas en charge URLSearchParams
				var match = window.location.search.match(/(\?|&)t=([^&]*)/);
				tParam = match ? decodeURIComponent(match[2]) : null;
			}

			// si id = update sinon insert
			if ($('#idVendeurEdition').val() == '') {
				$.post('ajax/user-add.php', sdata
					, function(data) {
						if (data.message2 == '1') {
							$('#formulaire').trigger("reset");
							$('#modal-body').hide();
							$('#boutoncancel').hide();
							$('#boutonsavevendeur').hide();
							$('#messageerreurformulairemodale').html('');
							$('#messageformulairemodale').html('<p class="bg-success">vendeur créé</p◊>');
							$("#boutonfermer").show();
							if (tParam && tParam === 'reception') {
								location.href = 'receptionjeux.php?id=' + data.message1;
							}
							else if (tParam === 'restitution') {
								location.href = 'restitutiondesjeux.php?id=' + data.message1;
							}
							else {
								location.href = location.href;
							}
						}
						if (data.message2 == '0') {
							$('#messageerreurformulairemodale').html('<p class="bg-danger">Erreur base de données.' + data.message1 + '</p>');
						}
					}
					, 'json');
			}
			else {
				$.post('ajax/user-update.php', sdata
					, function(data) {
						if (data.message2 == '1') {
							$('#formulaire').trigger("reset");
							$('#modal-body').hide();
							$('#boutoncancel').hide();
							$('#boutonsavevendeur').hide();
							$('#messageerreurformulairemodale').html('');
							$('#messageformulairemodale').html('<p class="bg-success">vendeur mis à jour</p>');
							$("#boutonfermer").show();
							if (tParam && tParam === 'reception') {
								location.href = 'receptionjeux.php?id=' + data.message1;
							}
							else if (tParam === 'restitution') {
								location.href = 'restitutiondesjeux.php?id=' + data.message1;
							}
							else {
								location.href = location.href;
							}

						}
						if (data.message2 == '0') {
							$('#messageerreurformulairemodale').html('<p class="bg-danger">Erreur base de données.' + data.message1 + '</p>');
						}
					}
					, 'json');
			}
		}
	});
	// Corrige le bug du champ prénom qui reste rouge même après saisie
	$("#prenomVendeurACreer").on('input', function () {
		const val = $(this).val().trim();
		if (val !== '') {
			$(this).removeClass("is-invalid");
			$("#prenom-result").html("");
		}
	});

}

/** Ouvrir la modale de création d'un vendeur */
function ouvreModaleCreation() {
	// click sur bouton nouveau vendeur, on reinit le formulaire et on force l'id à vide pour éviter le bug
	$('#idVendeurEdition').val('');
	$('#formulaire').trigger("reset");
	$('#modal-body').show();
	$('#boutoncancel').show();
	$('#boutonsavevendeur').show();
	$('.is-invalid').removeClass('is-invalid');
	$('.invalid-feedback').html('');
	$('#messageformulairemodale').html('');
	$('#messageerreurformulairemodale').html('');
	$('.champ').hide();
	$('#modal-titre').html('<br>Création d\'un vendeur');
	$('#modal').modal('show');
}

/** Ouvrir la modale de modification d'un vendeur existant */
function ouvreModaleModification(id) {
	$('#formulaire').trigger("reset");
	$('#modal-body').show();
	$('#boutoncancel').show();
	$('#boutonsavevendeur').show();
	$('#messageformulairemodale').html('');
	$('.is-invalid').removeClass('is-invalid');
	$('.invalid-feedback').html('');
	$('#messageerreurformulairemodale').html('');
	// recup le vendeur
	$.post('ajax/user-get.php', { 'id': id }, function(data) {
		//console.log(data);
		var obj = jQuery.parseJSON(data);
		if (obj.message2 == '1') {
			$("#idVendeurEdition").val(obj.message1['id']);
			$("#nomVendeurACreer").val(obj.message1['nom']);
			$("#prenomVendeurACreer").val(obj.message1['prenom']);
			$("#emailVendeurACreer").val(obj.message1['email']);
			$("#telephoneVendeurACreer").val(obj.message1['telephone']);
			$("#adresseVendeurACreer").val(obj.message1['adresse']);
			$("#codepostalVendeurACreer").val(obj.message1['codepostal']);
			$("#villeVendeurACreer").val(obj.message1['ville']);
			$('.champ').hide();
			if (obj.message1['attestation_signee'] == 'True') {
				$('#Liste_statut').val(1);
				$("#champ1").show();
				$("#attestation_signeeVendeurACreer").val(1)
			}
			else {
				if (obj.message1['denomination_sociale'] != '') {
					console.log(obj.message1['denomination_sociale']);
					$('#Liste_statut').val(2);
					$("#champ2,#champ3").show();
					$("#denomination_socialeVendeurACreer").val(obj.message1['denomination_sociale']);
					$("#siege_socialVendeurACreer").val(obj.message1['siege_social']);
				}
				else {
					$('#Liste_statut').val(1);
					$("#champ1").show();
					$("#attestation_signeeVendeurACreer").val(0)
				}
			}
			$('#modal-titre').html('<br>Modification du vendeur ' + obj.message1['prenom'] + ' ' + obj.message1['nom']);
			$('#modal').modal('show');
		}
	});
}