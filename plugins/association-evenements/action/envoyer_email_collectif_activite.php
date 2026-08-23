<?php
/***************************************************************************\
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & François de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_envoyer_email_collectif_activite() {
    /**
     * Envoie (planifie) un envoi collectif pour une activité/événement.
     * Entrées attendues via _request(): id_evenement, sujet, html, array_activites
     */

    $date_start = $date = date('Y-m-d H:i:s');

    // Sécuriser l'action (token/session)
    $securiser_action = charger_fonction('securiser_action', 'inc');
    $securiser_action();

    $sujet = _request('sujet') ?? '';
    $id_evenement = intval(_request('id_evenement'));
    $html = _request('html') ?? '';

    // Petit nettoyage HTML
    $html = str_replace(['{{', '}}'], ['', ''], $html);

    // Récupérer le titre de l'événement et préférer la version FR
    $row = sql_fetsel('titre', 'spip_evenements', 'id_evenement = ' . $id_evenement);
    $evenement_nom = $row['titre'] ?? '';
    if (function_exists('extraire_multi')) {
        $evenement_nom = extraire_multi($evenement_nom, 'fr');
    }
    $evenement_nom = function_exists('supprimer_tags') ? supprimer_tags($evenement_nom) : trim(strip_tags($evenement_nom));

    // Récupérer la liste d'activités et leurs emails
    $array_activites = explode(',', (_request('array_activites') ?? ''));
    $array_activites = array_filter(array_map('intval', $array_activites));
    if (empty($array_activites)) {
        association_log('email', 'action_envoyer_email_collectif_activite: liste d\'activites vide', 'erreur');
        return;
    }

    $id_activites_list = sql_in('id_activite', $array_activites);
    $res = sql_select('email_inscrit', 'spip_asso_activites', $id_activites_list);
    $activites_info_array = sql_fetch_all($res);

    $emails = [];
    foreach ($activites_info_array as $r) {
        if (!empty($r['email_inscrit'])) {
            $emails[] = $r['email_inscrit'];
        }
    }

    // Ajouter les emails des responsables de l'événement
    $respo_ids = array();
    if (function_exists('liste_responsables_evenement')) {
        $respo = liste_responsables_evenement($id_evenement);
        $respo_ids = $respo['auteur_array'] ?? array();
    } else {
        // tenter d'inclure la fonction si présente dans le plugin
        include_spip('inc/fonctions/liste_responsables_evenement');
        if (function_exists('liste_responsables_evenement')) {
            $respo = liste_responsables_evenement($id_evenement);
            $respo_ids = $respo['auteur_array'] ?? array();
        }
    }

    if (!empty($respo_ids)) {
        $res_respo = sql_select('email', 'spip_auteurs', sql_in('id_auteur', $respo_ids));
        while ($rr = sql_fetch($res_respo)) {
            if (!empty($rr['email'])) {
                $emails[] = $rr['email'];
            }
        }
    }

    // Normaliser + dédupliquer les adresses pour éviter les collisions sur clés uniques
    $emails = array_map(static function ($email) {
        return strtolower(trim((string)$email));
    }, $emails);
    $emails = array_values(array_unique(array_filter($emails, static function ($email) {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    })));

    if (empty($emails)) {
        association_log('email', 'action_envoyer_email_collectif_activite: aucun email trouvé', 'erreur');
        return;
    }

    // Ajouter les nouveaux abonnés (comparaison normalisée pour éviter les doublons)
    $existing = sql_allfetsel('email', 'spip_mailsubscribers');
    $existing_emails = array_map(static function ($row) {
        return strtolower(trim((string)($row['email'] ?? '')));
    }, is_array($existing) ? $existing : array());
    $existing_emails = array_values(array_unique(array_filter($existing_emails)));
    $new_emails = array_diff($emails, $existing_emails);
    foreach ($new_emails as $ne) {
        sql_insertq('spip_mailsubscribers', ['email' => $ne]);
    }

    // Créer le mailshot
    $total = count($emails);
    $id_auteur_connecte = session_get('id_auteur');

    $id_mailshot = sql_insertq('spip_mailshots', [
        'id_auteur' => $id_auteur_connecte,
        'id_evenement' => $id_evenement,
        'sujet' => $sujet,
        'html' => $html,
        'listes' => $evenement_nom,
        'total' => $total,
        'date' => $date,
        'date_start' => $date_start,
        'statut' => 'init',
        'composition_lock' => 0,
    ]);

    foreach ($emails as $email) {
        sql_insertq('spip_mailshots_destinataires', [
            'id_mailshot' => $id_mailshot,
            'email' => $email,
            'date' => $date,
            'statut' => 'todo',
        ]);
    }

    ecrire_meta('mailshot_processing', 'oui');
    include_spip('inc/genie');
    genie_queue_watch_dist();

}
