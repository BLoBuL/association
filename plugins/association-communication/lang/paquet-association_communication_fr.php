<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
$traductions = [
	'association_communication_nom' => 'Association - Communication',
	'association_communication_slogan' => 'Gérer les notifications et abonnements associatifs',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
