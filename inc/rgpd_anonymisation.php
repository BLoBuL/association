<?php

/**
 * Anonymisation RGPD des donnees metier association.
 *
 * @package SPIP\Association\RGPD
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Anonymise les donnees association rattachees a un auteur.
 *
 * @param int $id_auteur
 * @param array $auteur
 * @return array
 */
function association_rgpd_anonymiser_auteur($id_auteur, $auteur = []) {
	include_spip('base/abstract_sql');

	$id_auteur = intval($id_auteur);
	if ($id_auteur <= 0) {
		return ['ok' => false, 'erreur' => _T('association:erreur_auteur_inexistant')];
	}

	$email = trim((string) ($auteur['email'] ?? ''));
	$anon = 'anonyme_' . $id_auteur;
	$resume = pipeline('association_rgpd_anonymiser_auteur', [
		'args' => ['id_auteur' => $id_auteur, 'email' => $email, 'anon' => $anon],
		'data' => [],
	]);
	$resume = is_array($resume) ? $resume : [];

	return [
		'ok' => true,
		'resume' => $resume,
	];
}

/**
 * Filtre des champs SQL selon le schema reel de la table.
 *
 * @param string $table
 * @param array $champs
 * @return array
 */
function association_rgpd_filtrer_champs($table, $champs) {
	$desc = sql_showtable($table, true);
	if (!isset($desc['field']) || !is_array($desc['field'])) {
		return [];
	}

	return array_intersect_key($champs, $desc['field']);
}

/**
 * Met a jour une table si au moins un champ est disponible.
 *
 * @param string $table
 * @param array $champs
 * @param string $where
 * @return int
 */
function association_rgpd_updateq($table, $champs, $where) {
	if (!$champs) {
		return 0;
	}
	$nombre = intval(sql_countsel($table, $where));
	if (!$nombre) {
		return 0;
	}
	return sql_updateq($table, $champs, $where) === false ? 0 : $nombre;
}
