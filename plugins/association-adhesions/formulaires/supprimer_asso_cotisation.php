<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_adhesions_autoriser_cotisation_reference($reference) {
	if (association_adhesions_module_actif('association_compta')) {
		return autoriser('modifier', 'asso_compte', (int) $reference);
	}

	return association_est_admin_complet($GLOBALS['visiteur_session'] ?? []);
}

function formulaires_supprimer_asso_cotisation_charger_dist() {
	$id_compte = (int) _request('id_compte');
	include_spip('inc/autoriser');
	include_spip('inc/cotisations_stockage');
	$cotisation = $id_compte ? association_cotisation_lire_par_compte($id_compte) : [];
	$editable = $cotisation && association_adhesions_autoriser_cotisation_reference($id_compte);

	return [
		'id_compte' => $id_compte,
		'supprimer_transaction' => 'non',
		'editable' => (bool) $editable,
		'message_erreur' => $editable ? '' : _T('association_adhesions:erreur_cotisation_introuvable'),
	];
}

function formulaires_supprimer_asso_cotisation_verifier_dist() {
	$erreurs = [];
	$id_compte = (int) _request('id_compte');
	$choix = (string) _request('supprimer_transaction');
	include_spip('inc/autoriser');

	if (!$id_compte || !association_adhesions_autoriser_cotisation_reference($id_compte)) {
		$erreurs['message_erreur'] = _T('info_interdit');
		return $erreurs;
	}
	if (!in_array($choix, ['oui', 'non'], true)) {
		$erreurs['supprimer_transaction'] = _T('info_obligatoire');
	}
	return $erreurs;
}

function formulaires_supprimer_asso_cotisation_traiter_dist() {
	$id_compte = (int) _request('id_compte');
	$choix = (string) _request('supprimer_transaction');
	include_spip('inc/autoriser');
	if (!$id_compte || !association_adhesions_autoriser_cotisation_reference($id_compte)) {
		return ['message_erreur' => _T('info_interdit')];
	}

	include_spip('inc/cotisations_stockage');
	$cotisation = association_cotisation_lire_par_compte($id_compte);
	if (!$cotisation) {
		return ['message_erreur' => _T('association_adhesions:erreur_cotisation_introuvable')];
	}

	$id_transaction = (int) ($cotisation['id_transaction'] ?? 0);
	$paiements_actifs = association_adhesions_module_actif('association_paiements');
	include_spip('inc/association_adhesions_integrations');
	if ($id_transaction && $choix === 'oui' && $paiements_actifs) {
		if (!association_adhesions_transaction_supprimer_non_encaissee($id_transaction)) {
			return ['message_erreur' => _T('association_adhesions:erreur_transaction_encaissee_protegee')];
		}
	} elseif ($id_transaction && $paiements_actifs) {
		association_adhesions_transaction_modifier($id_transaction, [
			'statut' => 'abandon',
			'message' => _T('association_adhesions:transaction_cotisation_supprimee'),
		]);
	}

	$id_cotisation = (int) ($cotisation['id_cotisation'] ?? 0);
	if ($id_cotisation) {
		sql_delete('spip_asso_cotisations', 'id_cotisation=' . $id_cotisation);
	}
	// Les documents ne sont pas détruits ici : seuls leurs liens vers l'ancien
	// compte et vers la cotisation sont retirés, conformément à SPIP.
	sql_delete('spip_documents_liens', "objet='compte' AND id_objet=" . $id_compte);
	if ($id_cotisation) {
		sql_delete('spip_documents_liens', "objet='cotisation' AND id_objet=" . $id_cotisation);
	}

	if (!empty($cotisation['id_compte']) && association_adhesions_module_actif('association_compta')) {
		include_spip('inc/association_compta_ecritures');
		association_compta_ecriture_supprimer((int) $cotisation['id_compte']);
	}

	return [
		'message_ok' => _T('association_adhesions:cotisation_supprimee'),
		'redirect' => (string) _request('url_retour'),
	];
}
