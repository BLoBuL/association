<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Indique si une integration optionnelle est disponible.
 *
 * Les tables des plugins tiers ne doivent jamais etre interrogees avant ce
 * controle : Association - Adhesions reste installable sans GIS ni Acces
 * restreint.
 */
function association_adhesions_integration_active($prefixe) {
	return function_exists('test_plugin_actif') && test_plugin_actif($prefixe);
}

function association_adhesions_integration_module_actif($prefixe) {
	if (function_exists('association_adhesions_module_actif')) {
		return association_adhesions_module_actif($prefixe);
	}
	if (function_exists('association_plugin_actif')) {
		return association_plugin_actif($prefixe);
	}
	if (function_exists('test_plugin_actif')) {
		return test_plugin_actif($prefixe);
	}

	// Les tests unitaires historiques chargent les fichiers sans initialiser le
	// chargeur de plugins SPIP. Dans ce seul contexte, conserver le comportement
	// de la suite complète et laisser l'appel de façade déjà simulé décider.
	return true;
}

function association_adhesions_transaction_lire($id_transaction) {
	if (!association_adhesions_integration_module_actif('association_paiements')) {
		return array();
	}
	include_spip('inc/association_paiements_transactions');

	return association_paiements_transaction_lire((int) $id_transaction);
}

function association_adhesions_transactions_lire($ids_transactions) {
	if (!association_adhesions_integration_module_actif('association_paiements')) {
		return array();
	}
	include_spip('inc/association_paiements_transactions');

	return association_paiements_transactions_lire((array) $ids_transactions);
}

function association_adhesions_transactions_auteur_lire($id_auteur, $statuts = array()) {
	if (!association_adhesions_integration_module_actif('association_paiements')) {
		return array();
	}
	include_spip('inc/association_paiements_transactions');

	return association_paiements_transactions_auteur_lire((int) $id_auteur, $statuts);
}

function association_adhesions_transaction_modifier($id_transaction, array $donnees) {
	if (!association_adhesions_integration_module_actif('association_paiements')) {
		return true;
	}
	include_spip('inc/association_paiements_transactions');

	return association_paiements_transaction_modifier((int) $id_transaction, $donnees);
}

function association_adhesions_transaction_supprimer_non_encaissee($id_transaction) {
	if (!association_adhesions_integration_module_actif('association_paiements')) {
		return true;
	}
	include_spip('inc/association_paiements_transactions');

	return association_paiements_transaction_supprimer_non_encaissee((int) $id_transaction);
}

function association_adhesions_transactions_supprimer_non_encaissees($ids_transactions, $dry_run = false) {
	if (!association_adhesions_integration_module_actif('association_paiements')) {
		return array('supprimes' => 0, 'protegees' => array(), 'ids' => array());
	}
	include_spip('inc/association_paiements_transactions');

	return association_paiements_transactions_supprimer_non_encaissees((array) $ids_transactions, $dry_run);
}

function association_adhesions_mailshot_creer($sujet, $html, array $destinataires, array $options = array()) {
	if (!association_adhesions_integration_module_actif('association_communication')) {
		return 0;
	}
	include_spip('inc/association_communication_mailshot');

	return association_communication_mailshot_creer($sujet, $html, $destinataires, $options);
}

/**
 * Retourne les zones d'Acces restreint utilisables dans la configuration.
 */
function association_adhesions_zones_options() {
	if (!association_adhesions_integration_active('accesrestreint')) {
		return array();
	}
	$zones = array();
	foreach (sql_allfetsel('id_zone,titre', 'spip_zones', '', '', 'titre') ?: array() as $zone) {
		$zones[(int) $zone['id_zone']] = (string) $zone['titre'];
	}
	return $zones;
}

/**
 * Retourne les zones configurees deja liees a un auteur.
 */
function association_adhesions_zones_auteur_liees(array $zones, $id_auteur) {
	$id_auteur = (int) $id_auteur;
	if (!association_adhesions_integration_active('accesrestreint') || !$zones || !$id_auteur) {
		return array();
	}
	$lignes = sql_allfetsel(
		'id_zone',
		'spip_zones_liens',
		sql_in('id_zone', $zones) . " AND objet='auteur' AND id_objet=" . $id_auteur
	) ?: array();
	return array_values(array_unique(array_filter(array_map('intval', array_column($lignes, 'id_zone')))));
}

/**
 * Retourne le point GIS lie a un auteur, ou un tableau vide.
 */
function association_adhesions_gis_point_auteur($id_auteur) {
	$id_auteur = (int) $id_auteur;
	if (!association_adhesions_integration_active('gis') || !$id_auteur) {
		return array();
	}
	return sql_fetsel(
		'G.*',
		'spip_gis AS G LEFT JOIN spip_gis_liens AS T ON T.id_gis=G.id_gis',
		'T.id_objet=' . $id_auteur . " AND T.objet='auteur'"
	) ?: array();
}
