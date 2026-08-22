<?php
/**
 * Plugin Association 3 pour Spip 3.0
 * Licence GPL 3
 *
 * 2016
 * Auteurs : cf paquet.xml
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

// Debug global pour le plugin Association.
// Mettre à true pour activer les logs de debug détaillés (à utiliser uniquement en dev).
// Par défaut désactivé pour éviter des traces répétées en production.
// Pour activer temporairement le debug sur une instance de test, créer
// un fichier local de configuration ou modifier cette valeur en DEV uniquement.
if (!defined('ASSOCIATION_DEBUG')) {
	define('ASSOCIATION_DEBUG', false);
}

// Includes nécessaires pour certaines utilitaires utilisés par les autorisations
include_spip('inc/utils');
include_spip('inc/association/utils');

/**
 * journaliser un message de debug si ASSOCIATION_DEBUG est activé.
 * Utilise spip_log pour respecter le système de logs de SPIP.
 * En production (ASSOCIATION_DEBUG = false), aucun message n'est enregistré.
 * Utiliser spip_log() directement pour les vraies erreurs métier.
 *
 * @param string $message
 * @param string $contexte
 * @param int $niveau (ex: _LOG_INFO, _LOG_ERREUR) — ignoré si ASSOCIATION_DEBUG est false
 */
function association_debug_log($message, $contexte = 'association', $niveau = _LOG_INFO){
	// Conserver la constante historique comme surcharge locale, tout en rendant
	// la categorie persistante pilotable depuis la configuration et SPIP CLI.
	$debug_actif = defined('ASSOCIATION_DEBUG') && ASSOCIATION_DEBUG;
	if (!$debug_actif) {
		include_spip('inc/association_log');
		$debug_actif = function_exists('association_log_doit_logger')
			&& association_log_doit_logger('autorisations', 'debug');
	}
	if (!$debug_actif) {
		return 0;
	}

	// Determine caller file and line
	$line = 0;
	$file = '';
	if (function_exists('debug_backtrace')) {
		// limit to 3 frames for performance
		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
		// choose the immediate caller if present
		$caller = isset($bt[1]) ? $bt[1] : (isset($bt[0]) ? $bt[0] : null);
		if ($caller) {
			$file = isset($caller['file']) ? $caller['file'] : '';
			$line = isset($caller['line']) ? intval($caller['line']) : 0;
		}
	}

	// s'assurer que le message est une chaîne
	if (!is_string($message)) {
		$message = var_export($message, true);
	}

	// Préformatage : inclure fichier:ligne pour faciliter le tracing
	$shortfile = $file ? basename($file) : 'unknown';
	$formatted = sprintf('[%s:%d] %s', $shortfile, $line, $message);

	if (function_exists('spip_log')) {
		spip_log($formatted, $contexte . $niveau);
	}

	// retourner la ligne déclenchante pour usage programmatique
	return $line;
}

/**
 * Fonction vide pour éviter les erreurs dans le pipeline.
 */
function association_autoriser(){}

/**
 * Autoriser la migration des comptes Association vers les familles.
 */
function autoriser_association_migrerfamilles_dist($faire, $type, $id, $qui, $opt) {
	$qui = association_normalize_qui($qui);
	return $qui['statut'] === '0minirezo' && empty($qui['restreint']);
}

/**
 * Récupérer l'id_evenement depuis différents contextes (opt, request, id_compte, id_activite)
 *
 * @param int $id_compte optionnel id_compte à utiliser pour résolution
 * @param array $opt options passées à l'autorisation (peut contenir id_evenement ou id_activite)
 * @return int id_evenement trouvé ou 0
 */
function association_obtenir_evenement_contexte($id_compte = 0, $opt = array()){
	// 1) opt explicite
	if (is_array($opt) && !empty($opt['id_evenement'])) {
		return intval($opt['id_evenement']);
	}

	// 2) request id_evenement
	$id = intval(_request('id_evenement'));
	if ($id > 0) return $id;

	// 3) id_activite -> map to evenement
	$id_activite = 0;
	if (is_array($opt) && !empty($opt['id_activite'])) {
		$id_activite = intval($opt['id_activite']);
	}
	if ($id_activite <= 0) {
		$id_activite = intval(_request('id_activite'));
	}
	if ($id_activite > 0) {
		$r = sql_fetsel('id_evenement', 'spip_asso_activites', 'id_activite=' . intval($id_activite));
		if ($r && !empty($r['id_evenement'])) return intval($r['id_evenement']);
	}

	// 4) id_compte fourni (param ou request) -> lire l'objet/id_objet
	$id_c = intval($id_compte ?: (_request('id') ?: _request('id_compte')));
	if ($id_c > 0) {
		$row = sql_fetsel('objet,id_objet', 'spip_asso_comptes', 'id_compte=' . intval($id_c));
		if ($row && isset($row['objet']) && $row['objet'] === 'evenement') {
			return intval($row['id_objet']);
		}
	}

	return 0;
}

/**
 * Normaliser la variable $qui pour correspondre aux attentes de SPIP (visiteur_session)
 * Retourne un tableau avec au moins les clés 'statut' et 'id_auteur' et 'webmestre'
 */
function association_normalize_qui($qui){
	// If $qui is not an array or is an empty array (autoriser() may pass []),
	// try to fall back to SPIP session globals (visiteur_session or auteur_session).
	if (!is_array($qui) || (is_array($qui) && empty($qui))) {
		if (isset($GLOBALS['visiteur_session']) && is_array($GLOBALS['visiteur_session']) && !empty($GLOBALS['visiteur_session'])) {
			$qui = $GLOBALS['visiteur_session'];
		} elseif (isset($GLOBALS['auteur_session']) && is_array($GLOBALS['auteur_session']) && !empty($GLOBALS['auteur_session'])) {
			$qui = $GLOBALS['auteur_session'];
		} else {
			$qui = array();
		}
	}
	// Ensure keys exist
	$qui = array_merge(array('statut' => '', 'id_auteur' => 0, 'webmestre' => 'non', 'restreint' => false), (array)$qui);

	// Si restreint n'a pas été défini, le calculer comme SPIP le fait
	// via liste_rubriques_auteur() pour les admins restreints
	if (empty($qui['restreint']) && isset($qui['id_auteur']) && $qui['id_auteur'] > 0) {
		include_spip('inc/autoriser');
		if (defined('_ADMINS_RESTREINTS') && _ADMINS_RESTREINTS) {
			$qui['restreint'] = liste_rubriques_auteur($qui['id_auteur']);
		}
	}

	return $qui;
}

// Helpers centraux de vérification de droits (association_est_admin_complet, association_est_responsable_evenement)
include_spip('inc/association_autorisations');
if (!function_exists('association_est_admin_complet') && defined('_DIR_PLUGIN_ASSOCIATION')) {
	require_once _DIR_PLUGIN_ASSOCIATION . 'inc/association_autorisations.php';
}

/**
 * Vérifie si un module de l'association est activé dans la configuration.
 * Remplace l'ancien is_db_value_true($GLOBALS['association_metas'][$page]).
 *
 * @param string $module nom du module (comptes, dons, ressources, ventes, prets, destinations, activites, adherents)
 * @return bool
 */
function association_module_actif($module) {
	return !empty($GLOBALS['association_metas'][$module])
		&& function_exists('is_db_value_true')
		&& is_db_value_true($GLOBALS['association_metas'][$module]);
}

/**
 * Autorisation d'affichage du menu "Adhérents".
 * Seuls les administrateurs (statut '0minirezo') non-restreints peuvent voir ce menu.
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array|null $qui Informations sur l'utilisateur
 * @param array|null $opt Options supplémentaires
 * @return bool
 */
function autoriser_adherents_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	return association_est_admin_complet($qui);
}

/**
 * Autorisation d'affichage du menu "Activités".
 * Accessible à tous les utilisateurs.
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array|null $qui Informations sur l'utilisateur
 * @param array|null $opt Options supplémentaires
 * @return bool
 */
function autoriser_activites_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	// Menu accessible à tous
	return true;
}

/**
 * Autorisation d'affichage du menu "Cotisations".
 * Seuls les administrateurs (statut '0minirezo') non-restreints peuvent voir ce menu.
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array|null $qui Informations sur l'utilisateur
 * @param array|null $opt Options supplémentaires
 * @return bool
 */
function autoriser_cotisations_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	return association_est_admin_complet($qui);
}

/**
 * Autorisation d'affichage du menu "benevoles".
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array|null $qui Informations sur l'utilisateur
 * @param array|null $opt Options supplémentaires
 * @return bool
 */
function autoriser_benevoles_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	return true;
}
function autoriser_benevoles_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	return true;
}

function autoriser_comptes_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	if (!association_module_actif('comptes')) return false;
	return association_est_admin_complet($qui);
}

/**
 * Autorisation d'affichage du menu "Dons".
 * Réservé aux administrateurs complets, si le module est activé.
 */
function autoriser_dons_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	if (!association_module_actif('dons')) return false;
	return association_est_admin_complet($qui);
}

/**
 * Autorisation d'affichage du menu "Ressources".
 * Réservé aux administrateurs complets, si le module est activé.
 */
function autoriser_ressources_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	if (!association_module_actif('ressources')) return false;
	return association_est_admin_complet($qui);
}

/**
 * Autorisation d'affichage du menu "Ventes".
 * Réservé aux administrateurs complets, si le module est activé.
 */
function autoriser_ventes_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	if (!association_module_actif('ventes')) return false;
	return association_est_admin_complet($qui);
}

/**
 * Autorisation d'affichage du menu "Prêts".
 * Réservé aux administrateurs complets, si le module est activé.
 */
function autoriser_prets_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	if (!association_module_actif('prets')) return false;
	return association_est_admin_complet($qui);
}

/**
 * Autorisation d'affichage du menu "Destinations".
 * Réservé aux administrateurs complets, si le module est activé.
 */
function autoriser_destinations_menu_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	if (!association_module_actif('destinations')) return false;
	return association_est_admin_complet($qui);
}

function autoriser_ressource_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_ressources_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_ressource_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_ressources_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_pret_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_prets_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_pret_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_prets_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_destination_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_destinations_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_don_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_dons_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_don_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_dons_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_vente_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_ventes_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_vente_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_ventes_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_destination_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_destinations_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_asso_plan_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_comptes_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_asso_plan_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_comptes_menu_dist($faire, $type, $id, $qui, $opt);
}

/**
 * Autorisations 'associer' — wrappers vers les fonctions _menu correspondantes.
 * Historiquement autoriser('associer','adherents') était utilisé dans les exec PHP.
 * On redirige vers la logique de menu pour centraliser les droits.
 */
function autoriser_adherents_associer_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	return autoriser_adherents_menu_dist($faire, $type, $id, $qui, $opt);
}
function autoriser_comptes_associer_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	return autoriser_comptes_menu_dist($faire, $type, $id, $qui, $opt);
}
function autoriser_activites_associer_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	return autoriser_activites_menu_dist($faire, $type, $id, $qui, $opt);
}
function autoriser_dons_associer_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	return autoriser_dons_menu_dist($faire, $type, $id, $qui, $opt);
}
function autoriser_ressources_associer_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	return autoriser_ressources_menu_dist($faire, $type, $id, $qui, $opt);
}
function autoriser_voiradherent_associer_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	// voir_adherent : même droits que adherents
	return autoriser_adherents_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_comptes_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_comptes entry id=' . intval($id) . ' qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');

	// Administrateurs toujours autorisés
	if ($qui['statut'] === '0minirezo') {
		association_debug_log('autoriser_comptes allow admin', 'association_autorisation');
		return true;
	}

	// Rédacteurs responsables : autorisés si le compte est lié à un événement qu'ils gèrent
	if ($qui['statut'] == '1comite') {
		$id_evenement = 0;
		if ($id && intval($id) > 0) {
			$row = sql_fetsel('objet,id_objet', 'spip_asso_comptes', 'id_compte=' . intval($id));
			if ($row && $row['objet'] === 'evenement') {
				$id_evenement = intval($row['id_objet']);
			}
		} else {
			$id_evenement = intval(
				(!empty($opt['id_evenement']) ? $opt['id_evenement'] : 0)
				?: _request('id_evenement')
			);
		}

		if ($id_evenement > 0 && association_est_responsable_evenement($qui, $id_evenement)) {
			association_debug_log('autoriser_comptes allow responsable evenement', 'association_autorisation');
			return true;
		}
	}

	return false;
}

/**
 * Autorisation de créer des comptes dans l'association.
 * Accessible aux webmestres ou administrateurs ayant le droit de configurer l'association.
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array $qui Informations sur l'utilisateur
 * @param array $opt Options supplémentaires
 * @return bool
 */
function autoriser_asso_comptes_creer_dist($faire, $type, $id, $qui, $opt) {
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_asso_comptes_creer entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true) . ' opt=' . var_export($opt, true), 'association_autorisation');

	if (autoriser('webmestre', '', '', $qui)) return true;
	if (autoriser('configurer', 'association') && $qui['statut'] === '0minirezo') return true;

	// Rédacteurs responsables : autorisés pour créer une écriture comptable sur leur événement
	if ($qui['statut'] == '1comite') {
		$id_evenement = association_obtenir_evenement_contexte(0, is_array($opt) ? $opt : array());
		if ($id_evenement > 0 && association_est_responsable_evenement($qui, $id_evenement)) {
			association_debug_log('autoriser_asso_comptes_creer allow responsable evenement', 'association_autorisation');
			return true;
		}
	}

	return false;
}

function autorite_autoriser_auteurs_menu($faire,$quoi,$id,$qui,$options){
	$qui = association_normalize_qui($qui);
	association_debug_log('autorite_autoriser_auteurs_menu entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');
	return ($qui['statut'] === '0minirezo');
}

/**
 * Empêche les utilisateurs avec le statut '6forum' de voir un auteur.
 *
 * @param string $faire Action demandée
 * @param string $quoi Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array $qui Informations sur l'utilisateur
 * @param array $options Options supplémentaires
 * @return bool
 */
function autorite_autoriser_auteur_voir($faire,$quoi,$id,$qui,$options){
	$qui = association_normalize_qui($qui);
	association_debug_log('autorite_autoriser_auteur_voir entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');
	return ($qui['statut'] === '0minirezo');
}

/**
 * Autorisation générique 'modifier' pour l'objet 'asso'.
 * Permet aux administrateurs et aux rédacteurs responsables d'événements
 * d'exécuter les actions liées à la comptabilité/association.
 */
function autoriser_asso_modifier($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	// Normaliser $qui
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_asso_modifier entry id=' . intval($id) . ' qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');

	// Administrateurs toujours autorisés
	if ($qui['statut'] == '0minirezo') {
		association_debug_log('autoriser_asso_modifier allow admin', 'association_autorisation');
		return true;
	}

	// Rédacteurs responsables d'au moins un événement peuvent modifier
	if ($qui['statut'] == '1comite') {
		// determine account ids or event id(s) implied by the action
		$account_ids = array();

		// If explicit id provided
		if ($id && intval($id) > 0) {
			$account_ids[] = intval($id);
		}

		// Check request-scoped id_compte
		$req_id = intval(_request('id_compte'));
		if ($req_id > 0) $account_ids[] = $req_id;

		// Check bulk selection
		$sel = _request('selecteur_comptes');
		if (is_array($sel) && count($sel)) {
			foreach ($sel as $s) {
				$s = intval($s);
				if ($s > 0) $account_ids[] = $s;
			}
		}

		// If we have account ids, require that each account is linked to an event the author manages
		if (count($account_ids) > 0) {
			// get the list of events the author manages
			list($activites_array, $type_auteur, $id_result) = droit_auteur_evenements($qui['id_auteur']);
			if (empty($activites_array)) return false;

			$activites_map = array_flip($activites_array);
			foreach (array_unique($account_ids) as $acct) {
				$row = sql_fetsel('objet,id_objet', 'spip_asso_comptes', 'id_compte=' . intval($acct));
				if (!$row) return false;
				if ($row['objet'] !== 'evenement') return false;
				$ev = intval($row['id_objet']);
				if (!isset($activites_map[$ev])) return false;
			}

			association_debug_log('autoriser_asso_modifier allow account_ids owned', 'association_autorisation');
			return true;
		}

		// No account id: this may be a creation -> check opt/request for id_evenement
		$id_evenement = 0;
		if (is_array($opt) && !empty($opt['id_evenement'])) {
			$id_evenement = intval($opt['id_evenement']);
		} elseif (intval(_request('id_evenement')) > 0) {
			$id_evenement = intval(_request('id_evenement'));
		}
		if ($id_evenement > 0) {
			list($activites_array, $type_auteur, $id_result) = droit_auteur_evenements($qui['id_auteur'], $id_evenement);
			if ($type_auteur === 'complet') {
				association_debug_log('autoriser_asso_modifier allow complet', 'association_autorisation');
				return true;
			}
			if ($type_auteur === 'restreint' && in_array($id_evenement, $activites_array)) {
				association_debug_log('autoriser_asso_modifier allow restreint', 'association_autorisation');
				return true;
			}
		}
	}

	return false;
}

// SPIP authorization lookup expects functions of the form autoriser_<faire>_<objet>
// Ensure calls like autoriser('modifier','asso') are handled.
function autoriser_modifier_asso($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	// Wrapper for autoriser('modifier','asso') -> delegate to autoriser_asso_modifier
	association_debug_log('autoriser_modifier_asso wrapper called for id=' . intval($id) . ' type=' . var_export($type, true), 'association_autorisation');
	$res = autoriser_asso_modifier($faire, $type, $id, $qui, $opt);
	association_debug_log('autoriser_modifier_asso wrapper result=' . (int)$res, 'association_autorisation');
	return $res;
}

function autoriser_modifier_asso_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	// Distribution wrapper: same behavior
	association_debug_log('autoriser_modifier_asso_dist wrapper called for id=' . intval($id) . ' type=' . var_export($type, true), 'association_autorisation');
	$res = autoriser_asso_modifier($faire, $type, $id, $qui, $opt);
	association_debug_log('autoriser_modifier_asso_dist wrapper result=' . (int)$res, 'association_autorisation');
	return $res;
}

/**
 * Autorisation pour modifier un compte (asso_compte)
 * Les administrateurs peuvent toujours modifier.
 * Les rédacteurs peuvent modifier uniquement si le compte est lié à un événement
 * dont ils sont responsables.
 */
function autoriser_modifier_asso_compte_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	// Normaliser $qui
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_modifier_asso_compte entry id=' . intval($id) . ' qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');

	// Admins can always modify
	if ($qui['statut'] === '0minirezo') {
		association_debug_log('autoriser_modifier_asso_compte allow admin', 'association_autorisation');
		return true;
	}

	// Non-admins: attempt to resolve an event context and delegate
	if ($qui['statut'] === '1comite') {
		// If an explicit account id is provided and the account is linked to a transaction marked vu==1,
		// keep the stricter rule: only admins can modify such accounts.
		$id_compte = intval($id ?: _request('id_compte') ?: _request('id'));
		if ($id_compte > 0) {
			$compte = sql_fetsel('id_transaction,vu,objet,id_objet', 'spip_asso_comptes', 'id_compte=' . intval($id_compte));
			if ($compte) {
				if (!empty($compte['id_transaction']) && intval($compte['vu']) === 1) {
					// non-admins cannot modify
					association_debug_log('autoriser_modifier_asso_compte deny vu==1 for non-admin', 'association_autorisation');
					return false;
				}
			}
		}

		// Resolve event context (from id_compte, id_activite, id_evenement, opt)
		$id_evenement = association_obtenir_evenement_contexte($id_compte, is_array($opt) ? $opt : array());
		if ($id_evenement > 0) {
			// Delegate decision to event-level authorization
			$res = (bool)autoriser('modifier', 'evenement', $id_evenement, $qui, $opt);
			association_debug_log('autoriser_modifier_asso_compte delegate to evenement modifier result=' . (int)$res, 'association_autorisation');
			return $res;
		}
	}

	return false;
}

/**
 * Autorisation pour créer un compte (asso_compte)
 * Les administrateurs peuvent toujours créer.
 * Les rédacteurs peuvent créer seulement si un id_evenement est fourni
 * et s'ils sont responsables de cet événement.
 */
function autoriser_creer_asso_compte_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	// Normaliser $qui
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_creer_asso_compte entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true) . ' opt=' . var_export($opt, true), 'association_autorisation');

	// Admins can always create
	if ($qui['statut'] === '0minirezo') {
		association_debug_log('autoriser_creer_asso_compte allow admin', 'association_autorisation');
		return true;
	}

	// For editors, require an event context and delegate to event authorization
	if ($qui['statut'] === '1comite') {
		$id_evenement = association_obtenir_evenement_contexte(0, is_array($opt) ? $opt : array());
		if ($id_evenement <= 0) return false;
		return (bool)autoriser('modifier', 'evenement', $id_evenement, $qui, $opt);
	}

	return false;
}

/**
 * Autorisation de joindre un document.
 * Les documents peuvent être joints à des forums ou à des objets autorisés.
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array $qui Informations sur l'utilisateur
 * @param array $opt Options supplémentaires
 * @return bool
 */
function autoriser_joindredocument($faire, $type, $id, $qui, $opt){
	include_spip('inc/config');
	return (
        $type=='forum'
        )
        OR
		(
        $type=='article'
        OR in_array(table_objet_sql($type),explode(',',lire_config('documents_objets', '')))
		)
		AND (
		  (
			  $id>0
		    AND autoriser('modifier', $type, $id, $qui, $opt)
		  )
			OR (
				$id<0
				AND abs($id) == $qui['id_auteur']
				AND autoriser('ecrire', $type, $id, $qui, $opt)
			)
		);
}

/**
 * Autorisation de modifier un événement.
 * Accessible aux administrateurs et aux rédacteurs (statut '1comite').
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array $qui Informations sur l'utilisateur
 * @param array $opt Options supplémentaires
 * @return bool
 */
/*function autoriser_evenement_modifier($faire, $type, $id, $qui, $opt) {
	return $qui['statut'] == '0minirezo' OR $qui['statut'] == '1comite';
}*/


/**
 * Autorisation de créer un événement dans un article.
 * Même logique que autoriser_modifier_evenement_dist :
 * admin complet, admin restreint sur la rubrique de l'article, ou rédacteur auteur de l'article.
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet (article)
 * @param int $id Identifiant de l'article parent
 * @param array|null $qui Informations sur l'utilisateur
 * @param array|null $opt Options supplémentaires
 * @return bool
 */
function autoriser_article_creerevenementdans($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);

	// Admin complet : toujours autorisé
	if (association_est_admin_complet($qui)) return true;

	// Admin restreint ou rédacteur : doit pouvoir modifier l'article parent
	if (intval($id) > 0 && in_array($qui['statut'], array('0minirezo', '1comite'))) {
		return (bool) autoriser('modifier', 'article', intval($id), $qui, $opt);
	}

	return false;
}

/**
 * Autorisation de modifier un événement.
 * Admins non-restreints (0minirezo) peuvent toujours.
 * Admins restreints (0minirezo avec restreint=true) peuvent si l'événement est dans leurs rubriques.
 * Les rédacteurs (1comite) peuvent si ils sont responsables via la fonction droit_auteur_evenements.
 */
function autoriser_modifier_evenement_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_modifier_evenement entry id=' . intval($id) . ' qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');

	// 1) Admin non-restreint : accès complet
	if (association_est_admin_complet($qui)) {
		association_debug_log('autoriser_modifier_evenement allow admin complet', 'association_autorisation');
		return true;
	}

	// 2) Admin restreint : rubrique de l'article parent dans ses rubriques autorisées
	// 3) Rédacteur (1comite) : auteur de l'article parent de l'événement
	if (intval($id) > 0 && in_array($qui['statut'], array('0minirezo', '1comite'))) {
		$res = association_peut_acceder_evenement($qui, intval($id));
		association_debug_log('autoriser_modifier_evenement acceder_natif result=' . (int)$res, 'association_autorisation');
		return $res;
	}

	return false;
}

// SPIP normalizes object types by removing underscores (e.g. 'asso_compte' -> 'assocompte').
// Provide wrappers with the normalized name so autoriser('modifier','asso_compte') resolves.
function autoriser_assocompte_modifier_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	association_debug_log('autoriser_assocompte_modifier_dist wrapper for id=' . intval($id), 'association_autorisation');
	$res = autoriser_modifier_asso_compte_dist($faire, $type, $id, $qui, $opt);
	association_debug_log('autoriser_assocompte_modifier_dist result=' . (int)$res, 'association_autorisation');
	return $res;
}

function autoriser_assocompte_creer_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	association_debug_log('autoriser_assocompte_creer_dist wrapper for id=' . intval($id), 'association_autorisation');
	$res = autoriser_creer_asso_compte_dist($faire, $type, $id, $qui, $opt);
	association_debug_log('autoriser_assocompte_creer_dist result=' . (int)$res, 'association_autorisation');
	return $res;
}

/**
 * Autorisation de générer une newsletter.
 * Accessible aux administrateurs et aux rédacteurs (statut '1comite').
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array $qui Informations sur l'utilisateur
 * @param array $opt Options supplémentaires
 * @return bool
 */
function autoriser_newsletter_generer($faire, $type, $id, $qui, $opt) {
	$qui = association_normalize_qui($qui);
	return ($qui['statut'] === '0minirezo') || ($qui['statut'] === '1comite');
}

/**
 * Autorisation d'envoyer une newsletter.
 * En mode test, tous ceux qui peuvent la modifier peuvent l'envoyer.
 * En mode réel, seuls les administrateurs peuvent l'envoyer.
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array $qui Informations sur l'utilisateur
 * @param array $opt Options supplémentaires
 * @return bool
 */
function autoriser_newsletter_envoyer($faire, $type, $id, $qui, $opt) {
	if (isset($opt['test']) and $opt['test']) {
		return autoriser('modifier', $type, $id, $qui, $opt);
	}
	$qui = association_normalize_qui($qui);
	return ($qui['statut'] === '0minirezo') || ($qui['statut'] === '1comite');
}

/**
 * Autorisation d'instituer une newsletter.
 * Seuls les administrateurs et rédacteurs peuvent publier une newsletter.
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array $qui Informations sur l'utilisateur
 * @param array $opt Options supplémentaires
 * @return bool
 */
function autoriser_newsletter_instituer($faire, $type, $id, $qui, $opt) {
	if (isset($opt['statut']) and $opt['statut'] === 'publie'){
		$qui = association_normalize_qui($qui);
		return ($qui['statut'] === '0minirezo') || ($qui['statut'] === '1comite');
	}
	return autoriser('modifier', $type, $id, $qui, $opt);
}

/**
 * Compatibilité SPIP4 : autorisation générique "publierdans"
 *
 * Certains appel dans les formulaires (ex: formulaires_instituer_objet_verifier_dist)
 * font appel à autoriser('publierdans', $type_parent, $id_parent).
 * SPIP4 ne fournit pas forcément une implémentation générique autoriser_publierdans_dist
 * (elle est souvent spécifique par type : autoriser_rubrique_publierdans_dist, ...).
 * Cette fonction fournit une compatibilité et des logs pour diagnostiquer les refus.
 */
function autoriser_publierdans_dist($faire, $type = '', $id = 0, $qui = null, $opt = []){
    // Normaliser $qui
    if (!is_array($qui)) {
        $qui = isset($GLOBALS['visiteur_session']) ? $GLOBALS['visiteur_session'] : [];
    }
    $qui = array_merge(['statut' => '', 'id_auteur' => 0], $qui);

    // Log d'entrée (sous forme de chaîne)
    association_debug_log(var_export(array(
        'appel' => 'autoriser_publierdans',
        'faire' => $faire,
        'type' => $type,
        'id' => $id,
        'qui' => array('id_auteur' => ($qui['id_auteur'] ?? null), 'statut' => ($qui['statut'] ?? null)),
        'opt' => $opt,
    ), true), 'association_autorisation');

    // Si un type est fourni, tenter de déléguer à autoriser_{type}_publierdans[_dist]
    $type_norm = autoriser_type($type);
    if ($type_norm) {
        $func = 'autoriser_' . $type_norm . '_publierdans';
        if (function_exists($func)) {
            $res = $func($faire, $type, $id, $qui, $opt);
            association_debug_log("delegation to $func -> " . (int)$res, 'association_autorisation');
            return (bool)$res;
        }
        $func_dist = $func . '_dist';
        if (function_exists($func_dist)) {
            $res = $func_dist($faire, $type, $id, $qui, $opt);
            association_debug_log("delegation to $func_dist -> " . (int)$res, 'association_autorisation');
            return (bool)$res;
        }
    }

    // Pas de délégation possible : fallback compatible SPIP3 -> permettre aux 0minirezo et 1comite
    $allowed = in_array($qui['statut'], array('0minirezo', '1comite'));
    association_debug_log("autoriser_publierdans fallback allowed=" . ($allowed ? '1' : '0'), 'association_autorisation');
    return $allowed;
}

/**
 * Autorisation de modifier un article.
 * Les administrateurs non-restreints peuvent modifier tous les articles.
 * Les administrateurs restreints peuvent modifier les articles des rubriques qui leur sont associées.
 * Les rédacteurs peuvent modifier les articles selon les permissions de SPIP.
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'article
 * @param array $qui Informations sur l'utilisateur
 * @param array $opt Options supplémentaires
 * @return bool
 */
function autoriser_modifier_article_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);

	// Admin non-restreint : accès complet
	if (association_est_admin_complet($qui)) return true;

	// Admin restreint
	if ($qui['statut'] === '0minirezo' && !empty($qui['restreint']) && intval($id) > 0 && !empty($qui['id_auteur'])) {
		// 1) Droit natif SPIP : la rubrique de l'article est dans les rubriques autorisées de l'admin.
		//    $qui['restreint'] est fourni par liste_rubriques_auteur() qui appelle calcul_rubriques_incluses()
		//    → la liste est déjà étendue aux sous-rubriques, un in_array direct suffit.
		$id_rubrique = intval(sql_getfetsel('id_rubrique', 'spip_articles', 'id_article=' . intval($id)));
		if ($id_rubrique > 0) {
			$rubriques_auteur = is_array($qui['restreint']) ? array_keys($qui['restreint']) : (array)$qui['restreint'];
			if (in_array($id_rubrique, $rubriques_auteur)) return true;
		}

		// 2) Accès élargi Association : l'article contient un événement (avec inscription) géré par l'admin.
		//    Permet à un admin restreint d'accéder à l'article même s'il n'a pas les droits
		//    sur la rubrique, du moment qu'il est responsable d'un événement lié.
		$evenements = sql_allfetsel('id_evenement', 'spip_evenements', 'id_article=' . intval($id) . ' AND inscription=1');
		if (!empty($evenements)) {
			include_spip('association_options');
			list($activites_array) = droit_auteur_evenements($qui['id_auteur']);
			if (!empty($activites_array)) {
				foreach ($evenements as $evt) {
					if (in_array($evt['id_evenement'], $activites_array)) return true;
				}
			}
		}

		return false;
	}

	// Autres cas (rédacteurs, etc.) : déléguer à SPIP
	return true;
}

/**
 * Autorisation de modifier une newsletter.
 * Les newsletters publiées ne peuvent être modifiées que par les administrateurs et rédacteurs.
 * Les autres newsletters peuvent être modifiées par les administrateurs et rédacteurs.
 *
 * @param string $faire Action demandée
 * @param string $type Type d'objet
 * @param int $id Identifiant de l'objet
 * @param array $qui Informations sur l'utilisateur
 * @param array $opt Options supplémentaires
 * @return bool
 */
function autoriser_newsletter_modifier($faire, $type, $id, $qui, $opt) {
	static $baked = array();
	$qui = association_normalize_qui($qui);
	if (isset($opt['champ'])
		AND $champ=$opt['champ']
	  AND in_array($champ,array('titre','chapo','texte'))){
		if (!isset($baked[$id]))
			$baked[$id] = sql_getfetsel('baked','spip_newsletters','id_newsletter='.intval($id));
		if ($baked[$id])
			return false;
	}
	if (!isset($opt['statut']))
		$statut = sql_getfetsel("statut", "spip_newsletters", "id_newsletter=".intval($id));
	else
		$statut = $opt['statut'];
	if ($statut === 'publie') {
		return ($qui['statut'] === '0minirezo') || ($qui['statut'] === '1comite');
	}
	else {
		return in_array($qui['statut'], array('0minirezo', '1comite'));
	}
}

/**
 * Autorisation de voir une activité/événement
 * Accessible aux administrateurs, aux rédacteurs et aux responsables de l'événement
 *
 * @param string $faire Action demandée ('voir_activites')
 * @param string $type Type d'objet ('evenement')
 * @param int $id Identifiant de l'événement
 * @param array $qui Informations sur l'utilisateur
 * @param array $opt Options supplémentaires
 * @return bool
 */
function autoriser_voir_activites_dist($faire, $type, $id, $qui, $opt) {
	$qui = association_normalize_qui($qui);

	// Utilisateur non connecté : refus immédiat
	if (empty($qui['id_auteur']) || !($qui['id_auteur'] > 0)) return false;

	// Admin non-restreint : accès complet
	if (association_est_admin_complet($qui)) return true;

	// Webmestre SPIP (shortcut core)
	if (function_exists('autoriser') && autoriser('webmestre')) return true;

	// Admin restreint ou rédacteur responsable : accès si responsable de l'événement
	if (in_array($qui['statut'], array('0minirezo', '1comite')) && intval($id) > 0) {
		if (association_est_responsable_evenement($qui, $id)) return true;

		// Fallback legacy pour les rédacteurs (liste_responsables_evenement)
		if ($qui['statut'] === '1comite' && function_exists('liste_responsables_evenement')) {
			$resp = liste_responsables_evenement(intval($id));
			if (is_array($resp) && in_array(intval($qui['id_auteur']), $resp)) return true;
		}
	}

	return false;
}

/**
 * Autorisation pour voir les onglets du menu des activités.
 * Vérifie les droits spécifiques pour chaque type d'onglet.
 *
 * Onglets disponibles:
 * - inscriptions : toujours visible (accès déjà contrôlé par voir_activites)
 * - mailshots_activite : visible pour admin complet et responsables
 * - comptabilite : visible seulement si compta activée et accès comptable accordé
 *
 * @param string $faire Action demandée (onglet demandé)
 * @param string $type Type d'objet
 * @param int $id ID de l'événement
 * @param array $qui Informations utilisateur
 * @param array $opt Options supplémentaires
 * @return bool
 */
function autoriser_onglet_activites_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	$qui = association_normalize_qui($qui);
	$onglet = $opt['onglet'] ?? $faire;

	// Onglet inscriptions : toujours autorisé (l'accès global est vérifié par voir_activites)
	if ($onglet === 'inscriptions') return true;

	// Onglets mailshots_activite et comptabilite :
	// admin non-restreint, admin restreint responsable, ou rédacteur responsable (droits identiques)
	if (in_array($onglet, ['mailshots_activite', 'comptabilite'])) {
		if (association_est_admin_complet($qui)) return true;
		if ($id > 0 && in_array($qui['statut'], ['0minirezo', '1comite'])) {
			return association_est_responsable_evenement($qui, $id);
		}

		return false;
	}

	return false;
}

