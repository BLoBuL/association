<?php

define('_ECRIRE_INC_VERSION', 1);
$racine = dirname(__DIR__);
$GLOBALS['maintenance_test_request'] = array();

function include_spip($fichier) { return true; }
function _T($cle, $args = array()) { return $cle; }
function _request($nom) { return $GLOBALS['maintenance_test_request'][$nom] ?? null; }
function lire_config($nom, $defaut = null) { return $defaut; }
function saisies_tableau2chaine($valeur) { return $valeur; }
function sql_allfetsel() { return array(); }
function association_log() { return true; }

function pipeline($nom, $flux) {
	$fournisseurs = array(
		'association_maintenance_bdd_configurer' => array(
			'association_adhesions_association_maintenance_bdd_configurer',
			'association_evenements_association_maintenance_bdd_configurer',
			'association_compta_association_maintenance_bdd_configurer',
			'association_paiements_association_maintenance_bdd_configurer',
			'association_communication_association_maintenance_bdd_configurer',
		),
		'association_maintenance_bdd_verifier_configuration' => array(
			'association_adhesions_association_maintenance_bdd_verifier_configuration',
			'association_evenements_association_maintenance_bdd_verifier_configuration',
			'association_compta_association_maintenance_bdd_verifier_configuration',
		),
	);
	foreach ($fournisseurs[$nom] ?? array() as $fonction) {
		$flux = $fonction($flux);
	}
	return $flux['data'];
}

require_once $racine . '/formulaires/inc/configurer_association.php';
require_once $racine . '/genie/association_maintenance_bdd.php';
require_once $racine . '/plugins/association-adhesions/association_adhesions_pipelines.php';
require_once $racine . '/plugins/association-evenements/association_evenements_pipelines.php';
require_once $racine . '/plugins/association-compta/association_compta_pipelines.php';
require_once $racine . '/plugins/association-paiements/association_paiements_pipelines.php';
require_once $racine . '/plugins/association-communication/association_communication_pipelines.php';

$assert = function ($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, "ECHEC: {$message}\n");
		exit(1);
	}
};

$source = array(
	'meta_cfg_maintenance_bdd_enable' => 'non',
	'meta_cfg_maintenance_dry_run' => 'non',
	'meta_cfg_maintenance_lot' => '42',
	'meta_cfg_maintenance_jours_inactivite' => '111',
	'meta_cfg_maintenance_jours_inscriptions_attente' => '22',
	'meta_cfg_maintenance_mois_non_encaisse' => '7',
	'meta_cfg_maintenance_supprimer_auteurs_sans_paiements' => 'non',
	'meta_cfg_maintenance_anonymiser_auteurs_avec_paiements' => 'oui',
	'meta_cfg_maintenance_supprimer_inscriptions_non_validees' => 'non',
	'meta_cfg_maintenance_anonymiser_inscriptions_inactifs' => 'oui',
	'meta_cfg_maintenance_supprimer_participations_orphelines' => 'non',
	'meta_cfg_maintenance_supprimer_participations_obsoletes' => 'oui',
	'meta_cfg_maintenance_supprimer_cotisations_orphelines' => 'non',
	'meta_cfg_maintenance_supprimer_cotisations_non_encaissees' => 'oui',
	'meta_cfg_maintenance_supprimer_transactions_orphelines' => 'non',
	'meta_cfg_maintenance_supprimer_urls_mailsubscriber' => 'oui',
	'meta_cfg_maintenance_supprimer_urls_obsoletes' => 'non',
	'meta_cfg_maintenance_supprimer_mailsubscribers_orphelines' => 'oui',
);

$options = association_maintenance_options_depuis_source($source);
$assert($options['enabled'] === false && $options['dry_run'] === false && $options['lot'] === 42, 'options globales incorrectes');
$assert($options['jours_inactivite'] === 111, 'seuil Adhésions absent');
$assert($options['jours_inscriptions_en_attente'] === 22, 'seuil Événements absent');
$assert($options['mois_non_encaisse'] === 7, 'seuil Comptabilité absent');
$assert(count($options['actions']) === 12, 'les douze actions métier doivent être fournies');
$assert($options['actions']['supprimer_auteurs_sans_paiements'] === false, 'booléen Adhésions incorrect');
$assert($options['actions']['supprimer_participations_obsoletes'] === true, 'booléen Événements incorrect');
$assert($options['actions']['supprimer_cotisations_orphelines'] === false, 'booléen Comptabilité incorrect');
$assert($options['actions']['supprimer_transactions_orphelines'] === false, 'booléen Paiements incorrect');
$assert($options['actions']['supprimer_urls_obsoletes'] === false, 'booléen Communication incorrect');
$assert(association_maintenance_options_depuis_source($source, true)['dry_run'] === true, 'le CVT doit pouvoir forcer le dry-run');

$GLOBALS['maintenance_test_request'] = array(
	'meta_cfg_maintenance_jours_inactivite' => '0',
	'meta_cfg_maintenance_jours_inscriptions_attente' => 'abc',
	'meta_cfg_maintenance_mois_non_encaisse' => '6',
);
$erreurs = pipeline('association_maintenance_bdd_verifier_configuration', array('args' => array(), 'data' => array()));
$assert(isset($erreurs['meta_cfg_maintenance_jours_inactivite']), 'Adhésions doit refuser zéro');
$assert(isset($erreurs['meta_cfg_maintenance_jours_inscriptions_attente']), 'Événements doit refuser une valeur non numérique');
$assert(!isset($erreurs['meta_cfg_maintenance_mois_non_encaisse']), 'Comptabilité doit accepter un entier positif');

echo "OK: configuration et validation de maintenance distribuées entre les modules.\n";
