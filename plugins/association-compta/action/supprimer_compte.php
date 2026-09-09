<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function action_supprimer_compte_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_compte = $securiser_action();

	include_spip('inc/autoriser');
	if (!autoriser('modifier', 'asso_compte', intval($id_compte))) {
		return false;
	}

	if (intval($id_compte) > 0) {
		include_spip('inc/association_compta_ecritures');
		association_compta_ecriture_supprimer((int) $id_compte);
	}

}
