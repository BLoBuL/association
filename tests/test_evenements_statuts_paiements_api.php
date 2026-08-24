<?php

$racine = dirname(__DIR__);
$fichiers = array(
	'inc/fonctions/eligibilite_desinscription_evenement.php',
	'inc/fonctions/eligibilite_modification_evenement.php',
	'formulaires/desinscription_evenement_public.php',
	'genie/association_expiration_auto_evenement.php',
);
foreach ($fichiers as $fichier) {
	$source = file_get_contents($racine . '/plugins/association-evenements/' . $fichier);
	if (strpos($source, 'spip_transactions') !== false
		|| strpos($source, 'association_paiements_transaction_lire(') === false) {
		fwrite(STDERR, "Accès Bank direct résiduel dans {$fichier}.\n");
		exit(1);
	}
}
$desinscription = file_get_contents($racine . '/plugins/association-evenements/formulaires/desinscription_evenement_public.php');
$cron = file_get_contents($racine . '/plugins/association-evenements/genie/association_expiration_auto_evenement.php');
if (strpos($desinscription, 'association_paiements_transaction_modifier(') === false
	|| strpos($cron, 'association_paiements_transaction_modifier(') === false) {
	fwrite(STDERR, "Les transitions de statut ne passent pas toutes par Paiements.\n");
	exit(1);
}
echo "OK: les éligibilités et expirations utilisent la façade Paiements.\n";
