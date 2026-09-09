<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Vérifie l'éligibilité d'un internaute à s'inscrire à un événement.
 *
 * Critères vérifiés :
 * - Le type d'inscrit de l'événement est-il compatible avec l'internaute.
 * - L'internaute est-il déjà inscrit (doublon).
 * - Exceptions spécifiques (ex. entreprises interdites).
 * - Respect des quotas d'inscription par adhérent.
 *
 * @param string $id_evenement L'identifiant de l'événement.
 * @return array Un tableau contenant les informations d'éligibilité.
 */
function eligibilite_inscription_evenement($id_evenement) {
	include_spip('agenda_fonctions');

	// Initialisations sûres pour éviter les notices
	$eligibilite_inscription_evenement = [];
	$eligibilite_auteur_inscription = 'non';
	$eligibilite_id_auteur = false;
	$eligibilite_statut_auteur = '';
	$eligibilite_token_inscription = 'non';
	$eligibilite_nb_inscription_quota_adherent = 'oui';
	$test_date_validite = 'non';
	$date_validite_auteur_connecte = '';
	$id_activite = null;
	$session = [];
	$id_auteur_connecte = 0;

	/* Récupération des informations sur l'événement */
	$affichage_dans_activites = affichage_dans_activites($id_evenement); // Type d'affichage de l'événement
	$date_debut_evenement = sql_fetsel('date_debut', 'spip_evenements', "id_evenement=$id_evenement"); // Date de début
	$date_fin_evenement = sql_fetsel('date_fin', 'spip_evenements', "id_evenement=$id_evenement"); // Date de fin

	/* Création du token unique pour l'événement */
	$date_debut_str = is_array($date_debut_evenement) ? ($date_debut_evenement['date_debut'] ?? '') : '';
	$token_evenement = md5($id_evenement . $date_debut_str); // Token basé sur l'ID et la date
	$token_url = _request('token') ?? ''; // Token fourni dans l'URL

	/* Récupération des informations sur l'internaute */
	if (!empty($GLOBALS['visiteur_session']) && is_array($GLOBALS['visiteur_session'])) {
		$id_auteur_connecte = intval($GLOBALS['visiteur_session']['id_auteur'] ?? 0); // ID de l'auteur connecté
		if ($id_auteur_connecte > 0) {
			include_spip('inc/session');
			$session = sql_fetsel('*', 'spip_auteurs', "id_auteur=$id_auteur_connecte"); // Données de session de l'auteur
			if (function_exists('actualiser_sessions')) {
				actualiser_sessions($session); // Mise à jour de la session
			}
		}
		$validite_raw = $GLOBALS['visiteur_session']['validite'] ?? '';
		if (!empty($validite_raw) && !empty($date_debut_str)) {
			// Comparaison des dates si disponibles
			$date_validite_auteur_connecte = affdate($validite_raw, 'Y-m-d 23:59:59');
			$test_date_validite = ($date_validite_auteur_connecte >= $date_debut_str) ? 'oui' : 'non';
		}
		$statut_interne_auteur_connecte = $GLOBALS['visiteur_session']['statut_interne'] ?? '';
	} else {
		$statut_interne_auteur_connecte = '';
	}

	$adhesions_actives = association_evenements_integration_active('association_adhesions');

	/* Vérification de l'éligibilité de l'internaute */
	if ($token_url && $token_url === $token_evenement) {
		$eligibilite_auteur_inscription = 'oui'; // Token valide
		$eligibilite_token_inscription = 'oui';
	} elseif (($affichage_dans_activites['type_inscrits_evenement'] ?? '') === 'public') {
		$eligibilite_auteur_inscription = 'oui'; // Événement public
	} elseif (($affichage_dans_activites['type_inscrits_evenement'] ?? '') === 'prive' && $id_auteur_connecte) {
		$eligibilite_auteur_inscription = 'oui'; // Événement privé et auteur connecté
		$eligibilite_id_auteur = $id_auteur_connecte;
	} elseif (($affichage_dans_activites['type_inscrits_evenement'] ?? '') === 'prive' && empty($id_auteur_connecte)) {
		$eligibilite_auteur_inscription = 'non'; // Événement privé et auteur non connecté
		$eligibilite_id_auteur = false;
	} elseif (($affichage_dans_activites['type_inscrits_evenement'] ?? '') === 'strict' && !$adhesions_actives) {
		$eligibilite_auteur_inscription = 'oui'; // Sans Adhésions, le public universel s'applique.
	} elseif (($affichage_dans_activites['type_inscrits_evenement'] ?? '') === 'strict' && ($statut_interne_auteur_connecte === 'ok' && $test_date_validite === 'oui')) {
		$eligibilite_auteur_inscription = 'oui'; // Événement strict et auteur valide
		$eligibilite_id_auteur = $id_auteur_connecte;
	} elseif (($affichage_dans_activites['type_inscrits_evenement'] ?? '') === 'strict' && ($statut_interne_auteur_connecte !== 'ok' || $test_date_validite === 'non')) {
		$eligibilite_auteur_inscription = 'non'; // Événement strict et auteur non valide
		$eligibilite_id_auteur = $id_auteur_connecte;
		$eligibilite_statut_auteur = $statut_interne_auteur_connecte;
	} else {
		$eligibilite_auteur_inscription = 'non'; // Cas par défaut
	}

	/* Vérification des doublons */
	$nom_cookie = 'id_evenement_' . $id_evenement;
	$cookie_email_inscrit = !empty($_COOKIE[$nom_cookie]) ? $_COOKIE[$nom_cookie] : ''; // Email inscrit dans le cookie
	$query_activite_doublon = sql_select('*', 'spip_asso_activites', 'id_evenement =' . $id_evenement . " AND statut !='desinscrit'");

	while ($asso_activite = sql_fetch($query_activite_doublon)) {
		$hash_emails = md5($asso_activite['email_inscrit']); // Hash de l'email inscrit
		$id_auteur_inscrit = isset($asso_activite['id_auteur']) ? intval($asso_activite['id_auteur']) : 0;
		if ($cookie_email_inscrit && $cookie_email_inscrit === $hash_emails) {
			$id_activite = !empty($asso_activite['id_activite']) ? intval($asso_activite['id_activite']) : null;
		} elseif ($id_auteur_connecte && $id_auteur_connecte === $id_auteur_inscrit) {
			$id_activite = !empty($asso_activite['id_activite']) ? intval($asso_activite['id_activite']) : null;
		}
	}
	/* Gestion des exceptions : interdiction pour certains types d'inscrits */
	$metas = (isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas'])) ? $GLOBALS['association_metas'] : [];
	$radio_type_adherent = $session['radio_type_adherent'] ?? '';
	if ((($metas['meta_cfg_event_inscription_compte_entreprise'] ?? '') === 'non' && $radio_type_adherent === 'entreprise')
		|| $radio_type_adherent === 'babysitter') {
		$eligibilite_exception = 'non';
	} else {
		$eligibilite_exception = 'oui';
	}

	/* Vérification des quotas d'inscription par adhérent */
	if (!empty($metas['meta_cfg_event_quota_inscription_adherent']) &&
	   ($metas['meta_cfg_event_quota_inscription_adherent'] !== 'desactive') &&
	   !empty($metas['nb_inscription_quota_adherent']) &&
	   !empty($metas['nb_jour_quota_adherent']) &&
	   !empty($id_auteur_connecte)) {
		$nb_jour_quota_adherent = intval($metas['nb_jour_quota_adherent']);
		$nb_inscription_quota_adherent = intval($metas['nb_inscription_quota_adherent']);
		$date_debut_quota = agenda_jourdecal(date('Y-m-d H:i:s'), -$nb_jour_quota_adherent, 'Y-m-d ' . date(' H:i:s'));

		if (($metas['meta_cfg_event_quota_inscription_adherent'] ?? '') === 'global') {
			$critere_query_activite = "A.date > '" . $date_debut_quota . "' AND A.id_auteur = $id_auteur_connecte AND A.statut !='desinscrit'";
		} elseif (($metas['meta_cfg_event_quota_inscription_adherent'] ?? '') === 'activites') {
			$id_article = sql_getfetsel('id_article', 'spip_evenements', "id_evenement='" . $id_evenement . "'");
			$query_article = sql_fetsel('*', 'spip_articles', "id_article=$id_article");
			$critere_article = (!empty($query_article) && ($query_article['quota_inscription_adherent'] ?? '') === 'non') ? 'E.id_article = 0 AND' : "E.id_article = $id_article AND";
			$critere_date = "A.date > '" . $date_debut_quota . "' AND";
			$critere_query_activite = "$critere_date $critere_article A.id_auteur = $id_auteur_connecte AND A.statut !='desinscrit'";
		} else {
			$critere_query_activite = "A.id_auteur = $id_auteur_connecte AND A.statut !='desinscrit'";
		}

		$query_activite_quota = sql_select('*', 'spip_asso_activites AS A LEFT JOIN spip_evenements AS E ON E.id_evenement = A.id_evenement', $critere_query_activite);
		$nb_resultat_inscription_adherent = function_exists('sql_count') ? sql_count($query_activite_quota) : 0;

		if ($nb_resultat_inscription_adherent >= $nb_inscription_quota_adherent) {
			$query_date_premiere_activite = sql_getfetsel('A.date', 'spip_asso_activites AS A LEFT JOIN spip_evenements AS E ON E.id_evenement = A.id_evenement', $critere_query_activite, '', 'A.date ASC');
			$heure_premiere_activite = affdate($query_date_premiere_activite, ' H:i:s');
			$date_prochaine_inscription_possible = agenda_jourdecal($query_date_premiere_activite, $nb_jour_quota_adherent, 'Y-m-d ' . $heure_premiere_activite);
			$eligibilite_nb_inscription_quota_adherent = 'non';
		} else {
			$eligibilite_nb_inscription_quota_adherent = 'oui';
		}
	}

	/* Test final d'éligibilité */
	if ($eligibilite_auteur_inscription === 'oui' && $eligibilite_exception === 'oui' && $eligibilite_nb_inscription_quota_adherent === 'oui') {
		$eligibilite_inscription_evenement += [
			'eligible_inscription' => 'oui',
			'eligibilite_auteur_inscription' => $eligibilite_auteur_inscription,
		];
	} else {
		$eligibilite_inscription_evenement += [
			'eligible_inscription' => 'non',
			'eligibilite_auteur_inscription' => $eligibilite_auteur_inscription,
			'eligibilite_id_auteur' => $eligibilite_id_auteur,
			'eligibilite_statut_auteur' => $eligibilite_statut_auteur,
			'eligibilite_exception' => $eligibilite_exception,
			'eligibilite_nb_inscription_quota_adherent' => $eligibilite_nb_inscription_quota_adherent,
			'test_date_validite' => $test_date_validite,
			'evenement_date_debut' => $date_debut_str,
			'date_validite_auteur_connecte' => $date_validite_auteur_connecte,
		];
	}

	if ($id_activite !== null) {
		$eligibilite_inscription_evenement += ['id_activite' => $id_activite];
	}
	if ($eligibilite_token_inscription === 'oui') {
		$eligibilite_inscription_evenement += ['eligibilite_token_inscription' => 'oui', 'token_url' => $token_url];
	}
	if ($eligibilite_nb_inscription_quota_adherent === 'non') {
		$eligibilite_inscription_evenement += [
			'query_date_premiere_activite' => $query_date_premiere_activite,
			'date_prochaine_inscription_possible' => $date_prochaine_inscription_possible,
		];
	}

	return $eligibilite_inscription_evenement;
}
