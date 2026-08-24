<?php

define('_ECRIRE_INC_VERSION', 1);

$GLOBALS['association_test_plugins'] = array();
$GLOBALS['association_test_sql_calls'] = 0;
$GLOBALS['association_test_zones'] = array(
	array('id_zone' => '2', 'titre' => 'Membres'),
	array('id_zone' => '4', 'titre' => 'Comite'),
);

function test_plugin_actif($prefixe) {
	return !empty($GLOBALS['association_test_plugins'][$prefixe]);
}

function sql_allfetsel($select, $table, $where = '', $group = '', $order = '') {
	$GLOBALS['association_test_sql_calls']++;
	if ($table === 'spip_zones') {
		return $GLOBALS['association_test_zones'];
	}
	return array(array('id_zone' => 2));
}

function sql_fetsel($select, $table, $where = '') {
	$GLOBALS['association_test_sql_calls']++;
	return array('id_gis' => 7, 'lat' => '48.1', 'lon' => '2.3');
}

function sql_in($champ, $valeurs) {
	return $champ . ' IN (' . implode(',', array_map('intval', $valeurs)) . ')';
}

require_once dirname(__DIR__) . '/plugins/association-adhesions/inc/association_adhesions_integrations.php';

function association_test_integration_assert($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, "ECHEC: {$message}\n");
		exit(1);
	}
}

association_test_integration_assert(association_adhesions_zones_options() === array(), 'Les zones doivent etre absentes sans Acces restreint.');
association_test_integration_assert(association_adhesions_zones_auteur_liees(array(2), 42) === array(), 'Les liens ne doivent pas etre lus sans Acces restreint.');
association_test_integration_assert(association_adhesions_gis_point_auteur(42) === array(), 'Le point ne doit pas etre lu sans GIS.');
association_test_integration_assert($GLOBALS['association_test_sql_calls'] === 0, 'Aucune table optionnelle ne doit etre interrogee sans son plugin.');

$GLOBALS['association_test_plugins']['accesrestreint'] = true;
association_test_integration_assert(
	association_adhesions_zones_options() === array(2 => 'Membres', 4 => 'Comite'),
	'Les zones actives doivent etre exposees avec leurs identifiants.'
);
association_test_integration_assert(
	association_adhesions_zones_auteur_liees(array(2, 4), 42) === array(2),
	'Les liens de zones doivent etre lus lorsque le plugin est actif.'
);

$GLOBALS['association_test_plugins']['gis'] = true;
$point = association_adhesions_gis_point_auteur(42);
association_test_integration_assert((int) ($point['id_gis'] ?? 0) === 7, 'Le point GIS lie doit etre retourne lorsque GIS est actif.');

$paquet = file_get_contents(dirname(__DIR__) . '/plugins/association-adhesions/paquet.xml');
association_test_integration_assert(strpos($paquet, '<utilise nom="accesrestreint"') !== false, 'Acces restreint doit etre declare comme integration optionnelle.');
association_test_integration_assert(strpos($paquet, '<utilise nom="gis"') !== false, 'GIS doit etre declare comme integration optionnelle.');

fwrite(STDOUT, "OK: integrations optionnelles Zones/GIS isolees et gardees\n");
