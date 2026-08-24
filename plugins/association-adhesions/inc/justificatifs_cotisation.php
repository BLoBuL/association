<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_justificatifs_cotisation_identifiants($id_compte) {
	$id_compte = (int) $id_compte;
	$id_cotisation = $id_compte
		? (int) sql_getfetsel('id_cotisation', 'spip_asso_cotisations', 'id_compte=' . $id_compte)
		: 0;
	return array('id_compte' => $id_compte, 'id_cotisation' => $id_cotisation);
}

function association_justificatifs_cotisation_condition($id_compte) {
	$ids = association_justificatifs_cotisation_identifiants($id_compte);
	$conditions = array();
	if ($ids['id_cotisation']) {
		$conditions[] = "(objet='cotisation' AND id_objet=" . $ids['id_cotisation'] . ')';
	}
	if ($ids['id_compte']) {
		$conditions[] = "(objet='compte' AND id_objet=" . $ids['id_compte'] . ')';
	}
	return $conditions ? '(' . implode(' OR ', $conditions) . ')' : '(0=1)';
}

function association_justificatifs_cotisation_etat($id_compte) {
	$condition = association_justificatifs_cotisation_condition($id_compte);
	$total = (int) sql_countsel('spip_documents_liens', $condition);
	$valides = (int) sql_countsel('spip_documents_liens', $condition . " AND vu='oui'");
	return array(
		'total' => $total,
		'valides' => $valides,
		'complet' => $total >= 2,
		'controle' => $total >= 2 && $valides === $total,
	);
}

function association_justificatifs_cotisation_marquer($id_compte, $valide = true) {
	$id_compte = (int) $id_compte;
	if ($id_compte < 1) {
		return array('ok' => false, 'message' => _T('association_adhesions:justificatifs_cotisation_introuvables'));
	}
	$etat = association_justificatifs_cotisation_etat($id_compte);
	if ($valide && !$etat['complet']) {
		return array('ok' => false, 'message' => _T('association_adhesions:justificatifs_cotisation_incomplets'));
	}
	if (!$etat['total']) {
		return array('ok' => false, 'message' => _T('association_adhesions:justificatifs_cotisation_introuvables'));
	}
	sql_updateq(
		'spip_documents_liens',
		array('vu' => $valide ? 'oui' : 'non'),
		association_justificatifs_cotisation_condition($id_compte)
	);
	include_spip('inc/invalideur');
	suivre_invalideur("id='asso_cotisation/" . association_justificatifs_cotisation_identifiants($id_compte)['id_cotisation'] . "'");
	return array(
		'ok' => true,
		'message' => $valide
			? _T('association_adhesions:justificatifs_cotisation_valides')
			: _T('association_adhesions:justificatifs_cotisation_a_revoir'),
	);
}

function association_justificatifs_cotisation_supprimer($id_compte, $id_document = 0) {
	$id_compte = (int) $id_compte;
	$id_document = (int) $id_document;
	$ids = association_justificatifs_cotisation_identifiants($id_compte);
	if ($id_compte < 1 || !$ids['id_cotisation']) {
		return array('ok' => false, 'message' => _T('association_adhesions:justificatifs_cotisation_introuvables'));
	}
	$condition = association_justificatifs_cotisation_condition($id_compte);
	if ($id_document) {
		$condition .= ' AND id_document=' . $id_document;
	}
	$documents = array_values(array_unique(array_map('intval', array_column(
		sql_allfetsel('id_document', 'spip_documents_liens', $condition) ?: array(),
		'id_document'
	))));
	if (!$documents) {
		return array('ok' => false, 'message' => _T('association_adhesions:justificatifs_cotisation_introuvables'));
	}
	include_spip('inc/autoriser');
	foreach ($documents as $document_id) {
		$autres_liens = 'id_document=' . $document_id . ' AND NOT ' . association_justificatifs_cotisation_condition($id_compte);
		if (sql_countsel('spip_documents_liens', $autres_liens)) {
			return array('ok' => false, 'message' => _T('association_adhesions:justificatifs_suppression_document_partage'));
		}
		if (!autoriser('modifier', 'document', $document_id)) {
			return array('ok' => false, 'message' => _T('association_adhesions:justificatifs_suppression_interdite'));
		}
	}
	include_spip('action/dissocier_document');
	include_spip('action/supprimer_document');
	foreach ($documents as $document_id) {
		supprimer_lien_document($document_id, 'cotisation', $ids['id_cotisation'], false, false);
		supprimer_lien_document($document_id, 'compte', $id_compte, false, false);
		if (!action_supprimer_document_dist($document_id)) {
			include_spip('action/editer_liens');
			objet_associer(array('document' => $document_id), array('cotisation' => $ids['id_cotisation']));
			return array('ok' => false, 'message' => _T('association_adhesions:justificatifs_suppression_echec'));
		}
	}
	return array(
		'ok' => true,
		'message' => _T($id_document
			? 'association_adhesions:justificatif_suppression_reussie'
			: 'association_adhesions:justificatifs_suppression_reussie'),
	);
}
