<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_bilan_contexte($inutile = '') {
	$annee = (int) (_request('annee') ?: date('Y'));
	$ids = array_values(array_unique(array_map('intval', (array) (_request('destination') ?: array(0)))));
	$destinations = sql_allfetsel('id_destination,intitule', 'spip_asso_destination', '', '', 'intitule');
	$libelles = array_column($destinations, 'intitule', 'id_destination');
	$avec_plan = (bool) sql_countsel('spip_asso_plan');
	$classe_banques = $GLOBALS['association_metas']['classe_banques'] ?? '';
	$bilans = array();

	foreach ($ids as $id_destination) {
		$select = 'C.imputation, SUM(C.recette) AS recettes, SUM(C.depense) AS depenses';
		$from = 'spip_asso_comptes AS C';
		$where = array("C.date>=" . sql_quote($annee . '-01-01'), "C.date<" . sql_quote(($annee + 1) . '-01-01'));
		$group = 'C.imputation';
		if ($id_destination) {
			$from .= ' INNER JOIN spip_asso_destination_op AS D ON D.id_compte=C.id_compte';
			$select = 'C.imputation, SUM(D.recette) AS recettes, SUM(D.depense) AS depenses';
			$where[] = 'D.id_destination=' . $id_destination;
		}
		if ($avec_plan) {
			$from .= ' LEFT JOIN spip_asso_plan AS P ON P.code=C.imputation';
			$select .= ', P.intitule, P.code';
			$where[] = 'P.classe<>' . sql_quote($classe_banques);
			$group = 'C.imputation,P.intitule,P.code';
		}
		$lignes = sql_allfetsel($select, $from, $where, $group, $avec_plan ? 'P.code' : 'C.imputation');
		$totaux = array('recettes' => 0.0, 'depenses' => 0.0, 'solde' => 0.0);
		foreach ($lignes as &$ligne) {
			$ligne['intitule'] = $ligne['intitule'] ?: $ligne['imputation'];
			$ligne['recettes'] = (float) $ligne['recettes'];
			$ligne['depenses'] = (float) $ligne['depenses'];
			$ligne['solde'] = $ligne['recettes'] - $ligne['depenses'];
			foreach ($totaux as $cle => $_) {
				$totaux[$cle] += $ligne[$cle];
			}
		}
		unset($ligne);
		$bilans[] = array(
			'id_destination' => $id_destination,
			'intitule' => $id_destination ? ($libelles[$id_destination] ?? ('#' . $id_destination)) : _T('association:toutes_destination'),
			'lignes' => $lignes,
			'totaux' => $totaux,
		);
	}

	$encaisses = array();
	$total_initial = $total_actuel = 0.0;
	if ($avec_plan && $classe_banques !== '') {
		foreach (sql_allfetsel('*', 'spip_asso_plan', 'classe=' . sql_quote($classe_banques), '', 'code') as $banque) {
			$initial = (float) $banque['solde_anterieur'];
			$mouvements = sql_fetsel('SUM(recette) AS recettes,SUM(depense) AS depenses', 'spip_asso_comptes', array('date>=' . sql_quote($banque['date_anterieure']), 'journal=' . sql_quote($banque['code'])));
			$actuel = $initial + (float) ($mouvements['recettes'] ?? 0) - (float) ($mouvements['depenses'] ?? 0);
			$total_initial += $initial;
			$total_actuel += $actuel;
			$encaisses[] = array('intitule' => $banque['intitule'], 'date' => $banque['date_anterieure'], 'initial' => $initial, 'actuel' => $actuel);
		}
	}
	return compact('annee', 'ids', 'destinations', 'bilans', 'encaisses', 'total_initial', 'total_actuel');
}
