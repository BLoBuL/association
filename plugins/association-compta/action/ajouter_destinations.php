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
function action_ajouter_destinations() {
	return action_ajouter_destinations_dist();
}

function action_ajouter_destinations_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$securiser_action();
	include_spip('inc/autoriser');
	if (!autoriser('modifier', 'destination')) {
		return false;
	}

	$intitule = _request('intitule');
	$commentaire = _request('commentaire');
	destination_insert($intitule, $commentaire);
}

function destination_insert($intitule, $commentaire) {
	sql_insertq('spip_asso_destination', [
		'intitule' => $intitule,
		'commentaire' => $commentaire,
	]);
}
