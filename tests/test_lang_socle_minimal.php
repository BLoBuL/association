<?php

$racine = dirname(__DIR__);
$cles = array(
	'erreur_auteur_inexistant',
	'erreur_configurer_association_titre',
	'erreur_date',
	'erreur_format_date',
	'log_cat_autorisations',
	'log_cat_cron',
	'log_cat_migration',
	'log_cat_sync',
	'titre_menu_association',
	'titre_onglet_configurer_association',
);
$erreurs = array();

if (!defined('_ECRIRE_INC_VERSION')) {
	define('_ECRIRE_INC_VERSION', true);
}
foreach (array('fr', 'en', 'es') as $langue) {
	$index = 'association_test_' . $langue;
	$GLOBALS['idx_lang'] = $index;
	include $racine . '/lang/association_' . $langue . '.php';
	$traductions = $GLOBALS[$index] ?? array();
	if (array_keys($traductions) !== $cles) {
		$erreurs[] = "Le domaine association_$langue doit contenir exactement les dix clés transversales.";
	}
	foreach ($cles as $cle) {
		if (trim((string) ($traductions[$cle] ?? '')) === '') {
			$erreurs[] = "La traduction $langue de $cle est absente.";
		}
	}
}

$references = array();
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $fichier) {
	$chemin = str_replace('\\', '/', $fichier->getPathname());
	if (!$fichier->isFile()
		|| !preg_match('/\.(?:php|html|xml|yaml)$/', $chemin)
		|| str_contains($chemin, '/lang/association_')
		|| str_contains($chemin, '/docs/')
		|| str_contains($chemin, '/tests/')) {
		continue;
	}
	if (preg_match_all('/association:([a-zA-Z0-9_]+)/', file_get_contents($chemin), $matches)) {
		foreach ($matches[1] as $cle) {
			if (!in_array($cle, $cles, true) && !in_array($cle, array('config', 'installation'), true)) {
				$references[$cle] = true;
			}
		}
	}
}
if ($references) {
	$erreurs[] = 'Des consommateurs utilisent encore le domaine historique : ' . implode(', ', array_keys($references));
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: le domaine association contient uniquement les traductions transversales.\n";
