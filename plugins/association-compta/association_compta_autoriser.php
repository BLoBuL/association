<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function autoriser_comptes_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = association_normalize_qui($qui);
	return association_module_actif('comptes') && association_est_admin_complet($qui);
}

function autoriser_destinations_menu_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = association_normalize_qui($qui);
	return association_module_actif('destinations') && association_est_admin_complet($qui);
}

function autoriser_destination_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_destinations_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_destination_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_destinations_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_asso_plan_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_assoplan_modifier_dist($faire, $type, $id, $qui, $opt);
}

// SPIP retire les soulignés du type avant de chercher l'autorisation.
function autoriser_assoplan_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_comptes_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_asso_plan_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_assoplan_supprimer_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_assoplan_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_comptes_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_comptes_associer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	return autoriser_comptes_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_comptes_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	$qui = association_normalize_qui($qui);
	if ($qui['statut'] === '0minirezo') {
		return true;
	}
	if ($qui['statut'] === '1comite') {
		return association_compta_autoriser_ecriture_deleguer($faire, (int) $id, $qui, $opt);
	}
	return false;
}

function autoriser_asso_comptes_creer_dist($faire, $type, $id, $qui, $opt) {
	$qui = association_normalize_qui($qui);
	if (autoriser('webmestre', '', '', $qui)) {
		return true;
	}
	if (autoriser('configurer', 'association') && $qui['statut'] === '0minirezo') {
		return true;
	}
	if ($qui['statut'] === '1comite') {
		return association_compta_autoriser_ecriture_deleguer($faire, 0, $qui, $opt);
	}
	return false;
}

function association_compta_autoriser_ecriture_deleguer($faire, $id_compte, $qui, $opt = []) {
	$compte = $id_compte > 0
		? sql_fetsel('objet,id_objet', 'spip_asso_comptes', 'id_compte=' . (int) $id_compte)
		: [];
	$decision = pipeline('association_compta_autoriser_ecriture', [
		'args' => [
			'faire' => $faire,
			'id_compte' => (int) $id_compte,
			'objet' => (string) ($compte['objet'] ?? ''),
			'id_objet' => (int) ($compte['id_objet'] ?? 0),
			'qui' => $qui,
			'opt' => is_array($opt) ? $opt : [],
		],
		'data' => null,
	]);
	return $decision === true;
}

function autoriser_modifier_asso_compte_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	// Normaliser $qui
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_modifier_asso_compte entry id=' . intval($id) . ' qui=' . var_export(['id' => $qui['id_auteur'], 'statut' => $qui['statut']], true), 'association_autorisation');

	// Admins can always modify
	if ($qui['statut'] === '0minirezo') {
		association_debug_log('autoriser_modifier_asso_compte allow admin', 'association_autorisation');
		return true;
	}

	// Non-admins: attempt to resolve an event context and delegate
	if ($qui['statut'] === '1comite') {
		// If an explicit account id is provided and the account is linked to a transaction marked vu==1,
		// keep the stricter rule: only admins can modify such accounts.
		$id_compte = intval($id ?: _request('id_compte') ?: _request('id'));
		if ($id_compte > 0) {
			$compte = sql_fetsel('id_transaction,vu,objet,id_objet', 'spip_asso_comptes', 'id_compte=' . intval($id_compte));
			if ($compte) {
				if (!empty($compte['id_transaction']) && intval($compte['vu']) === 1) {
					// non-admins cannot modify
					association_debug_log('autoriser_modifier_asso_compte deny vu==1 for non-admin', 'association_autorisation');
					return false;
				}
			}
		}

		// Resolve event context (from id_compte, id_activite, id_evenement, opt)
		return association_compta_autoriser_ecriture_deleguer($faire, $id_compte, $qui, $opt);
	}

	return false;
}

function autoriser_creer_asso_compte_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	// Normaliser $qui
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_creer_asso_compte entry qui=' . var_export(['id' => $qui['id_auteur'], 'statut' => $qui['statut']], true) . ' opt=' . var_export($opt, true), 'association_autorisation');

	// Admins can always create
	if ($qui['statut'] === '0minirezo') {
		association_debug_log('autoriser_creer_asso_compte allow admin', 'association_autorisation');
		return true;
	}

	// For editors, require an event context and delegate to event authorization
	if ($qui['statut'] === '1comite') {
		return association_compta_autoriser_ecriture_deleguer($faire, 0, $qui, $opt);
	}

	return false;
}

function autoriser_assocompte_modifier_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	association_debug_log('autoriser_assocompte_modifier_dist wrapper for id=' . intval($id), 'association_autorisation');
	$res = autoriser_modifier_asso_compte_dist($faire, $type, $id, $qui, $opt);
	association_debug_log('autoriser_assocompte_modifier_dist result=' . (int) $res, 'association_autorisation');
	return $res;
}

function autoriser_assocompte_creer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
	association_debug_log('autoriser_assocompte_creer_dist wrapper for id=' . intval($id), 'association_autorisation');
	$res = autoriser_creer_asso_compte_dist($faire, $type, $id, $qui, $opt);
	association_debug_log('autoriser_assocompte_creer_dist result=' . (int) $res, 'association_autorisation');
	return $res;
}
