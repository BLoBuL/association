<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_evenements_comptes_supprimer_inscription($id_activite) {
	$id_activite = (int) $id_activite;
	$activite = $id_activite
		? sql_fetsel('id_evenement,id_transaction', 'spip_asso_activites', 'id_activite=' . $id_activite)
		: array();
	if (!$activite) {
		return 0;
	}

	$id_evenement = (int) $activite['id_evenement'];
	$id_transaction = (int) $activite['id_transaction'];
	$where = "((objet='evenement' AND id_objet={$id_evenement} AND id_transaction={$id_transaction})"
		. " OR (objet='activite' AND id_objet={$id_activite}))";
	$comptes = sql_allfetsel('id_compte', 'spip_asso_comptes', $where);
	$ids_comptes = array_map('intval', array_column($comptes, 'id_compte'));
	if ($ids_comptes) {
		sql_delete('spip_asso_destination_op', sql_in('id_compte', $ids_comptes));
	}

	return (int) sql_delete('spip_asso_comptes', $where);
}
