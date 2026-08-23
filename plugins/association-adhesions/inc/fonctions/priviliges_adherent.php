<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Normalise les zones configurees pour les privileges adherents.
 *
 * @param mixed $zones
 * @return array
 */
function association_zones_adherent_normaliser($zones) {
    if (!is_array($zones)) {
        $zones = array($zones);
    }

    return array_values(array_unique(array_filter(array_map('intval', $zones))));
}

/**
 * Retourne les zones configurees auxquelles un auteur est deja lie.
 *
 * @param mixed $zones
 * @param int $id_auteur
 * @return array
 */
function association_auteur_zones_adherent_liees($zones, $id_auteur) {
    $zones = association_zones_adherent_normaliser($zones);
    $id_auteur = intval($id_auteur);
    if (!$zones || !$id_auteur) {
        return array();
    }

    $zones_liees = array();
    foreach ((array)sql_allfetsel(
        'id_zone',
        'spip_zones_liens',
        'id_zone IN (' . implode(',', $zones) . ')'
            . " AND objet='auteur'"
            . ' AND id_objet=' . $id_auteur
    ) as $liaison) {
        $id_zone = intval($liaison['id_zone'] ?? 0);
        if ($id_zone) {
            $zones_liees[] = $id_zone;
        }
    }

    return array_values(array_unique($zones_liees));
}

/**
 * ACTIVATION DES PRIVILEGES ADHERENTS
 *
 * @since 3.1.0
 * @version 0.4b
 *
 * @param string $id_evenement
 * @param int $nombre_inscrits
 * @param boolean $valider
 * @param boolean $montant_payer
 * @param int $id_activite
 * @return array
 */

function activer_privileges_adherent($id_auteur,$reinscription){
    include_spip('inc/autoriser');
    include_spip('action/editer_zone');
    include_spip('inc/mailsubscribers');
    include_spip('inc/filtres');

    $newsletter_subscribe = charger_fonction("subscribe","newsletter");
    $newsletter_unsubscribe = charger_fonction("unsubscribe","newsletter");
    // Infos de l'auteur
    $query_auteur = sql_fetsel("id_auteur,prenom,nom_famille,statut_interne,email",'spip_auteurs', "id_auteur=$id_auteur");
    $id_zone = association_zones_adherent_normaliser(lire_config('/association_metas/zone_adherent', array()));
    $liste_diffusion = (!empty(lire_config('/association_metas/liste_diffusion')))? lire_config('/association_metas/liste_diffusion') : '';
    $email_query_auteur = isset($query_auteur['email']) ? $query_auteur['email'] : '';

    if(email_valide($email_query_auteur) AND !empty($email_query_auteur)){
        $infos_mailsubscriber = sql_fetsel("email,nom,id_mailsubscriber,statut", 'spip_mailsubscribers',
                                           'email=' . sql_quote($email_query_auteur) . " OR email=" . sql_quote(mailsubscribers_obfusquer_email($email_query_auteur)));
        $id_mailsubscriber = $infos_mailsubscriber['id_mailsubscriber'];
        $statut_mailsubscriber = $infos_mailsubscriber['statut'];
    }

    /*GESTION DES ZONES RESTREINTES*/
    if(!empty($id_zone)){
        autoriser_exception('affecterzones', 'auteur', $id_auteur);
        zone_lier($id_zone, 'auteur', $id_auteur);
        autoriser_exception('affecterzones', 'auteur', $id_auteur, false);
    }
    /*GESTION DES LISTES DE DIFFUSION*/
    $options  = array('lang'=> $GLOBALS['spip_lang']);
    $options['force'] = false;
    $options['nom'] = $query_auteur['prenom'] . ' ' . $query_auteur['nom_famille'] ;
    $options['notify'] = false;

     /*On inscrit d'office l\'adherent a la liste des "A jour"*/
    $options['listes']    = array('statut_interne_ok');
    $newsletter_subscribe($email_query_auteur,$options);

    /*On inscrit d'office l\'adherent au(x) liste(s) de diffusion par defaut*/
    if(!empty($liste_diffusion)){
        $options['listes'] = $liste_diffusion;
        $newsletter_subscribe($email_query_auteur,$options);
    }
    $options['listes'] = array();
    $query_mailsubscriptions = sql_select(
        array(
        "mailsubscriptions.id_mailsubscriber AS id_mailsubscriber",
        "mailsubscriptions.statut AS statut_subscription",
        "mailsubscribinglists.identifiant AS identifiant_list",
        "mailsubscribinglists.statut AS statut_list"
        ),
        array(
        "spip_mailsubscriptions AS mailsubscriptions",
        "spip_mailsubscribinglists AS mailsubscribinglists"
        ),
        array(
        "mailsubscriptions.id_mailsubscribinglist = mailsubscribinglists.id_mailsubscribinglist",
        "mailsubscriptions.id_mailsubscriber = '$id_mailsubscriber'"
        )
        );
    if($query_mailsubscriptions){
        /*On boucle sur les listes pour lesquelles il y a un abonnement*/
        while($mailsubscription=sql_fetch($query_mailsubscriptions)) {
             if(strpos($mailsubscription['identifiant_list'],'statut_interne') !== false  AND $mailsubscription['identifiant_list'] != 'statut_interne_'.$query_auteur['statut_interne'] AND $mailsubscription['statut_subscription'] == 'valide'){
                $options['listes'] = array($mailsubscription['identifiant_list']);
                $newsletter_unsubscribe($email_query_auteur,$options);
            }
             if($mailsubscription['statut_list'] == 'ouverte' AND $mailsubscription['statut_subscription'] != 'valide'){
                $options['listes'] = array($mailsubscription['identifiant_list']);
                $newsletter_subscribe($email_query_auteur,$options);
            }
        }
    }
    return true;
}
function desactiver_privileges_adherent($id_auteur){
    include_spip('inc/autoriser');
    include_spip('action/editer_zone');
    include_spip('inc/mailsubscribers');
    include_spip('mailsubscribers_fonctions');

    $newsletter_subscribe = charger_fonction("subscribe","newsletter");
    $newsletter_unsubscribe = charger_fonction("unsubscribe","newsletter");

    $query_auteur = sql_fetsel('id_auteur,email','spip_auteurs', "id_auteur=$id_auteur");
    $email_query_auteur = isset($query_auteur['email']) ? $query_auteur['email'] : '';
    $infos_mailsubscriber = sql_fetsel("email,nom,id_mailsubscriber,statut", 'spip_mailsubscribers',
                                       'email=' . sql_quote($email_query_auteur) . " OR email=" . sql_quote(mailsubscribers_obfusquer_email($email_query_auteur)));
    $id_mailsubscriber = $infos_mailsubscriber['id_mailsubscriber'];

    /*GESTION DES ZONES RESTREINTES*/
    $id_zone = association_zones_adherent_normaliser(lire_config('/association_metas/zone_adherent', array()));
    $zones_liees = association_auteur_zones_adherent_liees($id_zone, $id_auteur);

    if ($zones_liees) {
        autoriser_exception('retirerzones', 'auteur', $id_auteur);
        zone_lier($zones_liees, 'auteur', $id_auteur, 'del');
        autoriser_exception('retirerzones', 'auteur', $id_auteur, false);
    }
    /*GESTION DES LISTES DE DIFFUSION*/
    $options = array();
    $options['notify'] = false;
    $options['force'] = false;
    $options['listes'] = array();
    /*1/ On supprime/nettoie tous ces désabonnements*/
    sql_delete("spip_mailsubscriptions", "statut = 'refuse'  AND id_mailsubscriber=$id_mailsubscriber");

    /*2/ On l'abonne à la liste des "Echus"*/
    $options['listes'] =  array('statut_interne_echu');
    $newsletter_subscribe($email_query_auteur,$options);

    /*3/ On le désabonne de la liste automatique des "A jour"*/
    $options['listes'] = array('statut_interne_ok');
    $newsletter_unsubscribe($email_query_auteur, $options);

    /*4/ On le désabonne des listes ouvertes*/
        /*Ici je mets dans une resource SQL la liste des inscriptions en ommettant la liste automatique lié au statut*/
        $options['listes'] = array();
        $query_mailsubscriptions = sql_select(
                                    array(
                                        "mailsubscriptions.id_mailsubscriber AS id_mailsubscriber",
                                        "mailsubscriptions.statut AS statut_subscription",
                                        "mailsubscribinglists.identifiant AS identifiant_list",
                                        "mailsubscribinglists.statut AS statut_list"
                                    ),
                                    array(
                                        "spip_mailsubscriptions AS mailsubscriptions",
                                        "spip_mailsubscribinglists AS mailsubscribinglists"
                                    ),
                                    array(
                                        "mailsubscriptions.id_mailsubscribinglist = mailsubscribinglists.id_mailsubscribinglist",
                                        'mailsubscribinglists.identifiant NOT LIKE "statut_interne%" ',
                                        "mailsubscribinglists.statut = 'ouverte'",
                                        "mailsubscriptions.id_mailsubscriber = '$id_mailsubscriber'"
                                    )
                                );
        while($mailsubscription=sql_fetch($query_mailsubscriptions)) {
            $options['listes'] = array($mailsubscription['identifiant_list']);
            $newsletter_unsubscribe($email_query_auteur,$options);
        }

        /*
        5/ Création du point de géolocalisation si on utilise GIS
        */
        if (test_plugin_actif('gis')) {
            if($id_gis = sql_getfetsel('G.id_gis', 'spip_gis AS G LEFT  JOIN spip_gis_liens AS T ON T.id_gis=G.id_gis', 'T.id_objet=' . intval($id_auteur) . " AND T.objet='auteur'")){
                include_spip('inc/fonctions/gis_auteur');
                gis_auteur($id_auteur, 'suppression', $id_gis);
            }
        }
    return true;
}
function verifier_privileges_adherent($id_auteur) {


    include_spip('action/editer_zone');
    include_spip('inc/mailsubscribers');
    include_spip('mailsubscribers_fonctions');

    $newsletter_subscribe = charger_fonction("subscribe", "newsletter");
    $newsletter_unsubscribe = charger_fonction("unsubscribe", "newsletter");

    $query_auteur = sql_fetsel("id_auteur, prenom, nom_famille, statut, statut_interne, email", "spip_auteurs", "id_auteur=$id_auteur");
    $email_query_auteur = isset($query_auteur['email']) ? $query_auteur['email'] : '';
    $nom_query_auteur = $query_auteur['prenom'] . ' ' . $query_auteur['nom_famille'];

    include_spip('inc/filtres');
    if (empty($email_query_auteur) || !email_valide($email_query_auteur)) {
        return true;
    }

    $id_zone = association_zones_adherent_normaliser(lire_config('/association_metas/zone_adherent', array()));
    $zones_liees = association_auteur_zones_adherent_liees($id_zone, $id_auteur);
    $zones_manquantes = array_values(array_diff($id_zone, $zones_liees));

    $infos_mailsubscriber = sql_fetsel("email, nom, id_mailsubscriber, statut", "spip_mailsubscribers", 'email=' . sql_quote($email_query_auteur) . " OR email=" . sql_quote(mailsubscribers_obfusquer_email($email_query_auteur)));
    $id_mailsubscriber = $infos_mailsubscriber['id_mailsubscriber'];
    $statut_mailsubscriber = $infos_mailsubscriber['statut'];


    $liste_diffusion = (!empty(lire_config('/association_metas/liste_diffusion')))? lire_config('/association_metas/liste_diffusion') : array();
    if (!is_array($liste_diffusion)) {
        $liste_diffusion = preg_split('/[;,\s]+/', trim((string)$liste_diffusion), -1, PREG_SPLIT_NO_EMPTY);
    }
    $liste_diffusion = array_values(array_unique(array_filter($liste_diffusion)));

    // Initialiser la variable pour éviter les erreurs "undefined variable"
    $query_subscriptions_liste_diffusion = null;

    if ($liste_diffusion && $id_mailsubscriber) {
        $ids_liste_diffusion = [];
        $query_id_liste_diffusion = sql_select("id_mailsubscribinglist", "spip_mailsubscribinglists", "identifiant IN ('" . implode("','", $liste_diffusion) . "')");
        while ($id_liste_diffusion = sql_fetch($query_id_liste_diffusion)) {
            $ids_liste_diffusion[] = $id_liste_diffusion['id_mailsubscribinglist'];
        }

        $query_subscriptions_liste_diffusion = sql_select("*", "spip_mailsubscriptions", 'id_mailsubscriber=' . $id_mailsubscriber . " AND id_mailsubscribinglist IN ('" . implode("','", $ids_liste_diffusion) . "')");
    }

    $options = ['lang' => $GLOBALS['spip_lang'], 'notify' => false, 'force' => false, 'nom' => $nom_query_auteur];

    $query_mailsubscriptions = sql_select(
        ["mailsubscriptions.id_mailsubscriber AS id_mailsubscriber", "mailsubscriptions.statut AS statut_subscription", "mailsubscribinglists.identifiant AS identifiant_list", "mailsubscribinglists.statut AS statut_list"],
        ["spip_mailsubscriptions AS mailsubscriptions", "spip_mailsubscribinglists AS mailsubscribinglists"],
        ["mailsubscriptions.id_mailsubscribinglist = mailsubscribinglists.id_mailsubscribinglist", "mailsubscriptions.id_mailsubscriber = '$id_mailsubscriber'"]
    );

    while ($mailsubscription = sql_fetch($query_mailsubscriptions)) {
        if ($mailsubscription['identifiant_list'] == 'statut_interne_' . $query_auteur['statut_interne'] && $mailsubscription['statut_subscription'] == 'valide') {
            continue;
        } elseif (strpos($mailsubscription['identifiant_list'], 'statut_interne') !== false && $mailsubscription['identifiant_list'] != 'statut_interne_' . $query_auteur['statut_interne'] && $mailsubscription['statut_subscription'] == 'valide') {
            $options['listes'] = [$mailsubscription['identifiant_list']];
            $newsletter_unsubscribe($email_query_auteur, $options);
        } elseif ($mailsubscription['identifiant_list'] == 'statut_interne_' . $query_auteur['statut_interne'] && $mailsubscription['statut_subscription'] != 'valide') {
            $options['listes'] = [$mailsubscription['identifiant_list']];
            $newsletter_subscribe($email_query_auteur, $options);
        }
    }

    $a_deja_liste_diffusion = false;
    if ($query_subscriptions_liste_diffusion) {
        while ($subscription = sql_fetch($query_subscriptions_liste_diffusion)) {
            if (($subscription['statut'] ?? '') === 'valide') {
                $a_deja_liste_diffusion = true;
                break;
            }
        }
    }

    if (!empty($liste_diffusion) && !$a_deja_liste_diffusion) {
        $options['listes'] = $liste_diffusion;
        $newsletter_subscribe($email_query_auteur, $options);
    }

    if ($query_auteur['statut_interne'] == 'ok' && $query_auteur['statut'] != '5poubelle') {
        if ($zones_manquantes) {
            autoriser_exception('affecterzones', 'auteur', $id_auteur);
            zone_lier($zones_manquantes, 'auteur', $id_auteur);
            autoriser_exception('affecterzones', 'auteur', $id_auteur, false);
        }

        if (!$id_mailsubscriber && !empty($liste_diffusion)) {
            $options['listes'] = $liste_diffusion;
            $newsletter_subscribe($email_query_auteur, $options);
        } elseif ($id_mailsubscriber && $statut_mailsubscriber != 'valide' && !empty($liste_diffusion) && !$a_deja_liste_diffusion) {
            $options['listes'] = $liste_diffusion;
            $newsletter_subscribe($email_query_auteur, $options);
        }
    } elseif (in_array($query_auteur['statut_interne'], ["prospect", "echu", "relance"]) && $query_auteur['statut'] != '5poubelle') {
        if ($zones_liees) {
            autoriser_exception('retirerzones', 'auteur', $id_auteur);
            zone_lier($zones_liees, 'auteur', $id_auteur, 'del');
            autoriser_exception('retirerzones', 'auteur', $id_auteur, false);
        }

        $query_mailsubscriptions = sql_select(
            ["mailsubscriptions.id_mailsubscriber AS id_mailsubscriber", "mailsubscriptions.statut AS statut_subscription", "mailsubscribinglists.identifiant AS identifiant_list", "mailsubscribinglists.statut AS statut_list"],
            ["spip_mailsubscriptions AS mailsubscriptions", "spip_mailsubscribinglists AS mailsubscribinglists"],
            ["mailsubscriptions.id_mailsubscribinglist = mailsubscribinglists.id_mailsubscribinglist", "mailsubscriptions.id_mailsubscriber = '$id_mailsubscriber'"]
        );

        while ($mailsubscription = sql_fetch($query_mailsubscriptions)) {
            if ($mailsubscription['statut_list'] == 'ouverte' && $mailsubscription['statut_subscription'] == 'valide') {
                $options['listes'] = [$mailsubscription['identifiant_list']];
                $newsletter_unsubscribe($email_query_auteur, $options);
            }
        }
    } elseif ($query_auteur['statut_interne'] == 'sorti' || $query_auteur['statut'] == '5poubelle') {
        $newsletter_unsubscribe($email_query_auteur, $options);

        if ($zones_liees) {
            autoriser_exception('retirerzones', 'auteur', $id_auteur);
            zone_lier($zones_liees, 'auteur', $id_auteur, 'del');
            autoriser_exception('retirerzones', 'auteur', $id_auteur, false);
        }

        if (!empty($id_mailsubscriber)) {
            sql_delete("spip_mailsubscriptions", "id_mailsubscriber=$id_mailsubscriber");
            sql_delete("spip_mailsubscribers", "id_mailsubscriber=$id_mailsubscriber");
        }
    }

    if (test_plugin_actif('gis')) {
        include_spip('inc/fonctions/gis_auteur');

        $id_gis = sql_getfetsel('G.id_gis', 'spip_gis AS G LEFT JOIN spip_gis_liens AS T ON T.id_gis=G.id_gis', 'T.id_objet=' . intval($id_auteur) . " AND T.objet='auteur'");
        if (empty($id_gis) AND $query_auteur['statut_interne'] == 'ok') {
            gis_auteur($id_auteur, 'creation');
        }elseif ($id_gis  AND $query_auteur['statut_interne'] != 'ok') {
            gis_auteur($id_auteur, 'suppression', $id_gis);
        } elseif ($id_gis AND $query_auteur['statut_interne'] == 'ok') {
        }
    }

    return true;
}
