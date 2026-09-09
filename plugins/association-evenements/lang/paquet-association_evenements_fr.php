<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$traductions = [
	'association_evenements_description' => 'Inscriptions, participants et tarifs des événements.',
	'association_evenements_nom' => 'Association - Événements',
	'association_evenements_slogan' => 'Gérer les événements et leurs inscriptions',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
