<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
$traductions = [
	'association_groupes_nom' => 'Association - Groupes',
	'association_groupes_slogan' => 'Gérer les groupes, fonctions et rôles associatifs',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
