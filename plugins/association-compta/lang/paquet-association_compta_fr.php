<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
$traductions = [
	'association_compta_description' => 'Journal, plan comptable et destinations analytiques.',
	'association_compta_nom' => 'Association - Comptabilité',
	'association_compta_slogan' => 'Tenir la comptabilité associative',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
