<?php

/**
 * Orchestrateur transversal de l'export RGPD Association.
 *
 * Chaque plugin métier alimente le pipeline public
 * `association_rgpd_export_auteur` sans que le socle lise ses tables.
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Retourne les données de la suite rattachées à un auteur.
 *
 * @param int $id_auteur
 * @return array
 */
function association_rgpd_export_donnees_auteur($id_auteur) {
	include_spip('base/abstract_sql');

	$id_auteur = intval($id_auteur);
	if ($id_auteur <= 0) {
		return [];
	}

	$auteur = sql_fetsel('id_auteur,email', 'spip_auteurs', 'id_auteur=' . $id_auteur);
	if (!$auteur) {
		return [];
	}

	$data = pipeline('association_rgpd_export_auteur', [
		'args' => [
			'id_auteur' => $id_auteur,
			'email' => trim((string) ($auteur['email'] ?? '')),
		],
		'data' => [],
	]);
	$data = is_array($data) ? $data : [];

	return ['date_export_association' => date('c')] + $data;
}

/**
 * Décode une structure JSON ou sérialisée issue d'un champ historique.
 *
 * @param mixed $valeur
 * @return mixed
 */
function association_rgpd_decoder_structure($valeur) {
	if (!is_string($valeur) || trim($valeur) === '') {
		return $valeur;
	}

	$decode = json_decode($valeur, true);
	if (json_last_error() === JSON_ERROR_NONE) {
		return $decode;
	}

	$unserialize = @unserialize($valeur, ['allowed_classes' => false]);
	if ($unserialize !== false || $valeur === serialize(false)) {
		return $unserialize;
	}

	return $valeur;
}

/**
 * Normalise les dates SQL sentinelles dans les exports.
 *
 * @param mixed $date
 * @return string
 */
function association_rgpd_export_date($date) {
	$date = trim((string) $date);
	if ($date === '' || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
		return '';
	}

	return $date;
}

/**
 * Teste l'existence d'une colonne SQL sans présumer du module actif.
 */
function association_rgpd_table_has_column($table, $column) {
	$desc = sql_showtable($table, true);
	return isset($desc['field']) && is_array($desc['field']) && array_key_exists($column, $desc['field']);
}
