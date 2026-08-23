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
	return autoriser_comptes_menu_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_asso_plan_supprimer_dist($faire, $type = '', $id = 0, $qui = null, $opt = null) {
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
		$id_evenement = 0;
		if ((int) $id > 0) {
			$compte = sql_fetsel('objet,id_objet', 'spip_asso_comptes', 'id_compte=' . (int) $id);
			if ($compte && $compte['objet'] === 'evenement') {
				$id_evenement = (int) $compte['id_objet'];
			}
		} else {
			$id_evenement = (int) (($opt['id_evenement'] ?? 0) ?: _request('id_evenement'));
		}
		return $id_evenement > 0 && autoriser('modifier', 'evenement', $id_evenement, $qui, $opt);
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
		$id_evenement = association_obtenir_evenement_contexte(0, is_array($opt) ? $opt : array());
		return $id_evenement > 0 && autoriser('modifier', 'evenement', $id_evenement, $qui, $opt);
	}
	return false;
}


function association_obtenir_evenement_contexte($id_compte = 0, $opt = array()){
	// 1) opt explicite
	if (is_array($opt) && !empty($opt['id_evenement'])) {
		return intval($opt['id_evenement']);
	}

	// 2) request id_evenement
	$id = intval(_request('id_evenement'));
	if ($id > 0) return $id;

	// 3) id_activite -> map to evenement
	$id_activite = 0;
	if (is_array($opt) && !empty($opt['id_activite'])) {
		$id_activite = intval($opt['id_activite']);
	}
	if ($id_activite <= 0) {
		$id_activite = intval(_request('id_activite'));
	}
	if ($id_activite > 0) {
		$contexte = pipeline('association_evenement_resoudre_contexte', array(
			'args' => array('id_activite' => $id_activite),
			'data' => 0,
		));
		$id_evenement = (int) ($contexte['data'] ?? 0);
		if ((int) $id_evenement > 0) {
			return (int) $id_evenement;
		}
	}

	// 4) id_compte fourni (param ou request) -> lire l'objet/id_objet
	$id_c = intval($id_compte ?: (_request('id') ?: _request('id_compte')));
	if ($id_c > 0) {
		$row = sql_fetsel('objet,id_objet', 'spip_asso_comptes', 'id_compte=' . intval($id_c));
		if ($row && isset($row['objet']) && $row['objet'] === 'evenement') {
			return intval($row['id_objet']);
		}
	}

	return 0;
}

function autoriser_modifier_asso_compte_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	// Normaliser $qui
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_modifier_asso_compte entry id=' . intval($id) . ' qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');

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
		$id_evenement = association_obtenir_evenement_contexte($id_compte, is_array($opt) ? $opt : array());
		if ($id_evenement > 0) {
			// Delegate decision to event-level authorization
			$res = (bool)autoriser('modifier', 'evenement', $id_evenement, $qui, $opt);
			association_debug_log('autoriser_modifier_asso_compte delegate to evenement modifier result=' . (int)$res, 'association_autorisation');
			return $res;
		}
	}

	return false;
}

function autoriser_creer_asso_compte_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	// Normaliser $qui
	$qui = association_normalize_qui($qui);
	association_debug_log('autoriser_creer_asso_compte entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true) . ' opt=' . var_export($opt, true), 'association_autorisation');

	// Admins can always create
	if ($qui['statut'] === '0minirezo') {
		association_debug_log('autoriser_creer_asso_compte allow admin', 'association_autorisation');
		return true;
	}

	// For editors, require an event context and delegate to event authorization
	if ($qui['statut'] === '1comite') {
		$id_evenement = association_obtenir_evenement_contexte(0, is_array($opt) ? $opt : array());
		if ($id_evenement <= 0) return false;
		return (bool)autoriser('modifier', 'evenement', $id_evenement, $qui, $opt);
	}

	return false;
}

function autoriser_assocompte_modifier_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	association_debug_log('autoriser_assocompte_modifier_dist wrapper for id=' . intval($id), 'association_autorisation');
	$res = autoriser_modifier_asso_compte_dist($faire, $type, $id, $qui, $opt);
	association_debug_log('autoriser_assocompte_modifier_dist result=' . (int)$res, 'association_autorisation');
	return $res;
}

function autoriser_assocompte_creer_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	association_debug_log('autoriser_assocompte_creer_dist wrapper for id=' . intval($id), 'association_autorisation');
	$res = autoriser_creer_asso_compte_dist($faire, $type, $id, $qui, $opt);
	association_debug_log('autoriser_assocompte_creer_dist result=' . (int)$res, 'association_autorisation');
	return $res;
}
