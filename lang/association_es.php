<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$traductions = array(
	'erreur_auteur_inexistant' => 'No se ha encontrado la cuenta de usuario.',
	'erreur_configurer_association_titre' => '¡Los datos introducidos contienen errores!',
	'erreur_date' => 'Esta fecha no existe',
	'erreur_format_date' => 'La fecha debe tener el formato DD/MM/AAAA',
	'log_cat_autorisations' => 'Autorizaciones',
	'log_cat_cron' => 'Tareas programadas (CRON)',
	'log_cat_migration' => 'Migración de la configuración',
	'log_cat_sync' => 'Sincronización',
	'titre_menu_association' => 'Vida asociativa',
	'titre_onglet_configurer_association' => 'Configuración',
);

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
