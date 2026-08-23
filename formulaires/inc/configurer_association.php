<?php

// **********************************************
//   DATA preparation for cotisation configuration
// **********************************************


// Function to prepare the list of payment methods
function preparer_choix_mode_paiement(){

    include_spip('inc/bank');
    $bank_lister_configs = bank_lister_configs();
    $mode_paiement_liste= $mode_paiement_listes =array();

    foreach($bank_lister_configs as  $val) {
        $mode_paiement_liste = is_array($mode_paiement_liste)? $mode_paiement_liste : array($mode_paiement_liste);
        if($val['actif']== 1 ){
            $presta_id = bank_config_id($val);
            $presta_type = $val['presta'];
            $presta_label = empty($val['label']) ? '' :  ' (' .$val['label'] . ')';
            $mode_paiement_listes +=   array($presta_id => _T('bank:label_presta_'.$presta_type.'') . $presta_label);
        }
    }

    return $mode_paiement_listes;
}
//
function identifier_tresorier(){
    $query_auteur = sql_fetsel('*','spip_auteurs',"statut = '0minirezo' AND statut_interne = 'ok' AND fonction IN ('tresoriere','tresorier')");
    return $query_auteur['nom_famille'] . '' . $query_auteur['prenom'];
}


// Function to check if at least one categorie_adherent of type 'entreprise' exists, if yes return 'true'
function verifier_categorie_adherent_entreprise(){
    return (bool) pipeline('association_configuration_categorie_entreprise', array(
        'args' => array(),
        'data' => false,
    ));

}


/**
 * Prépare la liste des pages uniques (articles avec id_rubrique = -1 et statut publié)
 * pour la sélection dans la configuration des modalités d'inscription.
 *
 * @return string Liste au format saisies_tableau2chaine (clé = champ page, valeur = titre)
 */
function preparer_liste_pages_uniques() {
    $articles = sql_allfetsel(
        'id_article, titre, page',
        'spip_articles',
        "id_rubrique = -1 AND statut = 'publie' AND page != ''"
    );
    $data = [];
    foreach ($articles as $article) {
        $data[$article['page']] = supprimer_numero($article['titre']);
    }
    return saisies_tableau2chaine($data);
}

// Function to prepare the list of zones
function preparer_liste_zones(){
    $zones = sql_allfetsel('id_zone,titre','spip_zones');
    // Initialize an empty array
    $zones_array = array();
    // Loop through the fetched data and reformat it
    foreach ($zones as $zone) {
        $zones_array[$zone['id_zone']] = $zone['titre'];
    }
    $data_zones = saisies_tableau2chaine($zones_array);
    return $data_zones;

}
// Function to prepare the list of mail subscribing lists starting by "liste_
function preparer_liste_mailsubscribinglists(){
    $listes = pipeline('association_configuration_listes_diffusion', array(
        'args' => array(),
        'data' => array(),
    ));
    return saisies_tableau2chaine(is_array($listes) ? $listes : array());
}

// Function to prepare the list of segments
function preparer_liste_champs_filtres(){
    $champs_extras_spip_auteurs = lire_config('champs_extras_spip_auteurs');
    $champs_exclus = array('input', 'textarea', 'explication');
    $data_champs_extras_spip_auteurs = array();
    foreach ($champs_extras_spip_auteurs as $champs_extras_spip_auteur) {
        if ($champs_extras_spip_auteur['saisie'] == 'fieldset') {
            foreach ($champs_extras_spip_auteur['saisies'] as $champs_extras_spip_auteur_sub) {
                if (!in_array($champs_extras_spip_auteur_sub['saisie'], $champs_exclus)) {
                    $data_champs_extras_spip_auteurs[$champs_extras_spip_auteur_sub['options']['nom']] = $champs_extras_spip_auteur_sub['options']['label'];
                }
            }
            continue;  // Skip to the next iteration of the outer loop
        }
        if (!in_array($champs_extras_spip_auteur['saisie'], $champs_exclus)) {
            $data_champs_extras_spip_auteurs[$champs_extras_spip_auteur['options']['nom']] = $champs_extras_spip_auteur['options']['label'];
        }
    }
    $data_selection_segment = saisies_tableau2chaine($data_champs_extras_spip_auteurs);

    return $data_selection_segment;
}
