<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Catalogue exhaustif des emails produits par le parcours d'adhesion.
 * Cette structure ne declenche aucun rendu, job ou envoi.
 */
function notifications_cotisations_audit_catalogue() {
    return array(
        array('id' => 'attente_paiement', 'label' => 'Instructions de paiement', 'template' => 'notifications/cotisation-attente_paiement', 'sujet_type' => 'attente_paiement', 'audience' => 'Adherent', 'declencheur' => 'Cotisation payante en attente', 'file' => true),
        array('id' => 'validation_pre', 'label' => 'Validation avant paiement', 'template' => 'notifications/cotisation-validation_pre-paiement', 'sujet_type' => 'validation_pre-paiement', 'audience' => 'Adherent', 'declencheur' => 'Validation manuelle pre-paiement', 'file' => true),
        array('id' => 'validation_post', 'label' => 'Validation apres paiement', 'template' => 'notifications/cotisation-validation_post-paiement', 'sujet_type' => 'validation_post-paiement', 'audience' => 'Adherent', 'declencheur' => 'Validation manuelle post-paiement', 'file' => true),
        array('id' => 'activation', 'label' => 'Activation de l\'adhesion', 'template' => 'notifications/cotisation-activation', 'sujet_type' => 'activation', 'audience' => 'Adherent', 'declencheur' => 'Activation, y compris cotisation gratuite', 'file' => true),
        array('id' => 'attente_admin', 'label' => 'Nouvelle cotisation a encaisser', 'template' => 'notifications/cotisation-attente_admin', 'sujet_type' => 'attente_admin', 'audience' => 'Tresorerie', 'declencheur' => 'Paiement hors ligne en attente', 'file' => true, 'admin' => true),
        array('id' => 'demande_admin', 'label' => 'Cotisation a valider', 'template' => 'notifications/cotisation-demande_admin', 'sujet_type' => 'demande_admin', 'audience' => 'Tresorerie', 'declencheur' => 'Validation manuelle demandee', 'file' => true, 'admin' => true),
        array('id' => 'encaissement_admin', 'label' => 'Paiement encaisse', 'template' => 'notifications/cotisation-encaissement_admin', 'sujet_type' => 'encaissement_admin', 'audience' => 'Tresorerie', 'declencheur' => 'Encaissement confirme', 'file' => true, 'admin' => true),
        array('id' => 'echeance_preventive', 'label' => 'Rappel avant echeance', 'template' => 'notifications/notification_echeances_adherent', 'sujet_type' => 'echeance_preventive', 'audience' => 'Adherent', 'declencheur' => 'Tache planifiee avant echeance', 'file' => true),
        array('id' => 'echeance_echu', 'label' => 'Adhesion echue', 'template' => 'notifications/notification_echeances_adherent_echu', 'sujet_type' => 'echeance_echu', 'audience' => 'Adherent', 'declencheur' => 'Tache planifiee apres echeance', 'file' => true),
        array('id' => 'recu', 'label' => 'Recu d\'encaissement', 'template' => 'notifications/recu_encaissement_adhesion', 'sujet_cle' => 'association:email_recu_encaissement_adhesion_sujet', 'audience' => 'Adherent (+ BCC configure)', 'declencheur' => 'Encaissement payant uniquement', 'file' => true, 'option' => 'meta_cfg_envoi_recu_paiement_adhesion'),
        array('id' => 'justificatifs_a_revoir', 'label' => 'Justificatifs a revoir', 'template' => 'notifications/cotisation-justificatifs-a-revoir', 'sujet_type' => 'justificatifs-a-revoir', 'audience' => 'Adherent', 'declencheur' => 'Action BO A revoir uniquement', 'file' => true),
    );
}

function notifications_cotisations_audit_template_existe($template) {
    if (function_exists('find_in_path') && find_in_path($template . '.html')) return true;
    return is_file(dirname(__DIR__) . '/' . $template . '.html');
}

function notifications_cotisations_audit($catalogue = null) {
    include_spip('inc/cotisations');
    $catalogue = is_array($catalogue) ? $catalogue : notifications_cotisations_audit_catalogue();
    $tresoriers = function_exists('obtenir_emails_tresoriers') ? obtenir_emails_tresoriers() : false;
    $nb_tresoriers = is_array($tresoriers) ? count($tresoriers) : 0;
    $resultats = array();

    foreach ($catalogue as $scenario) {
        $problemes = array();
        $template_ok = notifications_cotisations_audit_template_existe($scenario['template']);
        if (!$template_ok) $problemes[] = 'Gabarit introuvable';

        $contexte = array('nom_adherent' => 'Adherent test', 'id_compte' => 1, 'montant' => 10, 'label_cotisation' => 'Cotisation test', 'date_validite' => '31/12/2099');
        if (!empty($scenario['sujet_type'])) {
            $sujet = notifications_cotisation_trouver_sujet($scenario['sujet_type'], $contexte);
        } else {
            $sujet = _T($scenario['sujet_cle'], array('numero_recu' => 'TEST'));
            if ($sujet === $scenario['sujet_cle']) $sujet = '';
        }
        if (trim((string)$sujet) === '') $problemes[] = 'Sujet introuvable';

        $destinataires = 'Adresse du compte (dynamique)';
        if (!empty($scenario['admin'])) {
            $destinataires = $nb_tresoriers . ' adresse(s) de tresorerie valide(s)';
            if (!$nb_tresoriers) $problemes[] = 'Aucun destinataire de tresorerie valide';
        }

        $actif = true;
        if (!empty($scenario['option'])) {
            $actif = (($GLOBALS['association_metas'][$scenario['option']] ?? '') === 'oui');
            if (!$actif) $problemes[] = 'Envoi des recus desactive dans la configuration';
        }

        $statut = empty($problemes) ? 'ok' : (($template_ok && trim((string)$sujet) !== '') ? 'attention' : 'erreur');
        $scenario += array(
            'sujet' => $sujet,
            'destinataires' => $destinataires,
            'actif' => $actif,
            'statut' => $statut,
            'problemes' => $problemes,
        );
        $resultats[] = $scenario;
    }
    return $resultats;
}

function notifications_cotisations_audit_resume($resultats = null) {
    $resultats = is_array($resultats) ? $resultats : notifications_cotisations_audit();
    $resume = array('total' => count($resultats), 'ok' => 0, 'attention' => 0, 'erreur' => 0);
    foreach ($resultats as $resultat) $resume[$resultat['statut']]++;
    return $resume;
}

function notifications_cotisations_audit_html() {
    $resultats = notifications_cotisations_audit();
    $resume = notifications_cotisations_audit_resume($resultats);
    $h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    $html = '<section class="box info notifications-audit"><h2>Audit des notifications d\'adhesion (sans envoi)</h2>';
    $html .= '<p><strong>' . $resume['total'] . ' scenarios :</strong> ' . $resume['ok'] . ' conformes, ' . $resume['attention'] . ' attention(s), ' . $resume['erreur'] . ' erreur(s). Aucune adresse email n\'est affichee et aucun message n\'est envoye.</p>';
    $html .= '<div class="table-responsive"><table class="spip liste"><thead><tr><th>Etat</th><th>Evenement</th><th>Destinataire</th><th>Gabarit / sujet</th><th>Execution</th></tr></thead><tbody>';
    foreach ($resultats as $r) {
        $etat = $r['statut'] === 'ok' ? 'OK' : ($r['statut'] === 'attention' ? 'Attention' : 'Erreur');
        $detail = $r['problemes'] ? implode('; ', $r['problemes']) : 'Configuration coherente';
        $html .= '<tr class="' . $h($r['statut']) . '"><td><strong>' . $h($etat) . '</strong><br><small>' . $h($detail) . '</small></td>';
        $html .= '<td><strong>' . $h($r['label']) . '</strong><br><small>' . $h($r['declencheur']) . '</small></td>';
        $html .= '<td>' . $h($r['audience']) . '<br><small>' . $h($r['destinataires']) . '</small></td>';
        $html .= '<td><code>' . $h($r['template']) . '</code><br><small>' . $h($r['sujet']) . '</small></td>';
        $html .= '<td>' . (!empty($r['file']) ? 'File de travaux' : 'Direct') . (!empty($r['option']) ? '<br><small>Option : ' . ($r['actif'] ? 'activee' : 'desactivee') . '</small>' : '') . '</td></tr>';
    }
    return $html . '</tbody></table></div></section>';
}
