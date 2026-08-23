<?php

$racine = dirname(__DIR__);
$socle = file_get_contents($racine . '/genie/association_maintenance_bdd.php');
$evenements = file_get_contents($racine . '/plugins/association-evenements/inc/association_evenements_maintenance.php');
$communication = file_get_contents($racine . '/plugins/association-communication/inc/association_communication_maintenance.php');
$compta = file_get_contents($racine . '/plugins/association-compta/inc/association_compta_maintenance.php');
$paiements = file_get_contents($racine . '/plugins/association-paiements/inc/association_paiements_maintenance.php');

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

$fonctions_communication = array(
	'asso_supprimer_mailsubscribers_pour_auteurs',
	'asso_supprimer_urls_obsoletes',
	'asso_supprimer_urls_par_type',
	'asso_supprimer_mailsubscribers_orphelines',
);
foreach ($fonctions_communication as $fonction) {
	if (strpos($communication, 'function ' . $fonction . '(') === false
		|| strpos($socle, 'function ' . $fonction . '(') !== false) {
		fwrite(STDERR, "Propriété Communication incorrecte pour {$fonction}.\n");
		exit(1);
	}
}
if (strpos($socle, "include_spip('inc/association_communication_maintenance');") === false) {
	fwrite(STDERR, "Le cron du socle ne charge pas l'API de maintenance Communication.\n");
	exit(1);
}

$proprietaires = array(
	'Comptabilité' => array($compta, array(
		'asso_supprimer_comptes_auteurs',
		'asso_supprimer_cotisations_orphelines',
		'asso_supprimer_cotisations_non_encaissees_anciennes',
	)),
	'Paiements' => array($paiements, array(
		'asso_supprimer_transactions_auteurs',
		'asso_supprimer_transactions_orphelines',
	)),
);
foreach ($proprietaires as $domaine => $definition) {
	list($source, $fonctions) = $definition;
	foreach ($fonctions as $fonction) {
		if (strpos($source, 'function ' . $fonction . '(') === false
			|| strpos($socle, 'function ' . $fonction . '(') !== false) {
			fwrite(STDERR, "Propriété {$domaine} incorrecte pour {$fonction}.\n");
			exit(1);
		}
	}
}
foreach (array('association_compta_maintenance', 'association_paiements_maintenance') as $api) {
	if (strpos($socle, "include_spip('inc/{$api}');") === false) {
		fwrite(STDERR, "API de maintenance non chargée: {$api}.\n");
		exit(1);
	}
}

foreach (array('association_maintenance_auteurs_encaisses', 'association_maintenance_supprimer_donnees_auteurs') as $pipeline) {
	if (strpos($socle, "pipeline('{$pipeline}'") === false) {
		fwrite(STDERR, "Orchestration SPIP absente pour {$pipeline}.\n");
		exit(1);
	}
}
if (preg_match("/sql_(?:select|fetsel|delete|updateq|countsel)\\([^;]*spip_(?:asso_|transactions|mailsub|urls)/s", $socle)) {
	fwrite(STDERR, "Le cron transversal exécute encore une requête SQL métier.\n");
	exit(1);
}

echo "OK: chaque maintenance métier extraite appartient à son plugin.\n";
