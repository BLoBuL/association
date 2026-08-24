<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Fournit un exemple de participation à la page Notifications commune.
 */
function association_evenements_association_notification_exemple($flux) {
	if (($flux['args']['operation'] ?? '') !== 'activite') {
		return $flux;
	}
	$statut = (string) ($flux['args']['statut'] ?? '');
	$nombre_inscrits = max(1, (int) ($flux['args']['nombre_inscrits'] ?? 1));
	$payant = !empty($flux['args']['payant']);
	$where = 'statut=' . sql_quote($statut)
		. ($payant ? ' AND id_transaction >= 1' : ' AND id_transaction = 0')
		. ($nombre_inscrits === 1 ? ' AND nombre_inscrits = 1' : ' AND nombre_inscrits > 1');
	$flux['data'] = (int) sql_getfetsel('id_activite', 'spip_asso_activites', $where, '', 'id_activite DESC');
	return $flux;
}
