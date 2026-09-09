<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Applique la configuration globale aux champs d'un evenement neuf.
 *
 * @param array $valeurs Contexte charge par le formulaire evenement_edit
 * @param string|int $id_evenement Identifiant de l'evenement ou "new"
 * @param array|null $metas Configuration globale a utiliser
 * @return array
 */
function association_evenement_appliquer_defauts(array $valeurs, $id_evenement = 'new', $metas = null) {
	if (intval($id_evenement)) {
		return $valeurs;
	}

	if (!is_array($metas)) {
		$metas = isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas'])
			? $GLOBALS['association_metas']
			: [];
	}

	if (array_key_exists('meta_cfg_event_inscription', $metas)) {
		$valeurs['inscription'] = in_array($metas['meta_cfg_event_inscription'], ['oui', '1', 1, true], true) ? 1 : 0;
	}
	if (array_key_exists('meta_cfg_event_type_inscrits_evenement', $metas)) {
		$valeurs['type_inscrits_evenement'] = $metas['meta_cfg_event_type_inscrits_evenement'] === 'only_strict'
			? 'strict'
			: $metas['meta_cfg_event_type_inscrits_evenement'];
	}
	if (array_key_exists('meta_cfg_event_afficher_liste_inscrits', $metas)) {
		$afficher = $metas['meta_cfg_event_afficher_liste_inscrits'];
		$valeurs['afficher_liste_inscrits'] = $afficher === 'toujours' ? '1' : ($afficher === 'jamais' ? '0' : $afficher);
	}

	$correspondances = [
		'meta_cfg_event_ouverture_differe' => 'ouverture_differe',
		'meta_cfg_event_inscription_deadline' => 'fermeture_inscription',
		'meta_cfg_event_validation' => 'validation',
		'meta_cfg_event_accompagnants' => 'accompagnants',
		'meta_cfg_event_limite_nb_accompagnants' => 'limite_places',
		'meta_cfg_event_invites' => 'invites',
		'meta_cfg_event_file_attente' => 'file_attentes',
		'meta_cfg_event_validation_auto' => 'validation_attente_automatique',
		'meta_cfg_event_limite_places_file_attente' => 'attentes',
		'message_condition_inscription_defaut' => 'message_condition_inscription',
	];
	foreach ($correspondances as $meta => $champ) {
		if (array_key_exists($meta, $metas)) {
			$valeurs[$champ] = $metas[$meta];
		}
	}

	if (array_key_exists('meta_cfg_event_condition_inscription', $metas)) {
		$valeurs['condition_inscription'] = in_array(
			$metas['meta_cfg_event_condition_inscription'],
			['toujours', 'oui'],
			true
		) ? 'oui' : 'non';
	}

	return $valeurs;
}
