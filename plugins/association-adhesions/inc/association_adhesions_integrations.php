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
