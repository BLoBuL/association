<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

$GLOBALS['association_test_config'] = array();
$GLOBALS['association_test_write_failures'] = array();
$GLOBALS['association_test_delete_failures'] = array();
$GLOBALS['association_test_logs'] = array();

define('_LOG_INFO', 1);
define('_LOG_ERREUR', 2);

function include_spip($path) {
	return true;
}

function _T($key, $args = array(), $default = '') {
	return $default !== '' ? $default : $key;
}

function generer_url_ecrire($exec, $args = '') {
	return 'ecrire/?exec=' . $exec . ($args !== '' ? '&' . $args : '');
}

function lire_config($path, $default = null) {
	return array_key_exists($path, $GLOBALS['association_test_config']) ? $GLOBALS['association_test_config'][$path] : $default;
}

function ecrire_config($path, $value) {
	if (!empty($GLOBALS['association_test_write_failures'][$path])) {
		$GLOBALS['association_test_write_failures'][$path]--;
		return false;
	}
	$GLOBALS['association_test_config'][$path] = $value;
	return true;
}

function effacer_config($path) {
	if (!empty($GLOBALS['association_test_delete_failures'][$path])) {
		$GLOBALS['association_test_delete_failures'][$path]--;
		return false;
	}
	unset($GLOBALS['association_test_config'][$path]);
	return true;
}

function spip_log($message, $channel) {
	$GLOBALS['association_test_logs'][] = array($message, $channel);
}

include_once PLUGIN_ROOT . '/inc/association_log.php';
include_once PLUGIN_ROOT . '/inc/association_config_cli_registre.php';
include_once PLUGIN_ROOT . '/inc/association_config_cli.php';
include_once PLUGIN_ROOT . '/association_autoriser.php';

function association_config_cli_test_assert($condition, $message) {
	if (!$condition) {
		echo 'ECHEC: ' . $message . "\n";
		exit(1);
	}
	echo 'OK: ' . $message . "\n";
}

function association_config_cli_test_snapshot_state($snapshot) {
	$state = array();
	foreach ($snapshot['options'] as $nom => $option) {
		$state[$nom] = array('exists' => $option['exists'], 'value' => $option['exists'] ? $option['value'] : null);
	}
	return $state;
}

$registre = association_config_cli_registre();

// Audit independant : toutes les saisies persistantes du formulaire central
// doivent avoir exactement un chemin physique dans le registre CLI.
function verifier_categorie_adherent_entreprise() { return true; }
function preparer_liste_zones() { return array(); }
function preparer_liste_mailsubscribinglists() { return array(); }
function preparer_liste_pages_uniques() { return array(); }
function preparer_choix_mode_paiement() { return array(); }
function preparer_liste_asso_destination_comptable() { return array(); }
function preparer_liste_asso_plan_classe() { return array(); }
function preparer_liste_asso_plan_compte() { return array(); }
function preparer_liste_champs_filtres() { return array(); }
function saisies_tableau2chaine($valeur) { return $valeur; }
function identifier_tresorier() { return 0; }
function test_plugin_actif($plugin) { return $plugin === 'gis'; }
function verifier_site_fiafe() { return true; }

function association_config_cli_test_collecter_saisies($saisies, &$noms) {
	foreach ($saisies as $saisie) {
		$type = isset($saisie['saisie']) ? $saisie['saisie'] : '';
		$options = isset($saisie['options']) ? $saisie['options'] : array();
		$nom = isset($options['nom']) ? $options['nom'] : '';
		if ($nom !== '' && $type !== 'fieldset' && $type !== 'explication' && $nom !== 'exec_maintenance_dry_run') {
			$noms[$nom] = true;
		}
		if (isset($saisie['saisies']) && is_array($saisie['saisies'])) {
			association_config_cli_test_collecter_saisies($saisie['saisies'], $noms);
		}
	}
}

$GLOBALS['visiteur_session'] = array('statut' => '0minirezo', 'webmestre' => 'oui');
include_once PLUGIN_ROOT . '/formulaires/configurer_association.php';
$saisies_persistantes = array();
$saisies_par_segment = array();
foreach (array('info', 'adhesion', 'entreprise', 'evenement', 'evenement_defaut', 'mode_paiement', 'segments', 'affichage_public', 'affichage_prive', 'modules', 'comptabilite', 'maintenance_bdd') as $segment) {
	$saisies_segment = array();
	association_config_cli_test_collecter_saisies(formulaires_configurer_association_saisies_dist($segment), $saisies_segment);
	$saisies_par_segment[$segment] = $saisies_segment;
	$saisies_persistantes += $saisies_segment;
}
association_config_cli_test_assert(
	empty($saisies_par_segment['adhesion']['pages_modalite_evenement'])
		&& isset($saisies_par_segment['evenement']['pages_modalite_evenement']),
	'les modalites evenement sont exposees uniquement dans l onglet evenement'
);
$chemins_formulaire = array();
foreach (array_keys($saisies_persistantes) as $nom) {
	$chemins_formulaire[] = 'association_metas/' . $nom;
}
$chemins_registre_association = array();
foreach ($registre as $definition) {
	if (isset($definition['path']) && strpos($definition['path'], 'association_metas/') === 0) {
		$chemins_registre_association[] = $definition['path'];
	}
}
sort($chemins_formulaire);
sort($chemins_registre_association);
association_config_cli_test_assert(count($chemins_formulaire) === 122, 'l inventaire du formulaire contient 122 configurations persistantes uniques');
association_config_cli_test_assert($chemins_registre_association === $chemins_formulaire, 'le registre couvre exactement toutes les configurations persistantes du formulaire');
$options_recette = array(
	'evenement.inscription', 'evenement.selection_famille', 'evenement.informations_supplementaires',
	'evenement.accompagnants', 'evenement.invites', 'evenement.limite_accompagnants',
	'evenement.type_inscrits', 'evenement.validation', 'evenement.quota',
	'evenement.liste_attente', 'evenement.validation_liste_attente', 'evenement.limite_liste_attente',
);
association_config_cli_test_assert(isset($registre['debug']), 'le commutateur debug global est expose');
association_config_cli_test_assert(isset($registre['debug.inscriptions']), 'les categories de debug sont exposees par liste blanche');
foreach ($options_recette as $option) {
	association_config_cli_test_assert(isset($registre[$option]), 'la liste blanche expose ' . $option);
}
echo 'INFO: registre=' . count($registre) . " options\n";
association_config_cli_test_assert(count($registre) === 135, 'la liste blanche couvre l ensemble de la configuration Association');

$chemins = array();
foreach ($registre as $option => $definition) {
	if (empty($definition['path'])) {
		continue;
	}
	association_config_cli_test_assert(!isset($chemins[$definition['path']]), 'le chemin physique est unique pour ' . $option);
	$chemins[$definition['path']] = $option;
}

// Chaque type et chaque option physique passent reellement par le cycle
// normalisation -> ecriture -> relecture effective.
foreach ($registre as $option => $definition) {
	if (empty($definition['path'])) {
		continue;
	}
	$type = $definition['type'];
	if ($type === 'boolean') {
		$valeur_test = 'on';
	} elseif ($type === 'enum') {
		$valeur_test = end($definition['allowed']);
		reset($definition['allowed']);
	} elseif ($type === 'integer') {
		$valeur_test = (string) $definition['min'];
	} elseif ($type === 'decimal') {
		$valeur_test = (string) $definition['min'];
	} elseif ($type === 'day_month') {
		$valeur_test = '01/01';
	} elseif ($type === 'email_list') {
		$valeur_test = 'test@example.test';
	} elseif ($type === 'list') {
		$element = !empty($definition['allowed']) ? reset($definition['allowed']) : 'test';
		$valeur_test = json_encode(array($element));
	} else {
		$valeur_test = 'test';
	}
	$resultat_cycle = association_config_cli_ecrire($option, $valeur_test);
	association_config_cli_test_assert(!empty($resultat_cycle['ok']) && !empty($resultat_cycle['verified']), 'cycle ecriture et relecture valide pour ' . $option);
}
$GLOBALS['association_test_config'] = array();

foreach (array(
	'info.nom', 'adhesion.cotisations_multidevises', 'adhesion.validite', 'entreprise.validite', 'evenement.modification_inscription',
	'paiement.modes_adhesion', 'affichage.segments', 'affichage.public_filtres_annuaire',
	'affichage.prive_colonnes_adherents', 'modules.gis_email', 'modules.reseau_fiafe', 'comptabilite.debut_exercice',
	'maintenance.active',
) as $option) {
	association_config_cli_test_assert(isset($registre[$option]), 'le domaine complet expose ' . $option);
}

$lecture = association_config_cli_lire();
association_config_cli_test_assert($lecture['ok'] && $lecture['options']['debug'] === 'off', 'le debug est desactive par defaut');

$ecriture = association_config_cli_ecrire('debug', 'on');
association_config_cli_test_assert($ecriture['ok'] && $ecriture['value'] === 'on' && $ecriture['verified'], 'le debug global peut etre active et verifie');
association_config_cli_test_assert(count($ecriture['affected_options']) === count(association_log_categories_defaut()), 'le debug global pilote toutes les categories');
foreach (array_keys(association_log_categories_defaut()) as $categorie) {
	association_config_cli_test_assert(lire_config('association/debug/categories/' . $categorie) === 'on', 'la categorie ' . $categorie . ' est persistee');
}

$ecriture = association_config_cli_ecrire('debug.inscriptions', 'off');
association_config_cli_test_assert($ecriture['ok'] && $ecriture['previous'] === 'on' && $ecriture['value'] === 'off', 'une categorie peut etre desactivee individuellement');
association_config_cli_test_assert(association_config_cli_lire('debug')['options']['debug'] === 'partial', 'le debug global indique un etat partiel');

$GLOBALS['association_test_logs'] = array();
association_debug_log('test persistant');
association_config_cli_test_assert(count($GLOBALS['association_test_logs']) === 1, 'la categorie autorisations pilote aussi le helper de debug historique');

$ecritures_recette = array(
	'evenement.inscription' => 'non',
	'evenement.selection_famille' => 'membre_famille',
	'evenement.informations_supplementaires' => 'non',
	'evenement.accompagnants' => 'non',
	'evenement.invites' => 'oui',
	'evenement.limite_accompagnants' => '12',
	'evenement.type_inscrits' => 'public',
	'evenement.validation' => 'oui',
	'evenement.quota' => 'strict',
	'evenement.liste_attente' => 'non',
	'evenement.validation_liste_attente' => 'non',
	'evenement.limite_liste_attente' => '25',
);
foreach ($ecritures_recette as $option => $valeur) {
	$resultat = association_config_cli_ecrire($option, $valeur);
	association_config_cli_test_assert($resultat['ok'] && $resultat['value'] === $valeur && $resultat['verified'], $option . ' est ecrite puis relue');
}

$ecritures_types = array(
	'info.nom' => 'Association test',
	'info.email' => 'admin@example.test; tresorier@example.test',
	'adhesion.date_scolaire_suivante' => '15/05',
	'adhesion.age_limite_enfants' => '18',
	'adhesion.donation_defaut' => '12,50',
	'adhesion.echeances_notification' => '["60","15"]',
	'comptabilite.active' => 'on',
	'maintenance.lot' => '500',
);
foreach ($ecritures_types as $option => $valeur) {
	$resultat = association_config_cli_ecrire($option, $valeur);
	association_config_cli_test_assert($resultat['ok'] && $resultat['verified'], 'le type de ' . $option . ' est valide, persiste et relu');
}
association_config_cli_test_assert(
	association_config_cli_lire('adhesion.echeances_notification')['options']['adhesion.echeances_notification'] === array('60', '15'),
	'les listes JSON sont relues sous forme de tableau'
);

$avant = $GLOBALS['association_test_config'];
$inconnue = association_config_cli_ecrire('email.smtp_password', 'on');
association_config_cli_test_assert(!$inconnue['ok'] && $inconnue['reason'] === 'unknown_option', 'une option hors liste blanche est refusee');
association_config_cli_test_assert($GLOBALS['association_test_config'] === $avant, 'une option inconnue ne modifie pas la configuration');
association_config_cli_test_assert(association_config_cli_code_sortie($inconnue) === 2, 'une option inconnue retourne le code 2');

foreach (array(
	array('debug', 'peut-etre'),
	array('debug.inscriptions', 'actif'),
	array('evenement.type_inscrits', 'ami'),
	array('evenement.limite_accompagnants', '-1'),
	array('evenement.limite_liste_attente', '100001'),
	array('info.email', 'adresse-invalide'),
	array('adhesion.date_scolaire_suivante', '31/13'),
	array('adhesion.echeances_notification', '["60","999"]'),
	array('modules.gis_actions', '["action_inconnue"]'),
	array('comptabilite.pc_dons', '70 10'),
	array('affichage.segments', '{"segment":"1"}'),
	array('maintenance.lot', '0'),
) as $cas) {
	$invalide = association_config_cli_ecrire($cas[0], $cas[1]);
	association_config_cli_test_assert(!$invalide['ok'] && $invalide['reason'] === 'invalid_value', 'la valeur invalide est refusee pour ' . $cas[0]);
	association_config_cli_test_assert($GLOBALS['association_test_config'] === $avant, 'le refus ne modifie rien pour ' . $cas[0]);
}

// L'instantane conserve a la fois les valeurs presentes et les metas absentes.
effacer_config('association/debug/categories/email');
$capture = association_config_cli_capturer();
association_config_cli_test_assert($capture['ok'] && $capture['snapshot']['format'] === 'association-config-snapshot-v2', 'un instantane JSON versionne est produit');
association_config_cli_test_assert($capture['snapshot']['registry_options'] === 134, 'l instantane couvre les 134 options physiques du registre');
association_config_cli_test_assert($capture['snapshot']['options']['debug.email']['exists'] === false, 'l absence d une meta est conservee');
$etat_capture = association_config_cli_test_snapshot_state($capture['snapshot']);

association_config_cli_ecrire('debug.email', 'on');
association_config_cli_ecrire('debug.inscriptions', 'on');
$restauration = association_config_cli_restaurer($capture['snapshot']);
association_config_cli_test_assert($restauration['ok'] && $restauration['verified'], 'l instantane complet est restaure et verifie');
association_config_cli_test_assert(association_config_cli_capturer_options(array_keys($etat_capture), $registre) === $etat_capture, 'la restauration retrouve exactement l etat initial');
association_config_cli_test_assert(!array_key_exists('association/debug/categories/email', $GLOBALS['association_test_config']), 'une meta initialement absente est effacee');

// Les instantanes v1 crees avant l extension restent restaurables sur leur
// sous-ensemble connu, sans toucher aux nouvelles options.
$snapshot_v1 = array(
	'format' => 'association-config-snapshot-v1',
	'plugin' => 'association',
	'options' => array(),
);
foreach (association_config_cli_snapshot_v1_options($registre) as $option_v1) {
	$snapshot_v1['options'][$option_v1] = $capture['snapshot']['options'][$option_v1];
}
association_config_cli_ecrire('debug.email', 'on');
association_config_cli_ecrire('evenement.inscription', 'oui');
$restauration_v1 = association_config_cli_restaurer($snapshot_v1);
association_config_cli_test_assert($restauration_v1['ok'] && $restauration_v1['restored'] === 24, 'un instantane v1 complet reste restaurable sans elargir son perimetre');

$snapshot_v1_partiel = $snapshot_v1;
unset($snapshot_v1_partiel['options']['debug.email']);
$refus_v1_partiel = association_config_cli_restaurer($snapshot_v1_partiel);
association_config_cli_test_assert(!$refus_v1_partiel['ok'] && $refus_v1_partiel['reason'] === 'snapshot_options_mismatch', 'un instantane v1 partiel est refuse');

// Une ecriture non persistante declenche le rollback exact de l'option.
$path_validation = $registre['debug.inscriptions']['path'];
$etat_avant_echec = $GLOBALS['association_test_config'];
$GLOBALS['association_test_write_failures'][$path_validation] = 1;
$echec = association_config_cli_ecrire('debug.inscriptions', 'on');
association_config_cli_test_assert(!$echec['ok'] && $echec['reason'] === 'write_failed' && $echec['rollback_restored'], 'un echec d ecriture declenche un rollback verifie');
association_config_cli_test_assert($GLOBALS['association_test_config'] === $etat_avant_echec, 'le rollback d ecriture est exact');

// Un echec de restauration restaure l'etat present avant la tentative.
association_config_cli_ecrire('debug.inscriptions', 'on');
$etat_avant_restore_echec = $GLOBALS['association_test_config'];
$GLOBALS['association_test_write_failures'][$path_validation] = 1;
$echec_restore = association_config_cli_restaurer($capture['snapshot']);
association_config_cli_test_assert(!$echec_restore['ok'] && $echec_restore['reason'] === 'restore_failed' && $echec_restore['rollback_restored'], 'un echec de restauration declenche son propre rollback');
association_config_cli_test_assert($GLOBALS['association_test_config'] === $etat_avant_restore_echec, 'le rollback de restauration retrouve l etat precedent');

$snapshot_inconnu = $capture['snapshot'];
$snapshot_inconnu['options']['email.smtp_password'] = array('exists' => true, 'value' => 'secret');
$refus_snapshot = association_config_cli_restaurer($snapshot_inconnu);
association_config_cli_test_assert(!$refus_snapshot['ok'] && $refus_snapshot['reason'] === 'snapshot_options_mismatch', 'un instantane avec option inconnue est refuse');
association_config_cli_test_assert($GLOBALS['association_test_config'] === $etat_avant_restore_echec, 'un instantane invalide ne modifie rien');

$lecture_inconnue = association_config_cli_lire('debug.inconnue');
association_config_cli_test_assert(!$lecture_inconnue['ok'] && association_config_cli_code_sortie($lecture_inconnue) === 2, 'une lecture inconnue est refusee avec le code 2');

foreach (array('AssociationConfigLire.php', 'AssociationConfigEcrire.php', 'AssociationConfigRestaurer.php') as $fichier) {
	association_config_cli_test_assert(is_file(PLUGIN_ROOT . '/spip-cli/' . $fichier), 'la commande ' . $fichier . ' est decouvrable dans spip-cli/');
}

echo "Tous les tests de configuration SPIP CLI ont reussi.\n";
