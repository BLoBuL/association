(function () {
	'use strict';

	document.addEventListener('change', function (event) {
		var cible = event.target;
		if (cible.matches('.association-compta-submit-on-change')) {
			cible.form.submit();
			return;
		}
		if (!cible.matches('#form_comptes #check_all')) {
			return;
		}
		cible.form.querySelectorAll('input[name="selecteur_comptes[]"]').forEach(function (caseCompte) {
			if (caseCompte !== cible) {
				caseCompte.checked = cible.checked;
			}
		});
	});

	document.addEventListener('submit', function (event) {
		var formulaire = event.target;
		if (!formulaire.matches('#form_comptes')) {
			return;
		}
		var action = formulaire.querySelector('[name="action_groupe"]');
		if (action && action.value === 'supprimer'
			&& !window.confirm(formulaire.dataset.confirmSuppression || '')) {
			event.preventDefault();
		}
	});

	document.addEventListener('click', function (event) {
		var lien = event.target.closest('.association-compta-confirmer');
		if (lien && !window.confirm(lien.dataset.confirm || '')) {
			event.preventDefault();
		}
	});
}());
