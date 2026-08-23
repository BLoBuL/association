<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/comptes');
include_spip('inc/destinations');

/**
 * Déclare le panneau de configuration propre à la comptabilité.
 */
function association_compta_configurer_saisies($config) {
	$saisies = array();
if($config == 'comptabilite' OR empty($config)) {
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'fieldset_config_comptabilite',
            'label' => _T('association_config:config_comptabilite_fieldset'),
            'restrictions' => array(
                'voir' => array(
                    'auteur' => 'webmestre',
                ),
                'modifier' => array(
                    'auteur' => 'webmestre',
                ),
            ),

        ),
        'saisies' => array(

            array(
                'saisie' => 'case',
                'options' => array(
                    'nom' => 'comptes',
                    'label' => _T('association_config:config_comptes_label'),
                    'label_case' => _T('association_config:config_comptes_label_case'),
                    //'explication' => _T('association_config:config_comptes_explication'),
                    'defaut' => '',
                    'restrictions' => array(
                        'voir' => array(
                            'auteur' => 'webmestre',
                        ),
                        'modifier' => array(
                            'auteur' => 'webmestre',
                        ),
                    ),
                )
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'classe_banques',
                    'label' => _T('association_config:config_classe_banques_label'),
                    'explication' => _T(
                        'association_config:config_classe_banques_explication',
                        array('url' => generer_url_ecrire('plan_comptable'))
                    ),
                    'data' => preparer_liste_asso_plan_classe(),
                    'defaut' => array_key_exists('5',preparer_liste_asso_plan_classe('array')) ? '5' : '',
                )
            ),
            array(
                'saisie' => 'case',
                'options' => array(
                    'nom' => 'destinations',
                    'label' => _T('association_config:config_destinations_label'),
                    'label_case' => _T('association_config:config_destinations_label_case'),
                    'explication' => _T(
                        'association_config:config_destinations_explication',
                        array('url' => generer_url_ecrire('destinations'))
                    ),
                )
            ),
            // Ajout sous-fieldset Exercice comptable
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_exercice_comptable',
                    'label' => _T('association_config:config_exercice_comptable_fieldset'),
                ),
                'saisies' => array(
                    array(
                        'saisie' => 'input',
                        'options' => array(
                            'nom' => 'exercice_comptable_debut',
                            'label' => _T('association_config:config_exercice_comptable_debut_label'),
                            'explication' => _T('association_config:config_exercice_comptable_debut_explication'),
                            'size' => 5,
                            'maxlength' => 5,
                            'placeholder' => 'JJ/MM',
                            'defaut' => '01/07',
                        ),
                    ),
                ),
            ),
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_cotisations',
                    'label' => _T('association_config:config_cotisations_fieldset')
                ),
                'saisies' => array(
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'pc_cotisations_creance',
                            'label' => _T('association_config:config_num_pc_creance_label'),
                            'explication' => _T('association_config:config_num_pc_creance_explication'),
                            'data' => preparer_liste_asso_plan_compte('data_saisies','1'),
                            'defaut' => '416'
                        ),
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'pc_cotisations_paiement',
                            'label' => _T('association_config:config_num_pc_paiement_label'),
                            'explication' => _T('association_config:config_num_pc_paiement_explication'),
                            'data' => preparer_liste_asso_plan_compte('data_saisies','7'),
                            'defaut' => '7010'
                        ),
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'dc_cotisations',
                            'label' => _T('association_config:config_num_dc_label'),
                            //'explication' => _T('association_config:config_num_pc'),
                            'data' => preparer_liste_asso_destination_comptable(),
                            'afficher_si' => '@destinations@ == "on"',
                        )
                    )
                )
            ),
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_activites',
                    'label' => _T('association_config:config_activites_fieldset')
                ),
                'saisies' => array(
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'pc_activites_creance',
                            'label' => _T('association_config:config_num_pc_creance_label'),
                            'explication' => _T('association_config:config_num_pc_creance_explication'),
                            'data' => preparer_liste_asso_plan_compte('data_saisies','1'),
                            'defaut' => '417'
                        ),
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'pc_activites_paiement',
                            'label' => _T('association_config:config_num_pc_paiement_label'),
                            'explication' => _T('association_config:config_num_pc_paiement_explication'),
                            'data' => preparer_liste_asso_plan_compte('data_saisies','7'),
                            'defaut' => '7011'
                        ),
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'pc_activites_frais',
                            'label' => _T('association_config:config_num_pc_frais_label'),
                            'explication' => _T('association_config:config_num_pc_frais_explication'),
                            'data' => preparer_liste_asso_plan_compte('data_saisies','6'),
                            'defaut' => '601001'
                        ),
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'dc_activites',
                            'label' => _T('association_config:config_num_dc_label'),
                            'explication' => _T('association_config:config_num_pc'),
                            'data' => preparer_liste_asso_destination_comptable(),
                            'afficher_si' => '@destinations@ == "on"',
                        )
                    )
                )
            ),
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_dons',
                    'label' => _T('association_config:config_dons_fieldset')
                ),
                'saisies' => array(
                    array(
                        'saisie' => 'case',
                        'options' => array(
                            'nom' => 'dons',
                            'label' => _T('association_config:config_dons_label'),
                        )
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'pc_dons',
                            'label' => _T('association_config:config_num_pc_label'),
                            'data' => preparer_liste_asso_plan_compte(),
                            'afficher_si' => '@dons@ == "on"',
                        )
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'dc_dons',
                            'label' => _T('association_config:config_num_dc_label'),
                            'data' => preparer_liste_asso_destination_comptable(),
                            'afficher_si' => '@dons@ == "on" && @destinations@ == "on"',
                        )
                    )
                )
            ),
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_ventes',
                    'label' => _T('association_config:config_ventes_fieldset')
                ),
                'saisies' => array(
                    array(
                        'saisie' => 'case',
                        'options' => array(
                            'nom' => 'ventes',
                            'label_case' => _T('association_config:config_ventes_label'),
                        )
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'pc_ventes',
                            'label' => _T('association_config:config_num_pc_label'),
                            'data' => preparer_liste_asso_plan_compte(),
                            'afficher_si' => '@ventes@ == "on"',
                        )
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'pc_frais_envoi',
                            'label' => _T('association_config:config_num_pc_label') . ' ' . _T('association_config:config_frais_envoi_label'),
                            'data' => preparer_liste_asso_plan_compte(),
                            'afficher_si' => '@ventes@ == "on"',
                        )
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'dc_ventes',
                            'label' => _T('association_config:config_num_dc_label'),
                            'data' => preparer_liste_asso_destination_comptable(),
                            'afficher_si' => '@destinations@ == "on" && @ventes@ == "on"',
                        )
                    )
                )
            ),
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_prets',
                    'label' => _T('association_config:config_prets_fieldset')
                ),
                'saisies' => array(
                    array(
                        'saisie' => 'case',
                        'options' => array(
                            'nom' => 'prets',
                            'label_case' => _T('association_config:config_prets_label'),
                        )
                    ),
                    array(
                        'saisie' => 'selection',
                        'options' => array(
                            'nom' => 'pc_prets',
                            'label' => _T('association_config:config_num_pc_label'),
                            'data' => preparer_liste_asso_plan_compte(),
                            'afficher_si' => '@prets@ == "on"',
                        )
                     )
                )
            )
        )
    );
}

	return $saisies;
}
