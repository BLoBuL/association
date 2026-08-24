<?php
/*
 * Paiement Bancaire
 * module de paiement bancaire multi prestataires
 * Encaisser le reglement differe d'une transaction
 * (on a recu le cheque, le virement)
 * Utilise dans le back-office sur les transactions en attente de paiement
 *
 * Auteurs :
 * Cedric Morin, Nursit.com
 * (c) 2012-2018 - Distribue sous licence GNU/GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) return;

function association_paiements_remboursement_autorise($id_transaction) {
	$id_transaction = (int) $id_transaction;
	if (!$id_transaction) {
		return false;
	}
	$statut = sql_getfetsel('statut', 'spip_transactions', 'id_transaction=' . $id_transaction);
	include_spip('inc/autoriser');
	return $statut === 'ok' && autoriser('rembourser', 'transaction', $id_transaction);
}

function formulaires_rembourser_transaction_charger_dist($id_transaction){

	$transaction = sql_fetsel("*","spip_transactions","id_transaction=".intval($id_transaction));
	if (!$transaction || !association_paiements_remboursement_autorise($id_transaction))
		return false;

	$valeurs = array(
		'_id_transaction' => $id_transaction,
		'_mode' => $transaction['mode'],
		'raison'=>'',
		'_autorisation_id_prefixe' => remboursement_prefixe(),
	);
	
	return $valeurs;
}

function formulaires_rembourser_transaction_verifier_dist($id_transaction){
	$erreurs = array();
	if (!association_paiements_remboursement_autorise($id_transaction)) {
		$erreurs['message_erreur'] = _T('info_interdit');
		return $erreurs;
	}
	$raison = _request('raison');
	if (!$raison){
		$erreurs['raison'] = _T('info_obligatoire');
	}
	return $erreurs;
}

function formulaires_rembourser_transaction_traiter_dist($id_transaction){
	if (!association_paiements_remboursement_autorise($id_transaction)) {
		return array('message_erreur' => _T('info_interdit'));
	}

	$raison = _request('raison');
    $notifier_inscrit = _request('notifier_inscrit');
	$raison_remboursement = "<hr />\n".date('Y-m-d H:i:s').' REMBOURSEMENT '.remboursement_prefixe()." : ".$raison;
    $res = array();
	$rembourser_transaction = charger_fonction('rembourser_transaction','bank');
	if($rembourser_transaction($id_transaction,array('message'=>$raison_remboursement))){
		pipeline('association_paiements_remboursement_traiter', array(
			'args' => array(
				'id_transaction' => (int) $id_transaction,
				'raison' => (string) $raison,
				'notifier' => !empty($notifier_inscrit),
			),
			'data' => array('traite' => false, 'domaine' => ''),
		));

		$res['message_ok'] = _T('association_paiements:transaction_remboursee');
        $page = 'transactions';
        $args = "id_transaction=".$id_transaction;
        $res['redirect'] = generer_url_ecrire($page,$args);
	}
	else {
		$res['message_erreur'] = _T('association_paiements:erreur_remboursement_impossible');
	}

	return $res;
}

function remboursement_prefixe(){
	return "#".$GLOBALS['visiteur_session']['id_auteur']."-".$GLOBALS['visiteur_session']['nom'];
}
