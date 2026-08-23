<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Déclare les panneaux de configuration propres aux événements.
 */
function association_evenements_configurer_saisies($config, $disable_meta_admin = true) {
	$saisies = array();
	if ($config === 'maintenance_bdd' || empty($config)) {
		$saisies[] = association_config_maintenance_fieldset(
			'config_maintenance_evenements_fieldset',
			_T('association_evenements:maintenance_titre'),
			array(
				association_config_maintenance_input('jours_inscriptions_attente', 90),
				association_config_maintenance_radio('supprimer_inscriptions_non_validees'),
				association_config_maintenance_radio('anonymiser_inscriptions_inactifs'),
				association_config_maintenance_radio('supprimer_participations_orphelines'),
				association_config_maintenance_radio('supprimer_participations_obsoletes'),
			)
		);
	}
	if (($config === 'modules' || empty($config))
		&& function_exists('verifier_site_fiafe')
		&& verifier_site_fiafe() === true) {
		$saisies[] = array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'config_evenement_fiafe',
				'label' => _T('association_config:config_evenement_fiafe'),
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
							'desactive' => _T('association_config:evenement_reseau_fiafe_desactive'),
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
							'desactive' => _T('association_config:evenement_reseau_profil_fiafe_desactive'),
						),
						'cacher_option_intro' => 'oui',
						'defaut' => 'desactive',
					),
				),
			),
		);
	}
if($config == 'evenement' OR empty($config)) {

    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'fieldset_specifique_evenement',
            'label' => _T('association_config:config_specifique_evenement_fieldset'),
        ),
        'saisies' => array(
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_inscription_sur_repetition',
                    'label' => _T('association_config:config_inscription_sur_repetition_label'),
                    'explication' => _T('association_config:config_inscription_sur_repetition_explication'),
                    'data' => array(
                        'source_et_repetition' => _T('association_config:oui'),
                        'source' => _T('association_config:non_defaut'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'source',
                ),
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_modification_inscription',
                    'label' => _T('association_config:evenement_modification_inscription_label'),
                    'explication' => _T('association_config:evenement_modification_inscription_explication'),
                    'data' => array(
                        'oui' => _T('association_config:meta_cfg_event_modification_inscription_oui'),
                        'non' => _T('association_config:meta_cfg_event_modification_inscription_non'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'oui',
                ),
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_desinscription_inscription',
                    'label' => _T('association_config:evenement_desinscription_inscription_label'),
                    'explication' => _T('association_config:evenement_desinscription_inscription_explication'),
                    'data' => array(
                        'souple' => _T('association_config:meta_cfg_event_desinscription_inscription_souple'),
                        'strict' => _T('association_config:meta_cfg_event_desinscription_inscription_strict'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'souple',
                ),
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_message_responsable',
                    'label' => _T('association_config:evenement_message_responsable_label'),
                    'explication' => _T('association_config:evenement_message_responsable_explication'),
                    'data' => array(
                        'oui' => _T('association_config:oui_defaut'),
                        'non' => _T('association_config:non'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'oui',
                ),
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_type_quota',
                    'label' => _T('association_config:evenement_type_quota_label'),
                    'explication' => _T('association_config:evenement_type_quota_explication'),
                    'data' => array(
                        'souple' => _T('association_config:evenement_type_quota_souple'),
                        'strict' => _T('association_config:evenement_type_quota_strict'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'souple',
                ),
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_delai_expiration',
                    'label' => _T('association_config:delai_expiration_label'),
                    'explication' => _T('association_config:delai_expiration_explication'),
                    'data' => array(
                        '' => _T('association_config:desactive_par_defaut'),
                        '1' => '1 ' . _T('jour'),
                        '2' => '2 ' . _T('jours'),
                        '3' => '3 ' . _T('jours'),
                        '4' => '4 ' . _T('jours'),
                        '5' => '5 ' . _T('jours'),
                        '6' => '6 ' . _T('jours'),
                        '7' => '7 ' . _T('jours'),
                        '8' => '8 ' . _T('jours'),
                        '9' => '9 ' . _T('jours'),
                        '10' => '10 ' . _T('jours'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => '',
                ),
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_config_accompagnants',
                    'label' => _T('association_config:config_accompagnants_label'),
                    'explication' => _T('association_config:config_accompagnants_explication'),
                    'data' => array(
                        'tout' => _T('association_config:config_accompagnants_tout'),
                        'membre_famille' => _T('association_config:config_accompagnants_membre_famille'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'tout',
                    'disable' => $disable_meta_admin,
                    'restrictions' => array(
                        'voir' => array(
                            'auteur' => 'webmestre',
                        ),
                        'modifier' => array(
                            'auteur' => 'webmestre',
                        ),
                    ),
                ),
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_form_info_supp',
                    'label' => _T('association_config:evenement_form_info_supp_label'),
                    'explication' => _T('association_config:evenement_form_info_supp_explication'),
                    'data' => array(
                        'oui' => _T('association_config:oui'),
                        'non' => _T('association_config:non'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'oui',
                    'disable' => $disable_meta_admin,
                    'class' => 'disable_meta_admin',
                    'restrictions' => array(
                        'voir' => array(
                            'auteur' => 'webmestre',
                        ),
                        'modifier' => array(
                            'auteur' => 'webmestre',
                        ),
                    ),
                ),
            ),
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_quota_inscription_adherent',
                    'label' => _T('association_config:evenement_quota_inscription_adherent_label'),
                    'explication' => _T('association_config:evenement_quota_inscription_adherent_explication'),
                    'data' => array(
                        'desactive' => _T('association_config:evenement_quota_inscription_adherent_desactive'),
                        'global' => _T('association_config:evenement_quota_inscription_adherent_global'),
                        'activites' => _T('association_config:evenement_quota_inscription_adherent_activites'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'desactive',
                ),
            ),
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'nb_inscription_quota_adherent',
                    'label' => _T('association_config:nb_inscription_quota_adherent_label'),
                    'explication' => _T('association_config:nb_inscription_quota_adherent_explication'),
                    'type' => 'text',
                    'afficher_si' => '@meta_cfg_event_quota_inscription_adherent@ != "desactive"',
                ),
            ),
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'nb_jour_quota_adherent',
                    'label' => _T('association_config:nb_jour_quota_adherent_label'),
                    'explication' => _T('association_config:nb_jour_quota_adherent_explication'),
                    'type' => 'text',
                    'afficher_si' => '@meta_cfg_event_quota_inscription_adherent@ != "desactive"',
                ),
            ),

        ),
    );

    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_notification_inscription_evenement',
            'label' => _T('association_config:config_notification_inscription_evenement'),
        ),
        'saisies' => array(
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'config_envoi_email_notif_defaut',
                    'label' => _T('association_config:config_envoi_email_notif_defaut_label'),
                    'explication' => _T('association_config:config_envoi_email_notif_defaut_explication'),
                    'type' => 'text',
                ),
            ),
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'meta_cfg_envoi_recu_paiement_participation',
                    'label' => _T('association_config:config_envoi_recu_paiement_participation_label'),
                    'explication' => _T('association_config:config_envoi_recu_paiement_participation_explication'),
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
                    'nom' => 'config_envoi_recu_participation_cc',
                    'label' => _T('association_config:config_envoi_recu_paiement_cc_label'),
                    'explication' => _T('association_config:config_envoi_recu_paiement_cc_explication_multi'),
                    'type' => 'text',
                ),
            ),

        ),
    );


// Fieldset pour Configuration de l'inscription contact
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_inscription_contact',
            'label' => _T('association_config:config_inscription_contact'),
        ),
        'saisies' => array(
            // Champ meta_cfg_telephone_responsable
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_telephone_responsable',
                    'label' => _T('association_config:meta_cfg_telephone_responsable_label'),
                    'explication' => _T('association_config:meta_cfg_telephone_responsable_explication'),
                    'data' => array(
                        'oui' => _T('association_config:oui'),
                        'non' => _T('association_config:non'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'oui',
                ),
            ),
            // Champ meta_cfg_evenement_formulaire_contact
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_evenement_formulaire_contact',
                    'label' => _T('association_config:meta_cfg_evenement_formulaire_contact_label'),
                    'explication' => _T('association_config:meta_cfg_evenement_formulaire_contact_explication'),
                    'data' => array(
                        'oui' => _T('association_config:oui'),
                        'non' => _T('association_config:non'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'oui',
                ),
            ),
            // Champ meta_cfg_event_email_defaut
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'meta_cfg_event_email_defaut',
                    'label' => _T('association_config:meta_cfg_event_email_defaut_label'),
                    'explication' => _T('association_config:meta_cfg_event_email_defaut_explication'),
                    'type' => 'text',
                ),
            ),
        ),
    );
    // Fieldset modalités d'inscription événements (pages uniques à accepter)
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_modalites_evenement_fieldset',
            'label' => _T('association_config:config_modalites_evenement_fieldset_label'),
            'explication' => _T('association_config:config_modalites_evenement_fieldset_explication'),
        ),
        'saisies' => array(
            array(
                'saisie' => 'checkbox',
                'options' => array(
                    'nom' => 'pages_modalite_evenement',
                    'label' => _T('association_config:config_modalites_evenement_pages_label'),
                    'explication' => _T('association_config:config_modalites_evenement_pages_explication'),
                    'data' => preparer_liste_pages_uniques(),
                ),
            ),
        ),
    );
}
if($config == 'evenement_defaut' OR empty($config)) {
// Fieldset pour Configuration de l'inscription à l'événement
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'config_inscription_evenement',
            'label' => _T('association_config:config_inscription_evenement_fieldset'),
        ),
        'saisies' => array(
            // Checkbox meta_cfg_event_inscription
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'meta_cfg_event_inscription',
                    'label' => _T('association_config:config_inscription_label'),
                    'data' => array(
                        'oui' => _T('association_config:oui_defaut'),
                        'non' => _T('association_config:non'),
                    ),
                    'defaut' => 'oui',
                ),
            ),
            // Sélection meta_cfg_event_type_inscrits_evenement
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_type_inscrits_evenement',
                    'label' => _T('association_config:evenement_type_inscrits_label'),
                    'explication' => _T('association_config:evenement_type_inscrits_explication'),
                    'data' => array(
                        'prive' => _T('association_config:evenement_type_inscrits_prive'),
                        'strict' => _T('association_config:evenement_type_inscrits_strict'),
                        'only_strict' => _T('association_config:evenement_type_inscrits_only_strict'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'prive',
                ),
            ),
            // Sélection meta_cfg_event_afficher_liste_inscrits
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_afficher_liste_inscrits',
                    'label' => _T('association_config:afficher_liste_inscrits_label'),
                    'explication' => _T('association_config:afficher_liste_inscrits_explication'),
                    'data' => array(
                        'toujours' => _T('association_config:toujours'),
                        '1' => _T('association_config:oui'),
                        '0' => _T('association_config:non'),
                        'jamais' => _T('association_config:jamais'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => '1',
                ),
            ),
            // Sélection meta_cfg_event_ouverture_differe
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_ouverture_differe',
                    'label' => _T('association_config:evenement_date_ouverture_differe_label'),
                    'explication' => _T('association_config:evenement_date_ouverture_differe_explication'),
                    'data' => array(
                        '0' => _T('association_config:evenement_date_ouverture_differe_desactive'),
                        'dt' => _T('association_config:evenement_date_ouverture_differe_date'),
                        '7' => _T('association_config:evenement_date_ouverture_differe_une_semaine'),
                        '14' => _T('association_config:evenement_date_ouverture_differe_deux_semaines'),
                        '21' => _T('association_config:evenement_date_ouverture_differe_trois_semaines'),
                        '28' => _T('association_config:evenement_date_ouverture_differe_un_mois'),
                        '42' => _T('association_config:evenement_date_ouverture_differe_un_mois_et_demi'),
                        '56' => _T('association_config:evenement_date_ouverture_differe_deux_mois'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => '0',
                ),
            ),
            // Sélection meta_cfg_event_inscription_deadline
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_inscription_deadline',
                    'label' => _T('association_config:label_evenement_inscription_deadline'),
                    'data' => array(
                        'last_minute' => _T('association_config:evenement_inscription_deadline_derniere_minute'),
                        'midnight' => _T('association_config:evenement_inscription_deadline_midnight'),
                        'midi' => _T('association_config:evenement_inscription_deadline_midi'),
                        '24h' => _T('association_config:evenement_inscription_deadline_24h'),
                        '48h' => _T('association_config:evenement_inscription_deadline_48h'),
                        '72h' => _T('association_config:evenement_inscription_deadline_72h'),
                        '96h' => _T('association_config:evenement_inscription_deadline_96h'),
                        '7j' => _T('association_config:evenement_inscription_deadline_7j'),
                        '14j' => _T('association_config:evenement_inscription_deadline_14j'),
                        '30j' => _T('association_config:evenement_inscription_deadline_30j'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'last_minute',
                ),
            ),
            // Sélection meta_cfg_event_validation
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_validation',
                    'label' => _T('association_config:label_evenement_validation'),
                    'data' => array(
                        'oui' => _T('association_config:evenement_validation_oui'),
                        'non' => _T('association_config:evenement_validation_non'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'non',
                ),
            ),
            // Sélection meta_cfg_event_accompagnants
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_accompagnants',
                    'label' => _T('association_config:label_evenement_accompagnants'),
                    'data' => array(
                        'oui' => _T('association_config:oui'),
                        'non' => _T('association_config:non'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'oui',
                ),
            ),
            // Input meta_cfg_event_limite_nb_accompagnants
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'meta_cfg_event_limite_nb_accompagnants',
                    'label' => _T('association_config:config_limite_nb_accompagnants'),
                    'explication' => _T('association_config:config_limite_nb_accompagnants_explication'),
                    'type' => 'text',
                    'defaut' => '5', // Valeur par défaut
                ),
            ),
            // Sélection meta_cfg_event_invites : autoriser les invités hors famille par défaut
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_invites',
                    'label' => _T('association_config:label_evenement_invites'),
                    'data' => array(
                        'oui' => _T('association_config:oui'),
                        'non' => _T('association_config:non'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'non',
                ),
            ),
            // Sélection meta_cfg_event_file_attente
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_file_attente',
                    'label' => _T('association_config:label_evenement_file_attente'),
                    'data' => array(
                        'oui' => _T('association_config:evenement_file_attente_oui'),
                        'non' => _T('association_config:evenement_file_attente_non'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'oui',
                ),
            ),
            // Sélection meta_cfg_event_validation_auto
            array(
                'saisie' => 'selection',
                'options' => array(
                    'nom' => 'meta_cfg_event_validation_auto',
                    'label' => _T('association_config:label_evenement_validation_auto'),
                    'data' => array(
                        'oui' => _T('association_config:evenement_validation_auto_oui'),
                        'non' => _T('association_config:evenement_validation_auto_non'),
                    ),
                    'cacher_option_intro' => 'oui',
                    'defaut' => 'oui',
                ),
            ),
            // Input meta_cfg_event_limite_places_file_attente
            array(
                'saisie' => 'input',
                'options' => array(
                    'nom' => 'meta_cfg_event_limite_places_file_attente',
                    'label' => _T('association_config:config_limite_file_places_attente'),
                    'explication' => _T('association_config:config_limite_file_places_attente_explication'),
                    'type' => 'text',
                    'defaut' => '5', // Valeur par défaut

                ),
            ),
            // Sélection meta_cfg_event_condition_inscription
            array(
                'saisie' => 'radio',
                'options' => array(
                    'nom' => 'meta_cfg_event_condition_inscription',
                    'label' => _T('association_config:config_label_evenement_condition_inscription_label'),
                    'explication' => _T('association_config:config_label_evenement_condition_inscription_explication'),
                    'data' => array(
                        'toujours' => _T('association_config:config_choix_toujours'),
                        'oui' => _T('association_config:oui'),
                        'non' => _T('association_config:non_defaut'),
                        'jamais' => _T('association_config:config_choix_jamais'),
                    ),
                    'defaut' => 'oui',

                ),
            ),
            array(
                'saisie' => 'textarea',
                'options' => array(
                    'nom' => 'message_condition_inscription_defaut',
                    'label' => _T('association_config:config_label_message_condition_inscription_defaut_label'),
                    'explication' => _T('association_config:config_label_message_condition_inscription_defaut_explication'),
                    'rows' => 3,
                    'cols' => 80,
                ),
                'afficher_si' => '@meta_cfg_event_condition_inscription@ IN "jamais,oui"',
            ),
        ),
    );


}

	if ($config === 'affichage_public') {
		$saisies[] = array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'config_statuts_liste_publique_inscrits_fieldset',
				'label' => _T('association_config:config_statuts_liste_publique_inscrits_fieldset'),
				'explication' => _T('association_config:config_statuts_liste_publique_inscrits_explication'),
			),
			'saisies' => array(
				array(
					'saisie' => 'explication',
					'options' => array(
						'nom' => 'info_statut_ok_fixe',
						'explication' => _T('association_config:config_statuts_liste_publique_ok_fixe'),
					),
				),
				array(
					'saisie' => 'checkbox',
					'options' => array(
						'nom' => 'config_statuts_liste_publique_inscrits',
						'label' => _T('association_config:config_statuts_liste_publique_options_label'),
						'data' => array(
							'preinscrit' => _T('association_config:config_statuts_liste_publique_preinscrit'),
							'liste_attente' => _T('association_config:config_statuts_liste_publique_liste_attente'),
						),
					),
				),
			),
		);
	}

	return $saisies;
}
