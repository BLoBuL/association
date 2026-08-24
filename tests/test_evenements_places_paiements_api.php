<?php

$racine = dirname(__DIR__);
foreach (array(
	'inc/fonctions/gestion_places.php',
	'inc/fonctions/activite_calculator.php',
) as $fichier) {
	$source = file_get_contents($racine . '/plugins/association-evenements/' . $fichier);
	if (strpos($source, 'spip_transactions') !== false
		|| strpos($source, 'association_evenements_transaction') === false) {
		fwrite(STDERR, "Le calcul Evenements contourne Paiements dans {$fichier}.\n");
		exit(1);
	}
}
echo "OK: les places et contextes Evenements utilisent la facade Paiements.\n";
