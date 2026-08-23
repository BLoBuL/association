<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

function association_compta_association_config_cli_registre($flux) {
	include_spip('inc/association_compta_config_cli');
	$flux['data'] = association_config_cli_ajouter_definitions($flux['data'], association_compta_config_cli_definitions());
	return $flux;
}

function association_compta_association_configuration_saisies($flux) {
	include_spip('formulaires/inc/configurer_association_compta');
	$flux['data'][] = array('ordre' => 50, 'saisies' => association_compta_configurer_saisies($flux['args']['config'] ?? ''));
	return $flux;
}

function association_compta_association_configuration_verifier($flux) {
	include_spip('formulaires/inc/configurer_association_compta_verifier');
	$flux['data'][] = array('ordre' => 10, 'erreurs' => association_compta_configurer_verifier($flux['args']['config'] ?? ''));
	return $flux;
}

function association_compta_post_edition($flux) {
    if (($flux['args']['table'] ?? '') === 'spip_commandes') {
        $id_commande = intval($flux['args']['id_objet'] ?? $flux['data']['id_commande'] ?? 0);
        if ($id_commande && function_exists('association_commande_comptable_synchroniser')) {
            association_commande_comptable_synchroniser($id_commande, array('source' => 'post_edition'));
        }
    }
    return $flux;
}

function association_compta_post_insertion($flux) {
    if (($flux['args']['table'] ?? '') === 'spip_commandes') {
        $id_commande = intval($flux['args']['id_objet'] ?? $flux['data']['id_commande'] ?? 0);
        if ($id_commande && function_exists('association_commande_comptable_synchroniser')) {
            association_commande_comptable_synchroniser($id_commande, array('source' => 'post_insertion'));
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
	$ids = array_values(array_filter(array_map('intval', (array) ($flux['args']['ids_auteurs'] ?? array()))));
	if (!$ids) { return $flux; }
	$res = sql_select('DISTINCT id_auteur', 'spip_asso_comptes', sql_in('id_auteur', $ids) . ' AND recette > 0');
	while ($row = sql_fetch($res)) { $flux['data'][] = intval($row['id_auteur']); }
	$flux['data'] = array_values(array_unique(array_map('intval', (array) $flux['data'])));
	return $flux;
}

function association_compta_association_maintenance_supprimer_donnees_auteurs($flux) {
	include_spip('inc/association_compta_maintenance');
	$ids = array_values(array_filter(array_map('intval', (array) ($flux['args']['ids_auteurs'] ?? array()))));
	$flux['data']['supprimer_comptes_auteurs'] = asso_supprimer_comptes_auteurs($ids, (bool) ($flux['args']['dry_run'] ?? true));
	return $flux;
}

function association_compta_association_rgpd_anonymiser_auteur($flux) {
	$id = intval($flux['args']['id_auteur'] ?? 0);
	$flux['data']['comptes_anonymises'] = association_rgpd_updateq('spip_asso_comptes', association_rgpd_filtrer_champs('spip_asso_comptes', array(
		'justification' => 'Operation associee a un compte anonymise ' . $id,
	)), 'id_auteur=' . $id);
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

function association_compta_association_maintenance_bdd_configurer($flux) {
	$source = $flux['args']['source'] ?? array();
	$flux['data']['mois_non_encaisse'] = intval(association_maintenance_lire_source(
		$source,
		'meta_cfg_maintenance_mois_non_encaisse',
		6
	));
	foreach (array('supprimer_cotisations_orphelines', 'supprimer_cotisations_non_encaissees') as $action) {
		$flux['data']['actions'][$action] = association_maintenance_valeur_booleenne(
			association_maintenance_lire_source($source, 'meta_cfg_maintenance_' . $action, true)
		);
	}
	return $flux;
}

function association_compta_association_maintenance_bdd_verifier_configuration($flux) {
	$champ = 'meta_cfg_maintenance_mois_non_encaisse';
	if ($erreur = association_config_maintenance_verifier_entier($champ)) {
		$flux['data'][$champ] = $erreur;
	}
	return $flux;
}

function association_compta_association_maintenance_bdd_executer($flux) {
	include_spip('inc/association_compta_maintenance');
	$options = (array) ($flux['args']['options'] ?? array());
	$actions = (array) ($options['actions'] ?? array());
	$dry_run = (bool) ($options['dry_run'] ?? true);
	$lot = intval($options['lot'] ?? 1000);
	$maintenant = intval($flux['args']['maintenant'] ?? time());
	$flux['data']['supprimer_cotisations_orphelines'] = !empty($actions['supprimer_cotisations_orphelines'])
		? asso_supprimer_cotisations_orphelines($dry_run, $lot)
		: array('skipped' => true);
	$flux['data']['supprimer_cotisations_non_encaissees_anciennes'] = !empty($actions['supprimer_cotisations_non_encaissees'])
		? asso_supprimer_cotisations_non_encaissees_anciennes($maintenant, intval($options['mois_non_encaisse'] ?? 6), $dry_run, $lot)
		: array('skipped' => true);
	return $flux;
}
