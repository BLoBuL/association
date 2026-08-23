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
		. ' AND justification LIKE ' . sql_quote(_T('association:pret_nd') . '%');
	return "(objet='pret' AND id_objet=$id_pret) OR ($legacy)";
}
