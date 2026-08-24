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

function association_evenements_compte_valider_transaction($id_transaction) {
	$id_transaction = (int) $id_transaction;
	$compte = sql_fetsel(
		'id_compte',
		'spip_asso_comptes',
		'id_transaction=' . $id_transaction . " AND objet='evenement'"
	);
	$activite = sql_fetsel('date', 'spip_asso_activites', 'id_transaction=' . $id_transaction);
	$id_compte = (int) ($compte['id_compte'] ?? 0);
	if ($id_compte <= 0) {
		association_log(
			'comptabilite',
			'association_evenements_compte_valider_transaction: aucun compte pour id_transaction=' . $id_transaction,
			'info'
		);
		return 0;
	}

	sql_updateq('spip_asso_comptes', array(
		'date' => !empty($activite['date']) ? $activite['date'] : date('Y-m-d H:i:s'),
		'imputation' => $GLOBALS['association_metas']['pc_activites_paiement'] ?? '',
		'vu' => 1,
	), 'id_compte=' . $id_compte);

	return $id_compte;
}

function association_evenements_compte_remboursement_creer($id_transaction, $id_activite = 0, $contexte_evenement = array()) {
	$id_transaction = (int) $id_transaction;
	$id_activite = (int) $id_activite;
	if ($id_transaction <= 0) {
		return 0;
	}
	if ($id_activite <= 0) {
		$id_activite = (int) sql_getfetsel('id_activite', 'spip_asso_activites', 'id_transaction=' . $id_transaction);
	}
	$activite = $id_activite
		? sql_fetsel('*', 'spip_asso_activites', 'id_activite=' . $id_activite)
		: array();
	$transaction = sql_fetsel('*', 'spip_transactions', 'id_transaction=' . $id_transaction);
	if (!$activite || !$transaction) {
		association_log('comptabilite', 'Remboursement evenement ignore: inscription ou transaction introuvable', 'erreur');
		return 0;
	}

	$id_evenement = (int) $activite['id_evenement'];
	$existant = (int) sql_getfetsel(
		'id_compte',
		'spip_asso_comptes',
		'id_transaction=' . $id_transaction . " AND objet='evenement' AND id_objet=" . $id_evenement . ' AND depense>0'
	);
	if ($existant > 0) {
		return $existant;
	}
	if (!$contexte_evenement) {
		$contexte_evenement = gestions_places($id_evenement);
	}
	include_spip('inc/comptes');
	$titre = $contexte_evenement['evenement_titre'] ?? ('#' . $id_evenement);
	return inserer_compte(
		date('Y-m-d H:i:s'),
		0,
		(float) $transaction['montant'],
		'Remboursement de ' . $activite['nom_inscrit'] . ' ' . $activite['prenom_inscrit'] . ' pour l\'activité "' . $titre . '"',
		$GLOBALS['association_metas']['pc_activites_paiement'] ?? '101',
		'activite_remboursement|' . $id_activite,
		(int) $activite['id_auteur'],
		$id_evenement,
		'evenement',
		'',
		'',
		'',
		$id_transaction,
		1
	);
}
