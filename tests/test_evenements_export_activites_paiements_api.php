<?php

$source = file_get_contents(dirname(__DIR__) . '/plugins/association-evenements/action/exporter_activites_csv.php');
if (strpos($source, 'spip_transactions') !== false
	|| strpos($source, 'association_paiements_transactions_lire(') === false
	|| strpos($source, 'spip_asso_activites AS a INNER JOIN spip_auteurs AS b ON') === false) {
	fwrite(STDERR, "L'export des inscriptions contourne encore la facade Paiements.\n");
	exit(1);
}
echo "OK: l'export des inscriptions enrichit ses lignes via Paiements.\n";
