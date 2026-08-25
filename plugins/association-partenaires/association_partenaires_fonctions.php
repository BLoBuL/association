<?php

/**
 * Retourne les partenariats publies et valides a une date donnee.
 *
 * @param string|null $date
 * @return array<int>
 */
function association_partenaires_ids_actifs($date = null) {
	$date = $date ?: date('Y-m-d');
	$where = array(
		"statut=" . sql_quote('publie'),
		"(date_debut=" . sql_quote('0000-00-00') . " OR date_debut<=" . sql_quote($date) . ')',
		"(date_fin=" . sql_quote('0000-00-00') . " OR date_fin>=" . sql_quote($date) . ')',
	);

	return array_map('intval', array_column(sql_allfetsel('id_partenaire', 'spip_asso_partenaires', $where), 'id_partenaire'));
}
