<?php

/**
 * Retourne les identifiants des bannieres publiables a une date donnee.
 *
 * Une borne a 0000-00-00 est consideree comme ouverte. La selection est
 * centralisee ici afin que tous les rendus publics appliquent la meme regle.
 *
 * @param string $emplacement
 * @param string|null $date
 * @return array<int>
 */
function association_bannieres_ids_actives($emplacement = '', $date = null) {
	$date = $date ?: date('Y-m-d');
	$where = array(
		"statut=" . sql_quote('publie'),
		"(date_debut=" . sql_quote('0000-00-00') . " OR date_debut<=" . sql_quote($date) . ')',
		"(date_fin=" . sql_quote('0000-00-00') . " OR date_fin>=" . sql_quote($date) . ')',
	);
	if ($emplacement !== '') {
		$where[] = 'emplacement=' . sql_quote($emplacement);
	}

	return array_map('intval', array_column(sql_allfetsel('id_banniere', 'spip_asso_bannieres', $where), 'id_banniere'));
}
