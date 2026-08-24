(function () {
	'use strict';

	function ajouterDestination(conteneur) {
		var compteur = conteneur.querySelector('#idNextDestination');
		var modele = conteneur.querySelector('select[name^="id_dest["]');
		if (!compteur || !modele) {
			return;
		}

		var id = parseInt(compteur.value, 10) || 1;
		var ligne = document.createElement('div');
		var liste = document.createElement('ul');
		var celluleDestination = document.createElement('li');
		var celluleMontant = document.createElement('li');
		var select = modele.cloneNode(true);
		var montant = document.createElement('input');
		var retirer = document.createElement('button');

		ligne.className = 'formo';
		ligne.id = 'row' + id;
		celluleDestination.className = 'editer_id_dest[' + id + ']';
		select.id = 'id_dest[' + id + ']';
		select.name = 'id_dest[' + id + ']';
		celluleDestination.appendChild(select);
		celluleMontant.className = 'editer_montant_dest[' + id + ']';
		montant.type = 'text';
		montant.id = 'montant_dest[' + id + ']';
		montant.name = 'montant_dest[' + id + ']';
		montant.value = '0';
		celluleMontant.appendChild(montant);
		retirer.type = 'button';
		retirer.className = 'destButton association-compta-destination-retirer';
		retirer.dataset.destinationRow = ligne.id;
		retirer.textContent = '−';
		liste.append(celluleDestination, celluleMontant, retirer);
		ligne.appendChild(liste);
		conteneur.insertBefore(ligne, compteur);
		compteur.value = String(id + 1);
	}

	document.addEventListener('click', function (event) {
		var ajouter = event.target.closest('.association-compta-destination-ajouter');
		if (ajouter) {
			ajouterDestination(ajouter.closest('.formulaire_edition_destinations'));
			return;
		}
		var retirer = event.target.closest('.association-compta-destination-retirer');
		if (!retirer) {
			return;
		}
		var conteneur = retirer.closest('.formulaire_edition_destinations');
		var ligne = conteneur && conteneur.querySelector('#' + retirer.dataset.destinationRow);
		if (ligne) {
			ligne.remove();
		}
	});
}());
