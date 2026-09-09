<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$traductions = array(
	'erreur_auteur_inexistant' => 'User account not found.',
	'erreur_configurer_association_titre' => 'Your submission contains errors!',
	'erreur_date' => 'This date does not exist',
	'erreur_format_date' => 'The date must use the DD/MM/YYYY format',
	'log_cat_autorisations' => 'Authorizations',
	'log_cat_cron' => 'Scheduled tasks (CRON)',
	'log_cat_migration' => 'Configuration migration',
	'log_cat_sync' => 'Synchronization',
	'titre_menu_association' => 'Association',
	'titre_onglet_configurer_association' => 'Settings',
);

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
