<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Fournit à Communication les exemples de prévisualisation d'Adhésions.
 */
function association_adhesions_association_notification_exemple($flux) {
	$operation = (string) ($flux['args']['operation'] ?? '');
	if ($operation === 'types_adherents') {
		$types = [];
		foreach ((array) lire_config('champs_extras_spip_auteurs', []) as $saisie) {
			if (($saisie['options']['nom'] ?? '') === 'radio_type_adherent') {
				$types = saisies_chaine2tableau($saisie['options']['datas'] ?? []);
				break;
			}
		}
		$flux['data'] = $types ?: ['adherent' => _T('association_adhesions:cotisation_adherent')];
		return $flux;
	}

	if ($operation !== 'cotisation') {
		return $flux;
	}
	$type = (string) ($flux['args']['type_adherent'] ?? 'adherent');
	$id_auteur = $type !== 'adherent'
		? (int) sql_getfetsel('id_auteur', 'spip_auteurs', 'radio_type_adherent=' . sql_quote($type), '', 'id_auteur DESC')
		: (int) sql_getfetsel('id_auteur', 'spip_auteurs', "statut='6forum'", '', 'id_auteur DESC');
	$cotisation = $id_auteur
		? sql_fetsel('*', 'spip_asso_cotisations', 'id_auteur=' . $id_auteur, '', 'id_cotisation DESC')
		: [];
	if ($cotisation && !empty($cotisation['id_compte'])) {
		include_spip('inc/cotisations_stockage');
		$cotisation = association_cotisation_lire_par_compte((int) $cotisation['id_compte']) ?: $cotisation;
	}
	$flux['data'] = $cotisation ?: [];
	return $flux;
}
