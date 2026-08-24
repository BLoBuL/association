<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce test doit etre lance avec SPIP CLI (php:run).\n");
	exit(2);
}

$racine = defined('_DIR_PLUGIN_ASSOCIATION')
	? rtrim(_DIR_PLUGIN_ASSOCIATION, '/\\')
	: dirname(__DIR__);
$racine = realpath($racine) ?: $racine;
$racines = array('association' => $racine);
foreach (array(
	'adhesions' => '_DIR_PLUGIN_ASSOCIATION_ADHESIONS',
	'communication' => '_DIR_PLUGIN_ASSOCIATION_COMMUNICATION',
	'compta' => '_DIR_PLUGIN_ASSOCIATION_COMPTA',
	'dons' => '_DIR_PLUGIN_ASSOCIATION_DONS',
	'evenements' => '_DIR_PLUGIN_ASSOCIATION_EVENEMENTS',
	'groupes' => '_DIR_PLUGIN_ASSOCIATION_GROUPES',
	'paiements' => '_DIR_PLUGIN_ASSOCIATION_PAIEMENTS',
	'prets' => '_DIR_PLUGIN_ASSOCIATION_PRETS',
	'ventes' => '_DIR_PLUGIN_ASSOCIATION_VENTES',
) as $module => $constante) {
	if (defined($constante)) {
		$racine_module = rtrim(constant($constante), '/\\');
		$racines[$module] = realpath($racine_module) ?: $racine_module;
	}
}
$repertoires = array(
	'prives' => array('racine_fond' => $racine, 'chemin' => $racine . '/prive'),
);
foreach ($racines as $module => $racine_module) {
	$repertoires['prives_' . $module] = array(
		'racine_fond' => $racine_module,
		'chemin' => $racine_module . '/prive',
	);
	$repertoires['publics_' . $module] = array(
		'racine_fond' => $racine_module . '/squelettes',
		'chemin' => $racine_module . '/squelettes',
	);
	foreach (array('modeles', 'emails', 'notifications') as $type_fond) {
		$repertoires[$type_fond . '_' . $module] = array(
			'racine_fond' => $racine_module,
			'chemin' => $racine_module . '/' . $type_fond,
		);
	}
}
if (isset($racines['evenements'])) {
	$repertoires['exports_evenements'] = array(
		'racine_fond' => $racines['evenements'],
		'chemin' => $racines['evenements'],
		'fichiers' => array('export_activites.csv.html', 'inscriptions_evenement.csv.html'),
	);
}

foreach ($racines as $racine_plugin) {
	_chemin(array($racine_plugin . '/'));
}

// Le compilateur CLI ne charge pas toujours les fonctions globales du plugin
// ni les compagnons des pages comme le fait le pipeline web complet.
include_spip('association_fonctions');
include_spip('inc/filtres_ecrire');
include_spip('public/assembler');
$trouver_table = charger_fonction('trouver_table', 'base');
foreach (array('spip_asso_cotisations', 'asso_cotisations', 'spip_asso_comptes', 'spip_asso_activites') as $table) {
	if (!$trouver_table($table)) {
		fwrite(STDERR, "ECHEC: table déclarée introuvable: {$table}\n");
	exit(1);
	}
}
$fonctions_squelettes = array($racine . '/prive/squelettes');
foreach ($racines as $racine_plugin) {
	$fonctions_squelettes[] = $racine_plugin . '/squelettes';
}
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
foreach ($repertoires as $type => $description) {
	$repertoire = $description['chemin'];
	$racine_fond = $description['racine_fond'];
	if (!is_dir($repertoire)) {
		continue;
	}
	$iterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($repertoire));
	foreach ($iterateur as $fichier) {
		if (!$fichier->isFile() || $fichier->getExtension() !== 'html') continue;
		$chemin = str_replace('\\', '/', $fichier->getPathname());
		if (!empty($description['fichiers'])) {
			$relatif = substr($chemin, strlen(str_replace('\\', '/', $racine_fond)) + 1);
			if (!in_array($relatif, $description['fichiers'], true)) {
				continue;
			}
		}
		$fond = substr($chemin, strlen(str_replace('\\', '/', $racine_fond)) + 1, -5);
		if ($fond === 'prive/objets/liste/inc-gis-auteur' && !defined('_DIR_PLUGIN_GIS')) {
			continue;
		}
		// Cette barre est assemblée dynamiquement par le contrôleur privé. Sa
		// boucle DATA journalise à tort une tentative SQL quand elle est compilée
		// seule par php:run, sans page privée appelante.
		if ($fond === 'prive/squelettes/top/inc-top_association') {
			continue;
		}
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

$prives = array_sum(array_filter($compiles, fn($nombre, $type) => str_starts_with($type, 'prives'), ARRAY_FILTER_USE_BOTH));
$publics = array_sum(array_filter($compiles, fn($nombre, $type) => str_starts_with($type, 'publics_'), ARRAY_FILTER_USE_BOTH));
$composants = array_sum(array_filter(
	$compiles,
	fn($nombre, $type) => preg_match('/^(?:modeles|emails|notifications|exports)_/', $type),
	ARRAY_FILTER_USE_BOTH
));
echo "OK: {$prives} squelettes prives, {$publics} pages publiques et {$composants} composants front compiles sous SPIP " . $GLOBALS['spip_version_branche'] . ".\n";
