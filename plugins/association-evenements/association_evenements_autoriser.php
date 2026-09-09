<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/association_evenements_autorisations');

/**
 * Droits de gestion bornés à l'événement, y compris ses responsables.
 */
function autoriser_evenement_gererinscriptions_dist($faire, $type, $id, $qui, $opt) {
	$qui = association_normalize_qui($qui);
	return (int) $id > 0 && (
		association_est_admin_complet($qui)
		|| association_est_responsable_evenement($qui, (int) $id)
		|| association_peut_acceder_evenement($qui, (int) $id)
	);
}

function autoriser_activites_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = association_normalize_qui($qui);
	if (empty($qui['id_auteur'])) {
		return false;
	}
	include_spip('association_options');
	$droit = droit_auteur_evenements((int) $qui['id_auteur']);
	return in_array($droit[1] ?? '', ['restreint', 'complet', 'restreint_wrong'], true);
}

function autoriser_activites_administrer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return association_est_admin_complet(association_normalize_qui($qui));
}

function autoriser_activites_associer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_activites_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_article_creerevenementdans($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = association_normalize_qui($qui);

	// Admin complet : toujours autorisé
	if (association_est_admin_complet($qui)) {
		return true;
	}

	// Admin restreint ou rédacteur : doit pouvoir modifier l'article parent
	if (intval($id) > 0 && in_array($qui['statut'], ['0minirezo', '1comite'])) {
		return (bool) autoriser('modifier', 'article', intval($id), $qui, $opt);
	}

	return false;
}

function autoriser_modifier_evenement_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_modifier_evenement entry id=' . intval($id) . ' qui=' . var_export(['id' => $qui['id_auteur'], 'statut' => $qui['statut']], true), 'association_autorisation');

	// 1) Admin non-restreint : accès complet
	if (association_est_admin_complet($qui)) {
		association_debug_log('autoriser_modifier_evenement allow admin complet', 'association_autorisation');
		return true;
	}

	// 2) Admin restreint : rubrique de l'article parent dans ses rubriques autorisées
	// 3) Rédacteur (1comite) : auteur de l'article parent de l'événement
	if (intval($id) > 0 && in_array($qui['statut'], ['0minirezo', '1comite'])) {
		$res = association_peut_acceder_evenement($qui, intval($id));
		association_debug_log('autoriser_modifier_evenement acceder_natif result=' . (int) $res, 'association_autorisation');
		return $res;
	}

	return false;
}

function autoriser_publierdans_dist($faire, $type = '', $id = 0, $qui = null, $opt = []) {
	// Normaliser $qui
	if (!is_array($qui)) {
		$qui = $GLOBALS['visiteur_session'] ?? [];
	}
	$qui = array_merge(['statut' => '', 'id_auteur' => 0], $qui);

	// Log d'entrée (sous forme de chaîne)
	association_debug_log(var_export([
		'appel' => 'autoriser_publierdans',
		'faire' => $faire,
		'type' => $type,
		'id' => $id,
		'qui' => ['id_auteur' => ($qui['id_auteur'] ?? null), 'statut' => ($qui['statut'] ?? null)],
		'opt' => $opt,
	], true), 'association_autorisation');

	// Si un type est fourni, tenter de déléguer à autoriser_{type}_publierdans[_dist]
	$type_norm = autoriser_type($type);
	if ($type_norm) {
		$func = 'autoriser_' . $type_norm . '_publierdans';
		if (function_exists($func)) {
			$res = $func($faire, $type, $id, $qui, $opt);
			association_debug_log("delegation to $func -> " . (int) $res, 'association_autorisation');
			return (bool) $res;
		}
		$func_dist = $func . '_dist';
		if (function_exists($func_dist)) {
			$res = $func_dist($faire, $type, $id, $qui, $opt);
			association_debug_log("delegation to $func_dist -> " . (int) $res, 'association_autorisation');
			return (bool) $res;
		}
	}

	// Pas de délégation possible : fallback compatible SPIP3 -> permettre aux 0minirezo et 1comite
	$allowed = in_array($qui['statut'], ['0minirezo', '1comite']);
	association_debug_log('autoriser_publierdans fallback allowed=' . ($allowed ? '1' : '0'), 'association_autorisation');
	return $allowed;
}

function autoriser_modifier_article_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = association_normalize_qui($qui);

	// Admin non-restreint : accès complet
	if (association_est_admin_complet($qui)) {
		return true;
	}

	// Admin restreint
	if ($qui['statut'] === '0minirezo' && !empty($qui['restreint']) && intval($id) > 0 && !empty($qui['id_auteur'])) {
		// 1) Droit natif SPIP : la rubrique de l'article est dans les rubriques autorisées de l'admin.
		//    $qui['restreint'] est fourni par liste_rubriques_auteur() qui appelle calcul_rubriques_incluses()
		//    → la liste est déjà étendue aux sous-rubriques, un in_array direct suffit.
		$id_rubrique = intval(sql_getfetsel('id_rubrique', 'spip_articles', 'id_article=' . intval($id)));
		if ($id_rubrique > 0) {
			$rubriques_auteur = is_array($qui['restreint']) ? array_keys($qui['restreint']) : (array) $qui['restreint'];
			if (in_array($id_rubrique, $rubriques_auteur)) {
				return true;
			}
		}

		// 2) Accès élargi Association : l'article contient un événement (avec inscription) géré par l'admin.
		//    Permet à un admin restreint d'accéder à l'article même s'il n'a pas les droits
		//    sur la rubrique, du moment qu'il est responsable d'un événement lié.
		$evenements = sql_allfetsel('id_evenement', 'spip_evenements', 'id_article=' . intval($id) . ' AND inscription=1');
		if (!empty($evenements)) {
			include_spip('association_options');
			[$activites_array] = droit_auteur_evenements($qui['id_auteur']);
			if (!empty($activites_array)) {
				foreach ($evenements as $evt) {
					if (in_array($evt['id_evenement'], $activites_array)) {
						return true;
					}
				}
			}
		}

		return false;
	}

	// Autres cas (rédacteurs, etc.) : déléguer à SPIP
	return true;
}

function autoriser_voir_activites_dist($faire, $type, $id, $qui, $opt) {
	$qui = association_normalize_qui($qui);

	// Utilisateur non connecté : refus immédiat
	if (empty($qui['id_auteur']) || !($qui['id_auteur'] > 0)) {
		return false;
	}

	// Admin non-restreint : accès complet
	if (association_est_admin_complet($qui)) {
		return true;
	}

	// Webmestre SPIP (shortcut core)
	if (function_exists('autoriser') && autoriser('webmestre')) {
		return true;
	}

	// Admin restreint ou rédacteur responsable : accès si responsable de l'événement
	if (in_array($qui['statut'], ['0minirezo', '1comite']) && intval($id) > 0) {
		if (association_est_responsable_evenement($qui, $id)) {
			return true;
		}
	}

	return false;
}

function autoriser_onglet_activites_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = association_normalize_qui($qui);
	$onglet = $opt['onglet'] ?? $faire;

	// Onglet inscriptions : toujours autorisé (l'accès global est vérifié par voir_activites)
	if ($onglet === 'inscriptions') {
		return true;
	}

	// Onglets mailshots_activite et comptabilite :
	// admin non-restreint, admin restreint responsable, ou rédacteur responsable (droits identiques)
	if (in_array($onglet, ['mailshots_activite', 'comptabilite'])) {
		if (association_est_admin_complet($qui)) {
			return true;
		}
		if ($id > 0 && in_array($qui['statut'], ['0minirezo', '1comite'])) {
			return association_est_responsable_evenement($qui, $id);
		}

		return false;
	}

	return false;
}
