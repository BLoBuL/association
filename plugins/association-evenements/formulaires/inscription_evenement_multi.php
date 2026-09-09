<?php

/*\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\*/
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/autoriser');
include_spip('inc/saisies');
include_spip('inc/filtres');
include_spip('formulaires/inc/inscription_evenement');
include_spip('formulaires/inc/inscription_evenement_saisies');
include_spip('inc/fonctions/activite_enregistrement_calculator');
include_spip('formulaires/inc/inscription_evenement_backend');

function ie_inscription_multi_ids($id_evenement = '', $id_activite = '') {
	return ie_resoudre_ids_inscription($id_evenement, $id_activite);
}

function ie_inscription_multi_mode() {
	return 'multi_prive';
}

/**
 * Compatibilité: certaines integrations attendent encore une fonction *_saisies.
 * On retourne les saisies produites par le backend commun.
 */
function formulaires_inscription_evenement_multi_saisies($id_evenement = '', $id_activite = '') {
	[$id_evenement, $id_activite] = ie_inscription_multi_ids($id_evenement, $id_activite);
	$contexte = ie_charger_commons(ie_inscription_multi_mode(), $id_evenement, $id_activite);
	$s = $contexte['_saisies'] ?? null;
	if (!is_array($s) || !isset($s['options']) || count(array_filter(array_keys($s), 'is_int')) == 0) {
		association_evenements_inscription_debug('saisies_multi_invalides', [
			'type' => gettype($s),
			'nombre' => is_array($s) ? count($s) : 0,
		]);
	}
	return isset($contexte['_saisies']) && is_array($contexte['_saisies']) ? $contexte['_saisies'] : [];
}

function formulaires_inscription_evenement_multi_charger_dist($id_evenement = '', $id_activite = '') {
	[$id_evenement, $id_activite] = ie_inscription_multi_ids($id_evenement, $id_activite);
	return ie_charger_commons(ie_inscription_multi_mode(), $id_evenement, $id_activite);
}
// Si inscriptions pas autorisees, retourner une chaine d'avertissement

function formulaires_inscription_evenement_multi_verifier_1_dist($id_evenement = '', $id_activite = '') {
	[$id_evenement, $id_activite] = ie_inscription_multi_ids($id_evenement, $id_activite);
	return ie_verifier_commons(ie_inscription_multi_mode(), $id_evenement, $id_activite, null, 1);
}

function formulaires_inscription_evenement_multi_verifier_2_dist($id_evenement = '', $id_activite = '') {
	[$id_evenement, $id_activite] = ie_inscription_multi_ids($id_evenement, $id_activite);
	return ie_verifier_commons(ie_inscription_multi_mode(), $id_evenement, $id_activite, null, 2);
}

function formulaires_inscription_evenement_multi_verifier_3_dist($id_evenement = '', $id_activite = '') {
	[$id_evenement, $id_activite] = ie_inscription_multi_ids($id_evenement, $id_activite);
	return ie_verifier_commons(ie_inscription_multi_mode(), $id_evenement, $id_activite, null, 3);
}

function formulaires_inscription_evenement_multi_verifier_4_dist($id_evenement = '', $id_activite = '') {
	[$id_evenement, $id_activite] = ie_inscription_multi_ids($id_evenement, $id_activite);
	return ie_verifier_commons(ie_inscription_multi_mode(), $id_evenement, $id_activite, null, 4);
}

function formulaires_inscription_evenement_multi_traiter_dist($id_evenement = '', $id_activite = '') {
	[$id_evenement, $id_activite] = ie_inscription_multi_ids($id_evenement, $id_activite);
	return ie_traiter_commons(ie_inscription_multi_mode(), $id_evenement, $id_activite, null);
}
