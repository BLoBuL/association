<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Vérifie les paramètres comptables du formulaire commun.
 */
function association_compta_configurer_verifier($config) {
	$erreurs = [];
	$erreur = false;
	if ($config !== 'comptabilite' && !empty($config)) {
		return $erreurs;
	}
	// Validation JJ/MM des champs exercice comptable (si présents dans le POST)
	$debut = trim((string) _request('exercice_comptable_debut'));
	$regex = '/^([0-9]{2})\/([0-9]{2})$/';
	if ($debut !== '') {
		if (!preg_match($regex, $debut, $m)) {
			$erreurs['exercice_comptable_debut'] = _T('association_config:erreur_exercice_comptable_format');
		} else {
			$j = intval($m[1]);
			$mo = intval($m[2]);
			if ($j < 1 || $j > 31 || $mo < 1 || $mo > 12) {
				$erreurs['exercice_comptable_debut'] = _T('association_config:erreur_exercice_comptable_jour_mois');
			}
		}
	}

	// TODO Ces vérifications sont à revoir, elles ne prennent pas en compte tous les cas de figures et ne place par les erreurs au bon endroit
	$ref_attribuee = [];
	// on verifie qu'il n'a pas deux fois la meme reference comptable en incluant celle des cotisations
	$ref_attribuee[_request('pc_cotisations')] = 0;
	if ((_request('dons') == 'on') and $ref_dons = _request('pc_dons')) {
		if (!array_key_exists($ref_dons, $ref_attribuee)) {
			$ref_attribuee[$ref_dons] = 0;
		} else {
			$erreur = true;
		}
	}

	if ((_request('ventes') == 'on' and $ref_ventes = _request('pc_ventes'))) {

		$ref_frais_envoi = _request('pc_frais_envoi');
		if (!array_key_exists($ref_ventes, $ref_attribuee)) {
			$ref_attribuee[$ref_ventes] = 0;
		} else {
			$erreur = true;
		}
		if ($ref_ventes != $ref_frais_envoi) {
			/* vente et frais_envoi peuvent etre associes a la meme reference comptable meme si c'est deconseille d'un point de vue comptable */
			if (!array_key_exists($ref_frais_envoi, $ref_attribuee)) {
				$ref_attribuee[$ref_frais_envoi] = 0;
			} else {
				$erreur = true;
			}
		}
	}

	if ((_request('prets') == 'on') and $ref_prets = _request('pc_prets')) {
		if (!array_key_exists($ref_prets, $ref_attribuee)) {
			$ref_attribuee[$ref_prets] = 0;
		} else {
			$erreur = true;
		}
	}

	if ((_request('activites') == 'on') and $ref_activites = _request('pc_activites')) {
		if (!array_key_exists($ref_activites, $ref_attribuee)) {
			$ref_attribuee[$ref_activites] = 0;
		} else {
			$erreur = true;
		}
	}
	if ($erreur) {
		$erreurs['message_erreur'] = _T('association_compta:erreur_configurer_association_titre') . '<br/>' . _T('association_compta:erreur_configurer_association_reference_multiple');
	} elseif (count($erreurs)) {
		// message générique si uniquement erreurs de format
		$erreurs['message_erreur'] = _T('association_compta:erreur_configurer_association_titre');
	}

	return $erreurs;
}
