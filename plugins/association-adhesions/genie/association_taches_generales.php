<?php
/*
* GESTION DES TÃ‚CHES CRON
* VERSION = 0.1b
*/

/**
* Exécute les tâches générales planifiées pour l'association.
*
* Cette fonction est appelée via une tâche CRON et effectue les actions suivantes :
* - Vérifie la validité des adhésions des membres.
* - Retire les privilèges des membres dont l'adhésion a expiré.
* - Envoie des notifications pour les adhésions expirées ou proches de l'expiration.
* - Met à jour les statuts des utilisateurs en fonction de leur validité.
*
* @param string $tache Nom de la tâche exécutée (non utilisé ici).
* @return bool Retourne `true` une fois la tâche terminée.
*/
function genie_association_taches_generales($tache) {

    include_spip('base/abstract_sql');

    include_spip('inc/cotisations');
    include_spip('inc/fonctions/privileges_adherent');
    include_spip('inc/fonctions/association_job_notifier_echeance');

     $date_actuelle = time();
     $date_actuelle_ymd = date('Y-m-d');

     // Vérifie si les paramètres nécessaires sont configurés
     if (!$GLOBALS['association_metas']['zone_adherent']) {
     }
     if (!$GLOBALS['association_metas']['liste_diffusion']) {
     }

 $desc_auteurs = sql_showtable('spip_auteurs', true);
 $type_fields = array();
 if ($desc_auteurs) {
     if (!empty($desc_auteurs['field']['type_adherent'])) {
         $type_fields[] = 'type_adherent';
     }
     if (!empty($desc_auteurs['field']['radio_type_adherent'])) {
         $type_fields[] = 'radio_type_adherent';
     }
 }
 $select_fields = array('id_auteur','email','statut','statut_interne','validite');
 if (!empty($type_fields)) {
     $select_fields = array_merge($select_fields, $type_fields);
 }
 $query_auteurs = sql_select(implode(',', $select_fields), 'spip_auteurs', "webmestre='non'");
 $delais_cache = array();
 while ($auteur = sql_fetch($query_auteurs)) {
     $id_auteur = $auteur['id_auteur'];
     $statut = $auteur['statut'];
     $statut_interne = $auteur['statut_interne'];
     $email_adherent = $auteur['email'];
     $date_validite = '';
     $nb_jour_differences = null;
     if (!empty($auteur['validite'])) {
         $timestamp_validite = strtotime($auteur['validite']);
         if ($timestamp_validite !== false) {
             $date_validite = date('Y-m-d', $timestamp_validite);
             $nb_jour_differences = association_adhesions_nombre_jours($date_actuelle_ymd, $date_validite, false);
         }
     }

     $type_adherent = 'adherent';
     foreach ($type_fields as $field) {
         if (!empty($auteur[$field])) {
             $type_adherent = $auteur[$field];
             break;
         }
     }
     if (!isset($delais_cache[$type_adherent])) {
         $delais_cache[$type_adherent] = association_obtenir_delais_echeance($type_adherent);
     }
     $delais_notification = $delais_cache[$type_adherent];

     // Vérifie la validité des adhésions
     if ($statut_interne == 'ok') {
         if ($date_validite && $date_validite < $date_actuelle_ymd) {

             desactiver_privileges_adherent($id_auteur);
             // Met à jour le statut des membres expirés
             sql_updateq('spip_auteurs', array("statut" => '6forum', "statut_interne" => 'echu'), "id_auteur=$id_auteur");
             // Désactive les privilèges et envoie une notification
             if (!empty($GLOBALS['association_metas']['notification_adherent_echu']) && $GLOBALS['association_metas']['notification_adherent_echu'] != 'non') {
                 job_queue_add('association_job_notifier_echeance', 'Notification - notification_echeances_adherent_echu', array($id_auteur, $email_adherent, 0, 'echeance_echu', $type_adherent), '', true, 0, 0);
             }
         }

         // Envoie des notifications pour les adhésions proches de l'expiration
         if ($nb_jour_differences !== null && $nb_jour_differences > 0 && !empty($delais_notification) && in_array($nb_jour_differences, $delais_notification, true)) {
             $date_heure_envoi = $date_actuelle_ymd . ' 23:59:59';
             $format = "%d-%d-%d %d:%d:%d";
             sscanf($date_heure_envoi, $format, $annee, $mois, $jour, $heure, $minute, $seconde);
             $date_heure_envoi_timestamp = mktime($heure, $minute, $seconde, $mois, $jour, $annee);

             job_queue_add('association_job_notifier_echeance', 'Notification - notification_echeances_adherent', array($id_auteur, $email_adherent, $nb_jour_differences, 'echeance_preventive', $type_adherent), '', true, $date_heure_envoi_timestamp, 0);

         }
     } elseif (in_array($statut, array('0minirezo', '1comite'))) {
         // Passe les administrateurs et rédacteurs en visiteurs si leur adhésion est expirée
         sql_updateq('spip_auteurs', array("statut" => '6forum'), "id_auteur=$id_auteur");
     }

     // Vérifie les privilèges des adhérents
     verifier_privileges_adherent($id_auteur);
 }

 // Enregistrement de la tâche dans le log
 return true;
}

