<?php

$traductions = [
	'boutique_titre' => 'Boutique associative',
	'commande_titre' => 'Commandes',
	'commande_absente' => 'Aucune commande en cours.',
	'commande_reference' => 'Commande @reference@',
	'commander' => 'Créer la commande',
	'configuration_devise' => 'Devise par défaut',
	'configuration_rubrique' => 'Identifiant de la rubrique catalogue',
	'configuration_titre' => 'Commerce',
	'panier_titre' => 'Panier',
	'modifier_panier' => 'Modifier le panier',
	'retour_boutique' => 'Continuer mes achats',
	'titre_menu' => 'Commerce',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
