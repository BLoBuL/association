<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
$traductions = [
	'association_paiements_nom' => 'Association - Paiements',
	'association_paiements_slogan' => 'Relier les métiers associatifs aux transactions Bank',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
