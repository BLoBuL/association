<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

################################
#### GESTIONNAIRE ACTIVITES ####
################################
include_spip('inc/evenement_fermeture');

/**
 * GESTION DES AUTORISATIONS SELON LES OPTIONS DÉFINIE DANS UN ÉVÈNEMENT.
 *
 * @since 3.1.0
 * @version 0.3b
 *
 * @param array or boolean $query_evenement*
 * @return array
 */
function affichage_dans_activites($id_evenement){
    include_spip('inc/filtres');
    include_spip('agenda_fonctions');

    // Robustesse: evite une clause SQL invalide du type id_evenement=
    $id_evenement = intval($id_evenement);
    if ($id_evenement <= 0) {
        return array();
    }

    $query_evenement = sql_fetsel("*", "spip_evenements", "id_evenement=$id_evenement");
    if (!is_array($query_evenement) || empty($query_evenement)) {
        return array();
    }
    // Array
    $result = array();
    // variables locales pour dates (éviter notices et avertissements d'analyse statique)
    $date_ouverture = null;
    $date_fermeture = null;
    // Evènement
    /* Test des options */
    // Accompagnants
    $accompagnants = $query_evenement['accompagnants'];
    $result['accompagnants'] = (empty($accompagnants) OR $accompagnants == 'non')? false : true;

    // Invités hors famille
    $invites_defaut = ($GLOBALS['association_metas']['meta_cfg_event_invites'] ?? 'non');
    $invites = $query_evenement['invites'] ?? $invites_defaut;
    $result['invites'] = ($invites === 'oui');
    $result['limite_invites'] = max(1, intval($query_evenement['limite_invites'] ?? 5));
    //$result['accompagnants_famille'] = ($accompagnants == 'famille') ? 'oui' : 'non';

    // Places
    $places = $query_evenement['places'];
    $result['places'] = (empty($places) OR $places == '0')? false : true;
    // Activation ou pas de la liste d'attente
    $places_attentes= $query_evenement['file_attentes'];
    $result['attentes'] = (empty($places_attentes) OR $places_attentes == 'non' OR !$result['places'])? false : true;
    // Nombres de places permises en liste d'attente
    $places_attentes_illimite= $query_evenement['attentes'];
    $result['attentes_illimite'] = (empty($places_attentes_illimite) OR $places_attentes_illimite == '0')? true : false;
    // Validation par un responsable
    $validation = $query_evenement['validation'];
    $result['validation'] = (empty($validation) OR $validation == 'non')? false : true;

    $result['condition_inscription'] = (empty($query_evenement['condition_inscription'])) ? 'non' :  $query_evenement['condition_inscription'];
    $result['message_condition_inscription'] = $query_evenement['message_condition_inscription'];

    //Validation automatique sur paiement
    $result['validation_sur_paiement'] = $query_evenement['validation_sur_paiement'];

    //Nombre limite d'accompagnant
    $limite_places = $query_evenement['limite_places'];
    $result['limite_places'] = (empty($limite_places))? false : true;
    $result['validation_attente_automatique'] = $query_evenement['validation_attente_automatique'];
    //Gestion des types d\'inscriptions (public, strict)
    $result['type_inscrits_evenement'] = $query_evenement['type_inscrits_evenement'];

    if($result['type_inscrits_evenement'] != 'public'){
        //Création du token pour les événements privés
        $token_evenement= md5($id_evenement . $query_evenement['date_debut']);
        $result['token'] = $token_evenement;
    }
    /*Gestion des dates d\'ouverture et de fermeture des inscriptions*/
    $date_debut_evenement = $query_evenement['date_debut'];
    $heure_debut_evenement = affdate($date_debut_evenement,'H:i:s');
    $date_fin_evenement = $query_evenement['date_fin'];
    /*DATE D\'OUVERTURE*/
    $ouverture_differe = $query_evenement['ouverture_differe'];
    if($ouverture_differe == '0'){
        $date_ouverture = agenda_jourdecal(date('Y-m-d'), -1,'Y-m-d 00:00:00');
    }elseif($ouverture_differe == 'dt'){
        $date_ouverture = $query_evenement['ouverture_differe_date'] ?? null;
    }else{
        $date_ouverture = agenda_jourdecal($date_debut_evenement, '-' .$ouverture_differe, 'Y-m-d '. $heure_debut_evenement);
    }
     /*DATE DE FERMETURE*/
    $fermeture_inscription = $query_evenement['fermeture_inscription'];
    $date_fermeture = association_evenement_calculer_date_fermeture($query_evenement);
  /*STATUT OUVERTURE INSCRIPTION*/
    $date_now = date("Y-m-d H:i:s");

    // Cas explicites liés à la configuration
    if ($fermeture_inscription === 'unlimited') {
        $result['statut_ouverture_inscription'] = 'inscription_ouverte';
    } elseif ($fermeture_inscription === 'cancel') {
        $result['statut_ouverture_inscription'] = 'inscription_annule';
    } elseif ($fermeture_inscription === 'now') {
        $result['statut_ouverture_inscription'] = 'inscription_suspendue';
    }
    // Cas basés sur les dates, uniquement si celles-ci sont définies
    elseif ($date_ouverture && $date_fermeture && $date_now > $date_ouverture && $date_now < $date_fermeture) {
        $result['statut_ouverture_inscription'] = 'inscription_ouverte';
    } elseif ($date_ouverture && $date_now < $date_ouverture) {
        $result['statut_ouverture_inscription'] = 'ouverture_a_venir';
    } elseif ($date_fin_evenement && $date_now > $date_fin_evenement) {
        $result['statut_ouverture_inscription'] = 'evenement_termine';
    } elseif ($date_fermeture && $date_now > $date_fermeture) {
        $result['statut_ouverture_inscription'] = 'inscription_ferme';
    } elseif ($date_ouverture && $date_fermeture && $date_ouverture >= $date_fermeture) {
        $result['statut_ouverture_inscription'] = 'erreur_ouverture_apres_fermeture';
    } else {
        $result['statut_ouverture_inscription'] = 'erreur_statut_ouverture';
    }

    if(isset ($query_evenement['info_supplementaire'])){
        $result['info_supplementaire'] = $query_evenement['info_supplementaire'] ?? false;
    }
    // Montant dynamique

    $payant = $query_evenement['payant'];
    $result['payant'] = (empty($payant) OR $payant == '0')? false : true;


    $condition_accompagnant = ($result['accompagnants'])? '' : ' AND quantite=1';
    if( ($result['payant'])){
         include_spip('inc/bank');
        $devise_defaut = bank_devise_defaut();
        $devise = $devise_defaut['code'];
        $query_categorie = sql_select('*', 'spip_asso_categories_activites', "statut='ok'". $condition_accompagnant); // Permet de ne séléctionner que ceux en cours (en cas de supression)
        while ($categorie = sql_fetch($query_categorie)) {
            $id_categorie = intval($categorie['id_categorie']);
            $montant_row = sql_fetsel('montant', 'spip_asso_categories_activites_liens', 'id_evenement=' . intval($id_evenement) . ' AND id_categorie=' . $id_categorie, '', 'montant DESC');
            // Valeur par défaut : null = non renseigné (non applicable)
            $montant_val = null;
            if (is_array($montant_row) && array_key_exists('montant', $montant_row) && $montant_row['montant'] !== '') {
                // garder la valeur telle quelle (peut être '0' pour gratuit)
                $montant_val = $montant_row['montant'];
            }
            // N'afficher la catégorie que si un montant est renseigné (y compris 0)
            if (is_numeric($montant_val)) {
                // Afficher le symbole (bank_affiche_montant attend une valeur numérique)
                $montant_symbole = bank_affiche_montant($montant_val, $devise, 'symbol');
                $result['montant']['categorie_prix_' . $id_categorie] = array(
                    'id_categorie' => $id_categorie,
                    'type_inscrit' => $categorie['type_inscrit'],
                    'montant_symbole' => str_replace(',00', '', $montant_symbole),
                    'montant' => $montant_val,
                    'titre' => $categorie['valeur'],
                    'quantite' => $categorie['quantite'],
                    'commentaires' => $categorie['commentaires'],
                    //'payant' => $montant_val !== 0
                );
            }
        }
        sql_free($query_categorie);
    }


    // On passe la requète
    // exposer les dates calculées
    $result['date_ouverture_inscription'] = $date_ouverture;
    $result['date_fermeture_inscription'] = $date_fermeture;
    $result['query_evenement'] = $query_evenement;
    return $result;
}


