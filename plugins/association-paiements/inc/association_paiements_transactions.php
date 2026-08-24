<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Lire les informations non sensibles nécessaires aux modules consommateurs.
 */
function association_paiements_transactions_lire(array $ids_transactions) {
	$ids_transactions = array_values(array_filter(array_unique(array_map('intval', $ids_transactions))));
	if (!$ids_transactions) {
		return array();
	}
	$rows = sql_allfetsel(
		'id_transaction,statut,mode,montant,devise,date_transaction,date_paiement',
		'spip_transactions',
		sql_in('id_transaction', $ids_transactions)
	);
	$index = array();
	foreach ($rows ?: array() as $row) {
		$index[(int) $row['id_transaction']] = $row;
	}
	return $index;
}

function association_paiements_transaction_lire($id_transaction) {
	$id_transaction = (int) $id_transaction;
	if ($id_transaction <= 0) {
		return array();
	}
	$transactions = association_paiements_transactions_lire(array($id_transaction));
	return $transactions[$id_transaction] ?? array();
}
