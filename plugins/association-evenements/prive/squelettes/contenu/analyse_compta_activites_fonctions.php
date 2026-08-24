<?php
/**
 * Fonctions pour l'analyse comptable des activités
 *
 * Ce fichier fournit des fonctions helper pour l'affichage et le calcul
 * des statistiques comptables des événements/activités avec support du filtrage.
 *
 * @package SPIP\Association\Comptabilite\Analyse
 */

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

/**
 * Récupère les statistiques d'un exercice comptable avec filtrage optionnel
 *
 * Cette fonction étend stats_compta_activites_exercice() en ajoutant
 * le support du filtrage par type d'opération (recettes/dépenses).
 *
 * @param int $exercice Année de l'exercice comptable
 * @param string $type Filtre: 'toutes', 'recette', 'depense'
 * @return array Statistiques avec les clés:
 *               - total_operations (int)
 *               - total_recettes (float)
 *               - total_depenses (float)
 *               - solde (float)
 *               - montant_moyen (float)
 *               - par_statut (array)
 *               - par_mode (array)
 */
function analyse_compta_activites_stats_exercice($exercice, $type = 'toutes', $vu = '1') {
    $stats = array(
        'total_operations' => 0,
        'total_recettes' => 0.0,
        'total_depenses' => 0.0,
        // 'solde' calculé après l'agrégation
        'montant_moyen' => 0.0,
        'par_statut' => array(),
        'par_mode' => array(),
    );
    $exercice = intval($exercice);
    $bornes = association_comptes_bornes_exercice($exercice);
    $date_debut = $bornes['debut'];
    $date_fin = $bornes['prochain_debut'];

	include_spip('inc/association_compta_ecritures');
	$ecritures = association_compta_ecritures_lister(array(
		'objet' => 'evenement',
		'date_debut' => $date_debut,
		'date_fin' => $date_fin,
		'vu' => analyse_compta_activites_normaliser_vu($vu),
	));
	$transactions = analyse_compta_activites_transactions($ecritures);
	foreach ($ecritures as $row) {
        $r = floatval($row['recette']);
        $d = floatval($row['depense']);
        // Ignorer totalement les lignes vides (ni recette ni depense)
        if ($r == 0 && $d == 0) {
            continue;
        }
        $inclure = true;
        if ($type === 'recette' && $r <= 0) { $inclure = false; }
        if ($type === 'depense' && $d <= 0) { $inclure = false; }

        // Totaux globaux (indépendants du filtre)
        $stats['total_recettes'] += $r;
        $stats['total_depenses'] += $d;
        if ($inclure) {
            $stats['total_operations']++;
            // Statut & mode : distinguer les écritures sans transaction
			$transaction = $transactions[(int) ($row['id_transaction'] ?? 0)] ?? array();
			$statut = !empty($row['id_transaction']) ? (!empty($transaction['statut']) ? $transaction['statut'] : 'inconnu') : 'hors_transaction';
			$mode = !empty($row['id_transaction']) ? (!empty($transaction['mode']) ? $transaction['mode'] : 'inconnu') : 'hors_transaction';
            if (!isset($stats['par_statut'][$statut])) {
                $stats['par_statut'][$statut] = array('count' => 0, 'montant' => 0.0); }
            $stats['par_statut'][$statut]['count']++;
            $stats['par_statut'][$statut]['montant'] += ($r - $d);
            if (!isset($stats['par_mode'][$mode])) {
                $stats['par_mode'][$mode] = array('count' => 0, 'montant' => 0.0); }
            $stats['par_mode'][$mode]['count']++;
            $stats['par_mode'][$mode]['montant'] += ($r - $d);
        }
    }
    $stats['solde'] = $stats['total_recettes'] - $stats['total_depenses'];
    if ($stats['total_operations'] > 0) {
        $stats['montant_moyen'] = $stats['solde'] / $stats['total_operations'];
    }

    return $stats;
}

function analyse_compta_activites_normaliser_vu($vu) {
	if (is_numeric($vu)) {
		return (int) $vu;
	}
	return is_string($vu) && preg_match('#^>=\s*0$#', $vu) ? '>=0' : 1;
}

function analyse_compta_activites_transactions(array $ecritures) {
	$ids = array_values(array_filter(array_unique(array_map('intval', array_column($ecritures, 'id_transaction')))));
	if (!$ids) {
		return array();
	}
	include_spip('inc/association_paiements_transactions');
	return association_paiements_transactions_lire($ids);
}

/**
 * Filtre SPIP pour stats_compta_activites_exercice avec filtrage
 */
function filtre_analyse_compta_activites_stats_exercice($exercice, $type = 'toutes', $vu = '1') {
    return analyse_compta_activites_stats_exercice($exercice, $type, $vu);
}

/**
 * Liste les événements payants d'un exercice avec filtrage optionnel
 *
 * Un événement est considéré "payant" s'il possède au moins une opération
 * avec recette > 0 ou dépense > 0.
 *
 * @param int $exercice Année de l'exercice comptable
 * @param string $type Filtre: 'toutes', 'recette', 'depense'
 * @return array Tableau d'événements avec les clés:
 *               - id_evenement (int)
 *               - titre (string)
 *               - date_evenement (string)
 *               - recettes (float)
 *               - depenses (float)
 *               - solde (float)
 *               - operations (int) : nombre d'opérations
 *               - rentabilite_percent (float|null)
 */
function analyse_compta_activites_lister_evenements_exercice($exercice, $type = 'toutes', $vu = '1') {
    $exercice = intval($exercice);
    $bornes = association_comptes_bornes_exercice($exercice);
    $date_debut = $bornes['debut'];
    $date_fin = $bornes['prochain_debut'];
    $rows_index = array(); // index par id_evenement

	include_spip('inc/association_compta_ecritures');
	$ecritures = association_compta_ecritures_lister(array(
		'objets' => array('evenement', 'activite'),
		'date_debut' => $date_debut,
		'date_fin' => $date_fin,
		'vu' => analyse_compta_activites_normaliser_vu($vu),
	));
	$ids_activites = array();
	$ids_evenements = array();
	foreach ($ecritures as $ecriture) {
		if (($ecriture['objet'] ?? '') === 'activite') {
			$ids_activites[] = (int) ($ecriture['id_objet'] ?? 0);
		} else {
			$ids_evenements[] = (int) ($ecriture['id_objet'] ?? 0);
		}
	}
	$activites = array();
	$ids_activites = array_values(array_filter(array_unique($ids_activites)));
	if ($ids_activites) {
		foreach (sql_allfetsel('id_activite,id_evenement', 'spip_asso_activites', sql_in('id_activite', $ids_activites)) as $activite) {
			$activites[(int) $activite['id_activite']] = (int) $activite['id_evenement'];
			$ids_evenements[] = (int) $activite['id_evenement'];
		}
	}
	$evenements = array();
	$ids_evenements = array_values(array_filter(array_unique($ids_evenements)));
	if ($ids_evenements) {
		foreach (sql_allfetsel('id_evenement,titre,date_debut,date_fin', 'spip_evenements', sql_in('id_evenement', $ids_evenements)) as $evenement) {
			$evenements[(int) $evenement['id_evenement']] = $evenement;
		}
	}
	foreach ($ecritures as $ecriture) {
		$id = ($ecriture['objet'] ?? '') === 'activite'
			? (int) ($activites[(int) ($ecriture['id_objet'] ?? 0)] ?? 0)
			: (int) ($ecriture['id_objet'] ?? 0);
		if ($id <= 0) {
			continue;
		}
		$evenement = $evenements[$id] ?? array();
		if (!isset($rows_index[$id])) {
			$rows_index[$id] = array(
				'id_evenement' => $id,
				'titre' => $evenement['titre'] ?? '',
				'date_evenement' => $evenement['date_debut'] ?? '',
				'recettes' => 0.0,
				'depenses' => 0.0,
				'operations' => 0,
			);
		}
		$rows_index[$id]['recettes'] += (float) ($ecriture['recette'] ?? 0);
		$rows_index[$id]['depenses'] += (float) ($ecriture['depense'] ?? 0);
		$rows_index[$id]['operations']++;
	}

    // Transformation + filtrage selon type
    $rows = array();
    foreach ($rows_index as $evt) {
        $r = $evt['recettes'];
        $d = $evt['depenses'];
        if ($r == 0 && $d == 0) { continue; }
        if ($type === 'recette' && $r <= 0) { continue; }
        if ($type === 'depense' && $d <= 0) { continue; }
        $solde = $r - $d;
        $rentabilite = ($d > 0) ? ($solde / $d * 100) : null;
        $evt['solde'] = $solde;
        $evt['rentabilite_percent'] = $rentabilite;
        $rows[] = $evt;
    }

    // Tri par date_evenement asc (déjà mais après fusion possible désordre)
    usort($rows, function($a, $b){
        return strcmp($a['date_evenement'], $b['date_evenement']);
    });
    return $rows;
}

/**
 * Filtre SPIP pour lister les événements payants avec filtrage
 */
function filtre_analyse_compta_activites_lister_evenements_exercice($exercice, $type = 'toutes', $vu = '1') {
    return analyse_compta_activites_lister_evenements_exercice($exercice, $type, $vu);
}

/**
 * Calcule les totaux agrégés pour le tableau des événements
 *
 * Cette fonction parcourt la liste d'événements et calcule :
 * - Total des recettes
 * - Total des dépenses
 * - Solde global
 * - Rentabilité globale
 *
 * @param array $evenements Tableau d'événements retourné par analyse_compta_activites_lister_evenements_exercice()
 * @return array Totaux avec les clés:
 *               - total_recettes (float)
 *               - total_depenses (float)
 *               - solde (float)
 *               - rentabilite_percent (float|null)
 *               - nb_evenements (int)
 */
function analyse_compta_activites_totaux_evenements($evenements) {
    $totaux = array(
        'total_recettes' => 0.0,
        'total_depenses' => 0.0,
        'solde' => 0.0,
        'rentabilite_percent' => null,
        'nb_evenements' => 0,
    );

    if (!is_array($evenements) || empty($evenements)) {
        return $totaux;
    }

    foreach ($evenements as $evt) {
        $totaux['total_recettes'] += floatval($evt['recettes']);
        $totaux['total_depenses'] += floatval($evt['depenses']);
        $totaux['nb_evenements']++;
    }

    $totaux['solde'] = $totaux['total_recettes'] - $totaux['total_depenses'];

    if ($totaux['total_depenses'] > 0) {
        $totaux['rentabilite_percent'] = ($totaux['solde'] / $totaux['total_depenses']) * 100;
    }

    return $totaux;
}

/**
 * Filtre SPIP pour calculer les totaux d'événements
 */
function filtre_analyse_compta_activites_totaux_evenements($evenements) {
    return analyse_compta_activites_totaux_evenements($evenements);
}

/**
 * Normalise le paramètre de filtre 'type'
 *
 * @param string $type Valeur brute du paramètre
 * @return string 'toutes', 'recette' ou 'depense'
 */
function analyse_compta_activites_normaliser_type($type) {
    // Gestion des valeurs nulles, vides ou variantes
    if (is_null($type) || $type === '' || $type === 'tout') {
        $type = 'toutes';
    }

    // Validation : on n'accepte que des valeurs valides
    if (!in_array($type, ['toutes', 'recette', 'depense'], true)) {
        $type = 'toutes';
    }

    return $type;
}

/**
 * Filtre SPIP pour normaliser le type
 */
function filtre_analyse_compta_activites_normaliser_type($type) {
    return analyse_compta_activites_normaliser_type($type);
}

/**
 * Retourne le libellé français du filtre de type
 *
 * @param string $type 'toutes', 'recette' ou 'depense'
 * @return string Libellé en français
 */
function analyse_compta_activites_libelle_type($type) {
    switch ($type) {
        case 'recette':
            return 'Recettes uniquement';
        case 'depense':
            return 'Dépenses uniquement';
        case 'toutes':
        default:
            return 'Toutes les opérations';
    }
}

/**
 * Filtre SPIP pour le libellé du type
 */
function filtre_analyse_compta_activites_libelle_type($type) {
    return analyse_compta_activites_libelle_type($type);
}

/**
 * Compte le nombre d'événements payants pour un exercice
 *
 * @param int $exercice Année de l'exercice
 * @param string $type Filtre optionnel
 * @return int Nombre d'événements
 */
function analyse_compta_activites_compter_evenements($exercice, $type = 'toutes', $vu = '1') {
    $evenements = analyse_compta_activites_lister_evenements_exercice($exercice, $type, $vu);
    return count($evenements);
}

/**
 * Filtre SPIP pour compter les événements
 */
function filtre_analyse_compta_activites_compter_evenements($exercice, $type = 'toutes', $vu = '1') {
    return analyse_compta_activites_compter_evenements($exercice, $type, $vu);
}
