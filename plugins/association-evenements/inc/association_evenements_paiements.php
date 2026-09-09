<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_evenements_paiements_actifs() {
	include_spip('inc/association_capacites');
	return association_plugin_actif('association_paiements');
}

function association_evenements_transaction_lire($id_transaction) {
	if (!association_evenements_paiements_actifs() || !(int) $id_transaction) {
		return [];
	}
	include_spip('inc/association_paiements_transactions');
	return association_paiements_transaction_lire((int) $id_transaction);
}

function association_evenements_transactions_lire($ids_transactions) {
	if (!association_evenements_paiements_actifs()) {
		return [];
	}
	include_spip('inc/association_paiements_transactions');
	return association_paiements_transactions_lire((array) $ids_transactions);
}

function association_evenements_transaction_creer($montant, array $options = []) {
	if (!association_evenements_paiements_actifs()) {
		return 0;
	}
	$inserer_transaction = charger_fonction('inserer_transaction', 'bank');
	return $inserer_transaction((float) $montant, $options);
}

function association_evenements_transaction_modifier($id_transaction, array $donnees) {
	if (!association_evenements_paiements_actifs() || !(int) $id_transaction) {
		return true;
	}
	include_spip('inc/association_paiements_transactions');
	return association_paiements_transaction_modifier((int) $id_transaction, $donnees);
}

function association_evenements_transaction_supprimer_non_encaissee($id_transaction) {
	if (!association_evenements_paiements_actifs() || !(int) $id_transaction) {
		return true;
	}
	include_spip('inc/association_paiements_transactions');
	return association_paiements_transaction_supprimer_non_encaissee((int) $id_transaction);
}

function association_evenements_transactions_supprimer_non_encaissees($ids_transactions, $dry_run = false) {
	if (!association_evenements_paiements_actifs()) {
		return ['supprimes' => 0, 'protegees' => [], 'ids' => []];
	}
	include_spip('inc/association_paiements_transactions');
	return association_paiements_transactions_supprimer_non_encaissees((array) $ids_transactions, $dry_run);
}

function association_evenements_transaction_rgpd($id_transaction) {
	if (!association_evenements_paiements_actifs() || !(int) $id_transaction) {
		return [];
	}
	include_spip('inc/association_paiements_rgpd');
	return association_paiements_rgpd_export_transaction((int) $id_transaction);
}

function association_evenements_reglement_traiter($activite, $transaction) {
	$id_transaction = (int) $activite['id_transaction'];
	$id_evenement = (int) $activite['id_evenement'];
	$id_activite = (int) $activite['id_activite'];
	$evenement = sql_fetsel('validation_sur_paiement', 'spip_evenements', 'id_evenement=' . $id_evenement);
	$date = date('Y-m-d H:i:s');
	if (($activite['statut'] ?? '') !== 'ok' && ($evenement['validation_sur_paiement'] ?? '') === 'oui') {
		$journal = $date . ' : ' . _T('association_evenements:journal_inscription_validation_paiement')
			. '<br>' . ($activite['journal'] ?? '');
		sql_updateq('spip_asso_activites', ['statut' => 'ok', 'journal' => $journal], 'id_activite=' . $id_activite);
		job_queue_add(
			'facteur_envoyer_mail_activites',
			'Notification - Validation inscription automatique suite à un paiement réussi',
			[$id_evenement, 'inscription_frontend', [$id_activite]]
		);
	} else {
		$journal = $date . ' : ' . _T('association_evenements:journal_encaissement_paiement')
			. '<br>' . ($activite['journal'] ?? '');
		sql_updateq('spip_asso_activites', ['journal' => $journal], 'id_activite=' . $id_activite);
	}

	if (association_evenements_integration_active('association_compta')) {
		include_spip('inc/association_evenements_comptabilite');
		association_evenements_compte_valider_transaction($id_transaction);
	}
	if (($GLOBALS['association_metas']['meta_cfg_envoi_recu_paiement_participation'] ?? '') === 'oui') {
		job_queue_add(
			'facteur_envoyer_recu_participation',
			'Notification - Reçu encaissement',
			[$activite['email_inscrit'] ?? '', $id_transaction, $id_activite, 'encaissement', '']
		);
	}
}
