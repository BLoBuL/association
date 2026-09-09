<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function verifier_categorie_adherent_entreprise() {
	return (bool) sql_getfetsel(
		'id_categorie',
		'spip_asso_categories_adherents',
		"type_adherent='entreprise'"
	);
}

function preparer_liste_zones() {
	include_spip('inc/association_adhesions_integrations');
	return saisies_tableau2chaine(association_adhesions_zones_options());
}

function preparer_liste_mailsubscribinglists() {
	$listes = pipeline('association_configuration_listes_diffusion', [
		'args' => [],
		'data' => [],
	]);
	return saisies_tableau2chaine(is_array($listes) ? $listes : []);
}

/**
 * Déclare les panneaux de configuration propres aux adhésions.
 */
function association_adhesions_configurer_saisies($config, $disable_meta_admin = true) {
	$saisies = [];
	if ($config === 'maintenance_bdd' || empty($config)) {
		$saisies[] = association_config_maintenance_fieldset(
			'config_maintenance_adhesions_fieldset',
			_T('association_adhesions:maintenance_titre'),
			[
				association_config_maintenance_input('jours_inactivite', 365),
				association_config_maintenance_radio('supprimer_auteurs_sans_paiements'),
				association_config_maintenance_radio('anonymiser_auteurs_avec_paiements'),
			]
		);
	}
	if (($config === 'modules' || empty($config)) && test_plugin_actif('gis')) {
		$actions_gis = saisies_tableau2chaine([
			'modification_adherent' => _T('association_config:filtre_modification_adherent'),
			'echec_adherent' => _T('association_config:filtre_echec_adherent'),
		]);
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'config_gis_fieldset',
				'label' => _T('association_config:config_gis_fieldset'),
			],
			'saisies' => [
				[
					'saisie' => 'input',
					'options' => [
						'nom' => 'notification_gis_config_email',
						'label' => _T('association_config:notification_gis_config_email_label'),
						'explication' => _T('association_config:notification_gis_config_email_explication'),
						'type' => 'text',
					],
				],
				[
					'saisie' => 'checkbox',
					'options' => [
						'nom' => 'notification_gis_config_action',
						'label' => _T('association_config:notification_gis_config_action_label'),
						'data' => $actions_gis,
					],
				],
			],
		];
	}
	if ($config == 'adhesion' or empty($config)) {
		// *****************************************
		//   Fieldset for cotisation configuration
		// *****************************************
		$saisies[] =
			[
				'saisie' => 'fieldset',
				'options' => [
					'nom' => 'config_cotisation_fieldset',
					'label' => _T('association_config:config_cotisation_fieldset'),
				],
				'saisies' => [
					// Selection for validity
					[
						'saisie' => 'radio',
						'options' => [
							'nom' => 'validite',
							'label' => _T('association_config:config_validite_label'),
							'cacher_option_intro' => 'oui',
							'data' => [
								'scolaire' => _T('association_config:config_choix_scolaire'),
								'annee' => _T('association_config:config_choix_annee'),
							],
							'defaut' => 'scolaire',
						],
					],
					// Input for date scolaire suivante
					[
						'saisie' => 'input',
						'options' => [
							'nom' => 'date_scolaire_suivante',
							'label' => _T('association_config:config_annee_suivante_label'),
							'explication' => _T('association_config:config_scolaire_uniquement'),
							'obligatoire' => 'non',
							'defaut' => '01/06',
							'afficher_si' => '@validite@ == "scolaire"',
						],
					],
					// Input for date scolaire nouvelle
					[
						'saisie' => 'input',
						'options' => [
							'nom' => 'date_scolaire_nouvelle',
							'label' => _T('association_config:config_nouvelle_annee_label'),
							'explication' => _T('association_config:config_scolaire_uniquement'),
							'obligatoire' => 'non',
							'defaut' => '30/09',
							'afficher_si' => '@validite@ == "scolaire"',
						],
					],
				],
			];

		$saisies[] =
			[
				'saisie' => 'fieldset',
				'options' => [
					'nom' => 'config_compte_secondaire_fieldset',
					'label' => _T('association_config:config_compte_secondaire_fieldset_label'),
					'explication' => _T('association_config:config_compte_secondaire_fieldset_explication'),
				],
				'saisies' => [
					// Activer les comptes secondaires automatiquement
					[
						'saisie' => 'radio',
						'options' => [
							'nom' => 'config_compte_secondaire',
							'label' => _T('association_config:config_compte_secondaire_label'),
							'explication' => _T('association_config:config_compte_secondaire_explication'),
							'data' => [
								'oui' => _T('association_adhesions:oui'),
								'non' => _T('association_adhesions:non'),
							],
							'defaut' => lire_config('association_metas/config_compte_secondaire', 'non'),
						],
					],
					// Selection for zone adherent
					[
						'saisie' => 'radio',
						'options' => [
							'nom' => 'config_compte_secondaire_activation',
							'label' => _T('association_config:config_compte_secondaire_activation_label'),
							'explication' => _T('association_config:config_compte_secondaire_activation_explication'),
							'data' => [
								'oui' => _T('association_adhesions:oui'),
								'non' => _T('association_adhesions:non'),
							],
							'afficher_si' => '@config_compte_secondaire@ == "oui"',
							'defaut' => 'non',
						],
					],
				],
			];

		// Fieldset : configuration limite d'âge enfants (global)
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'config_enfants_fieldset',
				'label' => _T('association_config:config_enfants_fieldset'),
			],
			'saisies' => [
				[
					'saisie' => 'input',
					'options' => [
						'nom' => 'meta_cfg_age_limit_enfants',
						'label' => _T('association_config:config_age_limit_enfants_label'),
						'explication' => _T('association_config:config_age_limit_enfants_explication'),
						'defaut' => lire_config('association_metas/meta_cfg_age_limit_enfants', ''),
						'type' => 'number',
					],
				],
			],
		];

		$saisies[] =
			[
				'saisie' => 'fieldset',
				'options' => [
					'nom' => 'config_privileges_fieldset',
					'label' => _T('association_config:config_privileges_fieldset_label'),
					'explication' => _T('association_config:config_privileges_fieldset_explication'),
				],
				'saisies' => [
					// Selection for carte adherent
					[
						'saisie' => 'radio',
						'options' => [
							'nom' => 'meta_cfg_carte_adherent',
							'label' => _T('association_config:config_carte_adherent_label'),
							'explication' => _T('association_config:config_carte_adherent_explication'),
							'data' => [
								'oui' => _T('association_adhesions:oui'),
								'non' => _T('association_adhesions:non'),
							],
							// 'obligatoire' => 'oui',
							'defaut' => 'non',
						],
					],
					// Selection for zone adherent
					[
						'saisie' => 'checkbox',
						'options' => [
							'nom' => 'zone_adherent',
							'label' => _T('association_config:config_zones_label'),
							'explication' => _T('association_config:config_zones_explication'),
							'data' => preparer_liste_zones(), // This will be filled dynamically with zones
						],
					],
					// Checkbox for liste diffusion
					[
						'saisie' => 'checkbox',
						'options' => [
							'nom' => 'liste_diffusion',
							'label' => _T('association_config:config_liste_diffusion_label'),
							'explication' => _T('association_config:config_liste_diffusion_explication'),
							'data' => preparer_liste_mailsubscribinglists(),
						],
					],

				],
			];

		$saisies[] =
			[
				'saisie' => 'fieldset',
				'options' => [
					'nom' => 'config_donation_fieldset',
					'label' => _T('association_config:config_donation_fieldset_label'),
					'explication' => _T('association_config:config_donation_fieldset_explication'),
				],
				'saisies' => [
					// Selection for carte adherent
					[
						'saisie' => 'radio',
						'options' => [
							'nom' => 'meta_cfg_donation',
							'label' => _T('association_config:config_donation_label'),
							'explication' => _T('association_config:config_donation_explication'),
							'data' => [
								'oui' => _T('association_adhesions:oui'),
								'non' => _T('association_adhesions:non'),
							],
							// 'obligatoire' => 'oui',
							'defaut' => 'non',
							'disable' => $disable_meta_admin,
						],
					],
					[
						'saisie' => 'input',
						'options' => [
							'nom' => 'meta_cfg_donation_defaut',
							'label' => _T('association_config:config_donation_defaut_label'),
							'explication' => _T('association_config:config_donation_defaut_explication'),
							'afficher_si' => '@meta_cfg_donation@ == "oui"',
							'disable' => $disable_meta_admin,
						],
					],
				],
			];

		// Fieldset modalités d'inscription cotisation (pages uniques à accepter)
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'config_modalites_inscription_fieldset',
				'label' => _T('association_config:config_modalites_inscription_fieldset_label'),
				'explication' => _T('association_config:config_modalites_inscription_fieldset_explication'),
			],
			'saisies' => [
				[
					'saisie' => 'checkbox',
					'options' => [
						'nom' => 'pages_modalite_inscription',
						'label' => _T('association_config:config_modalites_inscription_pages_label'),
						'explication' => _T('association_config:config_modalites_inscription_pages_explication'),
						'data' => preparer_liste_pages_uniques(),
					],
				],
			],
		];

		// Fieldset for notification configuration
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'notification_recu_paiement',
				'label' => _T('association_config:config_notification_recu_paiement_fieldset'),
			],
			'saisies' => [

				[
					'saisie' => 'radio',
					'options' => [
						'nom' => 'meta_cfg_envoi_recu_paiement_adhesion',
						'label' => _T('association_config:config_envoi_recu_paiement_adhesion_label'),
						'explication' => _T('association_config:config_envoi_recu_paiement_adhesion_explication'),
						'cacher_option_intro' => 'oui',
						'data' => [
							'oui' => _T('association_config:oui'),
							'non' => _T('association_config:non'),
						],
						'defaut' => 'non',
					],
				],
				[
					'saisie' => 'input',
					'options' => [
						'nom' => 'config_envoi_recu_adhesion_cc',
						'label' => _T('association_config:config_envoi_recu_paiement_cc_label'),
						'explication' => _T('association_config:config_envoi_recu_paiement_cc_explication_multi'),
						'type' => 'text',
					],
				],
			],
		];

		// Fieldset for notification configuration
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'notification_adhesion',
				'label' => _T('association_config:config_notification_adhesion_fieldset'),
			],
			'saisies' => [
				[
					'saisie' => 'radio',
					'options' => [
						'nom' => 'meta_cfg_envoi_validation_paiement_adhesion',
						'label' => _T('association_config:config_envoi_validation_paiement_adhesion_label'),
						'explication' => _T('association_config:config_envoi_validation_paiement_adhesion_explication'),
						'cacher_option_intro' => 'oui',
						'data' => [
							'oui' => _T('association_adhesions:oui'),
							'non' => _T('association_adhesions:non'),
						],
						'defaut' => 'oui',
					],
				],

				[
					'saisie' => 'radio',
					'options' => [
						'nom' => 'notification_adherent_echu',
						'label' => _T('association_config:config_notification_adherent_echu_label'),
						'cacher_option_intro' => 'oui',
						'data' => [
							'oui' => _T('association_adhesions:oui'),
							'non' => _T('association_adhesions:non'),
						],
						'defaut' => 'oui',
					],
				],

				[
					'saisie' => 'checkbox',
					'options' => [
						'nom' => 'notification_echeance_cotisation',
						'label' => _T('association_config:config_choix_echeance_label'),
						'data' => [
							'60' => _T('association_config:config_choix_echeance_60'),
							'30' => _T('association_config:config_choix_echeance_30'),
							'15' => _T('association_config:config_choix_echeance_15'),
							'7' => _T('association_config:config_choix_echeance_7'),
						],
						// 'defaut' => array('60','30','15','7'),
					],
				],
				[
					'saisie' => 'input',
					'options' => [
						'nom' => 'config_destinataires_creation_cotisation_tresorier',
						'label' => _T('association_config:config_destinataires_creation_cotisation_label_tresorier'),
						'explication' => _T('association_config:config_destinataires_creation_cotisation_explication_tresorier'),
						'type' => 'text',
					],
				],
			],
		];
	}
	if (($config == 'entreprise' or empty($config)) and verifier_categorie_adherent_entreprise()) {
		$saisies[] =
			[
				'saisie' => 'fieldset',
				'options' => [
					'nom' => 'config_cotisation_entreprise_fieldset',
					'label' => _T('association_config:config_cotisation_entreprise_fieldset'),
				],
				'saisies' => [
					// Selection for validity
					[
						'saisie' => 'radio',
						'options' => [
							'nom' => 'validite_entreprise',
							'label' => _T('association_config:config_validite_label'),
							'cacher_option_intro' => 'oui',
							'data' => [
								'scolaire' => _T('association_config:config_choix_scolaire'),
								'annee' => _T('association_config:config_choix_annee'),
							],
							'defaut' => 'scolaire',
						],
					],
					// Input for date scolaire suivante
					[
						'saisie' => 'input',
						'options' => [
							'nom' => 'date_scolaire_suivante_entreprise',
							'label' => _T('association_config:config_annee_suivante_label'),
							'explication' => _T('association_config:config_scolaire_uniquement'),
							'obligatoire' => 'non',
							'defaut' => '01/06',
							'afficher_si' => '@validite_entreprise@ == "scolaire"',
						],
					],
					// Input for date scolaire nouvelle
					[
						'saisie' => 'input',
						'options' => [
							'nom' => 'date_scolaire_nouvelle_entreprise',
							'label' => _T('association_config:config_nouvelle_annee_label'),
							'explication' => _T('association_config:config_scolaire_uniquement'),
							'obligatoire' => 'non',
							'defaut' => '30/09',
							'afficher_si' => '@validite_entreprise@ == "scolaire"',
						],
					],
				],
			];
		// Fieldset that regroups specific parameter for entreprise accounts
		$saisies[] =
			[
				'saisie' => 'fieldset',
				'options' => [
					'nom' => 'config_adherent_entreprise',
					'label' => _T('association_config:config_adherent_entreprise_fieldset'),
				],
				// Allow or not entreprise account to create a "cotisation"
				// Saisies for entreprise accounts
				'saisies' => [
					[
						'saisie' => 'selection',
						'options' => [
							'nom' => 'meta_cfg_cotisation_compte_entreprise',
							'label' => _T('association_config:config_adherent_compte_entreprise_label'),
							'explication' => _T('association_config:config_adherent_compte_entreprise_explication'),
							'data' => [
								'oui' => _T('association_config:oui_defaut'),
								'non' => _T('association_config:non'),
							],
							'defaut' => 'oui',
						],
					],
					// Allow or entreprise account to register to an event
					[
						'saisie' => 'selection',
						'options' => [
							'nom' => 'meta_cfg_event_inscription_compte_entreprise',
							'label' => _T('association_config:config_inscription_evenement_entreprise_label'),
							'explication' => _T('association_config:config_inscription_evenement_entreprise_explication'),
							'data' => [
								'oui' => _T('association_config:oui'),
								'non' => _T('association_config:non_defaut'),
							],
							'defaut' => 'non',

						],
					],
					// Add entreprise account to existing mailing lists
					[
						'saisie' => 'checkbox',
						'options' => [
							'nom' => 'meta_cfg_liste_diffusion_compte_entreprise',
							'label' => _T('association_config:config_liste_diffusion_compte_entreprise_label'),
							'explication' => _T('association_config:config_liste_diffusion_compte_entreprise_explication'),
							'data' => preparer_liste_mailsubscribinglists(),
						],
					],
					[
						'saisie' => 'radio',
						'options' => [
							'nom' => 'notification_echeance_notifier_echu_entreprise',
							'label' => _T('association_config:notification_echeance_notifier_echu_entreprise_label'),
							'explication' => _T('association_config:notification_echeance_notifier_echu_entreprise_explication'),
							'data' => [
								'oui' => _T('association_adhesions:oui'),
								'non' => _T('association_adhesions:non'),
							],
							'defaut' => (lire_config('association_metas/notification_echeance_notifier_echu_entreprise') ?: 'oui'),
						],
					],
					[
						'saisie' => 'checkbox',
						'options' => [
							'nom' => 'notification_echeance_cotisation_entreprise',
							'label' => _T('association_config:config_choix_echeance_entreprise_label'),
							'explication' => _T('association_config:config_choix_echeance_entreprise_explication'),
							'data' => [
								'60' => _T('association_config:config_choix_echeance_60'),
								'30' => _T('association_config:config_choix_echeance_30'),
								'15' => _T('association_config:config_choix_echeance_15'),
								'7' => _T('association_config:config_choix_echeance_7'),
							],
						],
					],

				],
			];
		$saisies[] =
			// Fieldset dédié : destinataires des notifications de création de cotisation
			[
				'saisie' => 'fieldset',
				'options' => [
					'nom' => 'fieldset_notification_creation_cotisation_entreprise',
					'label' => _T('association_config:config_notification_creation_cotisation_entreprise_fieldset'),
					'explication' => _T('association_config:config_notification_creation_cotisation_entreprise_explication'),
				],
				'saisies' => [

					[
						'saisie' => 'input',
						'options' => [
							'nom' => 'config_destinataires_creation_cotisation_tresorier_entreprise',
							'label' => _T('association_config:config_destinataires_creation_cotisation_label_tresorier'),
							'explication' => _T('association_config:config_destinataires_creation_cotisation_explication_tresorier'),
							'type' => 'text',
						],
					],
				],
			];

		$saisies[] = [
			'saisie' => 'radio',
			'options' => [
				'nom' => 'meta_cfg_cotisations_multidevises',
				'label' => _T('association_config:config_cotisations_multidevises_label'),
				'explication' => _T('association_config:config_cotisations_multidevises_explication'),
				'data' => [
					'oui' => _T('association_adhesions:oui'),
					'non' => _T('association_adhesions:non'),
				],
				'defaut' => 'non',
			],
		];

	}

	if ($config === 'affichage_public') {
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'config_annuaire_membre',
				'label' => _T('association_config:config_annuaire_membre_fieldset'),
			],
			'saisies' => [
				[
					'saisie' => 'checkbox',
					'options' => [
						'nom' => 'config_filtres_annuaire',
						'label' => _T('association_config:config_filtres_annuaire_label'),
						'explication' => _T('association_config:config_filtres_annuaire_explication'),
						'data' => [
							'code_postal' => _T('association_config:config_choix_code_postal'),
							'quartier' => _T('association_config:config_choix_quartier'),
							'ville' => _T('association_config:config_choix_ville'),
						],
					],
				],
			],
		];
	}

	if ($config === 'affichage_prive') {
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'config_filtres_tableau',
				'label' => _T('association_config:config_filtres_tableau_fieldset'),
				'explication' => _T('association_config:config_filtres_tableau_explication'),
			],
			'saisies' => [
				[
					'saisie' => 'selection_multiple',
					'options' => [
						'nom' => 'config_champs_filtres_adherents',
						'label' => _T('association_config:config_champs_filtres_adherents_label'),
						'explication' => _T('association_config:config_champs_filtres_adherents_explication'),
						'data' => preparer_liste_champs_filtres(),
					],
				],
			],
		];
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'config_colonnes_tableau',
				'label' => _T('association_config:config_champs_colonnes_tableau_fieldset'),
				'explication' => _T('association_config:config_champs_colonnes_tableau_explication'),
			],
			'saisies' => [
				[
					'saisie' => 'selection_multiple',
					'options' => [
						'nom' => 'config_champs_colonnes_adherents',
						'label' => _T('association_config:config_champs_colonnes_adherents_label'),
						'explication' => _T('association_config:config_champs_colonnes_adherents_explication'),
						'data' => preparer_liste_champs_filtres(),
					],
				],
			],
		];
	}

	return $saisies;
}
