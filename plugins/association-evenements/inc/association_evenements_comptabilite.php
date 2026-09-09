<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_evenements_comptabilite_disponible() {
	return association_evenements_integration_active('association_compta');
}

function association_evenements_paiements_disponibles() {
	return association_evenements_integration_active('association_paiements');
}

function association_evenements_comptes_supprimer_inscription($id_activite) {
	if (!association_evenements_comptabilite_disponible()) {
		return 0;
	}
	$id_activite = (int) $id_activite;
	$activite = $id_activite
		? sql_fetsel('id_evenement,id_transaction', 'spip_asso_activites', 'id_activite=' . $id_activite)
		: [];
	if (!$activite) {
		return 0;
	}

	$id_evenement = (int) $activite['id_evenement'];
	$id_transaction = (int) $activite['id_transaction'];
	include_spip('inc/association_compta_ecritures');
	$comptes = array_merge(
		association_compta_ecritures_objet_lister('evenement', $id_evenement, [
			'id_transaction' => $id_transaction,
			'champs' => 'id_compte',
		]),
		association_compta_ecritures_objet_lister('activite', $id_activite, ['champs' => 'id_compte'])
	);
	$ids_comptes = array_values(array_unique(array_map('intval', array_column($comptes, 'id_compte'))));
	$nombre = 0;
	foreach ($ids_comptes as $id_compte) {
		$nombre += association_compta_ecriture_supprimer($id_compte) ? 1 : 0;
	}
	return $nombre;
}

function association_evenements_compte_valider_transaction($id_transaction) {
	if (!association_evenements_comptabilite_disponible()) {
		return 0;
	}
	$id_transaction = (int) $id_transaction;
	$activite = sql_fetsel('id_evenement,date', 'spip_asso_activites', 'id_transaction=' . $id_transaction);
	include_spip('inc/association_compta_ecritures');
	$comptes = $activite ? association_compta_ecritures_objet_lister('evenement', (int) $activite['id_evenement'], [
		'id_transaction' => $id_transaction,
		'champs' => 'id_compte',
	]) : [];
	$compte = $comptes[0] ?? [];
	$id_compte = (int) ($compte['id_compte'] ?? 0);
	if ($id_compte <= 0) {
		association_log(
			'comptabilite',
			'association_evenements_compte_valider_transaction: aucun compte pour id_transaction=' . $id_transaction,
			'info'
		);
		return 0;
	}

	association_compta_ecriture_modifier($id_compte, [
		'date' => !empty($activite['date']) ? $activite['date'] : date('Y-m-d H:i:s'),
		'imputation' => $GLOBALS['association_metas']['pc_activites_paiement'] ?? '',
		'vu' => 1,
	]);

	return $id_compte;
}

function association_evenements_compte_remboursement_creer($id_transaction, $id_activite = 0, $contexte_evenement = []) {
	if (!association_evenements_comptabilite_disponible() || !association_evenements_paiements_disponibles()) {
		return 0;
	}
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
		: [];
	include_spip('inc/association_evenements_paiements');
	$transaction = association_evenements_transaction_lire($id_transaction);
	if (!$activite || !$transaction) {
		association_log('comptabilite', 'Remboursement evenement ignore: inscription ou transaction introuvable', 'erreur');
		return 0;
	}

	$id_evenement = (int) $activite['id_evenement'];
	include_spip('inc/association_compta_ecritures');
	$ecritures = association_compta_ecritures_objet_lister('evenement', $id_evenement, [
		'id_transaction' => $id_transaction,
	]);
	$existant = 0;
	foreach ($ecritures as $ecriture) {
		if ((float) ($ecriture['depense'] ?? 0) > 0) {
			$existant = (int) ($ecriture['id_compte'] ?? 0);
			break;
		}
	}
	if ($existant > 0) {
		return $existant;
	}
	if (!$contexte_evenement) {
		$contexte_evenement = gestions_places($id_evenement);
	}
	$titre = $contexte_evenement['evenement_titre'] ?? ('#' . $id_evenement);
	return association_compta_ecriture_creer([
		'date' => date('Y-m-d H:i:s'), 'recette' => 0, 'depense' => (float) $transaction['montant'],
		'justification' => 'Remboursement de ' . $activite['nom_inscrit'] . ' ' . $activite['prenom_inscrit'] . ' pour l\'activité "' . $titre . '"',
		'imputation' => $GLOBALS['association_metas']['pc_activites_paiement'] ?? '101',
		'journal' => 'activite_remboursement|' . $id_activite, 'id_auteur' => (int) $activite['id_auteur'],
		'id_objet' => $id_evenement, 'objet' => 'evenement', 'id_transaction' => $id_transaction, 'vu' => 1,
	]);
}

function association_evenements_compte_inscription_creer($id_activite, $contexte_evenement = []) {
	if (!association_evenements_comptabilite_disponible() || !association_evenements_paiements_disponibles()) {
		return 0;
	}
	$id_activite = (int) $id_activite;
	$activite = $id_activite ? sql_fetsel('*', 'spip_asso_activites', 'id_activite=' . $id_activite) : [];
	if (!$activite) {
		return 0;
	}
	$id_evenement = (int) $activite['id_evenement'];
	$evenement = sql_fetsel('payant', 'spip_evenements', 'id_evenement=' . $id_evenement);
	if (array_key_exists('payant', (array) $evenement) && (int) $evenement['payant'] === 0) {
		return 0;
	}
	include_spip('inc/association_evenements_paiements');
	$transaction = association_evenements_transaction_lire((int) $activite['id_transaction']);
	if (!$transaction) {
		association_log('comptabilite', 'Compte evenement ignore: transaction introuvable id_activite=' . $id_activite, 'erreur');
		return 0;
	}
	if (!$contexte_evenement) {
		$contexte_evenement = gestions_places($id_evenement);
	}
	include_spip('inc/association_compta_ecritures');
	$titre = $contexte_evenement['evenement_titre'] ?? ('#' . $id_evenement);
	$imputation = $transaction['statut'] === 'ok'
		? ($GLOBALS['association_metas']['pc_activites_paiement'] ?? '101')
		: ($GLOBALS['association_metas']['pc_activites_creance'] ?? '101');
	return association_compta_ecriture_creer([
		'date' => $activite['date'] ?: date('Y-m-d H:i:s'), 'recette' => (float) $transaction['montant'], 'depense' => 0,
		'justification' => 'Participation de ' . $activite['nom_inscrit'] . ' ' . $activite['prenom_inscrit'] . ' à l\'activité "' . $titre . '"',
		'imputation' => $imputation, 'journal' => 'activite|' . $id_activite,
		'id_auteur' => (int) $activite['id_auteur'], 'id_objet' => $id_evenement, 'objet' => 'evenement',
		'id_transaction' => (int) $activite['id_transaction'],
	]);
}

function association_evenements_compte_inscription_actualiser($id_activite, $id_transaction) {
	if (!association_evenements_comptabilite_disponible() || !association_evenements_paiements_disponibles()) {
		return 0;
	}
	$id_activite = (int) $id_activite;
	$id_transaction = (int) $id_transaction;
	$activite = $id_activite ? sql_fetsel('*', 'spip_asso_activites', 'id_activite=' . $id_activite) : [];
	include_spip('inc/association_evenements_paiements');
	$transaction = $id_transaction ? association_evenements_transaction_lire($id_transaction) : [];
	if (!$activite || !$transaction) {
		return 0;
	}
	$id_evenement = (int) $activite['id_evenement'];
	include_spip('inc/association_compta_ecritures');
	$comptes = association_compta_ecritures_objet_lister('evenement', $id_evenement, [
		'id_transaction' => $id_transaction,
		'champs' => 'id_compte',
	]);
	if (!$comptes) {
		$comptes = association_compta_ecritures_objet_lister('activite', $id_activite, ['champs' => 'id_compte']);
	}
	$compte = $comptes[0] ?? [];
	$id_compte = (int) ($compte['id_compte'] ?? 0);
	if ($id_compte <= 0) {
		return association_evenements_compte_inscription_creer($id_activite);
	}
	association_compta_ecriture_modifier($id_compte, [
		'date' => $activite['date'] ?: date('Y-m-d H:i:s'), 'recette' => (float) $transaction['montant'], 'depense' => 0,
		'imputation' => $GLOBALS['association_metas']['pc_activites_creance'] ?? '101',
		'journal' => $activite['journal'] ?? '', 'id_objet' => $id_evenement, 'objet' => 'evenement',
		'id_transaction' => $id_transaction, 'vu' => 0,
	]);
	return $id_compte;
}
