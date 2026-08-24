<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function formulaires_supprimer_asso_cotisation_charger_dist() {
	$id_compte = (int) _request('id_compte');
	include_spip('inc/autoriser');
	include_spip('inc/cotisations_stockage');
	$cotisation = $id_compte ? association_cotisation_lire_par_compte($id_compte) : array();
	$editable = $cotisation && autoriser('modifier', 'asso_compte', $id_compte);

	return array(
		'id_compte' => $id_compte,
		'supprimer_transaction' => 'non',
		'editable' => (bool) $editable,
		'message_erreur' => $editable ? '' : _T('association_adhesions:erreur_cotisation_introuvable'),
	);
}

function formulaires_supprimer_asso_cotisation_verifier_dist() {
	$erreurs = array();
	$id_compte = (int) _request('id_compte');
	$choix = (string) _request('supprimer_transaction');
	include_spip('inc/autoriser');

	if (!$id_compte || !autoriser('modifier', 'asso_compte', $id_compte)) {
		$erreurs['message_erreur'] = _T('info_interdit');
		return $erreurs;
	}
	if (!in_array($choix, array('oui', 'non'), true)) {
		$erreurs['supprimer_transaction'] = _T('info_obligatoire');
	}
	return $erreurs;
}

function formulaires_supprimer_asso_cotisation_traiter_dist() {
	$id_compte = (int) _request('id_compte');
	$choix = (string) _request('supprimer_transaction');
	include_spip('inc/autoriser');
	if (!$id_compte || !autoriser('modifier', 'asso_compte', $id_compte)) {
		return array('message_erreur' => _T('info_interdit'));
	}

	include_spip('inc/cotisations_stockage');
	$cotisation = association_cotisation_lire_par_compte($id_compte);
	if (!$cotisation) {
		return array('message_erreur' => _T('association_adhesions:erreur_cotisation_introuvable'));
	}

	$id_transaction = (int) ($cotisation['id_transaction'] ?? 0);
	if ($id_transaction && $choix === 'oui') {
		sql_delete('spip_transactions', 'id_transaction=' . $id_transaction);
	} elseif ($id_transaction) {
		sql_updateq(
			'spip_transactions',
			array('statut' => 'abandon', 'message' => _T('association_adhesions:transaction_cotisation_supprimee')),
			'id_transaction=' . $id_transaction
		);
	}

	$id_cotisation = (int) ($cotisation['id_cotisation'] ?? 0);
	if ($id_cotisation) {
		sql_delete('spip_asso_cotisations', 'id_cotisation=' . $id_cotisation);
	}
	// Les documents ne sont pas détruits ici : seuls leurs liens vers l'ancien
	// compte sont retirés, conformément au comportement natif de SPIP.
	sql_delete('spip_documents_liens', "objet='compte' AND id_objet=" . $id_compte);

	include_spip('inc/association_compta_ecritures');
	association_compta_ecriture_supprimer($id_compte);

	return array(
		'message_ok' => _T('association_adhesions:cotisation_supprimee'),
		'redirect' => (string) _request('url_retour'),
	);
}
