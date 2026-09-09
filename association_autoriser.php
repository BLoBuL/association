<?php

/**
 * Autorisations transverses de la suite Association.
 *
 * Les autorisations métier sont déclarées par chaque plugin de la suite.
 *
 * @plugin     Association
 * @copyright  2016-2026
 * @licence    GPL-3.0-or-later
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

if (!defined('ASSOCIATION_DEBUG')) {
	define('ASSOCIATION_DEBUG', false);
}

include_spip('inc/utils');
include_spip('inc/association/utils');
include_spip('inc/association_autorisations');

/**
 * Déclarer le pipeline d'autorisations du socle.
 */
function association_autoriser() {
}

/**
 * Autoriser la configuration du socle et les imports qui réutilisent le type
 * conventionnel `_association`.
 */
function autoriser_association_configurer_dist($faire, $type, $id, $qui, $opt) {
	return association_est_admin_complet(association_normalize_qui($qui));
}

/**
 * Journaliser un message de diagnostic lorsque cette catégorie est active.
 *
 * @param mixed $message
 * @param string $contexte
 * @param int $niveau
 * @return int Ligne appelante, ou 0 si le diagnostic est désactivé.
 */
function association_debug_log($message, $contexte = 'association', $niveau = _LOG_INFO) {
	$debug_actif = ASSOCIATION_DEBUG;
	if (!$debug_actif) {
		include_spip('inc/association_log');
		$debug_actif = function_exists('association_log_doit_logger')
			&& association_log_doit_logger('autorisations', 'debug');
	}
	if (!$debug_actif) {
		return 0;
	}

	$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
	$appel = $trace[1] ?? $trace[0] ?? [];
	$fichier = isset($appel['file']) ? basename($appel['file']) : 'unknown';
	$ligne = isset($appel['line']) ? (int) $appel['line'] : 0;
	if (!is_string($message)) {
		$message = var_export($message, true);
	}

	spip_log(sprintf('[%s:%d] %s', $fichier, $ligne, $message), $contexte . $niveau);

	return $ligne;
}

/**
 * Normaliser le contexte utilisateur reçu par une autorisation SPIP.
 *
 * @param array|null $qui
 * @return array
 */
function association_normalize_qui($qui) {
	if (!is_array($qui) || !$qui) {
		if (!empty($GLOBALS['visiteur_session']) && is_array($GLOBALS['visiteur_session'])) {
			$qui = $GLOBALS['visiteur_session'];
		} elseif (!empty($GLOBALS['auteur_session']) && is_array($GLOBALS['auteur_session'])) {
			$qui = $GLOBALS['auteur_session'];
		} else {
			$qui = [];
		}
	}

	$qui = array_merge(
		['statut' => '', 'id_auteur' => 0, 'webmestre' => 'non', 'restreint' => false],
		$qui
	);
	if (!$qui['restreint'] && (int) $qui['id_auteur'] > 0 && defined('_ADMINS_RESTREINTS') && _ADMINS_RESTREINTS) {
		include_spip('inc/autoriser');
		$qui['restreint'] = liste_rubriques_auteur((int) $qui['id_auteur']);
	}

	return $qui;
}

/**
 * Indiquer si une fonctionnalité historique est activée dans la configuration.
 *
 * @param string $module
 * @return bool
 */
function association_module_actif($module) {
	if (!array_key_exists($module, $GLOBALS['association_metas'])) {
		return true;
	}

	return function_exists('association_valeur_bdd_est_vraie')
		&& association_valeur_bdd_est_vraie($GLOBALS['association_metas'][$module]);
}

/**
 * Autoriser l'ajout d'un document dans un contexte éditorial permis par SPIP.
 *
 * @param string $faire
 * @param string $type
 * @param int $id
 * @param array $qui
 * @param array $opt
 * @return bool
 */
function autoriser_joindredocument($faire, $type, $id, $qui, $opt) {
	include_spip('inc/config');
	$objets = explode(',', lire_config('documents_objets', ''));

	return $type === 'forum'
		|| (
			($type === 'article' || in_array(table_objet_sql($type), $objets, true))
			&& (
				($id > 0 && autoriser('modifier', $type, $id, $qui, $opt))
				|| (
					$id < 0
					&& abs($id) === (int) $qui['id_auteur']
					&& autoriser('ecrire', $type, $id, $qui, $opt)
				)
			)
		);
}
