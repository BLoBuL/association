<?php

// Ceci est un fichier langue de SPIP -- This is a SPIP language file

///  Fichier produit par PlugOnet
// Module: paquet-association
// Langue: fr
// Date: 04-01-2013 15:31:28
// Items: 2

if (!defined('_ECRIRE_INC_VERSION')) return;

$traductions = array(

    'association_slogan' => 'Module de gestion associative - Backoffice',
	'association_description' => 'Ce plugin permet de gérer les associations et les adhérents.',

    // Messages plugin


);

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
