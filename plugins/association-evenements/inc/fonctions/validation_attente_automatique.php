<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Valide automatiquement des inscriptions en attente pour un événement.
 *
 * Cette fonction parcourt les activités liées à un événement dont le statut est
 * "liste_attente" et, en fonction du nombre de places disponibles, bascule
 * certaines inscriptions vers un statut validé (préinscrit ou inscrit), met à
 * jour le journal des activités et déclenche l'envoi des notifications par
 * courriel via la file de travaux.
 *
 * Contrat d'utilisation:
 * - Entrées:
 *   - $id_evenement (int): identifiant unique de l'événement cible.
 *   - $source (string): chaîne optionnelle indiquant l'origine de l'appel (logging/trace).
 * - Sorties: aucune valeur retournée (effets de bord en base et envoi de mails).
 * - Effets:
 *   - Met à jour la table `spip_asso_activites` (champs: date, statut, journal).
 *   - Ajoute des tâches dans la file via `job_queue_add` pour notifier les participants.
 *
 * Cas limites gérés:
 * - Aucune inscription en attente: la fonction ne fait rien.
 * - Places disponibles nulles ou négatives: aucune bascule ne sera effectuée.
 * - Plusieurs inscriptions en attente: la sélection respecte la disponibilité en décrémentant au fil des itérations.
 *
 * Remarques et améliorations possibles:
 * - Sécuriser les critères SQL avec `sql_quote($id_evenement)` pour éviter toute injection.
 * - Journaliser l'origine `$source` si utile pour le debug (ligne spip_log commentée).
 * - Gérer explicitement les erreurs de file de travaux (retour de `job_queue_add`).
 */
function validation_attente_automatique($id_evenement , $source=''){

    // Normaliser l'identifiant pour éviter toute ambiguïté de type.
    $id_evenement = intval($id_evenement);

    // Ici on va chercher toutes les inscriptions en attente
    $query_activites_liste_attente = sql_select(
        'id_activite,nombre_inscrits',
        'spip_asso_activites',
        "id_evenement = $id_evenement AND statut ='liste_attente'",
        '',
        'date ASC'
    );


    // Ici la config de l'évènement
    $affichage_dans_activites  = affichage_dans_activites($id_evenement);
    $gestions_places    = gestions_places($id_evenement);
    $place_dispo = intval($gestions_places['places_disponibles']);
    $id_activites = array();

    if(!empty($query_activites_liste_attente)){
        // Test sur chaque inscription en attente pour vérifier s'il y a assez de places
        while ($query_activite_liste_attente = sql_fetch($query_activites_liste_attente)) {
            $id_activite = intval($query_activite_liste_attente['id_activite']);
            $nombre_inscrits = intval($query_activite_liste_attente['nombre_inscrits']);
            if($nombre_inscrits <= $place_dispo){
               $id_activites[] = $id_activite;
               // Décompte des places à chaque occurrence sélectionnée
               $place_dispo = $place_dispo - $nombre_inscrits;
            }
        }

        // Déterminer le type de validation et le statut cible selon la configuration d'affichage
        if(!empty($affichage_dans_activites['validation'])){
            $type = 'preinscription_automatique';
            $statut = 'preinscrit';
        }else{
             $type = 'inscription_automatique';
             $statut = 'ok';
        }

        if(is_array($id_activites) || is_object($id_activites)){
            // On envoie un email pour chaque inscrit concerné
            foreach($id_activites as $id_activite){
                $id_activite_selection = array($id_activite);

                // On met à jour le statut de l'inscription
                $query_activite = sql_fetsel('journal', 'spip_asso_activites', "id_activite = $id_activite");

                // Déterminer le message de journal en fonction du statut
                if($statut == 'preinscrit'){
                    $message_journal =  _T('association_evenements:journal_preinscription_validation_automatique');
                }elseif($statut == 'ok'){
                    $message_journal =  _T('association_evenements:journal_inscription_validation_automatique');
                }else{
                    // Valeur par défaut si jamais un autre statut est utilisé
                    $message_journal =  _T('association_evenements:journal_validation_automatique');
                }

                // Préparer l'entrée du journal: date + libellé + ancien journal
                $entree_journal = date('d/m/Y H:i') . " : " . _T('association_evenements:journal_validation_automatique') . "<br>" . $query_activite['journal'];

                // Mise à jour de l'activité (date/statut/journal)
                $date = date('Y-m-d H:i:s');
                sql_updateq(
                    'spip_asso_activites',
                    array(
                        'date'   => $date,
                        'statut' => $statut,
                        'journal'=> $entree_journal
                    ),
                    "id_activite=$id_activite"
                );

                // Ajout dans la file de travaux pour notifier par mail
                job_queue_add(
                    'facteur_envoyer_mail_activites',
                    'Notification - validation_automatique',
                    $arguments = array($id_evenement, $type, $id_activite_selection),
                    $file = '',
                    $no_duplicate = FALSE,
                    $time = 0,
                    $priority = 0
                );
            };
        }
    }
}
