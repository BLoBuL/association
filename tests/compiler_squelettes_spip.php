<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce test doit etre lance avec SPIP CLI (php:run).\n");
	exit(2);
}

$racine = defined('_DIR_PLUGIN_ASSOCIATION')
	? rtrim(_DIR_PLUGIN_ASSOCIATION, '/\\')
	: dirname(__DIR__);
$racine = realpath($racine) ?: $racine;
$repertoires = array(
	'prives' => $racine . '/prive',
	'publics' => $racine . '/squelettes',
);

if (defined('_DIR_PLUGIN_ASSOCIATION')) {
	_chemin(array(rtrim(_DIR_PLUGIN_ASSOCIATION, '/\\') . '/'));
}

// Le compilateur CLI ne charge pas toujours les fonctions globales du plugin
// ni les compagnons des pages comme le fait le pipeline web complet.
include_spip('association_fonctions');
$fonctions_squelettes = array($racine . '/prive/squelettes', $racine . '/squelettes');
foreach ($fonctions_squelettes as $repertoire_fonctions) {
	if (!is_dir($repertoire_fonctions)) {
		continue;
	}
	$fonctions = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($repertoire_fonctions));
	foreach ($fonctions as $fichier) {
		if ($fichier->isFile() && substr($fichier->getFilename(), -14) === '_fonctions.php') {
			include_once $fichier->getPathname();
		}
	}
}

include_spip('public/styliser');
include_spip('public/composer');

$echecs = array();
$compiles = array_fill_keys(array_keys($repertoires), 0);
foreach ($repertoires as $type => $repertoire) {
	if (!is_dir($repertoire)) {
		continue;
	}
	$iterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($repertoire));
	foreach ($iterateur as $fichier) {
		if (!$fichier->isFile() || $fichier->getExtension() !== 'html') continue;
		$chemin = str_replace('\\', '/', $fichier->getPathname());
		$fond = substr($chemin, strlen(str_replace('\\', '/', $racine)) + 1, -5);
		try {
			list($squelette, $mime, $grammaire, $source) = public_styliser_dist(
				$fond,
				array(),
				$GLOBALS['spip_lang'],
				''
			);
			$fonction = $squelette
				? public_composer_dist($squelette, $mime, $grammaire, $source, '')
				: false;
			if (!$fonction) {
				$echecs[] = $fond;
				continue;
			}
			$compiles[$type]++;
		} catch (Throwable $e) {
			$echecs[] = $fond . ' : ' . $e->getMessage();
		}
	}
}

if ($echecs) {
	foreach ($echecs as $echec) fwrite(STDERR, "ECHEC: $echec\n");
	exit(1);
}

echo "OK: {$compiles['prives']} squelettes prives et {$compiles['publics']} squelettes publics compiles sous SPIP " . $GLOBALS['spip_version_branche'] . ".\n";
