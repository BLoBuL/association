<?php
/***************************************************************************\
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & Francois de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
//DOCUMENTATION
//https://contrib.spip.net/Formulaire-de-configuration-avec-le-plugin-Saisies

include_spip('formulaires/inc/configurer_association');
include_spip('inc/comptes');
include_spip('inc/destinations');

function formulaires_configurer_association_saisies_dist($config = '', $lister_champs = ''){
$saisies=array();

$disable_meta_admin = $GLOBALS['visiteur_session']['statut'] != '0minirezo';

if($config == 'info' OR empty($config)){
$saisies[]= array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'info_asso',
        'label' => _T('association_config:config_info_asso_fieldset'),
    ),
    'saisies' => array(
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'nom',
                'label' => _T('association_config:config_nom_label'),
                'defaut' => lire_config('nom_site'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'rue',
                'label' => _T('association_config:config_rue_label'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'cp',
                'label' => _T('association_config:config_codepostal_label'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'ville',
                'label' => _T('association_config:config_ville_label'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'pays',
                'label' => _T('association_config:config_pays_label'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'email',
                'label' => _T('association_config:config_email_label'),
                'type' => 'text',
                'explication' => _T('association_config:config_email_explication_multi'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'telephone',
                'label' => _T('association_config:config_telephone_label'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'num_enregistrement',
                'label' => _T('association_config:config_num_enregistrement_label'),
            ),
        ),
        array(
            'saisie' => 'textarea',
            'options' => array(
                'nom' => 'info_complementaires',
                'label' => _T('association_config:config_info_complementaire_label'),
                'rows' => 3,
                'cols' => 80,
            ),
        ),
    ),
);
}
if($config == 'adhesion' OR empty($config)) {
// *****************************************
//   Fieldset for cotisation configuration
// *****************************************
$saisies[] =
    array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_cotisation_fieldset',
            'label' => _T('association_config:config_cotisation_fieldset'),
        ),
        'saisies' => array(
            // Selection for validity
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'validite',
                    'label' => _T('association_config:config_validite_label'),
                    'cacher_option_intro' => 'oui',
                    'data' => array(
                        'scolaire' => _T('association_config:config_choix_scolaire'),
                        'annee' => _T('association_config:config_choix_annee'),
                    ),
                    'defaut' => 'scolaire',
                ),
            ),
            // Input for date scolaire suivante
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'date_scolaire_suivante',
                    'label' => _T('association_config:config_annee_suivante_label'),
                    'explication' => _T('association_config:config_scolaire_uniquement'),
                    'obligatoire' => 'non',
                    'defaut' => '01/06',
                    'afficher_si' => '@validite@ == "scolaire"',
                ),
            ),
            // Input for date scolaire nouvelle
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'date_scolaire_nouvelle',
                    'label' => _T('association_config:config_nouvelle_annee_label'),
                    'explication' => _T('association_config:config_scolaire_uniquement'),
                    'obligatoire' => 'non',
                    'defaut' => '30/09',
                    'afficher_si' => '@validite@ == "scolaire"',
                ),
            ),
        ),
    );

$saisies[] =
    array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_compte_secondaire_fieldset',
            'label' => _T('association_config:config_compte_secondaire_fieldset_label'),
            'explication' => _T('association_config:config_compte_secondaire_fieldset_explication'),
        ),
        'saisies' => array(
            // Activer les comptes secondaires automatiquement
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'config_compte_secondaire',
                    'label' => _T('association_config:config_compte_secondaire_label'),
                    'explication' => _T('association_config:config_compte_secondaire_explication'),
                    'data' => array(
                        'oui' => _T('association:oui'),
                        'non' => _T('association:non'),
                    ),
					'defaut' => lire_config('association_metas/config_compte_secondaire', 'non'),
                ),
            ),
            // Selection for zone adherent
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'config_compte_secondaire_activation',
                    'label' => _T('association_config:config_compte_secondaire_activation_label'),
                    'explication' => _T('association_config:config_compte_secondaire_activation_explication'),
                    'data' => array(
                        'oui' => _T('association:oui'),
                        'non' => _T('association:non'),
                    ),
                    'afficher_si' => '@config_compte_secondaire@ == "oui"',
                    'defaut' => 'non',
                ),
            ),
        ),
    );

// Fieldset : configuration limite d'âge enfants (global)
$saisies[] = array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'config_enfants_fieldset',
        'label' => _T('association_config:config_enfants_fieldset'),
    ),
    'saisies' => array(
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'meta_cfg_age_limit_enfants',
                'label' => _T('association_config:config_age_limit_enfants_label'),
                'explication' => _T('association_config:config_age_limit_enfants_explication'),
                'defaut' => lire_config('association_metas/meta_cfg_age_limit_enfants', ''),
                'type' => 'number',
            ),
        ),
    ),
);

$saisies[] =
    array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_privileges_fieldset',
            'label' => _T('association_config:config_privileges_fieldset_label'),
            'explication' => _T('association_config:config_privileges_fieldset_explication'),
        ),
        'saisies' => array(
            // Selection for carte adherent
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'meta_cfg_carte_adherent',
                    'label' => _T('association_config:config_carte_adherent_label'),
                    'explication' => _T('association_config:config_carte_adherent_explication'),
                    'data' => array(
                        'oui' => _T('association:oui'),
                        'non' => _T('association:non'),
                    ),
                    //'obligatoire' => 'oui',
                    'defaut' => 'non',
                ),
            ),
            // Selection for zone adherent
            array(
                'saisie' => 'checkbox',
                'options' => array(
                    'nom' => 'zone_adherent',
                    'label' => _T('association_config:config_zones_label'),
                    'explication' => _T('association_config:config_zones_explication'),
                    'data' => preparer_liste_zones(), // This will be filled dynamically with zones
                ),
            ),
            // Checkbox for liste diffusion
            array(
                'saisie' => 'checkbox',
                'options' => array(
                    'nom' => 'liste_diffusion',
                    'label' => _T('association_config:config_liste_diffusion_label'),
                    'explication' => _T('association_config:config_liste_diffusion_explication'),
                    'data' => preparer_liste_mailsubscribinglists(),
                ),
            ),

        ),
    );

$saisies[] =
    array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_donation_fieldset',
            'label' => _T('association_config:config_donation_fieldset_label'),
            'explication' => _T('association_config:config_donation_fieldset_explication'),
        ),
        'saisies' => array(
            // Selection for carte adherent
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'meta_cfg_donation',
                    'label' => _T('association_config:config_donation_label'),
                    'explication' => _T('association_config:config_donation_explication'),
                    'data' => array(
                        'oui' => _T('association:oui'),
                        'non' => _T('association:non'),
                    ),
                    //'obligatoire' => 'oui',
                    'defaut' => 'non',
                    'disable' => $disable_meta_admin,
                ),
            ),
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'meta_cfg_donation_defaut',
                    'label' => _T('association_config:config_donation_defaut_label'),
                    'explication' => _T('association_config:config_donation_defaut_explication'),
                    'afficher_si' => '@meta_cfg_donation@ == "oui"',
                    'disable' => $disable_meta_admin,
                ),
            ),
        ),
    );

// Fieldset modalités d'inscription cotisation (pages uniques à accepter)
$saisies[] = array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'config_modalites_inscription_fieldset',
        'label' => _T('association_config:config_modalites_inscription_fieldset_label'),
        'explication' => _T('association_config:config_modalites_inscription_fieldset_explication'),
    ),
    'saisies' => array(
        array(
            'saisie' => 'checkbox',
            'options' => array(
                'nom' => 'pages_modalite_inscription',
                'label' => _T('association_config:config_modalites_inscription_pages_label'),
                'explication' => _T('association_config:config_modalites_inscription_pages_explication'),
                'data' => preparer_liste_pages_uniques(),
            ),
        ),
    ),
);

// Fieldset for notification configuration
$saisies[]= array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'notification_recu_paiement',
        'label' => _T('association_config:config_notification_recu_paiement_fieldset'),
    ),
    'saisies' => array(

        array(
            'saisie' => 'radio',
            'options' => array(
                'nom' => 'meta_cfg_envoi_recu_paiement_adhesion',
                'label' => _T('association_config:config_envoi_recu_paiement_adhesion_label'),
                'explication' => _T('association_config:config_envoi_recu_paiement_adhesion_explication'),
                'cacher_option_intro' => 'oui',
                'data' => array(
                    'oui' => _T('association_config:oui'),
                    'non' => _T('association_config:non'),
                ),
                'defaut' => 'non',
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'config_envoi_recu_adhesion_cc',
                'label' => _T('association_config:config_envoi_recu_paiement_cc_label'),
                'explication' => _T('association_config:config_envoi_recu_paiement_cc_explication_multi'),
                'type' => 'text',
            ),
        ),
    ),
);

// Fieldset for notification configuration
$saisies[]= array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'notification_adhesion',
        'label' => _T('association_config:config_notification_adhesion_fieldset'),
    ),
    'saisies' => array(
        array(
            'saisie' => 'radio',
            'options' => array(
                'nom' => 'meta_cfg_envoi_validation_paiement_adhesion',
                'label' => _T('association_config:config_envoi_validation_paiement_adhesion_label'),
                'explication' => _T('association_config:config_envoi_validation_paiement_adhesion_explication'),
                'cacher_option_intro' => 'oui',
                'data' => array(
                    'oui' => _T('association:oui'),
                    'non' => _T('association:non'),
                ),
                'defaut' => 'oui',
            ),
        ),

        array(
            'saisie' => 'radio',
            'options' => array(
                'nom' => 'notification_adherent_echu',
                'label' => _T('association_config:config_notification_adherent_echu_label'),
                'cacher_option_intro' => 'oui',
                'data' => array(
                    'oui' => _T('association:oui'),
                    'non' => _T('association:non'),
                ),
                'defaut' => 'oui',
            ),
        ),

        array(
            'saisie' => 'checkbox',
            'options' => array(
                'nom' => 'notification_echeance_cotisation',
                'label' => _T('association_config:config_choix_echeance_label'),
                'data' => array(
                    '60' => _T('association_config:config_choix_echeance_60'),
                    '30' => _T('association_config:config_choix_echeance_30'),
                    '15' => _T('association_config:config_choix_echeance_15'),
                    '7' => _T('association_config:config_choix_echeance_7'),
                ),
                // 'defaut' => array('60','30','15','7'),
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'config_destinataires_creation_cotisation_tresorier',
                'label' => _T('association_config:config_destinataires_creation_cotisation_label_tresorier'),
                'explication' => _T('association_config:config_destinataires_creation_cotisation_explication_tresorier'),
                'type' => 'text',
            ),
        ),
    ),
);
}
if(($config == 'entreprise' OR empty($config)) AND verifier_categorie_adherent_entreprise()) {
    $saisies[] =
        array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'config_cotisation_entreprise_fieldset',
                'label' => _T('association_config:config_cotisation_entreprise_fieldset'),
            ),
            'saisies' => array(
                // Selection for validity
                array(
                    'saisie' => 'radio',
                    'options' => array(
                        'nom' => 'validite_entreprise',
                        'label' => _T('association_config:config_validite_label'),
                        'cacher_option_intro' => 'oui',
                        'data' => array(
                            'scolaire' => _T('association_config:config_choix_scolaire'),
                            'annee' => _T('association_config:config_choix_annee'),
                        ),
                        'defaut' => 'scolaire',
                    ),
                ),
                // Input for date scolaire suivante
                array(
                    'saisie' => 'input',
                    'options' => array(
                        'nom' => 'date_scolaire_suivante_entreprise',
                        'label' => _T('association_config:config_annee_suivante_label'),
                        'explication' => _T('association_config:config_scolaire_uniquement'),
                        'obligatoire' => 'non',
                        'defaut' => '01/06',
                        'afficher_si' => '@validite_entreprise@ == "scolaire"',
                    ),
                ),
                // Input for date scolaire nouvelle
                array(
                    'saisie' => 'input',
                    'options' => array(
                        'nom' => 'date_scolaire_nouvelle_entreprise',
                        'label' => _T('association_config:config_nouvelle_annee_label'),
                        'explication' => _T('association_config:config_scolaire_uniquement'),
                        'obligatoire' => 'non',
                        'defaut' => '30/09',
                        'afficher_si' => '@validite_entreprise@ == "scolaire"',
                    ),
                ),
            ),
        );
    // Fieldset that regroups specific parameter for entreprise accounts
    $saisies[] =
        array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'config_adherent_entreprise',
                'label' => _T('association_config:config_adherent_entreprise_fieldset'),
            ),
            // Allow or not entreprise account to create a "cotisation"
            // Saisies for entreprise accounts
            'saisies' => array(
                array(
                    'saisie' => 'selection',
                    'options' => array(
                        'nom' => 'meta_cfg_cotisation_compte_entreprise',
                        'label' => _T('association_config:config_adherent_compte_entreprise_label'),
                        'explication' => _T('association_config:config_adherent_compte_entreprise_explication'),
                        'data' => array(
                            'oui' => _T('association_config:oui_defaut'),
                            'non' => _T('association_config:non'),
                        ),
                        'defaut' => 'oui',
                    ),
                ),
                // Allow or entreprise account to register to an event
                array(
                    'saisie' => 'selection',
                    'options' => array(
                        'nom' => 'meta_cfg_event_inscription_compte_entreprise',
                        'label' => _T('association_config:config_inscription_evenement_entreprise_label'),
                        'explication' => _T('association_config:config_inscription_evenement_entreprise_explication'),
                        'data' => array(
                            'oui' => _T('association_config:oui'),
                            'non' => _T('association_config:non_defaut'),
                        ),
                        'defaut' => 'non',

                    ),
                ),
                // Add entreprise account to existing mailing lists
                array(
                    'saisie' => 'checkbox',
                    'options' => array(
                        'nom' => 'meta_cfg_liste_diffusion_compte_entreprise',
                        'label' => _T('association_config:config_liste_diffusion_compte_entreprise_label'),
                        'explication' => _T('association_config:config_liste_diffusion_compte_entreprise_explication'),
                        'data' => preparer_liste_mailsubscribinglists(),
                    ),
                ),
                array(
                    'saisie' => 'radio',
                    'options' => array(
                        'nom' => 'notification_echeance_notifier_echu_entreprise',
                        'label' => _T('association_config:notification_echeance_notifier_echu_entreprise_label'),
                        'explication' => _T('association_config:notification_echeance_notifier_echu_entreprise_explication'),
                        'data' => array(
                            'oui' => _T('association:oui'),
                            'non' => _T('association:non'),
                        ),
                        'defaut' => (lire_config('association_metas/notification_echeance_notifier_echu_entreprise') ? : 'oui'),
                    ),
                ),
                array(
                    'saisie' => 'checkbox',
                    'options' => array(
                        'nom' => 'notification_echeance_cotisation_entreprise',
                        'label' => _T('association_config:config_choix_echeance_entreprise_label'),
                        'explication' => _T('association_config:config_choix_echeance_entreprise_explication'),
                        'data' => array(
                            '60' => _T('association_config:config_choix_echeance_60'),
                            '30' => _T('association_config:config_choix_echeance_30'),
                            '15' => _T('association_config:config_choix_echeance_15'),
                            '7' => _T('association_config:config_choix_echeance_7'),
                        ),
                    ),
                ),

            )
        );
        $saisies[]=
            // Fieldset dédié : destinataires des notifications de création de cotisation
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'fieldset_notification_creation_cotisation_entreprise',
                    'label' => _T('association_config:config_notification_creation_cotisation_entreprise_fieldset'),
                    'explication' => _T('association_config:config_notification_creation_cotisation_entreprise_explication'),
                ),
                'saisies' => array(

                    array(
                        'saisie' => 'input',
                        'options' => array(
                            'nom' => 'config_destinataires_creation_cotisation_tresorier_entreprise',
                            'label' => _T('association_config:config_destinataires_creation_cotisation_label_tresorier'),
                            'explication' => _T('association_config:config_destinataires_creation_cotisation_explication_tresorier'),
                            'type' => 'text',
                        ),
                    ),
                ),
            );

        $saisies[] = array(
            'saisie' => 'radio',
            'options' => array(
                'nom' => 'meta_cfg_cotisations_multidevises',
                'label' => _T('association_config:config_cotisations_multidevises_label'),
                'explication' => _T('association_config:config_cotisations_multidevises_explication'),
                'data' => array(
                    'oui' => _T('association:oui'),
                    'non' => _T('association:non'),
                ),
                'defaut' => 'non',
            ),
        );

}
include_spip('formulaires/inc/configurer_association_evenements');
$saisies = array_merge($saisies, association_evenements_configurer_saisies($config));
if($config == 'mode_paiement' OR empty($config)) {
// Fieldset for payment configurations
    $choix_mode_paiement = preparer_choix_mode_paiement();
    $data_mode_paiement = saisies_tableau2chaine($choix_mode_paiement);

    $saisies[]= array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'config_paiement_formulaire',
        'label' => _T('association_config:config_paiement_formulaire'),
        'explication' => _T('association_config:config_mode_paiement_explication'),
    ),
    'saisies' => array(

        // Checkbox for "adhesion" payment method
        array(
            'saisie' => 'checkbox',
            'options' => array(
                'nom' => 'mode_paiement_adhesion',
                'label' => _T('association_config:config_mode_paiement_adhesion'),
                'data' => $choix_mode_paiement,
            ),
        ),
        // Checkbox for "participation" payment method
        array(
            'saisie' => 'checkbox',
            'options' => array(
                'nom' => 'mode_paiement_participation',
                'label' => _T('association_config:config_mode_paiement_participation'),
                'data' => $choix_mode_paiement,
            ),
        ),
        // Checkbox for "formidable" payment method
        array(
            'saisie' => 'checkbox',
            'options' => array(
                'nom' => 'mode_paiement_formidable',
                'label' => _T('association_config:config_mode_paiement_formulaire'),
                'data' => $choix_mode_paiement,
            ),
        ),
    ),
);
$saisies[]= array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'config_taxes_formulaire',
        'label' => _T('association_config:fieldset_config_taxe_formulaire_label'),

    ),
    'saisies' => array(
        // Input for taxe
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'meta_cfg_taxe',
                'label' => _T('association_config:config_taxe_label_adhesion_label'),
                'explication' => _T('association_config:config_taxe_explication'),
                'obligatoire' => 'non',
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'meta_cfg_taxe_evenement',
                'label' => _T('association_config:config_taxe_label_participation_evenement_label'),
                'explication' => _T('association_config:config_taxe_explication'),
            ),
        ),
    ),
);

// Fieldset for authorizations configuration
$nom_tresoriere=identifier_tresorier();

$saisies[]=array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'config_autorisations_fieldset',
        'label' => _T('association_config:config_autorisations_fieldset'),
        'explication' => _T('association_config:meta_cfg_autorisation_encaisser_transaction_explication'),
    ),
    'saisies' => array(
        // Selection box for authorization level
        array(
            'saisie' => 'selection',
            'options' => array(
                'nom' => 'meta_cfg_autorisation_encaisser_transaction',
                'label' => _T('association_config:meta_cfg_autorisation_encaisser_transaction_label'),
                'defaut' => 'admin_et_responsable', // Default value if none selected
                'data' => array(
                    'tresoriere' => _T('association_config:autorisation_encaisser_transaction_tresoriere') . ' ' .$nom_tresoriere,
                    'admin_only' => _T('association_config:autorisation_encaisser_transaction_admin_only'),
                    'admin_et_responsable' => _T('association_config:autorisation_encaisser_transaction_admin_et_responsable'),
                ),
                'disable_choix' => empty($nom_tresoriere) ? 'tresoriere' : false,
                'disable' => $disable_meta_admin, // Disable based on condition
                'explication' => _T('association_config:meta_cfg_autorisation_encaisser_transaction_explication'),
            ),
        ),

    ),
);
}
if($config == 'segments' OR empty($config)){

$saisies[]= array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'config_selection_segment_fieldset',
        'label' => _T('association_config:config_selection_segment_fieldset'),
        'explication' => _T('association_config:config_selection_segment_explication'),
    ),
    'saisies' => array(
        // Selection box for segment
        array(
            'saisie' => 'selection_multiple',
            'options' => array(
                'nom' => 'selection_segment',
                'label' => _T('association_config:config_selection_segment_label'),
                //'multiple' => 'multiple',
                'size' => 10,
                'data' => preparer_liste_champs_filtres(),
                ),

            ),

    ),
);
}
if ($config == 'affichage_public') {
        $saisies[]=
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'config_annuaire_membre',
                    'label' => _T('association_config:config_annuaire_membre_fieldset'),
                ),
                'saisies' => array(
                    // Checkbox for annuaire filters
                    array(
                        'saisie' => 'checkbox',
                        'options' => array(
                            'nom' => 'config_filtres_annuaire',
                            'label' => _T('association_config:config_filtres_annuaire_label'),
                            'explication' => _T('association_config:config_filtres_annuaire_explication'),
                            'data' => array(
                                'code_postal' => _T('association_config:config_choix_code_postal'),
                                'quartier' => _T('association_config:config_choix_quartier'),
                                'ville' => _T('association_config:config_choix_ville'),
                            ),
                        ),
                    ),
                ),
            );

    // Fieldset : statuts affichés dans la liste publique des inscrits d'un événement
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_statuts_liste_publique_inscrits_fieldset',
            'label' => _T('association_config:config_statuts_liste_publique_inscrits_fieldset'),
            'explication' => _T('association_config:config_statuts_liste_publique_inscrits_explication'),
        ),
        'saisies' => array(
            // Inscrits confirmés : toujours affiché, affiché à titre informatif (non modifiable)
            array(
                'saisie' => 'explication',
                'options' => array(
                    'nom' => 'info_statut_ok_fixe',
                    'explication' => _T('association_config:config_statuts_liste_publique_ok_fixe'),
                ),
            ),
            // Checkboxes pour les statuts optionnels
            array(
                'saisie' => 'checkbox',
                'options' => array(
                    'nom' => 'config_statuts_liste_publique_inscrits',
                    'label' => _T('association_config:config_statuts_liste_publique_options_label'),
                    'data' => array(
                        'preinscrit'   => _T('association_config:config_statuts_liste_publique_preinscrit'),
                        'liste_attente' => _T('association_config:config_statuts_liste_publique_liste_attente'),
                    ),
                ),
            ),
        ),
    );
}
if($config == 'affichage_prive') {
        $saisies[] = array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'config_filtres_tableau',
                'label' => _T('association_config:config_filtres_tableau_fieldset'),
                'explication' => _T('association_config:config_filtres_tableau_explication'),
            ),
            'saisies' => array(
                array(
                    'saisie' => 'selection_multiple',
                    'options' => array(
                        'nom' => 'config_champs_filtres_adherents',
                        'label' => _T('association_config:config_champs_filtres_adherents_label'),
                        'explication' => _T('association_config:config_champs_filtres_adherents_explication'),
                        // Préparer la liste des champs extra de type radio/select
                        'data' => preparer_liste_champs_filtres(),
                    ),
                ),
            ),
        );


// Nouveau fieldset : sélection des champs extras affichés comme colonnes du tableau adhérents
        $saisies[] = array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'config_colonnes_tableau',
                'label' => _T('association_config:config_champs_colonnes_tableau_fieldset'),
                'explication' => _T('association_config:config_champs_colonnes_tableau_explication'),
            ),
            'saisies' => array(
                array(
                    'saisie' => 'selection_multiple',
                    'options' => array(
                        'nom' => 'config_champs_colonnes_adherents',
                        'label' => _T('association_config:config_champs_colonnes_adherents_label'),
                        'explication' => _T('association_config:config_champs_colonnes_adherents_explication'),
                        'data' => preparer_liste_champs_filtres(),
                    ),
                ),
            ),
        );
    }
if($config == 'modules' OR empty($config)) {
    if (test_plugin_actif('gis')) {

        $array_gis = array('modification_adherent' => _T('association_config:filtre_modification_adherent'),
            'echec_adherent' => _T('association_config:filtre_echec_adherent'),
        );
        $array_gis = saisies_tableau2chaine($array_gis);
        $saisies[] = array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'config_gis_fieldset',
                'label' => _T('association_config:config_gis_fieldset')
            ),
            'saisies' => array(
                array(
                    'saisie' => 'input',
                    'options' => array(
                        'nom' => 'notification_gis_config_email',
                        'label' => _T('association_config:notification_gis_config_email_label'),
                        'explication' => _T('association_config:notification_gis_config_email_explication'),
                        'type' => 'text',
                    )
                ),
                array(
                    'saisie' => 'checkbox',
                    'options' => array(
                        'nom' => 'notification_gis_config_action',
                        'label' => _T('association_config:notification_gis_config_action_label'),
                        'data' => $array_gis,
                    ),
                )
            )
        );
    }

// Vérifie si la fonction `verifier_site_fiafe` existe, puis l'exécute.
// Cette vérification permet d'éviter des erreurs si la fonction n'est pas définie.
// Si la fonction existe, son résultat est stocké dans la variable `$verification_site_fiafe`.
    if (function_exists('verifier_site_fiafe') ) {
        $verification_site_fiafe = verifier_site_fiafe();
    }
    if ($verification_site_fiafe === true) {
        $saisies[] = array(
            'saisie' => 'fieldset',
            'options' => array(
                'nom' => 'config_evenement_fiafe',
                'label' => _T('association_config:config_evenement_fiafe')
            ),
            'saisies' => array(
                array(
                    'saisie' => 'selection',
                    'options' => array(
                        'nom' => 'meta_cfg_event_reseau_fiafe',
                        'label' => _T('association_config:evenement_reseau_fiafe_label'),
                        'explication' => _T('association_config:evenement_reseau_fiafe_explication'),
                        'data' => array(
                            'active' => _T('association_config:evenement_reseau_fiafe_active'),
                            'desactive' => _T('association_config:evenement_reseau_fiafe_desactive')
                        ),
                        'cacher_option_intro' => 'oui',
                        'defaut' => 'desactive',
                    ),
                ),
                array(
                    'saisie' => 'selection',
                    'options' => array(
                        'nom' => 'meta_cfg_event_profil_reseau_fiafe',
                        'label' => _T('association_config:evenement_profil_reseau_fiafe_label'),
                        'explication' => _T('association_config:evenement_profil_reseau_fiafe_explication'),
                        'data' => array(
                            'active' => _T('association_config:evenement_reseau_profil_fiafe_active'),
                            'desactive' => _T('association_config:evenement_reseau_profil_fiafe_desactive')
                        ),
                        'cacher_option_intro' => 'oui',
                        'defaut' => 'desactive',
                    ),
                ),
            ),
        );
    }
}
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
if($config == 'maintenance_bdd' OR empty($config)) {
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_maintenance_bdd_fieldset',
            'label' => _T('association_config:config_maintenance_bdd_fieldset'),
            'explication' => _T('association_config:config_maintenance_bdd_explication'),
            'restrictions' => array(
                'voir' => array('auteur' => 'webmestre'),
                'modifier' => array('auteur' => 'webmestre'),
            ),
        ),
        'saisies' => array(
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'meta_cfg_maintenance_bdd_enable',
                    'label' => _T('association_config:config_maintenance_bdd_enable_label'),
                    'explication' => _T('association_config:config_maintenance_bdd_enable_explication'),
                    'data' => array('oui' => _T('association:oui'), 'non' => _T('association:non')),
                    'defaut' => 'oui',
                ),
            ),
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'meta_cfg_maintenance_dry_run',
                    'label' => _T('association_config:config_maintenance_bdd_dry_run_label'),
                    'explication' => _T('association_config:config_maintenance_bdd_dry_run_explication'),
                    'data' => array('oui' => _T('association:oui'), 'non' => _T('association:non')),
                    'defaut' => 'oui',
                ),
            ),
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'meta_cfg_maintenance_jours_inactivite',
                    'label' => _T('association_config:config_maintenance_bdd_jours_inactivite_label'),
                    'explication' => _T('association_config:config_maintenance_bdd_jours_inactivite_explication'),
                    'type' => 'text',
                    'defaut' => '365',
                ),
            ),
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'meta_cfg_maintenance_jours_inscriptions_attente',
                    'label' => _T('association_config:config_maintenance_bdd_jours_inscriptions_attente_label'),
                    'explication' => _T('association_config:config_maintenance_bdd_jours_inscriptions_attente_explication'),
                    'type' => 'text',
                    'defaut' => '90',
                ),
            ),
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'meta_cfg_maintenance_mois_non_encaisse',
                    'label' => _T('association_config:config_maintenance_bdd_mois_non_encaisse_label'),
                    'explication' => _T('association_config:config_maintenance_bdd_mois_non_encaisse_explication'),
                    'type' => 'text',
                    'defaut' => '6',
                ),
            ),
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'meta_cfg_maintenance_lot',
                    'label' => _T('association_config:config_maintenance_bdd_lot_label'),
                    'explication' => _T('association_config:config_maintenance_bdd_lot_explication'),
                    'type' => 'text',
                    'defaut' => '1000',
                ),
            ),
            array(
                'saisie' => 'fieldset',
                'options' => array(
                    'nom' => 'config_maintenance_bdd_actions',
                    'label' => _T('association_config:config_maintenance_bdd_actions_fieldset'),
                ),
                'saisies' => array(
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_supprimer_auteurs_sans_paiements',
                            'label' => _T('association_config:config_maintenance_bdd_supprimer_auteurs_sans_paiements_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_supprimer_auteurs_sans_paiements_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_anonymiser_auteurs_avec_paiements',
                            'label' => _T('association_config:config_maintenance_bdd_anonymiser_auteurs_avec_paiements_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_anonymiser_auteurs_avec_paiements_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_supprimer_inscriptions_non_validees',
                            'label' => _T('association_config:config_maintenance_bdd_supprimer_inscriptions_non_validees_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_supprimer_inscriptions_non_validees_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_anonymiser_inscriptions_inactifs',
                            'label' => _T('association_config:config_maintenance_bdd_anonymiser_inscriptions_inactifs_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_anonymiser_inscriptions_inactifs_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_supprimer_cotisations_orphelines',
                            'label' => _T('association_config:config_maintenance_bdd_supprimer_cotisations_orphelines_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_supprimer_cotisations_orphelines_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_supprimer_cotisations_non_encaissees',
                            'label' => _T('association_config:config_maintenance_bdd_supprimer_cotisations_non_encaissees_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_supprimer_cotisations_non_encaissees_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_supprimer_transactions_orphelines',
                            'label' => _T('association_config:config_maintenance_bdd_supprimer_transactions_orphelines_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_supprimer_transactions_orphelines_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_supprimer_participations_orphelines',
                            'label' => _T('association_config:config_maintenance_bdd_supprimer_participations_orphelines_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_supprimer_participations_orphelines_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_supprimer_participations_obsoletes',
                            'label' => _T('association_config:config_maintenance_bdd_supprimer_participations_obsoletes_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_supprimer_participations_obsoletes_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_supprimer_urls_mailsubscriber',
                            'label' => _T('association_config:config_maintenance_bdd_supprimer_urls_mailsubscriber_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_supprimer_urls_mailsubscriber_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_supprimer_urls_obsoletes',
                            'label' => _T('association_config:config_maintenance_bdd_supprimer_urls_obsoletes_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_supprimer_urls_obsoletes_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                    array(
                        'saisie' => 'radio',
                        'options' => array(
                            'nom' => 'meta_cfg_maintenance_supprimer_mailsubscribers_orphelines',
                            'label' => _T('association_config:config_maintenance_bdd_supprimer_mailsubscribers_orphelines_label'),
                            'explication' => _T('association_config:config_maintenance_bdd_supprimer_mailsubscribers_orphelines_explication'),
                            'data'=>array('oui'=>_T('association:oui'),'non'=>_T('association:non')),
                            'defaut'=>'oui'
                        )
                    ),
                ),
            ),

            // Bouton pour exécuter un dry-run immédiatement (visible uniquement aux webmestres via restrictions du fieldset)
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'exec_maintenance_dry_run',
                    'label' => _T('association_config:config_maintenance_bdd_exec_dry_run_label'),
                    'explication' => _T('association_config:config_maintenance_bdd_exec_dry_run_explication'),
                    'type' => 'submit',
                    'valeur' => '1',
                    'class' => 'bouton_action',
                ),
            ),
        ),
    );
}
if ($config == 'debug' OR empty($config)) {
    // Accessible uniquement aux webmestres
    $est_webmestre = isset($GLOBALS['visiteur_session']['webmestre'])
        && $GLOBALS['visiteur_session']['webmestre'] === 'oui';

    if ($est_webmestre) {
        include_spip('inc/association_log');

        $saisies_debug = array();
        foreach (association_log_categories_defaut() as $cat => $libelle) {
            $saisies_debug[] = array(
                'saisie' => 'case',
                'options' => array(
                    'nom'         => 'debug_log_cat_' . $cat,
                    'label'       => _T('association_config:config_debug_cat_' . $cat . '_label', array(), $libelle),
                    'label_case'  => _T('association_config:config_debug_cat_' . $cat . '_label', array(), $libelle),
                    'explication' => _T('association_config:config_debug_cat_' . $cat . '_explication', array(), ''),
                ),
            );
        }

        $saisies[] = array(
            'saisie'  => 'fieldset',
            'options' => array(
                'nom'          => 'config_debug_fieldset',
                'label'        => _T('association_config:config_debug_fieldset'),
                'explication'  => _T('association_config:config_debug_explication'),
                'restrictions' => array(
                    'voir'     => array('auteur' => 'webmestre'),
                    'modifier' => array('auteur' => 'webmestre'),
                ),
            ),
            'saisies' => $saisies_debug,
        );
    }
}

// Ici on envoie que les noms des saisies pour la fonction de traitement et identifier qui ne sont pas toujours envoyés si vide comme les checkbox
    if($lister_champs) {
        // On aplatit les saisies
        $liste_saisies =saisies_lister_par_nom($saisies,false);
        // je veux boucler sur saisies pour extraire uniquement le nom de chaque saisie pour les mettre dans un tableau
        foreach ($liste_saisies as $saisie) {
            $liste_champs[] = $saisie['options']['nom'];
        }
        return $liste_champs;
    }else{
        return $saisies;
    }
}
// Fonction pour charger les valeurs de configuration
function formulaires_configurer_association_charger_dist($config) {
	$contexte = deserialize_values($GLOBALS['association_metas']);

    return $contexte;
}
// Fonction pour vérifier les valeurs du formulaire
function formulaires_configurer_association_verifier_dist($config){
    $erreurs = array();
    $erreur = false;
    // Validation JJ/MM des champs exercice comptable (si présents dans le POST)
    $debut = trim((string)_request('exercice_comptable_debut'));
    $regex = '/^([0-9]{2})\/([0-9]{2})$/';
    if ($debut !== '') {
        if (!preg_match($regex, $debut, $m)) {
            $erreurs['exercice_comptable_debut'] = _T('association_config:erreur_exercice_comptable_format');
        } else {
            $j = intval($m[1]); $mo = intval($m[2]);
            if ($j < 1 || $j > 31 || $mo < 1 || $mo > 12) {
                $erreurs['exercice_comptable_debut'] = _T('association_config:erreur_exercice_comptable_jour_mois');
            }
        }
    }

    // TODO Ces vérifications sont à revoir, elles ne prennent pas en compte tous les cas de figures et ne place par les erreurs au bon endroit
    $ref_attribuee = array();
    // on verifie qu'il n'a pas deux fois la meme reference comptable en incluant celle des cotisations
    $ref_attribuee[_request('pc_cotisations')]=0;
    if ((_request('dons') == 'on') AND $ref_dons = _request('pc_dons')) {
        if (!array_key_exists($ref_dons,$ref_attribuee)) {
            $ref_attribuee[$ref_dons]=0;
        }
        else $erreur = true;
    }

    if ((_request('ventes') == 'on' AND $ref_ventes = _request('pc_ventes'))) {

        $ref_frais_envoi = _request('pc_frais_envoi');
        if (!array_key_exists($ref_ventes,$ref_attribuee)) {
            $ref_attribuee[$ref_ventes]=0;
        }
        else $erreur = true;
        if ($ref_ventes != $ref_frais_envoi) {
            /* vente et frais_envoi peuvent etre associes a la meme reference comptable meme si c'est deconseille d'un point de vue comptable */
            if (!array_key_exists($ref_frais_envoi,$ref_attribuee)) {
                $ref_attribuee[$ref_frais_envoi]=0;
            }
            else $erreur = true;
        }
    }

    if ((_request('prets') == 'on') AND $ref_prets = _request('pc_prets')){
        if (!array_key_exists($ref_prets,$ref_attribuee)) {
            $ref_attribuee[$ref_prets]=0;
        }
        else $erreur = true;
    }

    if ((_request('activites') == 'on') AND $ref_activites = _request('pc_activites')) {
        if (!array_key_exists($ref_activites,$ref_attribuee)) {
            $ref_attribuee[$ref_activites]=0;
        }
        else $erreur = true;
    }
    if ($erreur) {
        $erreurs['message_erreur'] = _T('association:erreur_configurer_association_titre').'<br/>'._T('association:erreur_configurer_association_reference_multiple');
    } elseif (count($erreurs)) {
        // message générique si uniquement erreurs de format
        $erreurs['message_erreur'] = _T('association:erreur_configurer_association_titre');
    }

    // Validation des adresses e-mail pour les destinataires de création de cotisation
    $emails_principal = trim((string)_request('email'));
    $emails_adh = trim((string)_request('config_destinataires_creation_cotisation_adh'));
    $emails_tres = trim((string)_request('config_destinataires_creation_cotisation_tresorier'));
    $emails_notif_defaut = trim((string)_request('config_envoi_email_notif_defaut'));
    $emails_gis = trim((string)_request('notification_gis_config_email'));
    $emails_adhesion_cc = trim((string)_request('config_envoi_recu_adhesion_cc'));
    $emails_participation_cc = trim((string)_request('config_envoi_recu_participation_cc'));
    $invalid = array();
    $invalid_field = '';

    foreach (array(
        'principal' => array('value' => $emails_principal, 'field' => 'email'),
        'adh' => array('value' => $emails_adh, 'field' => 'config_destinataires_creation_cotisation_adh'),
        'tres' => array('value' => $emails_tres, 'field' => 'config_destinataires_creation_cotisation_tresorier'),
        'notif_defaut' => array('value' => $emails_notif_defaut, 'field' => 'config_envoi_email_notif_defaut'),
        'gis' => array('value' => $emails_gis, 'field' => 'notification_gis_config_email'),
        'adhesion_cc' => array('value' => $emails_adhesion_cc, 'field' => 'config_envoi_recu_adhesion_cc'),
        'participation_cc' => array('value' => $emails_participation_cc, 'field' => 'config_envoi_recu_participation_cc'),
    ) as $k => $data) {
        $list = $data['value'];
        $field_name = $data['field'];

        if ($list !== '') {
            // Sépare sur virgule, point-virgule et espaces
            $parts = preg_split('/[;,\s]+/', $list, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $e) {
                $e = trim($e);
                if ($e === '') continue;
                // Validation avec filter_var
                if (!filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    $invalid[] = $e;
                    if (empty($invalid_field)) {
                        $invalid_field = $field_name;
                    }
                }
            }
        }
    }

    // Validation des champs numériques de la maintenance BDD
    foreach (array('meta_cfg_maintenance_jours_inactivite','meta_cfg_maintenance_jours_inscriptions_attente','meta_cfg_maintenance_mois_non_encaisse','meta_cfg_maintenance_lot') as $numfield) {
        $v = trim((string)_request($numfield));
        if ($v !== '' && !ctype_digit($v)) {
            $erreurs[$numfield] = _T('association_config:erreur_entier_positif');
        }
    }

    if (count($invalid)) {
        // On associe l'erreur au premier champ ayant une email invalide pour affichage
        $erreurs[$invalid_field] = _T('association_config:erreur_emails_invalides');
    }
    return $erreurs;
}
// Fonction pour traiter les valeurs du formulaire
function formulaires_configurer_association_traiter_dist($config){
    include_spip('inc/cvt_configurer');
    $retours = array();

    // On charge la liste des saisies proposées par le formulaire segmenté
    $saisies = formulaires_configurer_association_saisies_dist($config,true);

    // On boucle sur les valeurs reçues pour les traiter et renvoyer un résultat vide pour les checkbox non-cochées
    foreach ($saisies as $saisie) {
        $valeurs[$saisie] =  (_request($saisie) ? _request($saisie) : '');
    }

    // On charge finalement le résultat
    $trace = cvtconf_configurer_stocker('configurer_association', array('_meta_table'=>'association_metas'), $valeurs);

    // Message de confirmation initial (toujours présent)
    $retours['message_ok'] = _T('config_info_enregistree') . $trace;
    $retours['editable'] = true;

    // Sauvegarde des préférences de debug (webmestre uniquement)
    if (isset($GLOBALS['visiteur_session']['webmestre']) && $GLOBALS['visiteur_session']['webmestre'] === 'oui') {
        include_spip('inc/association_log');
        foreach (array_keys(association_log_categories_defaut()) as $cat) {
            $v = _request('debug_log_cat_' . $cat) ? 'on' : 'off';
            ecrire_config('association/debug/categories/' . $cat, $v);
        }
    }

    // Si le bouton d'exécution dry-run a été cliqué, exécuter la maintenance en mode dry-run
    if (isset($_REQUEST['exec_maintenance_dry_run']) || _request('exec_maintenance_dry_run')) {
        // Sécurité : vérifier que l'utilisateur est webmestre
        if (empty($GLOBALS['visiteur_session']) || ($GLOBALS['visiteur_session']['webmestre'] != 'oui')) {
            $retours['message_ok'] .= '<br/>' . _T('association_config:config_maintenance_bdd_exec_dry_run_no_rights');
        } else {
            association_log('cron', 'Associaspip: Lancement dry-run via interface (user id: ' . intval($GLOBALS['visiteur_session']['id_auteur'] ?? 0) . ')', 'info');
            // Charger les fonctions du genie
            include_spip('genie/association_maintenance_bdd');

            // Helper pour interpréter les valeurs
            $is_true = function($v) {
                if (is_bool($v)) return $v;
                $v = (string)$v;
                return in_array(strtolower($v), array('1','on','oui','true'), true);
            };

            // Construire les options depuis les valeurs soumises (avec fallbacks)
            $options = array(
                'enabled' => isset($valeurs['meta_cfg_maintenance_bdd_enable']) ? $is_true($valeurs['meta_cfg_maintenance_bdd_enable']) : true,
                'dry_run' => true, // force dry-run
                'jours_inactivite' => isset($valeurs['meta_cfg_maintenance_jours_inactivite']) ? intval($valeurs['meta_cfg_maintenance_jours_inactivite']) : 365,
                'jours_inscriptions_en_attente' => isset($valeurs['meta_cfg_maintenance_jours_inscriptions_attente']) ? intval($valeurs['meta_cfg_maintenance_jours_inscriptions_attente']) : 90,
                'mois_non_encaisse' => isset($valeurs['meta_cfg_maintenance_mois_non_encaisse']) ? intval($valeurs['meta_cfg_maintenance_mois_non_encaisse']) : 6,
                'lot' => isset($valeurs['meta_cfg_maintenance_lot']) ? intval($valeurs['meta_cfg_maintenance_lot']) : 1000,
                'actions' => array(
                    'supprimer_auteurs_sans_paiements' => isset($valeurs['meta_cfg_maintenance_supprimer_auteurs_sans_paiements']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_auteurs_sans_paiements']) : true,
                    'anonymiser_auteurs_avec_paiements' => false,
                    'supprimer_inscriptions_non_validees' => isset($valeurs['meta_cfg_maintenance_supprimer_inscriptions_non_validees']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_inscriptions_non_validees']) : true,
                    'anonymiser_inscriptions_inactifs' => false,
                    'supprimer_cotisations_orphelines' => isset($valeurs['meta_cfg_maintenance_supprimer_cotisations_orphelines']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_cotisations_orphelines']) : true,
                    'supprimer_cotisations_non_encaissees' => isset($valeurs['meta_cfg_maintenance_supprimer_cotisations_non_encaissees']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_cotisations_non_encaissees']) : true,
                    'supprimer_transactions_orphelines' => isset($valeurs['meta_cfg_maintenance_supprimer_transactions_orphelines']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_transactions_orphelines']) : true,
                    'supprimer_participations_orphelines' => isset($valeurs['meta_cfg_maintenance_supprimer_participations_orphelines']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_participations_orphelines']) : true,
                    'supprimer_participations_obsoletes' => isset($valeurs['meta_cfg_maintenance_supprimer_participations_obsoletes']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_participations_obsoletes']) : true,
                    'supprimer_urls_mailsubscriber' => isset($valeurs['meta_cfg_maintenance_supprimer_urls_mailsubscriber']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_urls_mailsubscriber']) : true,
                    'supprimer_urls_obsoletes' => isset($valeurs['meta_cfg_maintenance_supprimer_urls_obsoletes']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_urls_obsoletes']) : true,
                    'supprimer_mailsubscribers_orphelines' => isset($valeurs['meta_cfg_maintenance_supprimer_mailsubscribers_orphelines']) ? $is_true($valeurs['meta_cfg_maintenance_supprimer_mailsubscribers_orphelines']) : true,
                ),
            );

            association_log('cron', 'Associaspip: Options dry-run construites: ' . json_encode($options), 'info');

            // Exécuter la maintenance en dry-run et écrire le rapport
            $resume = association_maintenance_bdd_run(time(), $options);

            $date = date('Y-m-d_H-i-s');
            $dir_rapports = _DIR_TMP . 'rapports/';
            if (!is_dir($dir_rapports)) {
                mkdir($dir_rapports, 0755, true);
            }
            $chemin = $dir_rapports . 'maintenance_asso_dryrun_' . $date .'.json';
            ecrire_fichier($chemin, json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Message récapitulatif court
            $retours['message_ok'] .= '<br/>' . _T('association_config:config_maintenance_bdd_exec_dry_run_done', array('fichier' => basename($chemin)));
            // Afficher directement le rapport JSON formaté dans l'interface
            $report_json = json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            // Escaper pour affichage HTML et limiter la hauteur avec un conteneur scrollable
            $report_html = '<div class="maintenance_report" style="margin-top:1em;">'
                . '<h4>' . _T('association_config:config_maintenance_bdd_exec_dry_run_report_title') . '</h4>'
                . '<pre style="white-space:pre-wrap;word-wrap:break-word;max-height:480px;overflow:auto;border:1px solid #ddd;padding:8px;background:#f9f9f9;">'
                . htmlspecialchars($report_json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</pre></div>';
            $retours['message_ok'] .= $report_html;
         }
     }

     // On retourne le message de confirmation

     return $retours;
 }


// Ajout : construction d'un résumé humain pour les rapports de maintenance (dry-run)
function maintenance_build_human_summary(array $resume, $max_items = 5) {
    // clés prioritaires à présenter
    $keys = array(
        'auteurs_inactifs',
        'auteurs_sans_paiements',
        'auteurs_avec_paiements',
        'inscriptions_non_validees_anciennes',
        'supprimer_cotisations_orphelines',
        'supprimer_cotisations_non_encaissees_anciennes',
        'supprimer_transactions_orphelines',
        'supprimer_participations_evenements_orphelines',
        'supprimer_participations_evenements_obsoletes',
        'supprimer_urls_obsoletes',
        'supprimer_urls_mailsubscriber',
        'supprimer_mailsubscribers_orphelines',
    );

    $html = '<div class="maintenance_summary" style="margin-top:1em;">';
    $html .= '<h4>' . htmlspecialchars(_T('association_config:config_maintenance_bdd_exec_dry_run_report_title')) . ' - résumé</h4>';
    $html .= '<ul style="list-style:none;padding:0;margin:0;">';

    foreach ($keys as $k) {
        if (!isset($resume[$k])) continue;
        $item = $resume[$k];
        $count = null;
        if (isset($item['nombre'])) $count = intval($item['nombre']);
        elseif (isset($item['supprimees'])) $count = intval($item['supprimees']);
        elseif (isset($item['supprimes'])) $count = intval($item['supprimes']);
        elseif (is_int($item)) $count = $item;
        elseif (is_array($item) && isset($item['ids'])) $count = count($item['ids']);
        else {
            if (is_array($item)) {
                foreach (array('ids','ids_activite','transactions_ids','urls') as $ik) {
                    if (isset($item[$ik]) && is_array($item[$ik])) {
                        $count = count($item[$ik]);
                        break;
                    }
                }
            }
        }
        $label = htmlspecialchars($k);
        $html .= '<li style="margin:6px 0;">';
        $html .= '<strong>' . $label . '</strong>';
        $html .= ' : ' . ($count !== null ? intval($count) : '<em>n/a</em>');

        $candidates = array();
        if (isset($item['ids']) && is_array($item['ids'])) $candidates = $item['ids'];
        elseif (isset($item['ids_activite']) && is_array($item['ids_activite'])) $candidates = $item['ids_activite'];
        elseif (isset($item['urls']) && is_array($item['urls'])) $candidates = $item['urls'];
        elseif (isset($item['ids']) && !is_array($item['ids']) && $item['ids']) $candidates = (array)$item['ids'];
        elseif (is_array($item) && count($item) && array_keys($item) === range(0, count($item)-1)) $candidates = $item;

        if ($candidates) {
            $slice = array_slice($candidates, 0, $max_items);
            $html .= '<div style="font-size:0.9em;margin-top:4px;">';
            $html .= 'Top: <code>' . htmlspecialchars(implode(', ', $slice)) . '</code>';
            if (count($candidates) > $max_items) {
                $html .= ' <em>…+' . (count($candidates) - $max_items) . '</em>';
            }
            $html .= '</div>';
        }
        $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</div>';
    return $html;
}

/* Remplacement ciblé : après l'appel à association_maintenance_bdd_run() le code devait écrire le rapport JSON et l'ajouter au message.
   Nous remplaçons cela par : écriture du fichier JSON (inchangé) + génération d'un résumé humain suivi du JSON formaté visible. */

// Exemple de bloc à insérer à l'endroit où $resume a été calculé (dry-run exécuté) :
if (isset($resume) && is_array($resume)) {
    // écriture du fichier rapport (si l'original le faisait)
    $date = date('Y-m-d_H-i-s');
    $dir_rapports = defined('_DIR_TMP') ? _DIR_TMP . 'rapports/' : (_DIR_RACINE . 'tmp/rapports/');
    if (!is_dir($dir_rapports)) {
        @mkdir($dir_rapports, 0755, true);
    }
    $chemin = $dir_rapports . 'maintenance_asso_dryrun_' . $date .'.json';
    if (function_exists('ecrire_fichier')) {
        ecrire_fichier($chemin, json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
        @file_put_contents($chemin, json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // message récapitulatif court (préservons la clé $retours si présente)
    if (!isset($retours)) $retours = array();
    $retours['message_ok'] = (isset($retours['message_ok']) ? $retours['message_ok'] : '');
    $retours['message_ok'] .= '<br/>' . _T('association_config:config_maintenance_bdd_exec_dry_run_done', array('fichier' => basename($chemin)));

    // construire résumé humain + JSON formaté
    $summary_html = maintenance_build_human_summary($resume, 5);
    $report_json = json_encode($resume, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $report_html = '<div class="maintenance_report" style="margin-top:1em;">'
        . '<h4>' . htmlspecialchars(_T('association_config:config_maintenance_bdd_exec_dry_run_report_title')) . '</h4>'
        // résumé humain
        . $summary_html
        // JSON complet en bas, sécurisé
        . '<pre style="white-space:pre-wrap;word-wrap:break-word;max-height:480px;overflow:auto;border:1px solid #ddd;padding:8px;background:#f9f9f9;margin-top:8px;">'
        . htmlspecialchars($report_json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        . '</pre></div>';

    $retours['message_ok'] .= $report_html;
}
