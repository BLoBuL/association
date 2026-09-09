<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/* CETTE FONCTION PERMET DE FAIRE LE SUIVI DES INSCRIPTIONS A UN EVENEMENT */
function gestions_places($id_evenement) {
	if (!intval($id_evenement)) {
		return [];
	}
	$meta_type_quota = $GLOBALS['association_metas']['meta_cfg_event_type_quota'];
	$meta_cfg_event_limite_nb_accompagnants = $GLOBALS['association_metas']['meta_cfg_event_limite_nb_accompagnants'];

	$affichage_dans_activites = affichage_dans_activites($id_evenement);
	$result = [];
	$query_evenement = sql_fetsel('*', 'spip_evenements', 'id_evenement=' . intval($id_evenement));

	$paiements_actifs = association_evenements_integration_active('association_paiements');
	if ($paiements_actifs) {
		include_spip('inc/association_evenements_paiements');
	}
	$activites_paiements = sql_allfetsel(
		'id_transaction,statut,nombre_inscrits',
		'spip_asso_activites',
		'id_evenement=' . intval($id_evenement) . ' AND id_transaction>0'
	);
	$ids_transactions = array_column($activites_paiements ?: [], 'id_transaction');
	$transactions = $paiements_actifs ? association_evenements_transactions_lire($ids_transactions) : [];

	/* SUIVI DES PAIEMENT */

	if ($affichage_dans_activites['payant']) {

		if ($affichage_dans_activites['validation'] == '1') {
			$result['nombre_total_inscrits'] = 0;
			foreach ($activites_paiements ?: [] as $activite_paiement) {
				$transaction = $transactions[(int) $activite_paiement['id_transaction']] ?? [];
				if (($activite_paiement['statut'] ?? '') !== 'ok') {
					continue;
				}
				if ($paiements_actifs && $affichage_dans_activites['validation_sur_paiement'] === 'oui' && ($transaction['statut'] ?? '') !== 'ok') {
					continue;
				}
				if ($transaction || !$paiements_actifs) {
					$result['nombre_total_inscrits'] += (int) $activite_paiement['nombre_inscrits'];
				}
			}
			$query_activites_valider = sql_fetsel('sum(nombre_inscrits) AS total_a_valider', 'spip_asso_activites', "id_evenement=$id_evenement AND statut = 'preinscrit'");
		} else {

			// $critere_statut_activite  = ($meta_type_quota == 'strict') ? "AND statut!='desinscrit'" : "AND statut=='inscrit'";
			$critere_statut_activite = " AND statut='ok'";
			$query_activites = sql_fetsel('*, sum(nombre_inscrits) AS total', 'spip_asso_activites', "id_evenement=$id_evenement $critere_statut_activite");
			$query_activites_valider = sql_fetsel('sum(nombre_inscrits) AS total_a_valider', 'spip_asso_activites', "id_evenement=$id_evenement AND statut = 'preinscrit'");

			# Nombre total d'inscrit
			$result['nombre_total_inscrits'] = (!empty($query_activites['total'])) ? $query_activites['total'] : 0;
		}

		// Ici on compte la somme des paiements en attente (sauf liste d'attente')
		$result['total_paiement_attente'] = 0;
		$result['total_encaisse'] = 0;
		foreach ($activites_paiements ?: [] as $activite_paiement) {
			$transaction = $transactions[(int) $activite_paiement['id_transaction']] ?? [];
			if (!$transaction) {
				continue;
			}
			if (!in_array($activite_paiement['statut'], ['desinscrit', 'liste_attente'], true)
				&& in_array($transaction['statut'], ['attente', 'commande'], true)) {
				$result['total_paiement_attente'] += (float) $transaction['montant'];
			}
			if ($transaction['statut'] === 'ok') {
				$result['total_encaisse'] += (float) $transaction['montant'];
			}
		}

	} else {

		// $critere_statut_activite  = ($meta_type_quota == 'strict') ? "AND statut!='desinscrit'" : "AND statut=='inscrit'";
		$critere_statut_activite = " AND statut='ok'";
		$query_activites = sql_fetsel('*, sum(nombre_inscrits) AS total', 'spip_asso_activites', "id_evenement=$id_evenement $critere_statut_activite");
		$query_activites_valider = sql_fetsel('sum(nombre_inscrits) AS total_a_valider', 'spip_asso_activites', "id_evenement=$id_evenement AND statut = 'preinscrit'");

		# Nombre total d'inscrit
		$result['nombre_total_inscrits'] = (!empty($query_activites['total'])) ? $query_activites['total'] : 0;
	}

	/* SUIVI DES QUOTAS */

	# Places total de l'évenement
	$result['places_evenement'] = $query_evenement['places'];

	if ($affichage_dans_activites['validation']) {
		$result['places_a_valider'] = (!empty($query_activites_valider['total_a_valider'])) ? $query_activites_valider['total_a_valider'] : 0; // Nombre de place restante à valider
		if ($meta_type_quota == 'strict') {
			$result['places_non_disponibles'] = $result['nombre_total_inscrits'] + $result['places_a_valider']; // places totale utilisées
		} else {
			$result['places_non_disponibles'] = $result['nombre_total_inscrits']; // places totale utilisées
		}

		$places_disponibles_prepa = $result['places_evenement'] - $result['places_non_disponibles'];
		$result['places_disponibles'] = ($places_disponibles_prepa <= 0) ? 0 : $places_disponibles_prepa; // Places encore disponibles
		$result['places_disponible_valider'] = ($query_activites_valider['total_a_valider'] > $result['places_disponibles']) ? true : false; // Infos sur les places à valider

	} else {
		// $result['validation'] = false;
		$result['places_non_disponibles'] = $result['nombre_total_inscrits']; // places totale utilisées
		# Places disponibles
		$places_disponibles_prepa = $result['places_evenement'] - $result['places_non_disponibles'];
		$result['places_disponibles'] = ($places_disponibles_prepa <= 0) ? 0 : $places_disponibles_prepa; // Places encore disponibles
	}

	# Limite des places par adhérent
	if (empty($query_evenement['limite_places'])) {
		$result['places_limites'] = (is_numeric($GLOBALS['association_metas']['meta_cfg_event_limite_nb_accompagnants'])) ? $GLOBALS['association_metas']['meta_cfg_event_limite_nb_accompagnants'] : 1000; // Defini dans la configuration d'association
	} elseif ($query_evenement['limite_places'] == '0') {
		$result['places_limites'] = 1000;
	} else {
		$result['places_limites'] = $query_evenement['limite_places']; // Defini dans l'evenement
	}

	# Places en attentes
	if ($affichage_dans_activites['attentes']) {
		$result['place_attentes_active'] = true;
		$query_activites_attente = sql_fetsel('sum(nombre_inscrits) AS total_attente', 'spip_asso_activites', "id_evenement=$id_evenement AND statut = 'liste_attente'");

		$result['places_total_attentes'] = ($affichage_dans_activites['attentes_illimite']) ? '1000000' : $query_evenement['attentes']; // Attente ilimité
		$result['places_en_attentes'] = $query_activites_attente['total_attente'];
		$result['places_en_attentes_disponible'] = $result['places_total_attentes'] - $result['places_en_attentes'];

		/* Si  quota attente pas illimité ET quota attente plein ET quota place plein */
		if (!$affichage_dans_activites['attentes_illimite'] and $result['places_en_attentes_disponible'] <= 0 and $result['places_disponibles'] == 0) {
			$result['quota_place_plein'] = 'oui';
			$result['quota_place_attente_plein'] = 'oui';
			$result['plein'] = true;
			/* Si  quota attente pas illimité ET quota attente PAS plein ET quota place plein */
		} elseif (!$affichage_dans_activites['attentes_illimite'] and $result['places_en_attentes_disponible'] > 0 and $result['places_disponibles'] == 0) {
			$result['quota_place_plein'] = 'oui';
			$result['quota_place_attente_plein'] = 'non';
			$result['plein'] = false;
			/* Si  quota attente est illimité ET quota place PAS plein */
		} elseif ($result['places_disponibles'] == 0) {
			$result['quota_place_plein'] = 'oui';
			$result['quota_place_attente_plein'] = 'non';
			$result['plein'] = false;
		} else {
			$result['quota_place_plein'] = 'non';
			$result['quota_place_attente_plein'] = 'non';
			$result['plein'] = false;
		}

	} else {
		/* Si pas d'attente ET plus de place dispo ET quota limité */
		if ($result['places_disponibles'] == '0' and $query_evenement['places'] !== '0') {
			$result['quota_place_plein'] = 'oui';
			$result['plein'] = true;
		} else {
			$result['quota_place_plein'] = 'non';
			$result['plein'] = false;
		}
	}

	# Infos de l'évenement
	$result['evenement_titre'] = $query_evenement['titre'];
	$result['evenement_lieu'] = $query_evenement['lieu'];
	$result['evenement_adresse'] = $query_evenement['adresse'];
	$result['inscriptible'] = $query_evenement['inscription'];
	$result['evenement_date_debut'] = association_datefr($query_evenement['date_debut']);
	$result['evenement_heure_debut'] = association_heurefr($query_evenement['date_debut']);
	$result['evenement_date_fin'] = association_datefr($query_evenement['date_fin']);
	$result['evenement_heure_fin'] = association_heurefr($query_evenement['date_fin']);
	if ($query_evenement['ouverture_differe']) {
		$newdate = str_replace('/', '-', $result['evenement_date_debut']);
		$dateDepartTimestamp = strtotime($newdate);
		$result['evenement_date_ouverture'] = date('Y-m-d H:i:s', strtotime('-' . $query_evenement['ouverture_differe'] . 'day', $dateDepartTimestamp));
		$result['evenement_date_ouverture_passed'] = association_comparateur_date('', $result['evenement_date_ouverture'], '>=');
		$result['evenement_date_ouverture'] = str_replace('-', '/', date('d-m-Y', strtotime($result['evenement_date_ouverture'])));
	} else {
		$result['evenement_date_ouverture'] = 'toujours';
	}
	# Accompagnants
	$result['accompagnants'] = $affichage_dans_activites['accompagnants'];

	$result['evenement_reseau_fiafe'] = $query_evenement['reseau_fiafe'] ?? false;

	// Normaliser les champs numériques pour éviter des comparaisons string/NULL ailleurs
	$numeric_keys = [
		'nombre_total_inscrits', 'places_evenement', 'places_a_valider', 'places_non_disponibles',
		'places_disponibles', 'places_total_attentes', 'places_en_attentes', 'places_en_attentes_disponible',
		'places_limites', 'total_paiement_attente', 'total_encaisse',
	];
	foreach ($numeric_keys as $k) {
		if (isset($result[$k])) {
			$result[$k] = is_numeric($result[$k]) ? intval($result[$k]) : ($result[$k] === '1000000' ? 1000000 : 0);
		} else {
			$result[$k] = 0;
		}
	}

	return $result;
}
