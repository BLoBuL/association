<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Déclare les panneaux de configuration propres aux adhésions.
 */
function association_adhesions_configurer_saisies($config) {
	$saisies = array();
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
include_spip('formulaires/inc/configurer_association_paiements');
$saisies = array_merge($saisies, association_paiements_configurer_saisies($config, $disable_meta_admin));

	return $saisies;
}
