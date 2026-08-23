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

/**
 * Autorisations 'associer' — wrappers vers les fonctions _menu correspondantes.
 * Historiquement autoriser('associer','adherents') était utilisé dans les exec PHP.
 * On redirige vers la logique de menu pour centraliser les droits.
 */


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


/**
 * Autorisation générique 'modifier' pour l'objet 'asso'.
 * Permet aux administrateurs et aux rédacteurs responsables d'événements
 * d'exécuter les actions liées à la comptabilité/association.
 */


// SPIP authorization lookup expects functions of the form autoriser_<faire>_<objet>
// Ensure calls like autoriser('modifier','asso') are handled.




/**
 * Autorisation pour modifier un compte (asso_compte)
 * Les administrateurs peuvent toujours modifier.
 * Les rédacteurs peuvent modifier uniquement si le compte est lié à un événement
 * dont ils sont responsables.
 */


/**
 * Autorisation pour créer un compte (asso_compte)
 * Les administrateurs peuvent toujours créer.
 * Les rédacteurs peuvent créer seulement si un id_evenement est fourni
 * et s'ils sont responsables de cet événement.
 */


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


/**
 * Autorisation de modifier un événement.
 * Admins non-restreints (0minirezo) peuvent toujours.
 * Admins restreints (0minirezo avec restreint=true) peuvent si l'événement est dans leurs rubriques.
 * Les rédacteurs (1comite) peuvent si ils sont responsables via la fonction droit_auteur_evenements.
 */


// SPIP normalizes object types by removing underscores (e.g. 'asso_compte' -> 'assocompte').
// Provide wrappers with the normalized name so autoriser('modifier','asso_compte') resolves.




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


/**
 * Compatibilité SPIP4 : autorisation générique "publierdans"
 *
 * Certains appel dans les formulaires (ex: formulaires_instituer_objet_verifier_dist)
 * font appel à autoriser('publierdans', $type_parent, $id_parent).
 * SPIP4 ne fournit pas forcément une implémentation générique autoriser_publierdans_dist
 * (elle est souvent spécifique par type : autoriser_rubrique_publierdans_dist, ...).
 * Cette fonction fournit une compatibilité et des logs pour diagnostiquer les refus.
 */


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
