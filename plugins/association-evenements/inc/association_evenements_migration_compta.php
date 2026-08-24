<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_evenements_migration_compta_automatique() {
	$creance = $GLOBALS['association_metas']['pc_activites_creance'] ?? '103';
	$paiement = $GLOBALS['association_metas']['pc_activites_paiement'] ?? '104';
	$res = sql_select(
		'c.id_compte,c.imputation,t.statut AS statut_transaction',
		'spip_asso_comptes AS c LEFT JOIN spip_transactions AS t ON t.id_transaction=c.id_transaction',
		"c.objet='evenement' AND c.id_transaction>0"
	);
	$nb = 0;
	while ($compte = sql_fetch($res)) {
		$cible = ($compte['statut_transaction'] ?? '') === 'ok' ? $paiement : $creance;
		if ($cible && $cible !== ($compte['imputation'] ?? '')) {
			sql_updateq('spip_asso_comptes', array('imputation' => $cible), 'id_compte=' . (int) $compte['id_compte']);
			$nb++;
		}
	}
	return $nb;
}
