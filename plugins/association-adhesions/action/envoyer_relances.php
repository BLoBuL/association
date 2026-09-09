<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * @deprecated Utiliser charger_fonction() pour cette action.
 */
function action_envoyer_relances() {
	return action_envoyer_relances_dist();
}

function action_envoyer_relances_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$securiser_action();
	include_spip('inc/autoriser');
	include_spip('inc/association_capacites');
	if (!autoriser('menu', 'adherents') || !association_plugin_actif('association_communication')) {
		return false;
	}
	$selection = _request('statut');
	$selection = is_array($selection) ? $selection : [];
	$html = recuperer_fond('notifications/email_collectif_adherent', [
		'titre' => (string) _request('titre'),
		'chapeau' => (string) _request('chapeau'),
		'texte' => (string) _request('texte'),
		'ajouter_information_paiement' => _request('ajouter_information_paiement'),
	]);

	include_spip('inc/email_collectif');
	$destinataires = association_email_collectif_resoudre_destinataires(array_keys($selection), []);
	$date = date('Y-m-d H:i:s');
	include_spip('inc/association_adhesions_integrations');
	association_adhesions_mailshot_creer(
		(string) _request('sujet'),
		$html,
		$destinataires,
		['date' => $date, 'date_start' => $date]
	);
}
