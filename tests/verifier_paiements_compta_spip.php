<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce controle doit etre execute dans le contexte SPIP.\n");
	exit(2);
}

include_spip('inc/comptes');
include_spip('inc/fonctions');

$erreurs = array();
$champs_transaction = array(
	'id_transaction', 'id_auteur', 'id_commande', 'statut', 'montant', 'date_paiement',
);
$commandes = sql_allfetsel('id_commande', 'spip_commandes', '', '', 'id_commande');
foreach ($commandes as $commande) {
	$id_commande = (int) $commande['id_commande'];
	$ancienne = sql_fetsel(
		implode(',', $champs_transaction),
		'spip_transactions',
		'id_commande=' . $id_commande,
		'',
		'id_transaction DESC'
	) ?: array();
	$nouvelle = association_commande_comptable_transaction($id_commande);
	$nouvelle = array_intersect_key($nouvelle, array_flip($champs_transaction));
	if ($ancienne !== $nouvelle) {
		$erreurs[] = 'commande:' . $id_commande;
	}
}

$stats_historiques = function ($exercice) {
	$stats = array(
		'total_operations' => 0,
		'total_recettes' => 0.0,
		'total_depenses' => 0.0,
		'montant_moyen' => 0.0,
		'par_statut' => array(),
		'par_mode' => array(),
	);
	$bornes = association_comptes_bornes_exercice((int) $exercice);
	$where = "((c.objet=" . sql_quote('activite') . " OR c.objet=" . sql_quote('evenement') . ")"
		. " OR c.journal LIKE " . sql_quote('activite|%') . ")"
		. " AND c.date >= " . sql_quote($bornes['debut'])
		. " AND c.date < " . sql_quote($bornes['prochain_debut']);
	$res = sql_select(
		'c.recette,c.depense,c.id_transaction,t.statut,t.mode',
		'spip_asso_comptes c LEFT JOIN spip_transactions t ON t.id_transaction=c.id_transaction',
		$where
	);
	while ($row = sql_fetch($res)) {
		$recette = (float) $row['recette'];
		$depense = (float) $row['depense'];
		if ($recette == 0 && $depense == 0) {
			continue;
		}
		$stats['total_recettes'] += $recette;
		$stats['total_depenses'] += $depense;
		$stats['total_operations']++;
		$statut = $row['id_transaction'] ? ($row['statut'] ?: 'inconnu') : 'hors_transaction';
		$mode = $row['id_transaction'] ? ($row['mode'] ?: 'inconnu') : 'hors_transaction';
		foreach (array('par_statut' => $statut, 'par_mode' => $mode) as $cle => $valeur) {
			if (!isset($stats[$cle][$valeur])) {
				$stats[$cle][$valeur] = array('count' => 0, 'montant' => 0.0);
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
};

$exercices = array(2024, 2025, 2026);
foreach ($exercices as $exercice) {
	if ($stats_historiques($exercice) !== stats_compta_activites_exercice($exercice)) {
		$erreurs[] = 'statistiques:' . $exercice;
	}
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo json_encode(array(
	'ok' => true,
	'commandes_comparees' => count($commandes),
	'exercices_compares' => count($exercices),
), JSON_UNESCAPED_SLASHES) . "\n";
