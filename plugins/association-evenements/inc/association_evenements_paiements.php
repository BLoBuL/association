<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
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
		sql_updateq('spip_asso_activites', array('statut' => 'ok', 'journal' => $journal), 'id_activite=' . $id_activite);
		job_queue_add(
			'facteur_envoyer_mail_activites',
			'Notification - Validation inscription automatique suite à un paiement réussi',
			array($id_evenement, 'inscription_frontend', array($id_activite))
		);
	} else {
		$journal = $date . ' : ' . _T('association_evenements:journal_encaissement_paiement')
			. '<br>' . ($activite['journal'] ?? '');
		sql_updateq('spip_asso_activites', array('journal' => $journal), 'id_activite=' . $id_activite);
	}

	if (!empty($GLOBALS['association_metas']['comptes'])) {
		include_spip('inc/association_evenements_comptabilite');
		association_evenements_compte_valider_transaction($id_transaction);
	}
	if (($GLOBALS['association_metas']['meta_cfg_envoi_recu_paiement_participation'] ?? '') === 'oui') {
		job_queue_add(
			'facteur_envoyer_recu_participation',
			'Notification - Reçu encaissement',
			array($activite['email_inscrit'] ?? '', $id_transaction, $id_activite, 'encaissement', '')
		);
	}
}
