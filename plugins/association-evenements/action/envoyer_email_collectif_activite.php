<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function action_envoyer_email_collectif_activite_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$securiser_action();
	include_spip('inc/autoriser');
	include_spip('inc/association_capacites');
	$id_evenement = (int) _request('id_evenement');
	if (!autoriser('gererinscriptions', 'evenement', $id_evenement)
		|| !association_capacite_disponible('campagnes_email')) {
		return false;
	}
	$selection = _request('array_activites');
	$ids = array_values(array_filter(array_map('intval', is_array($selection) ? $selection : explode(',', (string) $selection))));
	if (!$ids) {
		return false;
	}
	// Ne lire que les inscriptions de l'événement autorisé.
	$emails = array_column(sql_allfetsel('email_inscrit', 'spip_asso_activites', [
		'id_evenement=' . $id_evenement, sql_in('id_activite', $ids),
	]) ?: [], 'email_inscrit');
	if (!$emails) {
		return false;
	}
	include_spip('inc/association_evenements_responsables');
	$responsables = association_evenements_responsables_ids($id_evenement);
	if ($responsables) {
		$emails = array_merge($emails, array_column(sql_allfetsel('email', 'spip_auteurs', sql_in('id_auteur', $responsables)) ?: [], 'email'));
	}
	$resultat = association_programmer_campagne([
		'sujet' => (string) _request('sujet'),
		'html' => (string) _request('html'),
		'destinataires' => $emails,
		'options' => ['id_evenement' => $id_evenement],
	]);
	return (int) $resultat['id_mailshot'];
}

/**
 * @deprecated Utiliser charger_fonction('envoyer_email_collectif_activite', 'action').
 */
function action_envoyer_email_collectif_activite() {
	return action_envoyer_email_collectif_activite_dist();
}
