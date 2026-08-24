<?php

$racine = dirname(__DIR__);
$formulaire = file_get_contents($racine . '/plugins/association-adhesions/formulaires/editer_asso_cotisation.php');
$api = file_get_contents($racine . '/plugins/association-adhesions/inc/api_cotisations.php');
$comptabilite = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_comptabilite.php');
$stockage = file_get_contents($racine . '/plugins/association-adhesions/inc/cotisations_stockage.php');

$erreurs = array();
foreach (array('date_operation', 'montant', 'date_fin_validite', 'statut_cotisation', 'justification') as $champ) {
	if (strpos($formulaire, "'nom' => '$champ'") === false) {
		$erreurs[] = "Le vrai formulaire BO ne propose pas le champ $champ.";
	}
}
if (strpos($api, "'nom' => 'id_categorie'") === false) {
	$erreurs[] = 'Le formulaire BO ne propose pas la catégorie métier commune.';
}

if (strpos($api, "\$origine === 'prive' && array_key_exists('montant', \$params)") === false) {
	$erreurs[] = 'Le montant saisi en BO n’est pas prioritaire sur le tarif de catégorie.';
}
if (strpos($api, "\$params['date_operation']") === false
	|| strpos($api, "\$params['date_fin_validite']") === false) {
	$erreurs[] = 'L’API ne reçoit pas les dates du formulaire BO.';
}
if (strpos($comptabilite, "'date_fin_validite' => \$date_fin_validite") === false
	|| strpos($stockage, "\$valeurs['date_fin_validite']") === false) {
	$erreurs[] = 'La validité propre à la cotisation n’est pas conservée dans la table métier.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: le formulaire BO conserve date, montant, validité et données métier.\n";
