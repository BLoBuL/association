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
function action_editer_asso_plan() {
	return action_editer_asso_plan_dist();
}

function action_editer_asso_plan_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_plan = $securiser_action();
	include_spip('inc/autoriser');
	if (!autoriser('modifier', 'asso_plan', (int) $id_plan)) {
		return [(int) $id_plan, _T('info_acces_interdit')];
	}
	$id_plan = (int) $id_plan;

	$code = _request('code');

	$active = _request('active');
	if ($active) { /* active est un booleen dans la base, et la request recupere l'etat de la checkbox */
		$active = true;
	} else {
		$active = false;
	}

	$intitule = _request('intitule');
	$classe = _request('classe');
	$reference = _request('reference');
	$solde_anterieur = _request('solde_anterieur');
	$commentaire = _request('commentaire');
	$date_anterieure = _request('date_anterieure');
	$type_op = _request('type_op');

	// include_spip('base/association');

	if (!$id_plan) {/* c'est une insertion */
		$id_plan = sql_insertq('spip_asso_plan', [
			'date_anterieure' => $date_anterieure,
			'active' => $active,
			'code' => $code,
			'intitule' => $intitule,
			'classe' => $classe,
			'solde_anterieur' => $solde_anterieur,
			'commentaire' => $commentaire,
			'type_op' => $type_op]);
	} else { /* c'est un ajout */
		sql_updateq(
			'spip_asso_plan',
			[
				'date_anterieure' => $date_anterieure,
				'active' => $active,
				'code' => $code,
				'intitule' => $intitule,
				'classe' => $classe,
				'solde_anterieur' => $solde_anterieur,
				'commentaire' => $commentaire,
				'type_op' => $type_op],
			"id_plan=$id_plan"
		);
	}

	return [$id_plan, ''];
}
