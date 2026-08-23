<?php

define('_ECRIRE_INC_VERSION', true);
$GLOBALS['meta']['adresse_site'] = 'https://www.Exemple-Asso.test/espace/';
function pipeline($nom, $flux) {
	if ($nom === 'association_configuration_navigation') {
		$flux['data']['test_module'] = ['ordre' => 25, 'label' => 'test:module'];
		return $flux['data'];
	}
	return $flux;
}
function generer_url_ecrire($page, $args = '') {
	return $page . ($args !== '' ? '?' . $args : '');
}
require dirname(__DIR__) . '/association_fonctions.php';

$erreurs = array();
$verifier = static function ($condition, $message) use (&$erreurs) {
	if (!$condition) {
		$erreurs[] = $message;
	}
};

$verifier(
	association_nom_fichier_export_rgpd('2026-08-23', 42) === 'export-association-exemple-asso_test-42-2026-08-23.json',
	'Le nom de fichier RGPD est incorrect.'
);
$verifier(association_is_serialized('b:0;'), 'Le booléen false sérialisé doit être reconnu.');
$verifier(!association_is_serialized('texte'), 'Une chaîne simple ne doit pas être reconnue comme sérialisée.');
$verifier(
	deserialize_values(array('liste' => 'a:2:{i:0;s:1:"a";i:1;s:1:"b";}', 'texte' => 'ok'))
		=== array('liste' => array('a', 'b'), 'texte' => 'ok'),
	'La désérialisation de configuration est incorrecte.'
);
$verifier(filtre_scalar_val(array('', array('ignore'), 'valeur'), 'defaut') === 'valeur', 'Le premier scalaire utile doit être retourné.');
$verifier(filtre_scalar_val(array(array('a', 'b')), 'defaut') === 'a,b', 'Les tableaux imbriqués doivent être aplatis.');
$verifier(filtre_scalar_val(null, 'defaut') === 'defaut', 'La valeur par défaut doit être conservée.');
$navigation_admin = association_configuration_navigation(false);
$navigation_webmestre = association_configuration_navigation('oui');
$verifier(
	array_keys($navigation_admin) === ['info', 'test_module', 'modules'],
	'La navigation doit fusionner et ordonner les contributions des modules.'
);
$verifier(
	$navigation_admin['test_module']['url'] === 'configurer_association?config=test_module',
	'Une entree modulaire doit recevoir son URL de configuration.'
);
$verifier(
	isset($navigation_webmestre['maintenance_bdd'], $navigation_webmestre['debug']),
	'Les entrees techniques doivent rester reservees au webmestre.'
);

if ($erreurs) {
	foreach ($erreurs as $erreur) {
		fwrite(STDERR, "ECHEC: {$erreur}\n");
	}
	exit(1);
}

echo "OK: fonctions transversales du socle.\n";
