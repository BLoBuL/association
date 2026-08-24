<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce test doit être lancé avec SPIP CLI (php:run).\n");
	exit(2);
}

include_spip('public/styliser');
include_spip('public/composer');
include_spip('public/assembler');

$prefixes = array(
	'association',
	'association_adhesions',
	'association_communication',
	'association_compta',
	'association_dons',
	'association_evenements',
	'association_groupes',
	'association_paiements',
	'association_prets',
	'association_ventes',
);
$surfaces = array('prive', 'squelettes', 'modeles', 'notifications');
$fonds = array();
$echecs = array();

foreach ($prefixes as $prefixe) {
	$constante = '_DIR_PLUGIN_' . strtoupper($prefixe);
	if (!defined($constante)) {
		// La matrice d'autonomie compile uniquement les plugins réellement
		// chargés. L'absence de constante est l'état normal d'un complément
		// facultatif désactivé.
		continue;
	}
	$racine = rtrim(constant($constante), '/\\');
	foreach ($surfaces as $surface) {
		$dossier = $racine . '/' . $surface;
		if (!is_dir($dossier)) {
			continue;
		}
		$iterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dossier));
		foreach ($iterateur as $fichier) {
			if (!$fichier->isFile()) {
				continue;
			}
			if (str_ends_with($fichier->getFilename(), '_fonctions.php')) {
				include_once $fichier->getPathname();
				continue;
			}
			if ($fichier->getExtension() !== 'html') {
				continue;
			}
			$chemin = str_replace('\\', '/', $fichier->getPathname());
			$fond = substr($chemin, strlen(str_replace('\\', '/', $racine)) + 1, -5);
			$fonds[$prefixe . ':' . $fond] = $fond;
		}
	}
}

$compiles = 0;
foreach ($fonds as $identifiant => $fond) {
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
			$echecs[] = $identifiant;
			continue;
		}
		$compiles++;
	} catch (Throwable $e) {
		$echecs[] = $identifiant . ' : ' . $e->getMessage();
	}
}

if ($echecs) {
	foreach ($echecs as $echec) {
		fwrite(STDERR, "ECHEC: $echec\n");
	}
	exit(1);
}

echo "OK: $compiles squelettes de la suite compilés sous SPIP " . $GLOBALS['spip_version_branche'] . ".\n";
