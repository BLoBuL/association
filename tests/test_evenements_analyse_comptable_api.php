<?php

$racine = dirname(__DIR__);
$analyse = file_get_contents($racine . '/plugins/association-evenements/prive/squelettes/contenu/analyse_compta_activites_fonctions.php');
$paiements = file_get_contents($racine . '/plugins/association-paiements/inc/association_paiements_transactions.php');
foreach (array('spip_asso_comptes', 'spip_transactions') as $table) {
	if (strpos($analyse, $table) !== false) {
		fwrite(STDERR, "L’analyse Événements lit encore directement {$table}.\n");
		exit(1);
	}
}
if (strpos($analyse, 'association_compta_ecritures_lister(') === false
	|| strpos($analyse, 'association_paiements_transactions_lire(') === false
	|| strpos($paiements, 'function association_paiements_transactions_lire(') === false) {
	fwrite(STDERR, "Les contrats Comptabilité/Paiements de l’analyse Événements sont incomplets.\n");
	exit(1);
}
echo "OK: l’analyse annuelle Événements utilise les API Comptabilité et Paiements.\n";
