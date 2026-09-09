<?php

/**
 * Action pour traiter les opérations sur les comptes
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function action_traiter_comptes_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	include_spip('inc/autoriser');

	$comptes = _request('selecteur_comptes');
	$action = _request('action_masse');
	include_spip('inc/association_compta_ecritures');

	if (is_array($comptes) && count($comptes) > 0) {
		foreach ($comptes as $id_compte) {
			$id_compte = intval($id_compte);
			if ($id_compte > 0 && autoriser('modifier', 'asso_compte', $id_compte)) {
				switch ($action) {
					case 'valider':
						association_compta_ecriture_modifier($id_compte, ['vu' => 1]);
						break;
					case 'invalider':
						association_compta_ecriture_modifier($id_compte, ['vu' => 0]);
						break;
					case 'supprimer':
						association_compta_ecriture_supprimer($id_compte);
						break;
				}
			}
		}
	}

	include_spip('inc/headers');
	redirige_url_ecrire('comptes');
}
