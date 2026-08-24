<?php

$racine = dirname(__DIR__);
$socle = file_get_contents($racine . '/association_options.php');
$adhesions = file_get_contents($racine . '/plugins/association-adhesions/association_adhesions_options.php');
$groupes = file_get_contents($racine . '/plugins/association-groupes/association_groupes_fonctions.php');
$modele = file_get_contents($racine . '/plugins/association-groupes/modeles/asso_membres_responsables.php');
$paquet = file_get_contents($racine . '/plugins/association-groupes/paquet.xml');

if (str_contains($socle, 'function association_telfr(')
	|| str_contains($adhesions, 'function association_calculer_nom_membre(')
	|| !str_contains($groupes, 'function association_groupes_nom_membre(')
	|| !str_contains($groupes, 'function association_groupes_telephone(')
	|| !str_contains($modele, 'association_groupes_nom_membre')
	|| !str_contains($modele, 'association_groupes_telephone')
	|| !str_contains($paquet, 'nom="association_adhesions"')
) {
	fwrite(STDERR, "Les filtres ou dépendances du modèle Groupes ne sont pas autonomes.\n");
	exit(1);
}

echo "OK: le modèle Groupes possède ses filtres et sa dépendance métier.\n";
