<?php

$racine = dirname(__DIR__);
$socle = file_get_contents($racine . '/genie/association_maintenance_bdd.php');
$evenements = file_get_contents($racine . '/plugins/association-evenements/inc/association_evenements_maintenance.php');

$fonctions_evenements = array(
	'asso_trouver_inscriptions_non_validees_anciennes',
	'asso_supprimer_inscriptions_par_ids',
	'asso_supprimer_transactions_inscriptions',
	'asso_anonymiser_inscriptions_auteurs',
	'asso_supprimer_participations_evenements_orphelines',
	'asso_supprimer_participations_evenements_obsoletes',
);

foreach ($fonctions_evenements as $fonction) {
	if (strpos($evenements, 'function ' . $fonction . '(') === false) {
		fwrite(STDERR, "Fonction événementielle absente du plugin propriétaire: {$fonction}.\n");
		exit(1);
	}
	if (strpos($socle, 'function ' . $fonction . '(') !== false) {
		fwrite(STDERR, "Fonction événementielle encore implémentée dans le socle: {$fonction}.\n");
		exit(1);
	}
}

if (strpos($socle, "include_spip('inc/association_evenements_maintenance');") === false) {
	fwrite(STDERR, "Le cron du socle ne charge pas l'API de maintenance Événements.\n");
	exit(1);
}

echo "OK: la maintenance événementielle appartient au plugin Événements.\n";
