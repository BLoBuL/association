<?php
// Helpers d'autorisation propres au plugin Association.

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}

/**
 * Helpers centraux de vérification de droits — plugin Association.
 *
 * Chargé via include_spip('inc/association_autorisations') depuis association_autoriser.php.
 * Regroupe les utilitaires partagés par toutes les fonctions d'autorisation du plugin.
 *
 * Licence GPL 3
 */

/**
 * Vérifie si l'utilisateur est administrateur non-restreint.
 * Centralise le pattern récurrent : $qui['statut'] === '0minirezo' && !$qui['restreint']
 *
 * @param array $qui utilisateur normalisé (via association_normalize_qui)
 * @return bool
 */
function association_est_admin_complet($qui) {
	return isset($qui['statut'], $qui['restreint'])
		&& $qui['statut'] === '0minirezo'
		&& !$qui['restreint'];
}

/**
 * Vérifie si l'utilisateur est responsable d'un événement donné via droit_auteur_evenements().
 * Couvre les admins restreints ET les rédacteurs (1comite).
 * Ne pas appeler pour un admin non-restreint (déjà autorisé en amont).
 *
 * @param array $qui          utilisateur normalisé (via association_normalize_qui)
 * @param int   $id_evenement identifiant de l'événement à tester
 * @return bool
 */
function association_est_responsable_evenement($qui, $id_evenement) {
	$id_evenement = intval($id_evenement);
	if ($id_evenement <= 0 || empty($qui['id_auteur'])) return false;
	list($activites_array, $type_auteur) = droit_auteur_evenements($qui['id_auteur'], $id_evenement);
	return $type_auteur === 'complet' || in_array($id_evenement, (array)$activites_array);
}

/**
 * Vérifie l'accès à un événement selon la logique SPIP native :
 * - admin restreint  → la rubrique de l'article parent figure dans ses rubriques autorisées.
 *                      $qui['restreint'] est produit par liste_rubriques_auteur() qui appelle
 *                      calcul_rubriques_incluses() : la liste contient déjà toutes les
 *                      sous-rubriques, un simple in_array suffit.
 * - rédacteur        → est déclaré auteur de l'article parent via spip_auteurs_liens
 *                      (même vérification que autoriser_article_modifier_dist natif de SPIP).
 *
 * Ne couvre pas l'admin non-restreint (à tester en amont avec association_est_admin_complet).
 *
 * @param array $qui          utilisateur normalisé (via association_normalize_qui)
 * @param int   $id_evenement identifiant de l'événement
 * @return bool
 */
function association_peut_acceder_evenement($qui, $id_evenement) {
	$id_evenement = intval($id_evenement);
	if ($id_evenement <= 0 || empty($qui['id_auteur'])) return false;

	// Récupérer l'article parent de l'événement
	$id_article = intval(sql_getfetsel('id_article', 'spip_evenements', 'id_evenement=' . $id_evenement));
	if ($id_article <= 0) return false;

	// Admin restreint : $qui['restreint'] est fourni par liste_rubriques_auteur() qui utilise
	// calcul_rubriques_incluses() en interne → la liste est déjà étendue à toutes les sous-rubriques.
	// Un simple in_array sur la rubrique directe de l'article suffit.
	if ($qui['statut'] === '0minirezo' && !empty($qui['restreint'])) {
		$id_rubrique = intval(sql_getfetsel('id_rubrique', 'spip_articles', 'id_article=' . $id_article));
		if ($id_rubrique <= 0) return false;

		$rubriques_auteur = is_array($qui['restreint']) ? array_keys($qui['restreint']) : (array)$qui['restreint'];
		return in_array($id_rubrique, $rubriques_auteur);
	}

	// Rédacteur (1comite) : auteur de l'article parent — même logique que SPIP natif
	// (autoriser_article_modifier_dist vérifie spip_auteurs_liens pour les rédacteurs)
	if ($qui['statut'] === '1comite') {
		return (bool) sql_fetsel(
			'id_auteur',
			'spip_auteurs_liens',
			"id_objet=" . $id_article . " AND objet='article' AND id_auteur=" . intval($qui['id_auteur'])
		);
	}

	return false;
}

