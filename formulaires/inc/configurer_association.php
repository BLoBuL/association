<?php

// Function to check if at least one categorie_adherent of type 'entreprise' exists, if yes return 'true'
function verifier_categorie_adherent_entreprise(){
    $flux = pipeline('association_configuration_categorie_entreprise', array(
        'args' => array(),
        'data' => false,
    ));
    return (bool) ($flux['data'] ?? false);

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
    $flux = pipeline('association_configuration_listes_diffusion', array(
        'args' => array(),
        'data' => array(),
    ));
    $listes = $flux['data'] ?? array();
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
