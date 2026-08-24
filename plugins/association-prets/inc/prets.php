<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Critère compatible pour retrouver l'écriture d'un prêt.
 *
 * Les nouvelles écritures utilisent le lien objet natif. Le second terme
 * conserve l'accès aux écritures historiques identifiées par id_journal.
 */
function association_pret_compte_where($id_pret) {
	$id_pret = (int) $id_pret;
	$legacy = 'id_journal=' . $id_pret
		. ' AND justification LIKE ' . sql_quote(_T('association_prets:pret_nd') . '%');
	return "(objet='pret' AND id_objet=$id_pret) OR ($legacy)";
}

/**
 * Aligne le statut d'une ressource sur l'existence d'un prêt non restitué.
 */
function association_prets_synchroniser_statut_ressource($id_ressource) {
	$id_ressource = intval($id_ressource);
	if ($id_ressource <= 0) {
		return false;
	}
	$actifs = sql_countsel(
		'spip_asso_prets',
		array(
			'id_ressource=' . $id_ressource,
			"(date_retour IS NULL OR date_retour='' OR date_retour='0000-00-00')",
		)
	);
	return sql_updateq(
		'spip_asso_ressources',
		array('statut' => intval($actifs) > 0 ? 'reserve' : 'ok'),
		'id_ressource=' . $id_ressource
	) !== false;
}
