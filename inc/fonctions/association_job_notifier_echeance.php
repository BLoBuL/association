<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

include_spip('inc/cotisations');
include_spip('base/abstract_sql');

/**
 * Job queue chargé d'envoyer les notifications d'échéances.
 *
 * @param int    $id_auteur
 * @param string $email_adherent
 * @param int    $nb_jour_differences
 * @param string $type_notification 'echeance_preventive' ou 'echeance_echu'
 * @param string $type_adherent    Type d'adhérent (adherent, entreprise...)
 */
function association_job_notifier_echeance($id_auteur, $email_adherent, $nb_jour_differences = 0, $type_notification = '', $type_adherent = '') {
    $notification_type = $type_notification ?: ($nb_jour_differences == 0 ? 'echeance_echu' : 'echeance_preventive');

    $auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur=' . intval($id_auteur));
    if (!$auteur) {
        association_log('notifications', 'association_job_notifier_echeance: auteur #' . intval($id_auteur) . ' introuvable', 'erreur');
        return false;
    }

    if (!$type_adherent) {
        $type_adherent = $auteur['type_adherent'] ?? $auteur['radio_type_adherent'] ?? 'adherent';
    }

    $options = array(
        'id_auteur' => $id_auteur,
        'nb_jour_differences' => $nb_jour_differences,
        'lang' => $auteur['lang'] ?? $GLOBALS['spip_lang'],
        'email_override' => $email_adherent,
        'validite' => $auteur['validite'] ?? '',
        'use_queue' => false
    );

    $query_cotisation = array(
        'id_auteur' => $id_auteur,
        'id_compte' => 0,
        'id_categorie' => 0,
        'reinscription' => ''
    );

    $query_categories = array(
        'type_adherent' => $type_adherent,
        'valeur' => ''
    );

    return notifier_cotisation_adherent($query_cotisation, $query_categories, array(), $notification_type, $options);
}
