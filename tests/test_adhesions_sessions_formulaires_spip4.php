<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

$GLOBALS['test_session_spip'] = array();

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

function association_log($module, $message, $niveau) {
	return true;
}

function generer_url_ecrire($exec) {
	return 'ecrire/?exec=' . $exec;
}

function _T($cle) {
	return $cle;
}

function test_assert($condition, $message) {
	if (!$condition) {
		echo "ECHEC: $message\n";
		exit(1);
	}
	echo "OK: $message\n";
}

include_once PLUGIN_ROOT . '/plugins/association-adhesions/association_adhesions_fonctions.php';
include_once PLUGIN_ROOT . '/plugins/association-adhesions/formulaires/adherents_recherche_rapide.php';
include_once PLUGIN_ROOT . '/plugins/association-adhesions/formulaires/adherents_recherche_avancee.php';

$_REQUEST = array('_input_nom_famille' => 'Martin');
formulaires_adherents_recherche_rapide_traiter_dist();
test_assert(
	($GLOBALS['test_session_spip']['adherents_recherche_rapide']['_input_nom_famille'] ?? '') === 'Martin',
	'la recherche rapide est memorisee par la session SPIP'
);

$_REQUEST = array();
formulaires_adherents_recherche_rapide_traiter_dist();
test_assert(
	!isset($GLOBALS['test_session_spip']['adherents_recherche_rapide']),
	'la recherche rapide vide efface la valeur de session SPIP'
);

$_POST = array('type_recherche' => 'multicritere', 'ville' => 'Paris', 'action' => 'formulaire');
formulaires_adherents_recherche_avancee_traiter_dist();
test_assert(
	($GLOBALS['test_session_spip']['adherents_recherche_avancee']['ville'] ?? '') === 'Paris'
		&& !isset($GLOBALS['test_session_spip']['adherents_recherche_avancee']['action']),
	'la recherche avancee persiste uniquement ses criteres utiles'
);

$_REQUEST = array('periode' => '2025/2026', 'statut_cotisation' => 'ok');
$filtres = filtre_filtres_effectifs_cotisations();
test_assert(
	$filtres['periode'] === '2025/2026' && $filtres['statut_cotisation'] === 'ok',
	'les filtres de cotisation donnent priorite a la requete'
);
$_REQUEST = array();
$filtres = filtre_filtres_effectifs_cotisations();
test_assert(
	$filtres['periode'] === '2025/2026' && $filtres['statut_cotisation'] === 'ok',
	'les filtres de cotisation sont relus depuis la session SPIP'
);

foreach (array(
	'plugins/association-adhesions/association_adhesions_fonctions.php',
	'plugins/association-adhesions/formulaires/adherents_recherche_rapide.php',
	'plugins/association-adhesions/formulaires/adherents_recherche_avancee.php',
) as $fichier) {
	$source = file_get_contents(PLUGIN_ROOT . '/' . $fichier);
	test_assert(strpos($source, 'session_start(') === false, $fichier . ' ne demarre aucune session PHP native');
	test_assert(strpos($source, '$_SESSION') === false, $fichier . ' ne lit pas directement la superglobale de session');
}

echo "Tous les tests de session SPIP 4 des formulaires Adhesions sont passes.\n";
