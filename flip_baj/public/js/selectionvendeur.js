/** Utilise la modale de création/édition de vendeur décrite dans modalevendeur.js et modalevendeur.php :
 *  * documentReadyDeLaModale dans $(document).ready
 *  * windowOnloadDeLaModale dans window.onload
 *  * appels de ouvreModaleModification et ouvreModaleCreation
 * 
 *  La table des vendeurs est peuplée dans le php, il s'agit donc ici de définir l'effet des clics et double-clic,
 *  et de gérer la recherche sur la table.
 */


$(document).ready(function() {
    // Datatable
    var table = $('#vendeurs').DataTable({
        "processing": true,
        "serverSide": false,
        "language": {
            "searchPlaceholder": "Rechercher le vendeur...",
            "url": "Json/fr-FR.json"
        },
        "ajax": {
            "url": "ajax/vendeurs-get.php",
            "type": "POST"
        },
        "columns": [
            { "data": "nom" },
            { "data": "prenom" },
            { "data": "email" },
            { "data": "telephone" },
            { "data": "nbjeuxpasrecus" },
            { "data": "nbjeuxstock" },
            { "data": "nbjeuxvendus" },
            { "data": "nbjeuxrendus" },
            { "data": "nbjeuxdonnes" },
            { 
                "data": "idDuVendeur",
                "render": function(data, type, row) {
                    return '<button class="btn btn-primary editVendeur" data-vendeur="' + data + '"><i class="bi bi-pencil"></i></button>';
                }
            },
            { "data": "idDuVendeur", "visible": false }
        ]
    });
    
    documentReadyDeLaModale();

    // Lien d'édition (au clic sur le bouton éditer)
    $('#vendeurs tbody').on('click', '.editVendeur', function() {
        var idVendeur = $(this).attr('data-vendeur');
        ouvreModaleModification(idVendeur);
    });

	// click sur bouton nouveau vendeur, appel de la modale
	$("#showModal").click(function() { ouvreModaleCreation(); });
	
    // Double-clic sur une ligne (ouvrir la page du vendeur)
    $('#vendeurs tbody').on('dblclick', 'tr', function() {
        var idVendeur = table.row(this).data().idDuVendeur;
        if ($_GET('t') == 'reception') {
            openEdit('receptionjeux.php?id=' + idVendeur);
        }
        if ($_GET('t') == 'restitution') {
            openEdit('restitutiondesjeux.php?id=' + idVendeur);
        }
    });

    // Focus sur la recherche après un délai de 800ms
    var input = $('div.dataTables_filter input');
    setTimeout(function() {
        input.focus();
    }, 80);
});

/**
 * ouvre une nouvelle location avec l'id en parametre
 */
function openEdit(url) {
	window.location = url;
}