<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * @deprecated Utiliser charger_fonction() pour cette action.
 */
function action_envoyer_email_collectif_adherent() {
	return action_envoyer_email_collectif_adherent_dist();
}

function action_envoyer_email_collectif_adherent_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$count = (int) $securiser_action();
	include_spip('inc/autoriser');
	include_spip('inc/association_capacites');
	if (!autoriser('menu', 'adherents') || !association_plugin_actif('association_communication')) {
		return false;
	}
	$selection = _request('selecteur_adherent');
	$selection = is_array($selection) ? $selection : ($selection ? [$selection] : []);
	$ids_auteurs = array_values(array_filter(array_unique(array_map('intval', $selection))));
	if ($count === 0) {
		$ids_auteurs = [];
	}

	include_spip('inc/email_collectif');
	$destinataires = association_email_collectif_resoudre_destinataires($ids_auteurs, []);
	$date = date('Y-m-d H:i:s');
	include_spip('inc/association_adhesions_integrations');
	association_adhesions_mailshot_creer(
		(string) _request('sujet'),
		(string) _request('html'),
		$destinataires,
		['date' => $date, 'date_start' => $date]
	);
}
