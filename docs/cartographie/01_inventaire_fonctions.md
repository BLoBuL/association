# Inventaire des fonctions

Generation: 2026-04-20

## Taxonomie SPIP (lecture transversale)

- `action_*`: 38 fonctions
- `autoriser_*`: 40 fonctions
- `formulaires_*`: 101 fonctions
- `balise_*`: 10 fonctions
- `critere_*`: 1 fonction
- `filtre_*`: 54 fonctions
- Suffixe `_dist`: 138 fonctions

Interpretation:

- Ces familles sont des conventions natives SPIP et ne doivent pas etre classees "hors convention" par defaut.
- Les fonctions sans prefixe SPIP sont majoritairement des helpers metier (a auditer pour nommage domaine).

## Liste complete

### `action/ajouter_activites.php`

- `action_ajouter_activites` (ligne 15)
- `activites_insert` (ligne 32)

### `action/ajouter_destinations.php`

- `action_ajouter_destinations` (ligne 14)
- `destination_insert` (ligne 24)

### `action/ajouter_prets.php`

- `action_ajouter_prets` (ligne 14)
- `prets_insert` (ligne 34)

### `action/editer_asso_comptes.php`

- `action_editer_asso_comptes` (ligne 18)

### `action/editer_asso_dons.php`

- `action_editer_asso_dons` (ligne 16)

### `action/editer_asso_membres.php`

- `action_editer_asso_membres` (ligne 14)

### `action/editer_asso_plan.php`

- `action_editer_asso_plan` (ligne 14)

### `action/editer_asso_ressources.php`

- `action_editer_asso_ressources` (ligne 14)

### `action/editer_asso_ventes.php`

- `action_editer_asso_ventes` (ligne 20)
- `ventes_modifier` (ligne 52)
- `ventes_insert` (ligne 85)

### `action/envoyer_email_collectif_activite.php`

- `action_envoyer_email_collectif_activite` (ligne 12)

### `action/envoyer_email_collectif_adherent.php`

- `action_envoyer_email_collectif_adherent` (ligne 13)

### `action/envoyer_relances.php`

- `action_envoyer_relances` (ligne 13)

### `action/gerer_activites.php`

- `action_gerer_activites` (ligne 14)

### `action/gis_geocoder_rechercher.php`

- `action_gis_geocoder_rechercher_dist` (ligne 15)
- `action_gis_geocoder_rechercher_dist` (ligne 26)

### `action/invalider_compte.php`

- `action_invalider_compte_dist` (ligne 2)

### `action/modifier_activites.php`

- `action_modifier_activites` (ligne 14)

### `action/modifier_destinations.php`

- `action_modifier_destinations` (ligne 14)

### `action/modifier_prets.php`

- `action_modifier_prets` (ligne 14)
- `prets_modifier` (ligne 35)

### `action/modifier_relances.php`

- `action_modifier_relances` (ligne 16)

### `action/supprimer_adherents.php`

- `action_supprimer_adherents` (ligne 14)

### `action/supprimer_categorie_activite.php`

- `action_supprimer_categorie_activite_dist` (ligne 5)

### `action/supprimer_categorie_cotisation.php`

- `action_supprimer_categorie_cotisation_dist` (ligne 5)

### `action/supprimer_commande.php`

- `action_supprimer_commande_dist` (ligne 13)

### `action/supprimer_compte.php`

- `action_supprimer_compte_dist` (ligne 5)

### `action/supprimer_destinations.php`

- `action_supprimer_destinations` (ligne 14)

### `action/supprimer_dons.php`

- `action_supprimer_dons` (ligne 14)

### `action/supprimer_plans.php`

- `action_supprimer_plans` (ligne 14)

### `action/supprimer_prets.php`

- `action_supprimer_prets` (ligne 14)

### `action/supprimer_ressources.php`

- `action_supprimer_ressources` (ligne 14)

### `action/supprimer_ventes.php`

- `action_supprimer_ventes` (ligne 14)

### `action/synchroniser_asso_membres.php`

- `action_synchroniser_asso_membres` (ligne 14)

### `action/synchroniser_comptabilite_evenement.php`

- `action_synchroniser_comptabilite_evenement_dist` (ligne 16)
- `synchroniser_comptabilite_evenement` (ligne 50)
- `nettoyer_doublons_comptabilite` (ligne 219)

### `action/test_notification_cotisation.php`

- `action_test_notification_cotisation_dist` (ligne 8)
- `action_test_notification_cotisation_redirect` (ligne 274)
- `notifications_cotisation_autoriser_test` (ligne 287)

### `action/traiter_comptes.php`

- `action_traiter_comptes_dist` (ligne 7)

### `action/valider_compte.php`

- `action_valider_compte_dist` (ligne 5)

### `association_administrations.php`

- `association_upgrade` (ligne 13)
- `association_maj_create` (ligne 289)
- `association_maj_112` (ligne 301)
- `association_maj_124` (ligne 317)
- `association_maj_spip_asso_activites` (ligne 322)
- `association_maj_142` (ligne 335)
- `association_import_champs_extras` (ligne 352)
- `association_vider_tables` (ligne 377)

### `association_autoriser.php`

- `association_debug_log` (ligne 34)
- `association_autoriser` (ligne 72)
- `association_obtenir_evenement_contexte` (ligne 81)
- `association_normalize_qui` (ligne 120)
- `association_module_actif` (ligne 157)
- `autoriser_adherents_menu_dist` (ligne 174)
- `autoriser_activites_menu_dist` (ligne 190)
- `autoriser_cotisations_menu_dist` (ligne 206)
- `autoriser_benevoles_menu_dist` (ligne 221)
- `autoriser_benevoles_dist` (ligne 225)
- `autoriser_comptes_menu_dist` (ligne 229)
- `autoriser_dons_menu_dist` (ligne 240)
- `autoriser_ressources_menu_dist` (ligne 250)
- `autoriser_ventes_menu_dist` (ligne 260)
- `autoriser_prets_menu_dist` (ligne 270)
- `autoriser_destinations_menu_dist` (ligne 280)
- `autoriser_adherents_associer_dist` (ligne 291)
- `autoriser_comptes_associer_dist` (ligne 294)
- `autoriser_activites_associer_dist` (ligne 297)
- `autoriser_dons_associer_dist` (ligne 300)
- `autoriser_ressources_associer_dist` (ligne 303)
- `autoriser_voiradherent_associer_dist` (ligne 306)
- `autoriser_comptes_dist` (ligne 310)
- `autoriser_asso_comptes_creer_dist` (ligne 356)
- `autorite_autoriser_auteurs_menu` (ligne 374)
- `autorite_autoriser_auteur_voir` (ligne 391)
- `autoriser_asso_modifier` (ligne 402)
- `autoriser_modifier_asso` (ligne 480)
- `autoriser_modifier_asso_dist` (ligne 487)
- `autoriser_modifier_asso_compte_dist` (ligne 502)
- `autoriser_creer_asso_compte_dist` (ligne 548)
- `autoriser_joindredocument` (ligne 580)
- `autoriser_article_creerevenementdans` (ligne 631)
- `autoriser_modifier_evenement_dist` (ligne 651)
- `autoriser_assocompte_modifier_dist` (ligne 674)
- `autoriser_assocompte_creer_dist` (ligne 680)
- `autoriser_newsletter_generer` (ligne 699)
- `autoriser_newsletter_envoyer` (ligne 716)
- `autoriser_newsletter_instituer` (ligne 735)
- `autoriser_publierdans_dist` (ligne 752)
- `autoriser_modifier_article_dist` (ligne 805)
- `autoriser_newsletter_modifier` (ligne 855)
- `autoriser_voir_activites_dist` (ligne 889)
- `autoriser_onglet_activites_dist` (ligne 931)
- `autoriser_page_else_minipres` (ligne 956)
- `autoriser_page` (ligne 968)

### `association_fonctions.php`

- `formulaires_editer_evenement_charger` (ligne 49)
- `evenements_edit_config` (ligne 172)
- `formulaires_editer_evenement_verifier` (ligne 191)
- `formulaires_mot_de_passe_traiter` (ligne 260)
- `preparer_liste_auteurs_newsletter` (ligne 300)
- `mailsubscribers_synchro_list_newsletter_statut_interne_ok` (ligne 311)
- `mailsubscribers_synchro_list_newsletter_statut_interne_prospect` (ligne 320)
- `mailsubscribers_synchro_list_newsletter_statut_interne_echu` (ligne 329)
- `mailsubscribers_synchro_list_newsletter_statut_interne_relance` (ligne 338)
- `mailsubscribers_synchro_list_newsletter_liste_conjoints` (ligne 347)
- `filtre_bank_config_id` (ligne 367)
- `formulaires_editer_newsletter_traiter` (ligne 386)
- `deserialize_values` (ligne 450)
- `association_is_serialized` (ligne 468)
- `filtre_roles_association` (ligne 483)
- `filtre_a_type_cotisation` (ligne 516)
- `filtre_liste_type_cotisation` (ligne 534)
- `filtre_ids_categories_par_type` (ligne 564)
- `filtre_liste_periodes_cotisations` (ligne 603)
- `periode_defaut_libelle` (ligne 746)
- `trouver_periode_par_libelle` (ligne 764)
- `periode_date_debut` (ligne 792)
- `periode_date_fin` (ligne 804)
- `filtre_filtres_effectifs_cotisations` (ligne 824)
- `liste_periodes_cotisations` (ligne 877)
- `filtre_scalar_val` (ligne 911)

### `association_options.php`

- `parser_emails_depuis_config` (ligne 62)
- `association_header_prive` (ligne 97)
- `association_jqueryui_plugins` (ligne 105)
- `association_icone` (ligne 116)
- `association_bouton` (ligne 130)
- `association_bouton_public_fa` (ligne 152)
- `association_bouton_ecrire_fa` (ligne 163)
- `association_lien_public_fa` (ligne 175)
- `association_list_lien_ecrire_fa` (ligne 187)
- `association_lien_ecrire_fa` (ligne 199)
- `association_retour` (ligne 213)
- `request_statut_interne_table_destinataire_mail_collectif` (ligne 217)
- `association_calculer_nom_membre` (ligne 231)
- `association_datefr` (ligne 239)
- `association_heurefr` (ligne 249)
- `association_verifier_date` (ligne 269)
- `association_comparateur_date` (ligne 292)
- `NbJours` (ligne 307)
- `association_nbrefr` (ligne 326)
- `association_recupere_montant` (ligne 332)
- `association_date_du_jour` (ligne 341)
- `association_flottant` (ligne 344)
- `association_telfr` (ligne 347)
- `generer_url_asso_don` (ligne 386)
- `generer_url_don` (ligne 389)
- `generer_url_asso_membre` (ligne 392)
- `generer_url_membre` (ligne 395)
- `generer_url_asso_vente` (ligne 398)
- `generer_url_vente` (ligne 401)
- `adherent_correction_statut` (ligne 404)
- `droit_auteur_evenements` (ligne 436)
- `insert_jqueryui` (ligne 532)
- `responsables_evenement` (ligne 550)

### `association_pipelines.php`

- `association_taches_generales_cron` (ligne 17)
- `association_ajouter_menus` (ligne 32)
- `icone_association` (ligne 99)
- `association_formulaire_charger` (ligne 121)
- `association_formulaire_traiter` (ligne 139)
- `association_pre_edition` (ligne 174)
- `association_post_edition` (ligne 198)
- `association_pre_insertion` (ligne 232)
- `association_post_insertion` (ligne 261)
- `association_afficher_contenu_objet` (ligne 305)
- `association_trig_bank_notifier_reglement` (ligne 352)
- `is_successful_reglement` (ligne 382)
- `mise_a_jour_participation` (ligne 400)
- `association_bank_redirige_apres_retour_transaction` (ligne 462)
- `association_notifications_destinataires` (ligne 493)
- `association_mailsubscriber_formater_informations_liees` (ligne 541)
- `association_mailsubscriber_informations_liees` (ligne 576)
- `association_declarer_tables_objets_sql` (ligne 715)
- `association_i3_verifier_formulaire` (ligne 738)
- `association_get_liens_map` (ligne 764)
- `association_sync_repetitions_tarifs` (ligne 784)
- `association_formulaire_verifier` (ligne 887)
- `association_saisies_lister_disponibles` (ligne 945)

### `balise/autoriser_page.php`

- `balise_AUTORISER_PAGE` (ligne 13)
- `balise_AUTORISER_PAGE_stat` (ligne 21)

### `balise/configurer_metas.php`

- `balise_CONFIGURER_METAS_dyn` (ligne 35)

### `balise/editeur_destinations.php`

- `balise_EDITEUR_DESTINATIONS_dist` (ligne 13)
- `balise_EDITEUR_DESTINATIONS_dyn` (ligne 18)

### `balise/meta.php`

- `balise_META` (ligne 13)
- `choisir_meta` (ligne 21)

### `balise/onglets_association.php`

- `balise_ONGLETS_ASSOCIATION_dist` (ligne 20)
- `balise_ONGLETS_ASSOCIATION_stat` (ligne 30)
- `balise_ONGLETS_ASSOCIATION_dyn` (ligne 40)

### `base/association.php`

- `association_declarer_tables_principales` (ligne 11)
- `association_declarer_tables_auxiliaires` (ligne 216)
- `association_declarer_champs_extras` (ligne 252)

### `base/association_champs_extras.php`

- `association_declarer_champs_extras_impl` (ligne 13)
- `association_preparer_donnees_communes` (ligne 63)
- `association_champs_evenement_base` (ligne 159)
- `association_champs_inscription` (ligne 290)
- `association_champs_validation` (ligne 440)
- `association_champs_paiement` (ligne 476)
- `association_champs_accompagnants` (ligne 593)
- `association_champs_attente` (ligne 653)
- `association_champs_conditions_inscription` (ligne 719)
- `association_champs_info_supplementaire` (ligne 794)
- `association_champs_communication_fiafe` (ligne 831)

### `exec/action_activites.php`

- `exec_action_activites` (ligne 13)

### `exec/action_adherents.php`

- `exec_action_adherents` (ligne 17)
- `exec_action_adherents_args` (ligne 27)
- `supprimer_adherents` (ligne 46)

### `exec/action_comptes.php`

- `exec_action_comptes` (ligne 18)
- `exec_action_comptes_args` (ligne 28)
- `supprimer_comptes` (ligne 46)

### `exec/action_destinations.php`

- `exec_action_destinations` (ligne 19)
- `cadre_relief` (ligne 35)

### `exec/action_email_collectif_activite.php`

- `exec_action_email_collectif_activite` (ligne 18)

### `exec/action_email_collectif_adherent.php`

- `exec_action_email_collectif_adherent` (ligne 16)

### `exec/action_email_relances.php`

- `exec_action_email_relances` (ligne 16)

### `exec/action_labels.php`

- `exec_action_labels` (ligne 10)

### `exec/action_plan.php`

- `exec_action_plan` (ligne 19)
- `cadre_relief` (ligne 35)

### `exec/action_prets.php`

- `exec_action_prets` (ligne 17)

### `exec/action_relances.php`

- `exec_action_relances` (ligne 20)

### `exec/action_ressources.php`

- `exec_action_ressources` (ligne 17)
- `exec_action_ressources_args` (ligne 28)

### `exec/action_ventes.php`

- `exec_action_ventes` (ligne 17)

### `exec/action_voir.php`

- `exec_action_voir` (ligne 17)
- `action_comptes_ligne` (ligne 50)

### `exec/activites.php`

- `exec_activites` (ligne 14)
- `exec_activites_evenements` (ligne 54)

### `exec/adherents_bck.php`

- `exec_adherents` (ligne 19)

### `exec/bilan.php`

- `exec_bilan` (ligne 23)
- `bilan_encaisse` (ligne 165)

### `exec/configurer_visuel.php`

- `exec_configurer_visuel` (ligne 11)

### `exec/csv_activites.php`

- `exec_csv_activites` (ligne 18)

### `exec/csv_adherents.php`

- `exec_csv_adherents` (ligne 23)

### `exec/destinations.php`

- `exec_destinations` (ligne 19)
- `raccourcis` (ligne 34)
- `cadre_relief` (ligne 41)
- `get_destination_rows` (ligne 63)
- `create_destination_row` (ligne 73)

### `exec/dons.php`

- `exec_dons` (ligne 16)

### `exec/edit_compte.php`

- `exec_editer_asso_comptes` (ligne 20)

### `exec/edit_cotisation.php`

- `exec_edit_cotisation` (ligne 15)

### `exec/edit_destination.php`

- `exec_edit_destination` (ligne 18)

### `exec/edit_don.php`

- `exec_edit_don` (ligne 18)

### `exec/edit_email_collectif_activite.php`

- `exec_edit_email_collectif_activite` (ligne 18)

### `exec/edit_email_collectif_adherent.php`

- `exec_edit_email_collectif_adherent` (ligne 16)

### `exec/edit_labels.php`

- `exec_edit_labels` (ligne 17)
- `labels_adherents` (ligne 85)

### `exec/edit_mail.php`

- `exec_edit_mail` (ligne 17)

### `exec/edit_plan.php`

- `exec_edit_plan` (ligne 18)

### `exec/edit_pret.php`

- `exec_edit_pret` (ligne 17)

### `exec/edit_relances.php`

- `exec_edit_relances` (ligne 18)
- `relances_while` (ligne 137)

### `exec/edit_ressource.php`

- `exec_edit_ressource` (ligne 19)

### `exec/edit_vente.php`

- `exec_edit_vente` (ligne 17)

### `exec/pdf_activite.php`

- `exec_pdf_activite` (ligne 15)

### `exec/pdf_adherents.php`

- `exec_pdf_adherents` (ligne 15)

### `exec/pdf_fiscal.php`

- `exec_pdf_fiscal` (ligne 30)
- `build_pdf` (ligne 60)

### `exec/plan_comptable.php`

- `exec_plan_comptable` (ligne 18)
- `raccourcis` (ligne 36)
- `cadre_relief` (ligne 50)

### `exec/prets.php`

- `exec_prets` (ligne 19)

### `exec/ressources.php`

- `exec_ressources` (ligne 19)
- `boite_info` (ligne 36)
- `raccourcis` (ligne 44)
- `cadre_relief` (ligne 48)

### `exec/settings_list_members_event.php`

- `exec_settings_list_members_event` (ligne 16)

### `exec/ventes.php`

- `exec_ventes` (ligne 18)

### `exec/voir_adherent.php`

- `exec_voir_adherent` (ligne 16)

### `export_comptes_evenement.csv_fonctions.php`

- `montant_signe` (ligne 25)
- `total_recettes_evenement` (ligne 43)
- `total_depenses_evenement` (ligne 55)
- `solde_evenement` (ligne 67)
- `filtre_justification_compte_dist` (ligne 83)

### `formulaires/adherents_recherche_avancee.php`

- `formulaires_adherents_recherche_avancee_saisies` (ligne 16)
- `formulaires_adherents_recherche_avancee_charger_dist` (ligne 21)
- `formulaires_adherents_recherche_avancee_verifier_dist` (ligne 26)
- `formulaires_adherents_recherche_avancee_traiter_dist` (ligne 32)

### `formulaires/adherents_recherche_rapide.php`

- `formulaires_adherents_recherche_rapide_charger_dist` (ligne 17)
- `formulaires_adherents_recherche_rapide_verifier_dist` (ligne 73)
- `formulaires_adherents_recherche_rapide_traiter_dist` (ligne 79)

### `formulaires/choisir_gabarit_envoi_collectif.php`

- `formulaires_choisir_gabarit_envoi_collectif_saisies` (ligne 16)
- `formulaires_choisir_gabarit_envoi_collectif_charger_dist` (ligne 54)

### `formulaires/configurer_association.php`

- `formulaires_configurer_association_saisies_dist` (ligne 17)
- `formulaires_configurer_association_charger_dist` (ligne 1848)
- `formulaires_configurer_association_verifier_dist` (ligne 1854)
- `formulaires_configurer_association_traiter_dist` (ligne 1973)
- `maintenance_build_human_summary` (ligne 2076)

### `formulaires/desinscription_evenement_public.php`

- `formulaires_desinscription_evenement_public_charger_dist` (ligne 5)
- `formulaires_desinscription_evenement_public_traiter_dist` (ligne 41)

### `formulaires/editer_asso_categorie_activite.php`

- `formulaires_editer_asso_categorie_activite_saisies_dist` (ligne 13)
- `formulaires_editer_asso_categorie_activite_charger_dist` (ligne 101)
- `formulaires_editer_asso_categorie_activite_traiter_dist` (ligne 141)

### `formulaires/editer_asso_categorie_cotisation.php`

- `formulaires_editer_asso_categorie_cotisation_saisies_dist` (ligne 22)
- `formulaires_editer_asso_categorie_cotisation_charger_dist` (ligne 227)
- `formulaires_editer_asso_categorie_cotisation_verifier_dist` (ligne 273)
- `formulaires_editer_asso_categorie_cotisation_traiter_dist` (ligne 323)

### `formulaires/editer_asso_comptes.php`

- `formulaires_editer_asso_comptes_saisies_dist` (ligne 8)
- `formulaires_editer_asso_comptes_charger_dist` (ligne 179)
- `preparer_liste_evenements` (ligne 242)
- `formulaires_editer_asso_comptes_verifier_dist` (ligne 266)
- `formulaires_editer_asso_comptes_traiter_dist` (ligne 383)

### `formulaires/editer_asso_cotisation.php`

- `formulaires_editer_asso_cotisation_saisies` (ligne 22)
- `formulaires_editer_asso_cotisation_charger_dist` (ligne 229)
- `formulaires_editer_asso_cotisation_verifier_dist` (ligne 275)
- `formulaires_editer_asso_cotisation_traiter` (ligne 375)
- `formulaires_editer_asso_cotisation_fichiers` (ligne 439)

### `formulaires/editer_asso_destinations.php`

- `formulaires_editer_asso_destinations_charger_dist` (ligne 16)
- `formulaires_editer_asso_destinations_verifier_dist` (ligne 22)
- `formulaires_editer_asso_destinations_traiter_dist` (ligne 31)

### `formulaires/editer_asso_dons.php`

- `formulaires_editer_asso_dons_charger_dist` (ligne 17)
- `formulaires_editer_asso_dons_verifier_dist` (ligne 58)
- `formulaires_editer_asso_dons_traiter` (ligne 92)

### `formulaires/editer_asso_membres.php`

- `formulaires_editer_asso_membres_charger_dist` (ligne 16)
- `formulaires_editer_asso_membres_verifier_dist` (ligne 26)
- `formulaires_editer_asso_membres_traiter` (ligne 49)

### `formulaires/editer_asso_plan.php`

- `formulaires_editer_asso_plan_charger_dist` (ligne 16)
- `formulaires_editer_asso_plan_verifier_dist` (ligne 45)
- `formulaires_editer_asso_plan_traiter_dist` (ligne 77)

### `formulaires/editer_asso_ressources.php`

- `formulaires_editer_asso_ressources_charger_dist` (ligne 16)
- `formulaires_editer_asso_ressources_verifier_dist` (ligne 39)
- `formulaires_editer_asso_ressources_traiter` (ligne 60)

### `formulaires/editer_asso_ventes.php`

- `formulaires_editer_asso_ventes_charger_dist` (ligne 17)
- `formulaires_editer_asso_ventes_verifier_dist` (ligne 59)
- `formulaires_editer_asso_ventes_traiter` (ligne 95)

### `formulaires/editer_evenement.php`

- `formulaires_editer_evenement_charger_dist` (ligne 17)
- `formulaires_editer_evenement_identifier_dist` (ligne 98)
- `formulaires_editer_evenement_verifier_dist` (ligne 101)
- `formulaires_editer_evenement_verifier_modifie_evenements_lies` (ligne 153)
- `formulaires_editer_evenement_traiter_dist` (ligne 181)

### `formulaires/email_collectif_adherent.php`

- `formulaires_email_collectif_adherent_fichiers` (ligne 20)
- `formulaires_email_collectif_adherent_saisies` (ligne 23)
- `formulaires_email_collectif_adherent_charger_dist` (ligne 233)
- `formulaires_email_collectif_adherent_verifier_dist` (ligne 338)
- `formulaires_email_collectif_adherent_traiter_dist` (ligne 376)
- `information_expediteur_email_collectif` (ligne 475)

### `formulaires/importer_destination_comptable.php`

- `formulaires_importer_destination_comptable_saisies_dist` (ligne 7)
- `formulaires_importer_destination_comptable_charger_dist` (ligne 57)
- `formulaires_importer_destination_comptable_verifier_dist` (ligne 66)
- `formulaires_importer_destination_comptable_traiter_dist` (ligne 75)
- `decoder_fichier_json` (ligne 104)
- `lister_destinations_json` (ligne 120)
- `lister_destination_recurive` (ligne 131)
- `generer_saisies_destinations` (ligne 142)

### `formulaires/importer_plan_comptable.php`

- `formulaires_importer_plan_comptable_saisies_dist` (ligne 15)
- `formulaires_importer_plan_comptable_charger_dist` (ligne 58)
- `formulaires_importer_plan_comptable_verifier_dist` (ligne 71)
- `formulaires_importer_plan_comptable_traiter_dist` (ligne 84)
- `decoder_fichier_json` (ligne 120)
- `lister_comptes_json` (ligne 140)
- `lister_compte_recurive` (ligne 157)
- `generer_saisies_comptes` (ligne 178)

### `formulaires/inc/adherents_recherche_avancee.php`

- `adherents_recherche_avancee_saisies` (ligne 14)
- `adherents_recherche_avancee_statut_adhesion_saisie` (ligne 73)
- `adherents_recherche_avancee_formater_saisie` (ligne 95)
- `nettoyage_liste_config_inscription3` (ligne 189)
- `adherents_recherche_avancee_multicritere_saisie` (ligne 211)
- `preparer_criteres_adherents` (ligne 262)
- `generer_array_adherents` (ligne 490)
- `afficher_resultat_recherche_avancee` (ligne 640)
- `association_config_champs_colonnes_triables` (ligne 663)

### `formulaires/inc/configurer_association.php`

- `preparer_choix_mode_paiement` (ligne 9)
- `identifier_tresorier` (ligne 28)
- `verifier_categorie_adherent_entreprise` (ligne 35)
- `preparer_liste_zones` (ligne 43)
- `preparer_liste_mailsubscribinglists` (ligne 56)
- `preparer_liste_champs_filtres` (ligne 70)

### `formulaires/inc/destinations.php`

- `update_destination_contexte_from_compte` (ligne 13)
- `_get_map_of_destination_id_montant` (ligne 34)
- `verifier_destination_comptable` (ligne 60)
- `_verifier_montant_destinations` (ligne 75)
- `association_editeur_destinations` (ligne 123)
- `_get_destination_id_intitule_options` (ligne 191)

### `formulaires/inc/inscription_evenement.php`

- `preparer_info_auteur` (ligne 26)
- `generer_array_categories_participation` (ligne 75)
- `preparer_chargement_modification_inscription` (ligne 133)
- `preparer_chargement_modification_inscription_multi` (ligne 244)
- `generer_famille_adherent` (ligne 330)
- `generer_saisies_info_public` (ligne 368)
- `generer_detail_participants` (ligne 449)
- `comparer_modification` (ligne 550)
- `formater_post_form` (ligne 622)
- `formater_post_form_multi` (ligne 761)
- `calculer_montant_total` (ligne 850)
- `generer_recapitulatif_multi` (ligne 923)
- `inserer_asso_activites` (ligne 1013)
- `modifier_asso_activites` (ligne 1054)
- `preparer_entree_journal` (ligne 1088)
- `notifier_inscription_activite` (ligne 1113)
- `inserer_transaction_activites` (ligne 1140)
- `modifier_transaction_activites` (ligne 1170)
- `inscrire_participant_mailsubscriber` (ligne 1200)
- `verifier_spam_formulaire_inscription` (ligne 1243)

### `formulaires/inc/inscription_evenement_saisies.php`

- `champs_saisie_nb_inscrits` (ligne 4)
- `champs_saisies_selection_membres_famille` (ligne 45)
- `champs_saisies_inscrits` (ligne 75)
- `champs_saisies_famille` (ligne 173)
- `champs_saisies_tarifs` (ligne 251)
- `trierparMontant` (ligne 303)
- `champs_saisies_info_supplementaire` (ligne 315)

### `formulaires/inscription_evenement.php`

- `formulaires_inscription_evenement_charger_dist` (ligne 19)
- `formulaires_inscription_evenement_verifier_dist` (ligne 411)
- `formulaires_inscription_evenement_traiter_dist` (ligne 595)

### `formulaires/inscription_evenement_multi.php`

- `formulaires_inscription_evenement_multi_saisies` (ligne 20)
- `formulaires_inscription_evenement_multi_charger_dist` (ligne 309)
- `formulaires_inscription_evenement_multi_verifier_1_dist` (ligne 347)
- `formulaires_inscription_evenement_multi_verifier_2_dist` (ligne 368)
- `formulaires_inscription_evenement_multi_verifier_3_dist` (ligne 428)
- `formulaires_inscription_evenement_multi_verifier_4_dist` (ligne 483)
- `formulaires_inscription_evenement_multi_traiter_dist` (ligne 504)

### `formulaires/inscription_evenement_multi_public.php`

- `formulaires_inscription_evenement_multi_public_saisies` (ligne 34)
- `formulaires_inscription_evenement_multi_public_charger_dist` (ligne 242)
- `formulaires_inscription_evenement_multi_public_verifier_dist` (ligne 319)
- `formulaires_inscription_evenement_multi_public_traiter_dist` (ligne 496)

### `formulaires/inscription_evenement_public.php`

- `formulaires_inscription_evenement_public_charger_dist` (ligne 17)
- `formulaires_inscription_evenement_public_verifier_dist` (ligne 382)
- `formulaires_inscription_evenement_public_traiter_dist` (ligne 546)

### `formulaires/migrer_asso_comptabilite.php`

- `formulaires_migrer_asso_comptabilite_saisies_dist` (ligne 9)
- `formulaires_migrer_asso_comptabilite_charger_dist` (ligne 88)
- `formulaires_migrer_asso_comptabilite_verifier_dist` (ligne 93)
- `formulaires_migrer_asso_comptabilite_traiter_dist` (ligne 118)
- `appliquer_migration_manuelle` (ligne 164)
- `appliquer_migration_auto` (ligne 188)
- `_migration_est_adherent_actif` (ligne 243)
- `_migration_generer_justification_cotisation` (ligne 264)
- `get_config_plan_comptable_migration` (ligne 283)
- `preparer_liste_compte_imputation` (ligne 294)

### `formulaires/rembourser_transaction.php`

- `formulaires_rembourser_transaction_charger_dist` (ligne 15)
- `formulaires_rembourser_transaction_verifier_dist` (ligne 35)
- `formulaires_rembourser_transaction_traiter_dist` (ligne 44)
- `remboursement_prefixe` (ligne 72)

### `formulaires/supprimer_asso_cotisation.php`

- `formulaires_supprimer_asso_cotisation_charger_dist` (ligne 3)
- `formulaires_supprimer_asso_cotisation_verifier_dist` (ligne 14)
- `formulaires_supprimer_asso_cotisation_traiter_dist` (ligne 26)

### `formulaires/synchro_asso_membres.php`

- `formulaires_synchro_asso_membres_charger_dist` (ligne 16)
- `formulaires_synchro_asso_membres_traiter` (ligne 26)

### `genie/association_expiration_auto_evenement.php`

- `genie_association_expiration_auto_evenement_dist` (ligne 20)

### `genie/association_maintenance_bdd.php`

- `asso_table_col_for_type` (ligne 13)
- `genie_association_maintenance_bdd` (ligne 70)
- `association_maintenance_bdd_run` (ligne 134)
- `asso_recuperer_auteurs_inactifs` (ligne 275)
- `asso_separer_auteurs_par_encaissements` (ligne 301)
- `asso_supprimer_auteurs` (ligne 336)
- `asso_supprimer_comptes_auteurs` (ligne 421)
- `asso_supprimer_transactions_auteurs` (ligne 436)
- `asso_supprimer_mailsubscribers_pour_auteurs` (ligne 451)
- `asso_anonymiser_auteurs` (ligne 507)
- `asso_trouver_inscriptions_non_validees_anciennes` (ligne 539)
- `asso_supprimer_inscriptions_par_ids` (ligne 565)
- `asso_supprimer_transactions_inscriptions` (ligne 579)
- `asso_anonymiser_inscriptions_auteurs` (ligne 595)
- `asso_supprimer_cotisations_orphelines` (ligne 632)
- `asso_supprimer_transactions_orphelines` (ligne 749)
- `asso_supprimer_cotisations_non_encaissees_anciennes` (ligne 839)
- `asso_supprimer_participations_evenements_orphelines` (ligne 930)
- `asso_supprimer_participations_evenements_obsoletes` (ligne 1009)
- `asso_supprimer_urls_obsoletes` (ligne 1133)
- `asso_supprimer_urls_par_type` (ligne 1210)
- `asso_supprimer_mailsubscribers_orphelines` (ligne 1259)

### `genie/association_taches_generales.php`

- `genie_association_taches_generales` (ligne 19)

### `inc/api_cotisations.php`

- `api_cotisations_saisies_communes` (ligne 29)
- `api_traiter_cotisation` (ligne 141)
- `preparer_liste_categories` (ligne 335)
- `identifier_categories_necessite_justificatif` (ligne 399)
- `traiter_upload_justificatif` (ligne 428)

### `inc/association/utils.php`

- `is_db_value_true` (ligne 9)

### `inc/association_comptabilite.php`

- `association_liste_destinations_associees` (ligne 16)
- `association_toutes_destination_option_list` (ligne 38)
- `association_editeur_destinations` (ligne 54)
- `association_ajouter_operation_comptable` (ligne 118)
- `association_modifier_operation_comptable` (ligne 142)
- `association_modifier_cotisation` (ligne 176)
- `association_verifier_montant_destinations` (ligne 210)
- `association_ajouter_destinations_comptables` (ligne 254)

### `inc/association_log.php`

- `association_log_categories_defaut` (ligne 27)
- `association_log_doit_logger` (ligne 52)
- `association_log_suffixe` (ligne 71)
- `association_log_caller` (ligne 89)
- `association_log` (ligne 121)

### `inc/autorisations.php`

- `association_est_admin_complet` (ligne 27)
- `association_est_responsable_evenement` (ligne 42)
- `association_peut_acceder_evenement` (ligne 64)

### `inc/comptes.php`

- `preparer_liste_asso_plan_classe` (ligne 19)
- `preparer_liste_asso_plan_compte` (ligne 56)
- `inserer_compte` (ligne 108)
- `modifier_compte` (ligne 174)
- `inserer_compte_activite` (ligne 255)
- `inserer_compte_remboursement_activite` (ligne 328)
- `modifier_compte_activite` (ligne 405)
- `valider_compte_activite` (ligne 505)
- `supprimer_compte_activite` (ligne 546)
- `compte_vente` (ligne 553)
- `compte_vente_frais_envoi` (ligne 573)
- `compte_don` (ligne 590)
- `compte_cotisation` (ligne 624)
- `modifier_compte_vente` (ligne 654)
- `modifier_activite_vente_frais_envoi` (ligne 676)
- `modifier_compte_don` (ligne 695)
- `modifier_compte_cotisation` (ligne 731)

### `inc/cotisations.php`

- `association_obtenir_delais_echeance` (ligne 16)
- `mise_a_jour_cotisation` (ligne 85)
- `changer_statut_cotisation` (ligne 109)
- `notifications_cotisation_trouver_sujet` (ligne 260)
- `notifier_cotisation_adherent` (ligne 393)
- `notifier_cotisation_preparer_contexte` (ligne 638)
- `notifier_cotisation_admin` (ligne 787)
- `activer_adherent` (ligne 921)
- `inverser_relation_compte` (ligne 1053)
- `associer_liste_diffusion_entreprise` (ligne 1097)
- `obtenir_emails_tresoriers` (ligne 1162)
- `calculer_dates_scolaires` (ligne 1217)
- `identification_contexte_inscription` (ligne 1267)

### `inc/destinations.php`

- `ajouter_destinations` (ligne 15)
- `create_destination_map_for_montant` (ligne 70)
- `destinations_are_enabled` (ligne 86)
- `default_destination_is_set` (ligne 90)
- `preparer_liste_asso_destination_comptable` (ligne 98)

### `inc/exporter_csv.php`

- `exporter_csv_champ` (ligne 22)
- `exporter_csv_ligne` (ligne 40)
- `inc_exporter_csv_dist` (ligne 83)

### `inc/fonctions/activite_calculator.php`

- `activite_calculator` (ligne 17)

### `inc/fonctions/activite_enregistrement_calculator.php`

- `activite_enregistrement_calculator` (ligne 17)

### `inc/fonctions/affichage_dans_activites.php`

- `affichage_dans_activites` (ligne 17)

### `inc/fonctions/alerte_inscription_evenement.php`

- `alerte_inscription_evenement` (ligne 8)

### `inc/fonctions/association_job_notifier_echeance.php`

- `association_job_notifier_echeance` (ligne 19)

### `inc/fonctions/association_validite_calculator.php`

- `association_validite_calculator` (ligne 7)

### `inc/fonctions/comptes.php`

- `association_comptes_bornes_exercice` (ligne 13)
- `association_comptes_start_year_from_date` (ligne 32)
- `filtre_calcul_fonction_comptes_calculer_totaux` (ligne 53)
- `filtre_calcul_fonction_comptes_lister_annees` (ligne 139)
- `filtre_calcul_fonction_comptes_lister_exercices` (ligne 143)
- `filtre_calcul_fonction_comptes_lister_operations` (ligne 166)
- `filtre_calcul_fonction_comptes_compter_operations` (ligne 179)
- `filtre_calcul_fonction_comptes_compter_recettes` (ligne 202)
- `filtre_calcul_fonction_comptes_compter_depenses` (ligne 225)
- `filtre_calcul_fonction_comptes_compter_recettes_selon_inclusion` (ligne 244)
- `filtre_calcul_fonction_comptes_compter_depenses_selon_inclusion` (ligne 255)
- `filtre_calcul_fonction_comptes_diff_recettes` (ligne 265)
- `filtre_calcul_fonction_comptes_diff_depenses` (ligne 275)
- `association_comptes_start_year` (ligne 287)
- `filtre_association_comptes_start_year` (ligne 294)
- `stats_compta_activites_exercice` (ligne 312)
- `filtre_stats_compta_activites_exercice` (ligne 377)
- `filtre_association_comptes_bornes_exercice_json` (ligne 385)
- `filtre_association_get_inclure_non_validees` (ligne 400)
- `filtre_association_vu_selon_inclusion` (ligne 425)
- `stats_compta_activites_lister_evenements_exercice` (ligne 444)
- `filtre_stats_compta_activites_lister_evenements_exercice` (ligne 487)

### `inc/fonctions/eligibilite_desinscription_evenement.php`

- `eligibilite_desinscription_evenement` (ligne 12)

### `inc/fonctions/eligibilite_inscription_evenement.php`

- `eligibilite_inscription_evenement` (ligne 16)

### `inc/fonctions/eligibilite_modification_evenement.php`

- `eligibilite_modification_evenement` (ligne 11)

### `inc/fonctions/facteur_envoyer_app.php`

- `facteur_envoyer_app` (ligne 29)

### `inc/fonctions/facteur_envoyer_mail_activites.php`

- `facteur_envoyer_mail_activites` (ligne 12)
- `facteur_envoyer_mail_activite_adherent` (ligne 81)
- `facteur_envoyer_mail_activite_responsable` (ligne 196)
- `_determiner_modeles_emails_adherent` (ligne 330)
- `_determiner_modeles_emails_responsable` (ligne 377)

### `inc/fonctions/facteur_envoyer_notification_gis.php`

- `facteur_envoyer_notification_gis` (ligne 3)

### `inc/fonctions/facteur_envoyer_recu_adhesion.php`

- `facteur_envoyer_recu_adhesion` (ligne 17)

### `inc/fonctions/facteur_envoyer_recu_participation.php`

- `facteur_envoyer_recu_participation` (ligne 4)

### `inc/fonctions/generer_export_csv.php`

- `generer_export_csv_adherents` (ligne 22)

### `inc/fonctions/gestion_places.php`

- `gestions_places` (ligne 6)

### `inc/fonctions/gis_auteur.php`

- `gis_auteur` (ligne 18)

### `inc/fonctions/liste_responsables_evenement.php`

- `liste_responsables_evenement` (ligne 17)

### `inc/fonctions/ouverture_inscription_evenement.php`

- `ouverture_inscription_evenement` (ligne 15)

### `inc/fonctions/priviliges_adherent.php`

- `activer_privileges_adherent` (ligne 18)
- `desactiver_privileges_adherent` (ligne 93)
- `verifier_privileges_adherent` (ligne 170)

### `inc/fonctions/roles_association.php`

- `roles_association` (ligne 16)
- `extraire_valeurs_chaine` (ligne 93)
- `extraire_groupes_chaine` (ligne 118)
- `saisies_extraire_groupes_chaine` (ligne 150)

### `inc/fonctions/validation_attente_automatique.php`

- `validation_attente_automatique` (ligne 33)

### `inc/gis_geocode.php`

- `gis_geocode_request` (ligne 17)
- `gis_geocode_google_format` (ligne 93)

### `inc/navigation_modules.php`

- `association_onglets` (ligne 18)
- `fin_page_association` (ligne 31)

### `inc/notifications_emails.php`

- `association_collecter_destinataires_admins` (ligne 21)

### `inc/page.php`

- `page_cadre_relief` (ligne 9)
- `page_no_cadre_relief` (ligne 13)
- `page_fond` (ligne 17)
- `page_no_fond` (ligne 25)

### `inscriptions_evenement.csv_fonctions.php`

- `lister_label_info_supplementaire` (ligne 22)
- `generer_detail_inscription_accompagnant` (ligne 52)

### `prive/objets/liste/auteurs_fonctions.php`

- `critere_compteur_articles_filtres_dist` (ligne 31)
- `balise_COMPTEUR_ARTICLES_dist` (ligne 71)
- `afficher_initiale` (ligne 87)
- `auteur_lien_messagerie` (ligne 142)

### `prive/objets/liste/table_comptabilite_activites_fonctions.php`

- `table_comptabilite_activites_get_filtre_sens` (ligne 32)
- `table_comptabilite_activites_compter_operations` (ligne 58)
- `table_comptabilite_activites_criteres_sens` (ligne 95)
- `table_comptabilite_activites_stats` (ligne 122)
- `table_comptabilite_activites_montants` (ligne 166)

### `prive/squelettes/contenu/adherents_fonctions.php`

- `association_lire_config_liste` (ligne 13)
- `association_normaliser_config_liste` (ligne 21)
- `association_index_champs_extras` (ligne 67)
- `association_definitions_champs_extras` (ligne 102)
- `filtre_liste_statut_interne_adherents` (ligne 143)
- `filtre_liste_type_adherent` (ligne 154)
- `est_actif_gestion_comptes_secondaires` (ligne 205)
- `filtre_a_type_compte` (ligne 238)
- `gestion_comptes_secondaires_active` (ligne 247)
- `filtre_liste_type_compte` (ligne 255)
- `filtre_has_type_adherent` (ligne 269)
- `filtre_liste_periodes_adherents` (ligne 308)
- `preparer_liste_adherents` (ligne 453)
- `get_recherche_active_resume` (ligne 505)
- `filtre_filtres_effectifs` (ligne 558)
- `filtre_liste_champs_filtres_configures` (ligne 647)
- `filtre_liste_champs_colonnes_configures` (ligne 654)
- `liste_filtres_dynamiques_adherents` (ligne 662)
- `liste_colonnes_dynamiques_adherents` (ligne 686)
- `association_donnees_colonnes_adherent` (ligne 700)
- `association_hydrater_colonnes_dynamiques` (ligne 723)
- `valeur_colonne_dynamique_adherent` (ligne 741)
- `formatter_valeur_colonne_dynamique` (ligne 749)
- `association_champs_triables_fixes` (ligne 759)
- `association_defaut_tri_adherents` (ligne 769)
- `filtre_liste_noms_filtres_dynamiques_dist` (ligne 785)
- `filtre_noms_filtres_dynamiques_dist` (ligne 801)
- `filtre_filtres_dynamiques_actifs_dist` (ligne 805)
- `filtre_url_supprimer_filtres_dynamiques_dist` (ligne 819)
- `filtre_liste_filtres_reset_adherents_dist` (ligne 832)
- `filtre_association_defaut_tri_adherents_dist` (ligne 840)
- `filtre_trier_colonne_dynamique_dist` (ligne 844)
- `association_liste_champs_recherche_avancee_actifs` (ligne 881)
- `association_recherche_avancee_reset_demandee` (ligne 900)
- `filtre_url_reinit_recherche_avancee_dist` (ligne 918)
- `filtre_get_recherche_rapide_active_dist` (ligne 925)
- `association_aplatir_datas_saisies` (ligne 962)

### `prive/squelettes/contenu/analyse_compta_activites_fonctions.php`

- `analyse_compta_activites_stats_exercice` (ligne 32)
- `filtre_analyse_compta_activites_stats_exercice` (ligne 107)
- `analyse_compta_activites_lister_evenements_exercice` (ligne 129)
- `filtre_analyse_compta_activites_lister_evenements_exercice` (ligne 227)
- `analyse_compta_activites_totaux_evenements` (ligne 248)
- `filtre_analyse_compta_activites_totaux_evenements` (ligne 279)
- `analyse_compta_activites_normaliser_type` (ligne 289)
- `filtre_analyse_compta_activites_normaliser_type` (ligne 306)
- `analyse_compta_activites_libelle_type` (ligne 316)
- `filtre_analyse_compta_activites_libelle_type` (ligne 331)
- `analyse_compta_activites_compter_evenements` (ligne 342)
- `filtre_analyse_compta_activites_compter_evenements` (ligne 350)

### `prive/squelettes/contenu/cotisations_fonctions.php`

- `preparer_liste_cotisations` (ligne 17)

### `prive/squelettes/contenu/inc-adherents/bloc_filtres_fonctions.php`

- `filtre_compteur_adherents` (ligne 4)
- `filtre_compte_titulaire` (ligne 59)
- `filtre_compte_secondaire` (ligne 68)

### `prive/squelettes/contenu/notifications_fonctions.php`

- `radio_type_adherent` (ligne 7)
- `exemple_adherent_par_type` (ligne 20)
- `exemple_activite_par_statut` (ligne 36)
- `listes_notifications` (ligne 51)

### `saisies/auteurs.php`

- `auteurs_valeurs_acceptables` (ligne 22)

### `verifier/code_postal.php`

- `verifier_code_postal_dist` (ligne 34)

## Repartition par prefixe

- `association_`: 105
- `autoriser_`: 39
- `formulaires_`: 100
- `filtre_`: 53
- `autres`: 349
