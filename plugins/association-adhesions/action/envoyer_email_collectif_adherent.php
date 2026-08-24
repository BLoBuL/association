<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function action_envoyer_email_collectif_adherent() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$count = (int) $securiser_action();
	$selection = _request('selecteur_adherent');
	$selection = is_array($selection) ? $selection : ($selection ? array($selection) : array());
	$ids_auteurs = array_values(array_filter(array_unique(array_map('intval', $selection))));
	if ($count === 0) {
		$ids_auteurs = array();
	}

	include_spip('inc/email_collectif');
	$destinataires = association_email_collectif_resoudre_destinataires($ids_auteurs, array());
	$date = date('Y-m-d H:i:s');
	association_communication_mailshot_creer(
		(string) _request('sujet'),
		(string) _request('html'),
		$destinataires,
		array('date' => $date, 'date_start' => $date)
	);
}
