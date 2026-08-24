<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_evenements_migration_compta_automatique() {
	$creance = $GLOBALS['association_metas']['pc_activites_creance'] ?? '103';
	$paiement = $GLOBALS['association_metas']['pc_activites_paiement'] ?? '104';
	include_spip('inc/association_compta_ecritures');
	include_spip('inc/association_paiements_transactions');
	$ecritures = association_compta_ecritures_lister(
		array('objet' => 'evenement'),
		array('champs' => 'id_compte,imputation,id_transaction')
	);
	$transactions = association_paiements_transactions_lire(array_column($ecritures, 'id_transaction'));
	$nb = 0;
	foreach ($ecritures as $compte) {
		$id_transaction = (int) ($compte['id_transaction'] ?? 0);
		if ($id_transaction <= 0) continue;
		$cible = (($transactions[$id_transaction]['statut'] ?? '') === 'ok') ? $paiement : $creance;
		if ($cible && $cible !== ($compte['imputation'] ?? '')) {
			association_compta_ecriture_modifier((int) $compte['id_compte'], array('imputation' => $cible));
			$nb++;
		}
	}
	return $nb;
}
