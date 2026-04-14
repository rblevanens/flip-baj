var id_transaction = '';
var paiement = "nondefinit";

$(document).ready(function() {

    //Création de la transaction
    if (id_transaction === '') {
        $.ajax({
            type: 'POST',
            url: 'ajax/transaction-add.php',
            data: {
                type: "panier",
                montantTotal: 0,
                montantPercu: 0,
                montantDon: 0,
                montantRendu: 0,
                paiement: paiement,
                ip: $("#ip").val(),
                id_phpbb_acheteur: ''
            },
            success: function(data) {
                if (data.message2 === '1') {
                    id_transaction = data.message1;
                } else {
                    alert('Erreur de base de données');
                }
            },
            dataType: 'json',
            async: false
        });
    }
    
    // Gestionnaire d'événement 'beforeunload'
    $(window).bind('beforeunload', function(event) {
        var confirmationMessage = 'Êtes-vous sûr de vouloir quitter ? Si vous aviez une transaction en cours, merci de le signaler aux responsables';
        return confirmationMessage;
    });

    $('select[name="type_transaction"]').change(function () {
        var res = $(this).find('option:selected').val();
        console.log("Type de paiement sélectionné :", res);
    
        switch (res) {
            case '0': // Aucun
                paiement = "nondefinit";
                $('#MontantPercu').prop('disabled', false);
                setTimeout(() => $('#MontantPercu').val(''), 50); // Forcer l'effacement juste après activation
                break;
    
            case '1': // Espèces
                paiement = "especes";
                $('#MontantPercu').prop('disabled', false);
                setTimeout(() => $('#MontantPercu').val(''), 50); // Forcer l'effacement juste après activation
                break;
    
            case '2': // CB
                paiement = "cb";
                $('#MontantPercu').prop('disabled', true); // rempli dans majinfosmonetaires
                break;
    
            case '3': // Chèque
                paiement = "chèque";
                $('#MontantPercu').prop('disabled', false);
                setTimeout(() => $('#MontantPercu').val(''), 50); // Forcer l'effacement juste après activation
                break;
        }
    
        majinfosmonetaires();
    });
    
    

    var SAC_ID, SAC_JEU, SAC_PRIX, SAC_CODEBARRE;

    var tablejeuxenstock = $('#jeuxenstock').DataTable({
        scrollY: 270,
        paging: false,
        bFilter: true,
        "bPaginate": false,
        "bInfo": false,
        "processing": true,
        "serverSide": false,
        language: {
            "url": "Json/fr-FR.json"
        },
        "ajax": {
            'type': 'POST',
            'url': 'ajax/jeuxliste-getenstockspeed.php',
            'data': {
                idStatut: STATUS_JEUX_EN_STOCK
            },
        },
        "columns": [
            { "data": "Jeu", "name": "Jeu", "title": "Jeu" },
            { "data": "Code", "name": "Code", "title": "Code barre", className: "jeu-codeBarre" },
            { "data": "Vendu", "name": "Vendu", "title": "Prix", render: function(data, type, row) { return data + ' €'; } },
            { "data": "Vendeur", "name": "Vendeur", "title": "Vendeur" }
        ],
        "createdRow": function(row, data, rowIndex) {
            var tab = Object.getOwnPropertyNames(data).sort()
            for (var i = 0; i < tab.length; i++) {
                $(row).attr('data-' + tab[i], data[tab[i]]);
            }
        }
    });

    var tablejeuxenvente = $('#jeuxenvente').DataTable({
        scrollY: 300,
        paging: false,
        bFilter: false,
        "bPaginate": false,
        "bInfo": false,
        "processing": true,
        "serverSide": false,
        language: {
            "url": "Json/fr-FR.json"
        },
        "columns": [
            { "data": "Jeu", "name": "Jeu", "title": "Jeu" },
            { "data": "Code", "name": "Code", "title": "Code barre", className: "jeu-codeBarre" },
            { "data": "Prix", "name": "Prix", "title": "Prix", render: function(data, type, row) { return data + ' €'; } },
            { "data": "Action", "name": "Action", "title": "Action" }
        ],
        "createdRow": function(row, data, rowIndex) {
            var tab = Object.getOwnPropertyNames(data).sort()
            for (var i = 0; i < tab.length; i++) {
                $(row).attr('data-' + tab[i], data[tab[i]]);
            }
        },
        "footerCallback": function(row, data, start, end, display) {
            var api = this.api(), data;
            var intVal = function(i) {
                return typeof i === 'string' ? i.replace(/[ \€,]/g, '') * 1 : typeof i === 'number' ? i : 0;
            };
            pageTotal = api.column(2, { page: 'current' }).data().reduce(function(a, b) {
                return intVal(a) + intVal(b);
            }, 0);
            $(api.column(2).footer()).html(pageTotal + '&nbsp;€');
            $('#TotalJeuxVendus').html(pageTotal + '&nbsp;€');
            $('#TotalJeuxVendus').data('total', pageTotal);
            $('#NbJeuxVendus').html(api.column(2).nodes().length);
        }
    });

    var input = $('div.dataTables_filter input');
    setTimeout(function() {
        input.focus();
    }, 800);


$('#jeuxenstock tbody').off('dblclick').on('dblclick', 'tr', function () {
    var rowElement = this;
    var data = tablejeuxenstock.row(rowElement).data();

    var id = data && data.dt_rowid ? data.dt_rowid : $(rowElement).data('dt_rowid');
    var code = data && data.Code ? data.Code : $(rowElement).data('code');

    console.log(`Tentative ajout au panier : ID=${id}, Code=${code}`);

    if (!id || !code) {
        alert(" Données du jeu manquantes");
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'ajax/verifier-statut-jeu.php',
        data: {
            id: id,
            codebarre: code
        },
        dataType: 'json',
        success: function (result) {
            console.log("Réponse serveur :", result);

            if (result.success) {
                var rowData = {
                    "Jeu": data.Jeu || 'Inconnu',
                    "Code": data.Code,
                    "Prix": data.Vendu,
                    "Vendeur": data.Vendeur,
                    "dt_rowid": id,
                    "Action": '<a class="delJeu" href="#"><i class="bi bi-cart-x" style="font-size: 1em; color: black;"></i></a>'
                };

                tablejeuxenvente.row.add(rowData).draw();
                tablejeuxenstock.row(rowElement).remove().draw();

                majinfosmonetaires();
            } else {
                alert(result.message || "⚠️ Ce jeu n'est plus en stock.");
                tablejeuxenstock.ajax.reload(null, false);
            }
        },
        error: function () {
            alert(" Erreur lors de la vérification du statut");
        }
    });
});






    $('.dataTables_filter').prepend($('#ajoutersac'));

    $("#showModal").on('click', function(e) {
        e.preventDefault();
        var idAcheteur = $('#idAcheteurModification').val();
        if (idAcheteur != '') {
            openModificationAcheteurModal(idAcheteur, id_transaction);
        } else if (id_transaction != '') {
            openCreationAcheteurModal(id_transaction);
        }
    });

    $(document).on('input', '#MontantPercu', function() {
        var v = this.value.trim();
        this.value = v;
        if (v == '') {
            v = 0;
        }
        if ($.isNumeric(v) === false) {
            this.value = this.value.slice(0, -1);
        } else {
            majinfosmonetaires();
        }
    });

    $(document).on('input', '#MontantDon', function() {
        var v = this.value.trim();
        this.value = v;
        if (v == '') {
            v = 0;
        }
        if ($.isNumeric(v) === false) {
            this.value = this.value.slice(0, -1);
        } else {
            majinfosmonetaires();
        }
    });

    function majinfosmonetaires() {
        var t = parseFloat($('#TotalJeuxVendus').data('total')) || 0;
        var s = parseFloat($("#MontantPercu").val()) || 0;
        if ((paiement === "especes" || paiement === "chèque") && s === 0) {
            alert("Le montant perçu ne peut pas être nul. L’acheteur doit payer !");
            return false;
        }
        
        var d = parseFloat($("#MontantDon").val()) || 0;
        if (paiement === "cb" || paiement === "chèque") {
            s = t + d;
            $('#MontantPercu').val(s);
        }
        if ($.isNumeric(t) && $.isNumeric(s)) {
            $('#MontantRendu').val(s - (t + d));
        }
        if (id_transaction !== '') {
            $.ajax({
                type: 'POST',
                url: 'ajax/transaction-updatelive.php',
                data: {
                    type: 'panier',
                    montantTotal: t,
                    montantPercu: s,
                    montantDon: d,
                    montantRendu: parseFloat($("#MontantRendu").val()) || 0,
                    paiement: paiement,
                    ip: $("#ip").val(),
                    id_phpbb_acheteur: $('#idAcheteurModification').val(),
                    id_transaction: id_transaction
                },
                success: function(data) {
                    if (data.message2 === '0') {
                        alert('Erreur de base de données');
                    }
                },
                dataType: 'json',
                async: true
            });
        }
    }

$('#jeuxenvente tbody').on('click', 'a.delJeu', function () {
    var ligne = $(this).closest('tr');
    var id = ligne.data('dt_rowid');
    var code = ligne.data('code');

    $.ajax({
        type: 'POST',
        url: 'ajax/jeuxliste-update.php',
        data: {
            id: id,
            statut: 2, // en stock
            old_id_statut: 5, // en panier
            codebarre: code
        },
        dataType: 'json',
        success: function (dataAjax) {
            if (dataAjax.message2 === '1') {
                tablejeuxenvente.row(ligne).remove().draw();
                tablejeuxenstock.ajax.reload(null, false);
                majinfosmonetaires();
            } else {
                alert("Erreur : " + dataAjax.message1);
            }
        }
    });
});




    $("#ajoutersac").click(function() {
        var c = tablejeuxenvente.row.add({
            "Jeu": SAC_JEU,
            "Code": SAC_CODEBARRE,
            "Prix": SAC_PRIX,
            "Vendeur": '',
            "dt_rowid": SAC_ID,
            "Action": '<a data-jeu="' + SAC_ID + '" class="delJeu" href="#"><i class="bi bi-cart-x"  style="font-size: 1em; color: black;"></i></a>'
        });
        $.ajax({
            type: 'POST',
            url: 'ajax/transactionliste-add.php',
            data: {
                idliste: SAC_ID,
                id_transaction: id_transaction
            },
            success: function(data) {
                if (data.message2 == '0') {
                    alert('Impossible de mettre à jour le panier');
                }
            },
            dataType: 'json',
            async: true
        });
        c.draw();
        majinfosmonetaires();
    });

    function checkinfosmonetaires() {
        var t = Number($('#TotalJeuxVendus').data('total')) || 0;
        var s = Number($('#MontantPercu').val()) || 0;
        var d = Number($('#MontantDon').val()) || 0;
    
        console.log(" Total jeux vendus :", t);
        console.log(" Montant perçu :", s);
        console.log(" Don :", d);
    
        if (d < 0) {
            alert("Le don doit être positif");
            return false;
        }
    
        if (paiement == 'nondefinit') {
            alert('Veuillez sélectionner le moyen de paiement');
            return false;
        }
    
        if (!$.isNumeric(s) && t > 0) {
            alert("Le montant perçu est invalide");
            return false;
        }
    
        if ((s - d) < t) {
            alert("L'acheteur n'a pas assez payé");
            return false;
        }
    
        if (t == 0) {
            alert('Aucun jeu de vendu');
            return false;
        }
    
        //  Vérification du montant en caisse uniquement si paiement en espèces
        if (paiement === "especes") {
            var montantARendre = parseFloat($("#MontantRendu").val()) || 0;
            console.log("Montant à rendre :", montantARendre);

            var argentEnCaisse = 0;

            $.ajax({
                url: 'ajax/argentencaisse-get.php',
                type: 'GET',
                dataType: 'json',
                async: false,
                success: function(data) {
                    argentEnCaisse = parseFloat(data.message1) || 0;
                    console.log(" Argent en caisse :", argentEnCaisse);
                },
                error: function() {
                    alert('Impossible de vérifier le montant en caisse.');
                    argentEnCaisse = -1;
                }
            });

            if (argentEnCaisse < montantARendre) {
                alert(" Il n'y a pas assez d'argent en caisse pour rendre la monnaie à l'acheteur.\nMerci de prévenir un responsable.");
                console.warn(" Vente bloquée : manque de monnaie");
                return false;
            }
        }

        console.log("Vente autorisée");
        return true;
    
    }
    
    
    
    
    
   $("#finaliserbutton").click(function () {
    console.log("Bouton finaliser cliqué");

    // Supprimer l'avertissement de modification non enregistrée
    $(window).off('beforeunload');

    // Mise à jour des montants
    majinfosmonetaires();

    // Vérifications (paiement, montants, caisse...)
    if (!checkinfosmonetaires()) return;

    if (!AfficherPopUp("La vente va être enregistrée.\nConfirmer la finalisation ?", confirmation)) {
        console.log("Vente annulée par l'utilisateur");
        return;
    }

    const montantDon = parseFloat($("#MontantDon").val()) || 0;
    const id_acheteur = $('#idAcheteurModification').val();
    let date = '';

    // Mise à jour de la transaction
    $.ajax({
        type: 'POST',
        url: 'ajax/transaction-updatelive.php',
        data: {
            type: 'vente',
            montantTotal: $('#TotalJeuxVendus').data('total'),
            montantPercu: $("#MontantPercu").val() || '0',
            montantDon: montantDon,
            montantRendu: $("#MontantRendu").val() || '0',
            paiement: paiement,
            ip: $("#ip").val(),
            id_phpbb_acheteur: id_acheteur,
            id_transaction: id_transaction
        },
        success: function (data) {
            if (data.message2 === '1') {
                date = data.message1;
            } else {
                alert('Erreur lors de l\'enregistrement de la vente.');
            }
        },
        dataType: 'json',
        async: false
    });

    if (!date) {
        console.log("Date vide : arrêt de la procédure");
        return;
    }

    // Passer les jeux du panier en statut vendu
    $('#jeuxenvente tbody tr').each(function () {
        const id = $(this).data('dt_rowid');
        const code = $(this).data('code');

        $.ajax({
            type: 'POST',
            url: 'ajax/jeuxliste-update.php',
            data: {
                id: id,
                statut: 3,  // VENDU
                old_id_statut: 5,  // PANIER
                codebarre: code
            },
            dataType: 'json',
            async: false,
            success: function (result) {
                if (result.message2 !== '1') {
                    alert("Erreur lors du passage en vendu pour le jeu " + code);
                }
            }
        });
        $.ajax({
        type: 'POST',
        url: 'ajax/transactionliste-add.php',
        data: {
            idliste: id,
            id_transaction: id_transaction
        },
        dataType: 'json',
        async: false,
        success: function (result) {
            if (result.message2 !== '1') {
                alert("Erreur lors de l'enregistrement du jeu dans la transaction " + id_transaction);
            }
        }
    });
    });
    
        //  Enregistrement du don si existant
        if (montantDon !== 0) {
            $.ajax({
                type: 'POST',
                url: 'ajax/don-add.php',
                data: {
                    id: id_acheteur || 0,
                    montant_don: montantDon,
                    type_don: 'vente'
                },
                success: function (data) {
                    if (data.message2 !== '1') {
                        alert(' Échec de l\'enregistrement du don.');
                    }
                },
                dataType: 'json',
                async: false
            });
        }
    
        if (!date) {
            console.log(" Date vide : arrêt de la procédure");
            return;
        }
    
        //  Liste des jeux sélectionnés à vendre
        let listeJeux = [];
        $('#jeuxenvente tbody tr').each(function () {
            listeJeux.push({
                id: $(this).data('dt_rowid'),
                code: $(this).data('code')
            });
        });
    
        //  Verrouillage et changement de statut des jeux
        
    
        // Génération et envoi de la facture (si acheteur existant)
        if (id_acheteur) {
            $.ajax({
                url: './pdf/generer_pdf.php',
                type: 'GET',
                data: { idacheteur: id_acheteur },
                success: function () {
                    console.log(' Facture générée');
                    setTimeout(function () {
                        $.ajax({
                            url: 'ajax/send-mail.php',
                            type: 'POST',
                            data: { idacheteur: id_acheteur },
                            success: function () {
                                console.log(' Email envoyé');
                            },
                            error: function (xhr, status, error) {
                                console.error(' Erreur lors de l\'envoi de l\'email :', status, error);
                            }
                        });
                    }, 15000);
                },
                error: function (xhr, status, error) {
                    console.error(' Erreur lors de la génération du PDF :', status, error);
                }
            });
        }
    
        // Tout est ok → redirection vers nouvelle vente
        window.location.href = 'vente.php';
    });
    
    
});
