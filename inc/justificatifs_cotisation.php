<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Retourne l'etat synthétique des justificatifs liés à une cotisation.
 */
function association_justificatifs_cotisation_etat($id_compte) {
    $id_compte = intval($id_compte);
    $total = intval(sql_countsel('spip_documents_liens', "objet='compte' AND id_objet=$id_compte"));
    $valides = intval(sql_countsel('spip_documents_liens', "objet='compte' AND id_objet=$id_compte AND vu='oui'"));
    return array(
        'total' => $total,
        'valides' => $valides,
        'complet' => ($total >= 2),
        'controle' => ($total >= 2 && $valides === $total),
    );
}

/**
 * Marque tous les justificatifs d'une cotisation comme controles ou a revoir.
 * Aucun statut de cotisation ni paiement n'est modifie ici.
 */
function association_justificatifs_cotisation_marquer($id_compte, $valide = true) {
    $id_compte = intval($id_compte);
    if ($id_compte < 1) return array('ok' => false, 'message' => _T('association:justificatifs_cotisation_introuvables'));

    $etat = association_justificatifs_cotisation_etat($id_compte);
    if ($valide && !$etat['complet']) {
        return array('ok' => false, 'message' => _T('association:justificatifs_cotisation_incomplets'));
    }
    if ($etat['total'] < 1) {
        return array('ok' => false, 'message' => _T('association:justificatifs_cotisation_introuvables'));
    }

    sql_updateq(
        'spip_documents_liens',
        array('vu' => $valide ? 'oui' : 'non'),
        "objet='compte' AND id_objet=$id_compte"
    );
    include_spip('inc/invalideur');
    suivre_invalideur("id='asso_compte/$id_compte'");
    return array('ok' => true, 'message' => $valide ? _T('association:justificatifs_cotisation_valides') : _T('association:justificatifs_cotisation_a_revoir'));
}

/**
 * Supprime définitivement les justificatifs propres à une cotisation.
 *
 * La suppression est refusée si un document est également lié à un autre
 * objet, afin de ne pas effacer un fichier encore utilisé ailleurs.
 */
function association_justificatifs_cotisation_supprimer($id_compte, $id_document = 0) {
    $id_compte = intval($id_compte);
    $id_document = intval($id_document);
    $document_cible = $id_document;
    if ($id_compte < 1) {
        return array('ok' => false, 'message' => _T('association:justificatifs_cotisation_introuvables'));
    }

    $condition = 'objet=' . sql_quote('compte') . ' AND id_objet=' . $id_compte;
    if ($id_document > 0) {
        $condition .= ' AND id_document=' . $id_document;
    }
    $documents = array_column(
        sql_allfetsel(
            'id_document',
            'spip_documents_liens',
            $condition
        ),
        'id_document'
    );
    $documents = array_values(array_unique(array_map('intval', $documents)));
    if (!$documents) {
        return array('ok' => false, 'message' => _T('association:justificatifs_cotisation_introuvables'));
    }

    include_spip('inc/autoriser');
    foreach ($documents as $document_id) {
        $autres_liens = 'id_document=' . $document_id
            . ' AND NOT (objet=' . sql_quote('compte') . ' AND id_objet=' . $id_compte . ')';
        if (sql_countsel('spip_documents_liens', $autres_liens)) {
            return array('ok' => false, 'message' => _T('association:justificatifs_suppression_document_partage'));
        }
        if (!autoriser('supprimer', 'document', $document_id)) {
            return array('ok' => false, 'message' => _T('association:justificatifs_suppression_interdite'));
        }
    }

    include_spip('action/dissocier_document');
    include_spip('action/supprimer_document');
    foreach ($documents as $document_id) {
        supprimer_lien_document($document_id, 'compte', $id_compte, false, false);
        if (!action_supprimer_document_dist($document_id)) {
            return array('ok' => false, 'message' => _T('association:justificatifs_suppression_echec'));
        }
    }

    return array(
        'ok' => true,
        'message' => _T($document_cible
            ? 'association:justificatif_suppression_reussie'
            : 'association:justificatifs_suppression_reussie')
    );
}
