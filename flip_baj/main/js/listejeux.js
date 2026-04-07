/**
 * Script JavaScript pour gérer la liste des jeux.
 * Utilise DataTables pour la manipulation et l'affichage des données.
 */

// Attente du chargement du document
$(document).ready(function() {

	// Liste des options pour le statut
	var optionsStatut = [
		{ value: '1', label: 'Pas reçu' },
		{ value: '2', label: 'En stock' },
		{ value: '3', label: 'Vendu' },
		{ value: '4', label: 'Rendu' },
		{ value: '5', label: 'Dans un panier' },
		{ value: '6', label: 'Donné' },
		{ value: '7', label: 'Au box' },
		{ value: '0', label: 'Non précisé' }
	];

	// Configuration de la table des jeux
	var tableJeuxEnStock = $('#jeuxenstock').DataTable({
		paging: false,
		bFilter: false,
		scrollY: 500,
		bPaginate: false,
		bInfo: true,
		processing: true,
		serverSide: false,
		language: {
			"url": "Json/fr-FR.json"
		},
		ajax: {
			type: 'POST',
			url: 'ajax/jeuxliste-getenstockspeed.php',
			data: function(data) {
				var nom = $('#searchNom').val();
				var code = $('#searchCode').val();
				var vigilance = $('#searchVigilance').val();
				var statut = $('#searchStatut').val();

				if (nom) data.nom_jeu = nom;
				if (code) data.code_barre = code;
				if (vigilance !== "") data.vigilance = vigilance;
				if (statut !== "0") data.idStatut = statut;
			}
		},
		columns: [
			{ data: "Jeu", name: "Jeu", title: "Jeu" },
			{ data: "Code", name: "Code", title: "Code barre", className: "jeu-codeBarre" },
			{ data: "Vendu", name: "Vendu", title: "Prix", render: function(data, type, row) { return data + ' €'; } },
			{
				data: "Vendeur", name: "Vendeur", title: "Vendeur", render: function(data, type, row) {
					return '<a data-jeu="' + row.DT_RowId + '" class="toFestival" href="receptionjeux.php?id=' + row.idvendeur + '">' + row.Vendeur + '</a>';
				}
			},
			{ data: "date_reception", name: "date_reception", title: "Date de réception" },
			{ data: "vigilance", name: "vigilance", title: "Vigilance" },
			{ data: "idstatut", "name": "idstatut", "title": "idStatut", "visible": false }, // Colonne cachée
			{
				data: "statut", "name": "statut", "title": "Statut", "render": function(data, type, row) {
					// Bouton d'édition du statut
					return '<button class="btn btn-link btn-edit-statut" data-id="' + row.DT_RowId + '">' + row.statut + '</button>';
				}
			}
		],
		createdRow: function(row, data, rowIndex) {
			// Attributs data- pour chaque propriété de l'objet
			var propertyNames = Object.getOwnPropertyNames(data).sort();
			for (var i = 0; i < propertyNames.length; i++) {
				$(row).attr('data-' + propertyNames[i], data[propertyNames[i]]);
			}
		},
		drawCallback: function(settings) {
			// Gestionnaire d'événements pour le bouton d'édition du statut
			$('#jeuxenstock tbody').on('click', '.btn-edit-statut', function() {
				var id = $(this).data('id');
				var statutActuel = tableJeuxEnStock.row('#' + id).data().idstatut;
				var code_barre = tableJeuxEnStock.row('#' + id).data().Code;
				console.log(code_barre);
				// Créer une liste déroulante avec les options de statut
				var selectStatut = $('#statutSelect');
				selectStatut.empty(); // Supprimer les éventuelles anciennes options

				optionsStatut.forEach(function(option) {
					selectStatut.append($('<option>', { value: option.value, text: option.label }));
				});

				// Sélectionner l'option correspondant au statut actuel
				selectStatut.val(statutActuel);
				
				// Vider le champ mot de passe
				$('#mdpInput').val('');

				// Afficher une boîte de dialogue modale Bootstrap avec le formulaire de mise à jour
				$('#editStatutModal').modal('show');

				// Passer les données nécessaires au bouton de confirmation de la modale
				$('#editStatutModalConfirm').data('statutActuel', statutActuel);
				$('#editStatutModalConfirm').data('id', id);
				$('#editStatutModalConfirm').data('codeBarre', code_barre);
			});
		}
	});

	// Double-clic sur une ligne pour aller à la réception des jeux du vendeur
	$('#jeuxenstock tbody').on('dblclick', 'tr', function() {
		var numrow = $(this).attr('data-idvendeur');
		$(location).attr('href', 'receptionjeux.php?id=' + numrow);
	});

	// Fonction qui crypte le mot de passe en utilisant SHA-256.
	// @param {string} motDePasse - Le mot de passe à crypter.
	// @returns {string} Le mot de passe crypté.
	
	function crypterMotDePasse(motDePasse) {
		// Utilise CryptoJS pour hacher le mot de passe avec SHA-256
	var hash = CryptoJS.SHA256(motDePasse).toString(CryptoJS.enc.Hex);
	return hash;
	}

	// /**
	//  * Fonction qui vérifie si le mot de passe entré correspond au mot de passe haché stocké.
	//  * @param {string} motDePasseEntree - Le mot de passe entré par l'utilisateur.
	//  * @param {string} motDePasseHacheStocke - Le mot de passe haché stocké en base de données.
	//  * @returns {boolean} true si les mots de passe correspondent, sinon false.
	//  */
	function verifierMotDePasse(motDePasseEntree, motDePasseHacheStocke) {
		// Hache le mot de passe entré
		var motDePasseHacheEntree = crypterMotDePasse(motDePasseEntree);

		// Compare les hachages
		return motDePasseHacheEntree === motDePasseHacheStocke;
	}


	// Attachez l'événement de clic du bouton de confirmation en dehors de la fonction de gestionnaire d'événements du bouton d'édition du statut
	$('#editStatutModalConfirm').on('click', function(e) {
		e.preventDefault();
		var selectStatut = $('#statutSelect');
		var nouvelleValeur = selectStatut.val();
		var mdp = $('#mdpInput').val();
		var statutActuel = $(this).data('statutActuel');
		var id = $(this).data('id');
		var code_barre = $(this).data('codeBarre');
		var motDePasseStocke = "dee377cfd8cddfb8db49d63a6f80174955c594a7cb995d4a70c2f6f305286786";

		console.log(nouvelleValeur + ' , ' + statutActuel);

		if (nouvelleValeur != statutActuel) {
			// Vérifie le Mot de passe
			if (mdp !== null && mdp !== '') {
				if (verifierMotDePasse(mdp, motDePasseStocke)) {
					console.log("Mot de passe correct");
					// Envoyer les données mises à jour au serveur
					$.ajax({
						url: 'ajax/jeuxliste-update.php',
						type: 'POST',
						data: { id: id, statut: nouvelleValeur, codebarre: code_barre, old_id_statut: statutActuel },
						success: function(data) {
							// Gérer la réponse du serveur
							console.log(data);

							// Recharge la table pour refléter les modifications
							tableJeuxEnStock.ajax.reload();
							
							// Fermer la boîte de dialogue
							$('#editStatutModal').modal('hide');
						},
						error: function(error) {
							console.error('Erreur lors de la mise à jour du statut:', error);
						}
					});
				} else {
					console.log("Mot de passe incorrect");
					alert("Mot de passe incorrect");
				}
			} else {
				console.log("Pas de mdp renseigné");
				alert("Veuillez renseigner le mot de passe");
			}
		} else {
			console.log("Valeurs identiques");
			alert("Veuillez renseigner une valeur de statut différente");
		}
	});

	// Gestion du formulaire de recherche
	$('.search').on('change', function(e) {
		e.preventDefault();
		tableJeuxEnStock.ajax.reload();
	});
});