<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Liste (agrégée) des événements payants d'un exercice comptable.
 * Un événement est considéré payant s'il possède au moins une opération (recette ou dépense > 0).
 * Retourne un tableau indexé numériquement de lignes:
 *  id_evenement, titre, date_evenement, recettes, depenses, solde, operations, rentabilite_percent
 */
function stats_compta_activites_lister_evenements_exercice($exercice){
    $exercice = intval($exercice);
    $bornes = association_comptes_bornes_exercice($exercice);
    $date_debut = $bornes['debut'];
    $date_fin = $bornes['prochain_debut'];
	include_spip('inc/association_compta_ecritures');
	$ecritures = association_compta_ecritures_lister(array(
		'date_debut' => $date_debut,
		'date_fin' => $date_fin,
		'journal_prefix' => 'activite|',
	));
	$ids_activites = array();
	foreach ($ecritures as $ecriture) {
		$ids_activites[] = (int) substr((string) ($ecriture['journal'] ?? ''), strlen('activite|'));
	}
	$activites = array();
	$ids_evenements = array();
	$ids_activites = array_values(array_filter(array_unique($ids_activites)));
	if ($ids_activites) {
		foreach (sql_allfetsel('id_activite,id_evenement', 'spip_asso_activites', sql_in('id_activite', $ids_activites)) as $activite) {
			$activites[(int) $activite['id_activite']] = (int) $activite['id_evenement'];
			$ids_evenements[] = (int) $activite['id_evenement'];
		}
	}
	$evenements = array();
	if ($ids_evenements) {
		foreach (sql_allfetsel('id_evenement,titre,date_debut', 'spip_evenements', sql_in('id_evenement', array_unique($ids_evenements))) as $evenement) {
			$evenements[(int) $evenement['id_evenement']] = $evenement;
		}
	}
	$agregats = array();
	foreach ($ecritures as $ecriture) {
		$id_activite = (int) substr((string) ($ecriture['journal'] ?? ''), strlen('activite|'));
		$id_evenement = (int) ($activites[$id_activite] ?? 0);
		if ($id_evenement <= 0) {
			continue;
		}
		if (!isset($agregats[$id_evenement])) {
			$agregats[$id_evenement] = array('recettes' => 0.0, 'depenses' => 0.0, 'operations' => 0);
		}
		$agregats[$id_evenement]['recettes'] += (float) ($ecriture['recette'] ?? 0);
		$agregats[$id_evenement]['depenses'] += (float) ($ecriture['depense'] ?? 0);
		$agregats[$id_evenement]['operations']++;
	}
	$rows = array();
	foreach ($agregats as $id_evenement => $agregat) {
		$r = (float) $agregat['recettes'];
		$d = (float) $agregat['depenses'];
        if($r == 0 && $d == 0){
            continue; // pas payant
        }
        $solde = $r - $d;
        $rentabilite = ($d > 0) ? ($solde / $d * 100) : null; // null si aucune dépense
		$evenement = $evenements[$id_evenement] ?? array();
        $rows[] = array(
			'id_evenement' => (int) $id_evenement,
			'titre' => $evenement['titre'] ?? '',
			'date_evenement' => $evenement['date_debut'] ?? '',
            'recettes' => $r,
            'depenses' => $d,
            'solde' => $solde,
			'operations' => (int) $agregat['operations'],
            'rentabilite_percent' => $rentabilite,
        );
    }
	usort($rows, function ($a, $b) { return strcmp($a['date_evenement'], $b['date_evenement']); });
    return $rows;
}
/**
 * Filtre SPIP exposant la liste agrégée des événements payants d'un exercice.
 */
function filtre_stats_compta_activites_lister_evenements_exercice($exercice){
    return stats_compta_activites_lister_evenements_exercice($exercice);
}
