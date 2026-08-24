<?php

$racine = dirname(__DIR__);
$paiements = file_get_contents($racine . '/plugins/association-paiements/association_paiements_pipelines.php')
	. file_get_contents($racine . '/plugins/association-paiements/inc/association_paiements_maintenance.php');

foreach (array('spip_asso_comptes', 'spip_asso_activites') as $table) {
	if (strpos($paiements, $table) !== false) {
		fwrite(STDERR, "Paiements lit encore directement {$table}.\n");
		exit(1);
	}
}

foreach (array(
	'plugins/association-paiements/paquet.xml',
	'plugins/association-compta/paquet.xml',
	'plugins/association-evenements/paquet.xml',
) as $fichier) {
	$contenu = file_get_contents($racine . '/' . $fichier);
	if (strpos($contenu, 'association_paiements_transactions_references') === false) {
		fwrite(STDERR, "Pipeline de références absent de {$fichier}.\n");
		exit(1);
	}
}

echo "OK: les références métier des transactions sont distribuées.\n";
