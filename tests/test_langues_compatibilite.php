<?php

define('_ECRIRE_INC_VERSION', 1);
$racine = dirname(__DIR__);
$fichiers = array_merge(glob($racine . '/lang/*.php'), glob($racine . '/plugins/*/lang/*.php'));
$attendus = array();
foreach ($fichiers as $fichier) {
	$GLOBALS['idx_lang'] = 'test_langue_ancienne';
	unset($GLOBALS['test_langue_ancienne']);
	$tableau = include $fichier;
	if (!is_array($tableau) || !$tableau || $tableau !== ($GLOBALS['test_langue_ancienne'] ?? null)) {
		throw new RuntimeException('Chargement SPIP 4.0 incorrect : ' . $fichier);
	}
	$attendus[$fichier] = $tableau;
}
// La fonction n'existe qu'après la première passe, comme sur SPIP récent.
if (!function_exists('lire_fichier_langue')) {
	function lire_fichier_langue($fichier) { return include $fichier; }
}
foreach ($attendus as $fichier => $attendu) {
	unset($GLOBALS['test_langue_ancienne']);
	if (lire_fichier_langue($fichier) !== $attendu || isset($GLOBALS['test_langue_ancienne'])) {
		throw new RuntimeException('Chargement SPIP récent incorrect : ' . $fichier);
	}
}
echo 'OK : ' . count($fichiers) . " fichiers de langue compatibles avec les deux chargeurs.\n";
