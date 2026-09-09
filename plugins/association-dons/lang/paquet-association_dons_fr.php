<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
$traductions = [
	'association_dons_nom' => 'Association - Dons',
	'association_dons_slogan' => 'Gérer les dons associatifs',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
