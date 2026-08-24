<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Critère compatible pour retrouver l'écriture d'un prêt.
 *
 * Les nouvelles écritures utilisent le lien objet natif. Le second terme
 * conserve l'accès aux écritures historiques identifiées par id_journal.
 */
function association_prets_compte_options() {
	return array(
		'legacy_id_journal' => true,
		'legacy_justification_prefix' => _T('association_prets:pret_nd'),
	);
}

function association_prets_compte_lire($id_pret) {
	include_spip('inc/association_compta_ecritures');
	$ecritures = association_compta_ecritures_objet_lister('pret', $id_pret, association_prets_compte_options() + array(
		'champs' => 'id_compte,journal,recette,id_auteur,id_objet,objet',
	));
	return $ecritures[0] ?? array();
}

function association_prets_compte_enregistrer($id_pret, array $donnees) {
	$id_pret = (int) $id_pret;
	$montant = (float) ($donnees['recette'] ?? 0);
	$existant = association_prets_compte_lire($id_pret);
	include_spip('inc/association_compta_ecritures');
	if ($montant <= 0) {
		return empty($existant['id_compte']) || association_compta_ecriture_supprimer((int) $existant['id_compte']);
	}
	$donnees['objet'] = 'pret';
	$donnees['id_objet'] = $id_pret;
	return !empty($existant['id_compte'])
		? (bool) association_compta_ecriture_modifier((int) $existant['id_compte'], $donnees)
		: (bool) association_compta_ecriture_creer($donnees);
}

function association_prets_compte_supprimer($id_pret) {
	include_spip('inc/association_compta_ecritures');
	return association_compta_ecritures_objet_supprimer('pret', $id_pret, association_prets_compte_options());
}

/**
 * Aligne le statut d'une ressource sur l'existence d'un prêt non restitué.
 */
function association_prets_synchroniser_statut_ressource($id_ressource) {
	$id_ressource = intval($id_ressource);
	if ($id_ressource <= 0) {
		return false;
	}
	$actifs = sql_countsel(
		'spip_asso_prets',
		array(
			'id_ressource=' . $id_ressource,
			"(date_retour IS NULL OR date_retour='' OR date_retour='0000-00-00')",
		)
	);
	return sql_updateq(
		'spip_asso_ressources',
		array('statut' => intval($actifs) > 0 ? 'reserve' : 'ok'),
		'id_ressource=' . $id_ressource
	) !== false;
}
