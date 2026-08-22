<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Prépare exclusivement le contexte de la liste privée des activités.
 * La présentation reste dans les squelettes.
 */
function association_activites_contexte($id_auteur, $orientation = 'futur', $annee = '', $tripayant = '') {
	$id_auteur = (int) $id_auteur;
	$orientation = $orientation === 'passe' ? 'passe' : 'futur';
	$droit = droit_auteur_evenements($id_auteur);
	$autorise = in_array($droit[1] ?? '', array('restreint', 'complet', 'restreint_wrong'), true)
		&& test_plugin_actif('agenda');
	if (!$autorise) {
		return array('autorise' => false);
	}

	$where = array("statut='publie'", "inscription='1'");
	if (($GLOBALS['association_metas']['meta_cfg_event_inscription_sur_repetition'] ?? '') !== 'source_et_repetition') {
		$where[] = "id_evenement_source='0'";
	}
	if (!empty($droit[2]['condition'])) {
		$where[] = $droit[2]['condition'];
	}
	$ids = array_column(sql_allfetsel('id_evenement', 'spip_evenements', $where, '', 'date_fin'), 'id_evenement');

	if ($orientation === 'passe') {
		$annee = $annee ?: date('Y');
		$date_debut = $annee === 'toutes' ? '1970-01-01 00:00:00' : $annee . '-01-01 00:00:00';
		$date_fin = $annee === 'toutes' || $annee === date('Y') ? date('Y-m-d H:i:s') : $annee . '-12-31 23:59:59';
	} else {
		$annee = '';
		$date_debut = date('Y-m-d H:i:s');
		$date_fin = '2040-01-01 00:00:00';
	}

	return array(
		'autorise' => true,
		'affichage' => !empty($droit[2]['affichage']),
		'selection_id_evenements' => $ids ?: array(0),
		'orientation' => $orientation,
		'annee' => $annee,
		'date_debut' => $date_debut,
		'date_fin' => $date_fin,
		'tripayant' => $tripayant,
	);
}
