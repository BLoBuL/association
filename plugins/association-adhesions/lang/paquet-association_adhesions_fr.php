<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$traductions = [
	'association_adhesions_description' => 'Adhésions, cotisations et validité des membres.',
	'association_adhesions_nom' => 'Association - Adhésions',
	'association_adhesions_slogan' => 'Gérer les adhésions séparément du journal comptable',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
