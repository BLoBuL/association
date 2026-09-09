<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_action_activites_contexte($inutile = '') {
	$id_evenement = (int) _request('id_evenement');
	$choix = (string) (_request('act') ?: _request('actions_inscription'));
	$ids = (array) _request('selecteur_id_activite');
	if ($id = (int) _request('id_activite')) {
		$ids[] = $id;
	}
	$ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

	$definitions = [
		'valider_inscription' => ['valider_activites', 'activite_titre_action_valider_inscriptions', 'activite_chapeau_action_valider_inscriptions', true, ''],
		'devalider_inscription' => ['devalider_activites', 'activite_titre_action_devalider_inscriptions', 'activite_chapeau_action_devalider_inscriptions', true, ''],
		'mettre_en_attente_inscription' => ['mettre_en_attente_activites', 'activite_titre_action_mettre_en_attente_inscriptions', 'activite_chapeau_action_mettre_en_attente_inscriptions', true, ''],
		'desinscrire_inscription' => ['desinscrire_activites', 'activite_titre_action_desinscriptions', 'activite_chapeau_action_desinscriptions', true, ''],
		'reactiver_preinscription_inscription' => ['reactiver_activites', 'activite_titre_action_reactiver_preinscriptions', 'activite_chapeau_action_reactiver_preinscriptions', true, 'preinscrit'],
		'reactiver_liste_attente_inscription' => ['reactiver_activites', 'activite_titre_action_reactiver_liste_attentes', 'activite_chapeau_action_reactiver_liste_attentes', true, 'liste_attente'],
		'supprimer_inscription' => ['supprimer_activites', 'activite_titre_action_supprimer_inscriptions', 'activite_chapeau_action_supprimer_inscriptions', false, ''],
	];
	if (!$id_evenement || !$ids || !isset($definitions[$choix])) {
		return ['valide' => false];
	}
	$ids = array_column(sql_allfetsel('id_activite', 'spip_asso_activites', ['id_evenement=' . $id_evenement, sql_in('id_activite', $ids)]), 'id_activite');
	if (!$ids) {
		return ['valide' => false];
	}
	[$action, $titre, $chapo, $notifier, $reactivation] = $definitions[$choix];
	include_spip('inc/securiser_action');
	$url_retour = generer_url_ecrire('voir_activites', 'id=' . $id_evenement);
	return [
		'valide' => true,
		'id_evenement' => $id_evenement,
		'ids' => $ids,
		'action_activites' => $action,
		'titre' => _T('association_evenements:' . $titre),
		'chapo' => _T('association_evenements:' . $chapo),
		'notifier' => $notifier,
		'type_reactivation' => $reactivation,
		'url_action' => generer_action_auteur('gerer_activites', count($ids), $url_retour),
	];
}
