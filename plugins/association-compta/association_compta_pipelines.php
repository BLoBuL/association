<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_compta_association_capacites($capacites) {
	$capacites['comptabilite'] = ['plugin' => 'association_compta'];
	$capacites['ecritures_comptables'] = ['plugin' => 'association_compta'];

	return $capacites;
}

function association_compta_association_comptabiliser_operation($operation) {
	include_spip('inc/association_compta_ecritures');
	$action = (string) ($operation['action'] ?? 'synchroniser');
	$id_compte = (int) ($operation['id_compte'] ?? 0);
	$donnees = (array) ($operation['donnees'] ?? []);

	if ($action === 'supprimer') {
		$operation['comptabilisee'] = $id_compte > 0
			? association_compta_ecriture_supprimer($id_compte)
			: true;
		$operation['id_compte'] = 0;
		return $operation;
	}

	if ($id_compte > 0) {
		$operation['comptabilisee'] = association_compta_ecriture_modifier($id_compte, $donnees);
	} else {
		$id_compte = (int) association_compta_ecriture_creer($donnees);
		$operation['id_compte'] = $id_compte;
		$operation['comptabilisee'] = $id_compte > 0;
	}

	return $operation;
}
function association_compta_association_paiements_reglement_traiter($flux) {
	if (!empty($flux['data']['traite'])) {
		return $flux;
	}
	$transaction = is_array($flux['args']['transaction'] ?? null) ? $flux['args']['transaction'] : [];
	$id_commande = (int) ($transaction['id_commande'] ?? 0);
	if (!$id_commande) {
		return $flux;
	}
	include_spip('inc/comptes');
	association_commande_comptable_synchroniser($id_commande, [
		'id_transaction' => (int) ($flux['args']['id_transaction'] ?? 0),
		'forcer_paiement' => true,
		'source' => 'association_paiements_reglement_traiter',
	]);
	$flux['data'] = ['traite' => true, 'domaine' => 'commandes'];
	return $flux;
}

/**
 * Signaler à Paiements les transactions référencées par le journal comptable.
 */
function association_compta_association_paiements_transactions_references($flux) {
	$ids = array_values(array_filter(array_map('intval', (array) ($flux['args']['ids_transactions'] ?? []))));
	if (!$ids) {
		return $flux;
	}
	$res = sql_select('DISTINCT id_transaction', 'spip_asso_comptes', sql_in('id_transaction', $ids));
	while ($row = sql_fetch($res)) {
		$flux['data'][] = (int) $row['id_transaction'];
	}
	$flux['data'] = array_values(array_unique(array_filter(array_map('intval', (array) $flux['data']))));
	return $flux;
}

function association_compta_association_config_cli_registre($flux) {
	include_spip('inc/association_compta_config_cli');
	$flux['data'] = association_config_cli_ajouter_definitions($flux['data'], association_compta_config_cli_definitions());
	return $flux;
}

function association_compta_association_configuration_saisies($flux) {
	include_spip('formulaires/inc/configurer_association_compta');
	$flux['data'][] = ['ordre' => 50, 'saisies' => association_compta_configurer_saisies($flux['args']['config'] ?? '')];
	return $flux;
}

function association_compta_association_configuration_verifier($flux) {
	include_spip('formulaires/inc/configurer_association_compta_verifier');
	$flux['data'][] = ['ordre' => 10, 'erreurs' => association_compta_configurer_verifier($flux['args']['config'] ?? '')];
	return $flux;
}

function association_compta_post_edition($flux) {
	if (($flux['args']['table'] ?? '') === 'spip_commandes') {
		$id_commande = intval($flux['args']['id_objet'] ?? $flux['data']['id_commande'] ?? 0);
		if ($id_commande && function_exists('association_commande_comptable_synchroniser')) {
			association_commande_comptable_synchroniser($id_commande, ['source' => 'post_edition']);
		}
	}
	return $flux;
}

function association_compta_post_insertion($flux) {
	if (($flux['args']['table'] ?? '') === 'spip_commandes') {
		$id_commande = intval($flux['args']['id_objet'] ?? $flux['data']['id_commande'] ?? 0);
		if ($id_commande && function_exists('association_commande_comptable_synchroniser')) {
			association_commande_comptable_synchroniser($id_commande, ['source' => 'post_insertion']);
		}
	}
	return $flux;
}

function association_compta_association_rgpd_export_auteur($flux) {
	include_spip('inc/association_compta_rgpd');
	$flux['data']['operations_comptables'] = association_compta_rgpd_export_operations(
		intval($flux['args']['id_auteur'] ?? 0)
	);
	return $flux;
}

function association_compta_association_maintenance_auteurs_encaisses($flux) {
	$ids = array_values(array_filter(array_map('intval', (array) ($flux['args']['ids_auteurs'] ?? []))));
	if (!$ids) {
		return $flux;
	}
	$res = sql_select('DISTINCT id_auteur', 'spip_asso_comptes', sql_in('id_auteur', $ids) . ' AND recette > 0');
	while ($row = sql_fetch($res)) {
		$flux['data'][] = intval($row['id_auteur']);
	}
	$flux['data'] = array_values(array_unique(array_map('intval', (array) $flux['data'])));
	return $flux;
}

function association_compta_association_maintenance_supprimer_donnees_auteurs($flux) {
	include_spip('inc/association_compta_maintenance');
	$ids = array_values(array_filter(array_map('intval', (array) ($flux['args']['ids_auteurs'] ?? []))));
	$flux['data']['supprimer_comptes_auteurs'] = asso_supprimer_comptes_auteurs($ids, (bool) ($flux['args']['dry_run'] ?? true));
	return $flux;
}

function association_compta_association_rgpd_anonymiser_auteur($flux) {
	$id = intval($flux['args']['id_auteur'] ?? 0);
	$flux['data']['comptes_anonymises'] = association_rgpd_updateq('spip_asso_comptes', association_rgpd_filtrer_champs('spip_asso_comptes', [
		'justification' => 'Operation associee a un compte anonymise ' . $id,
	]), 'id_auteur=' . $id);
	return $flux;
}

function association_compta_association_configuration_navigation($flux) {
	$webmestre = !empty($flux['args']['webmestre']);
	$comptes = $GLOBALS['association_metas']['comptes'] ?? false;
	$comptes = in_array($comptes, [true, 1, '1', 'on', 'oui'], true);
	if ($webmestre || $comptes) {
		$flux['data']['comptabilite'] = ['ordre' => 100, 'label' => 'association_config:navigation_config_comptabilite'];
	}
	return $flux;
}
