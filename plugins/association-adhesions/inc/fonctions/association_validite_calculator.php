<?php

# #########################################################################################
# ### CALCUL DE LA VALIDITÉ POUR AFFICHAGE À L'AJOUT D'UNE COTISATION #####################
# #########################################################################################
// TODO : On va gérer des durées de cotisation différentes selon les catégories
function association_validite_calculator($id_auteur, $id_categorie = null) {

	// Vérifier si l'ID de l'auteur est valide
	if (!$id_auteur || !is_numeric($id_auteur)) {
		return false; // ID invalide
	}

	// Récupérer les informations essentielles de l'auteur
	// Utiliser SELECT * pour éviter les erreurs si certaines colonnes personnalisées manquent
	$query_auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
	if (!$query_auteur) {
		return false;
	}

	// Si la colonne radio_type_adherent n'existe pas dans la table, logguer pour traçabilité
	if (!array_key_exists('radio_type_adherent', $query_auteur)) {
		association_log('adherents', 'association_validite_calculator: colonne radio_type_adherent inexistante pour id_auteur=' . intval($id_auteur), 'info');
	}

	// Type d'auteur (champ extra radio_type_adherent) — fallback 'adherent'
	$type_auteur = isset($query_auteur['radio_type_adherent']) && $query_auteur['radio_type_adherent'] ? $query_auteur['radio_type_adherent'] : 'adherent';

	// Charger metas en sécurité
	$m = isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : [];

	include_spip('inc/cotisations');

	// Cas entreprise : règles distinctes (validation annuelle / date fixe / durée en mois)
	if ($type_auteur === 'entreprise') {
		$date_cible = association_date_validite_a_accorder(null, $type_auteur, $query_auteur['validite'] ?? null);
		if ($date_cible) {
			return date('d/m/Y', strtotime($date_cible));
		}

		$date_actuelle = new DateTime();
		$date_actuelle->add(new DateInterval('P1Y'));
		return $date_actuelle->format('d/m/Y');
	}

	// Si on arrive ici, on ne considère que les adhérents (cas 'scolaire' ou 'annuel')
	$type_cotisation = $m['validite'] ?? '';

	if ($type_cotisation === 'scolaire') {
		$date_cible = association_date_validite_a_accorder(null, $type_auteur, $query_auteur['validite'] ?? null);
		if ($date_cible) {
			return date('d/m/Y', strtotime($date_cible));
		}
		$date_actuelle = new DateTime();
		$date_actuelle->add(new DateInterval('P1Y'));
		return $date_actuelle->format('d/m/Y');
	} else {
		// Si le type de cotisation est annuel (comportement historique)

		// Date actuelle
		$date_actuelle = new DateTime();

		if (!isset($query_auteur['validite']) || empty($query_auteur['validite'])) {
			// Si aucune date de validité n'est définie, utiliser la date actuelle
			$date_reference = clone $date_actuelle;
		} else {
			// Convertir la date de validité en objet DateTime
			try {
				$date_reference = new DateTime(substr($query_auteur['validite'], 0, 10));

				// Si la date de validité est déjà passée, utiliser la date actuelle
				if ($date_reference < $date_actuelle) {
					$date_reference = clone $date_actuelle;
				}
			} catch (Exception $e) {
				// En cas d'erreur de format de date, utiliser la date actuelle
				$date_reference = clone $date_actuelle;
			}
		}

		// Ajouter un an à la date de référence
		try {
			$date_reference->add(new DateInterval('P1Y'));
			return $date_reference->format('d/m/Y');
		} catch (Exception $e) {
			$date_actuelle->add(new DateInterval('P1Y'));
			return $date_actuelle->format('d/m/Y');
		}
	}
}
