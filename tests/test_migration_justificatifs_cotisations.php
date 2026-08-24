<?php

define('_ECRIRE_INC_VERSION', 1);
$GLOBALS['liens_justificatifs'] = array(
	array('id_document' => 1, 'id_objet' => 20, 'objet' => 'compte', 'vu' => 'non'),
	array('id_document' => 2, 'id_objet' => 20, 'objet' => 'compte', 'vu' => 'oui'),
	array('id_document' => 2, 'id_objet' => 200, 'objet' => 'cotisation', 'vu' => 'oui'),
);

function sql_allfetsel($champs, $table, $where = '', $group = '', $order = '') {
	if ($table === 'spip_asso_cotisations') {
		return array(array('id_cotisation' => 200, 'id_compte' => 20));
	}
	if ($table === 'spip_documents_liens') {
		return array_values(array_filter($GLOBALS['liens_justificatifs'], function ($lien) use ($where) {
			return $lien['objet'] === 'compte' && $lien['id_objet'] === 20;
		}));
	}
	return array();
}
function sql_countsel($table, $where) {
	preg_match('/id_document=(\d+)/', $where, $document);
	$id_document = (int) ($document[1] ?? 0);
	return count(array_filter($GLOBALS['liens_justificatifs'], function ($lien) use ($id_document) {
		return $lien['objet'] === 'cotisation' && $lien['id_objet'] === 200 && $lien['id_document'] === $id_document;
	}));
}
function sql_updateq($table, $donnees, $where) {
	preg_match('/id_document=(\d+)/', $where, $document);
	foreach ($GLOBALS['liens_justificatifs'] as &$lien) {
		if ($lien['objet'] === 'compte' && $lien['id_objet'] === 20 && $lien['id_document'] === (int) $document[1]) {
			$lien = array_merge($lien, $donnees);
		}
	}
	unset($lien);
	return 1;
}
function sql_delete($table, $where) {
	preg_match('/id_document=(\d+)/', $where, $document);
	$avant = count($GLOBALS['liens_justificatifs']);
	$GLOBALS['liens_justificatifs'] = array_values(array_filter(
		$GLOBALS['liens_justificatifs'],
		fn($lien) => !($lien['objet'] === 'compte' && $lien['id_objet'] === 20 && $lien['id_document'] === (int) $document[1])
	));
	return $avant - count($GLOBALS['liens_justificatifs']);
}

require dirname(__DIR__) . '/plugins/association-adhesions/inc/association_adhesions_migration_justificatifs.php';
$premier = association_adhesions_migrer_justificatifs_cotisations();
$second = association_adhesions_migrer_justificatifs_cotisations();
$erreurs = array();
if ($premier !== 2 || $second !== 0) $erreurs[] = 'La migration doit etre complete puis idempotente.';
if (count($GLOBALS['liens_justificatifs']) !== 2) $erreurs[] = 'Un doublon de lien documentaire subsiste.';
foreach ($GLOBALS['liens_justificatifs'] as $lien) {
	if ($lien['objet'] !== 'cotisation' || $lien['id_objet'] !== 200) {
		$erreurs[] = 'Un justificatif reste lie au compte historique.';
	}
}

$racine = dirname(__DIR__) . '/plugins/association-adhesions';
$api = file_get_contents($racine . '/inc/api_cotisations.php');
$inclure = file_get_contents($racine . '/prive/inclure/justificatifs_cotisation.html');
$ligne = file_get_contents($racine . '/prive/objets/liste/item_cotisation_adherent.html');
if (strpos($api, "'objet' => 'cotisation'") === false) $erreurs[] = 'Les nouveaux uploads ne ciblent pas la cotisation.';
if (strpos($inclure, '{objet=cotisation}') === false || strpos($ligne, '{objet=cotisation}') === false) {
	$erreurs[] = 'Les squelettes ne lisent pas le lien documentaire canonique.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", array_unique($erreurs)) . "\n");
	exit(1);
}
echo "OK: migration idempotente des justificatifs vers les cotisations.\n";
