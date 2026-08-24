<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_zones_adherent_normaliser($zones) {
	if (!is_array($zones)) {
		$zones = array($zones);
	}
	return array_values(array_unique(array_filter(array_map('intval', $zones))));
}

function association_auteur_zones_adherent_liees($zones, $id_auteur) {
	$zones = association_zones_adherent_normaliser($zones);
	$id_auteur = (int) $id_auteur;
	if (!$zones || !$id_auteur) {
		return array();
	}
	$lignes = sql_allfetsel(
		'id_zone',
		'spip_zones_liens',
		sql_in('id_zone', $zones) . " AND objet='auteur' AND id_objet=" . $id_auteur
	) ?: array();
	return array_values(array_unique(array_filter(array_map('intval', array_column($lignes, 'id_zone')))));
}

function association_privileges_auteur_lire($id_auteur) {
	$id_auteur = (int) $id_auteur;
	return $id_auteur ? (sql_fetsel(
		'id_auteur,prenom,nom_famille,statut,statut_interne,email',
		'spip_auteurs',
		'id_auteur=' . $id_auteur
	) ?: array()) : array();
}

function association_privileges_zones_lier($id_auteur, array $zones) {
	if (!$zones) {
		return;
	}
	include_spip('inc/autoriser');
	include_spip('action/editer_zone');
	autoriser_exception('affecterzones', 'auteur', $id_auteur);
	zone_lier($zones, 'auteur', $id_auteur);
	autoriser_exception('affecterzones', 'auteur', $id_auteur, false);
}

function association_privileges_zones_delier($id_auteur, array $zones) {
	if (!$zones) {
		return;
	}
	include_spip('inc/autoriser');
	include_spip('action/editer_zone');
	autoriser_exception('retirerzones', 'auteur', $id_auteur);
	zone_lier($zones, 'auteur', $id_auteur, 'del');
	autoriser_exception('retirerzones', 'auteur', $id_auteur, false);
}

function activer_privileges_adherent($id_auteur, $reinscription = null) {
	$auteur = association_privileges_auteur_lire($id_auteur);
	if (!$auteur) {
		return false;
	}
	$zones = association_zones_adherent_normaliser(lire_config('/association_metas/zone_adherent', array()));
	association_privileges_zones_lier((int) $id_auteur, $zones);
	include_spip('inc/association_communication_privileges');
	return association_communication_privileges_activer(
		$auteur,
		lire_config('/association_metas/liste_diffusion', array())
	);
}

function desactiver_privileges_adherent($id_auteur) {
	$auteur = association_privileges_auteur_lire($id_auteur);
	if (!$auteur) {
		return false;
	}
	$zones = association_zones_adherent_normaliser(lire_config('/association_metas/zone_adherent', array()));
	$zones_liees = association_auteur_zones_adherent_liees($zones, $id_auteur);
	association_privileges_zones_delier((int) $id_auteur, $zones_liees);
	include_spip('inc/association_communication_privileges');
	association_communication_privileges_desactiver($auteur);
	if (test_plugin_actif('gis')) {
		$id_gis = (int) sql_getfetsel(
			'G.id_gis',
			'spip_gis AS G LEFT JOIN spip_gis_liens AS T ON T.id_gis=G.id_gis',
			'T.id_objet=' . (int) $id_auteur . " AND T.objet='auteur'"
		);
		if ($id_gis) {
			include_spip('inc/fonctions/gis_auteur');
			gis_auteur($id_auteur, 'suppression', $id_gis);
		}
	}
	return true;
}

function verifier_privileges_adherent($id_auteur, $reinscription = null) {
	$auteur = association_privileges_auteur_lire($id_auteur);
	include_spip('inc/filtres');
	if (!$auteur || empty($auteur['email']) || !email_valide($auteur['email'])) {
		return true;
	}
	$zones = association_zones_adherent_normaliser(lire_config('/association_metas/zone_adherent', array()));
	$zones_liees = association_auteur_zones_adherent_liees($zones, $id_auteur);
	include_spip('inc/association_communication_privileges');
	association_communication_privileges_verifier(
		$auteur,
		lire_config('/association_metas/liste_diffusion', array())
	);

	$statut_interne = (string) ($auteur['statut_interne'] ?? '');
	$statut_spip = (string) ($auteur['statut'] ?? '');
	if ($statut_interne === 'ok' && $statut_spip !== '5poubelle') {
		association_privileges_zones_lier((int) $id_auteur, array_values(array_diff($zones, $zones_liees)));
	} elseif (in_array($statut_interne, array('prospect', 'echu', 'relance', 'sorti'), true) || $statut_spip === '5poubelle') {
		association_privileges_zones_delier((int) $id_auteur, $zones_liees);
	}

	if (test_plugin_actif('gis')) {
		include_spip('inc/fonctions/gis_auteur');
		$id_gis = (int) sql_getfetsel(
			'G.id_gis',
			'spip_gis AS G LEFT JOIN spip_gis_liens AS T ON T.id_gis=G.id_gis',
			'T.id_objet=' . (int) $id_auteur . " AND T.objet='auteur'"
		);
		if (!$id_gis && $statut_interne === 'ok') {
			gis_auteur($id_auteur, 'creation');
		} elseif ($id_gis && $statut_interne !== 'ok') {
			gis_auteur($id_auteur, 'suppression', $id_gis);
		}
	}
	return true;
}
