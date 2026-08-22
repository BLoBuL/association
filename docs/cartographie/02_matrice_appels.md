# Matrice des appels vers les fonctions du plugin

Generation: 2026-04-20

Types detectes: `appel_php`, `filtre_template`.
Chaque section ci-dessous liste **toutes** les occurrences detectees statiquement.

## Lecture SPIP des "aucun appel detecte"

- Total sections sans appel statique: **325**.
- Dans ce total: `action_*`=35, `autoriser_*`=29, `formulaires_*`=96, `balise_*`=10, `filtre_*`=42.
- Pour ces familles, l'absence d'appel PHP direct est souvent normale (resolution implicite SPIP).
- Se reporter a `03_appels_dynamiques_spip.md` avant de conclure a une fonction orpheline.

## Par fonction (appels entrants complets)

### `NbJours`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `exec/voir_adherent.php:33` (appel_php, caller: `exec_voir_adherent`) -> `//$nb_jour_differences = NbJours($date_actuelle_ymd, $date_validite);`
  - `genie/association_taches_generales.php:58` (appel_php, caller: `genie_association_taches_generales`) -> `$nb_jour_differences = NbJours($date_actuelle_ymd, $date_validite);`

### `_determiner_modeles_emails_adherent`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/fonctions/facteur_envoyer_mail_activites.php:137` (appel_php, caller: `facteur_envoyer_mail_activite_adherent`) -> `list($sujet_email_adherent, $model_email_adherent) = _determiner_modeles_emails_adherent($type);`

### `_determiner_modeles_emails_responsable`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/fonctions/facteur_envoyer_mail_activites.php:257` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `list($sujet_email_responsable, $model_email_responsable) = _determiner_modeles_emails_responsable($type, $nombre_inscrits);`

### `_get_destination_id_intitule_options`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/inc/destinations.php:126` (appel_php, caller: `association_editeur_destinations`) -> `$liste_destination = _get_destination_id_intitule_options();`

### `_get_map_of_destination_id_montant`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/inc/destinations.php:20` (appel_php, caller: `update_destination_contexte_from_compte`) -> `$dest_id_montant = _get_map_of_destination_id_montant($id_compte);`

### `_migration_est_adherent_actif`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/migrer_asso_comptabilite.php:275` (appel_php, caller: `_migration_generer_justification_cotisation`) -> `$actif = _migration_est_adherent_actif($id_auteur);`

### `_migration_generer_justification_cotisation`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/migrer_asso_comptabilite.php:232` (appel_php, caller: `appliquer_migration_auto`) -> `$justification = _migration_generer_justification_cotisation($id_auteur);`

### `_verifier_montant_destinations`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/inc/destinations.php:66` (appel_php, caller: `verifier_destination_comptable`) -> `if ($err_dest = _verifier_montant_destinations($montant)) {`

### `action_ajouter_activites`

- Aucun appel detecte statiquement.

### `action_ajouter_destinations`

- Aucun appel detecte statiquement.

### `action_ajouter_prets`

- Aucun appel detecte statiquement.

### `action_comptes_ligne`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/action_voir.php:40` (appel_php, caller: `exec_action_voir`) -> `$res = action_comptes_ligne("id_compte=$id_compte");`

### `action_editer_asso_comptes`

- Aucun appel detecte statiquement.

### `action_editer_asso_dons`

- Aucun appel detecte statiquement.

### `action_editer_asso_membres`

- Aucun appel detecte statiquement.

### `action_editer_asso_plan`

- Aucun appel detecte statiquement.

### `action_editer_asso_ressources`

- Aucun appel detecte statiquement.

### `action_editer_asso_ventes`

- Aucun appel detecte statiquement.

### `action_envoyer_email_collectif_activite`

- Aucun appel detecte statiquement.

### `action_envoyer_email_collectif_adherent`

- Aucun appel detecte statiquement.

### `action_envoyer_relances`

- Aucun appel detecte statiquement.

### `action_gerer_activites`

- Aucun appel detecte statiquement.

### `action_gis_geocoder_rechercher_dist`

- Aucun appel detecte statiquement.

### `action_invalider_compte_dist`

- Aucun appel detecte statiquement.

### `action_modifier_activites`

- Aucun appel detecte statiquement.

### `action_modifier_destinations`

- Aucun appel detecte statiquement.

### `action_modifier_prets`

- Aucun appel detecte statiquement.

### `action_modifier_relances`

- Aucun appel detecte statiquement.

### `action_supprimer_adherents`

- Aucun appel detecte statiquement.

### `action_supprimer_categorie_activite_dist`

- Aucun appel detecte statiquement.

### `action_supprimer_categorie_cotisation_dist`

- Aucun appel detecte statiquement.

### `action_supprimer_commande_dist`

- Aucun appel detecte statiquement.

### `action_supprimer_compte_dist`

- Aucun appel detecte statiquement.

### `action_supprimer_destinations`

- Aucun appel detecte statiquement.

### `action_supprimer_dons`

- Aucun appel detecte statiquement.

### `action_supprimer_plans`

- Aucun appel detecte statiquement.

### `action_supprimer_prets`

- Aucun appel detecte statiquement.

### `action_supprimer_ressources`

- Aucun appel detecte statiquement.

### `action_supprimer_ventes`

- Aucun appel detecte statiquement.

### `action_synchroniser_asso_membres`

- Aucun appel detecte statiquement.

### `action_synchroniser_comptabilite_evenement_dist`

- Aucun appel detecte statiquement.

### `action_test_notification_cotisation_dist`

- Aucun appel detecte statiquement.

### `action_test_notification_cotisation_redirect`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `action/test_notification_cotisation.php:16` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `action_test_notification_cotisation_redirect(_request('redirect'), _T('asso:erreur_test_notification_interdit'), false);`
  - `action/test_notification_cotisation.php:272` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `action_test_notification_cotisation_redirect(_request('redirect'), $message ?: _T('asso:erreur_envoi_email'), $ok);`

### `action_traiter_comptes_dist`

- Aucun appel detecte statiquement.

### `action_valider_compte_dist`

- Aucun appel detecte statiquement.

### `activer_adherent`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `inc/cotisations.php:167` (appel_php, caller: `changer_statut_cotisation`) -> `activer_adherent($id_auteur, $reinscription, true, $id_auteur);`
  - `inc/cotisations.php:217` (appel_php, caller: `changer_statut_cotisation`) -> `activer_adherent($id_auteur, $reinscription, true, $id_auteur);`
  - `inc/cotisations.php:987` (appel_php, caller: `activer_adherent`) -> `activer_adherent($id_principal, $reinscription, true, $origin_to_pass);`
  - `inc/cotisations.php:999` (appel_php, caller: `activer_adherent`) -> `activer_adherent($id_secondaire, $reinscription, false, $originateur);`

### `activer_privileges_adherent`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/cotisations.php:969` (appel_php, caller: `activer_adherent`) -> `activer_privileges_adherent($id_auteur, $reinscription);`

### `activite_calculator`

- Aucun appel detecte statiquement.

### `activite_enregistrement_calculator`

- Total occurrences: **13**
- Repartition: `appel_php`=13
- Occurrences:
  - `action/ajouter_activites.php:88` (appel_php, caller: `activites_insert`) -> `$cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, true, '');`
  - `action/ajouter_activites.php:92` (appel_php, caller: `activites_insert`) -> `$cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, true, '');`
  - `action/ajouter_activites.php:94` (appel_php, caller: `activites_insert`) -> `$cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, false, '');`
  - `action/ajouter_activites.php:118` (appel_php, caller: `activites_insert`) -> `$cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, false, '');`
  - `action/modifier_activites.php:87` (appel_php, caller: `action_modifier_activites`) -> `$cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, $montant_payer = true, $id_activite);`
  - `action/modifier_activites.php:91` (appel_php, caller: `action_modifier_activites`) -> `$cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, $montant_payer = true, $id_activite);`
  - `action/modifier_activites.php:93` (appel_php, caller: `action_modifier_activites`) -> `$cal_result =  activite_enregistrement_calculator( $id_evenement, $nombre_inscrits, $valider, $montant_payer = false, $id_activite);`
  - `action/modifier_activites.php:140` (appel_php, caller: `action_modifier_activites`) -> `$cal_result = activite_enregistrement_calculator($id_evenement, $nombre_inscrits, $valider, $montant_payer = false, $id_activite);`
  - `formulaires/inc/inscription_evenement.php:943` (appel_php, caller: `generer_recapitulatif_multi`) -> `$activite_enregistrement_calculator = activite_enregistrement_calculator(`
  - `formulaires/inscription_evenement.php:613` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `$cal_result =  activite_enregistrement_calculator($id_evenement, $data_form['nombre_participants'], '', '', $id_activite);`
  - `formulaires/inscription_evenement_multi.php:522` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `$cal_result =  activite_enregistrement_calculator($id_evenement, $data_form['nombre_participants'], '', '', $id_activite);`
  - `formulaires/inscription_evenement_multi_public.php:516` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$cal_result = activite_enregistrement_calculator($id_evenement, $data_form['nombre_participants'], '', '', $id_activite);`
  - `formulaires/inscription_evenement_public.php:567` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `$cal_result =  activite_enregistrement_calculator($id_evenement, $data_form['nombre_participants'], '', '', $id_activite);`

### `activites_insert`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/ajouter_activites.php:30` (appel_php, caller: `action_ajouter_activites`) -> `activites_insert($categorie_result ,$date, $id_evenement, $id_auteur, $nom_participants, $commentaire, $valider, $gratuit_single, $total_inscrits, $id_activite,$notify_the_members);`

### `adherent_correction_statut`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/adherents_bck.php:31` (appel_php, caller: `exec_adherents`) -> `adherent_correction_statut();`

### `adherents_recherche_avancee_formater_saisie`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `formulaires/inc/adherents_recherche_avancee.php:80` (appel_php, caller: `adherents_recherche_avancee_statut_adhesion_saisie`) -> `$saisies[] = adherents_recherche_avancee_formater_saisie($champs_extra, $liste_champs_table,'statut_adhesion');`
  - `formulaires/inc/adherents_recherche_avancee.php:174` (appel_php, caller: `adherents_recherche_avancee_formater_saisie`) -> `return adherents_recherche_avancee_formater_saisie($saisie, $liste_champs_table,$type_recherche);`
  - `formulaires/inc/adherents_recherche_avancee.php:247` (appel_php, caller: `adherents_recherche_avancee_multicritere_saisie`) -> `$saisies[] = adherents_recherche_avancee_formater_saisie($champs_extra, $liste_champs_table,'multicritere');`

### `adherents_recherche_avancee_multicritere_saisie`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/inc/adherents_recherche_avancee.php:17` (appel_php, caller: `adherents_recherche_avancee_saisies`) -> `$saisies_multicritere_saisie = adherents_recherche_avancee_multicritere_saisie();`

### `adherents_recherche_avancee_saisies`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/adherents_recherche_avancee.php:18` (appel_php, caller: `formulaires_adherents_recherche_avancee_saisies`) -> `$saisies = adherents_recherche_avancee_saisies();`
  - `formulaires/email_collectif_adherent.php:24` (appel_php, caller: `formulaires_email_collectif_adherent_saisies`) -> `$saisies_recherche_avancee = adherents_recherche_avancee_saisies();`

### `adherents_recherche_avancee_statut_adhesion_saisie`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/inc/adherents_recherche_avancee.php:16` (appel_php, caller: `adherents_recherche_avancee_saisies`) -> `$saisies_statut_adhesion_saisie = adherents_recherche_avancee_statut_adhesion_saisie();`

### `affichage_dans_activites`

- Total occurrences: **39**
- Repartition: `appel_php`=35, `filtre_template`=4
- Occurrences:
  - `exec/csv_activites.php:27` (appel_php, caller: `exec_csv_activites`) -> `$config_evenement = affichage_dans_activites($id_evenement);`
  - `formulaires/desinscription_evenement_public.php:6` (appel_php, caller: `formulaires_desinscription_evenement_public_charger_dist`) -> `$affichage_dans_activites =affichage_dans_activites($id_evenement);`
  - `formulaires/desinscription_evenement_public.php:44` (appel_php, caller: `formulaires_desinscription_evenement_public_traiter_dist`) -> `$affichage_dans_activites =affichage_dans_activites($id_evenement);`
  - `formulaires/inc/inscription_evenement.php:31` (appel_php, caller: `preparer_info_auteur`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inc/inscription_evenement.php:144` (appel_php, caller: `preparer_chargement_modification_inscription`) -> `$affichage_dans_activites = affichage_dans_activites($query_activite['id_evenement']);`
  - `formulaires/inc/inscription_evenement.php:257` (appel_php, caller: `preparer_chargement_modification_inscription_multi`) -> `$affichage_dans_activites = affichage_dans_activites($query_activite['id_evenement']);`
  - `formulaires/inscription_evenement.php:47` (appel_php, caller: `formulaires_inscription_evenement_charger_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement.php:434` (appel_php, caller: `formulaires_inscription_evenement_verifier_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement.php:605` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:29` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$affichage_dans_activites =affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:319` (appel_php, caller: `formulaires_inscription_evenement_multi_charger_dist`) -> `$affichage_dans_activites =affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:355` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_1_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:376` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_2_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:436` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_3_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:491` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_4_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:515` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:36` (appel_php, caller: `formulaires_inscription_evenement_multi_public_saisies`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:244` (appel_php, caller: `formulaires_inscription_evenement_multi_public_charger_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:335` (appel_php, caller: `formulaires_inscription_evenement_multi_public_verifier_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:499` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_public.php:24` (appel_php, caller: `formulaires_inscription_evenement_public_charger_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `formulaires/inscription_evenement_public.php:391` (appel_php, caller: `formulaires_inscription_evenement_public_verifier_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement); // Configuration de l'événement.`
  - `formulaires/inscription_evenement_public.php:557` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `genie/association_expiration_auto_evenement.php:82` (appel_php, caller: `genie_association_expiration_auto_evenement_dist`) -> `$affichage_dans_activites  = affichage_dans_activites($id_evenement_concerne);`
  - `inc/fonctions/activite_calculator.php:19` (appel_php, caller: `activite_calculator`) -> `$gestion = affichage_dans_activites($id_evenement);`
  - `inc/fonctions/activite_enregistrement_calculator.php:23` (appel_php, caller: `activite_enregistrement_calculator`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `inc/fonctions/alerte_inscription_evenement.php:10` (appel_php, caller: `alerte_inscription_evenement`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `inc/fonctions/eligibilite_desinscription_evenement.php:22` (appel_php, caller: `eligibilite_desinscription_evenement`) -> `$affichage_dans_activites =affichage_dans_activites($id_evenement);`
  - `inc/fonctions/eligibilite_inscription_evenement.php:33` (appel_php, caller: `eligibilite_inscription_evenement`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement); // Type d'affichage de l'événement`
  - `inc/fonctions/eligibilite_modification_evenement.php:21` (appel_php, caller: `eligibilite_modification_evenement`) -> `$affichage_dans_activites =affichage_dans_activites($id_evenement);`
  - `inc/fonctions/gestion_places.php:10` (appel_php, caller: `gestions_places`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `inc/fonctions/ouverture_inscription_evenement.php:18` (appel_php, caller: `ouverture_inscription_evenement`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `inc/fonctions/validation_attente_automatique.php:49` (appel_php, caller: `validation_attente_automatique`) -> `$affichage_dans_activites  = affichage_dans_activites($id_evenement);`
  - `inscriptions_evenement.csv_fonctions.php:24` (appel_php, caller: `lister_label_info_supplementaire`) -> `$affichage_dans_activites = affichage_dans_activites($id_evenement);`
  - `inscriptions_evenement.csv_fonctions.php:57` (appel_php, caller: `generer_detail_inscription_accompagnant`) -> `$affichage_dans_activites = affichage_dans_activites($query_activite['id_evenement']);`
  - `prive/squelettes/contenu/inc-voir_activites/bloc_configuration.html:15` (filtre_template, caller: `(squelette)`) -> `<!--[ (#ID_EVENEMENT\|affichage_dans_activites\|foreach)]`
  - `prive/squelettes/contenu/inc-voir_activites/bloc_configuration.html:53` (filtre_template, caller: `(squelette)`) -> `(#ID_EVENEMENT\|affichage_dans_activites\|table_valeur{payant}\|oui)`
  - `prive/squelettes/contenu/lightbox_lien_inscription_vip.html:5` (filtre_template, caller: `(squelette)`) -> `[(#SET{token,#ID_EVENEMENT\|affichage_dans_activites\|table_valeur{token}})]`
  - `prive/squelettes/contenu/voir_activites.html:10` (filtre_template, caller: `(squelette)`) -> `#SET{affichage_dans_activites, #ID_EVENEMENT\|affichage_dans_activites}`

### `afficher_initiale`

- Total occurrences: **6**
- Repartition: `filtre_template`=6
- Occurrences:
  - `prive/objets/liste/auteurs.html:22` (filtre_template, caller: `(squelette)`) -> `#SELF\|parametre_url{debutaut,@#ID_AUTEUR}\|ancre_url{paginationaut}\|afficher_initiale{#NOM_FAMILLE**\|initiale{},#COMPTEUR_BOUCLE,#GET{debut},#ENV{nb,10}}`
  - `prive/objets/liste/auteurs.html:27` (filtre_template, caller: `(squelette)`) -> `#REM\|afficher_initiale{#REM,#TOTAL_BOUCLE,#GET{debut},#ENV{nb,10}}`
  - `prive/objets/liste/auteurs_associer.html:34` (filtre_template, caller: `(squelette)`) -> `#SELF\|parametre_url{debutauta,@#ID_AUTEUR}\|ancre_url{paginationauta}\|afficher_initiale{#NOM_FAMILLE**\|initiale{},#COMPTEUR_BOUCLE,#GET{debut},#ENV{nb,10}}`
  - `prive/objets/liste/auteurs_associer.html:39` (filtre_template, caller: `(squelette)`) -> `#REM\|afficher_initiale{#REM,#TOTAL_BOUCLE,#GET{debut},#ENV{nb,10}}`
  - `prive/objets/liste/visiteurs.html:21` (filtre_template, caller: `(squelette)`) -> `#SELF\|parametre_url{debutaut,@#ID_AUTEUR}\|ancre_url{paginationaut}\|afficher_initiale{#NOM_FAMILLE**\|initiale{},#COMPTEUR_BOUCLE,#GET{debut},#ENV{nb,10}}`
  - `prive/objets/liste/visiteurs.html:26` (filtre_template, caller: `(squelette)`) -> `#REM\|afficher_initiale{#REM,#TOTAL_BOUCLE,#GET{debut},#ENV{nb,10}}`

### `afficher_resultat_recherche_avancee`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `exec/adherents_bck.php:59` (appel_php, caller: `exec_adherents`) -> `$id_auteurs = afficher_resultat_recherche_avancee($_REQUEST);`
  - `formulaires/email_collectif_adherent.php:286` (appel_php, caller: `formulaires_email_collectif_adherent_charger_dist`) -> `$liste_auteurs = afficher_resultat_recherche_avancee($_POST);`

### `ajouter_destinations`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `inc/comptes.php:147` (appel_php, caller: `inserer_compte`) -> `//ajouter_destinations($id_compte, $recette, $depense, $destination_map);`
  - `inc/comptes.php:241` (appel_php, caller: `modifier_compte`) -> `//ajouter_destinations($id_compte, $recette, $depense, $destination_map); // Code commenté.`

### `alerte_inscription_evenement`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `formulaires/inscription_evenement_multi.php:54` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$alerte_inscription_evenement = alerte_inscription_evenement($id_evenement,$id_activite);`
  - `formulaires/inscription_evenement_multi.php:332` (appel_php, caller: `formulaires_inscription_evenement_multi_charger_dist`) -> `$alerte_inscription_evenement = alerte_inscription_evenement($id_evenement,$id_activite);`
  - `formulaires/inscription_evenement_multi_public.php:285` (appel_php, caller: `formulaires_inscription_evenement_multi_public_charger_dist`) -> `$alerte_inscription_evenement = alerte_inscription_evenement($id_evenement, $id_activite);`
  - `formulaires/inscription_evenement_public.php:71` (appel_php, caller: `formulaires_inscription_evenement_public_charger_dist`) -> `$alerte_inscription_evenement = alerte_inscription_evenement($id_evenement,$id_activite);`

### `analyse_compta_activites_compter_evenements`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:351` (appel_php, caller: `filtre_analyse_compta_activites_compter_evenements`) -> `return analyse_compta_activites_compter_evenements($exercice, $type, $vu);`

### `analyse_compta_activites_libelle_type`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:332` (appel_php, caller: `filtre_analyse_compta_activites_libelle_type`) -> `return analyse_compta_activites_libelle_type($type);`

### `analyse_compta_activites_lister_evenements_exercice`

- Total occurrences: **4**
- Repartition: `appel_php`=3, `filtre_template`=1
- Occurrences:
  - `prive/squelettes/contenu/analyse_compta_activites.html:85` (filtre_template, caller: `(squelette)`) -> `[(#SET{events,#GET{exercice}\|analyse_compta_activites_lister_evenements_exercice{#GET{type},#GET{vu_effectif}}})]`
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:228` (appel_php, caller: `filtre_analyse_compta_activites_lister_evenements_exercice`) -> `return analyse_compta_activites_lister_evenements_exercice($exercice, $type, $vu);`
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:240` (appel_php, caller: `filtre_analyse_compta_activites_lister_evenements_exercice`) -> `* @param array $evenements Tableau d'événements retourné par analyse_compta_activites_lister_evenements_exercice()`
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:343` (appel_php, caller: `analyse_compta_activites_compter_evenements`) -> `$evenements = analyse_compta_activites_lister_evenements_exercice($exercice, $type, $vu);`

### `analyse_compta_activites_normaliser_type`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:307` (appel_php, caller: `filtre_analyse_compta_activites_normaliser_type`) -> `return analyse_compta_activites_normaliser_type($type);`

### `analyse_compta_activites_stats_exercice`

- Total occurrences: **2**
- Repartition: `appel_php`=1, `filtre_template`=1
- Occurrences:
  - `prive/squelettes/contenu/analyse_compta_activites.html:44` (filtre_template, caller: `(squelette)`) -> `[(#SET{stats,#GET{exercice}\|analyse_compta_activites_stats_exercice{#GET{type},#GET{vu_effectif}}})]`
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:108` (appel_php, caller: `filtre_analyse_compta_activites_stats_exercice`) -> `return analyse_compta_activites_stats_exercice($exercice, $type, $vu);`

### `analyse_compta_activites_totaux_evenements`

- Total occurrences: **2**
- Repartition: `appel_php`=1, `filtre_template`=1
- Occurrences:
  - `prive/squelettes/contenu/analyse_compta_activites.html:86` (filtre_template, caller: `(squelette)`) -> `[(#SET{totaux,#GET{events}\|analyse_compta_activites_totaux_evenements})]`
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:280` (appel_php, caller: `filtre_analyse_compta_activites_totaux_evenements`) -> `return analyse_compta_activites_totaux_evenements($evenements);`

### `api_cotisations_saisies_communes`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/editer_asso_cotisation.php:66` (appel_php, caller: `formulaires_editer_asso_cotisation_saisies`) -> `$saisies = api_cotisations_saisies_communes($params);`

### `api_traiter_cotisation`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `formulaires/editer_asso_cotisation.php:420` (appel_php, caller: `formulaires_editer_asso_cotisation_traiter`) -> `// par api_traiter_cotisation() - ne pas dupliquer ici. Si un statut explicite est fourni`
  - `formulaires/editer_asso_cotisation.php:423` (appel_php, caller: `formulaires_editer_asso_cotisation_traiter`) -> `$resultat = api_traiter_cotisation($params);`
  - `inc/cotisations.php:88` (appel_php, caller: `mise_a_jour_cotisation`) -> `api_traiter_cotisation(`

### `appliquer_migration_auto`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/migrer_asso_comptabilite.php:145` (appel_php, caller: `formulaires_migrer_asso_comptabilite_traiter_dist`) -> `appliquer_migration_auto($cfg);`

### `appliquer_migration_manuelle`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/migrer_asso_comptabilite.php:128` (appel_php, caller: `formulaires_migrer_asso_comptabilite_traiter_dist`) -> `appliquer_migration_manuelle($imputations_existantes, $pc_cotisations_creance, $pc_cotisations_paiement);`

### `asso_anonymiser_auteurs`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:179` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['anonymiser_auteurs'] = asso_anonymiser_auteurs($avec_paiements, $opt['dry_run']);`

### `asso_anonymiser_inscriptions_auteurs`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:202` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['anonymiser_inscriptions_inactifs'] = asso_anonymiser_inscriptions_auteurs($inactifs, $opt['dry_run']);`

### `asso_recuperer_auteurs_inactifs`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:146` (appel_php, caller: `association_maintenance_bdd_run`) -> `$inactifs = asso_recuperer_auteurs_inactifs($limite_inactifs, $opt['lot']);`

### `asso_separer_auteurs_par_encaissements`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:154` (appel_php, caller: `association_maintenance_bdd_run`) -> `[$sans_paiements, $avec_paiements] = asso_separer_auteurs_par_encaissements($inactifs);`

### `asso_supprimer_auteurs`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:165` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_auteurs'] = asso_supprimer_auteurs($sans_paiements, $opt['dry_run']);`

### `asso_supprimer_comptes_auteurs`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:164` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_comptes_auteurs'] = asso_supprimer_comptes_auteurs($sans_paiements, $opt['dry_run']);`

### `asso_supprimer_cotisations_non_encaissees_anciennes`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/migrer_asso_comptabilite.php:139` (appel_php, caller: `formulaires_migrer_asso_comptabilite_traiter_dist`) -> `asso_supprimer_cotisations_non_encaissees_anciennes(time(), 6, false, $lot_max);`
  - `genie/association_maintenance_bdd.php:216` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_cotisations_non_encaissees_anciennes'] = asso_supprimer_cotisations_non_encaissees_anciennes($maintenant, $opt['mois_non_encaisse'], $opt['dry_run'], $opt['lot']);`

### `asso_supprimer_cotisations_orphelines`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/migrer_asso_comptabilite.php:137` (appel_php, caller: `formulaires_migrer_asso_comptabilite_traiter_dist`) -> `asso_supprimer_cotisations_orphelines(false, $lot_max);`
  - `genie/association_maintenance_bdd.php:210` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_cotisations_orphelines'] = asso_supprimer_cotisations_orphelines($opt['dry_run'], $opt['lot']);`

### `asso_supprimer_inscriptions_par_ids`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:194` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_inscriptions_non_validees'] = asso_supprimer_inscriptions_par_ids(array_column($anciennes_non_validees, 'id_activite'), $opt['dry_run']);`

### `asso_supprimer_mailsubscribers_orphelines`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:253` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_mailsubscribers_orphelines'] = asso_supprimer_mailsubscribers_orphelines($opt['dry_run'], $opt['lot']);`

### `asso_supprimer_mailsubscribers_pour_auteurs`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:162` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_mailsubscribers'] = asso_supprimer_mailsubscribers_pour_auteurs($sans_paiements, $opt['dry_run']);`

### `asso_supprimer_participations_evenements_obsoletes`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:234` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_participations_evenements_obsoletes'] = asso_supprimer_participations_evenements_obsoletes($opt['dry_run'], $opt['lot'], $opt['jours_inscriptions_en_attente']);`

### `asso_supprimer_participations_evenements_orphelines`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:228` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_participations_evenements_orphelines'] = asso_supprimer_participations_evenements_orphelines($opt['dry_run'], $opt['lot']);`

### `asso_supprimer_transactions_auteurs`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:163` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_transactions_auteurs'] = asso_supprimer_transactions_auteurs($sans_paiements, $opt['dry_run']);`

### `asso_supprimer_transactions_inscriptions`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:193` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_transactions_inscriptions'] = asso_supprimer_transactions_inscriptions($anciennes_non_validees, $opt['dry_run']);`

### `asso_supprimer_transactions_orphelines`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/migrer_asso_comptabilite.php:141` (appel_php, caller: `formulaires_migrer_asso_comptabilite_traiter_dist`) -> `asso_supprimer_transactions_orphelines(false, $lot_max);`
  - `genie/association_maintenance_bdd.php:222` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_transactions_orphelines'] = asso_supprimer_transactions_orphelines($opt['dry_run'], $opt['lot']);`

### `asso_supprimer_urls_obsoletes`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:246` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_urls_obsoletes'] = asso_supprimer_urls_obsoletes($opt['dry_run'], 10000);`

### `asso_supprimer_urls_par_type`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:241` (appel_php, caller: `association_maintenance_bdd_run`) -> `$resume['supprimer_urls_mailsubscriber'] = asso_supprimer_urls_par_type('mailsubscriber', $opt['dry_run'], 10000);`

### `asso_table_col_for_type`

- Aucun appel detecte statiquement.

### `asso_trouver_inscriptions_non_validees_anciennes`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_maintenance_bdd.php:187` (appel_php, caller: `association_maintenance_bdd_run`) -> `$anciennes_non_validees = asso_trouver_inscriptions_non_validees_anciennes($limite_inscriptions, $opt['lot']);`

### `association_afficher_contenu_objet`

- Aucun appel detecte statiquement.

### `association_ajouter_destinations_comptables`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `inc/association_comptabilite.php:136` (appel_php, caller: `association_ajouter_operation_comptable`) -> `association_ajouter_destinations_comptables($id_compte, $recette, $depense);`
  - `inc/association_comptabilite.php:150` (appel_php, caller: `association_modifier_operation_comptable`) -> `association_ajouter_destinations_comptables($id_compte, $recette, 0);`
  - `inc/association_comptabilite.php:181` (appel_php, caller: `association_modifier_cotisation`) -> `$err = association_ajouter_destinations_comptables($id_compte, $recette, $depense);`

### `association_ajouter_menus`

- Aucun appel detecte statiquement.

### `association_ajouter_operation_comptable`

- Aucun appel detecte statiquement.

### `association_aplatir_datas_saisies`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:129` (appel_php, caller: `association_definitions_champs_extras`) -> `$options = association_aplatir_datas_saisies($options);`

### `association_autoriser`

- Aucun appel detecte statiquement.

### `association_bank_redirige_apres_retour_transaction`

- Aucun appel detecte statiquement.

### `association_bouton`

- Total occurrences: **12**
- Repartition: `appel_php`=12
- Occurrences:
  - `exec/destinations.php:85` (appel_php, caller: `create_destination_row`) -> `. "<td style='text-align: center;'>" . association_bouton(_T('asso:mettre_a_jour'), 'edit-12.gif', 'edit_destination', 'id='.$id, $onload_option) . "</td>"`
  - `exec/destinations.php:86` (appel_php, caller: `create_destination_row`) -> `. "<td style='text-align: center;'>" . association_bouton(_T('asso:supprimer'), 'poubelle.gif', 'action_destinations', 'id='.$id) . "</td>"`
  - `exec/dons.php:90` (appel_php, caller: `exec_dons`) -> `echo '<td  class="arial11 border1" style="text-align:center;">' . association_bouton(_T('asso:supprimer_le_don'), 'poubelle-12.gif', 'action_dons', "id=$id_don") . "</td>\n";`
  - `exec/dons.php:91` (appel_php, caller: `exec_dons`) -> `echo '<td class="arial11 border1" style="text-align:center;">' . association_bouton(_T('asso:mettre_a_jour_le_don'), 'edit-12.gif', 'edit_don', "id=$id_don") . "</td>\n";;`
  - `exec/plan_comptable.php:115` (appel_php, caller: `cadre_relief`) -> `echo '<td class="" style="text-align:center;">'. association_bouton(_T('asso:supprimer'), 'poubelle-12.gif', 'action_plan', 'id='.$data['id_plan'], $onload_option) . '</td>';`
  - `exec/plan_comptable.php:117` (appel_php, caller: `cadre_relief`) -> `echo '<td class="" style="text-align:center;">'. association_bouton(_T('asso:modifier'), 'edit-12.gif', 'edit_plan', 'id='.$data['id_plan'], $onload_option) . '</td>';`
  - `exec/prets.php:88` (appel_php, caller: `exec_prets`) -> `echo '<td class="arial11 border1" style="text-align:center;">'. association_bouton(_T('asso:prets_nav_annuler'), 'poubelle-12.gif', 'action_prets', 'id_pret='.$data['id_pret'].'&id_ressource='.$id_ressource) . "</td>\n";`
  - `exec/prets.php:89` (appel_php, caller: `exec_prets`) -> `echo '<td class="arial11 border1" style="text-align:center;">' . association_bouton(_T('asso:prets_nav_editer'), 'edit-12.gif', 'edit_pret', 'id_old='.$data['id_pret']) . "</td>\n";`
  - `exec/ressources.php:76` (appel_php, caller: `cadre_relief`) -> `echo '<td class="arial11 border1">', association_bouton(_T('asso:prets_nav_gerer'), 'voir-12.png', 'prets', 'id='.$data['id_ressource']), "</td>\n";`
  - `exec/ressources.php:78` (appel_php, caller: `cadre_relief`) -> `echo '<td class="arial11 border1" style="text-align:center;">', association_bouton(_T('asso:ressources_nav_supprimer'), 'poubelle-12.gif', 'action_ressources', 'id='.$data['id_ressource']), "</td>\n";`
  - `exec/ressources.php:79` (appel_php, caller: `cadre_relief`) -> `echo '<td class="arial11 border1" style="text-align:center;">', association_bouton(_T('asso:ressources_nav_editer'), 'edit-12.gif', 'edit_ressource', 'id='.$data['id_ressource']), "</td>\n";`
  - `exec/ventes.php:99` (appel_php, caller: `exec_ventes`) -> `. association_bouton(_T('asso:mettre_a_jour_la_vente'), 'edit-12.gif', 'edit_vente',"id=$id") . '</td>'`

### `association_bouton_ecrire_fa`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_options.php:149` (appel_php, caller: `association_bouton`) -> `return association_bouton_ecrire_fa($texte,$fa_class,$script,$args);`

### `association_bouton_public_fa`

- Aucun appel detecte statiquement.

### `association_calculer_nom_membre`

- Total occurrences: **3**
- Repartition: `appel_php`=2, `filtre_template`=1
- Occurrences:
  - `action/editer_asso_dons.php:30` (appel_php, caller: `action_editer_asso_dons`) -> `$bienfaiteur = association_calculer_nom_membre($data['sexe'], $data['prenom'], $data['nom_famille']);`
  - `exec/action_adherents.php:54` (appel_php, caller: `supprimer_adherents`) -> `$res .="\n<tr><td>" . $data['id_auteur'] . " <strong>".association_calculer_nom_membre($data['sexe'], $data['prenom'], $data['nom_famille']).'</strong></td><td><input type="checkbox" name="drop[]" value="'.$id.'" checked`
  - `modeles/asso_membres_responsables.php:20` (filtre_template, caller: `(global)`) -> `<td><a class="spip_in fn" title="<:asso:adherent_label_modifier_visiteur:>" href="[(#ID_AUTEUR\|generer_url_entite{auteur})]">[(#SEXE\|association_calculer_nom_membre{#PRENOM, #NOM_FAMILLE})]</a></td>`

### `association_champs_accompagnants`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:31` (appel_php, caller: `association_declarer_champs_extras_impl`) -> `$champs = association_champs_accompagnants($champs);`

### `association_champs_attente`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:34` (appel_php, caller: `association_declarer_champs_extras_impl`) -> `$champs = association_champs_attente($champs);`

### `association_champs_communication_fiafe`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:47` (appel_php, caller: `association_declarer_champs_extras_impl`) -> `$champs = association_champs_communication_fiafe($champs);`

### `association_champs_conditions_inscription`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:37` (appel_php, caller: `association_declarer_champs_extras_impl`) -> `$champs = association_champs_conditions_inscription($champs, $donnees_communes);`

### `association_champs_evenement_base`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:19` (appel_php, caller: `association_declarer_champs_extras_impl`) -> `$champs = association_champs_evenement_base($champs, $donnees_communes);`

### `association_champs_info_supplementaire`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:42` (appel_php, caller: `association_declarer_champs_extras_impl`) -> `$champs = association_champs_info_supplementaire($champs);`

### `association_champs_inscription`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:22` (appel_php, caller: `association_declarer_champs_extras_impl`) -> `$champs = association_champs_inscription($champs, $donnees_communes);`

### `association_champs_paiement`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:28` (appel_php, caller: `association_declarer_champs_extras_impl`) -> `$champs = association_champs_paiement($champs, $donnees_communes);`

### `association_champs_triables_fixes`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:855` (appel_php, caller: `filtre_trier_colonne_dynamique_dist`) -> `$colonnes_triables = association_champs_triables_fixes();`

### `association_champs_validation`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:25` (appel_php, caller: `association_declarer_champs_extras_impl`) -> `$champs = association_champs_validation($champs);`

### `association_collecter_destinataires_admins`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_pipelines.php:531` (appel_php, caller: `association_notifications_destinataires`) -> `$dests = association_collecter_destinataires_admins();`

### `association_comparateur_date`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `genie/association_taches_generales.php:74` (appel_php, caller: `genie_association_taches_generales`) -> `$date_compare = association_comparateur_date('', $auteur['validite'], '>');`
  - `inc/fonctions/gestion_places.php:159` (appel_php, caller: `gestions_places`) -> `$result['evenement_date_ouverture_passed'] = association_comparateur_date('', $result['evenement_date_ouverture'], '>=');`

### `association_comptes_bornes_exercice`

- Total occurrences: **11**
- Repartition: `appel_php`=10, `filtre_template`=1
- Occurrences:
  - `inc/fonctions/comptes.php:61` (appel_php, caller: `filtre_calcul_fonction_comptes_calculer_totaux`) -> `$bornes = association_comptes_bornes_exercice($ex_int);`
  - `inc/fonctions/comptes.php:167` (appel_php, caller: `filtre_calcul_fonction_comptes_lister_operations`) -> `$bornes = association_comptes_bornes_exercice(intval($exercice));`
  - `inc/fonctions/comptes.php:180` (appel_php, caller: `filtre_calcul_fonction_comptes_compter_operations`) -> `$bornes = association_comptes_bornes_exercice(intval($exercice));`
  - `inc/fonctions/comptes.php:203` (appel_php, caller: `filtre_calcul_fonction_comptes_compter_recettes`) -> `$bornes = association_comptes_bornes_exercice(intval($exercice));`
  - `inc/fonctions/comptes.php:226` (appel_php, caller: `filtre_calcul_fonction_comptes_compter_depenses`) -> `$bornes = association_comptes_bornes_exercice(intval($exercice));`
  - `inc/fonctions/comptes.php:323` (appel_php, caller: `stats_compta_activites_exercice`) -> `$bornes = association_comptes_bornes_exercice($exercice);`
  - `inc/fonctions/comptes.php:390` (appel_php, caller: `filtre_association_comptes_bornes_exercice_json`) -> `$bornes = association_comptes_bornes_exercice($ex_int);`
  - `inc/fonctions/comptes.php:446` (appel_php, caller: `stats_compta_activites_lister_evenements_exercice`) -> `$bornes = association_comptes_bornes_exercice($exercice);`
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:43` (appel_php, caller: `analyse_compta_activites_stats_exercice`) -> `$bornes = association_comptes_bornes_exercice($exercice);`
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:131` (appel_php, caller: `analyse_compta_activites_lister_evenements_exercice`) -> `$bornes = association_comptes_bornes_exercice($exercice);`
  - `prive/squelettes/contenu/export_activites_compta.html:22` (filtre_template, caller: `(squelette)`) -> `[(#SET{bornes,#GET{exercice}\|association_comptes_bornes_exercice})]`

### `association_comptes_start_year`

- Total occurrences: **4**
- Repartition: `filtre_template`=4
- Occurrences:
  - `prive/squelettes/contenu/comptes.html:3` (filtre_template, caller: `(squelette)`) -> `[(#SET{exercice, #ENV{exercice}\|sinon{#ENV{annee}\|sinon{#DATE\|association_comptes_start_year}}})]`
  - `prive/squelettes/navigation/comptes.html:1` (filtre_template, caller: `(squelette)`) -> `[(#SET{exercice_env, #ENV{exercice}\|sinon{#ENV{annee}\|sinon{#DATE\|association_comptes_start_year}}})]`
  - `prive/squelettes/navigation/comptes.html:10` (filtre_template, caller: `(squelette)`) -> `[(#SET{totaux, [(#ENV{imputation,%}\|calcul_fonction_comptes_calculer_totaux{#ENV{exercice}\|sinon{#ENV{annee}\|sinon{#DATE\|association_comptes_start_year}}, #ENV{vu}})]})]`
  - `prive/squelettes/navigation/comptes.html:11` (filtre_template, caller: `(squelette)`) -> `[(#SET{totaux_env,[(#ENV{imputation,%}\|calcul_fonction_comptes_calculer_totaux{#ENV{exercice}\|sinon{#ENV{annee}\|sinon{#DATE\|association_comptes_start_year}},#ENV{vu}})]})]`

### `association_comptes_start_year_from_date`

- Total occurrences: **6**
- Repartition: `appel_php`=6
- Occurrences:
  - `inc/fonctions/comptes.php:59` (appel_php, caller: `filtre_calcul_fonction_comptes_calculer_totaux`) -> `$ex_int = association_comptes_start_year_from_date(date('Y-m-d'));`
  - `inc/fonctions/comptes.php:152` (appel_php, caller: `filtre_calcul_fonction_comptes_lister_exercices`) -> `$min_start = association_comptes_start_year_from_date($mm['mind']);`
  - `inc/fonctions/comptes.php:153` (appel_php, caller: `filtre_calcul_fonction_comptes_lister_exercices`) -> `$max_start = association_comptes_start_year_from_date($mm['maxd']);`
  - `inc/fonctions/comptes.php:288` (appel_php, caller: `association_comptes_start_year`) -> `return association_comptes_start_year_from_date($date_sql);`
  - `inc/fonctions/comptes.php:295` (appel_php, caller: `filtre_association_comptes_start_year`) -> `return association_comptes_start_year_from_date($date_sql);`
  - `inc/fonctions/comptes.php:388` (appel_php, caller: `filtre_association_comptes_bornes_exercice_json`) -> `$ex_int = association_comptes_start_year_from_date(date('Y-m-d'));`

### `association_config_champs_colonnes_triables`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/inc/adherents_recherche_avancee.php:536` (appel_php, caller: `generer_array_adherents`) -> `$champs_dyn_config = association_config_champs_colonnes_triables();`

### `association_date_du_jour`

- Total occurrences: **24**
- Repartition: `appel_php`=24
- Occurrences:
  - `exec/action_activites.php:39` (appel_php, caller: `exec_action_activites`) -> `echo association_date_du_jour();`
  - `exec/action_adherents.php:35` (appel_php, caller: `exec_action_adherents_args`) -> `echo association_date_du_jour();`
  - `exec/action_comptes.php:36` (appel_php, caller: `exec_action_comptes_args`) -> `echo association_date_du_jour();`
  - `exec/action_email_collectif_activite.php:39` (appel_php, caller: `exec_action_email_collectif_activite`) -> `echo association_date_du_jour();`
  - `exec/action_email_collectif_adherent.php:28` (appel_php, caller: `exec_action_email_collectif_adherent`) -> `echo association_date_du_jour();`
  - `exec/action_email_relances.php:28` (appel_php, caller: `exec_action_email_relances`) -> `echo association_date_du_jour();`
  - `exec/action_prets.php:44` (appel_php, caller: `exec_action_prets`) -> `echo association_date_du_jour();`
  - `exec/action_relances.php:44` (appel_php, caller: `exec_action_relances`) -> `echo association_date_du_jour();`
  - `exec/action_ventes.php:34` (appel_php, caller: `exec_action_ventes`) -> `echo association_date_du_jour();`
  - `exec/action_voir.php:30` (appel_php, caller: `exec_action_voir`) -> `echo association_date_du_jour();`
  - `exec/activites.php:40` (appel_php, caller: `exec_activites`) -> `//echo association_date_du_jour();`
  - `exec/bilan.php:51` (appel_php, caller: `exec_bilan`) -> `echo association_date_du_jour();`
  - `exec/configurer_visuel.php:28` (appel_php, caller: `exec_configurer_visuel`) -> `echo association_date_du_jour();`
  - `exec/dons.php:32` (appel_php, caller: `exec_dons`) -> `echo association_date_du_jour();`
  - `exec/edit_cotisation.php:44` (appel_php, caller: `exec_edit_cotisation`) -> `echo association_date_du_jour();`
  - `exec/edit_don.php:41` (appel_php, caller: `exec_edit_don`) -> `echo association_date_du_jour();`
  - `exec/edit_email_collectif_activite.php:26` (appel_php, caller: `exec_edit_email_collectif_activite`) -> `echo association_date_du_jour();`
  - `exec/edit_email_collectif_adherent.php:24` (appel_php, caller: `exec_edit_email_collectif_adherent`) -> `echo association_date_du_jour();`
  - `exec/edit_labels.php:37` (appel_php, caller: `exec_edit_labels`) -> `echo association_date_du_jour();`
  - `exec/edit_mail.php:25` (appel_php, caller: `exec_edit_mail`) -> `echo association_date_du_jour();`
  - `exec/edit_relances.php:33` (appel_php, caller: `exec_edit_relances`) -> `echo association_date_du_jour();`
  - `exec/edit_vente.php:40` (appel_php, caller: `exec_edit_vente`) -> `echo '<div>'.association_date_du_jour().'</div>';`
  - `exec/ventes.php:39` (appel_php, caller: `exec_ventes`) -> `echo association_date_du_jour();`
  - `inc/page.php:33` (appel_php, caller: `page_no_fond`) -> `echo association_date_du_jour();`

### `association_datefr`

- Total occurrences: **15**
- Repartition: `appel_php`=15
- Occurrences:
  - `exec/action_voir.php:56` (appel_php, caller: `action_comptes_ligne`) -> `. '<td><strong>'.association_datefr($data['date']).'</strong></td>'`
  - `exec/bilan.php:187` (appel_php, caller: `bilan_encaisse`) -> `echo "\n<td class='arial11 border1' style='text-align:right;'>".association_datefr($date_solde).'</td>';`
  - `exec/dons.php:81` (appel_php, caller: `exec_dons`) -> `echo '<td class="arial11 border1">'.association_datefr($data['date_don'])."</td>\n";`
  - `exec/edit_pret.php:45` (appel_php, caller: `exec_edit_pret`) -> `$date_retour=association_datefr($data['date_retour']);`
  - `exec/edit_pret.php:46` (appel_php, caller: `exec_edit_pret`) -> `$date_sortie=association_datefr($data['date_sortie']);`
  - `exec/edit_relances.php:154` (appel_php, caller: `relances_while`) -> `.'<td class="$class">'.association_datefr($data['validite'])."</td>\n"`
  - `exec/plan_comptable.php:113` (appel_php, caller: `cadre_relief`) -> `echo '<td class="">'.association_datefr($data['date_anterieure']).'</td>';`
  - `exec/prets.php:79` (appel_php, caller: `exec_prets`) -> `echo '<td class="arial11 border1" style="text-align:right">'.association_datefr($data['date_sortie']).'</td>';`
  - `exec/prets.php:86` (appel_php, caller: `exec_prets`) -> `if ($data['date_retour']==0) { echo '&nbsp';} else {echo association_datefr($data['date_retour']);}`
  - `exec/ventes.php:90` (appel_php, caller: `exec_ventes`) -> `. association_datefr($data['date_vente']).'</td>'`
  - `inc/fonctions/activite_calculator.php:46` (appel_php, caller: `activite_calculator`) -> `$contexte['date_inscription'] = association_datefr($query_activite['date']);`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:95` (appel_php, caller: `facteur_envoyer_mail_activite_adherent`) -> `$date_ev = association_datefr($date_evenement);`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:220` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `$date_ev = association_datefr($evenement['date_debut']);`
  - `inc/fonctions/gestion_places.php:151` (appel_php, caller: `gestions_places`) -> `$result['evenement_date_debut'] = association_datefr($query_evenement['date_debut']);`
  - `inc/fonctions/gestion_places.php:153` (appel_php, caller: `gestions_places`) -> `$result['evenement_date_fin'] = association_datefr($query_evenement['date_fin']);`

### `association_debug_log`

- Total occurrences: **33**
- Repartition: `appel_php`=33
- Occurrences:
  - `association_autoriser.php:313` (appel_php, caller: `autoriser_comptes_dist`) -> `association_debug_log('autoriser_comptes entry id=' . intval($id) . ' qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');`
  - `association_autoriser.php:317` (appel_php, caller: `autoriser_comptes_dist`) -> `association_debug_log('autoriser_comptes allow admin', 'association_autorisation');`
  - `association_autoriser.php:337` (appel_php, caller: `autoriser_comptes_dist`) -> `association_debug_log('autoriser_comptes allow responsable evenement', 'association_autorisation');`
  - `association_autoriser.php:358` (appel_php, caller: `autoriser_asso_comptes_creer_dist`) -> `association_debug_log('autoriser_asso_comptes_creer entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true) . ' opt=' . var_export($opt, true), 'association_autorisation');`
  - `association_autoriser.php:367` (appel_php, caller: `autoriser_asso_comptes_creer_dist`) -> `association_debug_log('autoriser_asso_comptes_creer allow responsable evenement', 'association_autorisation');`
  - `association_autoriser.php:377` (appel_php, caller: `autorite_autoriser_auteurs_menu`) -> `association_debug_log('autorite_autoriser_auteurs_menu entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');`
  - `association_autoriser.php:393` (appel_php, caller: `autorite_autoriser_auteur_voir`) -> `association_debug_log('autorite_autoriser_auteur_voir entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');`
  - `association_autoriser.php:405` (appel_php, caller: `autoriser_asso_modifier`) -> `association_debug_log('autoriser_asso_modifier entry id=' . intval($id) . ' qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');`
  - `association_autoriser.php:409` (appel_php, caller: `autoriser_asso_modifier`) -> `association_debug_log('autoriser_asso_modifier allow admin', 'association_autorisation');`
  - `association_autoriser.php:451` (appel_php, caller: `autoriser_asso_modifier`) -> `association_debug_log('autoriser_asso_modifier allow account_ids owned', 'association_autorisation');`
  - `association_autoriser.php:465` (appel_php, caller: `autoriser_asso_modifier`) -> `association_debug_log('autoriser_asso_modifier allow complet', 'association_autorisation');`
  - `association_autoriser.php:469` (appel_php, caller: `autoriser_asso_modifier`) -> `association_debug_log('autoriser_asso_modifier allow restreint', 'association_autorisation');`
  - `association_autoriser.php:482` (appel_php, caller: `autoriser_modifier_asso`) -> `association_debug_log('autoriser_modifier_asso wrapper called for id=' . intval($id) . ' type=' . var_export($type, true), 'association_autorisation');`
  - `association_autoriser.php:484` (appel_php, caller: `autoriser_modifier_asso`) -> `association_debug_log('autoriser_modifier_asso wrapper result=' . (int)$res, 'association_autorisation');`
  - `association_autoriser.php:490` (appel_php, caller: `autoriser_modifier_asso_dist`) -> `association_debug_log('autoriser_modifier_asso_dist wrapper called for id=' . intval($id) . ' type=' . var_export($type, true), 'association_autorisation');`
  - `association_autoriser.php:492` (appel_php, caller: `autoriser_modifier_asso_dist`) -> `association_debug_log('autoriser_modifier_asso_dist wrapper result=' . (int)$res, 'association_autorisation');`
  - `association_autoriser.php:505` (appel_php, caller: `autoriser_modifier_asso_compte_dist`) -> `association_debug_log('autoriser_modifier_asso_compte entry id=' . intval($id) . ' qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');`
  - `association_autoriser.php:509` (appel_php, caller: `autoriser_modifier_asso_compte_dist`) -> `association_debug_log('autoriser_modifier_asso_compte allow admin', 'association_autorisation');`
  - `association_autoriser.php:523` (appel_php, caller: `autoriser_modifier_asso_compte_dist`) -> `association_debug_log('autoriser_modifier_asso_compte deny vu==1 for non-admin', 'association_autorisation');`
  - `association_autoriser.php:534` (appel_php, caller: `autoriser_modifier_asso_compte_dist`) -> `association_debug_log('autoriser_modifier_asso_compte delegate to evenement modifier result=' . (int)$res, 'association_autorisation');`
  - `association_autoriser.php:551` (appel_php, caller: `autoriser_creer_asso_compte_dist`) -> `association_debug_log('autoriser_creer_asso_compte entry qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true) . ' opt=' . var_export($opt, true), 'association_autorisation');`
  - `association_autoriser.php:555` (appel_php, caller: `autoriser_creer_asso_compte_dist`) -> `association_debug_log('autoriser_creer_asso_compte allow admin', 'association_autorisation');`
  - `association_autoriser.php:653` (appel_php, caller: `autoriser_modifier_evenement_dist`) -> `association_debug_log('autoriser_modifier_evenement entry id=' . intval($id) . ' qui=' . var_export(array('id' => $qui['id_auteur'], 'statut' => $qui['statut']), true), 'association_autorisation');`
  - `association_autoriser.php:657` (appel_php, caller: `autoriser_modifier_evenement_dist`) -> `association_debug_log('autoriser_modifier_evenement allow admin complet', 'association_autorisation');`
  - `association_autoriser.php:665` (appel_php, caller: `autoriser_modifier_evenement_dist`) -> `association_debug_log('autoriser_modifier_evenement acceder_natif result=' . (int)$res, 'association_autorisation');`
  - `association_autoriser.php:675` (appel_php, caller: `autoriser_assocompte_modifier_dist`) -> `association_debug_log('autoriser_assocompte_modifier_dist wrapper for id=' . intval($id), 'association_autorisation');`
  - `association_autoriser.php:677` (appel_php, caller: `autoriser_assocompte_modifier_dist`) -> `association_debug_log('autoriser_assocompte_modifier_dist result=' . (int)$res, 'association_autorisation');`
  - `association_autoriser.php:682` (appel_php, caller: `autoriser_assocompte_creer_dist`) -> `association_debug_log('autoriser_assocompte_creer_dist wrapper for id=' . intval($id), 'association_autorisation');`
  - `association_autoriser.php:684` (appel_php, caller: `autoriser_assocompte_creer_dist`) -> `association_debug_log('autoriser_assocompte_creer_dist result=' . (int)$res, 'association_autorisation');`
  - `association_autoriser.php:760` (appel_php, caller: `autoriser_publierdans_dist`) -> `association_debug_log(var_export(array(`
  - `association_autoriser.php:775` (appel_php, caller: `autoriser_publierdans_dist`) -> `association_debug_log("delegation to $func -> " . (int)$res, 'association_autorisation');`
  - `association_autoriser.php:781` (appel_php, caller: `autoriser_publierdans_dist`) -> `association_debug_log("delegation to $func_dist -> " . (int)$res, 'association_autorisation');`
  - `association_autoriser.php:788` (appel_php, caller: `autoriser_publierdans_dist`) -> `association_debug_log("autoriser_publierdans fallback allowed=" . ($allowed ? '1' : '0'), 'association_autorisation');`

### `association_declarer_champs_extras`

- Total occurrences: **7**
- Repartition: `appel_php`=7
- Occurrences:
  - `association_administrations.php:25` (appel_php, caller: `association_upgrade`) -> `//cextras_api_upgrade(association_declarer_champs_extras(), $maj['create']);`
  - `association_administrations.php:87` (appel_php, caller: `association_upgrade`) -> `//cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.2']);`
  - `association_administrations.php:92` (appel_php, caller: `association_upgrade`) -> `//cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.4']);`
  - `association_administrations.php:105` (appel_php, caller: `association_upgrade`) -> `//cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.8']);`
  - `association_administrations.php:119` (appel_php, caller: `association_upgrade`) -> `//cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.13']);`
  - `association_administrations.php:124` (appel_php, caller: `association_upgrade`) -> `//cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.15']);`
  - `association_administrations.php:398` (appel_php, caller: `association_vider_tables`) -> `cextras_api_vider_tables(association_declarer_champs_extras(array()));`

### `association_declarer_champs_extras_impl`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association.php:256` (appel_php, caller: `association_declarer_champs_extras`) -> `return association_declarer_champs_extras_impl($champs);`

### `association_declarer_tables_auxiliaires`

- Aucun appel detecte statiquement.

### `association_declarer_tables_objets_sql`

- Aucun appel detecte statiquement.

### `association_declarer_tables_principales`

- Aucun appel detecte statiquement.

### `association_defaut_tri_adherents`

- Total occurrences: **2**
- Repartition: `appel_php`=1, `filtre_template`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents.html:7` (filtre_template, caller: `(squelette)`) -> `[(#SET{defaut_tri,#VAL\|association_defaut_tri_adherents})]`
  - `prive/squelettes/contenu/adherents_fonctions.php:842` (appel_php, caller: `filtre_association_defaut_tri_adherents_dist`) -> `return association_defaut_tri_adherents();`

### `association_definitions_champs_extras`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:670` (appel_php, caller: `liste_filtres_dynamiques_adherents`) -> `$definitions = association_definitions_champs_extras($fields);`
  - `prive/squelettes/contenu/adherents_fonctions.php:693` (appel_php, caller: `liste_colonnes_dynamiques_adherents`) -> `$colonnes = association_definitions_champs_extras($fields);`

### `association_donnees_colonnes_adherent`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:732` (appel_php, caller: `association_hydrater_colonnes_dynamiques`) -> `$valeurs = association_donnees_colonnes_adherent($id_auteur);`
  - `prive/squelettes/contenu/adherents_fonctions.php:746` (appel_php, caller: `valeur_colonne_dynamique_adherent`) -> `$row = association_donnees_colonnes_adherent($id_auteur);`

### `association_editeur_destinations`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `balise/editeur_destinations.php:27` (appel_php, caller: `balise_EDITEUR_DESTINATIONS_dyn`) -> `return association_editeur_destinations($destinations_id_montant, $unique_dest, $defaut_dest);`

### `association_est_admin_complet`

- Total occurrences: **13**
- Repartition: `appel_php`=13
- Occurrences:
  - `association_autoriser.php:176` (appel_php, caller: `autoriser_adherents_menu_dist`) -> `return association_est_admin_complet($qui);`
  - `association_autoriser.php:208` (appel_php, caller: `autoriser_cotisations_menu_dist`) -> `return association_est_admin_complet($qui);`
  - `association_autoriser.php:233` (appel_php, caller: `autoriser_comptes_menu_dist`) -> `return association_est_admin_complet($qui);`
  - `association_autoriser.php:243` (appel_php, caller: `autoriser_dons_menu_dist`) -> `return association_est_admin_complet($qui);`
  - `association_autoriser.php:253` (appel_php, caller: `autoriser_ressources_menu_dist`) -> `return association_est_admin_complet($qui);`
  - `association_autoriser.php:263` (appel_php, caller: `autoriser_ventes_menu_dist`) -> `return association_est_admin_complet($qui);`
  - `association_autoriser.php:273` (appel_php, caller: `autoriser_prets_menu_dist`) -> `return association_est_admin_complet($qui);`
  - `association_autoriser.php:283` (appel_php, caller: `autoriser_destinations_menu_dist`) -> `return association_est_admin_complet($qui);`
  - `association_autoriser.php:635` (appel_php, caller: `autoriser_article_creerevenementdans`) -> `if (association_est_admin_complet($qui)) return true;`
  - `association_autoriser.php:656` (appel_php, caller: `autoriser_modifier_evenement_dist`) -> `if (association_est_admin_complet($qui)) {`
  - `association_autoriser.php:809` (appel_php, caller: `autoriser_modifier_article_dist`) -> `if (association_est_admin_complet($qui)) return true;`
  - `association_autoriser.php:896` (appel_php, caller: `autoriser_voir_activites_dist`) -> `if (association_est_admin_complet($qui)) return true;`
  - `association_autoriser.php:941` (appel_php, caller: `autoriser_onglet_activites_dist`) -> `if (association_est_admin_complet($qui)) return true;`

### `association_est_responsable_evenement`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `association_autoriser.php:336` (appel_php, caller: `autoriser_comptes_dist`) -> `if ($id_evenement > 0 && association_est_responsable_evenement($qui, $id_evenement)) {`
  - `association_autoriser.php:366` (appel_php, caller: `autoriser_asso_comptes_creer_dist`) -> `if ($id_evenement > 0 && association_est_responsable_evenement($qui, $id_evenement)) {`
  - `association_autoriser.php:903` (appel_php, caller: `autoriser_voir_activites_dist`) -> `if (association_est_responsable_evenement($qui, $id)) return true;`
  - `association_autoriser.php:943` (appel_php, caller: `autoriser_onglet_activites_dist`) -> `return association_est_responsable_evenement($qui, $id);`

### `association_flottant`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `exec/dons.php:83` (appel_php, caller: `exec_dons`) -> `echo '<td class="arial11 border1" style="text-align:right;">'.association_flottant($data['argent']).'&nbsp;&euro;</td>';`
  - `exec/dons.php:87` (appel_php, caller: `exec_dons`) -> `: ('<td class="arial11 border1" style="text-align:right;">'.association_flottant($data['valeur']).'&nbsp;&euro;</td>'`

### `association_formulaire_charger`

- Aucun appel detecte statiquement.

### `association_formulaire_traiter`

- Aucun appel detecte statiquement.

### `association_formulaire_verifier`

- Aucun appel detecte statiquement.

### `association_get_liens_map`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `association_pipelines.php:809` (appel_php, caller: `association_sync_repetitions_tarifs`) -> `$liens_source_map = association_get_liens_map($id_evenement);`
  - `association_pipelines.php:825` (appel_php, caller: `association_sync_repetitions_tarifs`) -> `$liens_rep_map = association_get_liens_map($id_rep);`

### `association_header_prive`

- Aucun appel detecte statiquement.

### `association_heurefr`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `inc/fonctions/facteur_envoyer_mail_activites.php:96` (appel_php, caller: `facteur_envoyer_mail_activite_adherent`) -> `$heure_ev = association_heurefr($date_evenement);`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:221` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `$heure_ev = association_heurefr($evenement['date_debut']);`
  - `inc/fonctions/gestion_places.php:152` (appel_php, caller: `gestions_places`) -> `$result['evenement_heure_debut'] = association_heurefr($query_evenement['date_debut']);`
  - `inc/fonctions/gestion_places.php:154` (appel_php, caller: `gestions_places`) -> `$result['evenement_heure_fin'] = association_heurefr($query_evenement['date_fin']);`

### `association_hydrater_colonnes_dynamiques`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:850` (appel_php, caller: `filtre_trier_colonne_dynamique_dist`) -> `association_hydrater_colonnes_dynamiques($adherents);`

### `association_i3_verifier_formulaire`

- Aucun appel detecte statiquement.

### `association_icone`

- Total occurrences: **18**
- Repartition: `appel_php`=18
- Occurrences:
  - `association_options.php:214` (appel_php, caller: `association_retour`) -> `//return bloc_des_raccourcis(association_icone(_T('asso:bouton_retour'),  str_replace('&', '&amp;', $_SERVER['HTTP_REFERER']), "retour-24.png"));`
  - `exec/action_adherents.php:37` (appel_php, caller: `exec_action_adherents_args`) -> `echo bloc_des_raccourcis(association_icone(_T('asso:bouton_retour'),  generer_url_ecrire('adherents'), "retour-24.png"));`
  - `exec/action_comptes.php:38` (appel_php, caller: `exec_action_comptes_args`) -> `echo bloc_des_raccourcis(association_icone(_T('asso:bouton_retour'),  generer_url_ecrire('comptes'), "retour-24.png"));`
  - `exec/destinations.php:36` (appel_php, caller: `raccourcis`) -> `$raccourcis = association_icone(_T('asso:ajouter_destination'),  generer_url_ecrire('edit_destination'), '',  '');`
  - `exec/destinations.php:37` (appel_php, caller: `raccourcis`) -> `$raccourcis.= association_icone(_T('asso:importer_destination_comptable'),  generer_url_ecrire('destination_comptable_import'), '',  '');`
  - `exec/dons.php:36` (appel_php, caller: `exec_dons`) -> `echo bloc_des_raccourcis(association_icone(_T('asso:ajouter_un_don'), generer_url_ecrire('edit_don'), 'ajout_don.png'));`
  - `exec/edit_email_collectif_activite.php:29` (appel_php, caller: `exec_edit_email_collectif_activite`) -> `//$res=association_icone(_T('asso:bouton_impression'),  $url_edit_labels, "print-24.png");`
  - `exec/edit_email_collectif_adherent.php:26` (appel_php, caller: `exec_edit_email_collectif_adherent`) -> `$res.=association_icone(_T('asso:bouton_retour'),  $url_retour, "retour-24.png");`
  - `exec/edit_mail.php:27` (appel_php, caller: `exec_edit_mail`) -> `$res.=association_icone(_T('asso:bouton_retour'),  $url_retour, "retour-24.png");`
  - `exec/edit_relances.php:36` (appel_php, caller: `exec_edit_relances`) -> `$res=association_icone(_T('asso:bouton_impression'),  $url_edit_labels, "print-24.png");`
  - `exec/edit_relances.php:38` (appel_php, caller: `exec_edit_relances`) -> `$res.=association_icone(_T('asso:bouton_retour'),  $url_retour, "retour-24.png");`
  - `exec/plan_comptable.php:40` (appel_php, caller: `raccourcis`) -> `$raccourcis.= association_icone(_T('asso:plan_nav_ajouter'), generer_url_ecrire('edit_plan'), 'EuroOff.gif',  'creer-12.gif');`
  - `exec/plan_comptable.php:42` (appel_php, caller: `raccourcis`) -> `$raccourcis.= association_icone(_T('asso:plan_comptable_import'), generer_url_ecrire('plan_comptable_import'), '',  '');`
  - `exec/plan_comptable.php:44` (appel_php, caller: `raccourcis`) -> `$raccourcis .= association_icone(_T('asso:destination_comptable'), generer_url_ecrire('destinations'), 'EuroOff.gif',  '');`
  - `exec/prets.php:46` (appel_php, caller: `exec_prets`) -> `echo bloc_des_raccourcis(association_icone(_T('asso:prets_nav_editer'), generer_url_ecrire('edit_pret','agir=ajoute&id_pret='.$id_ressource), 'livredor.png', 'creer-12.gif'));`
  - `exec/prets.php:49` (appel_php, caller: `exec_prets`) -> `echo bloc_des_raccourcis(association_icone(_T('asso:prets_nav_ajouter'), generer_url_ecrire('edit_pret','agir=ajoute&id_objet='.$id_ressource), 'livredor.png', 'creer-12.gif'));`
  - `exec/ressources.php:46` (appel_php, caller: `raccourcis`) -> `return association_icone(_T('asso:ressources_nav_ajouter'),  generer_url_ecrire('edit_ressource'), 'ajout_don.png');`
  - `exec/ventes.php:62` (appel_php, caller: `exec_ventes`) -> `$res=association_icone(_T('asso:ajouter_une_vente'),  $url_ajout_vente, 'ajout_don.png');`

### `association_import_champs_extras`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_administrations.php:298` (appel_php, caller: `association_maj_create`) -> `association_import_champs_extras(); // En cas de nécessité sur les mise à jour. Ne sera pas nécessaire.`

### `association_index_champs_extras`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:108` (appel_php, caller: `association_definitions_champs_extras`) -> `$index = association_index_champs_extras();`

### `association_is_serialized`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_fonctions.php:452` (appel_php, caller: `deserialize_values`) -> `if (is_string($value) && association_is_serialized($value)) {`

### `association_job_notifier_echeance`

- Aucun appel detecte statiquement.

### `association_jqueryui_plugins`

- Aucun appel detecte statiquement.

### `association_lien_ecrire_fa`

- Total occurrences: **22**
- Repartition: `appel_php`=8, `filtre_template`=14
- Occurrences:
  - `association_options.php:196` (appel_php, caller: `association_list_lien_ecrire_fa`) -> `$res .=  association_lien_ecrire_fa($texte, $fa_class, $script). '</li>';`
  - `exec/activites.php:30` (appel_php, caller: `exec_activites`) -> `echo '<li>' .association_lien_ecrire_fa(_T('asso:categories_de_participation_activites'), 'fa fa-cog', 'categories_activites').'</li>';`
  - `exec/activites.php:31` (appel_php, caller: `exec_activites`) -> `echo '<li>' .association_lien_ecrire_fa(_T('asso:lien_export_activites'), 'fa-solid fa-file-export', 'export_activites').'</li>';`
  - `exec/activites.php:32` (appel_php, caller: `exec_activites`) -> `echo '<li>' .association_lien_ecrire_fa(_T('asso:lien_suivi_activites'), 'fa-solid fa-align-left', 'suivi_activites').'</li>';`
  - `exec/activites.php:37` (appel_php, caller: `exec_activites`) -> `echo '<li>' .association_lien_ecrire_fa(_T('asso:label_export_activites_compta'), 'fa-solid fa-file-invoice-dollar', 'analyse_compta_activites').'</li>';`
  - `exec/voir_adherent.php:36` (appel_php, caller: `exec_voir_adherent`) -> `echo '<li>' .association_lien_ecrire_fa(_T('asso:voir_fiche_adherent'),  'fa-solid fa-user','auteur', 'id_auteur=' . $id_auteur).'</li>';`
  - `exec/voir_adherent.php:37` (appel_php, caller: `exec_voir_adherent`) -> `echo '<li>' .association_lien_ecrire_fa(_T('asso:modifier_fiche_adherent'),  'fa-regular fa-pen-to-square','auteur_edit', 'id_auteur=' . $id_auteur).'</li>';`
  - `exec/voir_adherent.php:39` (appel_php, caller: `exec_voir_adherent`) -> `echo '<li class="action">' .association_lien_ecrire_fa(_T('asso:ajouter_nouvel_cotisation'), 'fa fa-plus-circle', 'editer_asso_cotisation', 'id_auteur=' . $id_auteur).'</li>';`
  - `prive/squelettes/extra/comptes.html:6` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:menu2_titre_plan_comptable:>}\|association_lien_ecrire_fa{'fa-solid fa-file-import','plan_comptable',''})]`
  - `prive/squelettes/extra/comptes.html:9` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:menu2_titre_destination_comptable:>}\|association_lien_ecrire_fa{'fa-solid fa-file-import','destinations',''})]`
  - `prive/squelettes/extra/comptes.html:12` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:menu2_titre_bilan:>}\|association_lien_ecrire_fa{'fa-solid fa-rotate','bilan',''})]`
  - `prive/squelettes/extra/comptes.html:15` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:ajouter_une_operation:>}\|association_lien_ecrire_fa{'fa-solid fa-rotate','editer_asso_comptes',''})]`
  - `prive/squelettes/extra/comptes.html:18` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:raccourci_page_migration:>}\|association_lien_ecrire_fa{'fa-solid fa-rotate','migration_donnees_comptables',''})]`
  - `prive/squelettes/extra/configurer_association.html:6` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:raccourci_page_pc_import:>}\|association_lien_ecrire_fa{'fa-solid fa-file-import','plan_comptable_import',''})]`
  - `prive/squelettes/extra/configurer_association.html:9` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:raccourci_page_dc_import:>}\|association_lien_ecrire_fa{'fa-solid fa-file-import','destination_comptable_import',''})]`
  - `prive/squelettes/extra/configurer_association.html:12` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:raccourci_page_migration:>}\|association_lien_ecrire_fa{'fa-solid fa-rotate','migration_donnees_comptables',''})]`
  - `prive/squelettes/navigation/categories_activites.html:6` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:ajouter_nouvelle_categorie_participation:>}\|association_lien_ecrire_fa{'fa fa-plus','editer_asso_categorie_activite'})]`
  - `prive/squelettes/navigation/categories_cotisation.html:6` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:ajouter_une_categorie_de_cotisation:>}\|association_lien_ecrire_fa{'fa fa-plus','editer_asso_categorie_cotisation'})]`
  - `prive/squelettes/navigation/editer_asso_activite.html:6` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:toutes_categories_de_activites:>}\|association_lien_ecrire_fa{'fa fa-cog','categories_activite'})]`
  - `prive/squelettes/navigation/editer_asso_categorie_activite.html:6` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:toutes_categories_de_participation:>}\|association_lien_ecrire_fa{'fa fa-cog','categories_activites'})]`
  - `prive/squelettes/navigation/editer_asso_categorie_cotisation.html:6` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:toutes_categories_de_cotisations_adherents:>}\|association_lien_ecrire_fa{'fa fa-cog','categories_cotisation'})]`
  - `prive/squelettes/navigation/editer_asso_cotisation.html:6` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:toutes_categories_de_cotisations_adherents:>}\|association_lien_ecrire_fa{'fa fa-cog','categories_cotisation'})]`

### `association_lien_public_fa`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/voir_adherent.php:38` (appel_php, caller: `exec_voir_adherent`) -> `echo '<li>' .association_lien_public_fa(_T('asso:consulter_profil_adherent'), 'fa fa-desktop', 'auteur', 'id_auteur=' . $id_auteur).'</li>';`

### `association_lire_config_liste`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:648` (appel_php, caller: `filtre_liste_champs_filtres_configures`) -> `return association_lire_config_liste('config_champs_filtres_adherents');`
  - `prive/squelettes/contenu/adherents_fonctions.php:655` (appel_php, caller: `filtre_liste_champs_colonnes_configures`) -> `return association_lire_config_liste('config_champs_colonnes_adherents');`

### `association_list_lien_ecrire_fa`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_options.php:125` (appel_php, caller: `association_icone`) -> `return association_list_lien_ecrire_fa($texte, $image, $script_part, '');`

### `association_liste_champs_recherche_avancee_actifs`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:902` (appel_php, caller: `association_recherche_avancee_reset_demandee`) -> `$champs = association_liste_champs_recherche_avancee_actifs();`
  - `prive/squelettes/contenu/adherents_fonctions.php:920` (appel_php, caller: `filtre_url_reinit_recherche_avancee_dist`) -> `foreach (association_liste_champs_recherche_avancee_actifs() as $champ) {`

### `association_liste_destinations_associees`

- Aucun appel detecte statiquement.

### `association_log`

- Total occurrences: **173**
- Repartition: `appel_php`=173
- Occurrences:
  - `action/envoyer_email_collectif_activite.php:44` (appel_php, caller: `action_envoyer_email_collectif_activite`) -> `association_log('email', 'action_envoyer_email_collectif_activite: liste d\'activites vide', 'erreur');`
  - `action/envoyer_email_collectif_activite.php:91` (appel_php, caller: `action_envoyer_email_collectif_activite`) -> `association_log('email', 'action_envoyer_email_collectif_activite: aucun email trouvé', 'erreur');`
  - `action/gis_geocoder_rechercher.php:58` (appel_php, caller: `action_gis_geocoder_rechercher_dist`) -> `association_log('gis', 'gis_geocoder_rechercher: reponse', 'debug', array('data' => $data));`
  - `action/synchroniser_comptabilite_evenement.php:28` (appel_php, caller: `action_synchroniser_comptabilite_evenement_dist`) -> `association_log('comptabilite', "Action de synchronisation de la comptabilité pour l'événement ID $id_evenement", 'info');`
  - `action/synchroniser_comptabilite_evenement.php:126` (appel_php, caller: `synchroniser_comptabilite_evenement`) -> `association_log('comptabilite', 'synchroniser_comptabilite_evenement: ajustement montant compte id_compte=' . intval($compte_paiement['id_compte']) . ' de ' . $compte_recette . ' vers ' . $transaction_montant, 'info');`
  - `action/test_notification_cotisation.php:54` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `association_log('notifications', 'test_notification_cotisation: input_email_membres_asso present mais aucun email valide pour auteur ' . $current_id_auteur, 'erreur');`
  - `action/test_notification_cotisation.php:61` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `association_log('notifications', 'test_notification_cotisation: utilisation du champ email standard pour auteur ' . $current_id_auteur, 'info');`
  - `action/test_notification_cotisation.php:69` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `association_log('notifications', 'test_notification_cotisation: aucun email d\'auteur valide trouve, fallback vers email_webmaster', 'erreur');`
  - `action/test_notification_cotisation.php:203` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `association_log('notifications', 'test_notification_cotisation: email_determine present mais aucun destinataire valide', 'erreur');`
  - `action/test_notification_cotisation.php:205` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `association_log('notifications', 'test_notification_cotisation: envoi de la notification admin vers l\'auteur/destinataire determine: ' . implode(',', (array)$opts['email_override']), 'info');`
  - `association_pipelines.php:793` (appel_php, caller: `association_sync_repetitions_tarifs`) -> `association_log('sync', "association_sync_repetitions_tarifs: skip already processing id_evenement={$id_evenement}", 'info');`
  - `association_pipelines.php:880` (appel_php, caller: `association_sync_repetitions_tarifs`) -> `association_log('sync', "association_sync_repetitions_tarifs: synced id_evenement_source={$id_evenement} -> id_rep={$id_rep} (ins:" . count($to_insert) . " upd:" . count($to_update) . " del:" . count($to_delete) . ")", '`
  - `formulaires/adherents_recherche_avancee.php:58` (appel_php, caller: `formulaires_adherents_recherche_avancee_traiter_dist`) -> `association_log('adherents', 'Recherche avancée sauvegardée en session: ' . count($criteres_recherche) . ' critères', 'debug');`
  - `formulaires/adherents_recherche_rapide.php:101` (appel_php, caller: `formulaires_adherents_recherche_rapide_traiter_dist`) -> `association_log('adherents', 'Recherche rapide sauvegardée en session: ' . count($criteres_recherche) . ' critères', 'debug');`
  - `formulaires/adherents_recherche_rapide.php:107` (appel_php, caller: `formulaires_adherents_recherche_rapide_traiter_dist`) -> `association_log('adherents', 'Recherche rapide réinitialisée (aucun critère soumis)', 'debug');`
  - `formulaires/configurer_association.php:2007` (appel_php, caller: `formulaires_configurer_association_traiter_dist`) -> `association_log('cron', 'Associaspip: Lancement dry-run via interface (user id: ' . intval($GLOBALS['visiteur_session']['id_auteur'] ?? 0) . ')', 'info');`
  - `formulaires/configurer_association.php:2042` (appel_php, caller: `formulaires_configurer_association_traiter_dist`) -> `association_log('cron', 'Associaspip: Options dry-run construites: ' . json_encode($options), 'info');`
  - `formulaires/email_collectif_adherent.php:310` (appel_php, caller: `formulaires_email_collectif_adherent_charger_dist`) -> `association_log('email', 'email_collectif: liste_auteurs -> '.var_export($contexte['liste_auteurs'], true), 'erreur');`
  - `formulaires/inc/adherents_recherche_avancee.php:265` (appel_php, caller: `preparer_criteres_adherents`) -> `association_log('adherents', 'preparer_criteres_adherents: contexte brut -> ' . var_export($contexte, true), 'erreur');`
  - `formulaires/inc/adherents_recherche_avancee.php:441` (appel_php, caller: `preparer_criteres_adherents`) -> `association_log('adherents', 'preparer_criteres_adherents: clauses_intermediaires -> ' . var_export($criteres, true), 'erreur');`
  - `formulaires/inc/adherents_recherche_avancee.php:468` (appel_php, caller: `preparer_criteres_adherents`) -> `association_log('adherents', 'preparer_criteres_adherents: criteres_sql -> ' . var_export($final, true), 'erreur');`
  - `formulaires/inc/adherents_recherche_avancee.php:554` (appel_php, caller: `generer_array_adherents`) -> `association_log('adherents', 'generer_array_adherents: where -> ' . var_export($where_for_log, true), 'erreur');`
  - `formulaires/inc/adherents_recherche_avancee.php:597` (appel_php, caller: `generer_array_adherents`) -> `association_log('adherents', 'generer_array_adherents: ids trouvés -> ' . var_export(array_slice($ids,0,20), true) . ' (count=' . count($ids) . ')', 'erreur');`
  - `formulaires/inc/adherents_recherche_avancee.php:655` (appel_php, caller: `afficher_resultat_recherche_avancee`) -> `association_log('adherents', 'afficher_resultat_recherche_avancee: aucun resultat pour les criteres initiaux, fallback validite >= '. $now, 'erreur');`
  - `formulaires/inc/inscription_evenement.php:452` (appel_php, caller: `generer_detail_participants`) -> `association_log('inscriptions', "Erreur: id_participants n'est pas un tableau", 'erreur');`
  - `formulaires/inc/inscription_evenement.php:460` (appel_php, caller: `generer_detail_participants`) -> `association_log('inscriptions', "Erreur: query_auteur n'est pas un tableau", 'erreur');`
  - `formulaires/inc/inscription_evenement.php:487` (appel_php, caller: `generer_detail_participants`) -> `association_log('inscriptions', "Type de participant non reconnu: $id_participant", 'info');`
  - `formulaires/inc/inscription_evenement.php:500` (appel_php, caller: `generer_detail_participants`) -> `association_log('inscriptions', "Champ '$field' manquant pour le participant '$id_participant'", 'info');`
  - `formulaires/inc/inscription_evenement.php:531` (appel_php, caller: `generer_detail_participants`) -> `association_log('inscriptions', "Erreur d'encodage JSON: " . json_last_error_msg(), 'erreur');`
  - `formulaires/inc/inscription_evenement.php:536` (appel_php, caller: `generer_detail_participants`) -> `association_log('inscriptions', "Exception dans generer_detail_participants: " . $e->getMessage(), 'erreur');`
  - `formulaires/inc/inscription_evenement.php:1124` (appel_php, caller: `notifier_inscription_activite`) -> `association_log('inscriptions', 'Erreur de notification d\'inscription à l\'activité : type inconnu' . $cal_result_statut, 'erreur');`
  - `formulaires/inc/inscription_evenement.php:1253` (appel_php, caller: `verifier_spam_formulaire_inscription`) -> `association_log('spam', "spam champs nobot - IP: $ip_client", 'erreur');`
  - `formulaires/inc/inscription_evenement.php:1261` (appel_php, caller: `verifier_spam_formulaire_inscription`) -> `association_log('spam', 'spam caractères chinois', 'erreur');`
  - `formulaires/inc/inscription_evenement.php:1267` (appel_php, caller: `verifier_spam_formulaire_inscription`) -> `association_log('spam', 'spam caractères russes', 'erreur');`
  - `formulaires/inc/inscription_evenement.php:1273` (appel_php, caller: `verifier_spam_formulaire_inscription`) -> `association_log('spam', 'spam lien hypertexte', 'erreur');`
  - `formulaires/inc/inscription_evenement.php:1282` (appel_php, caller: `verifier_spam_formulaire_inscription`) -> `association_log('spam', 'spam email chinois qq.com', 'erreur');`
  - `formulaires/inc/inscription_evenement.php:1290` (appel_php, caller: `verifier_spam_formulaire_inscription`) -> `association_log('spam', 'spam email @mailbox.in.ua', 'erreur');`
  - `formulaires/inc/inscription_evenement.php:1297` (appel_php, caller: `verifier_spam_formulaire_inscription`) -> `association_log('spam', 'spam prenom et nom identique : ' . $post_data[$prenom_field] . '&' . $post_data[$nom_field], 'erreur');`
  - `formulaires/inscription_evenement_public.php:561` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `association_log('inscriptions', $_POST, 'erreur');`
  - `formulaires/inscription_evenement_public.php:565` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `association_log('inscriptions', $data_form, 'erreur');`
  - `genie/association_expiration_auto_evenement.php:92` (appel_php, caller: `genie_association_expiration_auto_evenement_dist`) -> `association_log('cron', "Associaspip : Expiration auto evenement cron terminés", 'info');`
  - `genie/association_maintenance_bdd.php:71` (appel_php, caller: `genie_association_maintenance_bdd`) -> `association_log('cron', 'Associaspip: Tâche CRON maintenance - début', 'info');`
  - `genie/association_maintenance_bdd.php:111` (appel_php, caller: `genie_association_maintenance_bdd`) -> `association_log('cron', 'Associaspip: Options maintenance construites depuis metas: ' . json_encode($options), 'info');`
  - `genie/association_maintenance_bdd.php:140` (appel_php, caller: `association_maintenance_bdd_run`) -> `association_log('cron', 'Associaspip: Maintenance BDD SKIPPED (désactivée via configuration).', 'info');`
  - `genie/association_maintenance_bdd.php:258` (appel_php, caller: `association_maintenance_bdd_run`) -> `association_log('cron', 'Associaspip: Maintenance - résumé: ' . json_encode($resume), 'info');`
  - `genie/association_maintenance_bdd.php:809` (appel_php, caller: `asso_supprimer_transactions_orphelines`) -> `association_log('cron', 'Erreur suppression transactions orphelines (' . $where . ')', 'erreur');`
  - `inc/api_cotisations.php:323` (appel_php, caller: `api_traiter_cotisation`) -> `association_log('cotisations', 'api_traiter_cotisation: exception: ' . $e->getMessage(), 'erreur');`
  - `inc/association_comptabilite.php:158` (appel_php, caller: `association_modifier_operation_comptable`) -> `association_log('comptabilite', 'association_modifier_operation_comptable: id_compte invalide - mise à jour ignorée', 'info');`
  - `inc/association_log.php:9` (appel_php, caller: `(global)`) -> `*   association_log('autorisations', 'message', 'debug', ['id_auteur' => 3]);`
  - `inc/association_log.php:10` (appel_php, caller: `(global)`) -> `*   association_log('autorisations', 'erreur critique', 'erreur');`
  - `inc/comptes.php:234` (appel_php, caller: `modifier_compte`) -> `association_log('comptabilite', "modifier_compte: id_compte invalide, mise à jour ignorée", 'info');`
  - `inc/comptes.php:267` (appel_php, caller: `inserer_compte_activite`) -> `association_log('comptabilite', "Compta activité ignorée (événement gratuit) id_evenement=".$row_activite['id_evenement']." id_activite=".$id_activite, 'info');`
  - `inc/comptes.php:274` (appel_php, caller: `inserer_compte_activite`) -> `association_log('comptabilite', "Compta activite ignoree (transaction introuvable) id_activite=" . intval($id_activite), 'erreur');`
  - `inc/comptes.php:331` (appel_php, caller: `inserer_compte_remboursement_activite`) -> `association_log('comptabilite', 'inserer_compte_remboursement_activite: id_transaction invalide', 'erreur');`
  - `inc/comptes.php:340` (appel_php, caller: `inserer_compte_remboursement_activite`) -> `association_log('comptabilite', 'inserer_compte_remboursement_activite: activite introuvable pour id_transaction=' . $id_transaction, 'erreur');`
  - `inc/comptes.php:348` (appel_php, caller: `inserer_compte_remboursement_activite`) -> `association_log('comptabilite', 'inserer_compte_remboursement_activite: donnees activite/transaction manquantes id_transaction=' . $id_transaction, 'erreur');`
  - `inc/comptes.php:446` (appel_php, caller: `modifier_compte_activite`) -> `association_log('comptabilite', "modifier_compte_activite: id_compte introuvable pour id_activite=" . intval($id_activite) . " id_transaction=" . intval($id_transaction), 'info');`
  - `inc/comptes.php:472` (appel_php, caller: `modifier_compte_activite`) -> `association_log('comptabilite', 'modifier_compte_activite: ajustement recette id_compte=' . intval($id_compte) . ' de ' . $current_recette . ' vers ' . $recette, 'info');`
  - `inc/comptes.php:521` (appel_php, caller: `valider_compte_activite`) -> `association_log('comptabilite', "valider_compte_activite: aucun compte trouvé pour id_transaction=" . intval($id_transaction), 'info');`
  - `inc/cotisations.php:170` (appel_php, caller: `changer_statut_cotisation`) -> `association_log('cotisations', 'Erreur lors de activer_adherent pour id_auteur=' . intval($id_auteur) . ' : ' . $e->getMessage(), 'erreur');`
  - `inc/cotisations.php:177` (appel_php, caller: `changer_statut_cotisation`) -> `association_log('cotisations', 'Activation privée non déclenchée pour id_compte=' . intval($id_compte) . ' origine=' . $origine . ' statut_cotisation=' . ($query_cotisation['statut_cotisation'] ?? '') . ' validation_m`
  - `inc/cotisations.php:220` (appel_php, caller: `changer_statut_cotisation`) -> `association_log('cotisations', 'Erreur lors de activer_adherent pour id_auteur=' . intval($id_auteur) . ' : ' . $e->getMessage(), 'erreur');`
  - `inc/cotisations.php:366` (appel_php, caller: `notifications_cotisation_trouver_sujet`) -> `association_log('cotisations', 'notifications_cotisation_trouver_sujet: traduction manquante pour cle=' . $cle . ' type=' . $type . ' args=' . json_encode($args), 'critique');`
  - `inc/cotisations.php:368` (appel_php, caller: `notifications_cotisation_trouver_sujet`) -> `association_log('cotisations', $contexte, 'critique');`
  - `inc/cotisations.php:370` (appel_php, caller: `notifications_cotisation_trouver_sujet`) -> `association_log('cotisations', 'notifications_cotisation_trouver_sujet: aucune clé de traduction pour type=' . $type . ' args=' . json_encode($args), 'critique');`
  - `inc/cotisations.php:371` (appel_php, caller: `notifications_cotisation_trouver_sujet`) -> `association_log('cotisations', $contexte, 'critique');`
  - `inc/cotisations.php:396` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Notification (type=' . $type . ') - notifier_cotisation_adherent pour id_compte=' . intval($query_cotisation['id_compte'] ?? 0), 'critique');`
  - `inc/cotisations.php:399` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Notification - aucun type de notif defini', 'critique');`
  - `inc/cotisations.php:418` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Notification - impossible de construire un contexte de notification valide (email manquant)', 'erreur');`
  - `inc/cotisations.php:489` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'notifier_cotisation_adherent: envoi bloqué - sujet manquant pour type=' . $type . ' id_compte=' . intval($contexte_notification['id_compte'] ?? 0) . ' args=' . json_encode(array('nom' => `
  - `inc/cotisations.php:491` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', $contexte_notification, 'critique');`
  - `inc/cotisations.php:516` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (attente_paiement): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:533` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (validation_post-paiement): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:549` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (validation_pre_paiement): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:574` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (activation): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:591` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (notification_echeances_adherent): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:621` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (notification_echeances_adherent_echu): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:718` (appel_php, caller: `notifier_cotisation_preparer_contexte`) -> `association_log('cotisations', 'Erreur preparer_liste_categories: ' . $e->getMessage(), 'erreur');`
  - `inc/cotisations.php:756` (appel_php, caller: `notifier_cotisation_preparer_contexte`) -> `association_log('cotisations', $msg, 'critique');`
  - `inc/cotisations.php:764` (appel_php, caller: `notifier_cotisation_preparer_contexte`) -> `association_log('cotisations', $input, 'critique');`
  - `inc/cotisations.php:766` (appel_php, caller: `notifier_cotisation_preparer_contexte`) -> `association_log('cotisations', $contexte, 'critique');`
  - `inc/cotisations.php:792` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'Notification (type=' . $type . ') - notifier_cotisation_admin pour id_compte=' . intval($query_cotisation['id_compte'] ?? 0), 'critique');`
  - `inc/cotisations.php:795` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'Notification - aucun type de notif defini pour notifier_cotisation_admin', 'critique');`
  - `inc/cotisations.php:802` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'Notification - impossible de préparer le contexte pour notifier_cotisation_admin', 'critique');`
  - `inc/cotisations.php:811` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', $contexte_notification, 'critique');`
  - `inc/cotisations.php:834` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'Notification - override destinataires admin utilisé: ' . implode(',', $emails_tres), 'critique');`
  - `inc/cotisations.php:836` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'Notification - override destinataires admin fourni mais aucune adresse valide trouvée: ' . print_r($override, true), 'erreur');`
  - `inc/cotisations.php:852` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'notifier_cotisation_admin: envoi bloqué - sujet manquant (cle cotisation_attente_admin_sujet) pour id_compte=' . intval($query_cotisation['id_compte'] ?? 0), 'critique');`
  - `inc/cotisations.php:853` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', $contexte_notification, 'critique');`
  - `inc/cotisations.php:858` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'Notifier sujet (attente_admin): ' . $sujet, 'critique');`
  - `inc/cotisations.php:862` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'notifier_cotisation_admin: erreur envoi attente_admin : ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:871` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'notifier_cotisation_admin: envoi bloqué - sujet manquant (cle cotisation_demande_admin_sujet) pour id_compte=' . intval($query_cotisation['id_compte'] ?? 0), 'critique');`
  - `inc/cotisations.php:872` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', $contexte_notification, 'critique');`
  - `inc/cotisations.php:877` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'Notifier sujet (demande_admin): ' . $sujet, 'critique');`
  - `inc/cotisations.php:880` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'notifier_cotisation_admin: erreur envoi demande_admin : ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:889` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'notifier_cotisation_admin: envoi bloqué - sujet manquant (cle cotisation_encaissement_admin_sujet) pour id_compte=' . intval($query_cotisation['id_compte'] ?? 0), 'critique');`
  - `inc/cotisations.php:890` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', $contexte_notification, 'critique');`
  - `inc/cotisations.php:900` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'Notifier sujet (encaissement_admin): ' . $sujet, 'critique');`
  - `inc/cotisations.php:903` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'notifier_cotisation_admin: erreur envoi encaissement_admin : ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:907` (appel_php, caller: `notifier_cotisation_admin`) -> `association_log('cotisations', 'Notification - type non géré par notifier_cotisation_admin: ' . $type, 'critique');`
  - `inc/cotisations.php:925` (appel_php, caller: `activer_adherent`) -> `association_log('cotisations', 'ID auteur invalide pour l\'activation de l\'adhérent', 'erreur');`
  - `inc/cotisations.php:937` (appel_php, caller: `activer_adherent`) -> `association_log('cotisations', 'Adhérent #' . $id_auteur . ' introuvable', 'erreur');`
  - `inc/cotisations.php:951` (appel_php, caller: `activer_adherent`) -> `association_log('cotisations', 'Adhérent #' . $id_auteur . ' déjà actif et valide', 'critique');`
  - `inc/cotisations.php:1008` (appel_php, caller: `activer_adherent`) -> `association_log('cotisations', 'Notification d\'activation en file ajoutée pour auteur #' . $id_secondaire, 'critique');`
  - `inc/cotisations.php:1010` (appel_php, caller: `activer_adherent`) -> `association_log('cotisations', 'Erreur notification activation secondaire id_auteur=' . intval($id_secondaire) . ' : ' . $e->getMessage(), 'erreur');`
  - `inc/cotisations.php:1061` (appel_php, caller: `inverser_relation_compte`) -> `association_log('cotisations', 'inverser_relation_compte: aucun principal trouvé pour #' . $id_secondaire, 'critique');`
  - `inc/cotisations.php:1075` (appel_php, caller: `inverser_relation_compte`) -> `association_log('cotisations', 'inverser_relation_compte: avant inversion principal=' . $id_principal . ' -> secondaires=[' . implode(',', $ids) . '] payeur=' . $id_secondaire, 'critique');`
  - `inc/cotisations.php:1093` (appel_php, caller: `inverser_relation_compte`) -> `association_log('cotisations', 'inverser_relation_compte: apres inversion: ' . var_export($comptes_apres, true), 'critique');`
  - `inc/cotisations.php:1115` (appel_php, caller: `associer_liste_diffusion_entreprise`) -> `association_log('cotisations', 'Aucune liste de diffusion configurée pour les entreprises', 'critique');`
  - `inc/cotisations.php:1131` (appel_php, caller: `associer_liste_diffusion_entreprise`) -> `association_log('cotisations', 'associer_liste_diffusion_entreprise : email invalide pour auteur #' . intval($id_auteur), 'erreur');`
  - `inc/cotisations.php:1142` (appel_php, caller: `associer_liste_diffusion_entreprise`) -> `association_log('cotisations', 'Entreprise #' . intval($id_auteur) . ' abonnée à la liste ' . $list, 'critique');`
  - `inc/cotisations.php:1144` (appel_php, caller: `associer_liste_diffusion_entreprise`) -> `association_log('cotisations', 'Erreur abonnement liste ' . $list . ' pour entreprise #' . intval($id_auteur) . ' : ' . $e->getMessage(), 'erreur');`
  - `inc/cotisations.php:1148` (appel_php, caller: `associer_liste_diffusion_entreprise`) -> `association_log('cotisations', 'associer_liste_diffusion_entreprise : (simulé) entreprise #' . intval($id_auteur) . ' -> liste ' . $list, 'critique');`
  - `inc/cotisations.php:1272` (appel_php, caller: `identification_contexte_inscription`) -> `association_log('cotisations', 'identification_contexte_inscription: auteur introuvable id_auteur=' . intval($id_auteur), 'critique');`
  - `inc/cotisations.php:1330` (appel_php, caller: `identification_contexte_inscription`) -> `association_log('cotisations', 'identification_contexte_inscription: id_auteur=' . intval($id_auteur) . ' statut_interne=' . $statut_interne . ' validite=' . ($date_validite_auteur ?: '') . ' dates_scolaires=' . json_enc`
  - `inc/fonctions/association_job_notifier_echeance.php:24` (appel_php, caller: `association_job_notifier_echeance`) -> `association_log('notifications', 'association_job_notifier_echeance: auteur #' . intval($id_auteur) . ' introuvable', 'erreur');`
  - `inc/fonctions/association_validite_calculator.php:23` (appel_php, caller: `association_validite_calculator`) -> `association_log('adherents', 'association_validite_calculator: colonne radio_type_adherent inexistante pour id_auteur=' . intval($id_auteur), 'info');`
  - `inc/fonctions/facteur_envoyer_app.php:79` (appel_php, caller: `facteur_envoyer_app`) -> `association_log('email', 'facteur_envoyer_app : job mis en queue pour ' . implode(', ', $emails) . ' sujet: ' . $sujet, 'info');`
  - `inc/fonctions/facteur_envoyer_app.php:103` (appel_php, caller: `facteur_envoyer_app`) -> `association_log('email', "Email envoyé avec succès à $to sujet: $sujet", 'info');`
  - `inc/fonctions/facteur_envoyer_app.php:106` (appel_php, caller: `facteur_envoyer_app`) -> `association_log('email', "Échec de l'envoi d'email à $to sujet: $sujet", 'erreur');`
  - `inc/fonctions/facteur_envoyer_app.php:110` (appel_php, caller: `facteur_envoyer_app`) -> `association_log('email', "Exception lors de l'envoi d'email à $to: " . $e->getMessage(), 'erreur');`
  - `inc/fonctions/facteur_envoyer_app.php:121` (appel_php, caller: `facteur_envoyer_app`) -> `association_log('email', "Exception lors de l'envoi d'email : " . $e->getMessage(), 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:15` (appel_php, caller: `facteur_envoyer_mail_activites`) -> `association_log('email', "Erreur: type d'action non spécifié", 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:20` (appel_php, caller: `facteur_envoyer_mail_activites`) -> `association_log('email', "Erreur: aucune activité spécifiée", 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:32` (appel_php, caller: `facteur_envoyer_mail_activites`) -> `association_log('email', "Erreur: activité introuvable " . $id_activite[0], 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:41` (appel_php, caller: `facteur_envoyer_mail_activites`) -> `association_log('email', "Erreur: événement introuvable " . $id_evenement, 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:66` (appel_php, caller: `facteur_envoyer_mail_activites`) -> `association_log('email', "Exception générale dans facteur_envoyer_mail_activites: " . $e->getMessage(), 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:85` (appel_php, caller: `facteur_envoyer_mail_activite_adherent`) -> `association_log('email', "Erreur: activité introuvable " . $id, 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:105` (appel_php, caller: `facteur_envoyer_mail_activite_adherent`) -> `association_log('email', "Erreur: auteur introuvable " . $id_auteur, 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:141` (appel_php, caller: `facteur_envoyer_mail_activite_adherent`) -> `association_log('email', "Email invalide pour l'adhérent (activité " . $id . "): " . $email_inscrit, 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:167` (appel_php, caller: `facteur_envoyer_mail_activite_adherent`) -> `association_log('email', "Échec de l'envoi de l'email à " . $email_inscrit, 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:170` (appel_php, caller: `facteur_envoyer_mail_activite_adherent`) -> `association_log('email', "Erreur lors de l'envoi de l'email à l'adhérent: " . $e->getMessage(), 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:245` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `association_log('email', "Email invalide pour le responsable " . $id_responsable . ": " . $email_responsable, 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:248` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `association_log('email', "Email manquant pour le responsable " . $id_responsable, 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:260` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `association_log('email', "Modèle d'email non trouvé pour le type " . $type, 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:293` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `association_log('email', "Échec de l'envoi de l'email aux responsables", 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:304` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `association_log('email', "Configuration 'config_envoi_email_notif_defaut' invalide ou vide", 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:309` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `association_log('email', "Échec de l'envoi de l'email à l'adresse par défaut", 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:313` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `association_log('email', "Pas de destinataire pour l'email aux responsables", 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:319` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `association_log('email', "Erreur lors de l'envoi de l'email aux responsables: " . $e->getMessage(), 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:365` (appel_php, caller: `_determiner_modeles_emails_adherent`) -> `association_log('email', "Type d'action non reconnu pour l'adhérent: " . $type, 'erreur');`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:417` (appel_php, caller: `_determiner_modeles_emails_responsable`) -> `association_log('email', "Type d'action non reconnu pour les responsables: " . $type, 'erreur');`
  - `inc/fonctions/facteur_envoyer_notification_gis.php:31` (appel_php, caller: `facteur_envoyer_notification_gis`) -> `association_log('gis', 'Aucun destinataire valide pour notification GIS (meta notification_gis_config_email)', 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:28` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `association_log('notifications', "Transaction introuvable : $id_transaction", 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:33` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `association_log('notifications', "Montant de la transaction nul : $id_transaction", 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:49` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `association_log('notifications', "Auteur introuvable : $id_auteur", 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:66` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `association_log('notifications', "Compte introuvable pour la transaction : $id_transaction", 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:122` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `association_log('notifications', ['action' => 'facteur_envoyer_recu_adhesion.render', 'len' => is_string($html) ? strlen($html) : 0, 'id_transaction' => $id_transaction], 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:129` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `association_log('notifications', 'bcc_meta=' . $bcc_meta, 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:132` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `association_log('notifications', 'bcc_parsed=' . print_r($bcc_parsed, true), 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:137` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `association_log('notifications', ['action' => 'facteur_envoyer_recu_adhesion.start', 'id_auteur' => $id_auteur, 'id_transaction' => $id_transaction, 'email' => $email_adherent, 'bcc' => $bcc], 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:143` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `association_log('notifications', ['action' => 'facteur_envoyer_recu_adhesion.end', 'result' => $envoyer, 'id_transaction' => $id_transaction, 'id_auteur' => $id_auteur], 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:146` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `association_log('notifications', 'Exception dans facteur_envoyer_recu_adhesion: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_participation.php:35` (appel_php, caller: `facteur_envoyer_recu_participation`) -> `association_log('notifications', "facteur_envoyer_recu_participation: aucun des champs attendus (prenom, nom, nom_famille, nom_entreprise) n'existe dans spip_auteurs", 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_participation.php:40` (appel_php, caller: `facteur_envoyer_recu_participation`) -> `association_log('notifications', "facteur_envoyer_recu_participation: erreur sql lors de la lecture de spip_auteurs pour email=" . $email_inscrit, 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_participation.php:78` (appel_php, caller: `facteur_envoyer_recu_participation`) -> `association_log('notifications', "facteur_envoyer_recu_participation remboursement: aucun des champs attendus n'existe dans spip_auteurs", 'critique');`
  - `inc/fonctions/facteur_envoyer_recu_participation.php:83` (appel_php, caller: `facteur_envoyer_recu_participation`) -> `association_log('notifications', "facteur_envoyer_recu_participation remboursement: erreur sql lors de la lecture de spip_auteurs pour email=" . $email_inscrit, 'critique');`
  - `inc/notifications_emails.php:61` (appel_php, caller: `association_collecter_destinataires_admins`) -> `association_log('notifications', 'association_collecter_destinataires_admins: aucun destinataire admin valide trouve', 'critique');`
  - `prive/squelettes/contenu/adherents_fonctions.php:221` (appel_php, caller: `est_actif_gestion_comptes_secondaires`) -> `association_log('migration', "Migration config compte_secondaire: $config_ancienne -> $valeur_migree", 'info');`
  - `prive/squelettes/contenu/adherents_fonctions.php:468` (appel_php, caller: `preparer_liste_adherents`) -> `association_log('adherents', '=== DEBUT preparer_liste_adherents (context) ===', 'debug');`
  - `prive/squelettes/contenu/adherents_fonctions.php:469` (appel_php, caller: `preparer_liste_adherents`) -> `association_log('adherents', 'Filtres actifs: ' . var_export($context->hasActiveFilters(), true), 'debug');`
  - `prive/squelettes/contenu/adherents_fonctions.php:470` (appel_php, caller: `preparer_liste_adherents`) -> `if ($context->periode) association_log('adherents', 'Période: ' . $context->periode, 'debug');`
  - `prive/squelettes/contenu/adherents_fonctions.php:471` (appel_php, caller: `preparer_liste_adherents`) -> `if ($context->statut_interne) association_log('adherents', 'Statut: ' . $context->statut_interne, 'debug');`
  - `prive/squelettes/contenu/adherents_fonctions.php:472` (appel_php, caller: `preparer_liste_adherents`) -> `if ($context->recherche_type) association_log('adherents', 'Type recherche: ' . $context->recherche_type, 'debug');`
  - `prive/squelettes/contenu/adherents_fonctions.php:473` (appel_php, caller: `preparer_liste_adherents`) -> `association_log('adherents', 'Critères SQL combinés: ' . $criteres_sql, 'debug');`
  - `prive/squelettes/contenu/adherents_fonctions.php:480` (appel_php, caller: `preparer_liste_adherents`) -> `association_log('adherents', '=== FIN preparer_liste_adherents ===', 'debug');`
  - `prive/squelettes/contenu/adherents_fonctions.php:481` (appel_php, caller: `preparer_liste_adherents`) -> `association_log('adherents', 'Nombre adhérents retournés: ' . $nb_adherents, 'debug');`
  - `prive/squelettes/contenu/adherents_fonctions.php:483` (appel_php, caller: `preparer_liste_adherents`) -> `association_log('adherents', 'Premiers IDs: ' . implode(', ', array_slice(array_keys($id_auteurs), 0, 10)), 'debug');`
  - `prive/squelettes/contenu/adherents_fonctions.php:983` (appel_php, caller: `association_aplatir_datas_saisies`) -> `association_log('adherents', 'association_aplatir_datas_saisies: saisies_aplatir_tableau introuvable après tentative de chargement', 'info');`
  - `prive/squelettes/contenu/cotisations_fonctions.php:81` (appel_php, caller: `preparer_liste_cotisations`) -> `association_log('cotisations', '=== preparer_liste_cotisations ===', 'debug');`
  - `prive/squelettes/contenu/cotisations_fonctions.php:82` (appel_php, caller: `preparer_liste_cotisations`) -> `association_log('cotisations', 'Filtres actifs: ' . var_export($filtres, true), 'debug');`
  - `prive/squelettes/contenu/cotisations_fonctions.php:83` (appel_php, caller: `preparer_liste_cotisations`) -> `association_log('cotisations', 'WHERE: ' . $where, 'debug');`
  - `prive/squelettes/contenu/cotisations_fonctions.php:101` (appel_php, caller: `preparer_liste_cotisations`) -> `association_log('cotisations', 'Nombre de cotisations retournées: ' . $nb, 'debug');`

### `association_log_caller`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/association_log.php:129` (appel_php, caller: `association_log`) -> `$caller = association_log_caller();`

### `association_log_categories_defaut`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/configurer_association.php:1806` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `foreach (association_log_categories_defaut() as $cat => $libelle) {`
  - `formulaires/configurer_association.php:1995` (appel_php, caller: `formulaires_configurer_association_traiter_dist`) -> `foreach (array_keys(association_log_categories_defaut()) as $cat) {`

### `association_log_doit_logger`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/association_log.php:122` (appel_php, caller: `association_log`) -> `if (!association_log_doit_logger($categorie, $level)) {`

### `association_log_suffixe`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/association_log.php:140` (appel_php, caller: `association_log`) -> `spip_log($message . $ctx, 'association:' . $categorie . association_log_suffixe($level));`

### `association_mailsubscriber_formater_informations_liees`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `association_pipelines.php:595` (appel_php, caller: `association_mailsubscriber_informations_liees`) -> `$flux['data'][$nom_champs_extra] = association_mailsubscriber_formater_informations_liees($champs_fieldset_saisies, $liste_informations_segmentables);`
  - `association_pipelines.php:598` (appel_php, caller: `association_mailsubscriber_informations_liees`) -> `$flux['data'][$nom_champs_extra]=association_mailsubscriber_formater_informations_liees($champs_extra, $liste_informations_segmentables);`

### `association_mailsubscriber_informations_liees`

- Aucun appel detecte statiquement.

### `association_maintenance_bdd_run`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `formulaires/configurer_association.php:2045` (appel_php, caller: `formulaires_configurer_association_traiter_dist`) -> `$resume = association_maintenance_bdd_run(time(), $options);`
  - `formulaires/configurer_association.php:2145` (appel_php, caller: `maintenance_build_human_summary`) -> `/* Remplacement ciblé : après l'appel à association_maintenance_bdd_run() le code devait écrire le rapport JSON et l'ajouter au message.`
  - `genie/association_maintenance_bdd.php:114` (appel_php, caller: `genie_association_maintenance_bdd`) -> `$resume = association_maintenance_bdd_run(time(), $options);`

### `association_maj_112`

- Aucun appel detecte statiquement.

### `association_maj_124`

- Aucun appel detecte statiquement.

### `association_maj_142`

- Aucun appel detecte statiquement.

### `association_maj_create`

- Aucun appel detecte statiquement.

### `association_maj_spip_asso_activites`

- Aucun appel detecte statiquement.

### `association_modifier_cotisation`

- Aucun appel detecte statiquement.

### `association_modifier_operation_comptable`

- Aucun appel detecte statiquement.

### `association_module_actif`

- Total occurrences: **6**
- Repartition: `appel_php`=6
- Occurrences:
  - `association_autoriser.php:232` (appel_php, caller: `autoriser_comptes_menu_dist`) -> `if (!association_module_actif('comptes')) return false;`
  - `association_autoriser.php:242` (appel_php, caller: `autoriser_dons_menu_dist`) -> `if (!association_module_actif('dons')) return false;`
  - `association_autoriser.php:252` (appel_php, caller: `autoriser_ressources_menu_dist`) -> `if (!association_module_actif('ressources')) return false;`
  - `association_autoriser.php:262` (appel_php, caller: `autoriser_ventes_menu_dist`) -> `if (!association_module_actif('ventes')) return false;`
  - `association_autoriser.php:272` (appel_php, caller: `autoriser_prets_menu_dist`) -> `if (!association_module_actif('prets')) return false;`
  - `association_autoriser.php:282` (appel_php, caller: `autoriser_destinations_menu_dist`) -> `if (!association_module_actif('destinations')) return false;`

### `association_nbrefr`

- Total occurrences: **32**
- Repartition: `appel_php`=9, `filtre_template`=23
- Occurrences:
  - `exec/ventes.php:55` (appel_php, caller: `exec_ventes`) -> `echo '<td class="impair" style="text-align:right;">'.association_nbrefr($solde).' &euro;</td>';`
  - `exec/ventes.php:97` (appel_php, caller: `exec_ventes`) -> `. association_nbrefr($q*$data['prix_vente']).'</td>'`
  - `formulaires/editer_asso_dons.php:45` (appel_php, caller: `formulaires_editer_asso_dons_charger_dist`) -> `$contexte['argent'] = association_nbrefr($contexte['argent']);`
  - `formulaires/editer_asso_dons.php:48` (appel_php, caller: `formulaires_editer_asso_dons_charger_dist`) -> `$contexte['valeur'] = association_nbrefr($contexte['valeur']);`
  - `formulaires/editer_asso_ressources.php:32` (appel_php, caller: `formulaires_editer_asso_ressources_charger_dist`) -> `$contexte['pu'] = association_nbrefr($contexte['pu']);`
  - `formulaires/editer_asso_ventes.php:45` (appel_php, caller: `formulaires_editer_asso_ventes_charger_dist`) -> `$contexte['prix_vente'] = association_nbrefr($contexte['prix_vente']);`
  - `formulaires/editer_asso_ventes.php:48` (appel_php, caller: `formulaires_editer_asso_ventes_charger_dist`) -> `$contexte['frais_envoi'] = association_nbrefr($contexte['frais_envoi']);`
  - `formulaires/inc/destinations.php:151` (appel_php, caller: `association_editeur_destinations`) -> `. association_nbrefr(association_recupere_montant($destMontant))`
  - `inc/association_comptabilite.php:80` (appel_php, caller: `association_editeur_destinations`) -> `. association_nbrefr(association_recupere_montant($destMontant))`
  - `modeles/asso_ressources.html:41` (filtre_template, caller: `(squelette)`) -> `<td class="decimal">[(#PU\|association_nbrefr)]</td>`
  - `prive/objets/liste/item_compte.html:32` (filtre_template, caller: `(squelette)`) -> `[(#RECETTE\|>{0}\|oui)<span class="montant_recette">+[(#RECETTE\|association_nbrefr)] [(#GET{devise})]</span>]`
  - `prive/objets/liste/item_compte.html:33` (filtre_template, caller: `(squelette)`) -> `[(#DEPENSE\|>{0}\|oui)<span class="montant_depense">-[(#DEPENSE\|association_nbrefr)] [(#GET{devise})]</span>]`
  - `prive/objets/liste/item_compte_activites.html:21` (filtre_template, caller: `(squelette)`) -> `[(#RECETTE\|>{0}\|oui)<span class="montant_recette">[(#RECETTE\|association_nbrefr)] [(#GET{devise})]</span>]`
  - `prive/objets/liste/item_compte_activites.html:22` (filtre_template, caller: `(squelette)`) -> `[(#DEPENSE\|>{0}\|oui)<span class="montant_depense">[(#DEPENSE\|association_nbrefr)] [(#GET{devise})]</span>]`
  - `prive/objets/liste/table_comptabilite_activites.html:26` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main">[(#GET{compta_stats}\|table_valeur{total_recettes}\|association_nbrefr)] <span class="stat-card-currency">[(#GET{devise})]</span></div>`
  - `prive/objets/liste/table_comptabilite_activites.html:27` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-sub">[(#GET{compta_stats}\|table_valeur{nb_recettes})] <:asso:operations:> • <:asso:moy_abbrev:> [(#GET{compta_stats}\|table_valeur{avg_recette}\|association_nbrefr)]</div>`
  - `prive/objets/liste/table_comptabilite_activites.html:32` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main">[(#GET{compta_stats}\|table_valeur{total_depenses}\|association_nbrefr)] <span class="stat-card-currency">[(#GET{devise})]</span></div>`
  - `prive/objets/liste/table_comptabilite_activites.html:33` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-sub">[(#GET{compta_stats}\|table_valeur{nb_depenses})] <:asso:operations:> • <:asso:moy_abbrev:> [(#GET{compta_stats}\|table_valeur{avg_depense}\|association_nbrefr)]</div>`
  - `prive/objets/liste/table_comptabilite_activites.html:38` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main stat-solde [(#GET{compta_stats}\|table_valeur{total_solde}\|>{0}\|?{solde_positif,solde_negatif})]">[(#GET{compta_stats}\|table_valeur{total_solde}\|association_nbrefr)] <span class="stat-card-curre`
  - `prive/objets/liste/table_comptabilite_activites.html:50` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main">[(#GET{compta_stats}\|table_valeur{avg_operation}\|association_nbrefr)] <span class="stat-card-currency">[(#GET{devise})]</span></div>`
  - `prive/squelettes/contenu/analyse_compta_activites.html:52` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main">[(#GET{stats}\|table_valeur{total_recettes}\|association_nbrefr)] <span class="stat-card-currency">€</span></div>`
  - `prive/squelettes/contenu/analyse_compta_activites.html:58` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main">[(#GET{stats}\|table_valeur{total_depenses}\|association_nbrefr)] <span class="stat-card-currency">€</span></div>`
  - `prive/squelettes/contenu/analyse_compta_activites.html:64` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main stat-solde [(#GET{stats}\|table_valeur{solde}\|>{0}\|?{solde_positif,solde_negatif})]">[(#GET{stats}\|table_valeur{solde}\|association_nbrefr)] <span class="stat-card-currency">€</span></div>`
  - `prive/squelettes/contenu/analyse_compta_activites.html:76` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main">[(#GET{stats}\|table_valeur{montant_moyen}\|association_nbrefr)] <span class="stat-card-currency">€</span></div>`
  - `prive/squelettes/contenu/comptes.html:36` (filtre_template, caller: `(squelette)`) -> `[(#GET{inclure_non_validees}\|=={1}\|?{[(#GET{totaux}\|table_valeur{recettes_incluant_unval}\|association_nbrefr)],[(#GET{totaux}\|table_valeur{recettes}\|association_nbrefr)]})] <span class="stat-card-currency">[(#GET{devise}`
  - `prive/squelettes/contenu/comptes.html:36` (filtre_template, caller: `(squelette)`) -> `[(#GET{inclure_non_validees}\|=={1}\|?{[(#GET{totaux}\|table_valeur{recettes_incluant_unval}\|association_nbrefr)],[(#GET{totaux}\|table_valeur{recettes}\|association_nbrefr)]})] <span class="stat-card-currency">[(#GET{devise}`
  - `prive/squelettes/contenu/comptes.html:46` (filtre_template, caller: `(squelette)`) -> `[(#GET{inclure_non_validees}\|=={1}\|?{[(#GET{totaux}\|table_valeur{depenses_incluant_unval}\|association_nbrefr)],[(#GET{totaux}\|table_valeur{depenses}\|association_nbrefr)]})] <span class="stat-card-currency">[(#GET{devise}`
  - `prive/squelettes/contenu/comptes.html:46` (filtre_template, caller: `(squelette)`) -> `[(#GET{inclure_non_validees}\|=={1}\|?{[(#GET{totaux}\|table_valeur{depenses_incluant_unval}\|association_nbrefr)],[(#GET{totaux}\|table_valeur{depenses}\|association_nbrefr)]})] <span class="stat-card-currency">[(#GET{devise}`
  - `prive/squelettes/contenu/comptes.html:55` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main stat-solde [(#GET{totaux}\|table_valeur{solde}\|>{0}\|?{solde_positif,solde_negatif})]">[(#GET{totaux}\|table_valeur{solde}\|association_nbrefr)] <span class="stat-card-currency">[(#GET{devise})]</s`
  - `prive/squelettes/navigation/comptes.html:23` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main">[(#GET{totaux_env}\|table_valeur{recettes}\|association_nbrefr)] <span class="stat-card-currency">[(#GET{devise})]</span></div>`
  - `prive/squelettes/navigation/comptes.html:29` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main">[(#GET{totaux_env}\|table_valeur{depenses}\|association_nbrefr)] <span class="stat-card-currency">[(#GET{devise})]</span></div>`
  - `prive/squelettes/navigation/comptes.html:35` (filtre_template, caller: `(squelette)`) -> `<div class="stat-card-main stat-solde [(#GET{totaux_env}\|table_valeur{solde}\|>{0}\|?{solde_positif,solde_negatif})]">[(#GET{totaux_env}\|table_valeur{solde}\|association_nbrefr)] <span class="stat-card-currency">[(#GET{devi`

### `association_normaliser_config_liste`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:15` (appel_php, caller: `association_lire_config_liste`) -> `return association_normaliser_config_liste($cfg);`

### `association_normalize_qui`

- Total occurrences: **26**
- Repartition: `appel_php`=26
- Occurrences:
  - `association_autoriser.php:175` (appel_php, caller: `autoriser_adherents_menu_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:207` (appel_php, caller: `autoriser_cotisations_menu_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:222` (appel_php, caller: `autoriser_benevoles_menu_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:226` (appel_php, caller: `autoriser_benevoles_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:231` (appel_php, caller: `autoriser_comptes_menu_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:241` (appel_php, caller: `autoriser_dons_menu_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:251` (appel_php, caller: `autoriser_ressources_menu_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:261` (appel_php, caller: `autoriser_ventes_menu_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:271` (appel_php, caller: `autoriser_prets_menu_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:281` (appel_php, caller: `autoriser_destinations_menu_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:312` (appel_php, caller: `autoriser_comptes_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:357` (appel_php, caller: `autoriser_asso_comptes_creer_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:376` (appel_php, caller: `autorite_autoriser_auteurs_menu`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:392` (appel_php, caller: `autorite_autoriser_auteur_voir`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:404` (appel_php, caller: `autoriser_asso_modifier`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:504` (appel_php, caller: `autoriser_modifier_asso_compte_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:550` (appel_php, caller: `autoriser_creer_asso_compte_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:632` (appel_php, caller: `autoriser_article_creerevenementdans`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:652` (appel_php, caller: `autoriser_modifier_evenement_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:700` (appel_php, caller: `autoriser_newsletter_generer`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:720` (appel_php, caller: `autoriser_newsletter_envoyer`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:737` (appel_php, caller: `autoriser_newsletter_instituer`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:806` (appel_php, caller: `autoriser_modifier_article_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:857` (appel_php, caller: `autoriser_newsletter_modifier`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:890` (appel_php, caller: `autoriser_voir_activites_dist`) -> `$qui = association_normalize_qui($qui);`
  - `association_autoriser.php:932` (appel_php, caller: `autoriser_onglet_activites_dist`) -> `$qui = association_normalize_qui($qui);`

### `association_notifications_destinataires`

- Aucun appel detecte statiquement.

### `association_obtenir_delais_echeance`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_taches_generales.php:68` (appel_php, caller: `genie_association_taches_generales`) -> `$delais_cache[$type_adherent] = association_obtenir_delais_echeance($type_adherent);`

### `association_obtenir_evenement_contexte`

- Total occurrences: **6**
- Repartition: `appel_php`=6
- Occurrences:
  - `association_autoriser.php:365` (appel_php, caller: `autoriser_asso_comptes_creer_dist`) -> `$id_evenement = association_obtenir_evenement_contexte(0, is_array($opt) ? $opt : array());`
  - `association_autoriser.php:530` (appel_php, caller: `autoriser_modifier_asso_compte_dist`) -> `$id_evenement = association_obtenir_evenement_contexte($id_compte, is_array($opt) ? $opt : array());`
  - `association_autoriser.php:561` (appel_php, caller: `autoriser_creer_asso_compte_dist`) -> `$id_evenement = association_obtenir_evenement_contexte(0, is_array($opt) ? $opt : array());`
  - `formulaires/editer_asso_comptes.php:195` (appel_php, caller: `formulaires_editer_asso_comptes_charger_dist`) -> `$id_evenement_ctx = association_obtenir_evenement_contexte($id_compte_int, array());`
  - `formulaires/editer_asso_comptes.php:273` (appel_php, caller: `formulaires_editer_asso_comptes_verifier_dist`) -> `$id_evenement_ctx = association_obtenir_evenement_contexte($id_compte_int, array());`
  - `formulaires/editer_asso_comptes.php:390` (appel_php, caller: `formulaires_editer_asso_comptes_traiter_dist`) -> `$id_evenement_ctx = association_obtenir_evenement_contexte($id_compte_int, array());`

### `association_onglets`

- Total occurrences: **29**
- Repartition: `appel_php`=29
- Occurrences:
  - `exec/action_activites.php:30` (appel_php, caller: `exec_action_activites`) -> `association_onglets('activites');`
  - `exec/action_adherents.php:32` (appel_php, caller: `exec_action_adherents_args`) -> `association_onglets('adherents');`
  - `exec/action_comptes.php:33` (appel_php, caller: `exec_action_comptes_args`) -> `association_onglets('comptes');`
  - `exec/action_email_collectif_activite.php:36` (appel_php, caller: `exec_action_email_collectif_activite`) -> `association_onglets('activites');`
  - `exec/action_email_collectif_adherent.php:25` (appel_php, caller: `exec_action_email_collectif_adherent`) -> `association_onglets('adherents');`
  - `exec/action_email_relances.php:25` (appel_php, caller: `exec_action_email_relances`) -> `association_onglets('adherents');`
  - `exec/action_prets.php:40` (appel_php, caller: `exec_action_prets`) -> `association_onglets();`
  - `exec/action_relances.php:41` (appel_php, caller: `exec_action_relances`) -> `association_onglets();`
  - `exec/action_ressources.php:34` (appel_php, caller: `exec_action_ressources_args`) -> `association_onglets();`
  - `exec/action_ventes.php:30` (appel_php, caller: `exec_action_ventes`) -> `association_onglets();`
  - `exec/action_voir.php:27` (appel_php, caller: `exec_action_voir`) -> `association_onglets('adherents');`
  - `exec/activites.php:23` (appel_php, caller: `exec_activites`) -> `association_onglets('activites');`
  - `exec/adherents_bck.php:34` (appel_php, caller: `exec_adherents`) -> `association_onglets('adherents');`
  - `exec/bilan.php:47` (appel_php, caller: `exec_bilan`) -> `association_onglets('comptes');`
  - `exec/configurer_visuel.php:23` (appel_php, caller: `exec_configurer_visuel`) -> `association_onglets(_T('asso:titre_onglet_comptes'));`
  - `exec/dons.php:27` (appel_php, caller: `exec_dons`) -> `association_onglets(_T('asso:titre_onglet_dons'));`
  - `exec/edit_cotisation.php:36` (appel_php, caller: `exec_edit_cotisation`) -> `association_onglets('adherents');`
  - `exec/edit_don.php:33` (appel_php, caller: `exec_edit_don`) -> `association_onglets(_T('asso:titre_onglet_dons'));`
  - `exec/edit_email_collectif_activite.php:23` (appel_php, caller: `exec_edit_email_collectif_activite`) -> `association_onglets('activites');`
  - `exec/edit_email_collectif_adherent.php:21` (appel_php, caller: `exec_edit_email_collectif_adherent`) -> `association_onglets('adherents');`
  - `exec/edit_labels.php:32` (appel_php, caller: `exec_edit_labels`) -> `association_onglets();`
  - `exec/edit_mail.php:22` (appel_php, caller: `exec_edit_mail`) -> `association_onglets('adherents');`
  - `exec/edit_pret.php:60` (appel_php, caller: `exec_edit_pret`) -> `association_onglets('prets');`
  - `exec/edit_relances.php:28` (appel_php, caller: `exec_edit_relances`) -> `association_onglets('adherents');`
  - `exec/edit_vente.php:29` (appel_php, caller: `exec_edit_vente`) -> `association_onglets('ventes');`
  - `exec/prets.php:29` (appel_php, caller: `exec_prets`) -> `association_onglets('prets');`
  - `exec/ventes.php:34` (appel_php, caller: `exec_ventes`) -> `association_onglets('ventes');`
  - `exec/voir_adherent.php:22` (appel_php, caller: `exec_voir_adherent`) -> `association_onglets('adherents');`
  - `inc/page.php:29` (appel_php, caller: `page_no_fond`) -> `association_onglets($selected_onglet);`

### `association_peut_acceder_evenement`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_autoriser.php:664` (appel_php, caller: `autoriser_modifier_evenement_dist`) -> `$res = association_peut_acceder_evenement($qui, intval($id));`

### `association_post_edition`

- Aucun appel detecte statiquement.

### `association_post_insertion`

- Aucun appel detecte statiquement.

### `association_pre_edition`

- Aucun appel detecte statiquement.

### `association_pre_insertion`

- Aucun appel detecte statiquement.

### `association_preparer_donnees_communes`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:16` (appel_php, caller: `association_declarer_champs_extras_impl`) -> `$donnees_communes = association_preparer_donnees_communes();`

### `association_recherche_avancee_reset_demandee`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/adherents_search_context.php:68` (appel_php, caller: `(global)`) -> `if (association_recherche_avancee_reset_demandee()) {`

### `association_recupere_montant`

- Total occurrences: **22**
- Repartition: `appel_php`=22
- Occurrences:
  - `action/editer_asso_comptes.php:29` (appel_php, caller: `action_editer_asso_comptes`) -> `$recette = association_recupere_montant(_request('montant'));`
  - `action/editer_asso_comptes.php:33` (appel_php, caller: `action_editer_asso_comptes`) -> `$depense = association_recupere_montant(_request('montant'));`
  - `action/editer_asso_dons.php:37` (appel_php, caller: `action_editer_asso_dons`) -> `$argent = association_recupere_montant(_request('argent'));`
  - `action/editer_asso_dons.php:39` (appel_php, caller: `action_editer_asso_dons`) -> `$valeur = association_recupere_montant(_request('valeur'));`
  - `action/editer_asso_ressources.php:23` (appel_php, caller: `action_editer_asso_ressources`) -> `$pu = association_recupere_montant(_request('pu'));`
  - `action/editer_asso_ventes.php:32` (appel_php, caller: `action_editer_asso_ventes`) -> `$quantite = association_recupere_montant(_request('quantite'));`
  - `action/editer_asso_ventes.php:35` (appel_php, caller: `action_editer_asso_ventes`) -> `$frais_envoi = association_recupere_montant(_request('frais_envoi'));`
  - `action/editer_asso_ventes.php:36` (appel_php, caller: `action_editer_asso_ventes`) -> `$prix_vente =  association_recupere_montant(_request('prix_vente'));`
  - `formulaires/editer_asso_dons.php:62` (appel_php, caller: `formulaires_editer_asso_dons_verifier_dist`) -> `$argent = association_recupere_montant(_request('argent'));`
  - `formulaires/editer_asso_dons.php:63` (appel_php, caller: `formulaires_editer_asso_dons_verifier_dist`) -> `$valeur = association_recupere_montant(_request('valeur'));`
  - `formulaires/editer_asso_ressources.php:43` (appel_php, caller: `formulaires_editer_asso_ressources_verifier_dist`) -> `$pu = association_recupere_montant(_request('pu'));`
  - `formulaires/editer_asso_ventes.php:63` (appel_php, caller: `formulaires_editer_asso_ventes_verifier_dist`) -> `$prix_vente = association_recupere_montant(_request('prix_vente'));`
  - `formulaires/editer_asso_ventes.php:64` (appel_php, caller: `formulaires_editer_asso_ventes_verifier_dist`) -> `$frais_envoi = association_recupere_montant(_request('frais_envoi'));`
  - `formulaires/editer_asso_ventes.php:65` (appel_php, caller: `formulaires_editer_asso_ventes_verifier_dist`) -> `$quantite = association_recupere_montant(_request('quantite'));`
  - `formulaires/inc/destinations.php:97` (appel_php, caller: `_verifier_montant_destinations`) -> `$total_destination += association_recupere_montant($toutesDestinationsMontants[$id]); /* les montants sont dans un autre tableau aux meme cles */`
  - `formulaires/inc/destinations.php:108` (appel_php, caller: `_verifier_montant_destinations`) -> `$montant = association_recupere_montant($toutesDestinationsMontants[1]);`
  - `formulaires/inc/destinations.php:151` (appel_php, caller: `association_editeur_destinations`) -> `. association_nbrefr(association_recupere_montant($destMontant))`
  - `inc/association_comptabilite.php:80` (appel_php, caller: `association_editeur_destinations`) -> `. association_nbrefr(association_recupere_montant($destMontant))`
  - `inc/association_comptabilite.php:232` (appel_php, caller: `association_verifier_montant_destinations`) -> `$total_destination += association_recupere_montant($toutesDestinationsMontants[$id]); /* les montants sont dans un autre tableau aux meme cles */`
  - `inc/association_comptabilite.php:243` (appel_php, caller: `association_verifier_montant_destinations`) -> `$montant = association_recupere_montant($toutesDestinationsMontants[1]);`
  - `inc/association_comptabilite.php:273` (appel_php, caller: `association_ajouter_destinations_comptables`) -> `$montant = association_recupere_montant($toutesDestinationsMontants[$id]);	/* le tableau des montants a des cles indentique a celui des id */`
  - `inc/destinations.php:46` (appel_php, caller: `ajouter_destinations`) -> `$montant = association_recupere_montant($destinationMontants[$index]);`

### `association_retour`

- Total occurrences: **19**
- Repartition: `appel_php`=19
- Occurrences:
  - `exec/action_activites.php:41` (appel_php, caller: `exec_action_activites`) -> `echo association_retour();`
  - `exec/action_email_collectif_activite.php:41` (appel_php, caller: `exec_action_email_collectif_activite`) -> `echo association_retour();`
  - `exec/action_email_collectif_adherent.php:30` (appel_php, caller: `exec_action_email_collectif_adherent`) -> `echo association_retour();`
  - `exec/action_email_relances.php:30` (appel_php, caller: `exec_action_email_relances`) -> `echo association_retour();`
  - `exec/action_prets.php:54` (appel_php, caller: `exec_action_prets`) -> `echo association_retour();`
  - `exec/action_relances.php:46` (appel_php, caller: `exec_action_relances`) -> `echo association_retour();`
  - `exec/action_ressources.php:49` (appel_php, caller: `exec_action_ressources_args`) -> `echo association_retour();`
  - `exec/action_ventes.php:37` (appel_php, caller: `exec_action_ventes`) -> `echo association_retour();`
  - `exec/action_voir.php:32` (appel_php, caller: `exec_action_voir`) -> `echo association_retour();`
  - `exec/activites.php:44` (appel_php, caller: `exec_activites`) -> `//echo association_retour();`
  - `exec/bilan.php:76` (appel_php, caller: `exec_bilan`) -> `echo association_retour(); // from bilan to comptes, since we have bilan under comptes`
  - `exec/configurer_visuel.php:32` (appel_php, caller: `exec_configurer_visuel`) -> `echo association_retour();`
  - `exec/edit_don.php:44` (appel_php, caller: `exec_edit_don`) -> `echo association_retour();`
  - `exec/edit_email_collectif_activite.php:30` (appel_php, caller: `exec_edit_email_collectif_activite`) -> `echo association_retour();`
  - `exec/edit_labels.php:40` (appel_php, caller: `exec_edit_labels`) -> `echo association_retour();`
  - `exec/edit_pret.php:75` (appel_php, caller: `exec_edit_pret`) -> `echo association_retour();`
  - `exec/edit_vente.php:44` (appel_php, caller: `exec_edit_vente`) -> `echo association_retour();`
  - `exec/prets.php:51` (appel_php, caller: `exec_prets`) -> `echo association_retour();`
  - `inc/page.php:38` (appel_php, caller: `page_no_fond`) -> `echo association_retour();`

### `association_saisies_lister_disponibles`

- Aucun appel detecte statiquement.

### `association_sync_repetitions_tarifs`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `association_pipelines.php:164` (appel_php, caller: `association_formulaire_traiter`) -> `association_sync_repetitions_tarifs(intval($id_evenement));`
  - `association_pipelines.php:215` (appel_php, caller: `association_post_edition`) -> `association_sync_repetitions_tarifs(intval($id_evenement));`

### `association_taches_generales_cron`

- Aucun appel detecte statiquement.

### `association_telfr`

- Total occurrences: **2**
- Repartition: `filtre_template`=2
- Occurrences:
  - `modeles/asso_membres_responsables.php:22` (filtre_template, caller: `(global)`) -> `<td>[<a class="spip_out tel" href="tel:[(#TELEPHONE\|replace{\D})]">(#TELEPHONE\|association_telfr)</a>]</td>`
  - `modeles/asso_membres_responsables.php:23` (filtre_template, caller: `(global)`) -> `<td>[<a class="spip_out tel" href="tel:[(#MOBILE\|replace{\D})]">(#MOBILE\|association_telfr)</a>]</td>`

### `association_toutes_destination_option_list`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/association_comptabilite.php:57` (appel_php, caller: `association_editeur_destinations`) -> `$liste_destination = association_toutes_destination_option_list();`

### `association_trig_bank_notifier_reglement`

- Aucun appel detecte statiquement.

### `association_upgrade`

- Aucun appel detecte statiquement.

### `association_validite_calculator`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/cotisations.php:943` (appel_php, caller: `activer_adherent`) -> `$validite = association_validite_calculator($id_auteur);`

### `association_verifier_date`

- Total occurrences: **7**
- Repartition: `appel_php`=7
- Occurrences:
  - `formulaires/editer_asso_comptes.php:358` (appel_php, caller: `formulaires_editer_asso_comptes_verifier_dist`) -> `if ($erreur_date = association_verifier_date(_request('date'))) {`
  - `formulaires/editer_asso_dons.php:81` (appel_php, caller: `formulaires_editer_asso_dons_verifier_dist`) -> `if ($erreur_date = association_verifier_date(_request('date_don'))) {`
  - `formulaires/editer_asso_membres.php:38` (appel_php, caller: `formulaires_editer_asso_membres_verifier_dist`) -> `if ($erreur_validite = association_verifier_date(_request($champ))) {`
  - `formulaires/editer_asso_plan.php:57` (appel_php, caller: `formulaires_editer_asso_plan_verifier_dist`) -> `if ($erreur_date = association_verifier_date(_request('date_anterieure'))) {`
  - `formulaires/editer_asso_ressources.php:50` (appel_php, caller: `formulaires_editer_asso_ressources_verifier_dist`) -> `if ($erreur_date = association_verifier_date(_request('date_acquisition'))) {`
  - `formulaires/editer_asso_ventes.php:82` (appel_php, caller: `formulaires_editer_asso_ventes_verifier_dist`) -> `if ($erreur_date = association_verifier_date(_request('date_vente'))) {`
  - `formulaires/editer_asso_ventes.php:85` (appel_php, caller: `formulaires_editer_asso_ventes_verifier_dist`) -> `if ($erreur_date = association_verifier_date(_request('date_envoi'))) {`

### `association_verifier_montant_destinations`

- Aucun appel detecte statiquement.

### `association_vider_tables`

- Aucun appel detecte statiquement.

### `associer_liste_diffusion_entreprise`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/cotisations.php:1022` (appel_php, caller: `activer_adherent`) -> `associer_liste_diffusion_entreprise($id_auteur, $query_auteur);`

### `auteur_lien_messagerie`

- Total occurrences: **2**
- Repartition: `filtre_template`=2
- Occurrences:
  - `prive/objets/liste/auteurs.html:47` (filtre_template, caller: `(squelette)`) -> `<td class="messagerie">[<a href="(#ID_AUTEUR\|auteur_lien_messagerie{#EN_LIGNE,#STATUT,#IMESSAGE})">[(#CHEMIN{images/m_envoi.gif}\|balise_img{<:info_envoyer_message_prive:>})]</a>]</td>`
  - `prive/objets/liste/visiteurs.html:45` (filtre_template, caller: `(squelette)`) -> `<td class="messagerie">[<a href="(#ID_AUTEUR\|auteur_lien_messagerie{#EN_LIGNE,#STATUT,#IMESSAGE})">[(#CHEMIN{images/m_envoi.gif}\|balise_img{<:info_envoyer_message_prive:>})]</a>]</td>`

### `auteurs_valeurs_acceptables`

- Aucun appel detecte statiquement.

### `autoriser_activites_associer_dist`

- Aucun appel detecte statiquement.

### `autoriser_activites_menu_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_autoriser.php:298` (appel_php, caller: `autoriser_activites_associer_dist`) -> `return autoriser_activites_menu_dist($faire, $type, $id, $qui, $opt);`

### `autoriser_adherents_associer_dist`

- Aucun appel detecte statiquement.

### `autoriser_adherents_menu_dist`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `association_autoriser.php:292` (appel_php, caller: `autoriser_adherents_associer_dist`) -> `return autoriser_adherents_menu_dist($faire, $type, $id, $qui, $opt);`
  - `association_autoriser.php:308` (appel_php, caller: `autoriser_voiradherent_associer_dist`) -> `return autoriser_adherents_menu_dist($faire, $type, $id, $qui, $opt);`

### `autoriser_article_creerevenementdans`

- Aucun appel detecte statiquement.

### `autoriser_asso_comptes_creer_dist`

- Aucun appel detecte statiquement.

### `autoriser_asso_modifier`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `association_autoriser.php:483` (appel_php, caller: `autoriser_modifier_asso`) -> `$res = autoriser_asso_modifier($faire, $type, $id, $qui, $opt);`
  - `association_autoriser.php:491` (appel_php, caller: `autoriser_modifier_asso_dist`) -> `$res = autoriser_asso_modifier($faire, $type, $id, $qui, $opt);`

### `autoriser_assocompte_creer_dist`

- Aucun appel detecte statiquement.

### `autoriser_assocompte_modifier_dist`

- Aucun appel detecte statiquement.

### `autoriser_benevoles_dist`

- Aucun appel detecte statiquement.

### `autoriser_benevoles_menu_dist`

- Aucun appel detecte statiquement.

### `autoriser_comptes_associer_dist`

- Aucun appel detecte statiquement.

### `autoriser_comptes_dist`

- Aucun appel detecte statiquement.

### `autoriser_comptes_menu_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_autoriser.php:295` (appel_php, caller: `autoriser_comptes_associer_dist`) -> `return autoriser_comptes_menu_dist($faire, $type, $id, $qui, $opt);`

### `autoriser_cotisations_menu_dist`

- Aucun appel detecte statiquement.

### `autoriser_creer_asso_compte_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_autoriser.php:683` (appel_php, caller: `autoriser_assocompte_creer_dist`) -> `$res = autoriser_creer_asso_compte_dist($faire, $type, $id, $qui, $opt);`

### `autoriser_destinations_menu_dist`

- Aucun appel detecte statiquement.

### `autoriser_dons_associer_dist`

- Aucun appel detecte statiquement.

### `autoriser_dons_menu_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_autoriser.php:301` (appel_php, caller: `autoriser_dons_associer_dist`) -> `return autoriser_dons_menu_dist($faire, $type, $id, $qui, $opt);`

### `autoriser_joindredocument`

- Aucun appel detecte statiquement.

### `autoriser_modifier_article_dist`

- Aucun appel detecte statiquement.

### `autoriser_modifier_asso`

- Aucun appel detecte statiquement.

### `autoriser_modifier_asso_compte_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_autoriser.php:676` (appel_php, caller: `autoriser_assocompte_modifier_dist`) -> `$res = autoriser_modifier_asso_compte_dist($faire, $type, $id, $qui, $opt);`

### `autoriser_modifier_asso_dist`

- Aucun appel detecte statiquement.

### `autoriser_modifier_evenement_dist`

- Aucun appel detecte statiquement.

### `autoriser_newsletter_envoyer`

- Aucun appel detecte statiquement.

### `autoriser_newsletter_generer`

- Aucun appel detecte statiquement.

### `autoriser_newsletter_instituer`

- Aucun appel detecte statiquement.

### `autoriser_newsletter_modifier`

- Aucun appel detecte statiquement.

### `autoriser_onglet_activites_dist`

- Aucun appel detecte statiquement.

### `autoriser_page`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `balise/autoriser_page.php:24` (appel_php, caller: `balise_AUTORISER_PAGE_stat`) -> `return autoriser_page($page);`

### `autoriser_page_else_minipres`

- Total occurrences: **16**
- Repartition: `appel_php`=16
- Occurrences:
  - `exec/action_comptes.php:20` (appel_php, caller: `exec_action_comptes`) -> `if (!autoriser_page_else_minipres('comptes')) {`
  - `exec/action_destinations.php:21` (appel_php, caller: `exec_action_destinations`) -> `if (!autoriser_page_else_minipres('destinations')) {`
  - `exec/action_plan.php:21` (appel_php, caller: `exec_action_plan`) -> `if (!autoriser_page_else_minipres('comptes')) {`
  - `exec/bilan.php:24` (appel_php, caller: `exec_bilan`) -> `if (!autoriser_page_else_minipres('comptes')) {`
  - `exec/comptes.php:22` (appel_php, caller: `(global)`) -> `if (!autoriser_page_else_minipres('comptes')) {`
  - `exec/csv_activites.php:20` (appel_php, caller: `exec_csv_activites`) -> `if (!autoriser_page_else_minipres('activites')) {`
  - `exec/destinations.php:21` (appel_php, caller: `exec_destinations`) -> `if (!autoriser_page_else_minipres('destinations')) {`
  - `exec/edit_compte.php:22` (appel_php, caller: `exec_editer_asso_comptes`) -> `if (!autoriser_page_else_minipres('comptes')) {`
  - `exec/edit_destination.php:20` (appel_php, caller: `exec_edit_destination`) -> `if (!autoriser_page_else_minipres('destinations')) {`
  - `exec/edit_plan.php:20` (appel_php, caller: `exec_edit_plan`) -> `if (!autoriser_page_else_minipres('comptes')) {`
  - `exec/edit_ressource.php:21` (appel_php, caller: `exec_edit_ressource`) -> `if (!autoriser_page_else_minipres('prets')) {`
  - `exec/edit_vente.php:20` (appel_php, caller: `exec_edit_vente`) -> `if (!autoriser_page_else_minipres('ventes')) {`
  - `exec/plan_comptable.php:20` (appel_php, caller: `exec_plan_comptable`) -> `if (!autoriser_page_else_minipres('comptes')) {`
  - `exec/prets.php:21` (appel_php, caller: `exec_prets`) -> `if (!autoriser_page_else_minipres('prets')) {`
  - `exec/ressources.php:21` (appel_php, caller: `exec_ressources`) -> `if (!autoriser_page_else_minipres('ressources')) {`
  - `exec/ventes.php:21` (appel_php, caller: `exec_ventes`) -> `if (!autoriser_page_else_minipres('ventes')) {`

### `autoriser_prets_menu_dist`

- Aucun appel detecte statiquement.

### `autoriser_publierdans_dist`

- Aucun appel detecte statiquement.

### `autoriser_ressources_associer_dist`

- Aucun appel detecte statiquement.

### `autoriser_ressources_menu_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_autoriser.php:304` (appel_php, caller: `autoriser_ressources_associer_dist`) -> `return autoriser_ressources_menu_dist($faire, $type, $id, $qui, $opt);`

### `autoriser_ventes_menu_dist`

- Aucun appel detecte statiquement.

### `autoriser_voir_activites_dist`

- Aucun appel detecte statiquement.

### `autoriser_voiradherent_associer_dist`

- Aucun appel detecte statiquement.

### `autorite_autoriser_auteur_voir`

- Aucun appel detecte statiquement.

### `autorite_autoriser_auteurs_menu`

- Aucun appel detecte statiquement.

### `balise_AUTORISER_PAGE`

- Aucun appel detecte statiquement.

### `balise_AUTORISER_PAGE_stat`

- Aucun appel detecte statiquement.

### `balise_COMPTEUR_ARTICLES_dist`

- Aucun appel detecte statiquement.

### `balise_CONFIGURER_METAS_dyn`

- Aucun appel detecte statiquement.

### `balise_EDITEUR_DESTINATIONS_dist`

- Aucun appel detecte statiquement.

### `balise_EDITEUR_DESTINATIONS_dyn`

- Aucun appel detecte statiquement.

### `balise_META`

- Aucun appel detecte statiquement.

### `balise_ONGLETS_ASSOCIATION_dist`

- Aucun appel detecte statiquement.

### `balise_ONGLETS_ASSOCIATION_dyn`

- Aucun appel detecte statiquement.

### `balise_ONGLETS_ASSOCIATION_stat`

- Aucun appel detecte statiquement.

### `bilan_encaisse`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/bilan.php:160` (appel_php, caller: `exec_bilan`) -> `bilan_encaisse();`

### `boite_info`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/ressources.php:28` (appel_php, caller: `exec_ressources`) -> `boite_info(),`

### `build_pdf`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/pdf_fiscal.php:56` (appel_php, caller: `exec_pdf_fiscal`) -> `else build_pdf($montants, $nom, $adresse, $cp, $annee, $ville, "$annee-$id_auteur");`

### `cadre_relief`

- Total occurrences: **5**
- Repartition: `appel_php`=5
- Occurrences:
  - `exec/action_destinations.php:32` (appel_php, caller: `exec_action_destinations`) -> `cadre_relief($id_destination)`
  - `exec/action_plan.php:32` (appel_php, caller: `exec_action_plan`) -> `cadre_relief($id_plan)`
  - `exec/destinations.php:30` (appel_php, caller: `exec_destinations`) -> `cadre_relief(),`
  - `exec/plan_comptable.php:34` (appel_php, caller: `exec_plan_comptable`) -> `cadre_relief();`
  - `exec/ressources.php:34` (appel_php, caller: `exec_ressources`) -> `cadre_relief();`

### `calculer_dates_scolaires`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/cotisations.php:1290` (appel_php, caller: `identification_contexte_inscription`) -> `$dates_scolaires = calculer_dates_scolaires();`

### `calculer_montant_total`

- Total occurrences: **7**
- Repartition: `appel_php`=7
- Occurrences:
  - `formulaires/inc/inscription_evenement.php:973` (appel_php, caller: `generer_recapitulatif_multi`) -> `$calculer_montant_total = calculer_montant_total(`
  - `formulaires/inscription_evenement.php:619` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `$calculer_montant_total = calculer_montant_total($id_evenement,$data_form['categorie']);`
  - `formulaires/inscription_evenement_multi.php:529` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `$calculer_montant_total = calculer_montant_total($id_evenement,$data_form['categorie_result']);`
  - `formulaires/inscription_evenement_multi.php:556` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `$calculer_montant_total = calculer_montant_total($id_evenement,$data_form['categorie_result']);`
  - `formulaires/inscription_evenement_multi_public.php:524` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$calculer_montant_total = calculer_montant_total($id_evenement, $data_form['categorie_result']);`
  - `formulaires/inscription_evenement_multi_public.php:563` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$calculer_montant_total = calculer_montant_total($id_evenement, $data_form['categorie_result']);`
  - `formulaires/inscription_evenement_public.php:574` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `if ($affichage_dans_activites['payant'] == true) { //$calculer_montant_total = calculer_montant_total($id_evenement,$data_form['categorie']);`

### `champs_saisie_nb_inscrits`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inscription_evenement_multi.php:230` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$saisies_selection_nb_inscrits= champs_saisie_nb_inscrits($gestions_places,$affichage_dans_activites);`
  - `formulaires/inscription_evenement_multi_public.php:129` (appel_php, caller: `formulaires_inscription_evenement_multi_public_saisies`) -> `$saisies_selection_nb_inscrits = champs_saisie_nb_inscrits($gestions_places, $affichage_dans_activites);`

### `champs_saisies_famille`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inscription_evenement_multi.php:214` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$saisies_info_supplementaire_famille = champs_saisies_famille($id_membre,$affichage_dans_activites);`
  - `formulaires/inscription_evenement_multi_public.php:116` (appel_php, caller: `formulaires_inscription_evenement_multi_public_saisies`) -> `$saisies_info_supplementaire_famille = champs_saisies_famille($id_auteur_connecte, $affichage_dans_activites);`

### `champs_saisies_info_supplementaire`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inc/inscription_evenement_saisies.php:148` (appel_php, caller: `champs_saisies_inscrits`) -> `$champs_saisies_info_supplementaire =  champs_saisies_info_supplementaire($id_inscrit,$affichage_dans_activites['info_supplementaire'],$i,$defaut);`
  - `formulaires/inc/inscription_evenement_saisies.php:234` (appel_php, caller: `champs_saisies_famille`) -> `$champs_saisies_info_supplementaire =  champs_saisies_info_supplementaire($cle,$affichage_dans_activites['info_supplementaire'],++$i,$famille);`

### `champs_saisies_inscrits`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `formulaires/inscription_evenement_multi.php:245` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$saisies_accompagnant = champs_saisies_inscrits($affichage_dans_activites,_request('nb_inscrits'),$info_auteur);`
  - `formulaires/inscription_evenement_multi.php:247` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$saisies_accompagnant = champs_saisies_inscrits($affichage_dans_activites,1,$info_auteur);`
  - `formulaires/inscription_evenement_multi_public.php:143` (appel_php, caller: `formulaires_inscription_evenement_multi_public_saisies`) -> `$saisies_accompagnant = champs_saisies_inscrits($affichage_dans_activites,$nb_inscrits, $info_auteur_connecte );`
  - `formulaires/inscription_evenement_multi_public.php:156` (appel_php, caller: `formulaires_inscription_evenement_multi_public_saisies`) -> `$saisies_accompagnant = champs_saisies_inscrits($affichage_dans_activites,1, $info_auteur_connecte);`

### `champs_saisies_selection_membres_famille`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inscription_evenement_multi.php:199` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$saisies_selection_membre_famille = champs_saisies_selection_membres_famille($data_famille,$affichage_dans_activites['accompagnants']);`
  - `formulaires/inscription_evenement_multi_public.php:103` (appel_php, caller: `formulaires_inscription_evenement_multi_public_saisies`) -> `$saisies_selection_membre_famille = champs_saisies_selection_membres_famille($data_famille, $affichage_dans_activites['accompagnants']);`

### `champs_saisies_tarifs`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inc/inscription_evenement_saisies.php:161` (appel_php, caller: `champs_saisies_inscrits`) -> `$champs_saisies_saisies_tarifs = champs_saisies_tarifs($id_inscrit,'inscrit_1',$array_type_adherents,$tableau_categories,$i);`
  - `formulaires/inc/inscription_evenement_saisies.php:239` (appel_php, caller: `champs_saisies_famille`) -> `$champs_saisies_saisies_tarifs = champs_saisies_tarifs($cle,$premier_inscrit[0],$array_type_adherents,$tableau_categories,$i);`

### `changer_statut_cotisation`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/api_cotisations.php:311` (appel_php, caller: `api_traiter_cotisation`) -> `changer_statut_cotisation($id_compte, $origine, $notifier);`

### `choisir_meta`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `balise/meta.php:18` (appel_php, caller: `balise_META`) -> `$p->code = 'choisir_meta(' . $arg . ')';`

### `comparer_modification`

- Aucun appel detecte statiquement.

### `compte_cotisation`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/api_cotisations.php:238` (appel_php, caller: `api_traiter_cotisation`) -> `$id_compte = compte_cotisation(`

### `compte_don`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/editer_asso_dons.php:72` (appel_php, caller: `action_editer_asso_dons`) -> `compte_don($date_don, $argent, $journal, $bienfaiteur, $id_don);`

### `compte_vente`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `action/editer_asso_ventes.php:103` (appel_php, caller: `ventes_insert`) -> `compte_vente($date_vente, $recette+$frais_envoi, $justification, $journal, $id_vente);`
  - `action/editer_asso_ventes.php:105` (appel_php, caller: `ventes_insert`) -> `compte_vente($date_vente, $recette, $justification, $journal, $id_vente);`

### `compte_vente_frais_envoi`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/editer_asso_ventes.php:106` (appel_php, caller: `ventes_insert`) -> `compte_vente_frais_envoi($date_vente, $frais_envoi, $justification, $journal, $id_vente);`

### `create_destination_map_for_montant`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `inc/comptes.php:651` (appel_php, caller: `compte_cotisation`) -> `create_destination_map_for_montant('dc_cotisations', $montant_choisi) // destination_map`
  - `inc/comptes.php:758` (appel_php, caller: `modifier_compte_cotisation`) -> `create_destination_map_for_montant('dc_cotisations', $montant)`

### `create_destination_row`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/destinations.php:69` (appel_php, caller: `get_destination_rows`) -> `$rows .= create_destination_row($data, $id_destination);`

### `critere_compteur_articles_filtres_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/objets/liste/auteurs_fonctions.php:64` (appel_php, caller: `critere_compteur_articles_filtres_dist`) -> `* @see critere_compteur_articles_filtres_dist()`

### `decoder_fichier_json`

- Total occurrences: **6**
- Repartition: `appel_php`=6
- Occurrences:
  - `formulaires/importer_destination_comptable.php:11` (appel_php, caller: `formulaires_importer_destination_comptable_saisies_dist`) -> `$json_data=decoder_fichier_json('json/destinations_comptables_2024.json');`
  - `formulaires/importer_destination_comptable.php:12` (appel_php, caller: `formulaires_importer_destination_comptable_saisies_dist`) -> `//$json_data=decoder_fichier_json('json/destination_comptable_complet_2024.json');`
  - `formulaires/importer_destination_comptable.php:79` (appel_php, caller: `formulaires_importer_destination_comptable_traiter_dist`) -> `//$json_data=decoder_fichier_json('json/destination_comptable_complet_2024.json');`
  - `formulaires/importer_destination_comptable.php:80` (appel_php, caller: `formulaires_importer_destination_comptable_traiter_dist`) -> `$json_data=decoder_fichier_json('json/destinations_comptables_2024.json');`
  - `formulaires/importer_plan_comptable.php:19` (appel_php, caller: `formulaires_importer_plan_comptable_saisies_dist`) -> `$json_data = decoder_fichier_json('json/plan_comptable_2024.json');`
  - `formulaires/importer_plan_comptable.php:88` (appel_php, caller: `formulaires_importer_plan_comptable_traiter_dist`) -> `$json_data = decoder_fichier_json('json/plan_comptable_2024.json');`

### `default_destination_is_set`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/destinations.php:76` (appel_php, caller: `create_destination_map_for_montant`) -> `if (destinations_are_enabled() and default_destination_is_set($dc_name)) {`

### `desactiver_privileges_adherent`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `genie/association_taches_generales.php:77` (appel_php, caller: `genie_association_taches_generales`) -> `desactiver_privileges_adherent($id_auteur);`

### `deserialize_values`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/configurer_association.php:1849` (appel_php, caller: `formulaires_configurer_association_charger_dist`) -> `$contexte = deserialize_values($GLOBALS['association_metas']);`

### `destination_insert`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/ajouter_destinations.php:22` (appel_php, caller: `action_ajouter_destinations`) -> `destination_insert($intitule, $commentaire);`

### `destinations_are_enabled`

- Total occurrences: **7**
- Repartition: `appel_php`=7
- Occurrences:
  - `exec/bilan.php:53` (appel_php, caller: `exec_bilan`) -> `if (destinations_are_enabled())`
  - `exec/bilan.php:100` (appel_php, caller: `exec_bilan`) -> `else if (destinations_are_enabled()) {`
  - `exec/plan_comptable.php:43` (appel_php, caller: `raccourcis`) -> `if (destinations_are_enabled()) {`
  - `formulaires/inc/destinations.php:14` (appel_php, caller: `update_destination_contexte_from_compte`) -> `if (!destinations_are_enabled()) {`
  - `formulaires/inc/destinations.php:63` (appel_php, caller: `verifier_destination_comptable`) -> `if (destinations_are_enabled() && !array_key_exists($montant_field, $erreurs))`
  - `inc/destinations.php:17` (appel_php, caller: `ajouter_destinations`) -> `if (!destinations_are_enabled()) {`
  - `inc/destinations.php:76` (appel_php, caller: `create_destination_map_for_montant`) -> `if (destinations_are_enabled() and default_destination_is_set($dc_name)) {`

### `droit_auteur_evenements`

- Total occurrences: **12**
- Repartition: `appel_php`=12
- Occurrences:
  - `association_autoriser.php:439` (appel_php, caller: `autoriser_asso_modifier`) -> `list($activites_array, $type_auteur, $id_result) = droit_auteur_evenements($qui['id_auteur']);`
  - `association_autoriser.php:463` (appel_php, caller: `autoriser_asso_modifier`) -> `list($activites_array, $type_auteur, $id_result) = droit_auteur_evenements($qui['id_auteur'], $id_evenement);`
  - `association_autoriser.php:828` (appel_php, caller: `autoriser_modifier_article_dist`) -> `list($activites_array) = droit_auteur_evenements($qui['id_auteur']);`
  - `exec/action_activites.php:17` (appel_php, caller: `exec_action_activites`) -> `$droit_auteur = droit_auteur_evenements($id_auteur);`
  - `exec/action_email_collectif_activite.php:20` (appel_php, caller: `exec_action_email_collectif_activite`) -> `$droit_auteur = droit_auteur_evenements($id_auteur);`
  - `exec/activites.php:16` (appel_php, caller: `exec_activites`) -> `$droit_auteur = droit_auteur_evenements($id_auteur);`
  - `exec/adherents_bck.php:22` (appel_php, caller: `exec_adherents`) -> `$droit_auteur_connecte = droit_auteur_evenements($id_auteur_connecte);`
  - `exec/edit_cotisation.php:17` (appel_php, caller: `exec_edit_cotisation`) -> `$droit_auteur = droit_auteur_evenements($id_auteur_session);`
  - `formulaires/inscription_evenement.php:32` (appel_php, caller: `formulaires_inscription_evenement_charger_dist`) -> `$droit_auteur = droit_auteur_evenements($id_auteur, $id_evenement);`
  - `formulaires/inscription_evenement_multi.php:324` (appel_php, caller: `formulaires_inscription_evenement_multi_charger_dist`) -> `$droit_auteur = droit_auteur_evenements($id_auteur, $id_evenement);`
  - `inc/autorisations.php:34` (appel_php, caller: `association_est_admin_complet`) -> `* Vérifie si l'utilisateur est responsable d'un événement donné via droit_auteur_evenements().`
  - `inc/autorisations.php:45` (appel_php, caller: `association_est_responsable_evenement`) -> `list($activites_array, $type_auteur) = droit_auteur_evenements($qui['id_auteur'], $id_evenement);`

### `eligibilite_desinscription_evenement`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/desinscription_evenement_public.php:28` (appel_php, caller: `formulaires_desinscription_evenement_public_charger_dist`) -> `$desinscription_possible = eligibilite_desinscription_evenement($id_activite);`

### `eligibilite_inscription_evenement`

- Total occurrences: **11**
- Repartition: `appel_php`=11
- Occurrences:
  - `formulaires/inscription_evenement_multi.php:356` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_1_dist`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:377` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_2_dist`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:437` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_3_dist`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:492` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_4_dist`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:41` (appel_php, caller: `formulaires_inscription_evenement_multi_public_saisies`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:254` (appel_php, caller: `formulaires_inscription_evenement_multi_public_charger_dist`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:330` (appel_php, caller: `formulaires_inscription_evenement_multi_public_verifier_dist`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:504` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_public.php:27` (appel_php, caller: `formulaires_inscription_evenement_public_charger_dist`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_public.php:389` (appel_php, caller: `formulaires_inscription_evenement_public_verifier_dist`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement); // Vérification de l'éligibilité.`
  - `formulaires/inscription_evenement_public.php:555` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `$eligibilite_inscription_evenement = eligibilite_inscription_evenement($id_evenement);`

### `eligibilite_modification_evenement`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inscription_evenement_multi_public.php:258` (appel_php, caller: `formulaires_inscription_evenement_multi_public_charger_dist`) -> `$eligibilite_modification_evenement = isset($id_activite) && !empty($id_activite) ? eligibilite_modification_evenement($id_activite) : false;`
  - `formulaires/inscription_evenement_public.php:31` (appel_php, caller: `formulaires_inscription_evenement_public_charger_dist`) -> `$eligibilite_modification_evenement = isset($id_activite) ? eligibilite_modification_evenement($id_activite) : false;`

### `est_actif_gestion_comptes_secondaires`

- Total occurrences: **6**
- Repartition: `appel_php`=6
- Occurrences:
  - `inc/adherents_search_context.php:272` (appel_php, caller: `(global)`) -> `if (!$ctx->type_compte && !isset($_REQUEST['type_compte']) && empty($_SESSION['adherents_filtres']['type_compte']) && function_exists('est_actif_gestion_comptes_secondaires') && est_actif_gestion_comptes_secondaires()) {`
  - `prive/squelettes/contenu/adherents_fonctions.php:239` (appel_php, caller: `filtre_a_type_compte`) -> `return est_actif_gestion_comptes_secondaires();`
  - `prive/squelettes/contenu/adherents_fonctions.php:248` (appel_php, caller: `gestion_comptes_secondaires_active`) -> `return est_actif_gestion_comptes_secondaires();`
  - `prive/squelettes/contenu/adherents_fonctions.php:256` (appel_php, caller: `filtre_liste_type_compte`) -> `if (est_actif_gestion_comptes_secondaires()) {`
  - `prive/squelettes/contenu/adherents_fonctions.php:606` (appel_php, caller: `filtre_filtres_effectifs`) -> `$def_compte = est_actif_gestion_comptes_secondaires() ? 'compte_principal' : '';`
  - `prive/squelettes/contenu/inc-adherents/bloc_filtres_fonctions.php:8` (appel_php, caller: `filtre_compteur_adherents`) -> `$config_compte_secondaire = est_actif_gestion_comptes_secondaires() ? 'oui' : 'non';`

### `evenements_edit_config`

- Aucun appel detecte statiquement.

### `exec_action_activites`

- Aucun appel detecte statiquement.

### `exec_action_adherents`

- Aucun appel detecte statiquement.

### `exec_action_adherents_args`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/action_adherents.php:25` (appel_php, caller: `exec_action_adherents`) -> `exec_action_adherents_args($_POST['delete']);`

### `exec_action_comptes`

- Aucun appel detecte statiquement.

### `exec_action_comptes_args`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/action_comptes.php:25` (appel_php, caller: `exec_action_comptes`) -> `exec_action_comptes_args([$id_compte]);`

### `exec_action_destinations`

- Aucun appel detecte statiquement.

### `exec_action_email_collectif_activite`

- Aucun appel detecte statiquement.

### `exec_action_email_collectif_adherent`

- Aucun appel detecte statiquement.

### `exec_action_email_relances`

- Aucun appel detecte statiquement.

### `exec_action_labels`

- Aucun appel detecte statiquement.

### `exec_action_plan`

- Aucun appel detecte statiquement.

### `exec_action_prets`

- Aucun appel detecte statiquement.

### `exec_action_relances`

- Aucun appel detecte statiquement.

### `exec_action_ressources`

- Aucun appel detecte statiquement.

### `exec_action_ressources_args`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/action_ressources.php:26` (appel_php, caller: `exec_action_ressources`) -> `} else exec_action_ressources_args($id_ressource);`

### `exec_action_ventes`

- Aucun appel detecte statiquement.

### `exec_action_voir`

- Aucun appel detecte statiquement.

### `exec_activites`

- Aucun appel detecte statiquement.

### `exec_activites_evenements`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/activites.php:49` (appel_php, caller: `exec_activites`) -> `exec_activites_evenements($droit_auteur[2]);`

### `exec_adherents`

- Aucun appel detecte statiquement.

### `exec_bilan`

- Aucun appel detecte statiquement.

### `exec_configurer_visuel`

- Aucun appel detecte statiquement.

### `exec_csv_activites`

- Aucun appel detecte statiquement.

### `exec_csv_adherents`

- Aucun appel detecte statiquement.

### `exec_destinations`

- Aucun appel detecte statiquement.

### `exec_dons`

- Aucun appel detecte statiquement.

### `exec_edit_cotisation`

- Aucun appel detecte statiquement.

### `exec_edit_destination`

- Aucun appel detecte statiquement.

### `exec_edit_don`

- Aucun appel detecte statiquement.

### `exec_edit_email_collectif_activite`

- Aucun appel detecte statiquement.

### `exec_edit_email_collectif_adherent`

- Aucun appel detecte statiquement.

### `exec_edit_labels`

- Aucun appel detecte statiquement.

### `exec_edit_mail`

- Aucun appel detecte statiquement.

### `exec_edit_plan`

- Aucun appel detecte statiquement.

### `exec_edit_pret`

- Aucun appel detecte statiquement.

### `exec_edit_relances`

- Aucun appel detecte statiquement.

### `exec_edit_ressource`

- Aucun appel detecte statiquement.

### `exec_edit_vente`

- Aucun appel detecte statiquement.

### `exec_editer_asso_comptes`

- Aucun appel detecte statiquement.

### `exec_pdf_activite`

- Aucun appel detecte statiquement.

### `exec_pdf_adherents`

- Aucun appel detecte statiquement.

### `exec_pdf_fiscal`

- Aucun appel detecte statiquement.

### `exec_plan_comptable`

- Aucun appel detecte statiquement.

### `exec_prets`

- Aucun appel detecte statiquement.

### `exec_ressources`

- Aucun appel detecte statiquement.

### `exec_settings_list_members_event`

- Aucun appel detecte statiquement.

### `exec_ventes`

- Aucun appel detecte statiquement.

### `exec_voir_adherent`

- Aucun appel detecte statiquement.

### `exemple_activite_par_statut`

- Total occurrences: **8**
- Repartition: `filtre_template`=8
- Occurrences:
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:20` (filtre_template, caller: `(squelette)`) -> `<td colspan="4"><h3>Notifications liées aux préinscrits - [#(#VAL{preinscrit}\|exemple_activite_par_statut{#NOMBRE_INSCRITS})]</h3>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:29` (filtre_template, caller: `(squelette)`) -> `<td style="min-width:270px"><a href="[(#URL_PAGE{item-notification,notification=#CLE}\|parametre_url{id_activite,[(#VAL{preinscrit}\|exemple_activite_par_statut{#NOMBRE_INSCRITS})]}\|parametre_url{id_evenement,#GET{id_evene`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:42` (filtre_template, caller: `(squelette)`) -> `<td colspan="4"><h3>Notifications liées aux inscrits - [#(#VAL{ok}\|exemple_activite_par_statut{#NOMBRE_INSCRITS})]</h3>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:51` (filtre_template, caller: `(squelette)`) -> `<td style="min-width:270px"><a href="[(#URL_PAGE{item-notification,notification=#CLE}\|parametre_url{lang,#LANG}\|parametre_url{id_activite,[(#VAL{ok}\|exemple_activite_par_statut{#NOMBRE_INSCRITS})]}\|parametre_url{id_evene`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:62` (filtre_template, caller: `(squelette)`) -> `<td colspan="4"><h3>Notifications liées au liste d'attente -  [#(#VAL{liste_attente}\|exemple_activite_par_statut{#NOMBRE_INSCRITS})]</h3>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:71` (filtre_template, caller: `(squelette)`) -> `<td style="min-width:270px"><a href="[(#URL_PAGE{item-notification,notification=#CLE}\|parametre_url{lang,#LANG}\|parametre_url{id_activite,[(#VAL{liste_attente}\|exemple_activite_par_statut{#NOMBRE_INSCRITS})]}\|parametre_u`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:83` (filtre_template, caller: `(squelette)`) -> `<h3>Notifications liées aux désinscrits - [#(#VAL{desinscrit}\|exemple_activite_par_statut{#NOMBRE_INSCRITS})]</h3>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:92` (filtre_template, caller: `(squelette)`) -> `<td style="min-width:270px"><a href="[(#URL_PAGE{item-notification,notification=#CLE}\|parametre_url{lang,#LANG}\|parametre_url{id_activite,[(#VAL{desinscrit}\|exemple_activite_par_statut{#NOMBRE_INSCRITS})]}\|parametre_url{`

### `exemple_adherent_par_type`

- Total occurrences: **6**
- Repartition: `filtre_template`=6
- Occurrences:
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_cotisation.html:8` (filtre_template, caller: `(squelette)`) -> `<a href="[(#URL_PAGE{item-notification,notification=#CLE}\|parametre_url{lang,#LANG}\|parametre_url{id_compte,[(#ENV{radio_type_adherent}\|sinon{adherent}\|exemple_adherent_par_type\|table_valeur{id_compte})]}\|parametre_url{r`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_cotisation.html:19` (filtre_template, caller: `(squelette)`) -> `#SET{id_compte_courant,#ENV{radio_type_adherent}\|sinon{adherent}\|exemple_adherent_par_type\|table_valeur{id_compte}}`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_cotisation.html:20` (filtre_template, caller: `(squelette)`) -> `#SET{id_auteur_courant,#ENV{radio_type_adherent}\|sinon{adherent}\|exemple_adherent_par_type\|table_valeur{id_auteur}}`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_cotisation.html:39` (filtre_template, caller: `(squelette)`) -> `<td style="min-width:270px"><a href="[(#URL_PAGE{item-notification,notification=#CLE}\|parametre_url{lang,#LANG}\|parametre_url{id_auteur,[(#ENV{radio_type_adherent}\|sinon{adherent}\|exemple_adherent_par_type\|table_valeur{i`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_cotisation.html:50` (filtre_template, caller: `(squelette)`) -> `#SET{id_compte_courant,#ENV{radio_type_adherent}\|sinon{adherent}\|exemple_adherent_par_type\|table_valeur{id_compte}}`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_cotisation.html:51` (filtre_template, caller: `(squelette)`) -> `#SET{id_auteur_courant,#ENV{radio_type_adherent}\|sinon{adherent}\|exemple_adherent_par_type\|table_valeur{id_auteur}}`

### `exporter_csv_champ`

- Aucun appel detecte statiquement.

### `exporter_csv_ligne`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `inc/exporter_csv.php:123` (appel_php, caller: `inc_exporter_csv_dist`) -> `$output = exporter_csv_ligne($liste_entetes, $delim, $importer_charset);`
  - `inc/exporter_csv.php:136` (appel_php, caller: `inc_exporter_csv_dist`) -> `$output = exporter_csv_ligne($row, $delim, $importer_charset);`

### `extraire_groupes_chaine`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/fonctions/roles_association.php:154` (appel_php, caller: `saisies_extraire_groupes_chaine`) -> `return extraire_groupes_chaine($chaine);`

### `extraire_valeurs_chaine`

- Aucun appel detecte statiquement.

### `facteur_envoyer_app`

- Total occurrences: **23**
- Repartition: `appel_php`=23
- Occurrences:
  - `inc/cotisations.php:433` (appel_php, caller: `notifier_cotisation_adherent`) -> `$res = facteur_envoyer_app($dest, $sujet, $html, $bcc, array('use_queue' => false));`
  - `inc/cotisations.php:514` (appel_php, caller: `notifier_cotisation_adherent`) -> `$res = facteur_envoyer_app($email_adherent, $sujet, $html, $bcc, array('use_queue' => $use_queue));`
  - `inc/cotisations.php:516` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (attente_paiement): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:531` (appel_php, caller: `notifier_cotisation_adherent`) -> `$res = facteur_envoyer_app($email_adherent, $sujet, $html, $bcc, array('use_queue' => $use_queue));`
  - `inc/cotisations.php:533` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (validation_post-paiement): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:547` (appel_php, caller: `notifier_cotisation_adherent`) -> `$res = facteur_envoyer_app($email_adherent, $sujet, $html, $bcc, array('use_queue' => $use_queue));`
  - `inc/cotisations.php:549` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (validation_pre_paiement): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:572` (appel_php, caller: `notifier_cotisation_adherent`) -> `$res = facteur_envoyer_app($email_adherent, $sujet, $html, $bcc, array('use_queue' => $use_queue));`
  - `inc/cotisations.php:574` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (activation): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:589` (appel_php, caller: `notifier_cotisation_adherent`) -> `$res = facteur_envoyer_app($email_adherent, $sujet, $html, false, array('use_queue' => $use_queue));`
  - `inc/cotisations.php:591` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (notification_echeances_adherent): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:619` (appel_php, caller: `notifier_cotisation_adherent`) -> `$res = facteur_envoyer_app($email_adherent, $sujet, $html, false, array('use_queue' => $use_queue));`
  - `inc/cotisations.php:621` (appel_php, caller: `notifier_cotisation_adherent`) -> `association_log('cotisations', 'Erreur wrapper facteur_envoyer_app (notification_echeances_adherent_echu): ' . ($res['message'] ?? ''), 'erreur');`
  - `inc/cotisations.php:859` (appel_php, caller: `notifier_cotisation_admin`) -> `// Envoyer directement via facteur_envoyer_app (supporte plusieurs destinataires)`
  - `inc/cotisations.php:860` (appel_php, caller: `notifier_cotisation_admin`) -> `$res = facteur_envoyer_app($emails_tres, $sujet, $html, false, array('use_queue' => $use_queue));`
  - `inc/cotisations.php:878` (appel_php, caller: `notifier_cotisation_admin`) -> `$res = facteur_envoyer_app($emails_tres, $sujet, $html, false, array('use_queue' => $use_queue));`
  - `inc/cotisations.php:901` (appel_php, caller: `notifier_cotisation_admin`) -> `$res = facteur_envoyer_app($emails_tres, $sujet, $html, false, array('use_queue' => $use_queue));`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:166` (appel_php, caller: `facteur_envoyer_mail_activite_adherent`) -> `if (!facteur_envoyer_app($email_inscrit, $sujet, $html, '')) {`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:292` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `if (!facteur_envoyer_app($dest, $sujet, $html, $bcc)) {`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:308` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `if (!facteur_envoyer_app($dest, $sujet, $html, $bcc)) {`
  - `inc/fonctions/facteur_envoyer_notification_gis.php:36` (appel_php, caller: `facteur_envoyer_notification_gis`) -> `$envoyer = facteur_envoyer_app($dest, $sujet, $corps );`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:141` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `$envoyer = facteur_envoyer_app($email_adherent, $sujet, $html, $bcc);`
  - `inc/fonctions/facteur_envoyer_recu_participation.php:121` (appel_php, caller: `facteur_envoyer_recu_participation`) -> `$envoyer = facteur_envoyer_app($email_inscrit, $sujet, $html, $bcc);`

### `facteur_envoyer_mail_activite_adherent`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/fonctions/facteur_envoyer_mail_activites.php:53` (appel_php, caller: `facteur_envoyer_mail_activites`) -> `$infos_activite = facteur_envoyer_mail_activite_adherent($id, $id_evenement, $type, $evenement, $activite_responsable_array);`

### `facteur_envoyer_mail_activite_responsable`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/fonctions/facteur_envoyer_mail_activites.php:61` (appel_php, caller: `facteur_envoyer_mail_activites`) -> `facteur_envoyer_mail_activite_responsable($infos_activites[0], $type, $evenement, $activite_responsable_array);`

### `facteur_envoyer_mail_activites`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `action/ajouter_activites.php:166` (appel_php, caller: `activites_insert`) -> `//facteur_envoyer_mail_activites($id_evenement, $id_auteur, $type, $id_activite);`
  - `action/modifier_activites.php:184` (appel_php, caller: `action_modifier_activites`) -> `//facteur_envoyer_mail_activites($id_evenement, $id_auteur, $type, $id_activite);`

### `facteur_envoyer_notification_gis`

- Aucun appel detecte statiquement.

### `facteur_envoyer_recu_adhesion`

- Aucun appel detecte statiquement.

### `facteur_envoyer_recu_participation`

- Aucun appel detecte statiquement.

### `filtre_a_type_compte`

- Aucun appel detecte statiquement.

### `filtre_a_type_cotisation`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_fonctions.php:535` (appel_php, caller: `filtre_liste_type_cotisation`) -> `if (!filtre_a_type_cotisation()) return array();`

### `filtre_analyse_compta_activites_compter_evenements`

- Aucun appel detecte statiquement.

### `filtre_analyse_compta_activites_libelle_type`

- Aucun appel detecte statiquement.

### `filtre_analyse_compta_activites_lister_evenements_exercice`

- Aucun appel detecte statiquement.

### `filtre_analyse_compta_activites_normaliser_type`

- Aucun appel detecte statiquement.

### `filtre_analyse_compta_activites_stats_exercice`

- Aucun appel detecte statiquement.

### `filtre_analyse_compta_activites_totaux_evenements`

- Aucun appel detecte statiquement.

### `filtre_association_comptes_bornes_exercice_json`

- Aucun appel detecte statiquement.

### `filtre_association_comptes_start_year`

- Aucun appel detecte statiquement.

### `filtre_association_defaut_tri_adherents_dist`

- Aucun appel detecte statiquement.

### `filtre_association_get_inclure_non_validees`

- Aucun appel detecte statiquement.

### `filtre_association_vu_selon_inclusion`

- Aucun appel detecte statiquement.

### `filtre_bank_config_id`

- Aucun appel detecte statiquement.

### `filtre_calcul_fonction_comptes_calculer_totaux`

- Aucun appel detecte statiquement.

### `filtre_calcul_fonction_comptes_compter_depenses`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `inc/fonctions/comptes.php:257` (appel_php, caller: `filtre_calcul_fonction_comptes_compter_depenses_selon_inclusion`) -> `return filtre_calcul_fonction_comptes_compter_depenses($imputation, $exercice, '');`
  - `inc/fonctions/comptes.php:259` (appel_php, caller: `filtre_calcul_fonction_comptes_compter_depenses_selon_inclusion`) -> `return filtre_calcul_fonction_comptes_compter_depenses($imputation, $exercice, $vu);`
  - `inc/fonctions/comptes.php:276` (appel_php, caller: `filtre_calcul_fonction_comptes_diff_depenses`) -> `$valide = intval(filtre_calcul_fonction_comptes_compter_depenses($imputation, $exercice, $vu));`
  - `inc/fonctions/comptes.php:277` (appel_php, caller: `filtre_calcul_fonction_comptes_diff_depenses`) -> `$total_unval = intval(filtre_calcul_fonction_comptes_compter_depenses($imputation, $exercice, 0));`

### `filtre_calcul_fonction_comptes_compter_depenses_selon_inclusion`

- Aucun appel detecte statiquement.

### `filtre_calcul_fonction_comptes_compter_operations`

- Aucun appel detecte statiquement.

### `filtre_calcul_fonction_comptes_compter_recettes`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `inc/fonctions/comptes.php:247` (appel_php, caller: `filtre_calcul_fonction_comptes_compter_recettes_selon_inclusion`) -> `return filtre_calcul_fonction_comptes_compter_recettes($imputation, $exercice, '');`
  - `inc/fonctions/comptes.php:249` (appel_php, caller: `filtre_calcul_fonction_comptes_compter_recettes_selon_inclusion`) -> `return filtre_calcul_fonction_comptes_compter_recettes($imputation, $exercice, $vu);`
  - `inc/fonctions/comptes.php:266` (appel_php, caller: `filtre_calcul_fonction_comptes_diff_recettes`) -> `$valide = intval(filtre_calcul_fonction_comptes_compter_recettes($imputation, $exercice, $vu));`
  - `inc/fonctions/comptes.php:267` (appel_php, caller: `filtre_calcul_fonction_comptes_diff_recettes`) -> `$total_unval = intval(filtre_calcul_fonction_comptes_compter_recettes($imputation, $exercice, 0));`

### `filtre_calcul_fonction_comptes_compter_recettes_selon_inclusion`

- Aucun appel detecte statiquement.

### `filtre_calcul_fonction_comptes_diff_depenses`

- Aucun appel detecte statiquement.

### `filtre_calcul_fonction_comptes_diff_recettes`

- Aucun appel detecte statiquement.

### `filtre_calcul_fonction_comptes_lister_annees`

- Aucun appel detecte statiquement.

### `filtre_calcul_fonction_comptes_lister_exercices`

- Total occurrences: **3**
- Repartition: `appel_php`=1, `filtre_template`=2
- Occurrences:
  - `inc/fonctions/comptes.php:141` (appel_php, caller: `filtre_calcul_fonction_comptes_lister_annees`) -> `return filtre_calcul_fonction_comptes_lister_exercices($imputation,$vu);`
  - `prive/squelettes/contenu/analyse_compta_activites.html:4` (filtre_template, caller: `(squelette)`) -> `[(#SET{exercices,#VAL{%}\|filtre_calcul_fonction_comptes_lister_exercices{%,''}})]`
  - `prive/squelettes/contenu/export_activites_compta.html:2` (filtre_template, caller: `(squelette)`) -> `[(#SET{exercices,#VAL{%}\|filtre_calcul_fonction_comptes_lister_exercices{%,''}})]`

### `filtre_calcul_fonction_comptes_lister_operations`

- Aucun appel detecte statiquement.

### `filtre_compte_secondaire`

- Aucun appel detecte statiquement.

### `filtre_compte_titulaire`

- Aucun appel detecte statiquement.

### `filtre_compteur_adherents`

- Aucun appel detecte statiquement.

### `filtre_filtres_dynamiques_actifs_dist`

- Aucun appel detecte statiquement.

### `filtre_filtres_effectifs`

- Aucun appel detecte statiquement.

### `filtre_filtres_effectifs_cotisations`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/cotisations_fonctions.php:20` (appel_php, caller: `preparer_liste_cotisations`) -> `$filtres = filtre_filtres_effectifs_cotisations();`

### `filtre_get_recherche_rapide_active_dist`

- Aucun appel detecte statiquement.

### `filtre_has_type_adherent`

- Aucun appel detecte statiquement.

### `filtre_ids_categories_par_type`

- Aucun appel detecte statiquement.

### `filtre_justification_compte_dist`

- Aucun appel detecte statiquement.

### `filtre_liste_champs_colonnes_configures`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:692` (appel_php, caller: `liste_colonnes_dynamiques_adherents`) -> `$fields = filtre_liste_champs_colonnes_configures();`
  - `prive/squelettes/contenu/adherents_fonctions.php:706` (appel_php, caller: `association_donnees_colonnes_adherent`) -> `$fields = filtre_liste_champs_colonnes_configures();`
  - `prive/squelettes/contenu/adherents_fonctions.php:725` (appel_php, caller: `association_hydrater_colonnes_dynamiques`) -> `$colonnes = filtre_liste_champs_colonnes_configures();`
  - `prive/squelettes/contenu/adherents_fonctions.php:856` (appel_php, caller: `filtre_trier_colonne_dynamique_dist`) -> `foreach (filtre_liste_champs_colonnes_configures() as $champ) {`

### `filtre_liste_champs_filtres_configures`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:669` (appel_php, caller: `liste_filtres_dynamiques_adherents`) -> `$fields = filtre_liste_champs_filtres_configures();`

### `filtre_liste_filtres_reset_adherents_dist`

- Aucun appel detecte statiquement.

### `filtre_liste_noms_filtres_dynamiques_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:803` (appel_php, caller: `filtre_noms_filtres_dynamiques_dist`) -> `return filtre_liste_noms_filtres_dynamiques_dist($base);`

### `filtre_liste_periodes_adherents`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:462` (appel_php, caller: `preparer_liste_adherents`) -> `$liste_periodes_adherents = filtre_liste_periodes_adherents(0);`

### `filtre_liste_periodes_cotisations`

- Total occurrences: **5**
- Repartition: `appel_php`=5
- Occurrences:
  - `association_fonctions.php:747` (appel_php, caller: `periode_defaut_libelle`) -> `$periodes = filtre_liste_periodes_cotisations(0, false);`
  - `association_fonctions.php:765` (appel_php, caller: `trouver_periode_par_libelle`) -> `$periodes = filtre_liste_periodes_cotisations(0, false);`
  - `association_fonctions.php:900` (appel_php, caller: `liste_periodes_cotisations`) -> `return filtre_liste_periodes_cotisations(0, false, $contexte);`
  - `inc/adherents_search_context.php:254` (appel_php, caller: `(global)`) -> `$periodes = filtre_liste_periodes_cotisations(0, false);`
  - `prive/squelettes/contenu/adherents_fonctions.php:300` (appel_php, caller: `filtre_has_type_adherent`) -> `* Contrairement à filtre_liste_periodes_cotisations() qui ne retourne que les périodes`

### `filtre_liste_statut_interne_adherents`

- Aucun appel detecte statiquement.

### `filtre_liste_type_adherent`

- Aucun appel detecte statiquement.

### `filtre_liste_type_compte`

- Aucun appel detecte statiquement.

### `filtre_liste_type_cotisation`

- Aucun appel detecte statiquement.

### `filtre_noms_filtres_dynamiques_dist`

- Aucun appel detecte statiquement.

### `filtre_roles_association`

- Aucun appel detecte statiquement.

### `filtre_scalar_val`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `association_fonctions.php:883` (appel_php, caller: `liste_periodes_cotisations`) -> `$contexte = filtre_scalar_val($_REQUEST['periode_contexte'], null);`
  - `association_fonctions.php:890` (appel_php, caller: `liste_periodes_cotisations`) -> `$type_cot = filtre_scalar_val($_REQUEST['type_cotisation'], null);`
  - `prive/squelettes/contenu/adherents_fonctions.php:314` (appel_php, caller: `filtre_liste_periodes_adherents`) -> `$contexte = filtre_scalar_val($_REQUEST['periode_contexte'], null);`

### `filtre_stats_compta_activites_exercice`

- Aucun appel detecte statiquement.

### `filtre_stats_compta_activites_lister_evenements_exercice`

- Aucun appel detecte statiquement.

### `filtre_trier_colonne_dynamique_dist`

- Aucun appel detecte statiquement.

### `filtre_url_reinit_recherche_avancee_dist`

- Aucun appel detecte statiquement.

### `filtre_url_supprimer_filtres_dynamiques_dist`

- Aucun appel detecte statiquement.

### `fin_page_association`

- Total occurrences: **31**
- Repartition: `appel_php`=31
- Occurrences:
  - `exec/action_activites.php:251` (appel_php, caller: `exec_action_activites`) -> `echo fin_page_association();`
  - `exec/action_adherents.php:44` (appel_php, caller: `exec_action_adherents_args`) -> `echo fin_page_association();`
  - `exec/action_comptes.php:44` (appel_php, caller: `exec_action_comptes_args`) -> `echo fin_page_association();`
  - `exec/action_email_collectif_activite.php:100` (appel_php, caller: `exec_action_email_collectif_activite`) -> `echo fin_page_association();`
  - `exec/action_email_collectif_adherent.php:75` (appel_php, caller: `exec_action_email_collectif_adherent`) -> `echo fin_page_association();`
  - `exec/action_email_relances.php:70` (appel_php, caller: `exec_action_email_relances`) -> `echo fin_page_association();`
  - `exec/action_prets.php:64` (appel_php, caller: `exec_action_prets`) -> `echo fin_page_association();`
  - `exec/action_relances.php:76` (appel_php, caller: `exec_action_relances`) -> `echo fin_page_association();`
  - `exec/action_ressources.php:59` (appel_php, caller: `exec_action_ressources_args`) -> `echo fin_page_association();`
  - `exec/action_ventes.php:56` (appel_php, caller: `exec_action_ventes`) -> `echo fin_page_association();`
  - `exec/action_voir.php:47` (appel_php, caller: `exec_action_voir`) -> `echo fin_page_association();`
  - `exec/activites.php:51` (appel_php, caller: `exec_activites`) -> `echo fin_page_association();`
  - `exec/adherents_bck.php:97` (appel_php, caller: `exec_adherents`) -> `echo fin_page_association();`
  - `exec/bilan.php:163` (appel_php, caller: `exec_bilan`) -> `echo fin_page_association();`
  - `exec/configurer_visuel.php:39` (appel_php, caller: `exec_configurer_visuel`) -> `echo fin_page_association();`
  - `exec/dons.php:97` (appel_php, caller: `exec_dons`) -> `echo fin_page_association();`
  - `exec/edit_cotisation.php:53` (appel_php, caller: `exec_edit_cotisation`) -> `echo fin_page_association();`
  - `exec/edit_don.php:53` (appel_php, caller: `exec_edit_don`) -> `echo fin_page_association();`
  - `exec/edit_email_collectif_activite.php:99` (appel_php, caller: `exec_edit_email_collectif_activite`) -> `echo fin_page_association();`
  - `exec/edit_email_collectif_adherent.php:38` (appel_php, caller: `exec_edit_email_collectif_adherent`) -> `echo fin_page_association();`
  - `exec/edit_labels.php:82` (appel_php, caller: `exec_edit_labels`) -> `echo fin_page_association();`
  - `exec/edit_mail.php:191` (appel_php, caller: `exec_edit_mail`) -> `echo fin_page_association();`
  - `exec/edit_pret.php:161` (appel_php, caller: `exec_edit_pret`) -> `echo fin_page_association();`
  - `exec/edit_relances.php:133` (appel_php, caller: `exec_edit_relances`) -> `echo fin_page_association();`
  - `exec/edit_vente.php:52` (appel_php, caller: `exec_edit_vente`) -> `echo fin_page_association();`
  - `exec/plan_comptable.php:123` (appel_php, caller: `cadre_relief`) -> `echo fin_page_association();`
  - `exec/prets.php:95` (appel_php, caller: `exec_prets`) -> `echo fin_page_association();`
  - `exec/ressources.php:84` (appel_php, caller: `cadre_relief`) -> `echo fin_page_association();`
  - `exec/ventes.php:123` (appel_php, caller: `exec_ventes`) -> `echo fin_page_association();`
  - `exec/voir_adherent.php:54` (appel_php, caller: `exec_voir_adherent`) -> `echo fin_page_association();`
  - `inc/page.php:23` (appel_php, caller: `page_fond`) -> `echo fin_page_association();`

### `formater_post_form`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inscription_evenement.php:609` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `$data_form = formater_post_form($id_evenement,$_POST,$affichage_dans_activites,'prive');`
  - `formulaires/inscription_evenement_public.php:563` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `$data_form = formater_post_form($id_evenement,$_POST,$affichage_dans_activites,'public');`

### `formater_post_form_multi`

- Total occurrences: **8**
- Repartition: `appel_php`=8
- Occurrences:
  - `formulaires/inc/inscription_evenement.php:928` (appel_php, caller: `generer_recapitulatif_multi`) -> `$data_form = formater_post_form_multi($valeur_post, 'prive');`
  - `formulaires/inscription_evenement_multi.php:361` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_1_dist`) -> `$data_form = formater_post_form_multi($_POST,'prive'); */`
  - `formulaires/inscription_evenement_multi.php:381` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_2_dist`) -> `$data_form = formater_post_form_multi($_POST,'prive');`
  - `formulaires/inscription_evenement_multi.php:443` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_3_dist`) -> `$data_form = formater_post_form_multi($_POST,'prive');`
  - `formulaires/inscription_evenement_multi.php:496` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_4_dist`) -> `$data_form = formater_post_form_multi($_POST,'prive');`
  - `formulaires/inscription_evenement_multi.php:519` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `$data_form = formater_post_form_multi($_POST,'prive');`
  - `formulaires/inscription_evenement_multi_public.php:324` (appel_php, caller: `formulaires_inscription_evenement_multi_public_verifier_dist`) -> `$data_form = formater_post_form_multi($_POST,'public');`
  - `formulaires/inscription_evenement_multi_public.php:512` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$data_form = formater_post_form_multi($_POST, 'public');`

### `formatter_valeur_colonne_dynamique`

- Total occurrences: **1**
- Repartition: `filtre_template`=1
- Occurrences:
  - `prive/objets/liste/item_inscription_adherent.html:31` (filtre_template, caller: `(squelette)`) -> `[(#GET{valeur}\|formatter_valeur_colonne_dynamique{#VALEUR{options}}\|typo)]`

### `formulaires_adherents_recherche_avancee_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_adherents_recherche_avancee_saisies`

- Aucun appel detecte statiquement.

### `formulaires_adherents_recherche_avancee_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_adherents_recherche_avancee_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_adherents_recherche_rapide_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_adherents_recherche_rapide_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_adherents_recherche_rapide_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_choisir_gabarit_envoi_collectif_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_choisir_gabarit_envoi_collectif_saisies`

- Aucun appel detecte statiquement.

### `formulaires_configurer_association_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_configurer_association_saisies_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/configurer_association.php:1978` (appel_php, caller: `formulaires_configurer_association_traiter_dist`) -> `$saisies = formulaires_configurer_association_saisies_dist($config,true);`

### `formulaires_configurer_association_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_configurer_association_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_desinscription_evenement_public_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_desinscription_evenement_public_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_categorie_activite_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_categorie_activite_saisies_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_categorie_activite_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_categorie_cotisation_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_categorie_cotisation_saisies_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/editer_asso_categorie_cotisation.php:261` (appel_php, caller: `formulaires_editer_asso_categorie_cotisation_charger_dist`) -> `$contexte['_saisies'] = formulaires_editer_asso_categorie_cotisation_saisies_dist($id_categorie);`

### `formulaires_editer_asso_categorie_cotisation_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_categorie_cotisation_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_comptes_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_comptes_saisies_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_comptes_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_comptes_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_cotisation_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_cotisation_fichiers`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_cotisation_saisies`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_cotisation_traiter`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_cotisation_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_destinations_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_destinations_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_destinations_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_dons_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_dons_traiter`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_dons_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_membres_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_membres_traiter`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_membres_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_plan_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_plan_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_plan_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_ressources_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_ressources_traiter`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_ressources_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_ventes_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_ventes_traiter`

- Aucun appel detecte statiquement.

### `formulaires_editer_asso_ventes_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_evenement_charger`

- Aucun appel detecte statiquement.

### `formulaires_editer_evenement_charger_dist`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/editer_evenement.php:160` (appel_php, caller: `formulaires_editer_evenement_verifier_modifie_evenements_lies`) -> `$valeurs = formulaires_editer_evenement_charger_dist($id_evenement, $id_article);`

### `formulaires_editer_evenement_identifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_evenement_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_evenement_verifier`

- Aucun appel detecte statiquement.

### `formulaires_editer_evenement_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_editer_evenement_verifier_modifie_evenements_lies`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/editer_evenement.php:143` (appel_php, caller: `formulaires_editer_evenement_verifier_dist`) -> `and $impact = formulaires_editer_evenement_verifier_modifie_evenements_lies($id_evenement, $id_article)) {`

### `formulaires_editer_newsletter_traiter`

- Aucun appel detecte statiquement.

### `formulaires_email_collectif_adherent_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_email_collectif_adherent_fichiers`

- Aucun appel detecte statiquement.

### `formulaires_email_collectif_adherent_saisies`

- Aucun appel detecte statiquement.

### `formulaires_email_collectif_adherent_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_email_collectif_adherent_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_importer_destination_comptable_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_importer_destination_comptable_saisies_dist`

- Aucun appel detecte statiquement.

### `formulaires_importer_destination_comptable_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_importer_destination_comptable_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_importer_plan_comptable_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_importer_plan_comptable_saisies_dist`

- Aucun appel detecte statiquement.

### `formulaires_importer_plan_comptable_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_importer_plan_comptable_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_public_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_public_saisies`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_public_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_public_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_saisies`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_verifier_1_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_verifier_2_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_verifier_3_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_multi_verifier_4_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_public_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_public_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_public_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_inscription_evenement_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_migrer_asso_comptabilite_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_migrer_asso_comptabilite_saisies_dist`

- Aucun appel detecte statiquement.

### `formulaires_migrer_asso_comptabilite_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_migrer_asso_comptabilite_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_mot_de_passe_traiter`

- Aucun appel detecte statiquement.

### `formulaires_rembourser_transaction_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_rembourser_transaction_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_rembourser_transaction_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_supprimer_asso_cotisation_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_supprimer_asso_cotisation_traiter_dist`

- Aucun appel detecte statiquement.

### `formulaires_supprimer_asso_cotisation_verifier_dist`

- Aucun appel detecte statiquement.

### `formulaires_synchro_asso_membres_charger_dist`

- Aucun appel detecte statiquement.

### `formulaires_synchro_asso_membres_traiter`

- Aucun appel detecte statiquement.

### `generer_array_adherents`

- Total occurrences: **5**
- Repartition: `appel_php`=5
- Occurrences:
  - `formulaires/email_collectif_adherent.php:283` (appel_php, caller: `formulaires_email_collectif_adherent_charger_dist`) -> `$liste_auteurs = generer_array_adherents(array("statut_interne = 'ok'"));`
  - `formulaires/email_collectif_adherent.php:291` (appel_php, caller: `formulaires_email_collectif_adherent_charger_dist`) -> `$liste_auteurs = generer_array_adherents(array("statut_interne = 'ok'"));`
  - `formulaires/inc/adherents_recherche_avancee.php:644` (appel_php, caller: `afficher_resultat_recherche_avancee`) -> `$id_auteurs = generer_array_adherents($criteres_sql);`
  - `formulaires/inc/adherents_recherche_avancee.php:657` (appel_php, caller: `afficher_resultat_recherche_avancee`) -> `$id_auteurs = generer_array_adherents($fallback_criteres);`
  - `prive/squelettes/contenu/adherents_fonctions.php:476` (appel_php, caller: `preparer_liste_adherents`) -> `$id_auteurs = generer_array_adherents($criteres_sql, $context->periode_data);`

### `generer_array_categories_participation`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/inc/inscription_evenement_saisies.php:263` (appel_php, caller: `champs_saisies_tarifs`) -> `$datas_categories = generer_array_categories_participation($tableau_categories,'datas',$array_type_adherents,$compteur);`

### `generer_detail_inscription_accompagnant`

- Total occurrences: **1**
- Repartition: `filtre_template`=1
- Occurrences:
  - `inscriptions_evenement.csv.html:29` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_asso_activites_liste_participants(ASSO_ACTIVITES){si #META{/association/meta_cfg_event_form_info_supp}\|=={oui}}{si #SESSION{id_auteur}}{id_evenement}{statut = ok}{par statut}>[(#ID_ACTIVITE\|generer_detail_inscrip`

### `generer_detail_participants`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/inc/inscription_evenement.php:725` (appel_php, caller: `formater_post_form`) -> `$detail_participants = generer_detail_participants($id_participants, $query_auteur);`

### `generer_export_csv_adherents`

- Total occurrences: **2**
- Repartition: `appel_php`=1, `filtre_template`=1
- Occurrences:
  - `exec/adherents_bck.php:93` (appel_php, caller: `exec_adherents`) -> `generer_export_csv_adherents($criteres_sql ?? ''),`
  - `prive/squelettes/contenu/adherents.html:343` (filtre_template, caller: `(squelette)`) -> `(#GET{criteres_sql,''}\|generer_export_csv_adherents)`

### `generer_famille_adherent`

- Total occurrences: **14**
- Repartition: `appel_php`=14
- Occurrences:
  - `formulaires/inc/inscription_evenement.php:49` (appel_php, caller: `preparer_info_auteur`) -> `$res['data_famille'] = generer_famille_adherent($res['id_auteur']);`
  - `formulaires/inc/inscription_evenement.php:149` (appel_php, caller: `preparer_chargement_modification_inscription`) -> `$data_famille = generer_famille_adherent($membre);`
  - `formulaires/inc/inscription_evenement.php:152` (appel_php, caller: `preparer_chargement_modification_inscription`) -> `$data_famille = generer_famille_adherent($non_membre);`
  - `formulaires/inc/inscription_evenement.php:164` (appel_php, caller: `preparer_chargement_modification_inscription`) -> `$data_famille = generer_famille_adherent($query_activite['id_auteur']);`
  - `formulaires/inc/inscription_evenement.php:171` (appel_php, caller: `preparer_chargement_modification_inscription`) -> `$data_famille = generer_famille_adherent($query_activite['id_auteur']);`
  - `formulaires/inscription_evenement.php:79` (appel_php, caller: `formulaires_inscription_evenement_charger_dist`) -> `$data_famille = generer_famille_adherent($membre);`
  - `formulaires/inscription_evenement.php:82` (appel_php, caller: `formulaires_inscription_evenement_charger_dist`) -> `$data_famille = generer_famille_adherent($non_membre);`
  - `formulaires/inscription_evenement.php:106` (appel_php, caller: `formulaires_inscription_evenement_charger_dist`) -> `$data_famille = generer_famille_adherent($id_adherent);`
  - `formulaires/inscription_evenement.php:439` (appel_php, caller: `formulaires_inscription_evenement_verifier_dist`) -> `$data_famille = generer_famille_adherent($membre);`
  - `formulaires/inscription_evenement.php:442` (appel_php, caller: `formulaires_inscription_evenement_verifier_dist`) -> `$data_famille = generer_famille_adherent($non_membre);`
  - `formulaires/inscription_evenement_multi.php:50` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$data_famille = generer_famille_adherent($id_adherent);`
  - `formulaires/inscription_evenement_public.php:67` (appel_php, caller: `formulaires_inscription_evenement_public_charger_dist`) -> `$data_famille = generer_famille_adherent($id_adherent);`
  - `formulaires/inscription_evenement_public.php:97` (appel_php, caller: `formulaires_inscription_evenement_public_charger_dist`) -> `$data_famille = generer_famille_adherent($id_auteur_connecte);`
  - `formulaires/inscription_evenement_public.php:405` (appel_php, caller: `formulaires_inscription_evenement_public_verifier_dist`) -> `$data_famille = generer_famille_adherent($id_auteur_connecte);`

### `generer_recapitulatif_multi`

- Total occurrences: **2**
- Repartition: `filtre_template`=2
- Occurrences:
  - `formulaires/inscription_evenement_multi.html:25` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_recap(DATA){source table, #ENV**\|unserialize\|generer_recapitulatif_multi{#ENV{id_evenement}}}>`
  - `formulaires/inscription_evenement_multi_public.html:64` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_recap(DATA){source table, #ENV**\|unserialize\|generer_recapitulatif_multi{#ENV{id_evenement}}}>`

### `generer_saisies_comptes`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `formulaires/importer_plan_comptable.php:31` (appel_php, caller: `formulaires_importer_plan_comptable_saisies_dist`) -> `'saisies' => generer_saisies_comptes([$classe], '', $compteur_niveau),`
  - `formulaires/importer_plan_comptable.php:208` (appel_php, caller: `generer_saisies_comptes`) -> `$resultat = array_merge($resultat, generer_saisies_comptes($element['Comptes'], $nom, $compteur_niveau + 1));`
  - `formulaires/importer_plan_comptable.php:211` (appel_php, caller: `generer_saisies_comptes`) -> `$resultat = array_merge($resultat, generer_saisies_comptes($element['SousComptes'], $nom, $compteur_niveau + 1));`

### `generer_saisies_destinations`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `formulaires/importer_destination_comptable.php:37` (appel_php, caller: `formulaires_importer_destination_comptable_saisies_dist`) -> `'saisies' => generer_saisies_destinations([$destination],'',$compteur_niveau),`
  - `formulaires/importer_destination_comptable.php:171` (appel_php, caller: `generer_saisies_destinations`) -> `$resultat = array_merge($resultat, generer_saisies_destinations($element['destinations'], $nom,$compteur_niveau+1));`
  - `formulaires/importer_destination_comptable.php:175` (appel_php, caller: `generer_saisies_destinations`) -> `$resultat = array_merge($resultat, generer_saisies_destinations($element['SousDestinations'], $nom,$compteur_niveau+1));`

### `generer_saisies_info_public`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inscription_evenement.php:163` (appel_php, caller: `formulaires_inscription_evenement_charger_dist`) -> `$saisies_info_public= generer_saisies_info_public('prive');`
  - `formulaires/inscription_evenement_public.php:108` (appel_php, caller: `formulaires_inscription_evenement_public_charger_dist`) -> `$saisies = generer_saisies_info_public('public');`

### `generer_url_asso_don`

- Aucun appel detecte statiquement.

### `generer_url_asso_membre`

- Aucun appel detecte statiquement.

### `generer_url_asso_vente`

- Aucun appel detecte statiquement.

### `generer_url_don`

- Aucun appel detecte statiquement.

### `generer_url_membre`

- Aucun appel detecte statiquement.

### `generer_url_vente`

- Aucun appel detecte statiquement.

### `genie_association_expiration_auto_evenement_dist`

- Aucun appel detecte statiquement.

### `genie_association_maintenance_bdd`

- Aucun appel detecte statiquement.

### `genie_association_taches_generales`

- Aucun appel detecte statiquement.

### `gestion_comptes_secondaires_active`

- Total occurrences: **4**
- Repartition: `filtre_template`=4
- Occurrences:
  - `prive/objets/liste/item_inscription_adherent.html:9` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_compte_secondaire(AUTEURS){si #VAL\|gestion_comptes_secondaires_active}{auteur_compte_principal = #ID_AUTEUR}{tout}>`
  - `prive/objets/liste/item_inscription_adherent.html:14` (filtre_template, caller: `(squelette)`) -> `[(#VAL\|gestion_comptes_secondaires_active\|oui) <td class="nom"></td>]`
  - `prive/squelettes/contenu/adherents.html:258` (filtre_template, caller: `(squelette)`) -> `[(#VAL\|gestion_comptes_secondaires_active\|oui)`
  - `prive/squelettes/contenu/adherents_fonctions.php:244` (filtre_template, caller: `filtre_a_type_compte`) -> `* Utilisable dans les templates comme : [(#VAL\|gestion_comptes_secondaires_active\|oui) ...]`

### `gestions_places`

- Total occurrences: **48**
- Repartition: `appel_php`=29, `filtre_template`=19
- Occurrences:
  - `action/ajouter_activites.php:36` (appel_php, caller: `activites_insert`) -> `$gestions_places            = gestions_places($id_evenement);`
  - `action/modifier_activites.php:31` (appel_php, caller: `action_modifier_activites`) -> `$gestions_places          = gestions_places($id_evenement);`
  - `action/synchroniser_comptabilite_evenement.php:149` (appel_php, caller: `synchroniser_comptabilite_evenement`) -> `$gestions_places = gestions_places($id_evenement);`
  - `evenements.csv.html:3` (filtre_template, caller: `(squelette)`) -> `[#(#ID_EVENEMENT)];[(#DATE_DEBUT\|Affdate{d-m-Y})];[(#TITRE\|supprimer_numero\|textebrut\|utf8_decode)];[(#PLACES\|=={0}\|?{'',#PLACES})];[(#ID_EVENEMENT\|gestions_places\|table_valeur{nombre_total_inscrits})];[(#ID_EVENEMENT\|ge`
  - `evenements.csv.html:3` (filtre_template, caller: `(squelette)`) -> `[#(#ID_EVENEMENT)];[(#DATE_DEBUT\|Affdate{d-m-Y})];[(#TITRE\|supprimer_numero\|textebrut\|utf8_decode)];[(#PLACES\|=={0}\|?{'',#PLACES})];[(#ID_EVENEMENT\|gestions_places\|table_valeur{nombre_total_inscrits})];[(#ID_EVENEMENT\|ge`
  - `evenements.csv.html:9` (filtre_template, caller: `(squelette)`) -> `[(#ID_EVENEMENT\|gestions_places\|table_valeur{})]`
  - `export_activites.csv.html:5` (filtre_template, caller: `(squelette)`) -> `{inverse}>[#(#ID_EVENEMENT)];[(#DATE_DEBUT\|Affdate{d-m-Y})];<BOUCLE_articles(ARTICLES){id_article}><BOUCLE_rubriques(RUBRIQUES){id_rubrique}>"##ID_RUBRIQUE";["(#TITRE\|textebrut\|utf8_decode\|replace{'"','""'})"];</BOUCLE_r`
  - `export_activites.csv.html:5` (filtre_template, caller: `(squelette)`) -> `{inverse}>[#(#ID_EVENEMENT)];[(#DATE_DEBUT\|Affdate{d-m-Y})];<BOUCLE_articles(ARTICLES){id_article}><BOUCLE_rubriques(RUBRIQUES){id_rubrique}>"##ID_RUBRIQUE";["(#TITRE\|textebrut\|utf8_decode\|replace{'"','""'})"];</BOUCLE_r`
  - `export_activites.csv.html:5` (filtre_template, caller: `(squelette)`) -> `{inverse}>[#(#ID_EVENEMENT)];[(#DATE_DEBUT\|Affdate{d-m-Y})];<BOUCLE_articles(ARTICLES){id_article}><BOUCLE_rubriques(RUBRIQUES){id_rubrique}>"##ID_RUBRIQUE";["(#TITRE\|textebrut\|utf8_decode\|replace{'"','""'})"];</BOUCLE_r`
  - `export_activites.csv.html:5` (filtre_template, caller: `(squelette)`) -> `{inverse}>[#(#ID_EVENEMENT)];[(#DATE_DEBUT\|Affdate{d-m-Y})];<BOUCLE_articles(ARTICLES){id_article}><BOUCLE_rubriques(RUBRIQUES){id_rubrique}>"##ID_RUBRIQUE";["(#TITRE\|textebrut\|utf8_decode\|replace{'"','""'})"];</BOUCLE_r`
  - `export_evenements_compta.xml.html:241` (filtre_template, caller: `(squelette)`) -> `<Cell><Data ss:Type="Number">[(#ID_EVENEMENT\|gestions_places\|table_valeur{nombre_total_inscrits})]</Data></Cell>`
  - `export_evenements_compta.xml.html:242` (filtre_template, caller: `(squelette)`) -> `<Cell><Data ss:Type="Number">[(#ID_EVENEMENT\|gestions_places\|table_valeur{total_paiement_attente})]</Data></Cell>`
  - `export_evenements_compta.xml.html:243` (filtre_template, caller: `(squelette)`) -> `<Cell><Data ss:Type="Number">[(#ID_EVENEMENT\|gestions_places\|table_valeur{total_encaisse})]</Data></Cell>`
  - `formulaires/desinscription_evenement_public.php:7` (appel_php, caller: `formulaires_desinscription_evenement_public_charger_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement.php:48` (appel_php, caller: `formulaires_inscription_evenement_charger_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement.php:433` (appel_php, caller: `formulaires_inscription_evenement_verifier_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement.php:606` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:30` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:320` (appel_php, caller: `formulaires_inscription_evenement_multi_charger_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:358` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_1_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:379` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_2_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:439` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_3_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:494` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_4_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:516` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:37` (appel_php, caller: `formulaires_inscription_evenement_multi_public_saisies`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:245` (appel_php, caller: `formulaires_inscription_evenement_multi_public_charger_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:334` (appel_php, caller: `formulaires_inscription_evenement_multi_public_verifier_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:500` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_public.php:25` (appel_php, caller: `formulaires_inscription_evenement_public_charger_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `formulaires/inscription_evenement_public.php:390` (appel_php, caller: `formulaires_inscription_evenement_public_verifier_dist`) -> `$gestions_places = gestions_places($id_evenement); // Gestion des places disponibles.`
  - `formulaires/inscription_evenement_public.php:558` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `$gestions_places = gestions_places($id_evenement);`
  - `genie/association_expiration_auto_evenement.php:34` (appel_php, caller: `genie_association_expiration_auto_evenement_dist`) -> `$gestion_place = gestions_places($id_evenement);`
  - `inc/comptes.php:261` (appel_php, caller: `inserer_compte_activite`) -> `$gestions_places = gestions_places($row_activite['id_evenement']);`
  - `inc/comptes.php:366` (appel_php, caller: `inserer_compte_remboursement_activite`) -> `$gestions_places = gestions_places($row_activite['id_evenement']);`
  - `inc/fonctions/activite_calculator.php:31` (appel_php, caller: `activite_calculator`) -> `$gestion_places = gestions_places($id_evenement);`
  - `inc/fonctions/activite_enregistrement_calculator.php:25` (appel_php, caller: `activite_enregistrement_calculator`) -> `$gestion_places = gestions_places($id_evenement);`
  - `inc/fonctions/alerte_inscription_evenement.php:11` (appel_php, caller: `alerte_inscription_evenement`) -> `$gestions_places = gestions_places($id_evenement);`
  - `inc/fonctions/ouverture_inscription_evenement.php:17` (appel_php, caller: `ouverture_inscription_evenement`) -> `$gestions_places =gestions_places($id_evenement);`
  - `inc/fonctions/validation_attente_automatique.php:50` (appel_php, caller: `validation_attente_automatique`) -> `$gestions_places    = gestions_places($id_evenement);`
  - `inscriptions_evenement.csv.html:8` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:export_evenements_nb_inscrits:>}\|textebrut\|utf8_decode)];[(#ID_EVENEMENT\|gestions_places\|table_valeur{nombre_total_inscrits})];[`
  - `inscriptions_evenement.csv.html:10` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:export_evenements_total_paiement_attente:>}\|textebrut\|utf8_decode)];[(#ID_EVENEMENT\|gestions_places\|table_valeur{total_paiement_attente})];`
  - `inscriptions_evenement.csv.html:11` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:export_evenements_encaisse:>}\|textebrut\|utf8_decode)];[(#ID_EVENEMENT\|gestions_places\|table_valeur{total_encaisse})];]`
  - `prive/objets/liste/item_activite.html:6` (filtre_template, caller: `(squelette)`) -> `[(#SET{gestions_places,[(#ID_EVENEMENT\|gestions_places)]})]`
  - `prive/objets/liste/item_export_activites.html:7` (filtre_template, caller: `(squelette)`) -> `[(#SET{gestions_places,[(#ID_EVENEMENT\|gestions_places)]})]`
  - `prive/squelettes/contenu/inc-voir_activites/bloc_configuration.html:17` (filtre_template, caller: `(squelette)`) -> `[ (#ID_EVENEMENT\|gestions_places\|foreach)]-->`
  - `prive/squelettes/contenu/inc-voir_activites/bloc_configuration.html:20` (filtre_template, caller: `(squelette)`) -> `[(#SET{gestions_places,[(#ID_EVENEMENT\|gestions_places)]})]`
  - `prive/squelettes/contenu/inc-voir_activites/bloc_raccourcis.html:5` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_condition_si_plein(CONDITION){si #ID_EVENEMENT\|gestions_places\|table_valeur{plein}}>`
  - `prive/squelettes/contenu/voir_activites.html:11` (filtre_template, caller: `(squelette)`) -> `#SET{gestions_places, #ID_EVENEMENT\|gestions_places}`

### `get_config_plan_comptable_migration`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/migrer_asso_comptabilite.php:144` (appel_php, caller: `formulaires_migrer_asso_comptabilite_traiter_dist`) -> `$cfg = get_config_plan_comptable_migration();`

### `get_destination_rows`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/destinations.php:48` (appel_php, caller: `cadre_relief`) -> `$rows = get_destination_rows('', '', $id_destination);`

### `get_recherche_active_resume`

- Total occurrences: **1**
- Repartition: `filtre_template`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents.html:13` (filtre_template, caller: `(squelette)`) -> `[(#SET{recherche_resume,#VAL\|get_recherche_active_resume})]`

### `gis_auteur`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `association_pipelines.php:221` (appel_php, caller: `association_post_edition`) -> `gis_auteur($id_auteur, 'modification');`
  - `inc/fonctions/priviliges_adherent.php:165` (appel_php, caller: `desactiver_privileges_adherent`) -> `gis_auteur($id_auteur, 'suppression', $id_gis);`
  - `inc/fonctions/priviliges_adherent.php:304` (appel_php, caller: `verifier_privileges_adherent`) -> `gis_auteur($id_auteur, 'creation');`
  - `inc/fonctions/priviliges_adherent.php:306` (appel_php, caller: `verifier_privileges_adherent`) -> `gis_auteur($id_auteur, 'suppression', $id_gis);`

### `gis_geocode_google_format`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/gis_geocode.php:57` (appel_php, caller: `gis_geocode_request`) -> `$data = gis_geocode_google_format($data_googleapis);`

### `gis_geocode_request`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `action/gis_geocoder_rechercher.php:20` (appel_php, caller: `action_gis_geocoder_rechercher_dist`) -> `if ($data = gis_geocode_request(_request('mode'), $arguments)) {`
  - `action/gis_geocoder_rechercher.php:52` (appel_php, caller: `action_gis_geocoder_rechercher_dist`) -> `$data = gis_geocode_request(_request('mode'), $arguments);`
  - `inc/fonctions/gis_auteur.php:48` (appel_php, caller: `gis_auteur`) -> `$json = gis_geocode_request('search', [`

### `icone_association`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `association_pipelines.php:62` (appel_php, caller: `association_ajouter_menus`) -> `icone_association('association'),`
  - `association_pipelines.php:77` (appel_php, caller: `association_ajouter_menus`) -> `icone_association('association'),`

### `identification_contexte_inscription`

- Aucun appel detecte statiquement.

### `identifier_categories_necessite_justificatif`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/editer_asso_cotisation.php:184` (appel_php, caller: `formulaires_editer_asso_cotisation_saisies`) -> `$liste_categorie_cotisation_justificatif = identifier_categories_necessite_justificatif();`
  - `inc/api_cotisations.php:53` (appel_php, caller: `api_cotisations_saisies_communes`) -> `$liste_categorie_cotisation_justificatif = identifier_categories_necessite_justificatif();`

### `identifier_tresorier`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/configurer_association.php:1101` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `$nom_tresoriere=identifier_tresorier();`

### `inc_exporter_csv_dist`

- Aucun appel detecte statiquement.

### `information_expediteur_email_collectif`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/email_collectif_adherent.php:315` (appel_php, caller: `formulaires_email_collectif_adherent_charger_dist`) -> `$expediteur= information_expediteur_email_collectif();`

### `inscrire_participant_mailsubscriber`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `formulaires/inscription_evenement.php:642` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `inscrire_participant_mailsubscriber($data_form);`
  - `formulaires/inscription_evenement_multi.php:549` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `inscrire_participant_mailsubscriber($data_form);`
  - `formulaires/inscription_evenement_multi_public.php:540` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `inscrire_participant_mailsubscriber($data_form);`
  - `formulaires/inscription_evenement_public.php:596` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `inscrire_participant_mailsubscriber($data_form);`

### `inserer_asso_activites`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `formulaires/inscription_evenement.php:628` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `$id_activite = inserer_asso_activites($id_evenement, $data_form, $cal_result, $id_transaction, $nouvelle_transaction, $message_journal);`
  - `formulaires/inscription_evenement_multi.php:537` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `$id_activite = inserer_asso_activites($id_evenement, $data_form, $cal_result, $id_transaction, $nouvelle_transaction, $message_journal);`
  - `formulaires/inscription_evenement_multi_public.php:533` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$id_activite = inserer_asso_activites($id_evenement, $data_form, $cal_result, $id_transaction, $nouvelle_transaction, $message_journal,$ip_client);`
  - `formulaires/inscription_evenement_public.php:583` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `$id_activite = inserer_asso_activites($id_evenement, $data_form, $cal_result, $id_transaction, $nouvelle_transaction, $message_journal,$ip_client);`

### `inserer_compte`

- Total occurrences: **8**
- Repartition: `appel_php`=8
- Occurrences:
  - `action/editer_asso_comptes.php:45` (appel_php, caller: `action_editer_asso_comptes`) -> `$id_compte = inserer_compte(`
  - `formulaires/editer_asso_comptes.php:424` (appel_php, caller: `formulaires_editer_asso_comptes_traiter_dist`) -> `inserer_compte(`
  - `inc/comptes.php:303` (appel_php, caller: `inserer_compte_activite`) -> `return inserer_compte(`
  - `inc/comptes.php:374` (appel_php, caller: `inserer_compte_remboursement_activite`) -> `return inserer_compte(`
  - `inc/comptes.php:563` (appel_php, caller: `compte_vente`) -> `return inserer_compte(`
  - `inc/comptes.php:580` (appel_php, caller: `compte_vente_frais_envoi`) -> `return inserer_compte(`
  - `inc/comptes.php:598` (appel_php, caller: `compte_don`) -> `return inserer_compte(`
  - `inc/comptes.php:637` (appel_php, caller: `compte_cotisation`) -> `return inserer_compte(`

### `inserer_compte_activite`

- Total occurrences: **5**
- Repartition: `appel_php`=5
- Occurrences:
  - `action/synchroniser_comptabilite_evenement.php:150` (appel_php, caller: `synchroniser_comptabilite_evenement`) -> `$id_paiement = inserer_compte_activite($id_activite, $gestions_places);`
  - `formulaires/inscription_evenement.php:632` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `$id_compte = inserer_compte_activite($id_activite,$gestions_places);`
  - `formulaires/inscription_evenement_multi.php:541` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `inserer_compte_activite($id_activite,$gestions_places);`
  - `formulaires/inscription_evenement_multi_public.php:558` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `inserer_compte_activite($id_activite, $gestions_places);`
  - `formulaires/inscription_evenement_public.php:590` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `$id_compte = inserer_compte_activite($id_activite,$gestions_places);`

### `inserer_compte_remboursement_activite`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `action/synchroniser_comptabilite_evenement.php:170` (appel_php, caller: `synchroniser_comptabilite_evenement`) -> `$id_remboursement = inserer_compte_remboursement_activite($id_transaction, $id_activite);`
  - `formulaires/rembourser_transaction.php:56` (appel_php, caller: `formulaires_rembourser_transaction_traiter_dist`) -> `inserer_compte_remboursement_activite($id_transaction, intval($query_activite['id_activite'] ?? 0));`

### `inserer_transaction_activites`

- Total occurrences: **8**
- Repartition: `appel_php`=8
- Occurrences:
  - `formulaires/inscription_evenement.php:621` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `$id_transaction = inserer_transaction_activites($data_form['montant_total'],$data_form['id_auteur']);`
  - `formulaires/inscription_evenement.php:653` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `$id_transaction = inserer_transaction_activites($data_form['montant_total'],$data_form['id_auteur']);`
  - `formulaires/inscription_evenement_multi.php:531` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `$id_transaction = inserer_transaction_activites($calculer_montant_total['montant_total'],$data_form['id_auteur']);`
  - `formulaires/inscription_evenement_multi.php:562` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `$id_transaction = inserer_transaction_activites($calculer_montant_total['montant_total'],$data_form['id_auteur']);`
  - `formulaires/inscription_evenement_multi_public.php:526` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$id_transaction = inserer_transaction_activites($calculer_montant_total['montant_total'], $id_auteur);`
  - `formulaires/inscription_evenement_multi_public.php:569` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$id_transaction = inserer_transaction_activites($calculer_montant_total['montant_total'], $data_form['id_auteur']);`
  - `formulaires/inscription_evenement_public.php:576` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `$id_transaction = inserer_transaction_activites($data_form['montant_total'],$data_form['id_auteur']);`
  - `formulaires/inscription_evenement_public.php:618` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `$id_transaction = inserer_transaction_activites($data_form['montant_total'], $data_form['id_auteur']);`

### `insert_jqueryui`

- Total occurrences: **1**
- Repartition: `filtre_template`=1
- Occurrences:
  - `association_options.php:531` (filtre_template, caller: `droit_auteur_evenements`) -> `@$GLOBALS['spip_pipeline']['jqueryui_plugins'] .= "\|insert_jqueryui";`

### `inverser_relation_compte`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `inc/cotisations.php:164` (appel_php, caller: `changer_statut_cotisation`) -> `inverser_relation_compte($id_auteur);`
  - `inc/cotisations.php:213` (appel_php, caller: `changer_statut_cotisation`) -> `inverser_relation_compte($id_auteur);`

### `is_db_value_true`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `association_autoriser.php:152` (appel_php, caller: `association_normalize_qui`) -> `* Remplace l'ancien is_db_value_true($GLOBALS['association_metas'][$page]).`
  - `association_autoriser.php:160` (appel_php, caller: `association_module_actif`) -> `&& is_db_value_true($GLOBALS['association_metas'][$module]);`
  - `inc/destinations.php:88` (appel_php, caller: `destinations_are_enabled`) -> `return is_db_value_true($GLOBALS['association_metas']['destinations']);`

### `is_successful_reglement`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_pipelines.php:355` (appel_php, caller: `association_trig_bank_notifier_reglement`) -> `if (!is_successful_reglement($flux['args'])) {`

### `labels_adherents`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/edit_labels.php:64` (appel_php, caller: `exec_edit_labels`) -> `$corps = labels_adherents($statut_interne);`

### `liste_colonnes_dynamiques_adherents`

- Total occurrences: **2**
- Repartition: `appel_php`=1, `filtre_template`=1
- Occurrences:
  - `prive/squelettes/contenu/adherents.html:6` (filtre_template, caller: `(squelette)`) -> `[(#SET{colonnes_dyn,#VAL\|liste_colonnes_dynamiques_adherents})]`
  - `prive/squelettes/contenu/adherents_fonctions.php:779` (appel_php, caller: `association_defaut_tri_adherents`) -> `foreach (liste_colonnes_dynamiques_adherents() as $nom => $def) {`

### `liste_filtres_dynamiques_adherents`

- Total occurrences: **7**
- Repartition: `appel_php`=6, `filtre_template`=1
- Occurrences:
  - `inc/adherents_search_context.php:160` (appel_php, caller: `(global)`) -> `$dyn_defs = function_exists('liste_filtres_dynamiques_adherents') ? liste_filtres_dynamiques_adherents() : array();`
  - `prive/squelettes/contenu/adherents.html:5` (filtre_template, caller: `(squelette)`) -> `[(#SET{filtres_dyn,#VAL\|liste_filtres_dynamiques_adherents})]`
  - `prive/squelettes/contenu/adherents_fonctions.php:625` (appel_php, caller: `filtre_filtres_effectifs`) -> `if ($dyn = liste_filtres_dynamiques_adherents()) {`
  - `prive/squelettes/contenu/adherents_fonctions.php:793` (appel_php, caller: `filtre_liste_noms_filtres_dynamiques_dist`) -> `foreach (liste_filtres_dynamiques_adherents() as $nom => $def) {`
  - `prive/squelettes/contenu/adherents_fonctions.php:811` (appel_php, caller: `filtre_filtres_dynamiques_actifs_dist`) -> `foreach (liste_filtres_dynamiques_adherents() as $nom => $def) {`
  - `prive/squelettes/contenu/adherents_fonctions.php:821` (appel_php, caller: `filtre_url_supprimer_filtres_dynamiques_dist`) -> `$dyn = liste_filtres_dynamiques_adherents();`
  - `prive/squelettes/contenu/adherents_fonctions.php:835` (appel_php, caller: `filtre_liste_filtres_reset_adherents_dist`) -> `foreach (liste_filtres_dynamiques_adherents() as $nom => $def) {`

### `liste_periodes_cotisations`

- Total occurrences: **2**
- Repartition: `filtre_template`=2
- Occurrences:
  - `association_fonctions.php:873` (filtre_template, caller: `filtre_filtres_effectifs_cotisations`) -> `* Wrapper utilisé par les squelettes : #VAL\|liste_periodes_cotisations`
  - `prive/squelettes/contenu/cotisations.html:23` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_periodes(DATA){source table, #VAL\|liste_periodes_cotisations}>`

### `liste_responsables_evenement`

- Total occurrences: **11**
- Repartition: `appel_php`=5, `filtre_template`=6
- Occurrences:
  - `action/envoyer_email_collectif_activite.php:62` (appel_php, caller: `action_envoyer_email_collectif_activite`) -> `$respo = liste_responsables_evenement($id_evenement);`
  - `action/envoyer_email_collectif_activite.php:68` (appel_php, caller: `action_envoyer_email_collectif_activite`) -> `$respo = liste_responsables_evenement($id_evenement);`
  - `association_autoriser.php:907` (appel_php, caller: `autoriser_voir_activites_dist`) -> `$resp = liste_responsables_evenement(intval($id));`
  - `docs/todo/rework_liste_responsables.md:139` (filtre_template, caller: `(squelette)`) -> `- d'autres squelettes FO/BO qui utilisent `#ID_EVENEMENT\|liste_responsables_evenement` ou `liste_responsables_evenement()``
  - `exec/action_email_collectif_activite.php:55` (appel_php, caller: `exec_action_email_collectif_activite`) -> `$activite_responsables = liste_responsables_evenement($id_evenement);`
  - `export_activites.csv.html:5` (filtre_template, caller: `(squelette)`) -> `{inverse}>[#(#ID_EVENEMENT)];[(#DATE_DEBUT\|Affdate{d-m-Y})];<BOUCLE_articles(ARTICLES){id_article}><BOUCLE_rubriques(RUBRIQUES){id_rubrique}>"##ID_RUBRIQUE";["(#TITRE\|textebrut\|utf8_decode\|replace{'"','""'})"];</BOUCLE_r`
  - `export_compta.xml.html:136` (filtre_template, caller: `(squelette)`) -> `<Cell ss:MergeAcross="4" ss:StyleID="s75"><Data ss:Type="String">[(#SET{id_responsables,[(#ID_EVENEMENT\|liste_responsables_evenement)]})]<BOUCLE_responsables(AUTEURS){id_auteur IN #GET{id_responsables}}{' & '}>[(#PRENOM)`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:47` (appel_php, caller: `facteur_envoyer_mail_activites`) -> `$activite_responsable = liste_responsables_evenement($id_evenement, '');`
  - `inscriptions_evenement.csv.html:15` (filtre_template, caller: `(squelette)`) -> `<B_responsables_activites>"[(#VAL{<:asso:export_evenements_responsables:>}\|textebrut\|utf8_decode)]";"<BOUCLE_responsables_activites(AUTEURS){id_auteur IN #ID_EVENEMENT\|liste_responsables_evenement{#ENV{id_article}}}{stat`
  - `prive/squelettes/contenu/inc-voir_activites/bloc_info_evenement.html:10` (filtre_template, caller: `(squelette)`) -> `[(#SET{ids_responsable,#ID_EVENEMENT\|liste_responsables_evenement\|table_valeur{auteur_array}})]`
  - `prive/squelettes/contenu/voir_activites.html:9` (filtre_template, caller: `(squelette)`) -> `#SET{activite_responsable, #ID_EVENEMENT\|liste_responsables_evenement}`

### `lister_compte_recurive`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/importer_plan_comptable.php:144` (appel_php, caller: `lister_comptes_json`) -> `lister_compte_recurive($class['Comptes'], $comptes);`
  - `formulaires/importer_plan_comptable.php:165` (appel_php, caller: `lister_compte_recurive`) -> `lister_compte_recurive($compte['SousComptes'], $comptesList);`

### `lister_comptes_json`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/importer_plan_comptable.php:89` (appel_php, caller: `formulaires_importer_plan_comptable_traiter_dist`) -> `$array_comptes_json = lister_comptes_json($json_data);`

### `lister_destination_recurive`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/importer_destination_comptable.php:126` (appel_php, caller: `lister_destinations_json`) -> `lister_destination_recurive($Destinations['SousDestinations'], $destinationsList);`
  - `formulaires/importer_destination_comptable.php:138` (appel_php, caller: `lister_destination_recurive`) -> `lister_destination_recurive($destination['SousDestinations'], $destinationsList);`

### `lister_destinations_json`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/importer_destination_comptable.php:82` (appel_php, caller: `formulaires_importer_destination_comptable_traiter_dist`) -> `$array_destinations_json=lister_destinations_json($json_data);`

### `lister_label_info_supplementaire`

- Total occurrences: **1**
- Repartition: `filtre_template`=1
- Occurrences:
  - `inscriptions_evenement.csv.html:28` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:export_inscriptions_evenement_num_evenement:>}\|textebrut\|utf8_decode)];[(#VAL{<:asso:export_inscriptions_evenement_prenom:>}\|textebrut\|utf8_decode)];[(#VAL{<:asso:export_inscriptions_evenement_nom:>}\|texteb`

### `listes_notifications`

- Total occurrences: **8**
- Repartition: `filtre_template`=8
- Occurrences:
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:23` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_notif_activites_preinscrit(DATA){source table, #VAL{preinscription_activite}\|listes_notifications}{par cle}>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:45` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_notif_activites_inscrit(DATA){source table, #VAL{inscription_activite}\|listes_notifications}{par cle}>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:65` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_notif_activites_attente(DATA){source table, #VAL{attente_activite}\|listes_notifications}{par cle}>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_activite.html:86` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_notif_activites_desinscription(DATA){source table, #VAL{desinscription_activite}\|listes_notifications}{par cle}>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_cotisation.html:2` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_notif_adhesion(DATA){source table, #VAL{adhesion}\|listes_notifications}{par cle}>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_cotisation.html:34` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_notif_echeances(DATA){source table, #VAL{echeances}\|listes_notifications}{par cle}>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_validation.html:7` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_notif_defaut(DATA){source table, #VAL{creation}\|listes_notifications}{par cle}>`
  - `prive/squelettes/contenu/inc-notifications/inc-tableau_notif_validation.html:23` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_notif_validation(DATA){si #CONFIG{inscription3/valider_comptes}\|=={on}}{source table, #VAL{validation}\|listes_notifications}{par cle}>`

### `mailsubscribers_synchro_list_newsletter_liste_conjoints`

- Aucun appel detecte statiquement.

### `mailsubscribers_synchro_list_newsletter_statut_interne_echu`

- Aucun appel detecte statiquement.

### `mailsubscribers_synchro_list_newsletter_statut_interne_ok`

- Aucun appel detecte statiquement.

### `mailsubscribers_synchro_list_newsletter_statut_interne_prospect`

- Aucun appel detecte statiquement.

### `mailsubscribers_synchro_list_newsletter_statut_interne_relance`

- Aucun appel detecte statiquement.

### `maintenance_build_human_summary`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/configurer_association.php:2169` (appel_php, caller: `maintenance_build_human_summary`) -> `$summary_html = maintenance_build_human_summary($resume, 5);`

### `mise_a_jour_cotisation`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_pipelines.php:376` (appel_php, caller: `association_trig_bank_notifier_reglement`) -> `mise_a_jour_cotisation($query_cotisation, $query_transaction);`

### `mise_a_jour_participation`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_pipelines.php:367` (appel_php, caller: `association_trig_bank_notifier_reglement`) -> `mise_a_jour_participation($query_activite, $query_transaction);`

### `modifier_activite_vente_frais_envoi`

- Aucun appel detecte statiquement.

### `modifier_asso_activites`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `formulaires/inscription_evenement.php:669` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `modifier_asso_activites($id_activite,$data_form,$cal_result,$id_transaction,$nouvelle_transaction,$message_journal);`
  - `formulaires/inscription_evenement_multi.php:578` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `modifier_asso_activites($id_activite,$data_form,$cal_result,$id_transaction,$nouvelle_transaction,$message_journal);`
  - `formulaires/inscription_evenement_multi_public.php:580` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `modifier_asso_activites($id_activite, $data_form, $cal_result, $id_transaction, ($nouvelle_transaction ?? ''), $message_journal,$ip_client);`
  - `formulaires/inscription_evenement_public.php:634` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `modifier_asso_activites($id_activite,$data_form,$cal_result,$id_transaction,$nouvelle_transaction,$message_journal,$ip_client);`

### `modifier_compte`

- Total occurrences: **9**
- Repartition: `appel_php`=9
- Occurrences:
  - `action/editer_asso_comptes.php:51` (appel_php, caller: `action_editer_asso_comptes`) -> `modifier_compte(`
  - `formulaires/editer_asso_comptes.php:411` (appel_php, caller: `formulaires_editer_asso_comptes_traiter_dist`) -> `modifier_compte(`
  - `inc/comptes.php:454` (appel_php, caller: `modifier_compte_activite`) -> `// Si le montant diffère, on met à jour via modifier_compte (qui mettra à jour les champs nécessaires)`
  - `inc/comptes.php:456` (appel_php, caller: `modifier_compte_activite`) -> `modifier_compte(`
  - `inc/comptes.php:475` (appel_php, caller: `modifier_compte_activite`) -> `modifier_compte(`
  - `inc/comptes.php:666` (appel_php, caller: `modifier_compte_vente`) -> `modifier_compte(`
  - `inc/comptes.php:685` (appel_php, caller: `modifier_activite_vente_frais_envoi`) -> `modifier_compte(`
  - `inc/comptes.php:705` (appel_php, caller: `modifier_compte_don`) -> `modifier_compte(`
  - `inc/comptes.php:743` (appel_php, caller: `modifier_compte_cotisation`) -> `modifier_compte(`

### `modifier_compte_activite`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `formulaires/inscription_evenement.php:662` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `modifier_compte_activite($id_activite,$id_transaction);`
  - `formulaires/inscription_evenement_multi.php:570` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `modifier_compte_activite($id_activite,$id_transaction);`
  - `formulaires/inscription_evenement_multi_public.php:587` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `modifier_compte_activite($id_activite,$id_transaction);`
  - `formulaires/inscription_evenement_public.php:627` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `modifier_compte_activite($id_activite,$id_transaction);`

### `modifier_compte_cotisation`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/api_cotisations.php:291` (appel_php, caller: `api_traiter_cotisation`) -> `modifier_compte_cotisation(`

### `modifier_compte_don`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/editer_asso_dons.php:47` (appel_php, caller: `action_editer_asso_dons`) -> `modifier_compte_don(`

### `modifier_compte_vente`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `action/editer_asso_ventes.php:73` (appel_php, caller: `ventes_modifier`) -> `modifier_compte_vente($id_compte, $date_vente, $recette+$frais_envoi, $justification, $journal);`
  - `action/editer_asso_ventes.php:75` (appel_php, caller: `ventes_modifier`) -> `modifier_compte_vente($id_compte, $date_vente, $recette, $justification, $journal);`

### `modifier_transaction_activites`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `formulaires/inscription_evenement.php:656` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `modifier_transaction_activites($data_form['montant_total'], $id_transaction);`
  - `formulaires/inscription_evenement_multi.php:565` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `modifier_transaction_activites($calculer_montant_total['montant_total'], $id_transaction);`
  - `formulaires/inscription_evenement_multi_public.php:572` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `modifier_transaction_activites($calculer_montant_total['montant_total'], $id_transaction);`
  - `formulaires/inscription_evenement_public.php:621` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `modifier_transaction_activites($data_form['montant_total'], $id_transaction);`

### `montant_signe`

- Total occurrences: **2**
- Repartition: `filtre_template`=2
- Occurrences:
  - `export_comptes_evenement.csv.html:13` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_comptes(ASSO_COMPTES){id_evenement=#ID_EVENEMENT}{par date}{inverse}>[#(#ID_COMPTE)];[(#DATE\|affdate{'d/m/Y'})];[(#ID_TRANSACTION\|?{#ID_TRANSACTION, -})];[#IMPUTATION];[(#RECETTE\|montant_signe{#DEPENSE})];["(#JUS`
  - `export_comptes_evenement.csv_fonctions.php:19` (filtre_template, caller: `(global)`) -> `* [(#RECETTE\|montant_signe{#DEPENSE})]`

### `nettoyage_liste_config_inscription3`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inc/adherents_recherche_avancee.php:213` (appel_php, caller: `adherents_recherche_avancee_multicritere_saisie`) -> `$liste_champs_table = nettoyage_liste_config_inscription3();`
  - `formulaires/inc/adherents_recherche_avancee.php:267` (appel_php, caller: `preparer_criteres_adherents`) -> `$liste_champs_table = nettoyage_liste_config_inscription3();`

### `nettoyer_doublons_comptabilite`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/synchroniser_comptabilite_evenement.php:73` (appel_php, caller: `synchroniser_comptabilite_evenement`) -> `$doublons_supprimes = nettoyer_doublons_comptabilite($id_evenement);`

### `notifications_cotisation_autoriser_test`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/test_notification_cotisation.php:12` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `? notifications_cotisation_autoriser_test()`

### `notifications_cotisation_trouver_sujet`

- Total occurrences: **5**
- Repartition: `appel_php`=5
- Occurrences:
  - `inc/cotisations.php:485` (appel_php, caller: `notifier_cotisation_adherent`) -> `$sujet = notifications_cotisation_trouver_sujet($type, $contexte_notification);`
  - `inc/cotisations.php:849` (appel_php, caller: `notifier_cotisation_admin`) -> `$sujet = notifications_cotisation_trouver_sujet('attente_admin', $contexte_notification);`
  - `inc/cotisations.php:868` (appel_php, caller: `notifier_cotisation_admin`) -> `$sujet = notifications_cotisation_trouver_sujet('demande_admin', $contexte_notification);`
  - `inc/cotisations.php:886` (appel_php, caller: `notifier_cotisation_admin`) -> `$sujet = notifications_cotisation_trouver_sujet('encaissement_admin', $contexte_notification);`
  - `prive/squelettes/contenu/notifications_fonctions.php:256` (appel_php, caller: `listes_notifications`) -> `$sujet = notifications_cotisation_trouver_sujet($type_map, $contexte_min);`

### `notifier_cotisation_adherent`

- Total occurrences: **13**
- Repartition: `appel_php`=13
- Occurrences:
  - `action/test_notification_cotisation.php:255` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `$ok = notifier_cotisation_adherent($fake_query_cotisation, $fake_query_categories, $fake_query_transaction, $type_map, $opts);`
  - `inc/cotisations.php:131` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'validation_pre-paiement');`
  - `inc/cotisations.php:139` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'attente_paiement');`
  - `inc/cotisations.php:183` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'activation');`
  - `inc/cotisations.php:186` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'validation_pre-paiement');`
  - `inc/cotisations.php:189` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'attente_paiement');`
  - `inc/cotisations.php:192` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'attente_paiement');`
  - `inc/cotisations.php:196` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_adherent($query_cotisation, $query_categories, $query_transaction, 'attente_paiement');`
  - `inc/cotisations.php:223` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_adherent($query_cotisation,$query_categories,$query_transaction,'activation');`
  - `inc/cotisations.php:237` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_adherent($query_cotisation,$query_categories,$query_transaction,'validation_post-paiement');`
  - `inc/cotisations.php:407` (appel_php, caller: `notifier_cotisation_adherent`) -> `// du type notifier_cotisation_adherent(array(), array(), array(), 'activation', ['id_auteur' => X])`
  - `inc/cotisations.php:1007` (appel_php, caller: `activer_adherent`) -> `notifier_cotisation_adherent(array(), array(), array(), 'activation', array('id_auteur' => $id_secondaire, 'use_queue' => true));`
  - `inc/fonctions/association_job_notifier_echeance.php:52` (appel_php, caller: `association_job_notifier_echeance`) -> `return notifier_cotisation_adherent($query_cotisation, $query_categories, array(), $notification_type, $options);`

### `notifier_cotisation_admin`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `action/test_notification_cotisation.php:211` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `$ok = notifier_cotisation_admin($fake_query_cotisation, $fake_query_categories, $fake_query_transaction, $type_map, $opts);`
  - `inc/cotisations.php:134` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_admin($query_cotisation, $query_categories, $query_transaction, 'attente_validation');`
  - `inc/cotisations.php:142` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_admin($query_cotisation, $query_categories, $query_transaction,  'attente_paiement');`
  - `inc/cotisations.php:241` (appel_php, caller: `changer_statut_cotisation`) -> `notifier_cotisation_admin($query_cotisation, $query_categories, $query_transaction, 'encaissement_paiement');`

### `notifier_cotisation_preparer_contexte`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `action/test_notification_cotisation.php:84` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `$contexte = notifier_cotisation_preparer_contexte($query_cot, $query_cat ?: array(), $query_tx ?: array());`
  - `action/test_notification_cotisation.php:108` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `$contexte = notifier_cotisation_preparer_contexte($query_cot, $query_cat ?: array(), $query_tx ?: array());`
  - `inc/cotisations.php:409` (appel_php, caller: `notifier_cotisation_adherent`) -> `$contexte_notification = notifier_cotisation_preparer_contexte($query_cotisation, $query_categories, $query_transaction, $options);`
  - `inc/cotisations.php:800` (appel_php, caller: `notifier_cotisation_admin`) -> `$contexte_notification = notifier_cotisation_preparer_contexte($query_cotisation, $query_categories, $query_transaction);`

### `notifier_inscription_activite`

- Total occurrences: **8**
- Repartition: `appel_php`=8
- Occurrences:
  - `formulaires/inscription_evenement.php:637` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `notifier_inscription_activite($id_activite, $id_evenement, $cal_result['statut'], 'prive');`
  - `formulaires/inscription_evenement.php:673` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `notifier_inscription_activite($id_activite,$id_evenement,'modification_backend',$prive_ou_public = 'prive');`
  - `formulaires/inscription_evenement_multi.php:545` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `notifier_inscription_activite($id_activite, $id_evenement, $cal_result['statut'], 'prive');`
  - `formulaires/inscription_evenement_multi.php:582` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `notifier_inscription_activite($id_activite,$id_evenement,'modification_backend',$prive_ou_public = 'prive');`
  - `formulaires/inscription_evenement_multi_public.php:536` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `notifier_inscription_activite($id_activite, $id_evenement, $cal_result['statut'], 'public');`
  - `formulaires/inscription_evenement_multi_public.php:583` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `notifier_inscription_activite($id_activite, $id_evenement, 'modification_frontend', 'public');`
  - `formulaires/inscription_evenement_public.php:586` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `notifier_inscription_activite($id_activite, $id_evenement, $cal_result['statut'], 'public');`
  - `formulaires/inscription_evenement_public.php:637` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `notifier_inscription_activite($id_activite,$id_evenement,'modification_frontend',$prive_ou_public = 'public');`

### `obtenir_emails_tresoriers`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/cotisations.php:814` (appel_php, caller: `notifier_cotisation_admin`) -> `$emails_tres = obtenir_emails_tresoriers();`

### `ouverture_inscription_evenement`

- Total occurrences: **15**
- Repartition: `appel_php`=12, `filtre_template`=3
- Occurrences:
  - `formulaires/desinscription_evenement_public.php:8` (appel_php, caller: `formulaires_desinscription_evenement_public_charger_dist`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement.php:49` (appel_php, caller: `formulaires_inscription_evenement_charger_dist`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:31` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:321` (appel_php, caller: `formulaires_inscription_evenement_multi_charger_dist`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:357` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_1_dist`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:378` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_2_dist`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:438` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_3_dist`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi.php:493` (appel_php, caller: `formulaires_inscription_evenement_multi_verifier_4_dist`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:255` (appel_php, caller: `formulaires_inscription_evenement_multi_public_charger_dist`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:331` (appel_php, caller: `formulaires_inscription_evenement_multi_public_verifier_dist`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `formulaires/inscription_evenement_public.php:28` (appel_php, caller: `formulaires_inscription_evenement_public_charger_dist`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `inc/fonctions/eligibilite_modification_evenement.php:22` (appel_php, caller: `eligibilite_modification_evenement`) -> `$ouverture_inscription_evenement = ouverture_inscription_evenement($id_evenement);`
  - `prive/objets/liste/item_activite.html:5` (filtre_template, caller: `(squelette)`) -> `[(#SET{statut_inscription,[(#ID_EVENEMENT\|ouverture_inscription_evenement)]})]`
  - `prive/objets/liste/item_export_activites.html:6` (filtre_template, caller: `(squelette)`) -> `[(#SET{statut_inscription,[(#ID_EVENEMENT\|ouverture_inscription_evenement)]})]`
  - `prive/squelettes/contenu/inc-voir_activites/bloc_configuration.html:19` (filtre_template, caller: `(squelette)`) -> `[(#SET{statut_inscription,[(#ID_EVENEMENT\|ouverture_inscription_evenement)]})]`

### `page_cadre_relief`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `exec/action_destinations.php:27` (appel_php, caller: `exec_action_destinations`) -> `page_cadre_relief(`
  - `exec/action_plan.php:27` (appel_php, caller: `exec_action_plan`) -> `page_cadre_relief(`
  - `exec/destinations.php:25` (appel_php, caller: `exec_destinations`) -> `page_cadre_relief(`

### `page_fond`

- Total occurrences: **5**
- Repartition: `appel_php`=5
- Occurrences:
  - `exec/edit_compte.php:28` (appel_php, caller: `exec_editer_asso_comptes`) -> `page_fond(`
  - `exec/edit_destination.php:26` (appel_php, caller: `exec_edit_destination`) -> `page_fond(`
  - `exec/edit_plan.php:26` (appel_php, caller: `exec_edit_plan`) -> `page_fond(`
  - `exec/edit_ressource.php:27` (appel_php, caller: `exec_edit_ressource`) -> `page_fond(`
  - `inc/page.php:11` (appel_php, caller: `page_cadre_relief`) -> `page_fond($title, $selected_onglet, $boite_info, $raccourcis, $cadre_relief . fin_cadre_relief(), $options);`

### `page_no_cadre_relief`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `exec/plan_comptable.php:24` (appel_php, caller: `exec_plan_comptable`) -> `page_no_cadre_relief(`
  - `exec/ressources.php:25` (appel_php, caller: `exec_ressources`) -> `page_no_cadre_relief(`

### `page_no_fond`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `inc/page.php:15` (appel_php, caller: `page_no_cadre_relief`) -> `page_no_fond($title, $selected_onglet, $boite_info, $raccourcis, $options);`
  - `inc/page.php:19` (appel_php, caller: `page_fond`) -> `page_no_fond($title, $selected_onglet, $boite_info, $raccourcis, $options);`

### `parser_emails_depuis_config`

- Total occurrences: **13**
- Repartition: `appel_php`=13
- Occurrences:
  - `action/test_notification_cotisation.php:48` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `$parsed = parser_emails_depuis_config($raw);`
  - `action/test_notification_cotisation.php:193` (appel_php, caller: `action_test_notification_cotisation_dist`) -> `$parsed = parser_emails_depuis_config($email);`
  - `inc/cotisations.php:476` (appel_php, caller: `notifier_cotisation_adherent`) -> `$emails_adh = parser_emails_depuis_config(isset($GLOBALS['association_metas']['config_destinataires_creation_cotisation_adh']) ? $GLOBALS['association_metas']['config_destinataires_creation_cotisation_adh'] : '');`
  - `inc/cotisations.php:1180` (appel_php, caller: `obtenir_emails_tresoriers`) -> `return parser_emails_depuis_config($s);`
  - `inc/fonctions/facteur_envoyer_app.php:42` (appel_php, caller: `facteur_envoyer_app`) -> `$parsed = parser_emails_depuis_config($str);`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:239` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `$norm = parser_emails_depuis_config(is_array($email_responsable) ? implode(',', $email_responsable) : $email_responsable);`
  - `inc/fonctions/facteur_envoyer_mail_activites.php:298` (appel_php, caller: `facteur_envoyer_mail_activite_responsable`) -> `$parsed_emails = parser_emails_depuis_config(`
  - `inc/fonctions/facteur_envoyer_notification_gis.php:28` (appel_php, caller: `facteur_envoyer_notification_gis`) -> `$destinataires = parser_emails_depuis_config($destinataire_meta);`
  - `inc/fonctions/facteur_envoyer_recu_adhesion.php:130` (appel_php, caller: `facteur_envoyer_recu_adhesion`) -> `$bcc_parsed = parser_emails_depuis_config($bcc_meta);`
  - `inc/fonctions/facteur_envoyer_recu_participation.php:118` (appel_php, caller: `facteur_envoyer_recu_participation`) -> `$bcc_parsed = parser_emails_depuis_config($bcc_meta);`
  - `inc/notifications_emails.php:35` (appel_php, caller: `association_collecter_destinataires_admins`) -> `$norm = parser_emails_depuis_config($row['input_email_membres_asso']);`
  - `inc/notifications_emails.php:42` (appel_php, caller: `association_collecter_destinataires_admins`) -> `$norm = parser_emails_depuis_config($row['email']);`
  - `inc/notifications_emails.php:53` (appel_php, caller: `association_collecter_destinataires_admins`) -> `$norm = parser_emails_depuis_config($dest_supp);`

### `periode_date_debut`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/cotisations_fonctions.php:27` (appel_php, caller: `preparer_liste_cotisations`) -> `$date_debut = periode_date_debut($periode);`

### `periode_date_fin`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/squelettes/contenu/cotisations_fonctions.php:28` (appel_php, caller: `preparer_liste_cotisations`) -> `$date_fin = periode_date_fin($periode);`

### `periode_defaut_libelle`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `association_fonctions.php:849` (appel_php, caller: `filtre_filtres_effectifs_cotisations`) -> `$periode = periode_defaut_libelle();`
  - `prive/squelettes/contenu/adherents_fonctions.php:584` (appel_php, caller: `filtre_filtres_effectifs`) -> `$eff['periode'] = periode_defaut_libelle();`

### `preparer_chargement_modification_inscription`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inscription_evenement.php:24` (appel_php, caller: `formulaires_inscription_evenement_charger_dist`) -> `$data_activite = preparer_chargement_modification_inscription($id_activite);`
  - `formulaires/inscription_evenement_public.php:52` (appel_php, caller: `formulaires_inscription_evenement_public_charger_dist`) -> `$data_activite = preparer_chargement_modification_inscription($id_activite);`

### `preparer_chargement_modification_inscription_multi`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `formulaires/inscription_evenement_multi.php:35` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$data_activite = preparer_chargement_modification_inscription_multi($id_activite,'prive');`
  - `formulaires/inscription_evenement_multi.php:331` (appel_php, caller: `formulaires_inscription_evenement_multi_charger_dist`) -> `$data_activite = preparer_chargement_modification_inscription_multi($id_activite,'prive');`
  - `formulaires/inscription_evenement_multi_public.php:284` (appel_php, caller: `formulaires_inscription_evenement_multi_public_charger_dist`) -> `$data_activite = preparer_chargement_modification_inscription_multi($id_activite, 'public');`

### `preparer_choix_mode_paiement`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/configurer_association.php:1030` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `$choix_mode_paiement = preparer_choix_mode_paiement();`

### `preparer_criteres_adherents`

- Total occurrences: **5**
- Repartition: `appel_php`=5
- Occurrences:
  - `exec/adherents_bck.php:60` (appel_php, caller: `exec_adherents`) -> `$criteres_sql = preparer_criteres_adherents($_REQUEST);`
  - `formulaires/email_collectif_adherent.php:278` (appel_php, caller: `formulaires_email_collectif_adherent_charger_dist`) -> `$criteres_sql = preparer_criteres_adherents($_POST);`
  - `formulaires/inc/adherents_recherche_avancee.php:641` (appel_php, caller: `afficher_resultat_recherche_avancee`) -> `$criteres_sql = preparer_criteres_adherents($contexte);`
  - `inc/adherents_search_context.php:336` (appel_php, caller: `(global)`) -> `$critere_avance = preparer_criteres_adherents($this->criteres_avances);`
  - `inc/adherents_search_context.php:470` (appel_php, caller: `(global)`) -> `return preparer_criteres_adherents($criteres_rapide);`

### `preparer_entree_journal`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `formulaires/inscription_evenement.php:625` (appel_php, caller: `formulaires_inscription_evenement_traiter_dist`) -> `$message_journal = preparer_entree_journal($cal_result['statut'], 'prive');`
  - `formulaires/inscription_evenement_multi.php:534` (appel_php, caller: `formulaires_inscription_evenement_multi_traiter_dist`) -> `$message_journal = preparer_entree_journal($cal_result['statut'], 'prive');`
  - `formulaires/inscription_evenement_multi_public.php:530` (appel_php, caller: `formulaires_inscription_evenement_multi_public_traiter_dist`) -> `$message_journal = preparer_entree_journal($cal_result['statut'], 'public');`
  - `formulaires/inscription_evenement_public.php:580` (appel_php, caller: `formulaires_inscription_evenement_public_traiter_dist`) -> `$message_journal = preparer_entree_journal($cal_result['statut'], 'public');`

### `preparer_info_auteur`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `formulaires/inscription_evenement_multi.php:180` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$info_auteur = preparer_info_auteur($id_auteur,$id_evenement);`
  - `formulaires/inscription_evenement_multi.php:213` (appel_php, caller: `formulaires_inscription_evenement_multi_saisies`) -> `$info_auteur = preparer_info_auteur($id_auteur,$id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:58` (appel_php, caller: `formulaires_inscription_evenement_multi_public_saisies`) -> `$info_auteur_connecte = preparer_info_auteur($GLOBALS['visiteur_session']['id_auteur'],$id_evenement);`
  - `formulaires/inscription_evenement_multi_public.php:338` (appel_php, caller: `formulaires_inscription_evenement_multi_public_verifier_dist`) -> `$info_auteur_connecte = preparer_info_auteur($GLOBALS['visiteur_session']['id_auteur'],$id_evenement);`

### `preparer_liste_adherents`

- Total occurrences: **3**
- Repartition: `appel_php`=1, `filtre_template`=2
- Occurrences:
  - `prive/squelettes/contenu/adherents.html:2` (filtre_template, caller: `(squelette)`) -> `[(#SET{criteres_sql,#VAL{criteres_sql}\|preparer_liste_adherents})]`
  - `prive/squelettes/contenu/adherents.html:3` (filtre_template, caller: `(squelette)`) -> `[(#SET{liste_adherents,#VAL{array_auteur}\|preparer_liste_adherents\|trier_colonne_dynamique})]`
  - `prive/squelettes/contenu/adherents_fonctions.php:468` (appel_php, caller: `preparer_liste_adherents`) -> `association_log('adherents', '=== DEBUT preparer_liste_adherents (context) ===', 'debug');`

### `preparer_liste_asso_destination_comptable`

- Total occurrences: **6**
- Repartition: `appel_php`=6
- Occurrences:
  - `formulaires/configurer_association.php:1419` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_destination_comptable(),`
  - `formulaires/configurer_association.php:1468` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_destination_comptable(),`
  - `formulaires/configurer_association.php:1502` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_destination_comptable(),`
  - `formulaires/configurer_association.php:1545` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_destination_comptable(),`
  - `formulaires/importer_destination_comptable.php:60` (appel_php, caller: `formulaires_importer_destination_comptable_charger_dist`) -> `$contexte =preparer_liste_asso_destination_comptable('array');`
  - `formulaires/importer_destination_comptable.php:84` (appel_php, caller: `formulaires_importer_destination_comptable_traiter_dist`) -> `$destinations = array_keys(preparer_liste_asso_destination_comptable('array'));`

### `preparer_liste_asso_plan_classe`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/configurer_association.php:1351` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_plan_classe(),`
  - `formulaires/configurer_association.php:1352` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'defaut' => array_key_exists('5',preparer_liste_asso_plan_classe('array')) ? '5' : '',`

### `preparer_liste_asso_plan_compte`

- Total occurrences: **15**
- Repartition: `appel_php`=15
- Occurrences:
  - `formulaires/configurer_association.php:1399` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte('data_saisies','1'),`
  - `formulaires/configurer_association.php:1409` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte('data_saisies','7'),`
  - `formulaires/configurer_association.php:1438` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte('data_saisies','1'),`
  - `formulaires/configurer_association.php:1448` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte('data_saisies','7'),`
  - `formulaires/configurer_association.php:1458` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte('data_saisies','6'),`
  - `formulaires/configurer_association.php:1493` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte(),`
  - `formulaires/configurer_association.php:1527` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte(),`
  - `formulaires/configurer_association.php:1536` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte(),`
  - `formulaires/configurer_association.php:1570` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte(),`
  - `formulaires/editer_asso_comptes.php:104` (appel_php, caller: `formulaires_editer_asso_comptes_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte('data_saisies'),`
  - `formulaires/importer_plan_comptable.php:60` (appel_php, caller: `formulaires_importer_plan_comptable_charger_dist`) -> `$contexte = preparer_liste_asso_plan_compte('array');`
  - `formulaires/importer_plan_comptable.php:90` (appel_php, caller: `formulaires_importer_plan_comptable_traiter_dist`) -> `$comptes = array_keys(preparer_liste_asso_plan_compte('array'));`
  - `formulaires/importer_plan_comptable.php:180` (appel_php, caller: `generer_saisies_comptes`) -> `$compte_existant = preparer_liste_asso_plan_compte('array');`
  - `formulaires/migrer_asso_comptabilite.php:65` (appel_php, caller: `formulaires_migrer_asso_comptabilite_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte('data_saisies','1'),`
  - `formulaires/migrer_asso_comptabilite.php:76` (appel_php, caller: `formulaires_migrer_asso_comptabilite_saisies_dist`) -> `'data' => preparer_liste_asso_plan_compte('data_saisies','7'),`

### `preparer_liste_auteurs_newsletter`

- Total occurrences: **4**
- Repartition: `appel_php`=4
- Occurrences:
  - `association_fonctions.php:312` (appel_php, caller: `mailsubscribers_synchro_list_newsletter_statut_interne_ok`) -> `$auteurs = preparer_liste_auteurs_newsletter('ok');`
  - `association_fonctions.php:321` (appel_php, caller: `mailsubscribers_synchro_list_newsletter_statut_interne_prospect`) -> `$auteurs = preparer_liste_auteurs_newsletter('prospect');`
  - `association_fonctions.php:330` (appel_php, caller: `mailsubscribers_synchro_list_newsletter_statut_interne_echu`) -> `$auteurs = preparer_liste_auteurs_newsletter('echu');`
  - `association_fonctions.php:339` (appel_php, caller: `mailsubscribers_synchro_list_newsletter_statut_interne_relance`) -> `$auteurs = preparer_liste_auteurs_newsletter('relance');`

### `preparer_liste_categories`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `inc/api_cotisations.php:59` (appel_php, caller: `api_cotisations_saisies_communes`) -> `$categories = preparer_liste_categories($id_auteur, $origine, $reinscription, $type_adherent);`
  - `inc/cotisations.php:716` (appel_php, caller: `notifier_cotisation_preparer_contexte`) -> `$liste_categories_adherents = preparer_liste_categories($id_auteur, 'prive', $reinscription, $type_auteur_adherent);`

### `preparer_liste_champs_filtres`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `formulaires/configurer_association.php:1150` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_champs_filtres(),`
  - `formulaires/configurer_association.php:1200` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_champs_filtres(),`
  - `formulaires/configurer_association.php:1222` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_champs_filtres(),`

### `preparer_liste_compte_imputation`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/migrer_asso_comptabilite.php:22` (appel_php, caller: `formulaires_migrer_asso_comptabilite_saisies_dist`) -> `$liste_compte_imputation = preparer_liste_compte_imputation();`

### `preparer_liste_cotisations`

- Total occurrences: **1**
- Repartition: `filtre_template`=1
- Occurrences:
  - `prive/squelettes/contenu/cotisations.html:90` (filtre_template, caller: `(squelette)`) -> `[(#SET{liste_cotisations,#VAL\|preparer_liste_cotisations{array}})]`

### `preparer_liste_evenements`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/editer_asso_comptes.php:91` (appel_php, caller: `formulaires_editer_asso_comptes_saisies_dist`) -> `'data' => preparer_liste_evenements(),`

### `preparer_liste_mailsubscribinglists`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/configurer_association.php:239` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_mailsubscribinglists(),`
  - `formulaires/configurer_association.php:480` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_mailsubscribinglists(),`

### `preparer_liste_zones`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/configurer_association.php:229` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `'data' => preparer_liste_zones(), // This will be filled dynamically with zones`

### `prets_insert`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/ajouter_prets.php:32` (appel_php, caller: `action_ajouter_prets`) -> `prets_insert($id_ressource, $id_emprunteur, $date_sortie, $duree, $date_retour, $journal, $montant, $imputation, $commentaire_sortie,$commentaire_retour);`

### `prets_modifier`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/modifier_prets.php:33` (appel_php, caller: `action_modifier_prets`) -> `prets_modifier($duree, $date_sortie, $date_retour, $id_emprunteur, $commentaire_sortie, $id_pret, $journal, $montant);`

### `raccourcis`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `exec/destinations.php:29` (appel_php, caller: `exec_destinations`) -> `raccourcis(),`
  - `exec/plan_comptable.php:28` (appel_php, caller: `exec_plan_comptable`) -> `raccourcis(),`
  - `exec/ressources.php:29` (appel_php, caller: `exec_ressources`) -> `raccourcis()`

### `radio_type_adherent`

- Total occurrences: **5**
- Repartition: `appel_php`=3, `filtre_template`=2
- Occurrences:
  - `prive/squelettes/contenu/adherents_fonctions.php:189` (appel_php, caller: `filtre_liste_type_adherent`) -> `// 3) Fallback : si une fonction helper existe (plugins), utiliser radio_type_adherent()`
  - `prive/squelettes/contenu/adherents_fonctions.php:191` (appel_php, caller: `filtre_liste_type_adherent`) -> `$r = radio_type_adherent();`
  - `prive/squelettes/contenu/notifications.html:67` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_filtre_radio_type_adherent(DATA){si #ENV{tab,adhesion}\|in_array{#LISTE{validation,adhesion}}}{source table,#VAL\|radio_type_adherent}{si #VAL\|radio_type_adherent\|count\|>{1}}{' \| '}>`
  - `prive/squelettes/contenu/notifications.html:67` (filtre_template, caller: `(squelette)`) -> `<BOUCLE_filtre_radio_type_adherent(DATA){si #ENV{tab,adhesion}\|in_array{#LISTE{validation,adhesion}}}{source table,#VAL\|radio_type_adherent}{si #VAL\|radio_type_adherent\|count\|>{1}}{' \| '}>`
  - `prive/squelettes/contenu/notifications_fonctions.php:21` (appel_php, caller: `exemple_adherent_par_type`) -> `//$array_radio_type_adherent = radio_type_adherent();`

### `relances_while`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/edit_relances.php:60` (appel_php, caller: `exec_edit_relances`) -> `$corps = relances_while($statut_interne);`

### `remboursement_prefixe`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/rembourser_transaction.php:30` (appel_php, caller: `formulaires_rembourser_transaction_charger_dist`) -> `'_autorisation_id_prefixe' => remboursement_prefixe(),`
  - `formulaires/rembourser_transaction.php:49` (appel_php, caller: `formulaires_rembourser_transaction_traiter_dist`) -> `$raison_remboursement = "<hr />\n".date('Y-m-d H:i:s').' REMBOURSEMENT '.remboursement_prefixe()." : ".$raison;`

### `request_statut_interne_table_destinataire_mail_collectif`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `exec/edit_email_collectif_adherent.php:31` (appel_php, caller: `exec_edit_email_collectif_adherent`) -> `$critere = request_statut_interne_table_destinataire_mail_collectif();`
  - `exec/edit_mail.php:32` (appel_php, caller: `exec_edit_mail`) -> `$critere = request_statut_interne_table_destinataire_mail_collectif();`

### `responsables_evenement`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `base/association_champs_extras.php:73` (appel_php, caller: `association_preparer_donnees_communes`) -> `if ($liste_responsable = responsables_evenement($id_evenement, $id_article)) {`

### `roles_association`

- Total occurrences: **3**
- Repartition: `appel_php`=1, `filtre_template`=2
- Occurrences:
  - `association_fonctions.php:485` (appel_php, caller: `filtre_roles_association`) -> `$roles = roles_association();`
  - `prive/squelettes/contenu/inc-benevoles/table_benevoles.html:1` (filtre_template, caller: `(squelette)`) -> `<!--[(#SET{roles,#LISTE{fonction,fonction_secondaire}\|roles_association})]-->`
  - `prive/squelettes/contenu/inc-benevoles/table_benevoles.html:3` (filtre_template, caller: `(squelette)`) -> `[(#SET{roles,#VAL\|roles_association})]`

### `saisies_extraire_groupes_chaine`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/fonctions/roles_association.php:38` (appel_php, caller: `roles_association`) -> `$groupes = is_string($datas) ? saisies_extraire_groupes_chaine($datas) : [];`

### `solde_evenement`

- Total occurrences: **1**
- Repartition: `filtre_template`=1
- Occurrences:
  - `export_comptes_evenement.csv.html:8` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:solde:>}\|textebrut\|utf8_decode)];[(#ID_EVENEMENT\|solde_evenement)];`

### `stats_compta_activites_exercice`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `inc/fonctions/comptes.php:378` (appel_php, caller: `filtre_stats_compta_activites_exercice`) -> `return stats_compta_activites_exercice($exercice);`
  - `prive/squelettes/contenu/analyse_compta_activites_fonctions.php:18` (appel_php, caller: `(global)`) -> `* Cette fonction étend stats_compta_activites_exercice() en ajoutant`

### `stats_compta_activites_lister_evenements_exercice`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `inc/fonctions/comptes.php:488` (appel_php, caller: `filtre_stats_compta_activites_lister_evenements_exercice`) -> `return stats_compta_activites_lister_evenements_exercice($exercice);`

### `supprimer_adherents`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/action_adherents.php:42` (appel_php, caller: `exec_action_adherents_args`) -> `echo supprimer_adherents($_POST["delete"]);`

### `supprimer_compte_activite`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `action/gerer_activites.php:105` (appel_php, caller: `action_gerer_activites`) -> `supprimer_compte_activite($id_activite);`
  - `formulaires/desinscription_evenement_public.php:64` (appel_php, caller: `formulaires_desinscription_evenement_public_traiter_dist`) -> `supprimer_compte_activite($id_activite);`

### `supprimer_comptes`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `exec/action_comptes.php:42` (appel_php, caller: `exec_action_comptes_args`) -> `echo supprimer_comptes($compte_ids);`

### `synchroniser_comptabilite_evenement`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `action/synchroniser_comptabilite_evenement.php:30` (appel_php, caller: `action_synchroniser_comptabilite_evenement_dist`) -> `$resultat = synchroniser_comptabilite_evenement($id_evenement);`
  - `formulaires/migrer_asso_comptabilite.php:152` (appel_php, caller: `formulaires_migrer_asso_comptabilite_traiter_dist`) -> `synchroniser_comptabilite_evenement($id_evenement);`

### `table_comptabilite_activites_compter_operations`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `prive/objets/liste/table_comptabilite_activites_fonctions.php:125` (appel_php, caller: `table_comptabilite_activites_stats`) -> `'nb_recettes' => table_comptabilite_activites_compter_operations($id_evenement, 'recette', $vu),`
  - `prive/objets/liste/table_comptabilite_activites_fonctions.php:126` (appel_php, caller: `table_comptabilite_activites_stats`) -> `'nb_depenses' => table_comptabilite_activites_compter_operations($id_evenement, 'depense', $vu),`
  - `prive/objets/liste/table_comptabilite_activites_fonctions.php:127` (appel_php, caller: `table_comptabilite_activites_stats`) -> `'nb_toutes' => table_comptabilite_activites_compter_operations($id_evenement, 'toutes', $vu),`

### `table_comptabilite_activites_criteres_sens`

- Aucun appel detecte statiquement.

### `table_comptabilite_activites_get_filtre_sens`

- Aucun appel detecte statiquement.

### `table_comptabilite_activites_montants`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `prive/objets/liste/table_comptabilite_activites_fonctions.php:131` (appel_php, caller: `table_comptabilite_activites_stats`) -> `$montants = table_comptabilite_activites_montants($id_evenement, $vu);`

### `table_comptabilite_activites_stats`

- Total occurrences: **1**
- Repartition: `filtre_template`=1
- Occurrences:
  - `prive/objets/liste/table_comptabilite_activites.html:14` (filtre_template, caller: `(squelette)`) -> `[(#SET{compta_stats,[(#ENV{id_evenement}\|table_comptabilite_activites_stats{#GET{vu_effectif}})]})]`

### `total_depenses_evenement`

- Total occurrences: **2**
- Repartition: `appel_php`=1, `filtre_template`=1
- Occurrences:
  - `export_comptes_evenement.csv.html:7` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:sorties:>}\|textebrut\|utf8_decode)];[(#ID_EVENEMENT\|total_depenses_evenement)];`
  - `export_comptes_evenement.csv_fonctions.php:68` (appel_php, caller: `solde_evenement`) -> `return total_recettes_evenement($id_evenement) - total_depenses_evenement($id_evenement);`

### `total_recettes_evenement`

- Total occurrences: **2**
- Repartition: `appel_php`=1, `filtre_template`=1
- Occurrences:
  - `export_comptes_evenement.csv.html:6` (filtre_template, caller: `(squelette)`) -> `[(#VAL{<:asso:entrees:>}\|textebrut\|utf8_decode)];[(#ID_EVENEMENT\|total_recettes_evenement)];`
  - `export_comptes_evenement.csv_fonctions.php:68` (appel_php, caller: `solde_evenement`) -> `return total_recettes_evenement($id_evenement) - total_depenses_evenement($id_evenement);`

### `traiter_upload_justificatif`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `inc/api_cotisations.php:306` (appel_php, caller: `api_traiter_cotisation`) -> `$documents = traiter_upload_justificatif($id_auteur, $id_compte, $params['documents']);`
  - `inc/api_cotisations.php:308` (appel_php, caller: `api_traiter_cotisation`) -> `$documents = traiter_upload_justificatif($id_auteur, $id_compte);`

### `trierparMontant`

- Aucun appel detecte statiquement.

### `trouver_periode_par_libelle`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `association_fonctions.php:794` (appel_php, caller: `periode_date_debut`) -> `$p = trouver_periode_par_libelle($libelle);`
  - `association_fonctions.php:806` (appel_php, caller: `periode_date_fin`) -> `$p = trouver_periode_par_libelle($libelle);`

### `update_destination_contexte_from_compte`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/editer_asso_dons.php:54` (appel_php, caller: `formulaires_editer_asso_dons_charger_dist`) -> `update_destination_contexte_from_compte($contexte, $id_compte, 'dons');`
  - `formulaires/editer_asso_ventes.php:55` (appel_php, caller: `formulaires_editer_asso_ventes_charger_dist`) -> `update_destination_contexte_from_compte($contexte, $id_compte, 'ventes');`

### `valeur_colonne_dynamique_adherent`

- Total occurrences: **1**
- Repartition: `filtre_template`=1
- Occurrences:
  - `prive/objets/liste/item_inscription_adherent.html:30` (filtre_template, caller: `(squelette)`) -> `[(#SET{valeur,#ID_AUTEUR\|valeur_colonne_dynamique_adherent{#VALEUR{nom}}})]`

### `validation_attente_automatique`

- Total occurrences: **2**
- Repartition: `appel_php`=1, `filtre_template`=1
- Occurrences:
  - `genie/association_expiration_auto_evenement.php:84` (appel_php, caller: `genie_association_expiration_auto_evenement_dist`) -> `validation_attente_automatique($id_evenement_concerne,'cron_expiration');`
  - `prive/squelettes/contenu/voir_activites.html:6` (filtre_template, caller: `(squelette)`) -> `[(#ID_EVENEMENT\|validation_attente_automatique{page_prive_voir_activites})]`

### `valider_compte_activite`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `association_pipelines.php:433` (appel_php, caller: `mise_a_jour_participation`) -> `valider_compte_activite($id_transaction);`

### `ventes_insert`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/editer_asso_ventes.php:47` (appel_php, caller: `action_editer_asso_ventes`) -> `$id_vente = ventes_insert($date_vente, $article, $code, $acheteur, $id_acheteur, $quantite, $date_envoi, $frais_envoi, $prix_vente, $commentaire, $journal, $recette);`

### `ventes_modifier`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `action/editer_asso_ventes.php:45` (appel_php, caller: `action_editer_asso_ventes`) -> `ventes_modifier($date_vente, $article, $code, $acheteur, $id_acheteur, $quantite, $date_envoi, $frais_envoi, $prix_vente, $commentaire, $id_vente, $journal, $justification, $recette, $id_compte);`

### `verifier_categorie_adherent_entreprise`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/configurer_association.php:385` (appel_php, caller: `formulaires_configurer_association_saisies_dist`) -> `if(($config == 'entreprise' OR empty($config)) AND verifier_categorie_adherent_entreprise()) {`

### `verifier_code_postal_dist`

- Aucun appel detecte statiquement.

### `verifier_destination_comptable`

- Total occurrences: **1**
- Repartition: `appel_php`=1
- Occurrences:
  - `formulaires/editer_asso_dons.php:78` (appel_php, caller: `formulaires_editer_asso_dons_verifier_dist`) -> `verifier_destination_comptable($argent, 'argent', $erreurs);`

### `verifier_privileges_adherent`

- Total occurrences: **3**
- Repartition: `appel_php`=3
- Occurrences:
  - `genie/association_taches_generales.php:102` (appel_php, caller: `genie_association_taches_generales`) -> `verifier_privileges_adherent($id_auteur);`
  - `inc/cotisations.php:953` (appel_php, caller: `activer_adherent`) -> `verifier_privileges_adherent($id_auteur, $reinscription);`
  - `inc/cotisations.php:960` (appel_php, caller: `activer_adherent`) -> `verifier_privileges_adherent($id_auteur, $reinscription);`

### `verifier_spam_formulaire_inscription`

- Total occurrences: **2**
- Repartition: `appel_php`=2
- Occurrences:
  - `formulaires/inscription_evenement_multi_public.php:370` (appel_php, caller: `formulaires_inscription_evenement_multi_public_verifier_dist`) -> `$spam_erreurs = verifier_spam_formulaire_inscription($_POST, 'email_inscrit_1', 'prenom_inscrit_1', 'nom_inscrit_1', true);`
  - `formulaires/inscription_evenement_public.php:535` (appel_php, caller: `formulaires_inscription_evenement_public_verifier_dist`) -> `if ($spam_erreurs = verifier_spam_formulaire_inscription($_POST, 'email_inscrit', 'prenom_inscrit_1', 'nom_inscrit_1', true)) {`
