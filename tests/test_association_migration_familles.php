<?php

define('_ECRIRE_INC_VERSION', true);
define('_LOG_ERREUR', 1);
define('_LOG_INFO_IMPORTANTE', 2);

$GLOBALS['familles_test'] = array();
$GLOBALS['liens_test'] = array();
$GLOBALS['prochain_id_famille_test'] = 100;

function include_spip($fichier) {}
function test_plugin_actif($plugin) { return $plugin === 'familles'; }
function sql_showtable($table, $complet = false) {
	return array('field' => array('auteur_compte_principal' => 'bigint(21)'));
}
function sql_allfetsel($select, $table, $where = '', $groupby = '', $orderby = '') {
	return array(
		array('id_auteur' => 10, 'nom' => 'Principal', 'email' => 'principal@example.test', 'auteur_compte_principal' => 0),
		array('id_auteur' => 11, 'nom' => 'Secondaire', 'email' => 'secondaire@example.test', 'auteur_compte_principal' => 10),
		array('id_auteur' => 20, 'nom' => 'Existant', 'email' => 'existant@example.test', 'auteur_compte_principal' => 0),
	);
}
function _T($cle, $vars = array()) { return $cle; }
function spip_log($message, $categorie) {}
function familles_objet_lister_familles($objet, $id_auteur, $options = array()) {
	if ($objet !== 'auteur') {
		return array();
	}
	return $GLOBALS['familles_test'][$id_auteur] ?? array();
}
function familles_creer($set = array()) {
	return $GLOBALS['prochain_id_famille_test']++;
}
function familles_objet_est_lie($id_famille, $objet, $id_objet, $role = null) {
	return isset($GLOBALS['liens_test'][$id_famille . ':' . $objet . ':' . $id_objet . ':' . $role]);
}
function familles_associer_objet($id_famille, $objet, $id_objet, $role = '', $options = array(), $priorite = 0, $set = array()) {
	$GLOBALS['liens_test'][$id_famille . ':' . $objet . ':' . $id_objet . ':' . $role] = true;
	$GLOBALS['familles_test'][$id_objet] = array_values(array_unique(array_merge(
		$GLOBALS['familles_test'][$id_objet] ?? array(),
		array($id_famille)
	)));
	return true;
}

$GLOBALS['familles_test'][20] = array(200);
require dirname(__DIR__) . '/plugins/association-adhesions/inc/association_familles.php';

if (!association_familles_integration_disponible()) {
	fwrite(STDERR, "L'integration Familles devrait etre disponible\n");
	exit(1);
}

$rapport = association_familles_previsualiser_migration();
if ($rapport['totaux'] !== array('familles' => 1, 'principaux' => 2, 'secondaires' => 1, 'alertes' => 0)) {
	fwrite(STDERR, "La previsualisation de migration est incorrecte\n");
	exit(1);
}

$premier = association_familles_executer_migration();
if ($premier['familles_creees'] !== 1 || $premier['liens_crees'] !== 3 || $premier['erreurs']) {
	fwrite(STDERR, "La premiere migration est incorrecte\n");
	exit(1);
}
if (($GLOBALS['familles_test'][11] ?? array()) !== array(100)) {
	fwrite(STDERR, "Le compte secondaire n'est pas rattache a la famille principale\n");
	exit(1);
}

$second = association_familles_executer_migration();
if ($second['familles_creees'] !== 0 || $second['liens_crees'] !== 0 || $second['erreurs']) {
	fwrite(STDERR, "La migration n'est pas idempotente\n");
	exit(1);
}

echo "Test migration Association vers Familles reussi\n";
