/**
 * Ce fichier JavaScript gère la fonctionnalité d'affichage des ventes de jeux dans une table DataTable.
 * Il utilise jQuery pour détecter le chargement du document, puis initialise la table DataTable avec les données AJAX chargées depuis le serveur.
 * Les données sont affichées dans une table avec les colonnes suivantes :
 * - Date de la transaction
 * - Nombre de jeux vendus
 * - Montant total de la transaction
 * - Montant perçu
 * - Montant rendu
 * - Moyen de paiement (affiché sous forme d'icone pour les chèques, les cartes bancaires ou l'espèces)
 * - Acheteur (affiché sous forme de bouton pour modifier ou ajouter un acheteur)
 * Les boutons "Modifier Acheteur" permettent de modifier l'acheteur associé à une transaction existante, tandis que les boutons "Ajouter Acheteur"
 * permettent d'ajouter un nouvel acheteur à une transaction.
 * Les fonctionnalités de modification et d'ajout d'acheteur sont gérées via la modale acheteur.
 * Les données sont chargées via une requête AJAX vers '../main/ajax/get-ventes.php'.
 */

$(document).ready(function() {
	$("#showVente").click(function() {
		window.location = '?page=ventedesjeux';
	});

	// Initialiser le tableau DataTable
	var table = $('#vendeurs').DataTable({
		scrollY: 500,
		language:{
			url: "Json/fr-FR.json"
		},
		// Définir les colonnes
		columns: [
			{ data: 'date' },
			{ data: 'jeux', defaultContent: '' },
			{ data: 'nbjeux', defaultContent: 0 },
			{ data: 'montantTotal' },
			{ data: 'montantPercu' },
			{ data: 'montantRendu' },
			{
				data: 'paiement',
				render: function(data, type, row) {
					var icon = '';
					if (data.startsWith("ch")) {
						icon = '<img src="img/cheque.png" width="25px"></i>'; // Icone de chèque
					} else if (data.startsWith("cb")) {
						icon = '<i class="bi bi-credit-card" style="font-size: 1.5em"></i>'; // Icone de carte bancaire
					} else {
						icon = '<i class="bi bi-cash" style="font-size: 1.5em"></i>'; // Icone d'espèces
					}
					return icon;
				}
			},
			{
				data: null,
				render: function(data, type, row) {
					var acheteurCellContent = '';
					if (row.id_acheteur !== null && row.nom !== null && row.prenom !== null) {
						acheteurCellContent = '<button class="btn btn-link edit-acheteur" data-id-acheteur="' + row.id_acheteur + '">'+row.prenom+' '+row.nom+' '+row.email+'</button>';
					} else {
						acheteurCellContent = '<button class="btn btn-link create-acheteur" data-id-transaction="' + row.id_transaction + '">Ajouter un acheteur</button>';
					}
					return acheteurCellContent;
				}
			}
		],
		// Charger les données via AJAX
		ajax: {
			url: 'index.php?page=api/ventes',
			dataSrc: ''
		}
	});

	// Gérer le clic sur les boutons "Modifier Acheteur"
	$('#vendeurs tbody').on('click', 'button.edit-acheteur', function() {
		var idAcheteur = $(this).data('id-acheteur');
		var idTransaction = table.row($(this).closest('tr')).data().id_transaction;
		openModificationAcheteurModal(idAcheteur, idTransaction);
	});

	// Gérer le clic sur les boutons "Ajouter Acheteur"
	$('#vendeurs tbody').on('click', 'button.create-acheteur', function() {
		var idTransaction = $(this).data('id-transaction');
		openCreationAcheteurModal(idTransaction);
	});

});