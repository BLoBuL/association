<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Statistiques comptables globales des événements d'un exercice.
 *
 * Le calcul appartient au module métier ; les écritures et transactions sont
 * toutefois obtenues par les API de leurs plugins propriétaires.
 */
function association_evenements_stats_compta_exercice($exercice) {
	$stats = [
		'total_operations' => 0,
		'total_recettes' => 0.0,
		'total_depenses' => 0.0,
		'montant_moyen' => 0.0,
		'par_statut' => [],
		'par_mode' => [],
	];
	$bornes = association_comptes_bornes_exercice((int) $exercice);
	include_spip('inc/association_compta_ecritures');
	$ecritures = association_compta_ecritures_lister([
		'objets' => ['activite', 'evenement'],
		'journaux_legacy' => ['activite|'],
		'date_debut' => $bornes['debut'],
		'date_fin' => $bornes['prochain_debut'],
	]);
	include_spip('inc/association_evenements_paiements');
	$transactions = association_evenements_transactions_lire(array_column($ecritures, 'id_transaction'));
	foreach ($ecritures as $ecriture) {
		$recette = (float) ($ecriture['recette'] ?? 0);
		$depense = (float) ($ecriture['depense'] ?? 0);
		if ($recette == 0 && $depense == 0) {
			continue;
		}
		$stats['total_recettes'] += $recette;
		$stats['total_depenses'] += $depense;
		$stats['total_operations']++;
		$id_transaction = (int) ($ecriture['id_transaction'] ?? 0);
		$transaction = $transactions[$id_transaction] ?? [];
		$statut = $id_transaction ? (($transaction['statut'] ?? '') ?: 'inconnu') : 'hors_transaction';
		$mode = $id_transaction ? (($transaction['mode'] ?? '') ?: 'inconnu') : 'hors_transaction';
		foreach (['par_statut' => $statut, 'par_mode' => $mode] as $cle => $valeur) {
			if (!isset($stats[$cle][$valeur])) {
				$stats[$cle][$valeur] = ['count' => 0, 'montant' => 0.0];
			}
			$stats[$cle][$valeur]['count']++;
			$stats[$cle][$valeur]['montant'] += $recette - $depense;
		}
	}
	$stats['solde'] = $stats['total_recettes'] - $stats['total_depenses'];
	if ($stats['total_operations']) {
		$stats['montant_moyen'] = $stats['solde'] / $stats['total_operations'];
	}
	return $stats;
}

/**
 * Liste (agrégée) des événements payants d'un exercice comptable.
 * Un événement est considéré payant s'il possède au moins une opération (recette ou dépense > 0).
 * Retourne un tableau indexé numériquement de lignes:
 *  id_evenement, titre, date_evenement, recettes, depenses, solde, operations, rentabilite_percent
 */
function stats_compta_activites_lister_evenements_exercice($exercice) {
	$exercice = intval($exercice);
	$bornes = association_comptes_bornes_exercice($exercice);
	$date_debut = $bornes['debut'];
	$date_fin = $bornes['prochain_debut'];
	include_spip('inc/association_compta_ecritures');
	$ecritures = association_compta_ecritures_lister([
		'date_debut' => $date_debut,
		'date_fin' => $date_fin,
		'journal_prefix' => 'activite|',
	]);
	$ids_activites = [];
	foreach ($ecritures as $ecriture) {
		$ids_activites[] = (int) substr((string) ($ecriture['journal'] ?? ''), strlen('activite|'));
	}
	$activites = [];
	$ids_evenements = [];
	$ids_activites = array_values(array_filter(array_unique($ids_activites)));
	if ($ids_activites) {
		foreach (sql_allfetsel('id_activite,id_evenement', 'spip_asso_activites', sql_in('id_activite', $ids_activites)) as $activite) {
			$activites[(int) $activite['id_activite']] = (int) $activite['id_evenement'];
			$ids_evenements[] = (int) $activite['id_evenement'];
		}
	}
	$evenements = [];
	if ($ids_evenements) {
		foreach (sql_allfetsel('id_evenement,titre,date_debut', 'spip_evenements', sql_in('id_evenement', array_unique($ids_evenements))) as $evenement) {
			$evenements[(int) $evenement['id_evenement']] = $evenement;
		}
	}
	$agregats = [];
	foreach ($ecritures as $ecriture) {
		$id_activite = (int) substr((string) ($ecriture['journal'] ?? ''), strlen('activite|'));
		$id_evenement = (int) ($activites[$id_activite] ?? 0);
		if ($id_evenement <= 0) {
			continue;
		}
		if (!isset($agregats[$id_evenement])) {
			$agregats[$id_evenement] = ['recettes' => 0.0, 'depenses' => 0.0, 'operations' => 0];
		}
		$agregats[$id_evenement]['recettes'] += (float) ($ecriture['recette'] ?? 0);
		$agregats[$id_evenement]['depenses'] += (float) ($ecriture['depense'] ?? 0);
		$agregats[$id_evenement]['operations']++;
	}
	$rows = [];
	foreach ($agregats as $id_evenement => $agregat) {
		$r = (float) $agregat['recettes'];
		$d = (float) $agregat['depenses'];
		if ($r == 0 && $d == 0) {
			continue; // pas payant
		}
		$solde = $r - $d;
		$rentabilite = ($d > 0) ? ($solde / $d * 100) : null; // null si aucune dépense
		$evenement = $evenements[$id_evenement] ?? [];
		$rows[] = [
			'id_evenement' => (int) $id_evenement,
			'titre' => $evenement['titre'] ?? '',
			'date_evenement' => $evenement['date_debut'] ?? '',
			'recettes' => $r,
			'depenses' => $d,
			'solde' => $solde,
			'operations' => (int) $agregat['operations'],
			'rentabilite_percent' => $rentabilite,
		];
	}
	usort($rows, function ($a, $b) { return strcmp($a['date_evenement'], $b['date_evenement']); });
	return $rows;
}
/**
 * Filtre SPIP exposant la liste agrégée des événements payants d'un exercice.
 */
function filtre_stats_compta_activites_lister_evenements_exercice($exercice) {
	return stats_compta_activites_lister_evenements_exercice($exercice);
}
