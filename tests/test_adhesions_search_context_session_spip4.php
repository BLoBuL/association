<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

$GLOBALS['test_session_spip'] = array();
$GLOBALS['test_filtres_dynamiques'] = array();

function include_spip($chemin) {
	return true;
}

function session_get($cle) {
	return $GLOBALS['test_session_spip'][$cle] ?? null;
}

function session_set($cle, $valeur) {
	if ($valeur === null) {
		unset($GLOBALS['test_session_spip'][$cle]);
	} else {
		$GLOBALS['test_session_spip'][$cle] = $valeur;
	}
	return $valeur;
}

function _request($cle) {
	return $_REQUEST[$cle] ?? null;
}

function association_recherche_avancee_reset_demandee() {
	return false;
}

function liste_filtres_dynamiques_adherents() {
	return $GLOBALS['test_filtres_dynamiques'];
}

function filtre_liste_periodes_cotisations($limite = 0, $avec_stats = false) {
	return array();
}

function est_actif_gestion_comptes_secondaires() {
	return false;
}

function test_assert($condition, $message) {
	if (!$condition) {
		echo "ECHEC: $message\n";
		exit(1);
	}
	echo "OK: $message\n";
}

include_once PLUGIN_ROOT . '/plugins/association-adhesions/inc/adherents_search_context.php';

$GLOBALS['test_session_spip'] = array(
	'adherents_recherche_rapide' => array('_input_nom_famille' => 'Martin'),
	'adherents_filtres' => array('statut_interne' => 'prospect', 'type_adherent' => 'famille'),
);
$_REQUEST = $_GET = $_POST = array();
$contexte = AdherentsSearchContext::fromRequest();
test_assert(
	$contexte->nom === 'Martin' && $contexte->recherche_type === 'rapide',
	'le contexte restaure la recherche rapide depuis la session SPIP'
);
test_assert(
	$contexte->statut_interne === 'prospect' && $contexte->type_adherent === 'famille',
	'le contexte restaure les filtres standards depuis la session SPIP'
);

$_REQUEST = $_GET = array('clear_search' => '1', 'clear_filters' => 'statut_interne');
$_POST = array();
$contexte = AdherentsSearchContext::fromRequest();
test_assert(
	$contexte->nom === null && !isset($GLOBALS['test_session_spip']['adherents_recherche_rapide']),
	'l effacement supprime la recherche de la session SPIP'
);
test_assert(
	($GLOBALS['test_session_spip']['adherents_filtres']['statut_interne'] ?? null) === null,
	'l effacement cible retire uniquement le filtre demande'
);

$GLOBALS['test_filtres_dynamiques'] = array('region' => array('type' => 'select'));
$_REQUEST = $_GET = array('region' => 'Europe');
$_POST = array();
$contexte = AdherentsSearchContext::fromRequest();
test_assert(
	($contexte->filtres_dynamiques['region'] ?? null) === 'Europe'
		&& ($GLOBALS['test_session_spip']['adherents_filtres']['region'] ?? null) === 'Europe',
	'un filtre dynamique est persiste avec la meme priorite requete puis session'
);

$source = file_get_contents(PLUGIN_ROOT . '/plugins/association-adhesions/inc/adherents_search_context.php');
test_assert(strpos($source, 'session_start(') === false, 'le contexte ne demarre aucune session PHP native');
test_assert(strpos($source, '$_SESSION') === false, 'le contexte ne lit pas directement la superglobale de session');

echo "Tous les tests de session SPIP 4 du contexte Adherents sont passes.\n";
