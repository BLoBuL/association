<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('_ECRIRE_INC_VERSION', 1);
define('PLUGIN_ROOT', dirname(__DIR__));

$GLOBALS['association_metas'] = array('destinations' => 'on');
$GLOBALS['test_request'] = array();
$GLOBALS['test_destinations_insertions'] = array();
$GLOBALS['test_destinations_suppressions'] = array();

function include_spip($chemin) {
	return true;
}

function _request($cle) {
	return $GLOBALS['test_request'][$cle] ?? null;
}

function association_valeur_bdd_est_vraie($valeur) {
	return in_array($valeur, array('on', 'oui', '1', 1, true), true);
}

function association_recupere_montant($valeur) {
	return (float) str_replace(',', '.', (string) $valeur);
}

function sql_delete($table, $where) {
	$GLOBALS['test_destinations_suppressions'][] = compact('table', 'where');
	return true;
}

function sql_insertq($table, $valeurs) {
	$GLOBALS['test_destinations_insertions'][] = array('table' => $table, 'valeurs' => $valeurs);
	return count($GLOBALS['test_destinations_insertions']);
}

function _T($cle) {
	return $cle;
}

function test_assert($condition, $message) {
	if (!$condition) {
		echo "ECHEC: $message\n";
		exit(1);
	}
	echo "OK: $message\n";
}

include_once PLUGIN_ROOT . '/plugins/association-compta/inc/destinations.php';
include_once PLUGIN_ROOT . '/plugins/association-compta/formulaires/inc/destinations.php';

$GLOBALS['test_request'] = array(
	'id_dest' => array(1 => '7', 2 => '9'),
	'montant_dest' => array(1 => '4', 2 => '6'),
);
$erreurs = array();
verifier_destination_comptable(10, 'montant', $erreurs);
test_assert($erreurs === array(), 'une ventilation équilibrée est acceptée');

$GLOBALS['test_request']['id_dest'][2] = '7';
$erreurs = array();
verifier_destination_comptable(10, 'montant', $erreurs);
test_assert(
	isset($erreurs['destinations']) && strpos($erreurs['destinations'], 'erreur_destination_dupliquee') !== false,
	'une destination dupliquée remonte dans le tableau d erreurs par référence'
);

$GLOBALS['test_request'] = array('id_dest' => array(1 => '9'), 'montant_dest' => array(1 => ''));
$erreurs = array();
verifier_destination_comptable(25, 'montant', $erreurs);
test_assert($erreurs === array(), 'une destination unique peut reprendre le montant global');
ajouter_destinations(42, 25, 0);
$insertion = end($GLOBALS['test_destinations_insertions']);
test_assert(
	(string) $insertion['valeurs']['id_destination'] === '9'
		&& (float) $insertion['valeurs']['recette'] === 25.0,
	'une destination indexée à 1 est normalisée et enregistrée sans perte'
);

$GLOBALS['test_request'] = array('id_dest' => array(), 'montant_dest' => array());
$erreurs = array();
verifier_destination_comptable(10, 'montant', $erreurs);
test_assert(
	($erreurs['destinations'] ?? '') === 'association_compta:erreur_pas_de_destination',
	'les destinations actives exigent une destination comptable'
);

$formulaire = file_get_contents(PLUGIN_ROOT . '/plugins/association-compta/formulaires/editer_asso_comptes.php');
foreach (array(
	'update_destination_contexte_from_compte($valeurs',
	'verifier_destination_comptable((float) _request(\'montant\')',
	'ajouter_destinations((int) $id_compte',
) as $appel) {
	test_assert(strpos($formulaire, $appel) !== false, 'cycle CVT rebranché : ' . $appel);
}

foreach (array(
	'plugins/association-adhesions/association_adhesions_pipelines.php' => "'destination_defaut' => 'cotisations'",
	'plugins/association-evenements/association_evenements_pipelines.php' => "'destination_defaut' => 'activites'",
	'plugins/association-dons/association_dons_pipelines.php' => "'destination_defaut' => 'dons'",
	'plugins/association-ventes/association_ventes_pipelines.php' => "'destination_defaut' => 'ventes'",
) as $fichier => $contrat) {
	test_assert(
		strpos(file_get_contents(PLUGIN_ROOT . '/' . $fichier), $contrat) !== false,
		'la destination par défaut est déclarée par son plugin métier'
	);
}

echo "Tous les tests du cycle comptable des destinations sont passés.\n";
