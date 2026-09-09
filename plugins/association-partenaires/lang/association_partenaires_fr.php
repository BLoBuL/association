<?php

$traductions = [
	'configuration_titre' => 'Partenaires',
	'configuration_titre_public' => 'Titre de la page publique',
	'date_debut' => 'Début du partenariat',
	'date_fin' => 'Fin du partenariat',
	'descriptif' => 'Présentation',
	'niveau' => 'Niveau de partenariat',
	'organisation' => 'Organisation',
	'ordre' => 'Ordre d’affichage',
	'partenaire' => 'Partenaire',
	'partenaire_aucun' => 'Aucun partenaire',
	'partenaire_creer' => 'Ajouter un partenaire',
	'partenaire_modifier' => 'Modifier ce partenaire',
	'partenaire_nb' => '@nb@ partenaires',
	'partenaire_un' => 'Un partenaire',
	'partenaires' => 'Partenaires',
	'statut' => 'Statut',
	'titre' => 'Titre public',
	'url' => 'Adresse web',
	'voir_site' => 'Voir le site',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
