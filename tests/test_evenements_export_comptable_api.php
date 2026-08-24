<?php

$source = file_get_contents(dirname(__DIR__) . '/plugins/association-evenements/export_comptes_evenement.csv_fonctions.php');
if (strpos($source, 'spip_asso_comptes') !== false
	|| strpos($source, 'association_compta_ecritures_lister(') === false) {
	fwrite(STDERR, "Les totaux de l’export Événements contournent l’API Comptabilité.\n");
	exit(1);
}
echo "OK: les totaux CSV Événements utilisent l’API Comptabilité.\n";
