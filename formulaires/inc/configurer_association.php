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
    $champs_extras_spip_auteurs = (array) lire_config('champs_extras_spip_auteurs', array());
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

/**
 * Construit une option oui/non du panneau de maintenance partagé.
 *
 * Les modules restent propriétaires des actions qu'ils déclarent ; le socle
 * fournit seulement ce petit constructeur de saisie afin de conserver une UI
 * homogène et les noms de métas historiques.
 */
function association_config_maintenance_radio($suffixe) {
    return array(
        'saisie' => 'radio',
        'options' => array(
            'nom' => 'meta_cfg_maintenance_' . $suffixe,
            'label' => _T('association_config:config_maintenance_bdd_' . $suffixe . '_label'),
            'explication' => _T('association_config:config_maintenance_bdd_' . $suffixe . '_explication'),
            'data' => array(
                'oui' => _T('association_config:oui'),
                'non' => _T('association_config:non'),
            ),
            'defaut' => 'oui',
        ),
    );
}

/**
 * Construit un seuil numérique du panneau de maintenance partagé.
 */
function association_config_maintenance_input($suffixe, $defaut) {
    return array(
        'saisie' => 'input',
        'options' => array(
            'nom' => 'meta_cfg_maintenance_' . $suffixe,
            'label' => _T('association_config:config_maintenance_bdd_' . $suffixe . '_label'),
            'explication' => _T('association_config:config_maintenance_bdd_' . $suffixe . '_explication'),
            'type' => 'number',
            'min' => 1,
            'defaut' => (string) $defaut,
        ),
    );
}

/**
 * Encapsule les réglages de maintenance d'un module, réservés aux webmestres.
 */
function association_config_maintenance_fieldset($nom, $label, array $saisies) {
    return array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => $nom,
            'label' => $label,
            'restrictions' => array(
                'voir' => array('auteur' => 'webmestre'),
                'modifier' => array('auteur' => 'webmestre'),
            ),
        ),
        'saisies' => $saisies,
    );
}
