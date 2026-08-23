<?php

if (!defined('_ECRIRE_INC_VERSION')) { return; }


function association_mailsubscriber_formater_informations_liees($champs_extra, $liste_informations_segmentables)
{
    $flux=array();

    if ((!empty($liste_informations_segmentables) and !in_array($champs_extra['options']['nom'], $liste_informations_segmentables)) or $champs_extra['saisie'] == 'fieldset') {
        unset($champs_extra);
        return false;
    }
    $nom_champs_extra = $champs_extra['options']['nom'];

    if ($champs_extra['saisie'] == 'selection') {
        //Ici on permet les selection multiple
        $champs_extra['saisie'] = 'selection_multiple';
        //$champs_extra['options']['multiple'] = 'oui';
    } elseif ($champs_extra['saisie'] == 'radio') {
        //Ici on transforme les choix radio en checkbox
        $champs_extra['saisie'] = 'checkbox';
    }

    // Sécuriser l'accès à options et datas pour éviter les notices
    $opts = isset($champs_extra['options']) && is_array($champs_extra['options']) ? $champs_extra['options'] : array();

    $res = array(
        'titre' => $champs_extra['options']['label'],
        'saisie' => $champs_extra['saisie'],
        'options' => array(
            'nom' => $opts['nom'] ?? '',
            'label' => $opts['label'] ?? '',
            'datas' => isset($opts['datas']) ? $opts['datas'] : array(),
        )
    );

    return $res;
}

function association_mailsubscriber_informations_liees($flux)
{
    include_spip('inc/filtres');
    $liste_champs_extra = lire_config('champs_extras_spip_auteurs');
    $liste_informations_segmentables = null;
    $raw_segment = isset($GLOBALS['association_metas']['selection_segment']) ? $GLOBALS['association_metas']['selection_segment'] : null;
    if (is_string($raw_segment) && $raw_segment !== '') {
        $decoded = @unserialize($raw_segment);
        $liste_informations_segmentables = ($decoded !== false || $raw_segment === 'b:0;') ? $decoded : null;
    } elseif (is_array($raw_segment)) {
        $liste_informations_segmentables = $raw_segment;
    }
    if (isset($flux['args']['declarer'])) {
        foreach ($liste_champs_extra as $champs_extra) {
            $nom_champs_extra = $champs_extra['options']['nom'];

            if ($champs_extra['saisie'] == 'fieldset') {
                foreach ($champs_extra['saisies'] as $champs_fieldset_saisies) {
                    $nom_champs_extra = $champs_fieldset_saisies['options']['nom'];
                    $flux['data'][$nom_champs_extra] = association_mailsubscriber_formater_informations_liees($champs_fieldset_saisies, $liste_informations_segmentables);
               }
            } else {
                $flux['data'][$nom_champs_extra]=association_mailsubscriber_formater_informations_liees($champs_extra, $liste_informations_segmentables);
            }
        };
        $flux['data'] = array_filter($flux['data']);


        $flux['data']['adherent'] = array(
            'titre' => $t = 'Adhérent de l\'association',
            'saisie' => 'selection',
            'options' => array(
                'nom' => 'adherent',
                'label' => $t,
                'datas' => array('oui' => 'Oui','non' => 'Non'),
            )
        );
        $flux['data']['statut_interne'] = array(
            'titre' => $t = 'Statut de l\'adhérent',
            'saisie' => 'selection',
            'options' => array(
                'nom' => 'statut_interne',
                'label' => $t,
                'datas' => array('ok' => 'A jour','prospect' => 'Nouvel inscrit','echu' => 'Echus', 'relance' => 'Relancé','sorti' => 'Désactivé'),
            )
        );
        //Année d'inscription
        $flux['data']['inscription'] = array(
            'titre' => $t = 'Année de première inscription',
            'saisie' => 'selection_multiple',
            'options' => array(
                'nom' => 'inscription',
                'label' => $t,
                'datas' => array('2026' => '2026','2025' => '2025','2024' => '2024','2023' => '2023','2022' => '2022','2021' => '2021','2020' => '2020','2019' => '2019', '2018' => '2018', '2017' => '2017', '2016' => '2016', '2015' => '2015', '2014' => '2014', '2013' => '2013' ),
                'size' => '5',
            )
        );
        //Année de validité
        $flux['data']['validite'] = array(
            'titre' => $t = 'Année de validité',
            'saisie' => 'selection_multiple',
            'options' => array(
                'nom' => 'validite',
                'label' => $t,
                'datas' => array('2026' => '2026','2025' => '2025','2024' => '2024','2023' => '2023','2022' => '2022','2021' => '2021','2020' => '2020','2019' => '2019', '2018' => '2018', '2017' => '2017', '2016' => '2016', '2015' => '2015', '2014' => '2014', '2013' => '2013' ),
                'size' => '5',
            )
        );
        if (!empty($liste_informations_segmentables) AND in_array('quartier_coeur', $liste_informations_segmentables) AND in_array('quartier_rattachement', $liste_informations_segmentables)) {
            $flux['data']['quartiers'] = array(
                'titre' =>  $t =  'Quartiers (rattachement ou coeur)',
                'saisie' => 'selection_multiple',
                'options' => array(
                    'nom' => 'quartiers',
                    'label' => $t,
                    'datas' => $flux['data']['quartier_rattachement']['options']['datas'],
                )
            );
        }
    }
    // Sécuriser l'accès à ['email'] pour éviter les notices quand l'argument est absent
    if (isset($flux['args']['email']) && $flux['args']['email'] && !isset($flux['args']['declarer'])) {
        $email = $flux['args']['email'];
        $liste_segment_perso = array("statut_interne","inscription","validite");

        if ($liste_informations_segmentables) {
            $liste_segments =  array_merge($liste_informations_segmentables, $liste_segment_perso);
        } else {
            $liste_segments = $liste_segment_perso;
        }
        // La configuration des segments peut conserver des champs supprimés.
        // Ne jamais les inclure dans la requête : le schéma SQL réel fait foi.
        $description_auteurs = sql_showtable('spip_auteurs', true);
        $champs_auteurs = isset($description_auteurs['field']) && is_array($description_auteurs['field'])
            ? array_keys($description_auteurs['field'])
            : array();
        $liste_segments_valides = array_values(array_filter(array_unique($liste_segments), function ($champ) use ($champs_auteurs) {
            return is_string($champ) && in_array($champ, $champs_auteurs, true);
        }));
        $liste_segments_obsoletes = array_values(array_diff($liste_segments, $liste_segments_valides));
        if ($liste_segments_obsoletes) {
            spip_log('Champs de segments obsoletes ignores : ' . implode(',', $liste_segments_obsoletes), 'association' . _LOG_INFO_IMPORTANTE);
        }
        $liste_segments = $liste_segments_valides;
        if (!$liste_segments) {
            return $flux;
        }
        // Protection contre injection et récupération sûre
        $query_auteur = sql_fetsel($liste_segments, "spip_auteurs", "email = " . sql_quote($email));

        if (!$query_auteur || !is_array($query_auteur)) {
            // Rien à faire si l'auteur n'existe pas
            return $flux;
        }

        $inscription = !empty($query_auteur['inscription']) ? affdate($query_auteur['inscription'], 'Y') : '';
        $validite = !empty($query_auteur['validite']) ? affdate($query_auteur['validite'], 'Y') : '';
        foreach ($liste_segments as $info_segment) {
            $val = isset($query_auteur[$info_segment]) ? $query_auteur[$info_segment] : '';
            // On verifie si il s'agit d'une valeur multiple (radio,checkbox ou selection_multiple) avec séparateur ","
            if (is_string($val) && strpos($val, ',') !== false) {
                $flux['data'][$info_segment] = explode(',', $val);
            } else {
                $flux['data'][$info_segment] = $val;
            }
        }
        if (!empty($query_auteur['statut_interne'])) {
            $flux['data']['statut_interne'] = $query_auteur['statut_interne'];
            $flux['data']['adherent'] = 'oui';
        } else {
            $flux['data']['adherent'] = 'non';
        }
        if (!empty($inscription)) {
            $flux['data']['inscription'] = $inscription;
        }
        if (!empty($validite)) {
            $flux['data']['validite'] = $validite;
        }

        if (isset($query_auteur['quartier_rattachement']) or isset($query_auteur['quartier_coeur'])) {
            $flux['data']['quartiers'] = array($query_auteur['quartier_rattachement'] ?? '',$query_auteur['quartier_coeur'] ?? '');
        }
    }
    return $flux;
}

function association_corbeille_table_infos($flux) {
    $flux['mailsubscribers'] = [
        'statut'    => 'poubelle',
        'tableliee' => ['spip_mailsubscriptions', 'spip_mailsubscriptions_optins'],
    ];
    $flux['mailsubscribinglists'] = [
        'statut'    => 'poubelle',
        'tableliee' => ['spip_mailsubscriptions', 'spip_mailsubscriptions_optins'],
    ];
    return $flux;
}

function association_communication_notifications_destinataires($flux) {
    $quoi = $flux['args']['quoi'] ?? '';
    $options = $flux['args']['options'] ?? array();
    if (
        ($quoi === 'instituerauteur' && ($options['statut_ancien'] ?? '') === '8aconfirmer' && ($options['type'] ?? '') === 'user')
        || ($quoi === 'i3_inscriptionauteur' && ($options['type'] ?? '') === 'user')
    ) {
        $mail = sql_getfetsel('email', 'spip_auteurs', 'id_auteur=' . intval($flux['args']['id'] ?? 0));
        if ($mail && !in_array($mail, (array) $flux['data'], true)) {
            $flux['data'][] = $mail;
            spip_log('association_communication_notifications_destinataires: ajout user=' . $mail, 'association' . _LOG_DEBUG);
        }
    } elseif (
        ($quoi === 'instituerauteur' && ($options['statut_ancien'] ?? '') === '8aconfirmer' && ($options['type'] ?? '') === 'admin')
        || ($quoi === 'i3_inscriptionauteur' && ($options['type'] ?? '') === 'admin')
    ) {
        $flux['data'] = association_collecter_destinataires_admins() ?: array();
    }
    return $flux;
}

function association_communication_association_maintenance_supprimer_donnees_auteurs($flux) {
	include_spip('inc/association_communication_maintenance');
	$ids = array_values(array_filter(array_map('intval', (array) ($flux['args']['ids_auteurs'] ?? array()))));
	$flux['data']['supprimer_mailsubscribers'] = asso_supprimer_mailsubscribers_pour_auteurs($ids, (bool) ($flux['args']['dry_run'] ?? true));
	return $flux;
}
