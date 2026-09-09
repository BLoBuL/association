<?php

$traductions = [
	'banniere' => 'Bannière',
	'bannieres' => 'Bannières publicitaires',
	'banniere_aucune' => 'Aucune bannière',
	'banniere_creer' => 'Ajouter une bannière',
	'banniere_modifier' => 'Modifier cette bannière',
	'banniere_nb' => '@nb@ bannières',
	'banniere_une' => 'Une bannière',
	'configuration_titre' => 'Bannières',
	'configuration_emplacement' => 'Emplacement par défaut',
	'date_debut' => 'Début de diffusion',
	'date_fin' => 'Fin de diffusion',
	'descriptif' => 'Description',
	'emplacement' => 'Emplacement',
	'ordre' => 'Ordre d’affichage',
	'statut' => 'Statut',
	'titre' => 'Titre',
	'url' => 'Lien cible',
	'voir_annonceur' => 'Voir le site de l’annonceur',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
