<?php

/**
 * Plugin Association — helper de logs avec catégories métier et niveau configurable.
 *
 * Configuration (via lire_config / ecrire_config) :
 * - association/debug/categories/<categorie> : on|off
 *
 * Usage :
 *   association_log('autorisations', 'message', 'debug', ['id_auteur' => 3]);
 *   association_log('autorisations', 'erreur critique', 'erreur');
 *
 * Licence GPL 3
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/config');

/**
 * Retourne la liste des catégories de log disponibles.
 * La clé est l'identifiant technique, la valeur est le libellé affiché.
 *
 * @return array<string, string>
 */
function association_log_categories_ajouter($definitions, $categories) {
	foreach ($categories as $cle => $definition) {
		if (!isset($definitions[$cle])) {
			$definitions[$cle] = $definition;
		}
	}
	return $definitions;
}

function association_log_categories_defaut() {
	$definitions = [
		'autorisations' => [
			'ordre' => 10,
			'label' => _T('association:log_cat_autorisations'),
		],
		'cron' => [
			'ordre' => 70,
			'label' => _T('association:log_cat_cron'),
		],
		'migration' => [
			'ordre' => 110,
			'label' => _T('association:log_cat_migration'),
		],
		'sync' => [
			'ordre' => 120,
			'label' => _T('association:log_cat_sync'),
		],
	];
	$definitions = pipeline('association_log_categories', $definitions);
	uasort($definitions, function ($a, $b) { return $a['ordre'] <=> $b['ordre']; });
	$categories = [];
	foreach ($definitions as $cle => $definition) {
		$categories[$cle] = $definition['label'];
	}
	return $categories;
}

/**
 * Vérifie si le log de debug est actif pour une catégorie donnée.
 * Les niveaux non-debug (erreur, critique, info) passent toujours.
 *
 * @param string $categorie
 * @param string $level  erreur|critique|info|debug
 * @return bool
 */
function association_log_doit_logger($categorie, $level = 'debug') {
	$level = strtolower(trim($level));

	// Erreurs et messages info passent toujours
	if ($level !== 'debug') {
		return true;
	}

	// Debug : piloté par la configuration
	$cfg = lire_config('association/debug/categories/' . strtolower(trim($categorie)), 'off');
	return $cfg === 'on';
}

/**
 * Convertit un niveau textuel en suffixe spip_log.
 *
 * @param string $level
 * @return int
 */
function association_log_suffixe($level) {
	$level = strtolower(trim($level));
	switch ($level) {
		case 'erreur':   return _LOG_ERREUR;
		case 'critique': return _LOG_CRITIQUE;
		case 'info':     return _LOG_INFO;
		case 'debug':    return _LOG_DEBUG;
		default:         return _LOG_INFO;
	}
}

/**
 * Extrait le fichier et la ligne de l'appelant depuis la pile d'appels.
 * Remonte jusqu'à trouver un frame hors de ce fichier et hors de association_autoriser.php.
 *
 * @param int $depth  Nombre maximum de frames à inspecter
 * @return array{file: string, line: int}
 */
function association_log_caller($depth = 6) {
	if (!function_exists('debug_backtrace')) {
		return ['file' => '', 'line' => 0];
	}

	$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $depth);
	$skip = [basename(__FILE__), 'association_autoriser.php'];

	foreach ($bt as $frame) {
		$f = isset($frame['file']) ? basename($frame['file']) : '';
		if ($f && !in_array($f, $skip)) {
			return ['file' => $f, 'line' => isset($frame['line']) ? intval($frame['line']) : 0];
		}
	}

	// Fallback : premier frame disponible
	$frame = $bt[0] ?? [];
	return [
		'file' => isset($frame['file']) ? basename($frame['file']) : 'unknown',
		'line' => isset($frame['line']) ? intval($frame['line']) : 0,
	];
}

/**
 * Logger standardisé pour le plugin Association.
 *
 * @param string $categorie  Catégorie métier : autorisations, cotisations, etc.
 * @param string $message    Message court
 * @param string $level      erreur|critique|info|debug  (défaut : debug)
 * @param array  $contexte   Données de contexte (ids, valeurs, etc.)
 * @param bool   $backtrace  Inclure fichier:ligne de l'appelant dans le message (défaut : true)
 */
function association_log($categorie, $message, $level = 'debug', $contexte = [], $backtrace = true) {
	if (!association_log_doit_logger($categorie, $level)) {
		return;
	}

	$categorie = strtolower(trim($categorie));

	if ($backtrace) {
		$caller = association_log_caller();
		if ($caller['file']) {
			$message = '[' . $caller['file'] . ':' . $caller['line'] . '] ' . $message;
		}
	}

	$ctx = '';
	if (!empty($contexte)) {
		$ctx = ' ctx=' . json_encode($contexte, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	spip_log($categorie . '-' . $message . $ctx, 'association' . association_log_suffixe($level));
}
