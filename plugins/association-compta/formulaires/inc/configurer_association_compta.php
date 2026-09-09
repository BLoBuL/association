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
	$saisies = [];
	if ($config === 'maintenance_bdd' || empty($config)) {
		$saisies[] = association_config_maintenance_fieldset(
			'config_maintenance_compta_fieldset',
			_T('association_compta:maintenance_titre'),
			[
				association_config_maintenance_input('mois_non_encaisse', 6),
				association_config_maintenance_radio('supprimer_cotisations_orphelines'),
				association_config_maintenance_radio('supprimer_cotisations_non_encaissees'),
			]
		);
	}
	if ($config == 'comptabilite' or empty($config)) {
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'fieldset_config_comptabilite',
				'label' => _T('association_config:config_comptabilite_fieldset'),
				'restrictions' => [
					'voir' => [
						'auteur' => 'webmestre',
					],
					'modifier' => [
						'auteur' => 'webmestre',
					],
				],

			],
			'saisies' => [

				[
					'saisie' => 'case',
					'options' => [
						'nom' => 'comptes',
						'label' => _T('association_config:config_comptes_label'),
						'label_case' => _T('association_config:config_comptes_label_case'),
						// 'explication' => _T('association_config:config_comptes_explication'),
						'defaut' => '',
						'restrictions' => [
							'voir' => [
								'auteur' => 'webmestre',
							],
							'modifier' => [
								'auteur' => 'webmestre',
							],
						],
					],
				],
				[
					'saisie' => 'selection',
					'options' => [
						'nom' => 'classe_banques',
						'label' => _T('association_config:config_classe_banques_label'),
						'explication' => _T(
							'association_config:config_classe_banques_explication',
							['url' => generer_url_ecrire('plan_comptable')]
						),
						'data' => preparer_liste_asso_plan_classe(),
						'defaut' => array_key_exists('5', preparer_liste_asso_plan_classe('array')) ? '5' : '',
					],
				],
				[
					'saisie' => 'case',
					'options' => [
						'nom' => 'destinations',
						'label' => _T('association_config:config_destinations_label'),
						'label_case' => _T('association_config:config_destinations_label_case'),
						'explication' => _T(
							'association_config:config_destinations_explication',
							['url' => generer_url_ecrire('destinations')]
						),
					],
				],
				// Ajout sous-fieldset Exercice comptable
				[
					'saisie' => 'fieldset',
					'options' => [
						'nom' => 'fieldset_exercice_comptable',
						'label' => _T('association_config:config_exercice_comptable_fieldset'),
					],
					'saisies' => [
						[
							'saisie' => 'input',
							'options' => [
								'nom' => 'exercice_comptable_debut',
								'label' => _T('association_config:config_exercice_comptable_debut_label'),
								'explication' => _T('association_config:config_exercice_comptable_debut_explication'),
								'size' => 5,
								'maxlength' => 5,
								'placeholder' => 'JJ/MM',
								'defaut' => '01/07',
							],
						],
					],
				],
				[
					'saisie' => 'fieldset',
					'options' => [
						'nom' => 'fieldset_cotisations',
						'label' => _T('association_config:config_cotisations_fieldset'),
					],
					'saisies' => [
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'pc_cotisations_creance',
								'label' => _T('association_config:config_num_pc_creance_label'),
								'explication' => _T('association_config:config_num_pc_creance_explication'),
								'data' => preparer_liste_asso_plan_compte('data_saisies', '1'),
								'defaut' => '416',
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'pc_cotisations_paiement',
								'label' => _T('association_config:config_num_pc_paiement_label'),
								'explication' => _T('association_config:config_num_pc_paiement_explication'),
								'data' => preparer_liste_asso_plan_compte('data_saisies', '7'),
								'defaut' => '7010',
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'dc_cotisations',
								'label' => _T('association_config:config_num_dc_label'),
								// 'explication' => _T('association_config:config_num_pc'),
								'data' => preparer_liste_asso_destination_comptable(),
								'afficher_si' => '@destinations@ == "on"',
							],
						],
					],
				],
				[
					'saisie' => 'fieldset',
					'options' => [
						'nom' => 'fieldset_activites',
						'label' => _T('association_config:config_activites_fieldset'),
					],
					'saisies' => [
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'pc_activites_creance',
								'label' => _T('association_config:config_num_pc_creance_label'),
								'explication' => _T('association_config:config_num_pc_creance_explication'),
								'data' => preparer_liste_asso_plan_compte('data_saisies', '1'),
								'defaut' => '417',
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'pc_activites_paiement',
								'label' => _T('association_config:config_num_pc_paiement_label'),
								'explication' => _T('association_config:config_num_pc_paiement_explication'),
								'data' => preparer_liste_asso_plan_compte('data_saisies', '7'),
								'defaut' => '7011',
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'pc_activites_frais',
								'label' => _T('association_config:config_num_pc_frais_label'),
								'explication' => _T('association_config:config_num_pc_frais_explication'),
								'data' => preparer_liste_asso_plan_compte('data_saisies', '6'),
								'defaut' => '601001',
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'dc_activites',
								'label' => _T('association_config:config_num_dc_label'),
								'explication' => _T('association_config:config_num_pc'),
								'data' => preparer_liste_asso_destination_comptable(),
								'afficher_si' => '@destinations@ == "on"',
							],
						],
					],
				],
				[
					'saisie' => 'fieldset',
					'options' => [
						'nom' => 'fieldset_dons',
						'label' => _T('association_config:config_dons_fieldset'),
					],
					'saisies' => [
						[
							'saisie' => 'case',
							'options' => [
								'nom' => 'dons',
								'label' => _T('association_config:config_dons_label'),
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'pc_dons',
								'label' => _T('association_config:config_num_pc_label'),
								'data' => preparer_liste_asso_plan_compte(),
								'afficher_si' => '@dons@ == "on"',
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'dc_dons',
								'label' => _T('association_config:config_num_dc_label'),
								'data' => preparer_liste_asso_destination_comptable(),
								'afficher_si' => '@dons@ == "on" && @destinations@ == "on"',
							],
						],
					],
				],
				[
					'saisie' => 'fieldset',
					'options' => [
						'nom' => 'fieldset_ventes',
						'label' => _T('association_config:config_ventes_fieldset'),
					],
					'saisies' => [
						[
							'saisie' => 'case',
							'options' => [
								'nom' => 'ventes',
								'label_case' => _T('association_config:config_ventes_label'),
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'pc_ventes',
								'label' => _T('association_config:config_num_pc_label'),
								'data' => preparer_liste_asso_plan_compte(),
								'afficher_si' => '@ventes@ == "on"',
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'pc_frais_envoi',
								'label' => _T('association_config:config_num_pc_label') . ' ' . _T('association_config:config_frais_envoi_label'),
								'data' => preparer_liste_asso_plan_compte(),
								'afficher_si' => '@ventes@ == "on"',
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'dc_ventes',
								'label' => _T('association_config:config_num_dc_label'),
								'data' => preparer_liste_asso_destination_comptable(),
								'afficher_si' => '@destinations@ == "on" && @ventes@ == "on"',
							],
						],
					],
				],
				[
					'saisie' => 'fieldset',
					'options' => [
						'nom' => 'fieldset_prets',
						'label' => _T('association_config:config_prets_fieldset'),
					],
					'saisies' => [
						[
							'saisie' => 'case',
							'options' => [
								'nom' => 'prets',
								'label_case' => _T('association_config:config_prets_label'),
							],
						],
						[
							'saisie' => 'selection',
							'options' => [
								'nom' => 'pc_prets',
								'label' => _T('association_config:config_num_pc_label'),
								'data' => preparer_liste_asso_plan_compte(),
								'afficher_si' => '@prets@ == "on"',
							],
						],
					],
				],
			],
		];
	}

	return $saisies;
}
