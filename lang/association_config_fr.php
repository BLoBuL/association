<?php
// FICHIER DE LANGUE ne concernant que la configuration du plugin
/***************************************************************************
*  Associaspip, extension de SPIP pour gestion d'associations             *
*                                                                         *
*  Copyright (c) 2007 Bernard Blazin & Francois de Montlivault (V1)       *
*  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
*  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
*                                                                         *
*  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
*  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
if (!defined("_ECRIRE_INC_VERSION")) return;
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
$traductions = array(

'oui' => 'Oui',
'non' => 'Non',
'oui_recommande' => 'Oui (recommandé)',
'non_recommande' => 'Non (recommandé)',
'oui_defaut' => 'Oui (par défaut)',
'non_defaut' => 'Non (par défaut)',
'desactive_par_defaut' => 'Désactivé (par défaut)',
'toujours' => 'Toujours',
'toujours_defaut' => 'Toujours (par défaut)',
'jamais' => 'Jamais',
'jamais_defaut' => 'Jamais (par défaut)',

// Ajout de clés manquantes utilisées dans le formulaire de configuration
'config_info_enregistree' => 'Paramètres enregistrés',
'jour' => 'jour',
'jours' => 'jours',

// Clés pour les tests de notification et le bloc entreprise
'config_test_notification_emails_label' => 'Adresses de test pour notifications',
'config_test_notification_emails_explication' => 'Adresse(s) e-mail séparées par des virgules utilisées pour envoyer un test de notification aux comptes entreprise (ex. test_notif_entreprise@blobul.com).',

// Localisation pour les adresses de test spécifiques aux entreprises
'config_test_notification_emails_entreprise_label' => 'Adresses de test pour notifications (entreprises)',
'config_test_notification_emails_entreprise_explication' => 'Adresse(s) e‑mail séparées par des virgules utilisées pour envoyer un test de notification aux comptes de type « entreprise » (ex. contact@entreprise.fr).',

'config_entreprise_fieldset_label' => 'Paramètres entreprise',
'config_entreprise_fieldset_explication' => 'Paramètres spécifiques aux comptes entreprise (notifications, listes de diffusion, etc.)',

'notification_echeance_notifier_echu_entreprise_label' => 'Notifier l\'adhérent entreprise quand il passe dans les échus',
'notification_echeance_notifier_echu_entreprise_explication' => 'Si activé, les comptes de type entreprise recevront une notification lorsqu\'ils passent en état « échus ».',

// Menu de navigation
'navigation_config_info' => 'Informations générales',
'navigation_config_adhesion' => 'Adhésions',
'navigation_config_entreprise' => 'Entreprises',
'navigation_config_evenement' => 'Evénements',
'navigation_config_evenement_defaut' => 'Configuration par défaut',
'navigation_config_mode_paiement' => 'Modes de paiement',
'navigation_config_segments' => 'Segments des listes de diffusions',
'navigation_config_modules' => 'Modules',
'navigation_config_comptabilite' => 'Comptabilité',
'navigation_config_notifications' => 'Test notifications',
'navigation_config_affichage_prive' => 'Affichage privé',
'navigation_config_affichage_public' => 'Affichage public',
'navigation_config_maintenance_bdd' => 'Maintenance BDD',
'navigation_config_debug' => 'Debug',
// Clé pour label annuaire public
'config_annuaire_public_label' => 'Annuaire public',
// Menu de configuration
'titre_page_configurer_association' => 'Paramètres du module de gestion d\'association',

// Configuration de l'association
'config_info_asso_fieldset' => 'Données de l\'association',

'config_nom_label' => 'Nom',
'config_email_label' => 'Adresse courriel',
'config_email_explication_multi' => 'Email(s) de contact de l\'association (séparé(s) par des virgules ou points-virgules si plusieurs)',
'config_adresse_label' => 'Adresse',
'config_rue_label' => 'Rue',
'config_num_rue_label' => 'N&deg;',
'config_ville_label' => 'Ville',
'config_codepostal_label' => 'Code Postal',
'config_pays_label' => 'Pays',
'config_telephone_label' => 'Téléphone',
'config_siret_label' => 'N&deg; SIRET',
'config_declaration_label' => 'N&deg; de déclaration',
'config_num_enregistrement_label' => 'Numéro d\'enregistrement (SIRET ou autre)',
'config_info_complementaire_label' => 'Informations complémentaires',

// Configuration des cotisations
'config_cotisation_fieldset' => 'Configuration des adhésions',
'config_cotisations_multidevises_label' => 'Cotisations multidevises',
'config_cotisations_multidevises_explication' => 'Permet de choisir une devise différente de celle du site pour chaque catégorie de cotisation. Cette option est désactivée par défaut.',
'config_cotisation_entreprise_fieldset' => 'Configuration des cotisations entreprise',

'config_cotisation_entreprise_fieldset' => 'Configuration des adhésions (entreprises)',

'config_validite_label' => 'Choisissez le type d\'année',
'config_choix_scolaire' => 'Scolaire',
'config_choix_annee' => 'Anniversaire (date d\'inscription + 1an)',

'config_annee_suivante_label' => 'Début des réinscriptions',
'config_nouvelle_annee_label' => 'Expiration des cotisations',
'config_scolaire_uniquement' => 'Au format jj/mm',

'config_privileges_fieldset_label' => 'Privilèges des adhérents',
'config_privileges_fieldset_explication' => 'Permet de définir les privilèges des adhérents à jour de leur cotisation',
'config_carte_adherent_label' => 'Carte adhérent',
'config_carte_adherent_explication' => 'Cette carte virtuelle permet aux adhérents de prouver leur adhésion à votre association',
'config_zones_label' => 'Zone restreinte',
'config_zones_explication' => 'Choisissez la zone restreinte du site à laquelle vos adhérents à jour peuvent avoir accès.',
'fieldset_config_taxe_formulaire_label' => 'Taxe locale',
'config_taxe_label_adhesion_label' => 'Taxe locale sur les adhésions',
'config_taxe_label_participation_evenement_label' => 'Taxe locale sur les participations aux événements',
'config_taxe_explication' => 'Spécifier la valeur du pourcentage de la taxe locale à appliquer, laisser vide si non-applicable.',

'config_liste_diffusion_label' => 'Liste(s) de diffusion',
'config_liste_diffusion_explication' => 'Les adhérents y seront inscrits automatiquement à la validation de leur cotisation',

// Gestion des comptes secondaires (conjoints, enfants)
'config_compte_secondaire_fieldset_label' => 'Gestion des comptes secondaires',
'config_compte_secondaire_fieldset_explication' => 'Permet de gérer les comptes secondaires (conjoints, enfants) liés à un compte principal',
'config_compte_secondaire_label' => 'Activer la gestion des comptes secondaires',
'config_compte_secondaire_explication' => 'Permet de créer des comptes secondaires (conjoint, enfants) rattachés à un compte principal. Ces comptes apparaîtront dans une colonne distincte dans la liste des adhérents.',
'config_compte_secondaire_activation_label' => 'Activation automatique des comptes secondaire',
'config_compte_secondaire_activation_explication' => 'Les comptes secondaires se voient attribué automatiquement les privilèges liés aux adhérents à jour de leur cotisation (accès aux zones restreintes, liste de diffusion etc...)',

// Options liées aux enfants / limite d'âge lors des inscriptions
'config_enfants_fieldset' => 'Configuration — enfants',
'config_age_limit_enfants_label' => 'Limite d\'âge pour les enfants (ans)',
'config_age_limit_enfants_explication' => 'Saisissez l\'âge maximal autorisé pour un enfant lors de l\'inscription. Laisser vide pour désactiver la vérification globale. La détection se fait sur les champs dont le nom contient à la fois "enfant" et "naissance".',

'config_donation_fieldset_label' => 'Dons à l\'association',
'config_donation_fieldset_explication' => 'Permet de configurer les options liées aux dons',
'config_donation_label' => 'Activer les dons',
'config_donation_explication' => 'Proposer aux adhérents de faire des dons à l\'association lors de leur adhésion',
'config_donation_defaut_label' => 'Don par défaut',
'config_donation_defaut_explication' => 'Montant par défaut pour les dons. Vous pouvez proposer une liste de montant séparé par des virgules.',

'config_modalites_inscription_fieldset_label' => 'Modalités d\'inscription (cotisation)',
'config_modalites_inscription_fieldset_explication' => 'Sélectionnez les pages uniques (CGU, règlement intérieur, charte…) que l\'adhérent devra accepter lors de son inscription ou réinscription.',
'config_modalites_inscription_pages_label' => 'Pages à accepter',
'config_modalites_inscription_pages_explication' => 'Seules les pages uniques publiées (articles sans rubrique) sont listées ici.',

'config_modalites_evenement_fieldset_label' => 'Modalités d\'inscription (événements)',
'config_modalites_evenement_fieldset_explication' => 'Sélectionnez les pages uniques que le participant devra accepter lors de son inscription à un événement. Si aucune page n\'est sélectionnée, la page CGU sera utilisée par défaut.',
'config_modalites_evenement_pages_label' => 'Pages à accepter',
'config_modalites_evenement_pages_explication' => 'Seules les pages uniques publiées (articles sans rubrique) sont listées ici.',

'config_annuaire_membre_fieldset' => 'Annuaire des membres',
'config_filtres_annuaire_label' => 'Choix des critères de filtrage:',
'config_filtres_annuaire_explication' => 'Permet de choisir les filtres disponible dans la colonne de gauche de l\'annuaire des membres',
'config_filtres_tableau_fieldset' => 'Filtres du tableau des adhérents',
'config_filtres_tableau_explication' => 'Choisissez les champs extras affichés comme filtres supplémentaires sur la page des adhérents.',
'config_champs_filtres_adherents_label' => 'Champs disponibles',
'config_champs_filtres_adherents_explication' => 'Seuls les champs extras de type liste (select) ou boutons radio peuvent être utilisés comme filtres dynamiques.',
'config_colonnes_tableau_fieldset' => 'Colonnes personnalisées du tableau adhérents',
'config_colonnes_tableau_explication' => 'Choisissez les champs extras affichés comme colonnes supplémentaires dans la liste des adhérents.',
'config_champs_colonnes_tableau_fieldset' => 'Champs / colonnes du tableau',
'config_champs_colonnes_tableau_explication' => 'Définissez ici les colonnes affichées dans le tableau des adhérents.',
'config_champs_colonnes_adherents_label' => 'Colonnes supplémentaires',
'config_champs_colonnes_adherents_explication' => 'Seuls les champs extras de type liste (select) ou radio peuvent être ajoutés comme colonnes.',
'config_choix_code_postal' => 'Code postal',
'config_choix_quartier' => 'Quartier',
'config_choix_ville' => 'Ville',

// Statuts affichés dans la liste publique des inscrits
'config_statuts_liste_publique_inscrits_fieldset' => 'Liste publique des inscrits — statuts affichés',
'config_statuts_liste_publique_inscrits_explication' => 'Choisissez quels statuts d\'inscription sont visibles dans la liste publique des participants à un événement. Les inscrits confirmés sont toujours affichés.',
'config_statuts_liste_publique_ok_fixe' => '✔ <b>Inscrits confirmés</b> (statut « ok ») — toujours affichés.',
'config_statuts_liste_publique_options_label' => 'Afficher également :',
'config_statuts_liste_publique_preinscrit' => 'Préinscrits (en attente de validation)',
'config_statuts_liste_publique_liste_attente' => 'Inscrits en liste d\'attente',

'config_notification_recu_paiement_fieldset' => 'Notifications email liées au reçu d\'adhésion',

'config_envoi_recu_paiement_adhesion_label' => 'Reçu d\'encaissement',
'config_envoi_recu_paiement_adhesion_explication' => 'Envoyer un reçu email suite à l\'encaissement d\'une adhesion',

'config_envoi_recu_paiement_cc_label' => 'Adresse email de copie des reçus',
'config_envoi_recu_paiement_cc_explication' => 'Ce compte email recevra une copie (cachée) de tous les reçus de paiement',
'config_envoi_recu_paiement_cc_explication_multi' => 'Adresse(s) email en copie carbone (BCC) pour les reçus (séparé(s) par des virgules ou points-virgules si plusieurs)',

'config_notification_adhesion_fieldset' => 'Notifications email liées à l\'adhésion',
'config_envoi_validation_paiement_adhesion_label' => 'Notification encaissement pour les réinscrits',
'config_envoi_validation_paiement_adhesion_explication' => 'Permets de choisir si on veut envoyer l\'email de confirmation de validation dans le cas d\'une réinscription ',

'config_notification_adherent_echu_label' => 'Notifier l\'adhérent quand il passe dans les échus',
'config_choix_echeance_label' => 'Notification de prévention avant échéance',
'config_choix_echeance_entreprise_label' => 'Notification de prévention avant échéance (entreprises)',
'config_choix_echeance_entreprise_explication' => 'Choisissez les relances anticipées spécifiques aux comptes entreprises. Laisser vide pour reprendre la configuration générique.',
'config_choix_echeance_60' => 'Deux mois avant échéance',
'config_choix_echeance_30' => 'Un mois avant échéance',
'config_choix_echeance_15' => 'Deux semaines avant échéance',
'config_choix_echeance_7' => 'Une semaine avant échéance',

'config_notification_creation_cotisation_fieldset' => 'Notifications création de cotisation',
'config_notification_creation_cotisation_explication' => 'Configurer les destinataires en copie cachée (BCC) des notifications envoyées lors de la création d\'une cotisation. Ces adresses recevront une copie des e‑mails normalement adressés à l\'adhérent. Indiquez une ou plusieurs adresses séparées par des virgules.',
'config_destinataires_creation_cotisation_label_adh' => 'Destinataire(s) - adhérent(s) (copie BCC)',
'config_destinataires_creation_cotisation_explication_adh' => 'Adresse(s) e‑mail externes qui recevront en copie cachée (BCC) les notifications normalement envoyées à l\'adhérent. Laisser vide pour désactiver l\'envoi de copies.',
'config_destinataires_creation_cotisation_label_tresorier' => 'Destinataire(s) - trésorier(ère)',
'config_destinataires_creation_cotisation_explication_tresorier' => 'Adresse(s) e‑mail des trésorier(ère)s ou bénévoles qui recevront les notifications liées à la création, la modification et la validation des cotisations (séparées par des virgules).',
'config_notification_creation_cotisation_entreprise_fieldset' => 'Notifications création de cotisation (entreprise)',
'config_notification_creation_cotisation_entreprise_explication' => 'Configurer les destinataires en copie cachée (BCC) des notifications envoyées lors de la création d\'une cotisation pour les comptes entreprise. Ces adresses recevront une copie des e‑mails normalement adressés à l\'entreprise. Indiquez une ou plusieurs adresses séparées par des virgules.',

'config_notification_creation_cotisation_entreprise_fieldset' => 'Notifications création de cotisation (entreprises)',
'config_notification_creation_cotisation_entreprise_explication' => 'Configurer les destinataires en copie cachée (BCC) des notifications envoyées lors de la création d\'une cotisation pour les comptes de type « entreprise ». Ces adresses recevront une copie des e‑mails normalement adressés à l\'entreprise. Indiquez une ou plusieurs adresses séparées par des virgules.',

// Message d'erreur pour e‑mails invalides
'erreur_emails_invalides' => 'Une ou plusieurs adresses e‑mail fournies sont invalides. Veuillez vérifier la syntaxe.',

'config_adherent_entreprise_fieldset' => 'Configuration des adhésions de type "entreprise"',
'config_adherent_compte_entreprise_label' => 'Compte entreprise',
'config_adherent_compte_entreprise_explication' => 'Permettre aux comptes "Entreprise" de créer leur propre cotisation via leur profil',

'config_inscription_evenement_entreprise_label' => 'Participation des entreprises aux événements',
'config_inscription_evenement_entreprise_explication' => 'Permettre aux comptes "Entreprise" de s\'inscrire aux événements de l\'association',
'config_liste_diffusion_compte_entreprise_label' => 'Liste de diffusion entreprise',
'config_liste_diffusion_compte_entreprise_explication' => 'Les comptes "Entreprise" y seront inscrits automatiquement à la validation de leur cotisation',


'config_specifique_evenement_fieldset' => 'Configuration spécifique aux événements',
'config_inscription_sur_repetition_label' => 'Inscription sur répétition',
'config_inscription_sur_repetition_explication' => 'Permettre les inscriptions sur les répétitions d\'un événement',

'evenement_modification_inscription_label' => 'Modification des inscriptions aux événements',
'evenement_modification_inscription_explication' => 'Permet aux inscrits de modifier leur inscription à un événement',
'meta_cfg_event_modification_inscription_oui' => 'Oui',
'meta_cfg_event_modification_inscription_non' => 'Non (par défaut)',

'evenement_desinscription_inscription_label' => 'Désinscription des inscriptions aux événements',
'evenement_desinscription_inscription_explication' => 'Permet aux inscrits de se désinscrire d\'un événement. Par défaut, on ne peut pas se désincrire si il y a eu une validation par un responsable ou un encaissement.',
'meta_cfg_event_desinscription_inscription_souple' => 'Souple',
'meta_cfg_event_desinscription_inscription_strict' => 'Strict (par défaut)',

'evenement_message_responsable_label' => 'Message pour le responsable',
'evenement_message_responsable_explication' => 'Proposer un champ de saisie optionnel lors de l\'inscription à destination des responsables',

'evenement_type_quota_label' => 'Gestion des quotas',
'evenement_type_quota_explication' => 'Cette option permet de bloquer les inscriptions lorsque le quota de préinscrit à un événement est égale au nombre de place disponible (cela permet  d\'éviter d\'avoir des inscrits hors quota si l\'option est STRICT)',
'evenement_type_quota_souple' => 'Souple (par défaut)',
'evenement_type_quota_strict' => 'Strict',

'delai_expiration_label' => 'Désinscription automatique pour les événements payants avec validation',
'delai_expiration_explication' => 'Cette option permet de définir un nombre de jour suite auquel les inscriptions payantes non-réglés seront désinscrits. Inscrits et responsables seront notifiés par email',

'config_accompagnants_label' => 'Type d\'accompagnant',
'config_accompagnants_explication' => 'Permet de configurer les options liés aux accompagnants',
'config_accompagnants_tout' => 'Proposer un champs de saisie pour tous les accompagnants (par défaut)',
'config_accompagnants_membre_famille' => 'Proposer les membres de la famille pour les adhérents et un champs de saisie pour les non-membres ',
'config_accompagnants_membre_famille_seulement' => 'Restreindre l\'ajout d\'accompagnant aux adhérents et au membre de leur famille uniquement',

'evenement_form_info_supp_label' => 'Information supplémentaires',
'evenement_form_info_supp_explication' => 'Permets de pouvoir paramètrer des informations supplémentaires (date de naissance, documents d\'identité ...) lors de l\'inscription à un événement.',

'nb_inscription_quota_adherent_label' => 'Nombre d\'inscription max',
'nb_inscription_quota_adherent_explication' => 'Fixe le nombre maximum d\'inscription permise par adhérent',

'nb_jour_quota_adherent_label' => 'Nombre de jour',
'nb_jour_quota_adherent_explication' => 'Fixe la période d\'application du quota',

'evenement_quota_inscription_adherent_label' => 'Quota personnel d\'inscription aux événements',
'evenement_quota_inscription_adherent_explication' => 'Permet de limiter le nombre d\'inscription d\'un adhérent sur une période pour l\'ensemble des événements (option global) ou par type d\'activité.',
'evenement_quota_inscription_adherent_desactive' => 'Désactivé (par défaut)',
'evenement_quota_inscription_adherent_global' => 'Global, le quota s\'applique à tous les événements du site',
'evenement_quota_inscription_adherent_activites' => 'Par activité, le quota est calculé par type d\'événement relié à un même article. Le quota peut être désactivé manuellement si besoin via la configuration de l\'article',


'config_notification_inscription_evenement' => 'Notifications email liées aux inscriptions aux événements ',
'config_envoi_email_notif_defaut_label' => 'Destinataire(s) par défaut des notifications emails',
'config_envoi_email_notif_defaut_explication' => 'Permet de spécifier le (ou les) compte(s) email(s) qui recevront les notifications (inscription, désinscription etc...) dans le cas ou aucun responsable n\'est selectionné. (Séparé par des virgules)',
'config_envoi_email_notif_bcc_label' => 'Destinataire(s) en copie des notifications emails',
'config_envoi_email_notif_bcc_explication' => 'Permet de spécifier le (ou les) compte(s) email(s) qui recevront une copie des notifications destinées aux responsables (inscription, désinscription etc...) en copie cachée. (Séparé par des virgules)',
'config_envoi_recu_paiement_participation_label' => 'Reçu d\'encaissement',
'config_envoi_recu_paiement_participation_explication' => 'Envoyer un reçu email suite à l\'encaissement d\'une participation financière à un événement',
'config_envoi_recu_paiement_participation_oui' => 'Oui',
'config_envoi_recu_paiement_participation_non' => 'Non',



'config_inscription_contact' => 'Contact des responsables aux événements',
'meta_cfg_telephone_responsable_label' => 'Téléphone des responsables',
'meta_cfg_telephone_responsable_explication' => 'Afficher les téléphones des reponsables dans les articles, événements et emails accessibles aux adhérents',

'form_config_plugin_evenement_formulaire_contact' => '',

'meta_cfg_evenement_formulaire_contact_label' => 'Formulaires de contact',
'meta_cfg_evenement_formulaire_contact_explication' => 'Afficher les formulaires de contact des responsables aux utilisateurs non-connectés',

'meta_cfg_event_email_defaut_label' => 'Email de contact',
'meta_cfg_event_email_defaut_explication' => 'Cette adresse de contact sera proposée aux non-adhérents sur les fiches événements dans le cas ou il n\'y a pas de responsables déclarés.',


// Configuration par défaut des événements
'config_inscription_evenement_fieldset' => 'Configuration par défaut des événements',

'config_inscription_label' => 'Inscription en ligne',


'evenement_type_inscrits_label' => 'Type d\'inscrits ',
'evenement_type_inscrits_explication' => 'Permet de définir qui peut s\'inscrire à ce rendez-vous',
'evenement_type_inscrits_public' => 'Public - Tout type d\'inscrit',
'evenement_type_inscrits_prive' => 'Privé - Personnes authentifiées sur le site',
'evenement_type_inscrits_strict' => 'Strict - Uniquement les adhérents à jour de leur cotisation',
'evenement_type_inscrits_only_strict' => 'Vérrouillé - Inscription limité aux adhérents à jour pour tous les rendez-vous',

'afficher_liste_inscrits_label' => 'Liste publique des inscrits',
'afficher_liste_inscrits_explication' => 'Affichage de la liste des inscrits au rendez-vous sur la fiche événement (uniquement visible par les adhérents).',

'evenement_date_ouverture_differe_label' => 'Ouverture différée des inscriptions à ce rendez-vous ',
'evenement_date_ouverture_differe_explication' => 'Permet de définir à partir de combien de semaines à l\'avance les adhérents peuvent s\'inscrire à ce rendez-vous',
'evenement_date_ouverture_differe_desactive' => 'Pas de limite',
'evenement_date_ouverture_differe_date' => 'Date spécifique',
'evenement_date_ouverture_differe_une_semaine' => '1 semaine (7 jours)',
'evenement_date_ouverture_differe_deux_semaines' => '2 semaines (14 jours)',
'evenement_date_ouverture_differe_trois_semaines' => '3 semaines (21 jours)',
'evenement_date_ouverture_differe_un_mois' => '4 semaines (28 jours)',
'evenement_date_ouverture_differe_un_mois_et_demi' => '6 semaines (42 jours)',
'evenement_date_ouverture_differe_deux_mois' => '8 semaines (56 jours)',

'label_evenement_inscription_deadline' => 'Fin de la période d\'inscription à un rendez-vous',
'evenement_inscription_deadline_derniere_minute' => 'Jusqu\'à la dernière minute',
'evenement_inscription_deadline_midnight' => 'Minuit du jour du rendez-vous',
'evenement_inscription_deadline_midi' => 'Midi du jour précédent le rendez-vous',
'evenement_inscription_deadline_24h' => '24h avant le début du rendez-vous',
'evenement_inscription_deadline_48h' => '48h avant le début du rendez-vous',
'evenement_inscription_deadline_72h' => '72h avant le début du rendez-vous',
'evenement_inscription_deadline_96h' => '96h avant le début du rendez-vous',
'evenement_inscription_deadline_7j' => 'Une semaine avant le début du rendez-vous',
'evenement_inscription_deadline_14j' => 'Deux semaine avant le début du rendez-vous',
'evenement_inscription_deadline_30j' => '30 jours avant le début du rendez-vous',

'evenement_inscription_deadline_suspendre' => 'Suspendre dès maintenant les inscriptions du rendez-vous',
'evenement_inscription_deadline_explication' => ' ',
'label_evenement_validation' => 'Inscription par validation :',
'evenement_validation_oui' => 'Oui, activer la validation',
'evenement_validation_non' => 'Non, l\'inscription est instantanée',
'evenement_validation_explication' => ' ',
'label_evenement_accompagnants' => 'Autoriser l\'ajout d\'accompagnant lors de l\'inscription',
'evenement_accompagnants_radio_famille' => 'Oui, uniquement <b>les membres de la famille</b> (saisit dans la fiche d\'adhésion)',
'evenement_accompagnants_oui' => 'Oui',
'evenement_accompagnants_non' => 'Non',

'config_limite_nb_accompagnants' => 'Nombre limite de participant par adhérent',
'config_limite_nb_accompagnants_explication' => 'Le chiffre ci-dessus doit être supérieur à 0',
'label_evenement_invites' => 'Autoriser les invités hors famille par défaut',
'label_evenement_file_attente' => 'Permettre les inscriptions hors quota',
'evenement_file_attente_oui' => 'Oui',
'evenement_file_attente_non' => 'Non',
'label_evenement_validation_auto' => 'Activer la validation automatique de la liste d\'attente',
'evenement_validation_auto_oui' => 'Oui',
'evenement_validation_auto_non' => 'Non',

'config_limite_file_places_attente' => 'Nombre limite d\'inscription en liste d\'attente',
'config_limite_file_places_attente_explication' => 'Saisissez ci-dessous le nombre de places disponibles maximum en liste d\'attente (0 = file d\'attente illimité)',

'config_label_evenement_condition_inscription_label' => 'Condition obligatoire lors de l\'inscription à un événement',
'config_label_evenement_condition_inscription_explication' => 'Affichage d\'une case à cocher obligatoire lors de l\'inscription à un événement',
'config_choix_toujours' => 'Toujours afficher',
'config_choix_jamais' => 'Jamais - désactiver complétement cette fonctionalité',
'config_label_message_condition_inscription_defaut_label' => 'Texte par défaut de la case à cocher',
'config_label_message_condition_inscription_defaut_explication' => 'Ce texte sera affiché par défaut mais peut-être personnalisé sur chaque événement par le responsable.',

'config_paiement_formulaire' => 'Configuration des modes de paiement',
'config_mode_paiement_explication' => 'Les modes de paiement disponible ci-dessous sont configuré via le plugin BANK par votre webmaster.',
'config_mode_paiement_participation' => 'Participations financières aux événements',
'config_mode_paiement_participation_explication' => '',
'config_mode_paiement_formulaire' => 'Formulaires ',
'config_mode_paiement_formulaire_explication' => '',
'config_mode_paiement_adhesion' => 'Règlement de l\'adhésion',
'config_mode_paiement_adhesion_explication' => 'Les modes de paiement disponible ci-dessous sont configuré via le plugin BANK par votre webmaster.',
'config_selection_segment_fieldset' => 'Configuration des segments de liste de diffusion',
'config_selection_segment_label' => 'Sélectionnez vos critères',
'config_selection_segment_explication' => 'Les segments permettent de créer des sous-listes de diffusion selon les critères sélectionnés ci-dessous. Utilisez la touche CTRL de votre clavier pour faire une sélection multiple.',

'config_autorisations_fieldset' => 'Autorisations',
'meta_cfg_autorisation_encaisser_transaction_label' => 'Encaissement',
'meta_cfg_autorisation_encaisser_transaction_explication' => 'Permettre de restreindre la possibilité d\'encaisser une transaction',
'autorisation_encaisser_transaction_tresoriere' => 'La\Le trésorier(e) : ',
'autorisation_encaisser_transaction_admin_only' => 'Tous les admins du site',
'autorisation_encaisser_transaction_admin_et_responsable' => 'Les admins et les responsables d\'activités (uniquement sur leurs activités)',

'config_gis_fieldset' => 'Configuration de la carte intéractive',
'notification_gis_config_email_label' => 'Email(s) destinataire(s) des notifications de géolocalisation',
'notification_gis_config_email_explication' => 'Vous pouvez spécifier plusieurs par emails séparés par des virgules',

'notification_gis_config_action_label' => 'Sélectionner les actions sur lesquelles une notification doit être envoyée :',
'notification_gis_config_action_explication' => ' ',
'filtre_modification_adherent' => 'Modification/Mise à jour d\'une adresse d\'un adhérent',
'filtre_echec_adherent' => 'Échec de géolocalisation de l\'adresse d\'un adhérent',

'config_evenement_fiafe' => 'Configuration des événements FIAFE',
'evenement_reseau_fiafe_label' => 'Evénement du Réseau FIAFE',
'evenement_reseau_fiafe_explication' => 'Si activé, vous aurez une option sur chaque événement (en distanciel) permettant de le partager automatiquement avec d\'autres accueils.',
'evenement_reseau_fiafe_active' => 'Oui, activer l\'option de partage d\'événement avec les autres accueils',
'evenement_reseau_fiafe_desactive' => 'Non, nous ne souhaitons pas partager d\'événement avec d\'autres accueils',

'evenement_profil_reseau_fiafe_label' => 'Afficher l\'agenda du Réseau FIAFE directement dans le profil des adhérents à jour',
'evenement_profil_reseau_fiafe_explication' => 'Si activé, tous les adhérents de votre association pourrons consulter la liste des événements FIAFE via leur page profil.',
'evenement_reseau_profil_fiafe_active' => 'Oui, afficher l\'onglet.',
'evenement_reseau_profil_fiafe_desactive' => 'Non, ne pas l\'afficher',

'config_comptabilite_fieldset' => 'Paramétrage de la comptabilité',

'config_comptes_label'=> 'Gestion comptable',
//'config_comptes_explication' => 'Activer la gestion comptable',
'config_comptes_label_case' => 'Activer la gestion comptable',
'config_info_membres' => 'Options de gestion des membres',
'config_classe_banques_label' => 'Classe des comptes financiers',
'config_classe_banques_explication' => 'Vous pouvez configurer votre plan comptable <a href="@url@">via cette page</a>.',
'config_destinations_label'=> 'Gestion des destinations comptables',
'config_destinations_label_case'=> 'Activer la gestion des destinations comptables',
'config_destinations_explication'=> 'Vous pouvez configurer vos destinations comptables <a href="@url@">via cette page</a>.',

'config_cotisations_fieldset'=> 'Gestion des cotisations',
'config_num_pc_creance_label'=> 'Réf. comptable des créances',
'config_num_pc_creance_explication'=> 'Référence comptable des créances',
'config_activites_fieldset'=> 'Compta des activités',
'config_num_pc_paiement_label'=> 'Réf. comptable des paiements',
'config_num_pc_paiement_explication'=> 'Référence comptable des paiements',
'config_num_pc_frais_label'=> 'Réf. comptable des frais',
'config_num_pc_frais_explication'=> 'Référence comptable des frais',


'config_dons_fieldset'=> 'Gestion des dons et colis',
'config_dons_label' => 'Activer la gestion des dons',
'config_ventes_fieldset'=> 'Gestion des ventes associatives',
'config_ventes_label' => 'Activer la gestion des ventes',
'config_frais_envoi_label'=> 'frais d\'envoi',
'config_prets_fieldset'=> 'Gestion des prêts et ressources',
'config_prets_label' => 'Activer la gestion des prêts',

'config_num_pc_label'=>'Réf. comptable',
'config_num_dc_label'=>'Dest. comptable',

'config_exercice_comptable_fieldset' => 'Exercice comptable',
'config_exercice_comptable_debut_label' => 'Début (JJ/MM)',
'config_exercice_comptable_debut_explication' => 'Jour et mois de début de l\'exercice comptable (format JJ/MM)',
// Erreurs exercice comptable
'erreur_exercice_comptable_format' => 'Format invalide (JJ/MM attendu)',
'erreur_exercice_comptable_jour_mois' => 'Jour ou mois invalide (JJ 01-31 / MM 01-12)',
'erreur_exercice_comptable_identiques' => 'Les dates de début et de fin ne doivent pas être identiques',

'config_maintenance_bdd_fieldset' => 'Maintenance automatique de la base de données (CRON)',
'config_maintenance_bdd_explication' => 'Paramètres pour la tâche cron de maintenance (genie_association_maintenance_bdd) : activer/désactiver et configurer les seuils et actions de nettoyage.',
'config_maintenance_bdd_enable_label' => 'Activer la maintenance automatique (CRON)',
'config_maintenance_bdd_enable_explication' => 'Si activé, la tâche cron effectuera les opérations de nettoyage selon les actions cochées et les paramètres ci‑dessous.',
'config_maintenance_bdd_dry_run_label' => 'Mode dry-run par défaut',
'config_maintenance_bdd_dry_run_explication' => 'Si oui, la maintenance se contente de simuler les suppressions (comptage) sans effectuer d\'écritures. Les anonymisations RGPD ne sont jamais simulées : elles sont désactivées lors du dry-run manuel.',
'config_maintenance_bdd_jours_inactivite_label' => 'Seuil (jours) pour auteurs inactifs',
'config_maintenance_bdd_jours_inactivite_explication' => 'Nombre de jours d\'inactivité après lesquels un auteur est considéré inactif (ex : 365).',
'config_maintenance_bdd_jours_inscriptions_attente_label' => 'Seuil (jours) pour inscriptions non validées',
'config_maintenance_bdd_jours_inscriptions_attente_explication' => 'Nombre de jours après lesquels les inscriptions en attente sont candidates à suppression (ex : 90).',
'config_maintenance_bdd_mois_non_encaisse_label' => 'Rétention (mois) pour cotisations non encaissées',
'config_maintenance_bdd_mois_non_encaisse_explication' => 'Nombre de mois après lesquels les cotisations non encaissées peuvent être supprimées (ex : 6).',
'config_maintenance_bdd_lot_label' => 'Taille de lot pour traitement',
'config_maintenance_bdd_lot_explication' => 'Nombre maximum d\'entrées traitées par lot pour chaque opération (ex : 1000).',
'config_maintenance_bdd_actions_fieldset' => 'Actions de nettoyage (cocher pour activer)',
'config_maintenance_bdd_supprimer_auteurs_sans_paiements_label' => 'Supprimer les auteurs sans encaissements',
'config_maintenance_bdd_supprimer_auteurs_sans_paiements_explication' => 'Supprime définitivement les comptes auteurs identifiés comme inactifs n\'ayant jamais eu d\'encaissement : leurs cotisations non-validées, transactions non-réglées et abonnements (mailsubscribers/mailsubscriptions). En dry‑run seule une estimation est réalisée.',
'config_maintenance_bdd_anonymiser_auteurs_avec_paiements_label' => 'Anonymiser les auteurs ayant des encaissements',
'config_maintenance_bdd_anonymiser_auteurs_avec_paiements_explication' => 'Anonymise réellement les auteurs inactifs ayant eu des encaissements : nom, email, login et données métier liées sont remplacés par des valeurs génériques (conforme RGPD). Les historiques comptables restent conservés sous forme anonymisée.',
'config_maintenance_bdd_supprimer_inscriptions_non_validees_label' => 'Supprimer les inscriptions en attente anciennes',
'config_maintenance_bdd_supprimer_inscriptions_non_validees_explication' => 'Supprime les inscriptions aux activités anciennes qui n\'ont jamais été validées (statut attente/valider=0) ainsi que les transactions liées non réglées. En dry‑run seule une estimation est réalisée.',
'config_maintenance_bdd_anonymiser_inscriptions_inactifs_label' => 'Anonymiser inscriptions des auteurs inactifs',
'config_maintenance_bdd_anonymiser_inscriptions_inactifs_explication' => 'Anonymise réellement les données personnelles présentes dans les inscriptions (nom, prénom, email, téléphone, IP) pour les auteurs inactifs afin de respecter la confidentialité/RGPD.',
'config_maintenance_bdd_supprimer_cotisations_orphelines_label' => 'Supprimer cotisations orphelines',
'config_maintenance_bdd_supprimer_cotisations_orphelines_explication' => 'Supprime les cotisations sans auteur (orphelines) dès lors qu\'elles ne sont pas liées à une transaction réglée (statut="ok"). Les transactions non réglées associées peuvent aussi être supprimées. Les cotisations liées à une transaction réglée sont protégées.',
'config_maintenance_bdd_supprimer_cotisations_non_encaissees_label' => 'Supprimer cotisations non-encaissées anciennes',
'config_maintenance_bdd_supprimer_cotisations_non_encaissees_explication' => 'Supprime les cotisations non encaissées plus anciennes que le seuil (mois). Ne supprime pas les cotisations liées à une transaction réglée. Les transactions non réglées associées sont également supprimées.',
'config_maintenance_bdd_supprimer_transactions_orphelines_label' => 'Supprimer transactions orphelines',
'config_maintenance_bdd_supprimer_transactions_orphelines_explication' => 'Supprime les transactions orphelines : transactions non liées à des cotisations ni à des participations et dont le statut n\'est pas \"ok\" (avec une limite temporelle pour éviter suppression récente). En dry‑run seule une estimation est réalisée.',
'config_maintenance_bdd_supprimer_participations_orphelines_label' => 'Supprimer participations évènements orphelines',
'config_maintenance_bdd_supprimer_participations_orphelines_explication' => 'Supprime les participations à des événements dont l\'événement n\'existe plus (orphelines) et qui ne sont pas réglées. Supprime aussi les transactions non réglées liées.',
'config_maintenance_bdd_supprimer_participations_obsoletes_label' => 'Supprimer participations évènements obsolètes',
'config_maintenance_bdd_supprimer_participations_obsoletes_explication' => 'Supprime les participations obsolètes : inscriptions à des événements passés depuis plus de N jours (seuil configurable) et dont l\'inscription n\'est pas réglée. Les inscriptions liées à une transaction réglée sont conservées.',
'config_maintenance_bdd_supprimer_urls_mailsubscriber_label' => 'Supprimer URLs mailsubscriber orphelines',
'config_maintenance_bdd_supprimer_urls_mailsubscriber_explication' => 'Supprime les URLs de redirection de type \"mailsubscriber\" dont l\'objet référencé n\'existe plus (ors d\'une suppression d\'objet, par ex.).',
'config_maintenance_bdd_supprimer_urls_obsoletes_label' => 'Supprimer URLs obsolètes',
'config_maintenance_bdd_supprimer_urls_obsoletes_explication' => 'Supprime les URLs de redirection obsolètes : toutes les URLs dont l\'objet référencé n\'existe plus dans la table correspondante. Opération limitée par lot.',
'config_maintenance_bdd_supprimer_mailsubscribers_orphelines_label' => 'Supprimer mailsubscribers orphelines',
'config_maintenance_bdd_supprimer_mailsubscribers_orphelines_explication' => 'Supprime les enregistrements mailsubscribers sans auteur correspondant (email non lié à un auteur). Supprime aussi les destinataires de mailshots associés si la table existe. En dry‑run seule une estimation est réalisée.',

'config_maintenance_bdd_exec_dry_run_label' => 'Exécuter un dry‑run maintenant',
'config_maintenance_bdd_exec_dry_run_explication' => 'Lance immédiatement la tâche de maintenance en mode simulation (dry‑run). Un rapport JSON sera écrit dans le dossier tmp/rapports.',
'config_maintenance_bdd_exec_dry_run_done' => 'Dry‑run exécuté, rapport enregistré : @fichier@',
'config_maintenance_bdd_exec_dry_run_no_rights' => 'Vous n\'avez pas les droits suffisants pour exécuter un dry‑run (webmestre requis).',
'config_maintenance_bdd_exec_dry_run_report_title' => 'Rapport de maintenance BDD (dry‑run)',

// --- Onglet Debug ---
'config_debug_fieldset'              => 'Logs de débogage',
'config_debug_explication'           => 'Active des logs détaillés par catégorie métier. Réservé aux webmestres. À désactiver en production.',
'config_debug_cat_autorisations_label'      => 'Autorisations',
'config_debug_cat_autorisations_explication' => 'Journalise les décisions d\'autorisation (accès événements, onglets, comptes…).',
'config_debug_cat_cotisations_label'        => 'Cotisations',
'config_debug_cat_cotisations_explication'  => 'Journalise les opérations liées aux cotisations (création, validation, activation, notifications).',
'config_debug_cat_notifications_label'      => 'Notifications',
'config_debug_cat_notifications_explication' => 'Journalise l\'envoi des notifications email (adhérent, admin, reçus).',
'config_debug_cat_inscriptions_label'       => 'Inscriptions aux événements',
'config_debug_cat_inscriptions_explication' => 'Journalise les inscriptions, désinscriptions et participants aux événements.',
'config_debug_cat_comptabilite_label'       => 'Comptabilité',
'config_debug_cat_comptabilite_explication' => 'Journalise les opérations comptables (comptes, remboursements, synchronisation).',
'config_debug_cat_adherents_label'          => 'Adhérents',
'config_debug_cat_adherents_explication'    => 'Journalise la recherche et la gestion des adhérents (filtres, listes, critères).',
'config_debug_cat_cron_label'               => 'Tâches planifiées (CRON)',
'config_debug_cat_cron_explication'         => 'Journalise les tâches CRON (expiration, maintenance BDD, tâches générales).',
'config_debug_cat_spam_label'               => 'Détection spam',
'config_debug_cat_spam_explication'         => 'Journalise la détection de spam dans les formulaires d\'inscription.',
'config_debug_cat_email_label'              => 'Envoi d\'emails',
'config_debug_cat_email_explication'        => 'Journalise l\'envoi des emails collectifs et individuels aux activités.',
'config_debug_cat_gis_label'                => 'Géolocalisation (GIS)',
'config_debug_cat_gis_explication'          => 'Journalise les opérations de géolocalisation et notifications GIS.',
'config_debug_cat_migration_label'          => 'Migration de configuration',
'config_debug_cat_migration_explication'    => 'Journalise les migrations automatiques de configuration.',
'config_debug_cat_sync_label'               => 'Synchronisation',
'config_debug_cat_sync_explication'         => 'Journalise la synchronisation des répétitions et tarifs.',

);

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
