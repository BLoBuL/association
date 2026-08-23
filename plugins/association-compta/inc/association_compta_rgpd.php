<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Formate une écriture du journal pour un export RGPD.
 */
function association_compta_rgpd_export_compte($row) {
	$id_transaction = intval($row['id_transaction'] ?? 0);
	return array(
		'id_compte' => intval($row['id_compte'] ?? 0),
		'id_auteur' => intval($row['id_auteur'] ?? 0),
		'date' => association_rgpd_export_date($row['date'] ?? ''),
		'reinscription' => (string)($row['reinscription'] ?? ''),
		'statut_cotisation' => (string)($row['statut_cotisation'] ?? ''),
		'id_categorie' => intval($row['id_categorie'] ?? 0),
		'objet' => (string)($row['objet'] ?? ''),
		'id_objet' => intval($row['id_objet'] ?? 0),
		'recette' => (float)($row['recette'] ?? 0),
		'depense' => (float)($row['depense'] ?? 0),
		'justification' => (string)($row['justification'] ?? ''),
		'imputation' => (string)($row['imputation'] ?? ''),
		'journal' => (string)($row['journal'] ?? ''),
		'id_journal' => intval($row['id_journal'] ?? 0),
		'vu' => intval($row['vu'] ?? 0),
		'maj' => association_rgpd_export_date($row['maj'] ?? ''),
		'id_transaction' => $id_transaction,
		'transaction' => function_exists('association_paiements_rgpd_export_transaction')
			? association_paiements_rgpd_export_transaction($id_transaction)
			: array(),
	);
}

/**
 * Exporte les écritures qui ne représentent pas une cotisation.
 */
function association_compta_rgpd_export_operations($id_auteur) {
	$where = array('id_auteur=' . intval($id_auteur));
	$conditions_cotisation = array();
	if (association_rgpd_table_has_column('spip_asso_comptes', 'objet')) {
		$conditions_cotisation[] = 'objet=' . sql_quote('cotisation');
	}
	if (association_rgpd_table_has_column('spip_asso_comptes', 'id_categorie')) {
		$conditions_cotisation[] = 'id_categorie>0';
	}
	if (association_rgpd_table_has_column('spip_asso_comptes', 'reinscription')) {
		$conditions_cotisation[] = 'reinscription<>' . sql_quote('');
	}
	if ($conditions_cotisation) {
		$where[] = 'NOT (' . implode(' OR ', $conditions_cotisation) . ')';
	}

	$export = array();
	foreach (sql_allfetsel('*', 'spip_asso_comptes', implode(' AND ', $where), '', 'date DESC, id_compte DESC') as $row) {
		$export[] = association_compta_rgpd_export_compte($row);
	}
	return $export;
}
