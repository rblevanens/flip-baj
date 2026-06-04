/**
 * Utilise la modale de création/édition de vendeur décrite dans modalevendeur.js et modalevendeur.php :
 *  * documentReadyDeLaModale dans $(document).ready
 *  * windowOnloadDeLaModale dans window.onload
 *  * appels de ouvreModaleModification et ouvreModaleCreation
 *
 *  Les informations vendeurs sont peuplées dans le php, mais les tables de jeux sont gérées en javascript.
 *  @todo : plein de trucs à décrire
 */

var restevendeur = 0;
$(document).ready(function() {
	/* On stock le contenu initial du div contenant les champs de formulaires pour le réinjecter
	plus tard quand on voudra remettre les champs à leurs valeurs initiales.*/
	var htmlcontent_ul_remb = $("#ul_remb").html()

	/**
	 * Met à jour les champs monétaires du vendeur en cours avec la requête de base.
	 * @function majinfosmonetairesvendeur
	 */
	function majinfosmonetairesvendeur() {
		//console.log(don);
		var id = $("#idVendeurEdition").val();
		//console.log('id'+id);
		var totalJeuxVendus = 0;
		var dejaRembourse = 0;
		// TotalJeuxVendusVendeurSelectionne
		$.ajax({
			type: 'POST',
			url: 'ajax/jeuxliste-gettotalvente.php',
			data: {
				id: id
			},
			success: function(data) {
				if (data.message2 == '1') totalJeuxVendus = parseInt(data.message1, 10);
				console.log(data);
			},
			dataType: 'json',
			async: false
		});
		// DejaPayeVendeurSelectionne
		$.ajax({
			type: 'POST',
			url: 'ajax/jeuxliste-getdejarembourse.php',
			data: {
				id: id
			},
			success: function(data) {
				if (data.message2 == '1') dejaRembourse = parseInt(data.message1, 10);
				//console.log(data);
			},
			dataType: 'json',
			async: false
		});

		//console.log(totalJeuxVendus);
		//console.log(dejaRembourse);
		//console.log(totalJeuxVendus);
		// ResteVendeurSelectionne
		restevendeur = totalJeuxVendus - dejaRembourse;

		$('#DejaPayeVendeurSelectionne').html(dejaRembourse + '&nbsp;€');
		$('#ResteVendeurSelectionne').html(restevendeur + '&nbsp;€');              // A payer avant don
		$('#TotalJeuxVendusVendeurSelectionne').html(totalJeuxVendus + '&nbsp;€'); // Total des jeux vendus

	}
	/**
	 * Affiche la liste des remboursements effectués.
	 * @function AfficherRemb
	 */
	function AfficherRemb() {
		var id = $("#idVendeurEdition").val();
		var listeremb = '';
		$.ajax({
			type: 'POST',
			url: 'ajax/remboursementsliste-get.php',
			data: {
				id: id
			},
			success: function(data) {
				if (data.message2 == '1') listeremb = data.message1;
				console.log(data);
				$("#listeremb").html(listeremb);
			},
			dataType: 'json',
		});
	}
	/**
	 * Affiche la liste des dons effectués.
	 * @function AfficherDons
	 */
	function AfficherDons() {
		var id = $("#idVendeurEdition").val();
		var listedons = '';
		$.ajax({
			type: 'POST',
			url: 'ajax/donsliste-get.php',
			data: {
				id: id
			},
			success: function(data) {
				if (data.message2 == '1') listedons = data.message1;
				console.log(data);
				$("#listedons").html(listedons);
			},
			dataType: 'json',
		});
	}

	/**
	 * Initialise la table des jeux en stock.
	 * @type {DataTable}
	 */
	var tablejeuxenstock = $('#jeuxenstock').DataTable({
		paging: false,
		searching: false,
		info: false,
		processing: true,
		serverSide: false,
		language: {
			"url": "Json/fr-FR.json"
		},
		ajax: {
			type: 'POST',
			url: 'ajax/jeuxliste-get.php',
			data: {
				idVendeurEdition: $('#idVendeurEdition').val(),
				statutJeu: STATUS_JEUX_EN_STOCK
			},
		},
		columns: [
			{ data: "Code", name: "code_barre", title: "Code", width: "93px", className: "jeu-codeBarre" },
			{ data: "Jeu", name: "nj", title: "Jeu", width: "310px" },
			{ data: "Vendu", name: "Vendu", title: "Vendu", orderable: false, className: "jeu-prix-vendu", render: function(data, type, row) { return data + ' €'; } },
			{ data: "Rendu", name: "Rendu", title: "Rendu", orderable: false, className: "jeu-prix-rendu", render: function(data, type, row) { return data + ' €'; } },
			{ data: "vigilance", name: "vigilance", title: "vigilance", visible: false },
			{
				data: "Action", name: "Action", title: "Action", width: "158px", orderable: false, render: function(data, type, row) {
					var actionButtons = ''
					if ($('#idVendeurEdition').val() != 1) {
						actionButtons += '&nbsp;&nbsp;&nbsp;&nbsp;<a data-jeu="' + row.DT_RowId + '" class="toFestival" href="#" alt="Donner le jeu de code ' + VraiCodeBarre(row.Code) + ' au festival" title="Donner le jeu de code ' + VraiCodeBarre(row.Code) + ' au festival"><i class="bi bi-gift"></i></a>';
					}
					if (row.vigilance != "Oui") {
						actionButtons += '&nbsp;&nbsp;&nbsp;&nbsp;<a data-jeu="' + row.DT_RowId + '" class="toLost" href="#" alt="Flagger le jeu de code ' + VraiCodeBarre(row.Code) + ' comme non retrouvé" title="Flagger le jeu de code ' + VraiCodeBarre(row.Code) + ' comme suspect ?"><i class="bi bi-exclamation-circle"></i></a>';
					}
					return actionButtons;
				}
			}
		],
		createdRow: function(row, data, rowIndex) {
			var tab = Object.getOwnPropertyNames(data).sort();
			for (var i = 0; i < tab.length; i++) {
				$(row).attr('data-' + tab[i], data[tab[i]]);
			}
			if (data.vigilance === "Oui") {
				$(row).addClass('fst-italic');
			}
			$(row).attr('data-row-id', data["DT_RowId"]);
		},
		drawCallback: function(settings) {
			$('#nbJeuxStockVendeurSelectionne').html(tablejeuxenstock.rows().count());
		}
	});


	/**
	 * Initialise la table des jeux vendus.
	 * @type {DataTable}
	 */
	var tablejeuxnvendus = $('#jeuxvendus').DataTable({
		paging: false,
		searching: false,
		info: false,
		processing: true,
		serverSide: true,
		language: {
			"url": "Json/fr-FR.json"
		},
		ajax: {
			type: 'POST',
			url: 'ajax/jeuxliste-get.php',
			data: {
				idVendeurEdition: $('#idVendeurEdition').val(),
				statutJeu: STATUS_JEUX_VENDUS
			},
		},
		columns: [
			{ data: "Code", name: "code_barre", title: "Code", width: "93px", className: "jeu-codeBarre" },
			{ data: "Jeu", name: "nj", title: "Jeu", width: "310px" },
			{ data: "Vendu", name: "Vendu", title: "Vendu", orderable: false, className: "colonneeditable", render: function(data, type, row) { return data + ' €'; } },
			{ data: "Rendu", name: "Rendu", title: "Rendu", orderable: false, className: "colonneeditable", render: function(data, type, row) { return data + ' €'; } },
			{ data: "vigilance", name: "vigilance", title: "Vigilance", visible: false },
			{
				data: "DateSortieStock", name: "date_sortie_stock", title: "Vente", width: "158px", render: function(data) {
					var madate = mysqlTimeStampToDate(data);
					var month = madate.getMonth() + 1;
					var NomDuJour = "Inconnu";
					switch (madate.getDay()) {
						case 1: NomDuJour = "Lundi"; break;
						case 2: NomDuJour = "Mardi"; break;
						case 3: NomDuJour = "Mercredi"; break;
						case 4: NomDuJour = "Jeudi"; break;
						case 5: NomDuJour = "Vendredi"; break;
						case 6: NomDuJour = "Samedi"; break;
						case 0: NomDuJour = "Dimanche"; break;
					}
					return NomDuJour + " " + madate.getDate() + "/" + (month.length > 1 ? month : "0" + month) + "/" + madate.getFullYear() + " à " + madate.getHours() + "h" + (madate.getMinutes() > 9 ? madate.getMinutes() : "0" + madate.getMinutes());
				}
			}
		],
		createdRow: function(row, data, rowIndex) {
			var tab = Object.getOwnPropertyNames(data).sort();
			for (var i = 0; i < tab.length; i++) {
				$(row).attr('data-' + tab[i], data[tab[i]]);
			}
			if (data.vigilance === "Oui") {
				$(row).addClass('fst-italic');
			}
		},
		drawCallback: function() {
			$('#nbJeuxVendusVendeurSelectionne').html(tablejeuxnvendus.rows().count());
		},
		"oLanguage": {
			"sInfo": "_TOTAL_ jeux",
			"sInfoEmpty": "Pas de jeux"
		}
	});

	/**
	 * Initialise la table des jeux rendus.
	 * @type {DataTable}
	 */
	var tablejeuxnrendus = $('#jeuxrendus').DataTable({
		paging: false,
		searching: false,
		info: false,
		processing: true,
		serverSide: true,
		language: {
			"url": "Json/fr-FR.json"
		},
		ajax: {
			type: 'POST',
			url: 'ajax/jeuxliste-get.php',
			data: {
				idVendeurEdition: $('#idVendeurEdition').val(),
				statutJeu: STATUS_JEUX_RENDUS
			},
		},
		columns: [
			{ data: "Code", name: "code_barre", title: "Code", width: "93px", className: "jeu-codeBarre" },
			{ data: "Jeu", name: "nj", title: "Jeu", width: "310px" },
			{ data: "Vendu", name: "Vendu", title: "Vendu", orderable: false, className: "colonneeditable", render: function(data, type, row) { return data + ' €'; } },
			{ data: "Rendu", name: "Rendu", title: "Rendu", orderable: false, className: "colonneeditable", render: function(data, type, row) { return data + ' €'; } },
			{ data: "vigilance", name: "vigilance", title: "Vigilance", visible: false },
			{
				data: "DateSortieStock", name: "date_sortie_stock", title: "Restitution", width: "158px", render: function(data) {
					var madate = mysqlTimeStampToDate(data);
					var month = madate.getMonth() + 1;
					var NomDuJour = "Inconnu";
					switch (madate.getDay()) {
						case 1: NomDuJour = "Lundi"; break;
						case 2: NomDuJour = "Mardi"; break;
						case 3: NomDuJour = "Mercredi"; break;
						case 4: NomDuJour = "Jeudi"; break;
						case 5: NomDuJour = "Vendredi"; break;
						case 6: NomDuJour = "Samedi"; break;
						case 0: NomDuJour = "Dimanche"; break;
					}
					return NomDuJour + " " + madate.getDate() + "/" + (month.length > 1 ? month : "0" + month) + "/" + madate.getFullYear() + " à " + madate.getHours() + "h" + (madate.getMinutes() > 9 ? madate.getMinutes() : "0" + madate.getMinutes());
				}
			}
		],
		createdRow: function(row, data, rowIndex) {
			var tab = Object.getOwnPropertyNames(data).sort();
			for (var i = 0; i < tab.length; i++) {
				$(row).attr('data-' + tab[i], data[tab[i]]);
			}
			if (data.vigilance === "Oui") {
				$(row).addClass('fst-italic');
			}
		},
		drawCallback: function() {
			$('#nbJeuxRendusVendeurSelectionne').html(tablejeuxnrendus.rows().count());
		}
	});


	/**
	 * Initialise la table des jeux donnés.
	 * @type {DataTable}
	 */
	var tablejeuxndonnes = $('#jeuxdonnes').DataTable({
		paging: false,
		searching: false,
		info: false,
		processing: true,
		serverSide: true,
		language: {
			"url": "Json/fr-FR.json"
		},
		ajax: {
			type: 'POST',
			url: 'ajax/jeuxliste-get.php',
			data: {
				idVendeurEdition: $('#idVendeurEdition').val(),
				statutJeu: '6'
			},
		},
		columns: [
			{ data: "Code", name: "code_barre", title: "Code", width: "93px", className: "jeu-codeBarre" },
			{ data: "Jeu", name: "nj", title: "Jeu", width: "310px" },
			{ data: "Vendu", name: "Vendu", title: "Vendu", orderable: false, render: function(data, type, row) { return data + ' €'; } },
			{ data: "Rendu", name: "Rendu", title: "Rendu", orderable: false, render: function(data, type, row) { return data + ' €'; } },
			{ data: "vigilance", name: "vigilance", title: "Vigilance", visible: false },
			{
				data: "DateSortieStock", name: "date_sortie_stock", title: "Don", width: "158px", render: function(data) {
					var madate = mysqlTimeStampToDate(data);
					var month = madate.getMonth() + 1;
					var NomDuJour = "Inconnu";
					switch (madate.getDay()) {
						case 1: NomDuJour = "Lundi"; break;
						case 2: NomDuJour = "Mardi"; break;
						case 3: NomDuJour = "Mercredi"; break;
						case 4: NomDuJour = "Jeudi"; break;
						case 5: NomDuJour = "Vendredi"; break;
						case 6: NomDuJour = "Samedi"; break;
						case 0: NomDuJour = "Dimanche"; break;
					}
					return NomDuJour + " " + madate.getDate() + "/" + (month.length > 1 ? month : "0" + month) + "/" + madate.getFullYear() + " à " + madate.getHours() + "h" + (madate.getMinutes() > 9 ? madate.getMinutes() : "0" + madate.getMinutes());
				}
			}
		],
		createdRow: function(row, data, rowIndex) {
			var tab = Object.getOwnPropertyNames(data).sort();
			for (var i = 0; i < tab.length; i++) {
				$(row).attr('data-' + tab[i], data[tab[i]]);
			}
			if (data.vigilance === "Oui") {
				$(row).addClass('fst-italic');
			}
		},
		drawCallback: function() {
			$('#nbJeuxDonnesVendeurSelectionne').html(tablejeuxndonnes.rows().count());
		}
	});

	/**
	 * Gère le clic sur les liens de don au festival dans les tableaux.
	 * @function
	 */
	$('tbody').on('click', 'a.toFestival', function() {
		var ligne = $(this).closest('tr');
		if (AfficherPopUp("Voulez-vous vraiment donner ce jeu au festival ?", confirmation)) {
			$.ajax({
				type: 'POST',
				url: 'ajax/jeuxliste-updatefestival.php',
				data: {
					'id': ligne.data("dt_rowid"),
					ip: $('#ip').val()
				},
				success: function(data) {
					if (data.message2 == '0') AfficherPopUp("Operation impossible", alerte);;
					tablejeuxenstock.ajax.reload();
					tablejeuxndonnes.ajax.reload();
				},
				error: function(data) {
					alert('Exception:', data.message1);
				},
				dataType: 'json',
				async: false
			});
		}
	});

	/**
	 * Gère le clic sur les liens "suspect".
	 * @function
	 */
	$('tbody').on('click', 'a.toLost', function() {
		var ligne = $(this).closest('tr');
		if (AfficherPopUp("Voulez-vous vraiment passer ce jeu en suspect ?", confirmation)) {
			$.ajax({
				type: 'POST',
				url: 'ajax/jeuxliste-setvigilance.php',
				data: { 'id': ligne.data("dt_rowid") },
				success: function(data) {
					if (data.message2 == '0') AfficherPopUp("Operation impossible", alerte);;
					tablejeuxenstock.ajax.reload();
				},
				dataType: 'json',
				async: false
			});
		}
	});

	/**
	 * Gère le double-clic sur une ligne pour sortir un jeu du stock (Restitution MVC).
	 * @function
	 */
	$('#jeuxenstock tbody').on('dblclick', 'tr', function() {
		$('body').addClass('waiting');

		// On récupère les infos depuis les attributs "data-" de la ligne cliquée
		let codeBarreJeu = $(this).data('code');
		let idVendeur = $('#idVendeurEdition').val();

		$.ajax({
			url: 'api/restituer-jeu',
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify({
				code_barre: codeBarreJeu,
				id_vendeur: idVendeur
			}),
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					// Le jeu est rendu, on met à jour les deux tableaux en arrière-plan sans perdre la page
					tablejeuxenstock.ajax.reload(null, false);
					tablejeuxnrendus.ajax.reload(null, false);
				} else {
					alert("Impossible de restituer le jeu : " + response.message);
				}
			},
			error: function() {
				alert("Erreur réseau lors de la communication avec le serveur.");
			},
			complete: function() {
				// S'exécute toujours, que ce soit un succès ou une erreur
				$('body').removeClass('waiting');
			}
		});
	});

	/**
	 * Gère le double-clic sur une ligne pour remettre un jeu en stock.
	 * @function
	 */
	$('#jeuxrendus tbody').on('dblclick', 'tr', function() {
		/*console.log($(this).data('dt_rowid'));
		console.log($(this).data('code'));
		console.log(STATUS_JEUX_RENDUS);*/
		$('body').addClass('waiting');
		$.ajax({
			type: 'POST',
			url: 'ajax/jeuxliste-update.php',
			data: { 'id': $(this).data('dt_rowid'), 'statut': STATUS_JEUX_EN_STOCK, 'old_id_statut': '4', 'codebarre': $(this).data('code') },
			success: function(data) {
				if (data.message2 == '0') AfficherPopUp("Operation impossible", alerte);;
			},
			dataType: 'json',
			async: false
		});
		// maj des compteurs
		tablejeuxenstock.ajax.reload();
		tablejeuxnrendus.ajax.reload();
		$('body').removeClass('waiting');
	});

	/**
	 * Cache ou affiche le corps de la table au clic sur l'en-tête.
	 * @function
	 */
	$(".reduire").click(function() {
		var tableId = $(this).data("table-id");
		$("#" + tableId).find("tbody").fadeToggle();
	});
	/**
	 * Cache ou affiche toutes les tables réductibles.
	 * @function
	 */
	$("#tout-reduire").click(function() {
		$(".reductible").fadeToggle();
	});

	majinfosmonetairesvendeur();


	/**
	 * Gère le clic sur le bouton de validation de remboursement (Version MVC).
	 * @function
	 */
	$('#valider_remboursement_bouton').click(function() {
		var id = $("#idVendeurEdition").val();
		var montant_remboursement = parseInt($("#montant_remboursement").val(), 10);
		var montant_don = parseInt($("#montant_don").val(), 10);

		if (isNaN(montant_remboursement)) montant_remboursement = 0;
		if (isNaN(montant_don)) montant_don = 0;

		if (montant_don == 0 && montant_remboursement == 0) {
			alert('Veuillez remplir le montant du remboursement');
			return;
		}

		var type_remb = 'erreur';
		if (montant_remboursement == 0) type_remb = 'no_remb';
		if ($('select[name="Moyen-remboursement"]').val() == '1') type_remb = TYPE_TRANS_ESPECES;
		else if ($('select[name="Moyen-remboursement"]').val() == '2') type_remb = TYPE_TRANS_CHEQUE;
		else if ($('select[name="Moyen-remboursement"]').val() == '3') type_remb = TYPE_TRANS_PAYPAL;
		else if ($('select[name="Moyen-remboursement"]').val() == '4') type_remb = TYPE_TRANS_VIREMENT;

		if (type_remb == 'erreur') {
			alert('Veuillez sélectionner un moyen de remboursement.');
			return;
		}
		if (montant_don < 0 || montant_remboursement < 0) {
			alert('Veuillez renseigner un montant positif.');
			return;
		}
		if (restevendeur < (montant_don + montant_remboursement)) {
			alert('Veuillez renseigner un montant inférieur ou égal au montant dû.');
			return;
		}
		if (!confirm('Le vendeur récupère un montant de ' + montant_remboursement + '€ et donne ' + montant_don + '€ à l\'association du jeu.')) {
			return;
		}

		// Vérification du montant en caisse si le remboursement est en espèces
		if (type_remb === TYPE_TRANS_ESPECES) {
			var montantARembourser = montant_remboursement;
			var argentEnCaisse = 0;

			$.ajax({
				url: 'ajax/argentencaisse-get.php',
				type: 'GET',
				dataType: 'json',
				async: false,
				success: function(data) {
					argentEnCaisse = parseFloat(data.message1) || 0;
				},
				error: function() {
					alert('Impossible de vérifier le montant en caisse.');
					argentEnCaisse = -1;
				}
			});

			if (argentEnCaisse < montantARembourser) {
				alert(" Il n'y a pas assez d'argent en caisse pour effectuer ce remboursement en espèces.\nMerci de prévenir un responsable.");
				return;
			}
		}

		// --- L'APPEL UNIQUE MVC VERS LA TRANSACTION SÉCURISÉE ---
		$.ajax({
			url: 'api/cloturer-restitution',
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify({
				id_vendeur: id,
				montant_remboursement: montant_remboursement,
				type_remboursement: type_remb,
				montant_don: montant_don
			}),
			dataType: 'json',
			success: function(response) {
				if (response.success) {

					// 1. Mise à jour visuelle de l'interface
					majinfosmonetairesvendeur();
					AfficherRemb();
					AfficherDons();
					$("#ul_remb").html(htmlcontent_ul_remb);

					// 2. Génération de la facture PDF
					$.ajax({
						url: './pdf/generer_pdf.php',
						type: 'GET',
						data: { 'idrestitution': id },
						success: function() {
							console.log('Facture générée avec succès');

							// 3. Envoi du mail différé de 5 secondes
							setTimeout(function() {
								$.ajax({
									url: 'ajax/send-mail.php',
									type: 'POST',
									data: { 'idrestitution': id }
								});
							}, 5000);

							// Fin de la boucle, on recharge pour vider la page proprement
							alert("Restitution clôturée avec succès !");
							location.reload();
						},
						error: function() {
							// Même si le PDF plante, la transaction financière a réussi en base.
							alert("Restitution validée, mais erreur lors de la génération du PDF.");
							location.reload();
						}
					});
				} else {
					alert("Erreur critique lors de la restitution : " + response.message);
				}
			},
			error: function() {
				alert("Erreur réseau lors de la communication avec le serveur.");
			}
		});
	});

	
	// Gestion de la modale de modification vendeur
	documentReadyDeLaModale();
	$("#showModal").click(function() { ouvreModaleModification($('#idVendeurEdition').val()); });

});