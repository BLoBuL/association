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
	$GLOBALS['test_session_spip'][$cle] = $valeur;
	return $valeur;
}

function test_assert($condition, $message) {
	if (!$condition) {
		echo "ECHEC: $message\n";
		exit(1);
	}
	echo "OK: $message\n";
}

include_once PLUGIN_ROOT . '/plugins/association-compta/inc/fonctions/comptes.php';

test_assert(
	filtre_association_get_inclure_non_validees('') === 0,
	'la preference est desactivee par defaut'
);
test_assert(
	filtre_association_get_inclure_non_validees('1') === 1,
	'la valeur explicite active la preference'
);
test_assert(
	($GLOBALS['test_session_spip']['association_compta_inclure_non_validees'] ?? null) === 1,
	'la preference est stockee par la session SPIP sous un nom metier'
);
test_assert(
	filtre_association_get_inclure_non_validees('') === 1,
	'la preference SPIP est relue sans session PHP native'
);
test_assert(
	filtre_association_get_inclure_non_validees('0') === 0,
	'la valeur explicite desactive la preference'
);

$source = file_get_contents(PLUGIN_ROOT . '/plugins/association-compta/inc/fonctions/comptes.php');
test_assert(strpos($source, 'session_start(') === false, 'Comptabilite ne demarre aucune session PHP native');
test_assert(strpos($source, '$_SESSION') === false, 'Comptabilite ne lit pas directement la superglobale de session');

echo "Tous les tests de session SPIP 4 de Comptabilite sont passes.\n";
