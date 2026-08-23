<?php

/**
 * Caracterisation de l'installateur du socle sur une base sans meta Association.
 *
 * Le test capture le plan transmis a maj_plugin() : il ne remplace pas le test
 * d'integration sur une vraie base SPIP, mais empeche le retour du bootstrap
 * manuel de version et des tables metier dans le `create` du socle.
 */

define('_ECRIRE_INC_VERSION', true);

$GLOBALS['association_installation_test'] = array(
	'inclusions' => array(),
	'cextras' => null,
	'maj' => null,
	'ecritures_meta' => array(),
);

function include_spip($fichier) {
	$GLOBALS['association_installation_test']['inclusions'][] = $fichier;
}

function association_declarer_champs_extras($champs = array()) {
	$champs['spip_evenements']['champ_test'] = array(
		'saisie' => 'input',
		'options' => array('nom' => 'champ_test', 'sql' => 'TEXT'),
	);
	return $champs;
}

function cextras_api_upgrade($champs, &$operations) {
	$GLOBALS['association_installation_test']['cextras'] = $champs;
	$operations[] = array('association_test_installer_cextras', array_keys($champs));
}

function maj_plugin($meta, $cible, $maj) {
	$GLOBALS['association_installation_test']['maj'] = compact('meta', 'cible', 'maj');
}

// Ces stubs transforment toute reintroduction du bootstrap manuel en echec.
function ecrire_config($cle, $valeur) {
	$GLOBALS['association_installation_test']['ecritures_meta'][] = array($cle, $valeur);
}

function lire_config($cle, $defaut = null) {
	return $defaut;
}

function sql_delete($table, $where = '') {
	$GLOBALS['association_installation_test']['ecritures_meta'][] = array('sql_delete', $table, $where);
}

require dirname(__DIR__) . '/association_administrations.php';
association_upgrade('association_base_version', '1.6.0');

$capture = $GLOBALS['association_installation_test'];
$erreurs = array();

if ($capture['ecritures_meta']) {
	$erreurs[] = 'une installation neuve ne doit pas fabriquer une ancienne meta de schema';
}

if (!$capture['maj'] || $capture['maj']['meta'] !== 'association_base_version' || $capture['maj']['cible'] !== '1.6.0') {
	$erreurs[] = 'maj_plugin() ne recoit pas la meta et la cible attendues';
}

$maj = $capture['maj']['maj'] ?? array();
$create = $maj['create'] ?? array();
$attendu_table = array('maj_tables', array('spip_association_metas'));
if (!isset($create[0]) || $create[0] !== $attendu_table) {
	$erreurs[] = 'create doit commencer par la creation canonique de spip_association_metas';
}

$tables_creees_par_socle = array();
foreach ($create as $operation) {
	if (($operation[0] ?? '') === 'maj_tables' && isset($operation[1]) && is_array($operation[1])) {
		$tables_creees_par_socle = array_merge($tables_creees_par_socle, $operation[1]);
	}
}
foreach (array(
	'spip_asso_comptes',
	'spip_asso_cotisations',
	'spip_asso_activites',
	'spip_asso_dons',
	'spip_asso_ventes',
	'spip_asso_prets',
	'spip_asso_ressources',
	'spip_evenements',
	'spip_asso_categories',
) as $table_interdite) {
	if (in_array($table_interdite, $tables_creees_par_socle, true)) {
		$erreurs[] = 'le socle tente encore de creer une table hors de son domaine: ' . $table_interdite;
	}
}

if (empty($capture['cextras']['spip_evenements']['champ_test'])) {
	$erreurs[] = 'les Champs Extras declares ne sont pas transmis a cextras_api_upgrade()';
}
if (count($create) < 2 || ($create[1][0] ?? '') !== 'association_test_installer_cextras') {
	$erreurs[] = 'les operations Champs Extras ne sont pas ajoutees au plan create';
}
if (!in_array(array('association_import_champs_extras'), $create, true)) {
	$erreurs[] = 'les Champs Extras auteurs historiques ne sont pas importes lors du create';
}

// Les migrations historiques restent disponibles pour une base possedant une
// ancienne meta : cette tranche ne doit pas les supprimer silencieusement.
foreach (array('1.1.0', '1.2.0', '1.5.9', '1.6.0') as $version_legacy) {
	if (!array_key_exists($version_legacy, $maj)) {
		$erreurs[] = 'migration historique absente: ' . $version_legacy;
	}
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: installation neuve canonique du socle Association.\n";
