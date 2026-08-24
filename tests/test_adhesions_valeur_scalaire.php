<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	define('_ECRIRE_INC_VERSION', true);
}
require dirname(__DIR__) . '/plugins/association-adhesions/association_adhesions_fonctions.php';

$cas = array(
	array(array('', array('ignore'), 'valeur'), 'defaut', 'valeur'),
	array(array(array('a', 'b')), 'defaut', 'a,b'),
	array(null, 'defaut', 'defaut'),
);
foreach ($cas as [$valeur, $defaut, $attendu]) {
	$resultat = association_adhesions_valeur_scalaire($valeur, $defaut);
	if ($resultat !== $attendu) {
		fwrite(STDERR, 'Valeur scalaire inattendue : ' . var_export($resultat, true) . "\n");
		exit(1);
	}
}

echo "OK: Adhésions normalise ses paramètres de période.\n";
