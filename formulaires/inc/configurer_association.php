<?php

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
