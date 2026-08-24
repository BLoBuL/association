<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_compta_ecriture_champs_autorises() {
	return array(
		'date', 'recette', 'depense', 'justification', 'imputation', 'journal',
		'id_auteur', 'id_objet', 'objet', 'id_transaction', 'vu',
	);
}

function association_compta_ecriture_normaliser(array $donnees) {
	$donnees = array_intersect_key($donnees, array_flip(association_compta_ecriture_champs_autorises()));
	foreach (array('recette', 'depense') as $champ) {
		if (array_key_exists($champ, $donnees)) {
			$donnees[$champ] = (float) $donnees[$champ];
		}
	}
	foreach (array('id_auteur', 'id_objet', 'id_transaction', 'vu') as $champ) {
		if (array_key_exists($champ, $donnees)) {
			$donnees[$champ] = (int) $donnees[$champ];
		}
	}
	return $donnees;
}

function association_compta_ecriture_creer(array $donnees) {
	$donnees += array(
		'date' => date('Y-m-d H:i:s'),
		'recette' => 0,
		'depense' => 0,
		'justification' => '',
		'imputation' => '',
		'journal' => '',
		'id_auteur' => 0,
		'id_objet' => 0,
		'objet' => '',
		'id_transaction' => 0,
		'vu' => 0,
	);
	return (int) sql_insertq('spip_asso_comptes', association_compta_ecriture_normaliser($donnees));
}

function association_compta_ecriture_modifier($id_compte, array $donnees) {
	$id_compte = (int) $id_compte;
	if ($id_compte <= 0) {
		return 0;
	}
	$donnees = association_compta_ecriture_normaliser($donnees);
	if ($donnees) {
		sql_updateq('spip_asso_comptes', $donnees, 'id_compte=' . $id_compte);
	}
	return $id_compte;
}

function association_compta_ecriture_supprimer($id_compte) {
	$id_compte = (int) $id_compte;
	if ($id_compte <= 0) {
		return false;
	}
	// Les ventilations appartiennent à l'écriture comptable et ne doivent pas
	// survivre à sa suppression.
	sql_delete('spip_asso_destination_op', 'id_compte=' . $id_compte);
	return (bool) sql_delete('spip_asso_comptes', 'id_compte=' . $id_compte);
}

/**
 * Liste les écritures liées à un objet métier, avec reprise facultative des
 * anciens liens portés par id_journal.
 */
function association_compta_ecritures_objet_lister($objet, $id_objet, array $options = array()) {
	$objet = trim((string) $objet);
	$id_objet = (int) $id_objet;
	if ($objet === '' || $id_objet <= 0) {
		return array();
	}
	$where = "(objet=" . sql_quote($objet) . ' AND id_objet=' . $id_objet . ')';
	if (!empty($options['legacy_id_journal'])) {
		$legacy = 'id_journal=' . $id_objet;
		if (!empty($options['legacy_justification_prefix'])) {
			$legacy .= ' AND justification LIKE ' . sql_quote((string) $options['legacy_justification_prefix'] . '%');
		}
		$where = '(' . $where . ' OR (' . $legacy . '))';
	}
	$imputations = array_values(array_unique(array_filter(array_map('strval', (array) ($options['imputations'] ?? array())), 'strlen')));
	if ($imputations) {
		$where .= ' AND ' . sql_in('imputation', $imputations);
	}
	if (array_key_exists('id_transaction', $options)) {
		$where .= ' AND id_transaction=' . (int) $options['id_transaction'];
	}
	$champs = (string) ($options['champs'] ?? '*');
	$ordre = (string) ($options['ordre'] ?? "(objet=" . sql_quote($objet) . ') DESC, id_compte DESC');
	return sql_allfetsel($champs, 'spip_asso_comptes', $where, '', $ordre) ?: array();
}

/**
 * Supprime les écritures et ventilations rattachées à un objet métier.
 */
function association_compta_ecritures_objet_supprimer($objet, $id_objet, array $options = array()) {
	$ecritures = association_compta_ecritures_objet_lister($objet, $id_objet, $options + array('champs' => 'id_compte'));
	$ok = true;
	foreach ($ecritures as $ecriture) {
		$ok = association_compta_ecriture_supprimer((int) ($ecriture['id_compte'] ?? 0)) && $ok;
	}
	return $ok;
}

/**
 * Calcule le total des recettes liées à une liste d'objets métier.
 */
function association_compta_ecritures_objets_total($objet, array $ids_objets, array $options = array()) {
	$objet = trim((string) $objet);
	$ids_objets = array_values(array_filter(array_unique(array_map('intval', $ids_objets))));
	if ($objet === '' || !$ids_objets) {
		return 0.0;
	}
	$where_objet = '(objet=' . sql_quote($objet) . ' AND ' . sql_in('id_objet', $ids_objets) . ')';
	if (!empty($options['legacy_id_journal'])) {
		$where_objet = '(' . $where_objet . ' OR ' . sql_in('id_journal', $ids_objets) . ')';
	}
	$where = array($where_objet);
	$imputations = array_values(array_unique(array_filter(array_map('strval', (array) ($options['imputations'] ?? array())), 'strlen')));
	if ($imputations) {
		$where[] = sql_in('imputation', $imputations);
	}
	if (!empty($options['validees'])) {
		$where[] = 'vu=1';
	}
	return (float) sql_getfetsel('SUM(recette)', 'spip_asso_comptes', implode(' AND ', $where));
}
