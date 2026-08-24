<?php

$racine = dirname(__DIR__);
$socle = file_get_contents($racine . '/genie/association_maintenance_bdd.php');
$evenements = file_get_contents($racine . '/plugins/association-evenements/inc/association_evenements_maintenance.php');
$communication = file_get_contents($racine . '/plugins/association-communication/inc/association_communication_maintenance.php');
$compta = file_get_contents($racine . '/plugins/association-compta/inc/association_compta_maintenance.php');
$paiements = file_get_contents($racine . '/plugins/association-paiements/inc/association_paiements_maintenance.php');
$paiements_api = file_get_contents($racine . '/plugins/association-paiements/inc/association_paiements_transactions.php');
$adhesions = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_maintenance.php');
$adhesions_cotisations = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_maintenance_cotisations.php');
$pipelines_evenements = file_get_contents($racine . '/plugins/association-evenements/association_evenements_pipelines.php');
$pipelines_communication = file_get_contents($racine . '/plugins/association-communication/association_communication_pipelines.php');
$pipelines_compta = file_get_contents($racine . '/plugins/association-compta/association_compta_pipelines.php');
$pipelines_paiements = file_get_contents($racine . '/plugins/association-paiements/association_paiements_pipelines.php');
$pipelines_adhesions = file_get_contents($racine . '/plugins/association-adhesions/association_adhesions_pipelines.php');

foreach (array('asso_recuperer_auteurs_inactifs', 'asso_separer_auteurs_par_encaissements', 'asso_supprimer_auteurs', 'asso_anonymiser_auteurs') as $fonction) {
	if (strpos($adhesions, 'function ' . $fonction . '(') === false || strpos($socle, 'function ' . $fonction . '(') !== false) {
		fwrite(STDERR, "La maintenance des auteurs n'appartient pas entièrement à Adhésions: {$fonction}.\n");
		exit(1);
	}
}
if (strpos($pipelines_adhesions, 'function association_adhesions_association_maintenance_bdd_preparer(') === false
	|| strpos($socle, "pipeline('association_maintenance_bdd_preparer'") === false) {
	fwrite(STDERR, "La préparation de maintenance Adhésions n'est pas branchée.\n");
	exit(1);
}

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
if (strpos($evenements, 'spip_transactions') !== false
	|| strpos($evenements, 'association_paiements_transactions_supprimer_non_encaissees(') === false) {
	fwrite(STDERR, "La maintenance Evenements contourne encore la facade Paiements.\n");
	exit(1);
}
if (strpos($paiements_api, 'function association_paiements_transactions_supprimer_non_encaissees(') === false) {
	fwrite(STDERR, "Paiements ne fournit pas la suppression protegee en lot.\n");
	exit(1);
}
if (strpos($adhesions_cotisations, 'spip_transactions') !== false
	|| strpos($adhesions_cotisations, 'association_paiements_transactions_supprimer_non_encaissees(') === false) {
	fwrite(STDERR, "La maintenance Cotisations contourne encore la facade Paiements.\n");
	exit(1);
}

if (strpos($socle, "include_spip('inc/association_evenements_maintenance');") !== false
	|| strpos($pipelines_evenements, 'function association_evenements_association_maintenance_bdd_executer(') === false) {
	fwrite(STDERR, "La maintenance Événements n'est pas fournie par son pipeline.\n");
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
if (strpos($socle, "include_spip('inc/association_communication_maintenance');") !== false
	|| strpos($pipelines_communication, 'function association_communication_association_maintenance_bdd_executer(') === false) {
	fwrite(STDERR, "La maintenance Communication n'est pas fournie par son pipeline.\n");
	exit(1);
}

$proprietaires = array(
	'Comptabilité' => array($compta, array(
		'asso_supprimer_comptes_auteurs',
	)),
	'Adhésions cotisations' => array($adhesions_cotisations, array(
		'association_adhesions_supprimer_cotisations_orphelines',
		'association_adhesions_supprimer_cotisations_non_encaissees_anciennes',
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
if (strpos($socle, "include_spip('inc/association_compta_maintenance');") !== false
	|| strpos($socle, "include_spip('inc/association_paiements_maintenance');") !== false
	|| strpos($pipelines_adhesions, 'function association_adhesions_association_maintenance_bdd_executer(') === false
	|| strpos($pipelines_paiements, 'function association_paiements_association_maintenance_bdd_executer(') === false) {
	fwrite(STDERR, "Adhésions ou Paiements ne fournit pas sa maintenance par pipeline.\n");
	exit(1);
}

if (strpos($socle, "pipeline('association_maintenance_bdd_executer'") === false) {
	fwrite(STDERR, "Le cron transversal n'appelle pas le pipeline de maintenance métier.\n");
	exit(1);
}

if (strpos($socle, "pipeline('association_maintenance_bdd_configurer'") === false) {
	fwrite(STDERR, "Le cron transversal n'appelle pas le pipeline de configuration métier.\n");
	exit(1);
}
foreach (array(
	'adhesions' => $pipelines_adhesions,
	'evenements' => $pipelines_evenements,
	'paiements' => $pipelines_paiements,
	'communication' => $pipelines_communication,
) as $module => $source_pipeline) {
	if (strpos($source_pipeline, 'function association_' . $module . '_association_maintenance_bdd_configurer(') === false) {
		fwrite(STDERR, "Le module {$module} ne fournit pas sa configuration de maintenance.\n");
		exit(1);
	}
}
foreach (array(
	'meta_cfg_maintenance_jours_inactivite',
	'meta_cfg_maintenance_jours_inscriptions_attente',
	'meta_cfg_maintenance_mois_non_encaisse',
	'meta_cfg_maintenance_supprimer_auteurs_sans_paiements',
	'meta_cfg_maintenance_supprimer_transactions_orphelines',
	'meta_cfg_maintenance_supprimer_urls_obsoletes',
) as $reglage_metier) {
	if (strpos($socle, $reglage_metier) !== false) {
		fwrite(STDERR, "Le cron transversal connaît encore {$reglage_metier}.\n");
		exit(1);
	}
}

foreach (array('association_maintenance_auteurs_encaisses', 'association_maintenance_supprimer_donnees_auteurs') as $pipeline) {
	if (strpos($adhesions, "pipeline('{$pipeline}'") === false) {
		fwrite(STDERR, "Orchestration SPIP absente pour {$pipeline}.\n");
		exit(1);
	}
}
if (preg_match("/sql_(?:select|fetsel|delete|updateq|countsel)\\([^;]*spip_(?:asso_|transactions|mailsub|urls)/s", $socle)) {
	fwrite(STDERR, "Le cron transversal exécute encore une requête SQL métier.\n");
	exit(1);
}

echo "OK: chaque maintenance métier extraite appartient à son plugin.\n";
