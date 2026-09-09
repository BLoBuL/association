<?php

/*
 * Paiement Bancaire
 * module de paiement bancaire multi prestataires
 * stockage des transactions
 *
 * Auteurs :
 * Cedric Morin, Nursit.com
 * (c) 2012-2015 - Distribue sous licence GNU/GPL
 *
 */
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function action_supprimer_commande_dist($id_transaction = null) {
	if ($id_transaction === null) {
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$id_transaction = $securiser_action();
	}
	$id_transaction = (int) $id_transaction;
	if (!$id_transaction || !autoriser('supprimer', 'transaction', $id_transaction)) {
		include_spip('inc/minipres');
		minipres(_T('info_acces_interdit'));
	}
	include_spip('inc/association_paiements_transactions');
	if (!association_paiements_transaction_supprimer_non_encaissee($id_transaction)) {
		include_spip('inc/minipres');
		minipres(_T('info_acces_interdit'));
	}
}
