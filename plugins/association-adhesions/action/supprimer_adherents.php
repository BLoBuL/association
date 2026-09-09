<?php

/*\
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & François de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\*/

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * @deprecated Utiliser charger_fonction() pour cette action.
 */
function action_supprimer_adherents() {
	return action_supprimer_adherents_dist();
}

function action_supprimer_adherents_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$securiser_action();
	include_spip('inc/autoriser');
	if (!autoriser('menu', 'adherents')) {
		return false;
	}
	$ids = array_values(array_unique(array_filter(array_map('intval', (array) _request('drop')), static function ($id) {
		return $id > 0;
	})));
	// Utiliser la corbeille SPIP pour conserver les liens et les historiques.
	include_spip('action/editer_auteur');
	foreach ($ids as $id_auteur) {
		if (!autoriser('modifier', 'auteur', $id_auteur, null, ['statut' => '5poubelle'])) {
			return false;
		}
	}
	foreach ($ids as $id_auteur) {
		if ($erreur = auteur_modifier($id_auteur, ['statut' => '5poubelle'])) {
			return false;
		}
	}
	return true;
}
