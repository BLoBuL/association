<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
$traductions = [
	'association_ventes_nom' => 'Association - Ventes',
	'association_ventes_slogan' => 'Gérer les ventes associatives',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
