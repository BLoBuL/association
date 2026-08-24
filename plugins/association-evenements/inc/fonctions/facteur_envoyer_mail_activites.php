<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Fonction principale qui gère l'envoi des emails de notification pour les activités
 *
 * @param string $id_evenement Identifiant de l'événement concerné
 * @param string $type Type d'action (inscription_frontend, desinscription_backend, etc.)
 * @param array $id_activite Tableau des identifiants d'activités concernées
 * @return bool true si l'envoi a réussi, false en cas d'erreur
 */
function facteur_envoyer_mail_activites($id_evenement = '', $type = '', $id_activite = array()) {
    // Vérification des paramètres
    if (empty($type)) {
        association_log('email', "Erreur: type d'action non spécifié", 'erreur');
        return false;
    }

    if (empty($id_activite) || !is_array($id_activite)) {
        association_log('email', "Erreur: aucune activité spécifiée", 'erreur');
        return false;
    }

    try {
        // Configuration et initialisation
        include_spip('inc/filtres');

        // Récupération de l'id_evenement si non fourni
        if (empty($id_evenement)) {
            $id_evenement_sql = sql_fetsel("*", "spip_asso_activites", "id_activite=" . intval($id_activite[0]));
            if (!$id_evenement_sql) {
                association_log('email', "Erreur: activité introuvable " . $id_activite[0], 'erreur');
                return false;
            }
            $id_evenement = $id_evenement_sql['id_evenement'];
        }

        // Récupération des informations de l'événement
        $evenement = sql_fetsel('titre,date_debut,accompagnants', "spip_evenements", "id_evenement=" . intval($id_evenement));
        if (!$evenement) {
            association_log('email', "Erreur: événement introuvable " . $id_evenement, 'erreur');
            return false;
        }

        // Récupération des responsables
        include_spip('inc/association_evenements_responsables');
        $activite_responsable_array = association_evenements_responsables_ids($id_evenement);

        // Traitement pour chaque activité
        $infos_activites = array();
        foreach ($id_activite as $id) {
            $infos_activite = facteur_envoyer_mail_activite_adherent($id, $id_evenement, $type, $evenement, $activite_responsable_array);
            if ($infos_activite) {
                $infos_activites[] = $infos_activite;
            }
        }

        // Envoi aux responsables si nécessaire
        if (!empty($infos_activites)) {
            facteur_envoyer_mail_activite_responsable($infos_activites[0], $type, $evenement, $activite_responsable_array);
        }

        return true;
    } catch (Exception $e) {
        association_log('email', "Exception générale dans facteur_envoyer_mail_activites: " . $e->getMessage(), 'erreur');
        return false;
    }
}

/**
 * Envoie un email de notification à l'adhérent concerné par une activité
 *
 * @param int $id_activite Identifiant de l'activité
 * @param int $id_evenement Identifiant de l'événement
 * @param string $type Type d'action
 * @param array $evenement Données de l'événement
 * @param array $activite_responsable_array Tableau des responsables
 * @return array|false Informations sur l'activité pour notification aux responsables, ou false en cas d'erreur
 */
function facteur_envoyer_mail_activite_adherent($id_activite, $id_evenement, $type, $evenement, $activite_responsable_array) {
    $id = intval($id_activite);
    $activite_auteur = sql_fetsel("*", "spip_asso_activites", "id_activite=$id");
    if (!$activite_auteur) {
        association_log('email', "Erreur: activité introuvable " . $id, 'erreur');
        return false;
    }

    // Configuration de l'événement
    $meta_cfg_event_config_accompagnants = ($GLOBALS['association_metas']['meta_cfg_event_config_accompagnants'] == 'membre_famille')
        ? 'membre_famille' : 'tout';
    $nom_evenement = supprimer_numero($evenement['titre']);
    $date_evenement = $evenement['date_debut'];
    $option_accompagnants = $evenement['accompagnants'];
    $date_ev = association_datefr($date_evenement);
    $heure_ev = association_heurefr($date_evenement);

    // Récupération des informations de l'adhérent
    $id_auteur = $activite_auteur['id_auteur'];
    $nombre_inscrits = $activite_auteur['nombre_inscrits'];

    if ($id_auteur != 0) {
        $query_auteur = sql_fetsel("*", "spip_auteurs", "id_auteur=" . intval($id_auteur));
        if (!$query_auteur) {
            association_log('email', "Erreur: auteur introuvable " . $id_auteur, 'erreur');
            return false;
        }
        $nom_inscrit = $query_auteur['nom_famille'] ?? $query_auteur['nom'];
        $prenom_inscrit = $query_auteur['prenom'] ?? '';
        $email_inscrit = $query_auteur['email'];
        $tel_inscrit = $query_auteur['mobile'] ?? $query_auteur['telephone'] ?? '';
    } else {
        $nom_inscrit = $activite_auteur['nom_inscrit'];
        $prenom_inscrit = $activite_auteur['prenom_inscrit'];
        $email_inscrit = $activite_auteur['email_inscrit'];
        $tel_inscrit = $activite_auteur['tel_inscrit'];
    }

    // Préparation du nom des participants
    $nom_participants = '';
    if ($option_accompagnants == 'oui') {
        if ($meta_cfg_event_config_accompagnants == 'membre_famille') {
            $nom_participants = ($nombre_inscrits > 1)
                ? substr_replace($activite_auteur['nom_participants'], ' et ', strrpos($activite_auteur['nom_participants'], ','), 1)
                : $activite_auteur['nom_participants'];
        } else {
            $nom_prenom_adherents = $prenom_inscrit . " " . $nom_inscrit;
            $nom_participants = ($nombre_inscrits > 1)
                ? $activite_auteur['nom_participants']
                : $nom_prenom_adherents;
        }
    } else {
        $nom_participants = $prenom_inscrit . " " . $nom_inscrit;
    }

    // Détermination des modèles et sujets d'emails selon le type d'action
    list($sujet_email_adherent, $model_email_adherent) = _determiner_modeles_emails_adherent($type);

    // Vérification de l'email
    if (empty($email_inscrit) || !filter_var($email_inscrit, FILTER_VALIDATE_EMAIL)) {
        association_log('email', "Email invalide pour l'adhérent (activité " . $id . "): " . $email_inscrit, 'erreur');
        return false;
    }

    try {
        // Préparation et envoi de l'email à l'adhérent
        $sujet = _T($sujet_email_adherent, array(
            'evenement' => $nom_evenement,
            'datev' => $date_ev
        ));

        $html = recuperer_fond($model_email_adherent, array(
            'id_responsables_array' => $activite_responsable_array,
            'id_evenement' => $id_evenement,
            'id_activite' => $id,
            'nombre_jours' => $GLOBALS['association_metas']['meta_cfg_event_delai_expiration'],
            'nom_participants' => $nom_participants,
            'infos_inscrit' => array(
                'nom_inscrit' => $nom_inscrit,
                'prenom_inscrit' => $prenom_inscrit,
                'email_inscrit' => $email_inscrit,
                'tel_inscrit' => $tel_inscrit
            )
        ));

        if (!facteur_envoyer_app($email_inscrit, $sujet, $html, '', array('enqueued' => true, 'use_queue' => false))) {
            association_log('email', "Échec de l'envoi de l'email à " . $email_inscrit, 'erreur');
        }
    } catch (Exception $e) {
        association_log('email', "Erreur lors de l'envoi de l'email à l'adhérent: " . $e->getMessage(), 'erreur');
    }

    // Retourner les infos pour l'email aux responsables
    return array(
        'id_auteur' => $id_auteur,
        'id_evenement' => $id_evenement,
        'id_activite' => $id,
        'nom_inscrit' => $nom_inscrit,
        'prenom_inscrit' => $prenom_inscrit,
        'email_inscrit' => $email_inscrit,
        'tel_inscrit' => $tel_inscrit,
        'nom_participants' => $nom_participants,
        'nombre_inscrits' => $nombre_inscrits
    );
}

/**
 * Envoie un email de notification aux responsables concernant une activité
 *
 * @param array $infos_activite Informations sur l'activité
 * @param string $type Type d'action
 * @param array $evenement Données de l'événement
 * @param array $activite_responsable_array Tableau des responsables
 * @return bool true si l'envoi a réussi, false en cas d'erreur
 */
function facteur_envoyer_mail_activite_responsable($infos_activite, $type, $evenement, $activite_responsable_array) {
    include_spip('inc/notifications_emails');
    $config_envoi_email_notif_defaut = $GLOBALS['association_metas']['config_envoi_email_notif_defaut'];
    $config_envoi_email_notif_bcc = $GLOBALS['association_metas']['config_envoi_email_notif_bcc'];
    $emails_responsables = array();

    // Types de notifications à envoyer aux responsables
    $types_notif_responsables = array(
        'desinscription_frontend', 'inscription_frontend', 'modification_frontend',
        'preinscription_frontend', 'attente_frontend', 'inscription_automatique',
        'preinscription_automatique', 'expiration_automatique'
    );

    if ((!$activite_responsable_array && empty($config_envoi_email_notif_defaut)) || !in_array($type, $types_notif_responsables)) {
        return false;
    }

    // Extraction des données nécessaires
    $id_evenement = $infos_activite['id_evenement'];
    $nom_participants = $infos_activite['nom_participants'];
    $nombre_inscrits = $infos_activite['nombre_inscrits'];

    // Configuration de l'événement
    $nom_evenement = supprimer_numero($evenement['titre']);
    $date_ev = association_datefr($evenement['date_debut']);
    $heure_ev = association_heurefr($evenement['date_debut']);

    // Récupération des emails des responsables
    if ($activite_responsable_array) {
        foreach ($activite_responsable_array as $id_responsable) {
            $info_auteur_responsable = sql_fetsel(
                "input_email_membres_asso, email",
                "spip_auteurs",
                "id_auteur=" . intval($id_responsable)
            );

            if ($info_auteur_responsable) {
                $email_responsable = !empty($info_auteur_responsable['input_email_membres_asso'])
                    ? $info_auteur_responsable['input_email_membres_asso']
                    : $info_auteur_responsable['email'];

                if (!empty($email_responsable)) {
                    // Normaliser l'email (gère chaîne avec plusieurs adresses)
                    $norm = parser_emails_depuis_config(is_array($email_responsable) ? implode(',', $email_responsable) : $email_responsable);
                     if ($norm) {
                         foreach ($norm as $e) {
                             $emails_responsables[] = $e;
                         }
                     } else {
                         association_log('email', "Email invalide pour le responsable " . $id_responsable . ": " . $email_responsable, 'erreur');
                     }
                } else {
                    association_log('email', "Email manquant pour le responsable " . $id_responsable, 'erreur');
                }
            }
        }
        // Élimination des doublons
        $emails_responsables = array_unique($emails_responsables);
    }

    // Choix du modèle et sujet pour les responsables
    list($sujet_email_responsable, $model_email_responsable) = _determiner_modeles_emails_responsable($type, $nombre_inscrits);

    if (!$model_email_responsable) {
        association_log('email', "Modèle d'email non trouvé pour le type " . $type, 'erreur');
        return false;
    }

    try {
        // Préparation et envoi de l'email aux responsables
        $sujet = _T($sujet_email_responsable, array(
            'evenement' => $nom_evenement,
            'adherents' => $nom_participants,
            'datev' => $date_ev,
            'heure' => $heure_ev
        ));

        $fond_content = array(
            'id_responsables_array' => $activite_responsable_array,
            'id_evenement' => $infos_activite['id_evenement'],
            'id_activite' => $infos_activite['id_activite'],
            'id_adherent' => $infos_activite['id_auteur'],
            'nom_participants' => $nom_participants,
            'nom_inscrit' => $infos_activite['nom_inscrit'],
            'prenom_inscrit' => $infos_activite['prenom_inscrit'],
            'email_inscrit' => $infos_activite['email_inscrit'],
            'tel_inscrit' => $infos_activite['tel_inscrit'],
            'nombre_jours' => $GLOBALS['association_metas']['meta_cfg_event_delai_expiration'],
        );

        $html = recuperer_fond($model_email_responsable, $fond_content);
        $bcc = $config_envoi_email_notif_bcc ?: false;

        // Envoi de l'email aux responsables ou à l'adresse par défaut
        if (!empty($emails_responsables)) {
            $dest = is_array($emails_responsables) ? implode(',', $emails_responsables) : $emails_responsables;
            if (!facteur_envoyer_app($dest, $sujet, $html, $bcc, array('enqueued' => true, 'use_queue' => false))) {
                association_log('email', "Échec de l'envoi de l'email aux responsables", 'erreur');
                return false;
            }
        } elseif (!empty($config_envoi_email_notif_defaut)) {
            // Utiliser le parser unifié pour gérer les multiples emails séparés par virgule/point-virgule
            $parsed_emails = parser_emails_depuis_config(
                is_array($config_envoi_email_notif_defaut)
                    ? implode(',', $config_envoi_email_notif_defaut)
                    : (string)$config_envoi_email_notif_defaut
            );
            if (!$parsed_emails) {
                association_log('email', "Configuration 'config_envoi_email_notif_defaut' invalide ou vide", 'erreur');
                return false;
            }
            $dest = implode(',', $parsed_emails);
            if (!facteur_envoyer_app($dest, $sujet, $html, $bcc, array('enqueued' => true, 'use_queue' => false))) {
                association_log('email', "Échec de l'envoi de l'email à l'adresse par défaut", 'erreur');
                return false;
            }
        } else {
            association_log('email', "Pas de destinataire pour l'email aux responsables", 'erreur');
            return false;
        }

        return true;
    } catch (Exception $e) {
        association_log('email', "Erreur lors de l'envoi de l'email aux responsables: " . $e->getMessage(), 'erreur');
        return false;
    }
}

/**
 * Détermine les modèles et sujets d'emails pour l'adhérent selon le type d'action
 *
 * @param string $type Type d'action
 * @return array Tableau contenant le sujet et le modèle d'email
 */
function _determiner_modeles_emails_adherent($type) {
    switch ($type) {
        // Désinscription
        case 'desinscription_frontend':
            return ['notifications:desinscription_activite_mail_sujet_frontend', 'notifications/desinscription_activite_frontend'];
        case 'desinscription_backend':
            return ['notifications:desinscription_activite_mail_sujet_backend', 'notifications/desinscription_activite_backend'];
        // Inscription
        case 'inscription_frontend':
            return ['notifications:inscription_activite_mail_sujet_frontend', 'notifications/inscription_activite_frontend'];
        case 'inscription_backend':
            return ['notifications:inscription_activite_mail_sujet_backend', 'notifications/inscription_activite_backend'];
        // Modification
        case 'modification_frontend':
            return ['notifications:modification_activite_mail_sujet_frontend', 'notifications/modification_activite_frontend'];
        case 'modification_backend':
            return ['notifications:modification_activite_mail_sujet_backend', 'notifications/modification_activite_backend'];
        // Préinscription
        case 'preinscription_frontend':
            return ['notifications:preinscription_activite_mail_sujet_frontend', 'notifications/preinscription_activite_frontend'];
        case 'preinscription_backend':
            return ['notifications:preinscription_activite_mail_sujet_backend', 'notifications/preinscription_activite_backend'];
        // Liste d'attente
        case 'attente_frontend':
            return ['notifications:attente_activite_mail_sujet_frontend', 'notifications/attente_activite_frontend'];
        case 'attente_backend':
            return ['notifications:attente_activite_mail_sujet_backend', 'notifications/attente_activite_backend'];
        // Automatiques
        case 'inscription_automatique':
            return ['notifications:inscription_automatique_activite_mail_sujet', 'notifications/inscription_automatique_activite'];
        case 'preinscription_automatique':
            return ['notifications:preinscription_automatique_activite_mail_sujet', 'notifications/preinscription_automatique_activite'];
        case 'expiration_automatique':
            return ['notifications:expiration_automatique_activite_mail_sujet', 'notifications/expiration_automatique_activite'];
        default:
            association_log('email', "Type d'action non reconnu pour l'adhérent: " . $type, 'erreur');
            return ['', ''];
    }
}

/**
 * Détermine les modèles et sujets d'emails pour les responsables selon le type d'action
 *
 * @param string $type Type d'action
 * @param int $nombre_inscrits Nombre d'inscrits
 * @return array Tableau contenant le sujet et le modèle d'email
 */
function _determiner_modeles_emails_responsable($type, $nombre_inscrits) {
    switch ($type) {
        case 'preinscription_frontend':
            $sujet = ($nombre_inscrits == 1)
                ? 'notifications:preinscription_activite_mail_sujet_responsable_frontend'
                : 'notifications:preinscription_activite_mail_sujet_responsable_frontend_p';
            return [$sujet, 'notifications/preinscription_activite_responsable_frontend'];
        case 'desinscription_frontend':
            $sujet = ($nombre_inscrits == 1)
                ? 'notifications:desinscription_activite_mail_sujet_responsable_frontend'
                : 'notifications:desinscription_activite_mail_sujet_responsable_frontend_p';
            return [$sujet, 'notifications/desinscription_activite_responsable_frontend'];
        case 'inscription_frontend':
            $sujet = ($nombre_inscrits == 1)
                ? 'notifications:inscription_activite_mail_sujet_responsable_frontend'
                : 'notifications:inscription_activite_mail_sujet_responsable_frontend_p';
            return [$sujet, 'notifications/inscription_activite_responsable_frontend'];
        case 'modification_frontend':
            return ['notifications:modification_activite_mail_sujet_responsable_frontend', 'notifications/modification_activite_responsable_frontend'];
        case 'attente_frontend':
            $sujet = ($nombre_inscrits == 1)
                ? 'notifications:attente_activite_mail_sujet_responsable_frontend'
                : 'notifications:attente_activite_mail_sujet_responsable_frontend_p';
            return [$sujet, 'notifications/attente_activite_responsable_frontend'];
        case 'inscription_automatique':
            $sujet = ($nombre_inscrits == 1)
                ? 'notifications:inscription_automatique_activite_mail_sujet_responsable'
                : 'notifications:inscription_automatique_activite_mail_sujet_responsable_p';
            return [$sujet, 'notifications/inscription_automatique_activite_responsable'];
        case 'preinscription_automatique':
            $sujet = ($nombre_inscrits == 1)
                ? 'notifications:preinscription_automatique_activite_mail_sujet_responsable'
                : 'notifications:preinscription_automatique_activite_mail_sujet_responsable_p';
            return [$sujet, 'notifications/preinscription_automatique_activite_responsable'];
        case 'expiration_automatique':
            $sujet = ($nombre_inscrits == 1)
                ? 'notifications:expiration_automatique_activite_mail_sujet_responsable'
                : 'notifications:expiration_automatique_activite_mail_sujet_responsable_p';
            return [$sujet, 'notifications/expiration_automatique_activite_responsable'];
        default:
            association_log('email', "Type d'action non reconnu pour les responsables: " . $type, 'erreur');
            return ['', ''];
    }
}

