<?php

$racine = dirname(__DIR__);
$erreurs = array();
$don_action = file_get_contents($racine . '/plugins/association-dons/action/editer_asso_dons.php');
$don_formulaire = file_get_contents($racine . '/plugins/association-dons/formulaires/editer_asso_dons.php');
$vente_action = file_get_contents($racine . '/plugins/association-ventes/action/editer_asso_ventes.php');
$vente_formulaire = file_get_contents($racine . '/plugins/association-ventes/formulaires/editer_asso_ventes.php');

if (substr_count($don_action, 'ajouter_destinations(') !== 2
	|| strpos($don_action, "include_spip('inc/destinations')") === false) {
	$erreurs[] = 'Dons doit enregistrer la ventilation après création et modification de son écriture.';
}
if (strpos($don_formulaire, 'verifier_destination_comptable($argent') === false) {
	$erreurs[] = 'Dons doit valider la ventilation avant son traitement.';
}
if (substr_count($vente_action, 'ajouter_destinations(') !== 2
	|| strpos($vente_action, "include_spip('inc/destinations')") === false) {
	$erreurs[] = 'Ventes doit enregistrer la ventilation après création et modification de son écriture principale.';
}
foreach (array($vente_action, $vente_formulaire) as $source) {
	if (strpos($source, '$montant_destination = $recette') === false
		|| strpos($source, 'pc_frais_envoi\'] ? $frais_envoi : 0') === false) {
		$erreurs[] = 'Ventes doit inclure les frais uniquement lorsqu ils partagent l imputation principale.';
	}
}
if (strpos($vente_formulaire, 'verifier_destination_comptable($montant_destination, \'prix_vente\', $erreurs)') === false) {
	$erreurs[] = 'Ventes doit valider le total ventilé avant traitement.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK - cycles de destinations Dons et Ventes complets\n";
