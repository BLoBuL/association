<?php
/**
 * Registre exhaustif des configurations Association exposees a SPIP CLI.
 *
 * Les noms sont stables et regroupes par domaine. Les chemins restent limites
 * a association_metas : aucune configuration de plugin tiers ni aucun secret
 * technique n'est expose ici.
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Complete le registre principal avec les options du formulaire Association.
 *
 * @param array<string, array<string, mixed>> $registre
 * @return array<string, array<string, mixed>>
 */
function association_config_cli_completer_registre($registre) {
	$nom_site_defaut = function_exists('lire_config') ? lire_config('nom_site', '') : '';
	$definitions = array(
		// Identite de l'association.
		'info.nom' => array('nom', 'string', $nom_site_defaut, 255),
		'info.rue' => array('rue', 'string', '', 255),
		'info.code_postal' => array('cp', 'string', '', 32),
		'info.ville' => array('ville', 'string', '', 128),
		'info.pays' => array('pays', 'string', '', 128),
		'info.email' => array('email', 'email_list', '', 1000),
		'info.telephone' => array('telephone', 'string', '', 64),
		'info.numero_enregistrement' => array('num_enregistrement', 'string', '', 128),
		'info.complement' => array('info_complementaires', 'string', '', 10000),

		// Adhesions, familles, privileges et notifications.
		'adhesion.cotisations_multidevises' => array('meta_cfg_cotisations_multidevises', 'oui_non', 'non'),
		'adhesion.validite' => array('validite', 'enum', 'scolaire', array('scolaire', 'annee')),
		'adhesion.date_scolaire_suivante' => array('date_scolaire_suivante', 'day_month', '01/06'),
		'adhesion.date_scolaire_nouvelle' => array('date_scolaire_nouvelle', 'day_month', '30/09'),
		'adhesion.compte_secondaire' => array('config_compte_secondaire', 'oui_non', 'non'),
		'adhesion.compte_secondaire_activation' => array('config_compte_secondaire_activation', 'oui_non', 'non'),
		'adhesion.age_limite_enfants' => array('meta_cfg_age_limit_enfants', 'integer', '', 0, 150, true),
		'adhesion.carte_adherent' => array('meta_cfg_carte_adherent', 'oui_non', 'non'),
		'adhesion.zones' => array('zone_adherent', 'list', array(), null),
		'adhesion.listes_diffusion' => array('liste_diffusion', 'list', array(), null),
		'adhesion.donation' => array('meta_cfg_donation', 'oui_non', 'non'),
		'adhesion.donation_defaut' => array('meta_cfg_donation_defaut', 'decimal', '', 0, 100000000, true),
		'adhesion.pages_modalites_inscription' => array('pages_modalite_inscription', 'list', array(), null),
		'adhesion.pages_modalites_evenement' => array('pages_modalite_evenement', 'list', array(), null),
		'adhesion.recu_paiement' => array('meta_cfg_envoi_recu_paiement_adhesion', 'oui_non', 'non'),
		'adhesion.recu_paiement_cc' => array('config_envoi_recu_adhesion_cc', 'email_list', '', 2000),
		'adhesion.notification_validation_paiement' => array('meta_cfg_envoi_validation_paiement_adhesion', 'oui_non', 'oui'),
		'adhesion.notification_echeance' => array('notification_adherent_echu', 'oui_non', 'oui'),
		'adhesion.echeances_notification' => array('notification_echeance_cotisation', 'list', array(), array('60', '30', '15', '7')),
		'adhesion.destinataires_creation_cotisation' => array('config_destinataires_creation_cotisation_tresorier', 'email_list', '', 2000),

		// Comptes entreprise.
		'entreprise.validite' => array('validite_entreprise', 'enum', 'scolaire', array('scolaire', 'annee')),
		'entreprise.date_scolaire_suivante' => array('date_scolaire_suivante_entreprise', 'day_month', '01/06'),
		'entreprise.date_scolaire_nouvelle' => array('date_scolaire_nouvelle_entreprise', 'day_month', '30/09'),
		'entreprise.cotisation' => array('meta_cfg_cotisation_compte_entreprise', 'oui_non', 'oui'),
		'entreprise.inscription_evenement' => array('meta_cfg_event_inscription_compte_entreprise', 'oui_non', 'non'),
		'entreprise.listes_diffusion' => array('meta_cfg_liste_diffusion_compte_entreprise', 'list', array(), null),
		'entreprise.notification_echeance' => array('notification_echeance_notifier_echu_entreprise', 'oui_non', 'oui'),
		'entreprise.echeances_notification' => array('notification_echeance_cotisation_entreprise', 'list', array(), array('60', '30', '15', '7')),
		'entreprise.destinataires_creation_cotisation' => array('config_destinataires_creation_cotisation_tresorier_entreprise', 'email_list', '', 2000),

		// Evenements et notifications (hors options historiques deja inscrites).
		'evenement.inscription_repetition' => array('meta_cfg_event_inscription_sur_repetition', 'enum', 'source', array('source_et_repetition', 'source')),
		'evenement.modification_inscription' => array('meta_cfg_event_modification_inscription', 'oui_non', 'oui'),
		'evenement.desinscription' => array('meta_cfg_event_desinscription_inscription', 'enum', 'souple', array('souple', 'strict')),
		'evenement.message_responsable' => array('meta_cfg_event_message_responsable', 'oui_non', 'oui'),
		'evenement.delai_expiration' => array('meta_cfg_event_delai_expiration', 'enum', '', array('', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10')),
		'evenement.quota_adherent' => array('meta_cfg_event_quota_inscription_adherent', 'enum', 'desactive', array('desactive', 'global', 'activites')),
		'evenement.nombre_quota_adherent' => array('nb_inscription_quota_adherent', 'integer', '', 0, 100000, true),
		'evenement.jours_quota_adherent' => array('nb_jour_quota_adherent', 'integer', '', 0, 3650, true),
		'evenement.destinataires_notification' => array('config_envoi_email_notif_defaut', 'email_list', '', 2000),
		'evenement.recu_paiement' => array('meta_cfg_envoi_recu_paiement_participation', 'oui_non', 'non'),
		'evenement.recu_paiement_cc' => array('config_envoi_recu_participation_cc', 'email_list', '', 2000),
		'evenement.telephone_responsable' => array('meta_cfg_telephone_responsable', 'oui_non', 'oui'),
		'evenement.formulaire_contact' => array('meta_cfg_evenement_formulaire_contact', 'oui_non', 'oui'),
		'evenement.email_defaut' => array('meta_cfg_event_email_defaut', 'email_list', '', 1000),
		'evenement.afficher_liste_inscrits' => array('meta_cfg_event_afficher_liste_inscrits', 'enum', '1', array('toujours', '1', '0', 'jamais')),
		'evenement.ouverture_differee' => array('meta_cfg_event_ouverture_differe', 'enum', '0', array('0', 'dt', '7', '14', '21', '28', '42', '56')),
		'evenement.deadline' => array('meta_cfg_event_inscription_deadline', 'enum', 'last_minute', array('last_minute', 'midnight', 'midi', '24h', '48h', '72h', '96h', '7j', '14j', '30j')),
		'evenement.condition_inscription' => array('meta_cfg_event_condition_inscription', 'enum', 'oui', array('toujours', 'oui', 'non', 'jamais')),
		'evenement.message_condition' => array('message_condition_inscription_defaut', 'string', '', 10000),

		// Paiement et taxes.
		'paiement.modes_adhesion' => array('mode_paiement_adhesion', 'list', array(), null),
		'paiement.modes_participation' => array('mode_paiement_participation', 'list', array(), null),
		'paiement.modes_formulaire' => array('mode_paiement_formidable', 'list', array(), null),
		'paiement.taxe_adhesion' => array('meta_cfg_taxe', 'decimal', '', 0, 100, true),
		'paiement.taxe_evenement' => array('meta_cfg_taxe_evenement', 'decimal', '', 0, 100, true),
		'paiement.autorisation_encaissement' => array('meta_cfg_autorisation_encaisser_transaction', 'enum', 'admin_et_responsable', array('tresoriere', 'admin_only', 'admin_et_responsable')),

		// Affichage et modules.
		'affichage.segments' => array('selection_segment', 'list', array(), null),
		'affichage.public_filtres_annuaire' => array('config_filtres_annuaire', 'list', array(), array('code_postal', 'quartier', 'ville')),
		'affichage.public_statuts_inscrits' => array('config_statuts_liste_publique_inscrits', 'list', array(), array('preinscrit', 'liste_attente')),
		'affichage.prive_filtres_adherents' => array('config_champs_filtres_adherents', 'list', array(), null),
		'affichage.prive_colonnes_adherents' => array('config_champs_colonnes_adherents', 'list', array(), null),
		'modules.gis_email' => array('notification_gis_config_email', 'email_list', '', 1000),
		'modules.gis_actions' => array('notification_gis_config_action', 'list', array(), array('modification_adherent', 'echec_adherent')),
		'modules.reseau_fiafe' => array('meta_cfg_event_reseau_fiafe', 'enum', 'desactive', array('active', 'desactive')),
		'modules.profil_reseau_fiafe' => array('meta_cfg_event_profil_reseau_fiafe', 'enum', 'desactive', array('active', 'desactive')),

		// Comptabilite.
		'comptabilite.active' => array('comptes', 'boolean', 'off'),
		'comptabilite.classe_banques' => array('classe_banques', 'identifier', '', 64),
		'comptabilite.destinations' => array('destinations', 'boolean', 'off'),
		'comptabilite.debut_exercice' => array('exercice_comptable_debut', 'day_month', '01/07'),
		'comptabilite.pc_cotisations_creance' => array('pc_cotisations_creance', 'identifier', '416', 64),
		'comptabilite.pc_cotisations_paiement' => array('pc_cotisations_paiement', 'identifier', '7010', 64),
		'comptabilite.dc_cotisations' => array('dc_cotisations', 'identifier', '', 64),
		'comptabilite.pc_activites_creance' => array('pc_activites_creance', 'identifier', '417', 64),
		'comptabilite.pc_activites_paiement' => array('pc_activites_paiement', 'identifier', '7011', 64),
		'comptabilite.pc_activites_frais' => array('pc_activites_frais', 'identifier', '601001', 64),
		'comptabilite.dc_activites' => array('dc_activites', 'identifier', '', 64),
		'comptabilite.dons' => array('dons', 'boolean', 'off'),
		'comptabilite.pc_dons' => array('pc_dons', 'identifier', '', 64),
		'comptabilite.dc_dons' => array('dc_dons', 'identifier', '', 64),
		'comptabilite.ventes' => array('ventes', 'boolean', 'off'),
		'comptabilite.pc_ventes' => array('pc_ventes', 'identifier', '', 64),
		'comptabilite.pc_frais_envoi' => array('pc_frais_envoi', 'identifier', '', 64),
		'comptabilite.dc_ventes' => array('dc_ventes', 'identifier', '', 64),
		'comptabilite.prets' => array('prets', 'boolean', 'off'),
		'comptabilite.pc_prets' => array('pc_prets', 'identifier', '', 64),

		// Maintenance : configuration uniquement, jamais l'action dry-run.
		'maintenance.active' => array('meta_cfg_maintenance_bdd_enable', 'oui_non', 'oui'),
		'maintenance.dry_run' => array('meta_cfg_maintenance_dry_run', 'oui_non', 'oui'),
		'maintenance.jours_inactivite' => array('meta_cfg_maintenance_jours_inactivite', 'integer', '365', 0, 36500),
		'maintenance.jours_inscriptions_attente' => array('meta_cfg_maintenance_jours_inscriptions_attente', 'integer', '90', 0, 36500),
		'maintenance.mois_non_encaisse' => array('meta_cfg_maintenance_mois_non_encaisse', 'integer', '6', 0, 1200),
		'maintenance.lot' => array('meta_cfg_maintenance_lot', 'integer', '1000', 1, 100000),
		'maintenance.supprimer_auteurs_sans_paiements' => array('meta_cfg_maintenance_supprimer_auteurs_sans_paiements', 'oui_non', 'oui'),
		'maintenance.anonymiser_auteurs_avec_paiements' => array('meta_cfg_maintenance_anonymiser_auteurs_avec_paiements', 'oui_non', 'oui'),
		'maintenance.supprimer_inscriptions_non_validees' => array('meta_cfg_maintenance_supprimer_inscriptions_non_validees', 'oui_non', 'oui'),
		'maintenance.anonymiser_inscriptions_inactifs' => array('meta_cfg_maintenance_anonymiser_inscriptions_inactifs', 'oui_non', 'oui'),
		'maintenance.supprimer_cotisations_orphelines' => array('meta_cfg_maintenance_supprimer_cotisations_orphelines', 'oui_non', 'oui'),
		'maintenance.supprimer_cotisations_non_encaissees' => array('meta_cfg_maintenance_supprimer_cotisations_non_encaissees', 'oui_non', 'oui'),
		'maintenance.supprimer_transactions_orphelines' => array('meta_cfg_maintenance_supprimer_transactions_orphelines', 'oui_non', 'oui'),
		'maintenance.supprimer_participations_orphelines' => array('meta_cfg_maintenance_supprimer_participations_orphelines', 'oui_non', 'oui'),
		'maintenance.supprimer_participations_obsoletes' => array('meta_cfg_maintenance_supprimer_participations_obsoletes', 'oui_non', 'oui'),
		'maintenance.supprimer_urls_mailsubscriber' => array('meta_cfg_maintenance_supprimer_urls_mailsubscriber', 'oui_non', 'oui'),
		'maintenance.supprimer_urls_obsoletes' => array('meta_cfg_maintenance_supprimer_urls_obsoletes', 'oui_non', 'oui'),
		'maintenance.supprimer_mailsubscribers_orphelines' => array('meta_cfg_maintenance_supprimer_mailsubscribers_orphelines', 'oui_non', 'oui'),
	);

	$paths_existants = array();
	foreach ($registre as $definition) {
		if (!empty($definition['path'])) {
			$paths_existants[$definition['path']] = true;
		}
	}

	foreach ($definitions as $nom => $spec) {
		$path = 'association_metas/' . $spec[0];
		if (isset($paths_existants[$path])) {
			continue;
		}
		$type = $spec[1];
		$default = $spec[2];
		$description = 'Configuration Association : ' . $nom . '.';
		if ($type === 'enum') {
			$definition = association_config_cli_definition_enum($path, $spec[3], $default, $description);
		} elseif ($type === 'oui_non') {
			$definition = association_config_cli_definition_enum($path, array('oui', 'non'), $default, $description);
		} elseif ($type === 'integer') {
			$definition = association_config_cli_definition_entier($path, $default, $spec[3], $spec[4], $description);
			if (!empty($spec[5])) {
				$definition['allow_empty'] = true;
			}
		} else {
			$definition = array(
				'type' => $type,
				'writable' => true,
				'path' => $path,
				'default' => $default,
				'description' => $description,
			);
			if ($type === 'string' || $type === 'identifier' || $type === 'email_list') {
				$definition['max_length'] = $spec[3];
			} elseif ($type === 'decimal') {
				$definition['min'] = $spec[3];
				$definition['max'] = $spec[4];
				$definition['allow_empty'] = !empty($spec[5]);
			} elseif ($type === 'list' && isset($spec[3]) && is_array($spec[3])) {
				$definition['allowed'] = $spec[3];
			}
		}
		$registre[$nom] = $definition;
		$paths_existants[$path] = true;
	}

	return $registre;
}
