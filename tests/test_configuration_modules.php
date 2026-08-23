<?php

$racine = dirname(__DIR__);
$socle = file_get_contents($racine . '/formulaires/inc/configurer_association.php');
$adhesions = file_get_contents($racine . '/plugins/association-adhesions/formulaires/inc/configurer_association_adhesions.php');
foreach (array('spip_asso_categories_adherents', 'spip_mailsubscribinglists') as $table) {
	if (strpos($socle, $table) !== false) {
		fwrite(STDERR, "La configuration du socle requête encore {$table}.\n");
		exit(1);
	}
}
$communication = file_get_contents($racine . '/plugins/association-communication/association_communication_pipelines.php');
if (
	strpos($adhesions, "pipeline('association_configuration_listes_diffusion'") === false
	|| strpos($communication, 'association_configuration_listes_diffusion') === false
) {
	fwrite(STDERR, "Contrat de listes de diffusion incomplet.\n");
	exit(1);
}

$formulaire_racine = file_get_contents($racine . '/formulaires/configurer_association.php');
$debut_saisies = strpos($formulaire_racine, 'function formulaires_configurer_association_saisies_dist');
$fin_saisies = strpos($formulaire_racine, 'function formulaires_configurer_association_charger_dist');
$declaration_saisies_racine = substr($formulaire_racine, $debut_saisies, $fin_saisies - $debut_saisies);
$modules_maintenance = array(
	'association-adhesions/formulaires/inc/configurer_association_adhesions.php' => array(
		'jours_inactivite',
		'supprimer_auteurs_sans_paiements',
		'anonymiser_auteurs_avec_paiements',
	),
	'association-evenements/formulaires/inc/configurer_association_evenements.php' => array(
		'jours_inscriptions_attente',
		'supprimer_inscriptions_non_validees',
		'anonymiser_inscriptions_inactifs',
		'supprimer_participations_orphelines',
		'supprimer_participations_obsoletes',
	),
	'association-compta/formulaires/inc/configurer_association_compta.php' => array(
		'mois_non_encaisse',
		'supprimer_cotisations_orphelines',
		'supprimer_cotisations_non_encaissees',
	),
	'association-paiements/formulaires/inc/configurer_association_paiements.php' => array(
		'supprimer_transactions_orphelines',
	),
	'association-communication/formulaires/inc/configurer_association_communication.php' => array(
		'supprimer_urls_mailsubscriber',
		'supprimer_urls_obsoletes',
		'supprimer_mailsubscribers_orphelines',
	),
);
foreach ($modules_maintenance as $fichier => $reglages) {
	$source = file_get_contents($racine . '/plugins/' . $fichier);
	foreach ($reglages as $reglage) {
		if (strpos($source, "association_config_maintenance_" . ($reglage === 'jours_inactivite' || $reglage === 'jours_inscriptions_attente' || $reglage === 'mois_non_encaisse' ? "input('" : "radio('") . $reglage . "'") === false) {
			fwrite(STDERR, "Réglage de maintenance absent de {$fichier}: {$reglage}.\n");
			exit(1);
		}
		if (strpos($declaration_saisies_racine, "meta_cfg_maintenance_{$reglage}") !== false) {
			fwrite(STDERR, "Le socle déclare encore le réglage métier {$reglage}.\n");
			exit(1);
		}
	}
}
echo "OK: les données de configuration métier viennent de leurs modules.\n";
