<?php
/**
* Plugin Association
* (c) 2004-2023 SPIP
* Distribue sous licence GNU/GPL
*
* @package SPIP\association\base
*/
if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Lire une configuration du plugin sans supposer que association_options.php
 * a déjà initialisé $GLOBALS['association_metas'].
 *
 * @param string $cle
 * @param mixed $defaut
 * @return mixed
 */
function association_champs_extras_meta($cle, $defaut = '') {
	if (isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']) && array_key_exists($cle, $GLOBALS['association_metas'])) {
		return $GLOBALS['association_metas'][$cle];
	}

	include_spip('inc/config');
	$valeur = lire_config('association_metas/' . $cle, null);
	return ($valeur !== null) ? $valeur : $defaut;
}


// Modification de la fonction principale pour passer les données à toutes les fonctions qui en ont besoin
function association_declarer_champs_extras_impl($champs=array()){

// Préparation des données communes utilisées par plusieurs sections
$donnees_communes = association_preparer_donnees_communes();

// Ajout des champs de base de l'événement
$champs = association_champs_evenement_base($champs, $donnees_communes);

// Ajout des champs d'inscription
$champs = association_champs_inscription($champs, $donnees_communes);

// Ajout des champs de validation
$champs = association_champs_validation($champs);

// Ajout des champs pour événements payants
$champs = association_champs_paiement($champs, $donnees_communes);

// Ajout des champs pour accompagnants
$champs = association_champs_accompagnants($champs);

// Ajout des champs pour file d'attente
$champs = association_champs_attente($champs);

// Ajout des champs pour conditions d'inscription
$champs = association_champs_conditions_inscription($champs, $donnees_communes);

// Ajout des informations supplémentaires si activées
$metas = isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : array();
if (($metas['meta_cfg_event_form_info_supp'] ?? '') === 'oui') {
    $champs = association_champs_info_supplementaire($champs);
}

// Ajout des champs de communication FIAFE si activés
if (($metas['meta_cfg_event_reseau_fiafe'] ?? '') === 'active') {
    $champs = association_champs_communication_fiafe($champs);
}

return $champs;
}


/**
* Prépare les données communes utilisées par plusieurs sections.
*
* Cette fonction rassemble et prépare les données nécessaires pour
* différentes sections du formulaire, comme les responsables, les modes
* de paiement, et les paramètres liés à la liste des inscrits.
*
* @return array Tableau contenant les données communes préparées.
*/
function association_preparer_donnees_communes() {
// Inclusion des fonctions SQL nécessaires
include_spip('base/abstract_sql');

$donnees = [];
$metas = isset($GLOBALS['association_metas']) && is_array($GLOBALS['association_metas']) ? $GLOBALS['association_metas'] : array();

if (!function_exists('responsables_evenement')) {
	include_spip('association_evenements_options');
}

// Préparation des responsables
if (intval(_request('id_evenement')) >= 1 OR intval(_request('id_article')) >= 1) {
    $id_evenement = (_request('id_evenement')) ? intval(_request('id_evenement')) : 'new';
    $id_article = _request('id_article');


    // Récupération des responsables associés à l'événement ou à l'article
    if ($liste_responsable = responsables_evenement($id_evenement, $id_article)) {

        $data_input_choix_responsable = array();
        while ($responsable = sql_fetch($liste_responsable['condition'])) {
            $data_input_choix_responsable += array(
                $responsable['id_auteur'] => '' . $responsable['nom_famille'] . ' ' . $responsable['prenom'] . ''
            );
        }
        $disable = false;
    } else {
        // Si aucun responsable n'est trouvé
        $data_input_choix_responsable = [];
        $liste_responsable = [];
        $liste_responsable['auteur_array'] = [];
        $liste_responsable['auteur_array_defaut'] = [];
        $disable = true;
    }
} else {
    // Si aucun événement ou article n'est spécifié
    $data_input_choix_responsable = [];
    $liste_responsable = [];
    $liste_responsable['auteur_array'] = [];
    $liste_responsable['auteur_array_defaut'] = [];
    $disable = true;
}

// Ajout des données des responsables au tableau des données
$donnees['data_input_choix_responsable'] = $data_input_choix_responsable;
$donnees['liste_responsable'] = $liste_responsable;
$donnees['disable'] = $disable;

// Préparation des modes de paiement
include_spip('inc/bank');
if (function_exists('bank_lister_configs')) {
    $bank_lister_configs = bank_lister_configs();
    $mode_paiement_liste = $mode_paiement_listes = array();
    if (!empty($metas['mode_paiement_participation'])) {
        $tmp = @unserialize($metas['mode_paiement_participation']);
        if ($tmp !== false && is_array($tmp)) {
            $mode_paiement_liste = $tmp;
        }
    }

    // Parcours des configurations de paiement disponibles
    foreach ($bank_lister_configs as $val) {
        $mode_paiement_liste = is_array($mode_paiement_liste) ? $mode_paiement_liste : array($mode_paiement_liste);
        if (in_array(bank_config_id($val), $mode_paiement_liste, true)) {
            $presta_id = bank_config_id($val);
            $presta_type = $val['presta'];
            $presta_label = empty($val['label']) ? '' : ' (' . $val['label'] . ')';
            $mode_paiement_listes += array(
                $presta_id => _T('bank:label_presta_' . $presta_type . '') . $presta_label
            );
        }
    }
}

// Ajout des données des modes de paiement au tableau des données
$donnees['mode_paiement_liste'] = $mode_paiement_liste ?? array();
$donnees['mode_paiement_listes'] = $mode_paiement_listes ?? array();

// Paramètres pour l'affichage de la liste des inscrits
$afficher_liste = $metas['meta_cfg_event_afficher_liste_inscrits'] ?? '';
if ($afficher_liste === 'toujours') {
    $afficher_liste_inscrits_defaut = '1';
    $afficher_liste_inscrits_disable_avec_post = '1';
} elseif ($afficher_liste === 'jamais') {
    $afficher_liste_inscrits_defaut = '0';
    $afficher_liste_inscrits_disable_avec_post = '1';
} else {
    $afficher_liste_inscrits_defaut = $afficher_liste;
    $afficher_liste_inscrits_disable_avec_post = '0';
}

// Ajout des paramètres de la liste des inscrits au tableau des données
$donnees['afficher_liste_inscrits_defaut'] = $afficher_liste_inscrits_defaut;
$donnees['afficher_liste_inscrits_disable_avec_post'] = $afficher_liste_inscrits_disable_avec_post;

return $donnees;
}
/**
* Ajoute les champs de base pour les événements
*
* @param array $champs Tableau des champs existants
* @return array Tableau des champs avec les champs de base ajoutés
*/
function association_champs_evenement_base($champs, $donnees) {
// Champs descriptifs
$champs['spip_evenements']['descriptif'] = array(
    "saisie" => 'textarea',
    "source" => 'agenda',
    "options" => array(
        "nom" => 'descriptif',
        "label" => _T('agenda:evenement_descriptif'),
        "sql" => "TEXT NOT NULL",
        "defaut" => '',
        "explication" => _T('association_evenements:evenement_descriptif_textarea_explication'),
        "class" => 'inserer_barre_edition inserer_previsualisation',
        "rows" => 5,
    ),
);

$champs['spip_evenements']['descriptif_securise'] = array(
    "saisie" => 'textarea',
    "source" => 'association',
    "options" => array(
        "nom" => 'descriptif_securise',
        "label" => _T('association_evenements:evenement_descriptif_securise_textarea'),
        "sql" => "TEXT NOT NULL",
        "defaut" => '',
        "explication" => _T('association_evenements:evenement_descriptif_securise_textarea_explication'),
        "traitements" => '_TRAITEMENT_RACCOURCIS',
        "inserer_barre" => 'edition',
        "rows" => 5,
        "class" => 'pleine_largeur',
    ),
);

// Type d'événement (présentiel/en ligne)
$champs['spip_evenements']['presentiel'] = array(
    "saisie" => 'radio',
    "options" => array(
        "nom" => 'presentiel',
        "label" => _T('association_evenements:presentiel_label'),
        "sql" => "varchar(12) NOT NULL",
        "defaut" => 'oui',
        "datas" => array(
            "oui" => _T('association_evenements:choix_presentiel'),
            "non" => _T('association_evenements:choix_en_ligne'),
        ),
        "explication" => _T('association_evenements:presentiel_explication'),
    )
);

// Lieu et adresse (si présentiel)
$champs['spip_evenements']['lieu'] = array(
    "saisie" => 'input',
    "source" => 'lieu',
    "options" => array(
        "nom" => 'lieu',
        "label" => _T('agenda:evenement_lieu'),
        "sql" => "TEXT NOT NULL",
        "defaut" => '',
        "explication" => _T('association_evenements:evenement_lieu_input_explication'),
        "afficher_si" => '@presentiel@ != "non" ',
    ),
);

$champs['spip_evenements']['adresse'] = array(
    "saisie" => 'input',
    "source" => 'adresse',
    "options" => array(
        "nom" => 'adresse',
        "label" => _T('agenda:evenement_adresse'),
        "sql" => "TEXT NOT NULL",
        "defaut" => '',
        "explication" => _T('association_evenements:evenement_adresse_input_explication'),
        "rows" => 3,
        "afficher_si" => '@presentiel@ != "non" ',
    ),
);

// Lien (si en ligne)
$champs['spip_evenements']['lien'] = array(
    "saisie" => 'input',
    "source" => 'lien',
    "options" => array(
        "nom" => 'lien',
        "label" => _T('association_evenements:evenement_lien_label'),
        "sql" => "varchar(255)",
        "defaut" => '',
        "explication" => _T('association_evenements:evenement_lien_explication'),
        "afficher_si" => '@presentiel@ == "non" ',
    ),
);
$champs['spip_evenements']['responsables']= array(
    // -----------------------------------------------
    //CHOIX DES RESPONSABLES
    // -----------------------------------------------
    "saisie" => 'checkbox',
    "source" => 'association',
    "options" => array(
        "nom" => 'responsables',
        "label" => _T('association_evenements:choix_responsables_evenement_label'),
        "datas" =>  $donnees['data_input_choix_responsable'],
        "sql" => "varchar(255) NOT NULL",
        "defaut" => $donnees['liste_responsable']['auteur_array_defaut'],
        "explication" =>  _T('association_evenements:choix_responsables_evenement_explication'),
        "disable" => $donnees['disable'],
    )
);


// -----------------------------------------------

$champs['spip_evenements']['inscription']= array(
    "saisie" => 'radio',
    "source" => 'agenda',
    "options" => array(
        "nom" => 'inscription',
        "label" => _T('association_evenements:label_inscription'),
        "sql" => "BOOLEAN NOT NULL",
        "datas" => array(
            "1" =>   _T('association_evenements:oui'),
            "0" =>   _T('association_evenements:non'),
        ),
    )
);
    return $champs;
}
/**
* Ajoute les champs d'inscription pour les événements
*
* @param array $champs Tableau des champs existants
* @param array $donnees Tableau de données communes
* @return array Tableau des champs avec les champs d'inscription ajoutés
*/

function association_champs_inscription($champs, $donnees) {
// Initialisation des valeurs par défaut si non définies
if (!is_array($donnees)) {
    $donnees = array();
}
$champs['spip_evenements']['fieldset_inscription_en_ligne'] = array(
    "saisie" => 'fieldset',
    "options" => array(
        "nom" => "fieldset_inscription_en_ligne",
        "label" => _T('association_evenements:label_inscription_en_ligne_fieldset'),
        "afficher_si" => '@inscription@ == "1"',
    ),
    'saisies' => array(
        'places' => array(
            "saisie" => 'input',
            "source" => 'agenda',
            "options" => array(
                "nom" => 'places',
                "label" =>  _T('association_evenements:label_places'),
                "sql" => "int(11) DEFAULT '0' NOT NULL",
                "explication" => _T('association_evenements:label_places_explication'),
            )
        ),

        // -----------------------------------------------
        //                   TYPE D'INSCRIT
        // -----------------------------------------------
        'type_inscrits_evenement' => array(
            "saisie" => 'selection',
            "source" => 'association',
            "options" => array(
                "nom" => 'type_inscrits_evenement',
                "label" => _T('association_evenements:evenement_type_inscrits_label'),
                "explication" => _T('association_evenements:evenement_type_inscrits_explication'),
                "sql" => "VARCHAR(30) DEFAULT 'strict' NOT NULL",
                "disable_avec_post" => (association_champs_extras_meta('meta_cfg_event_type_inscrits_evenement', 'prive') == 'only_strict' ) ? '1' : '0',
                "cacher_option_intro" => '1',
                "datas" =>
                    array(
                        'public' => _T('association_evenements:evenement_type_inscrits_public'),
                        'prive' => _T('association_evenements:evenement_type_inscrits_prive'),
                        'strict' => _T('association_evenements:evenement_type_inscrits_strict'),
                    ),
            ),
        ),
        'afficher_liste_inscrits' => array(
            "saisie" => 'radio',
            "source" => 'association',
            "options" => array(
                "nom" => 'afficher_liste_inscrits',
                "label" => _T('association_evenements:afficher_liste_inscrits_label'),
                "sql" => "BOOLEAN NULL",
                //"defaut" =>$afficher_liste_inscrits_defaut,
                "explication" => _T('association_evenements:afficher_liste_inscrits_explication'),
                "disable_avec_post" => $donnees['afficher_liste_inscrits_disable_avec_post'],
                "datas" => array(
                    "1" =>   _T('association_evenements:oui'),
                    "0" =>   _T('association_evenements:non'),
                ),

            ),

        ),
        'ouverture_differe' => array(
            "saisie" => 'selection',
            "source" => 'association',
            "options" => array(
                "nom" => 'ouverture_differe',
                "label" => _T('association_evenements:evenement_ouverture_differe_date'),
                "explication" => _T('association_evenements:evenement_date_ouverture_differe_explication'),
                "sql" => "VARCHAR(2) DEFAULT '0' NOT NULL",
                "cacher_option_intro" => '1',
                "datas" =>
                    array (
                        "0" => _T('association_evenements:evenement_date_ouverture_differe_desactive'),
                        "dt" => _T('association_evenements:evenement_date_ouverture_differe_date'),
                        "7" => _T('association_evenements:evenement_date_ouverture_differe_une_semaine'),
                        "14" => _T('association_evenements:evenement_date_ouverture_differe_deux_semaines'),
                        "21" => _T('association_evenements:evenement_date_ouverture_differe_trois_semaines'),
                        "28" => _T('association_evenements:evenement_date_ouverture_differe_un_mois'),
                        "42" => _T('association_evenements:evenement_date_ouverture_differe_un_mois_et_demi'),
                        "56" => _T('association_evenements:evenement_date_ouverture_differe_deux_mois'),
                    ),
                "defaut" => association_champs_extras_meta('meta_cfg_event_ouverture_differe', '0'),
                // "afficher_si" => '@inscription@ == "1"',
            )

        ),
        'ouverture_differe_date' => array(
            "saisie" => 'date',
            "source" => 'association',
            "options" => array(
                "nom" => 'ouverture_differe_date',
                "label" => _T('association_evenements:evenement_ouverture_differe_date_label'),
                "explication" => _T('association_evenements:evenement_ouverture_differe_date_explication'),
                "obligatoire" => 'oui',
                "sql" => "DATETIME NULL DEFAULT NULL",
                "defaut" => '',
                "horaire" => 1,
                "afficher_si" => '@ouverture_differe@ == "dt" ',
                //"afficher_si_avec_post" => True,
            ),
            "verifier" => array(
                "type" => 'date',
                "options" => array(
                    "normaliser" => 'datetime'
                )
            )
        ),

        'fermeture_inscription' => array(
            "saisie" => 'selection',
            "source" => 'association',
            "options" => array(
                "nom" => 'fermeture_inscription',
                "label" => _T('association_evenements:evenement_fermeture_inscription_label'),
                "explication" => _T('association_evenements:evenement_fermeture_inscription_explication'),
                "sql" => "VARCHAR(12) DEFAULT 'last_minute' NOT NULL",
                "cacher_option_intro" => '1',
                "datas" =>
                    array (
                        "last_minute" => _T('association_evenements:evenement_fermeture_inscription_last_minute'),
                        "dt" => _T('association_evenements:evenement_fermeture_inscription_date_precise'),
                        "midnight" => _T('association_evenements:evenement_fermeture_inscription_midnight'),
                        "midi" => _T('association_evenements:evenement_fermeture_inscription_midi'),
                        "24h" => _T('association_evenements:evenement_fermeture_inscription_24h'),
                        "48h" => _T('association_evenements:evenement_fermeture_inscription_48h'),
                        "72h" => _T('association_evenements:evenement_fermeture_inscription_72h'),
                        "96h" => _T('association_evenements:evenement_fermeture_inscription_96h'),
                        "7j" => _T('association_evenements:evenement_fermeture_inscription_7j'),
                        "14j" => _T('association_evenements:evenement_fermeture_inscription_14j'),
                        "21j" => _T('association_evenements:evenement_fermeture_inscription_21j'),
                        "30j" => _T('association_evenements:evenement_fermeture_inscription_30j'),
                        "unlimited" => _T('association_evenements:evenement_fermeture_pas_de_limite'),
                        "now" => _T('association_evenements:evenement_fermeture_suspendre_inscription'),
                        "cancel" => _T('association_evenements:evenement_fermeture_annule_inscription'),
                    ),
                "defaut" => association_champs_extras_meta('meta_cfg_event_inscription_deadline', 'last_minute')
            )
        ),
        'fermeture_inscription_date' => array(
            "saisie" => 'date',
            "source" => 'association',
            "options" => array(
                "nom" => 'fermeture_inscription_date',
                "label" => _T('association_evenements:evenement_fermeture_inscription_date_label'),
                "explication" => _T('association_evenements:evenement_fermeture_inscription_date_explication'),
                "obligatoire" => 'oui',
                "sql" => "DATETIME NULL DEFAULT NULL",
                "defaut" => '',
                "horaire" => 1,
                "afficher_si" => '@fermeture_inscription@ == "dt"',
            ),
            "verifier" => array(
                "type" => 'date',
                "options" => array(
                    "normaliser" => 'datetime'
                )
            )
        ),
    )
);
return $champs;
}
/**
* Ajoute les champs de validation pour les événements
*
* @param array $champs Tableau des champs existants
* @return array Tableau des champs avec les champs de validation ajoutés
*/
function association_champs_validation($champs) {
$champs['spip_evenements']['evenement_fieldset'] = array(
    "saisie" => 'fieldset',
    "options" => array(
        "nom" => "association",
        "label" => _T('association_evenements:evenement_fieldset'),
        "afficher_si" => '@inscription@ == "1"'
    ),
    'saisies' => array(
        'validation' => array(
            "saisie" => 'radio',
            "source" => 'association',
            "options" => array(
                "nom" => 'validation',
                "label" => _T('association_evenements:evenement_validation_radio_label'),
                "sql" => "varchar(30) NOT NULL",
                "defaut" => 'oui',
                "datas" => array(
                    'oui' =>  _T('association_evenements:evenement_validation_radio_oui'),
                    'non' =>  _T('association_evenements:evenement_validation_radio_non'),
                ),
                "explication" => _T('association_evenements:evenement_validation_radio_explication'),
            ),
        ),
    ),
);

return $champs;
}
/**
 * Ajoute les champs pour les événements payants
 *
 * @param array $champs Tableau des champs existants
 * @param array $donnees_communes Tableau de données communes
 * @return array Tableau des champs avec les champs de paiement ajoutés
 */
function association_champs_paiement($champs, $donnees_communes) {
    // Inclusion des fonctions SQL nécessaires
    include_spip('base/abstract_sql');

    // Préparation des données pour modes de paiement
    $mode_paiement_liste = $mode_paiement_listes = array();

    if (function_exists('bank_lister_configs')) {
        $bank_lister_configs = bank_lister_configs();
        $mode_paiement_participation = association_champs_extras_meta('mode_paiement_participation', '');
        $mode_paiement_liste = !empty($mode_paiement_participation) ?
            unserialize($mode_paiement_participation) : array();

        foreach ($bank_lister_configs as $val) {
            $mode_paiement_liste = is_array($mode_paiement_liste) ? $mode_paiement_liste : array($mode_paiement_liste);
            if (in_array(bank_config_id($val), $mode_paiement_liste, true)) {
                $presta_id = bank_config_id($val);
                $presta_type = $val['presta'];
                $presta_label = empty($val['label']) ? '' : ' (' . $val['label'] . ')';
                $mode_paiement_listes += array($presta_id => _T('bank:label_presta_' . $presta_type . '') . $presta_label);
            }
        }
    }

    // Fieldset principal pour événement payant
    $champs['spip_evenements']['evenement_payant'] = array(
        "saisie" => 'fieldset',
        "options" => array(
            "nom" => "evenement_payant",
            "label" => _T('association_evenements:evenement_payant_fieldset'),
            "afficher_si" => '@inscription@ == "1"',
        ),
        'saisies' => array(
            'payant' => array(
                "saisie" => 'radio',
                "source" => 'association',
                "options" => array(
                    "nom" => 'payant',
                    "label" => _T('association_evenements:evenement_payant_radio_label'),
                    "sql" => "BOOLEAN NOT NULL",
                    "defaut" => '0',
                    "cacher_option_intro" => '1',
                    "datas" => array(
                        "1" => _T('association_evenements:evenement_payant_radio_oui'),
                        "0" => _T('association_evenements:evenement_payant_radio_non')
                    ),
                    "explication" => _T('association_evenements:evenement_payant_radio_explication'),
                ),
            ),
            'validation_sur_paiement' => array(
                "saisie" => 'radio',
                "source" => 'association',
                "options" => array(
                    "nom" => 'validation_sur_paiement',
                    "label" => _T('association_evenements:validation_auto_sur_paiement_label'),
                    "sql" => "char(3) NOT NULL",
                    "defaut" => 'oui',
                    "datas" => array(
                        'oui' => _T('association_evenements:validation_auto_sur_paiement_oui'),
                        'non' => _T('association_evenements:validation_auto_sur_paiement_non'),
                    ),
                    // Le champ 'validation_sur_paiement' ne doit pas tester sa propre valeur
                    // (cela provoque des erreurs d'évaluation d'afficher_si en front).
                    // On affiche simplement ce champ si l'événement est payant.
                    "afficher_si" => '@payant@ == "1"',
                    "explication" => _T('association_evenements:validation_auto_sur_paiement_explication'),
                ),
            )
        ),
    );

    // Ajout des catégories de prix
    $saisie_categorie = array();

    $categorie_querie = sql_select('*', 'spip_asso_categories_activites', "statut='ok'");
    while ($categorie = sql_fetch($categorie_querie)) {
        $saisie_categorie['categorie_prix_' . $categorie['id_categorie']] = array(
            "saisie" => 'input',
            "source" => 'association',
            "options" => array(
                "nom" => 'categorie_prix_' . $categorie['id_categorie'],
                "label" => $categorie['valeur'],
                // Supprimer ou commenter cette ligne pour ne pas créer de colonne SQL
                // "sql" => "TEXT DEFAULT NULL",
                "defaut" => '',
                "explication" => $categorie['commentaires'],
                "afficher_si" => '@payant@ == "1"',
            )
        );
    }

    $saisie_categorie['mode_paiement'] = array(
        "saisie" => 'checkbox',
        "source" => 'association',
        "options" => array(
            "nom" => 'mode_paiement',
            "label" => _T('association_evenements:mode_paiement_evenement_label'),
            "datas" => $mode_paiement_listes,
            "sql" => "varchar(255) NOT NULL",
            "defaut" => $mode_paiement_liste,
            "explication" => _T('association_evenements:mode_paiement_evenement_explication'),
            "afficher_si" => '@payant@ == "1"',
        ),
    );

    $champs['spip_evenements']['categorie_prix'] = array(
        "saisie" => 'fieldset',
        "options" => array(
            "nom" => "categorie_prix",
            "label" => _T('association_evenements:evenement_montant_label'),
            "afficher_si" => '@payant@ == "1"',
            "explication" => _T('association_evenements:evenement_montant_explication')
        ),
        'saisies' => $saisie_categorie,
    );

    return $champs;
}
/**
* Ajoute les champs pour les accompagnants
*
* @param array $champs Tableau des champs existants
* @return array Tableau des champs avec les champs d'accompagnants ajoutés
*/
function association_champs_accompagnants($champs) {
$champs['spip_evenements']['accompagnant'] = array(
    "saisie" => 'fieldset',
    "options" => array(
        "nom" => "accompagnant",
        "label" => _T('association_evenements:accompagnant_fieldset'),
        "afficher_si" => '@inscription@ == "1"'
    ),
    'saisies' => array(
        'accompagnants' => array(
            "saisie" => 'radio',
            "source" => 'association',
            "options" => array(
                "nom" => 'accompagnants',
                "label" => _T('association_evenements:evenement_accompagnant_radio_label'),
                "sql" => "varchar(30) NOT NULL",
                "defaut" => 'non',
                "cacher_option_intro" => '1',
                "datas" => array(
                    'oui' => _T('association_evenements:evenement_accompagnants_radio_oui'),
                    'non' => _T('association_evenements:evenement_accompagnants_radio_non'),
                ),
                "explication" => _T('association_evenements:evenement_montant_radio_explication'),
            ),
        ),
        'limite_places' => array(
            "saisie" => 'selection',
            "source" => 'association',
            "options" => array(
                "nom" => 'limite_places',
                "label" => _T('association_evenements:evenement_accompagnant_limite_label'),
                "sql" => "INT(10) NOT NULL",
                "defaut" => '2',
                "explication" => _T('association_evenements:evenement_accompagnant_explication'),
                "afficher_si" => '@accompagnants@ != "non"',
                "datas" => array(
                    '2' => _T('association_evenements:evenement_nb_participant', array('nb'=>2)),
                    '3' => _T('association_evenements:evenement_nb_participant', array('nb'=>3)),
                    '4' => _T('association_evenements:evenement_nb_participant', array('nb'=>4)),
                    '5' => _T('association_evenements:evenement_nb_participant', array('nb'=>5)),
                    '6' => _T('association_evenements:evenement_nb_participant', array('nb'=>6)),
                    '7' => _T('association_evenements:evenement_nb_participant', array('nb'=>7)),
                    '8' => _T('association_evenements:evenement_nb_participant', array('nb'=>8)),
                    '9' => _T('association_evenements:evenement_nb_participant', array('nb'=>9)),
                    '10' => _T('association_evenements:evenement_nb_participant', array('nb'=>10)),
                ),
            ),
        ),
        // Invités hors famille : on/off par événement
        'invites' => array(
            "saisie" => 'radio',
            "source" => 'association',
            "options" => array(
                "nom" => 'invites',
                "label" => _T('association_evenements:evenement_invites_radio_label'),
                "sql" => "varchar(3) NOT NULL DEFAULT 'non'",
                "defaut" => ($GLOBALS['association_metas']['meta_cfg_event_invites'] ?? 'non'),
                "cacher_option_intro" => '1',
                "datas" => array(
                    'oui' => _T('association_evenements:evenement_invites_radio_oui'),
                    'non' => _T('association_evenements:evenement_invites_radio_non'),
                ),
                "afficher_si" => '@accompagnants@ != "non"',
            ),
        ),
        // Nombre max d'invités autorisés
        'limite_invites' => array(
            "saisie" => 'input',
            "source" => 'association',
            "options" => array(
                "nom" => 'limite_invites',
                "label" => _T('association_evenements:evenement_invites_limite_label'),
                "sql" => "INT(10) UNSIGNED NOT NULL DEFAULT '5'",
                "defaut" => '5',
                "type" => 'number',
                "explication" => _T('association_evenements:evenement_invites_limite_explication'),
                "afficher_si" => '@invites@ == "oui"',
            ),
        ),
    ),
);

return $champs;
}

/**
* Ajoute les champs pour la file d'attente
*
* @param array $champs Tableau des champs existants
* @return array Tableau des champs avec les champs de file d'attente ajoutés
*/
function association_champs_attente($champs) {
$champs['spip_evenements']['attente'] = array(
    "saisie" => 'fieldset',
    "options" => array(
        "nom" => "attente",
        "label" => _T('association_evenements:attente_fieldset'),
        "afficher_si" => '@inscription@ == "1" && @places@ > "0"'
    ),
    'saisies' => array(
        'file_attentes' => array(
            "saisie" => 'radio',
            "source" => 'association',
            "options" => array(
                "nom" => 'file_attentes',
                "label" => _T('association_evenements:evenement_attente_radio_label'),
                "sql" => "varchar(30) NOT NULL",
                "defaut" => 'oui',
                "cacher_option_intro" => '1',
                "datas" => array(
                    'oui' => _T('association_evenements:evenement_attente_radio_oui'),
                    'non' => _T('association_evenements:evenement_attente_radio_non'),
                ),
                "explication" => _T('association_evenements:evenement_file_attentes_radio_explication'),
            ),
        ),
        'accompagnants' => array(
            "saisie" => 'radio',
            "source" => 'association',
            "options" => array(
                "nom" => 'validation_attente_automatique',
                "label" => _T('association_evenements:evenement_validation_attente_automatique_radio_label'),
                "sql" => "varchar(3) NOT NULL",
                "defaut" => association_champs_extras_meta('meta_cfg_event_validation_auto', 'non'),
                "datas" => array(
                    'oui' => _T('association_evenements:evenement_validation_attente_automatique_radio_oui'),
                    'non' => _T('association_evenements:evenement_validation_attente_automatique_radio_non'),
                ),
                "explication" => _T('association_evenements:evenement_validation_attente_automatique_radio_explication'),
                "afficher_si" => '@file_attentes@ == "oui"'
            ),
        ),
        'attentes' => array(
            "saisie" => 'input',
            "source" => 'association',
            "options" => array(
                "nom" => 'attentes',
                "label" => _T('association_evenements:evenement_attente_label'),
                "sql" => "varchar(30) NOT NULL",
                "defaut" => '',
                "explication" => _T('association_evenements:evenement_attente_explication_explication'),
                "afficher_si" => '@file_attentes@ == "oui"'
            ),
        ),
    ),
);

return $champs;
}

/**
* Ajoute les champs pour les conditions d'inscription
*
* @param array $champs Tableau des champs existants
* @param array $donnees_communes Tableau de données communes
* @return array Tableau des champs avec les conditions d'inscription ajoutées
*/
function association_champs_conditions_inscription($champs, $donnees_communes) {
$condition_inscription_defaut = association_champs_extras_meta('meta_cfg_event_condition_inscription', 'jamais');
if($condition_inscription_defaut == 'jamais') {

    $champs['spip_evenements']['condition_inscription'] = array(
        "saisie" => 'hidden',
        "options" => array(
            "sql" => "varchar(3) DEFAULT 'non' NULL",
            "nom" => "condition_inscription",
            "label" => _T('association_evenements:case_condition_inscription_label'),
            "defaut" => 'non',
        ),
    );

    $champs['spip_evenements']['message_condition_inscription'] = array(
        "saisie" => 'hidden',
        "options" => array(
            "sql" => "TEXT",
            "nom" => "message_condition_inscription",
            "label" => _T('association_evenements:evenement_message_condition_inscription'),
            "defaut" => '',
        ),
    );
} else {
    $champs['spip_evenements']['fieldset_modalites'] = array(
        "saisie" => 'fieldset',
        "options" => array(
            "nom" => "fieldset_modalites",
            "label" => _T('association_evenements:label_modalites'),
            "afficher_si" => '@inscription@ == "1"',
        ),
        'saisies' => array(
            'condition_inscription' => array(
                "saisie" => 'radio',
                "source" => 'association',
                "options" => array(
                    "label" => _T('association_evenements:case_condition_inscription_label'),
                    "nom" => 'condition_inscription',
                    "sql" => "varchar(3) DEFAULT 'non' NULL",
                    "explication" => _T('association_evenements:case_condition_inscription_explication'),
                    "defaut" => ($condition_inscription_defaut == 'toujours' ||
                                 $condition_inscription_defaut == 'oui') ? 'oui' : 'non',
                    "datas" => array(
                        "oui" => _T('association_evenements:case_condition_inscription_oui'),
                        "non" => _T('association_evenements:case_condition_inscription_non'),
                    ),
                ),
            ),
            'message_condition_inscription' => array(
                "saisie" => 'textarea',
                "source" => 'association',
                "options" => array(
                    "nom" => 'message_condition_inscription',
                    "label" => _T('association_evenements:evenement_message_condition_inscription'),
                    "sql" => "varchar(255) NOT NULL",
                    "defaut" => association_champs_extras_meta('message_condition_inscription_defaut', ''),
                    "explication" => _T('association_evenements:evenement_message_condition_inscription_textarea_explication'),
                    "rows" => 5,
                    "afficher_si" => '@condition_inscription@ == "oui"',
                    "obligatoire" => 'oui',
                ),
            ),
        )
    );
}

return $champs;
}

/**
* Ajoute les champs pour les informations supplémentaires
*
* @param array $champs Tableau des champs existants
* @return array Tableau des champs avec les informations supplémentaires ajoutées
*/
function association_champs_info_supplementaire($champs) {
if(isset($champs['spip_evenements']['fieldset_modalites']) &&
   isset($champs['spip_evenements']['fieldset_modalites']['saisies'])) {

    $champs['spip_evenements']['fieldset_modalites']['saisies']['info_suppelmentaire'] = array(
        "saisie" => 'checkbox',
        "source" => 'association',
        "options" => array(
            "nom" => 'info_supplementaire',
            "label" => _T('association_evenements:evenement_info_supplementaire'),
            "sql" => "INT(10) NOT NULL",
            "defaut" => '2',
            "explication" => _T('association_evenements:evenement_info_supplementaire_explication'),
            "datas" => array(
                'document_identite' => _T('association_evenements:document_identite_label'),
                'telephone' => _T('association_evenements:telephone'),
                'email' => _T('association_evenements:email'),
                'date_naissance' => _T('association_evenements:date_naissance'),
                'nationalite' => _T('association_evenements:nationalite'),
                'fonction' => _T('association_evenements:fonction'),
                'entreprise' => _T('association_evenements:entreprise'),
            ),
            'choix_alternatif_label' => 'Autres',
            'choix_alternatif' => 'autres',
        ),
    );
}

return $champs;
}

/**
* Ajoute les champs pour la communication FIAFE
*
* @param array $champs Tableau des champs existants
* @return array Tableau des champs avec la communication FIAFE ajoutée
*/
function association_champs_communication_fiafe($champs) {
$champs['spip_evenements']['fieldset_communication'] = array(
    "saisie" => 'fieldset',
    "options" => array(
        "nom" => "fieldset_communication",
        "label" => _T('association_evenements:label_communication'),
    ),
);

$champs['spip_evenements']['reseau_fiafe'] = array(
    "saisie" => 'radio',
    "source" => 'association',
    "options" => array(
        "label" => _T('association_evenements:reseau_fiafe_label'),
        "nom" => 'reseau_fiafe',
        "sql" => "varchar(3) DEFAULT 'non' NULL",
        "explication" => _T('association_evenements:reseau_fiafe_explication'),
        "defaut" => 'non',
        "datas" => array(
            "oui" => _T('association_evenements:oui'),
            "non" => _T('association_evenements:non'),
        ),
        "afficher_si" => '@presentiel@ == "non"',
        "disable_avec_post" => (association_champs_extras_meta('meta_cfg_event_reseau_fiafe', 'active') == 'desactive') ? '1' : '0',
    )
);

return $champs;
}
