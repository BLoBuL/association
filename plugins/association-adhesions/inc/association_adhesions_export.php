<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Liste blanche des colonnes publiques de profil, jamais des secrets auteurs.
 */
function association_adhesions_export_colonnes($selection): array {
	if (!is_array($selection) || !$selection) {
		return [];
	}
	include_spip('inc/config');
	include_spip('inc/saisies');
	$disponibles = [
		'id_auteur' => _T('info_numero_abbreviation'),
		'nom' => _T('info_nom'),
		'email' => _T('email'),
		'statut' => _T('info_statut'),
	];
	$interdits = ['pass', 'htpass', 'alea_actuel', 'alea_futur', 'low_sec', 'cookie_oubli', 'prefs', 'login', 'webmestre'];
	$configuration = lire_config('inscription3', []);
	foreach (saisies_lister_par_nom(lire_config('champs_extras_spip_auteurs', []), false) as $champ => $saisie) {
		if (preg_match('/^[a-z][a-z0-9_]*$/D', $champ)
			&& !in_array($champ, $interdits, true)
			&& (($configuration[$champ . '_table'] ?? '') === 'on'
				|| ($configuration[$champ . '_nocreation_table'] ?? '') === 'on')) {
			$disponibles[$champ] = (string) ($saisie['options']['label'] ?? $champ);
		}
	}
	$colonnes = [];
	$trouver_table = charger_fonction('trouver_table', 'base');
	$description = $trouver_table('spip_auteurs');
	foreach ($selection as $champ => $valeur) {
		if (!isset($disponibles[$champ], $description['field'][$champ]) || !is_string($valeur) || !in_array($valeur, ['on', $champ], true)) {
			return [];
		}
		$colonnes[$champ] = $disponibles[$champ];
	}
	return $colonnes;
}
