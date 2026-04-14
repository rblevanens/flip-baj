/** Ce document met à jour les différents champs de stats.php et créé la liste de sélection des dates.
 * Il fait appel via ajax au document @see stats-get.php qui contient l'ensemble des appels SQL nécessaires à la page
 */

/**Cette constante permet la mise en page des montants avec le signe €
*/
const formatter = new Intl.NumberFormat('ru-RU', {
	style: 'currency',
	currency: 'EUR',
	minimumFractionDigits: 2
})

$(document).ready(function() {
	/*setInterval(function() {
		updateStats($('#annee').val()) // Permet de refresh les stats toutes les 5 secondes
	}, 5000);*/

	/**Cette fonction prends en argument la première et la dernière date que l'on veut proposer dans le slect
	 * @param {Integer} start - C'est la date à partir de laquelle les options existent
	 * @param {Integer} end - C'est la date jusqu'à laquelle les options existent
	 * @return {HtmlElement} C'est le code Html pour afficher les différentes options, stocké dans res
	 */
	function yearselect(start, end) {
		var dateajout = end
		var res = ""
		while (dateajout >= start) {
			res += "<option>" + dateajout + "</option>";
			dateajout -= 1;
		}
		return res
	}

	$('#annee').html(yearselect(2023, (new Date).getFullYear()));



	/**
   * Number.prototype.format(n, x)
   * @param {integer} n: length of decimal
   * @param {integer} x: length of sections
   */
	Number.prototype.format = function(n, x) {
		var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
		return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$& ');
	};

	/**Cette fonction va chercher les stats en fonction de l'année et les renvoies dans stats.php.
	 * 
	 * @param {Integer} annee - C'est l'année (4 chiffres) pour laquelle on veut les stats.
	 * @see stats-get.php
	 */
	function updateStats(annee) {
		$.ajax({
			type: 'POST',
			url: 'ajax/stats-get.php',
			data: {
				'annee': annee
			},
			success: function(data) {
				$('#nbJeuxEnregistres').html('0');
				$('#totalDonsNonRemb').html('0');

				$('#nbJeuxStock').html('0');
				$('#nbJeuxVendus').html('0');
				$('#nbJeuxDonnés').html('0');
				$('#nbSacsVendus').html('0');
				$('#totalVentes').html('0');
				$('#VentesCB').html('0');
				$('#VentesEspeces').html('0');
				$('#VentesCheque').html('0');
				$('#totalStockPrixVendu').html('0');
				$('#totalRemb').html('0');
				$('#RembEspeces').html('0');
				$('#RembCheques').html('0');
				$('#RembPaypal').html('0');
				$('#RembRestant').html('0');
				$('#totalDons').html('0');
				$('#totalCommissionsHT').html('0');
				$('#totalCommissionsTTC').html('0');

				if (!isNaN(data.nbJeuxEnregistres)) {
					$('#nbJeuxEnregistres').html(data.nbJeuxEnregistres.format());
				}
				if (!isNaN(data.nbJeuxStock)) {
					$('#nbJeuxStock').html(data.nbJeuxStock.format());
				}
				if (!isNaN(data.nbJeuxVendus)) {
					$('#nbJeuxVendus').html(data.nbJeuxVendus.format());
				}
				if (!isNaN(data.nbJeuxDonnés)) {
					$('#nbJeuxDonnés').html(data.nbJeuxDonnés.format());
				}
				if (!isNaN(data.VentesSac)) {
					$('#nbSacsVendus').html(data.VentesSac.format());
				}
				var totalVentes = data.VentesCB + data.VentesEspeces + data.VentesCheque ;
				if (!isNaN(totalVentes)) {
					$('#totalVentes').html(formatter.format(totalVentes));
				}
				if (!isNaN(data.VentesCB)) {
					$('#VentesCB').html(formatter.format(data.VentesCB));
				}
				if (!isNaN(data.VentesEspeces)) {
					$('#VentesEspeces').html(formatter.format(data.VentesEspeces));
				}
				if (!isNaN(data.VentesCheque)) {
					$('#VentesCheque').html(formatter.format(data.VentesCheque));
				}
				if (!isNaN(data.totalStockPrixVendu)) {
					$('#totalStockPrixVendu').html(formatter.format(data.totalStockPrixVendu));
				}
				if (!isNaN(data.totalRemb)) {
					$('#totalRemb').html(formatter.format(data.totalRemb));
				}
				if (!isNaN(data.RembEspeces)) {
					$('#RembEspeces').html(formatter.format(data.RembEspeces));
				}
				if (!isNaN(data.RembCheques)) {
					$('#RembCheques').html(formatter.format(data.RembCheques));
				}
				if (!isNaN(data.RembPaypal)) {
					$('#RembPaypal').html(formatter.format(data.RembPaypal));
				}

				if (!isNaN(data.totalDonsNonRemb)) {
					$('#totalDonsNonRemb').html(formatter.format(data.totalDonsNonRemb));
				}

				var RembRestant = data.totalARendre - data.totalRemb - data.totalDonsNonRemb ;
				if (!isNaN(RembRestant)) {
					$('#RembRestant').html(formatter.format(RembRestant));
				}
				if (!isNaN(data.totalDons)) {
					$('#totalDons').html(formatter.format(data.totalDons));
				}
				if (!isNaN(data.commissionHT)) {
					$('#totalCommissionsHT').html(formatter.format(data.commissionHT));
				}
				if (!isNaN(data.commissionTTC)) {
					$('#totalCommissionsTTC').html(formatter.format(data.commissionTTC));
				}
			},
			dataType: 'json',
			async: false
		});

	}
	updateStats($('#annee').val());
	$('select[name="annee"]').change(function() { // lorsqu'on change l'année
		updateStats($('#annee').val());
	});
});
