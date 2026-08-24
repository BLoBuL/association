<?php

$racine = dirname(__DIR__);
foreach (array('action/ajouter_activites.php', 'action/modifier_activites.php') as $fichier) {
	$source = file_get_contents($racine . '/plugins/association-evenements/' . $fichier);
	if (strpos($source, 'spip_transactions') !== false
		|| strpos($source, 'association_evenements_transaction_modifier(') === false) {
		fwrite(STDERR, "L’édition BO contourne Paiements dans {$fichier}.\n");
		exit(1);
	}
}
$modifier = file_get_contents($racine . '/plugins/association-evenements/action/modifier_activites.php');
if (strpos($modifier, 'association_evenements_transaction_lire(') === false) {
	fwrite(STDERR, "La modification BO ne charge pas sa transaction par Paiements.\n");
	exit(1);
}
echo "OK: l’édition BO Événements utilise la façade Paiements.\n";
