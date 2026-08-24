<?php

$racine = dirname(__DIR__);
foreach (array(
	'formulaires/inc/inscription_evenement.php',
	'formulaires/inc/inscription_evenement_backend.php',
) as $fichier) {
	$source = file_get_contents($racine . '/plugins/association-evenements/' . $fichier);
	if (strpos($source, 'spip_transactions') !== false
		|| strpos($source, 'association_evenements_transaction_lire(') === false) {
		fwrite(STDERR, "Le formulaire contourne Paiements dans {$fichier}.\n");
		exit(1);
	}
}
$commun = file_get_contents($racine . '/plugins/association-evenements/formulaires/inc/inscription_evenement.php');
if (strpos($commun, 'association_evenements_transaction_modifier(') === false) {
	fwrite(STDERR, "La modification du montant ne passe pas par Paiements.\n");
	exit(1);
}
echo "OK: les formulaires Événements utilisent la façade Paiements.\n";
