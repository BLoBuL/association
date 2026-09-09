<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Maintenance des écritures comptables, propriété de Comptabilité.
 */

function asso_supprimer_comptes_auteurs(array $ids_auteurs, $dry_run = true) {
	if (!$ids_auteurs) {
		return ['supprimes' => 0];
	}
	$in = sql_in('id_auteur', $ids_auteurs);
	$ids_comptes = array_values(array_filter(array_map('intval', array_column(
		sql_allfetsel('id_compte', 'spip_asso_comptes', $in) ?: [],
		'id_compte'
	))));
	if ($dry_run) {
		return ['supprimes' => count($ids_comptes)];
	}
	include_spip('inc/association_compta_ecritures');
	$nb = 0;
	foreach ($ids_comptes as $id_compte) {
		$nb += association_compta_ecriture_supprimer($id_compte) ? 1 : 0;
	}
	return ['supprimes' => intval($nb)];
}
