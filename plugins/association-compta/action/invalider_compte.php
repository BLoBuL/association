<?php

function action_invalider_compte_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_compte = $securiser_action();

	include_spip('inc/autoriser');
	if (!autoriser('modifier', 'asso_compte', intval($id_compte))) {
		return false;
	}

	if (intval($id_compte) > 0) {
		include_spip('inc/association_compta_ecritures');
		association_compta_ecriture_modifier((int) $id_compte, ['vu' => 0]);
	}
	// Redirection vers la page d'origine
	if ($redirect = _request('redirect')) {
		include_spip('inc/headers');

		$redirect = html_entity_decode($redirect, ENT_QUOTES, 'UTF-8'); // -> & au lieu de &amp;
		redirige_par_entete($redirect);
	} else {
		return true;
	}

}
