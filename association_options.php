<?php

/**
 * Chargements et helpers transverses de la suite Association.
 *
 * @plugin     Association
 * @copyright  2007-2026
 * @licence    GPL-3.0-or-later
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

// Certains appels historiques de SPIP CLI n'ajoutent que les sous-dossiers
// surchargeables. Maintenir explicitement la racine pour les modules de langue.
if (defined('_DIR_PLUGIN_ASSOCIATION')) {
	_chemin([rtrim(_DIR_PLUGIN_ASSOCIATION, '/\\') . '/']);
}

include_spip('inc/association_log');

$GLOBALS['association_metas'] = $GLOBALS['association_metas'] ?? [];
$charger_meta = charger_fonction('meta', 'inc');
$charger_meta('association_metas');

$GLOBALS['table_des_tables']['association_metas'] = 'association_metas';

if (!defined('_DIR_PLUGIN_ASSOCIATION_ICONES')) {
	$repertoire = defined('_DIR_PLUGIN_ASSOCIATION')
		? _DIR_PLUGIN_ASSOCIATION
		: rtrim(dirname(__FILE__), '/\\') . '/';
	define('_DIR_PLUGIN_ASSOCIATION_ICONES', $repertoire . 'img_pack/');
}

/**
 * Construire un lien privé accompagné d'une icône Font Awesome.
 *
 * @param string $texte
 * @param string $classe_icone
 * @param string $script
 * @param string|array $args
 * @return string
 */
function association_lien_ecrire_fa($texte, $classe_icone, $script, $args = '') {
	$classe_icone = $classe_icone ?: 'fa-regular fa-circle-question';

	return '<a href="' . generer_url_ecrire($script, $args) . '" class="lien_avec_fa">'
		. '<span class="fa_icone"><i class="' . attribut_html($classe_icone) . '" aria-hidden="true"></i></span>'
		. '<span>' . $texte . '</span></a>';
}

/**
 * Convertir une date SQL vers le format français.
 *
 * @param string $date
 * @return string
 */
function association_datefr($date) {
	$elements = explode('-', substr((string) $date, 0, 10));
	if (count($elements) !== 3) {
		return '';
	}

	return $elements[2] . '/' . $elements[1] . '/' . $elements[0];
}

/**
 * Extraire les heures et minutes d'une date SQL.
 *
 * @param string $date
 * @return string
 */
function association_heurefr($date) {
	$heure = trim(substr((string) $date, 10, 6));
	$elements = explode(':', $heure);

	return count($elements) >= 2 ? $elements[0] . ':' . $elements[1] : '';
}

/**
 * Vérifier une date au format jj/mm/aaaa.
 *
 * @param string $date
 * @return string|null
 */
function association_verifier_date($date) {
	if (!preg_match('#^\d{2}/\d{2}/\d{4}$#', (string) $date)) {
		return _T('association:erreur_format_date');
	}
	[$jour, $mois, $annee] = array_map('intval', explode('/', $date));

	return checkdate($mois, $jour, $annee) ? null : _T('association:erreur_date');
}

/**
 * Comparer deux dates selon un opérateur explicite.
 *
 * @param string $datetime1 Une valeur vide représente maintenant.
 * @param string $datetime2
 * @param string $signe
 * @return bool
 */
function association_comparateur_date($datetime1, $datetime2, $signe) {
	$timestamp1 = $datetime1 !== '' ? strtotime($datetime1) : time();
	$timestamp2 = strtotime($datetime2);
	$comparaisons = [
		'<' => static fn ($a, $b) => $a < $b,
		'>' => static fn ($a, $b) => $a > $b,
		'>=' => static fn ($a, $b) => $a >= $b,
		'<=' => static fn ($a, $b) => $a <= $b,
	];

	return isset($comparaisons[$signe])
		&& $timestamp1 !== false
		&& $timestamp2 !== false
		&& $comparaisons[$signe]($timestamp1, $timestamp2);
}

/**
 * Formater un montant avec deux décimales.
 *
 * @param mixed $montant
 * @return string
 */
function association_nbrefr($montant) {
	return number_format((float) $montant, 2, ',', ' ');
}

/**
 * Convertir un montant saisi en nombre flottant.
 *
 * @param mixed $valeur
 * @return float
 */
function association_recupere_montant($valeur) {
	$valeur = str_replace([' ', ','], ['', '.'], (string) $valeur);

	return $valeur !== '' ? (float) $valeur : 0.0;
}

/**
 * Formater un numéro de téléphone français par groupes de deux chiffres.
 *
 * @param mixed $numero
 * @return string
 */
function association_telfr($numero) {
	$numero = preg_replace('/\D/', '', (string) $numero);
	if (!(int) $numero) {
		return '';
	}

	return trim((string) preg_replace('/(\d{2})/', '$1&nbsp;', $numero));
}
