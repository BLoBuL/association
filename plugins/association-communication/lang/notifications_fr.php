<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
$traductions = [
	'email_formule_politesse' => 'Cordialement, ',
	'email_signature' => 'L\'équipe de @nom_site_spip@.',
	'notification_lien_profil' => 'Suivez le statut de votre inscription depuis votre page profil : ',
	'activation_cotisation_donation' => 'L\'Association vous remercie pour votre don.',
	'bouton_page_paiement' => 'PAGE DE PAIEMENT',
	'bouton_page_profil' => 'MON PROFIL',
	'lien_page_profil' => 'Accéder à ma page profil :',
	'lien_page_paiement' => 'Accéder à la page de paiement via ce lien :',
	'votre_nom' => 'Votre nom :',
	'montant_a_regler' => 'Montant à régler :',
	// Notifications échéances (adhérent / entreprise)
	'notification_echeance_preventive_intro_adherent' => 'Ce message concerne votre adhésion <strong>@nom_adherent@</strong>.',
	'notification_echeance_preventive_intro_entreprise' => 'Ce message concerne le compte entreprise <strong>@nom_entreprise@</strong> (contact référent : @nom_referent@).',
	'notification_echeance_bloc_label_validite' => 'Date de validité :',
	'notification_echeance_bloc_label_rappel' => 'Rappel :',
	'notification_echeance_bloc_rappel_day' => 'Envoi programmé le jour de l’échéance.',
	'notification_echeance_bloc_rappel_preventif' => 'Envoi programmé @nb@ jour(s) avant l’échéance.',
	'notification_echeance_echu_intro_adherent' => 'Votre adhésion <strong>@nom_adherent@</strong> est arrivée à échéance.',
	'notification_echeance_echu_intro_entreprise' => 'Le compte entreprise <strong>@nom_entreprise@</strong> est considéré comme “échu”. Contact référent : @nom_referent@.',
	'notification_echeance_bloc_label_statut' => 'Statut actuel :',

	/*
	 * '_sujet' : Sujet de l'email (dans le client de messagerie 7-8 mots max)
	 * _ title : Title de l'email (entête de l'email doit être très succinct 5-6 mots)
	 * _ intro : Introduction de l'email (entête de l'email 10 mots max)
	 * '_titre' : Titre de l'email (environ 10 mots)
	 * '_chapo' : Chapo de l'email (1er paragraphe)
	 * '_explication' : Explication complémentaire
	 * '_message_lien_profil' : Lien vers la page profil de l'adhérent
	 */

	# PAIEMENT EN ATTENTE PAR L'ADHERENT
	/* Adhérent : /notifications/cotisation-attente_paiement.html */
	'attente_paiement_sujet' => 'Cotisation en attente de paiement',
	'attente_paiement_title' => 'Vous avez une cotisation en attente de paiement',
	'attente_paiement_intro' => 'Information de paiement',
	'attente_paiement_inscription_titre' => 'Nous avons bien reçu votre demande d\'adhésion à l\'association.',
	'attente_paiement_reinscription_titre' => 'Nous avons bien reçu votre demande de renouvellement',
	'attente_paiement_chapo' => 'Nous vous invitons à procéder au règlement de votre cotisation en suivant les instructions disponibles ci-dessous.',
	'attente_paiement_rappel' => 'Récapitulatif :',
	'attente_paiement_message_lien_profil' => 'Détail et suivi depuis votre page profil :',

	// Version entreprise
	'attente_paiement_entreprise_sujet' => 'Inscription entreprise en attente de paiement',
	'attente_paiement_entreprise_title' => 'Vous avez une cotisation en attente de paiement',
	'attente_paiement_entreprise_intro' => 'Information de paiement',
	'attente_paiement_inscription_entreprise_titre' => 'Nous avons bien reçu votre demande d\'inscription entreprise.',
	'attente_paiement_reinscription_entreprise_titre' => 'Nous avons bien reçu votre demande de renouvellement ',
	'attente_paiement_entreprise_chapo' => 'Nous vous invitons à procéder au règlement de votre cotisation en suivant les instructions disponibles ci-dessous.',

	// ALIASES pour compatibilité avec les templates
	'attente_paiement_adherent_sujet' => 'Paiement requis pour finaliser votre adhésion',
	'attente_paiement_adherent_title' => 'Finalisez votre adhésion',
	'attente_paiement_adherent_intro' => 'Demande enregistrée. Réglez le paiement.',
	'attente_paiement_adherent_titre' => 'Réglez votre adhésion et devenez membre de notre association',
	'attente_paiement_adherent_chapo' => 'Votre cotisation est en attente de paiement. Vous pouvez procéder au paiement de votre cotisation grâce au lien en bas de ce message ou via votre page profil.',

	# VALIDATION DU COMPTE ( ou réinscription )
	/* Adhérent : /notications/cotisation_activation.html */

	'activation_cotisation_inscription_sujet' => 'Inscription confirmée',
	'activation_cotisation_reinscription_sujet' => 'Réinscription confirmée',
	'activation_cotisation_title' => 'Validation de votre adhésion',
	'activation_cotisation_intro' => 'Votre compte est activé.',
	'activation_cotisation_inscription_titre' => 'Bienvenue @nom_adherent@, vous êtes membre de l\'association @nom_site@ !',
	'activation_cotisation_reinscription_titre' => 'Merci @nom_adherent@, votre adhésion est renouvelée avec succès !',
	'activation_cotisation_chapo' => 'Bravo, votre cotisation est validée. Profitez des activités et services réservés aux membres.',
	'activation_cotisation_message_lien_profil' => "Rendez‑vous sur <a href='@url_profil@'>votre page profil</a> pour compléter votre fiche et vos préférences.",
	'activation_cotisation_explication' => 'En tant que membre de l\'association, vous pouvez accéder :',
	'activation_cotisation_explication_annuaire' => '<b>à l\'annuaire des membres de l\'association</b>',
	'activation_cotisation_explication_b' => 'aux inscriptions en ligne et activités',
	'activation_cotisation_explication_c' => 'aux documents joints et galeries protégées',

	// VARIANTE ENTREPRISE
	'activation_cotisation_inscription_entreprise_sujet' => 'Votre inscription entreprise est désormais complète',
	'activation_cotisation_reinscription_entreprise_sujet' => 'Votre inscription entreprise est désormais complète',
	'activation_cotisation_entreprise_title' => 'Validation de votre inscription entreprise',
	'activation_cotisation_entreprise_intro' => 'Votre annonce entreprise est désormais active',
	'activation_cotisation_inscription_entreprise_titre' => 'Bienvenue @nom_entreprise@, votre inscription entreprise est active sur @nom_site@',
	'activation_cotisation_reinscription_entreprise_titre' => 'Merci @nom_entreprise@, votre adhésion entreprise est renouvelée !',
	'activation_cotisation_entreprise_chapo' => 'Votre cotisation est validée. Le compte entreprise est actif et dispose désormais des privilèges associés.',

	# POST_PAIEMENT
	/* Adhérent : /notications/cotisation_validation_post-paiement.html */
	'validation_post_paiement_sujet' => 'Paiement reçu',
	'validation_post_paiement_title' => 'Réception de votre paiement',
	'validation_post_paiement_intro' => 'En attente de validation',
	'validation_post_paiement_inscription_titre' => 'Nous confirmons la réception de votre paiement.',
	'validation_post_paiement_reinscription_titre' => 'Nous confirmons la réception de votre paiement.',
	'validation_post_paiement_chapo' => 'Un responsable vérifiera votre inscription et procédera à la validation manuelle.',
	'validation_post_paiement_explication' => 'Vous serez informé(e) par email dès que la validation aura été effectuée. Vous pouvez suivre l\'état de votre cotisation depuis votre page profil.',

	// VARIANTE ENTREPRISE
	'validation_post_paiement_entreprise_sujet' => 'Paiement reçu (inscription entreprise)',
	'validation_post_paiement_entreprise_title' => 'Nous confirmons la réception du paiement de votre inscription entreprise.',
	'validation_post_paiement_entreprise_intro' => 'Nous confirmons la réception du paiement de votre inscription entreprise. La demande est enregistrée et en attente de validation par un responsable.',
	'validation_post_paiement_inscription_entreprise_titre' => 'Nous confirmons la réception du paiement de votre inscription entreprise.',
	'validation_post_paiement_reinscription_entreprise_titre' => 'Nous confirmons la réception du paiement de votre inscription entreprise.',
	'validation_post_paiement_entreprise_chapo' => 'Un responsable examinera votre inscription entreprise et procédera à la validation.',
	'validation_post_paiement_entreprise_explication' => 'Vous recevrez un email dès que la validation sera faite. Suivez l\'état depuis votre page profil entreprise.',

	# PRE_PAIEMENT
	/* Adhérent : /notications/cotisation_validation_pre-paiement.html */
	'validation_pre_paiement_sujet' => 'Inscription en attente de validation',
	'validation_pre_paiement_title' => 'En attente de validation',
	'validation_pre_paiement_intro' => 'Votre demande a été transmise à un responsable.',
	'validation_pre_paiement_inscription_titre' => 'Votre cotisation est en attente de validation',
	'validation_pre_paiement_reinscription_titre' => 'Votre cotisation est en attente de validation',
	'validation_pre_paiement_chapo' => 'Un responsable va vérifier votre inscription. Vous serez notifié dès que vous pourrez procéder au paiement.',
	'validation_pre_paiement_explication' => 'Vous recevrez un email avec les instructions de paiement après validation. Suivez l\'état depuis votre page profil.',

	// VARIANTE ENTREPRISE
	'validation_pre_paiement_entreprise_sujet' => 'Inscription entreprise en attente de validation',
	'validation_pre_paiement_entreprise_title' => 'Validation en cours',
	'validation_pre_paiement_entreprise_intro' => 'Votre inscription entreprise a été transmis à un responsable.',
	'validation_pre_paiement_entreprise_inscription_titre' => 'Inscription entreprise en attente de validation',
	'validation_pre_paiement_entreprise_reinscription_titre' => 'Inscription entreprise en attente de validation',
	'validation_pre_paiement_entreprise_chapo' => 'Un responsable va vérifier votre inscription entreprise. Vous serez notifié dès que vous pourrez procéder au paiement.',
	'validation_pre_paiement_entreprise_explication' => 'Vous recevrez un email avec les instructions de paiement après validation. Suivez l\'état depuis votre page profil entreprise.',

	# VALIDATION DE LA CATEGORIE DE PAIEMENT
	/* Administrateur : blobul_plugin_fiafe/notifications/cotisation_demande_admin.html */
	'cotisation_demande_admin_sujet' => '@nom@ - Validation catégorie de cotisation',
	'cotisation_demande_admin_inscription_titre' => 'Inscription - Valider la catégorie de cotisation de @nom_adherent@',
	'cotisation_demande_admin_reinscription_titre' => 'Réinscription - Valider la catégorie de cotisation de @nom_adherent@ ',
	'cotisation_demande_admin_chapo' => 'La catégorie de cotisation choisie par cet adhérent nécessite la validation d\'un(e) responsable',
	'cotisation_demande_admin_explication' => 'Certaines catégories de cotisation, telles que les tarifs réduits ou gratuits nécessitent votre validation. Cette validation sera notifié à l\'adhérent, et lui ouvrir l\'accès aux différents modes de paiements proposés par votre association.',
	'cotisation_demande_admin_lien_cotisation' => 'Certaines catégories de cotisation, telles que les tarifs réduits ou gratuits nécessitent votre validation. Cette validation sera notifié à l\'adhérent, et lui ouvrir l\'accès aux différents modes de paiements proposés par votre association.',
	'cotisation_demande_admin_lien_cotisation_explication' => 'Afin de procéder à la validation de la catégorie de la cotisation, veuillez vous rendre sur l\'historique des cotisations de l\'adhérent, séléctionner la cotisation en "attente de validation", vérifier les informations fournies puis changer pour le statut de celle-ci pour "Cotisation en attente de paiement"',
	'cotisation_demande_admin_lien_cotisation_bouton' => 'Historique des cotisations de @nom_adherent@',

	# NOTIFICATION DE CREATION DE COTISATION et d'ATTENTE DE PAIEMENT

	/* Administrateur : blobul_plugin_fiafe/notifications/cotisation_attente_admin.html */
	'cotisation_attente_admin_sujet' => 'Création d\'une nouvelle cotisation : @nom@ - @type@',
	'cotisation_attente_admin_inscription_titre' => '@nom_adherent@ vient de créer une nouvelle transaction pour s\'inscrire',
	'cotisation_attente_admin_reinscription_titre' => '@nom_adherent@ vient de créer une nouvelle transaction pour se réinscrire',
	'cotisation_attente_admin_chapo' => 'Vous pouvez suivre le statut de la transaction générée par cet adhérent via le module de gestion des transactions.',
	'cotisation_attente_admin_lien_cotisation_explication' => 'Dans le cas d\'un paiement instantané (Paypal ou autre) aucune action n\'est requise de votre part. Dans le cas d\'un paiement avec encaissement manuel (espèces, virement ou chèque), vous devez procéder à la validation de la transaction suite à la réception du réglement, grâce au lien suivant :',
	'cotisation_attente_admin_lien_cotisation_bouton' => 'Voir la cotisation N°@id_compte@',
	'cotisation_attente_admin_lien_transaction_bouton' => 'Valider la transaction N°@id_transaction@',
	'cotisation_attente_admin_lien_cotisation_remarque' => 'Remarque : si le lien de la transaction n\'affiche aucune option, cela indique que la transaction a déjà été réglé.',

	// Fallback pour les en-têtes d'email admin (title / intro)
	'cotisation_attente_admin_title' => 'Nouvelle cotisation créée',
	'cotisation_attente_admin_intro' => 'Une nouvelle cotisation a été enregistrée et nécessite votre attention.',

	// Labels pour le bloc résumé dans les emails admin
	'cotisation_admin_label_qui' => 'Qui :',
	'cotisation_admin_label_type' => 'Type :',
	'cotisation_admin_label_montant' => 'Montant :',

	// Clés pour notification d'encaissement admin
	'cotisation_encaissement_admin_sujet' => 'Paiement reçu : @id_compte@ @nom@ - @type@ - @montant@',
	'cotisation_encaissement_admin_title' => 'Encaissement reçu',
	'cotisation_encaissement_admin_intro' => 'Un paiement a été enregistré pour une cotisation.',
	'cotisation_encaissement_admin_chapo' => 'Un paiement a été reçu pour la cotisation indiquée ci‑dessous. Vous pouvez consulter la transaction et le compte de l\'adhérent via les liens.',

	// Fallback title/intro pour la notification de demande admin
	'cotisation_demande_admin_title' => 'Validation d\'une cotisation',
	'cotisation_demande_admin_intro' => 'Une cotisation requiert la validation d\'un responsable.',
	'justificatifs_admin_titre' => 'Justificatifs requis pour cette cotisation',
	'justificatifs_admin_etat' => '@recus@ document(s) reçu(s), @controles@ contrôlé(s).',
	'justificatifs_admin_controles' => 'Le dossier documentaire est contrôlé.',
	'justificatifs_admin_action' => 'Le dossier doit encore être contrôlé dans le back-office.',
	'justificatifs_admin_bouton' => 'Consulter les justificatifs',
	'justificatifs_a_revoir_sujet' => 'Vos justificatifs d\'adhésion sont à fournir de nouveau',
	'justificatifs_a_revoir_title' => 'Justificatifs à revoir',
	'justificatifs_a_revoir_intro' => 'Une nouvelle transmission de vos justificatifs est nécessaire.',
	'justificatifs_a_revoir_titre' => 'Vos justificatifs doivent être transmis de nouveau',
	'justificatifs_a_revoir_explication' => 'Les justificatifs joints à votre adhésion n\'ont pas pu être validés. Merci de contacter l\'association afin de convenir d\'une nouvelle transmission de fichiers lisibles au format PDF, JPEG ou PNG.',

	// #EMAIL DE RELANCE AUTOMATIQUE XX JOURS AVANT ECHEANCE
	'email_notification_echeances_sujet' => 'Réinscrivez-vous dès maintenant !',
	'email_notification_echeances_title' => 'Réinscrivez-vous dès maintenant !',
	'email_notification_echeances_intro' => 'Votre inscription à notre association arrive à échéance dans @nb_jour_differences@ jours.',
	'email_notification_echeances_titre' => 'Votre inscription à "@nom_site_spip@" est valable jusqu\'au @date_validite@. ',
	'email_notification_echeances_chapo' => '',
	'email_notification_echeances_explication' => 'Vous pouvez d\'ores et déjà prolonger votre inscription d\'un an en vous rendant sur "votre page profil" en cliquant sur le bouton ci-dessous :',
	'email_notification_echeances_explication_2' => 'Vous serez notifié par email lorsque le règlement de votre adhésion aura bien été confirmé par un responsable.',
	'email_notification_echeances_explication_3' => 'Si vous ne souhaitez plus être membre de notre association suite à votre déménagement ou toutes autres raisons, merci de nous le notifier afin que vous ne soyez plus relancé(e).',
	'notification_echeance_warning_admin' => 'En tant qu\'administrateur ou responsable d\'activité, vous pourriez perdre vos droits d\'administration sur le site si vous ne renouvelez pas votre adhésion avant la date indiquée.',
	'email_notification_echeances_rappel' => '<strong>Rappel :</strong> Votre adhésion expirera dans <strong>@delai@</strong> jour(s), soit le <strong>@date_validite@</strong>.',

	# EMAIL DE RELANCE AUTOMATIQUE LORSQUE ADHERENT ARRIVE A ECHEANCE
	'email_notification_echeance_echu_sujet' => 'Votre adhésion est arrivée à échéance',
	'email_notification_echeance_echu_title' => 'Votre adhésion est arrivée à échéance',
	'email_notification_echeance_echu_intro' => 'Réinscrivez-vous pour continuer à profiter des avantages adhérents !',
	'email_notification_echeance_echu_titre' => 'Il n\'est pas trop tard pour se réinscrire !',
	'email_notification_echeance_echu_chapo' => '',
	'email_notification_echeance_echu_explication' => 'Vous pouvez toujours prolonger votre inscription d\'un an en vous rendant sur "votre page profil" en cliquant sur le bouton ci-dessous :',
	'email_notification_echeance_echu_explication_2' => 'Vous serez notifié par email lorsque le règlement de votre adhésion aura bien été confirmé par un responsable.',
	'email_notification_echeance_echu_explication_3' => 'Si vous ne souhaitez plus être membre de notre association suite à votre déménagement ou toutes autres raisons, merci de nous le notifier afin que vous ne soyez plus relancé(e).',
	'email_notification_categorie_cotisation' => 'Catégorie(s) de cotisation à votre disposition :',
	'email_notification_mode_paiement' => 'Mode(s) de paiement disponible(s) :',
	'label_iban' => 'IBAN :',
	'label_bic' => 'BIC :',
	'email_signature_activites_inscription' => 'Ce message vous est adressé par @prenom@ @nom@ (<a href="mailto:@email@">@email@</a>) car vous vous êtes inscrit à ce rendez-vous.',
	// EMAIL AUTOMATIQUE LIÉ A l'UTILISATION GIS (géolocalisation)

	'email_notification_gis_lien' => 'Lien vers la fiche de l\'adhérent',

	// MODIFICATION D'UN POINT GPS
	'email_notification_gis_modification_sujet' => 'Mise à jour de l\'adresse de @nom_adherent@',
	'email_notification_gis_modification_title' => 'Notification de mise à jour de l\'adresse',
	'email_notification_gis_modification_intro' => 'Adresse mise à jour',
	'email_notification_gis_modification_titre' => 'Notification de mise à jour',
	'email_notification_gis_modification_explication' => 'L\'adresse postale de @nom_adherent@ a été mise à jour : <br><b>@nouvelle_adresse@</b>',

	// ECHEC DE CREATION D'UN POINT GPS
	'email_notification_gis_echec_sujet' => 'Echec de localisation GPS de l\'adresse de @nom_adherent@',
	'email_notification_gis_echec_title' => 'Notification d\'échec de localisation GPS',
	'email_notification_gis_echec_intro' => 'Adresse incorrect ou incomplète',
	'email_notification_gis_echec_titre' => 'Notification d\'échec de localisation GPS de l\'adresse d\'un adhérent',
	'email_notification_gis_echec_explication' => 'Le système de géolocalisation n\'a pas réussi à reconnaitre l\'adresse fournie par l\'adhérent. Vérifiez que celle-ci soit complète et cohérente.<br>
Addresse fournie : <br><b>@string_recherche@</b>',
	'email_notification_gis_echec_explication_1' => 'Pour corriger cette adresse, veuillez mettre à jour celle-ci directement dans la fiche de l\'adhérent.',
	'email_notification_gis_lien_test_geolocalisation' => 'Vous pouvez utiliser le lien suivant pour faire un test de géolocalisation et ainsi trouver la meilleur syntaxe pour obtenir un résultat correct.',
	'email_notification_gis_bouton_test_geolocalisation' => 'Test de géolocalisation',

	// Notifications des événements
	'attente_activite_mail_sujet_backend' => "Vous avez été inscrit(e) à la liste d'attente de '@evenement@' du @datev@.",
	'attente_activite_mail_sujet_frontend' => "Vous vous êtes inscrit(e) en liste d'attente du rendez-vous '@evenement@' qui aura lieu le @datev@",
	'attente_activite_mail_sujet_responsable_frontend' => "@adherents@ s'est inscrit(e) en liste d'attente de '@evenement@' du @datev@.",
	'attente_activite_mail_sujet_responsable_frontend_p' => "@adherents@ se sont inscrits en liste d'attente de '@evenement@' du @datev@.",
	'date_inscription' => 'Date de votre inscription&nbsp;:',
	'desinscription_activite_mail_sujet_backend' => "Vous avez été désinscrit(e) de '@evenement@' du @datev@.",
	'desinscription_activite_mail_sujet_frontend' => "Vous vous êtes désinscrit(e) du rendez-vous '@evenement@' du @datev@",
	'desinscription_activite_mail_sujet_responsable_frontend' => "@adherents@ s'est désinscrit(e) de '@evenement@' du @datev@.",
	'desinscription_activite_mail_sujet_responsable_frontend_p' => "@adherents@ se sont désinscrits de '@evenement@' du @datev@.",
	'email_adresse_evenement' => 'L\'adresse du rendez-vous&nbsp;:',
	'email_attente_backend_texte' => 'Votre inscription à ce rendez-vous sera confirmée par un responsable dans le cas d\'un désistement d\'autres membres ou de l\'augmentation des places disponibles. Nous vous remercions de votre compréhension.',
	'email_attente_backend_titre' => "Votre inscription est désormais en file d'attente pour le rendez-vous '@evenement@'",
	'email_attente_frontend_texte' => 'Votre inscription à ce rendez-vous sera confirmée par un responsable en cas de désistement d\'autres membres ou d\'augmentation des places disponibles. Nous vous remercions pour votre compréhension.',
	'email_attente_frontend_titre' => "Vous vous êtes inscrit(e) en liste d'attente du rendez-vous '@evenement@'",
	'email_attente_responsable_frontend' => '@adherents@ s\'est inscrit(e) en liste d\'attente  au rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_attente_responsable_frontend_p' => '@adherents@ se sont inscrits en liste d\'attente  au rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@',
	'email_bouton_module_inscription' => 'Gestionnaire des inscriptions',
	'email_contact_adherent' => 'Coordonnées de la personne&nbsp;:',
	'email_date_evenement' => 'Date du rendez-vous&nbsp;:',
	'email_desinscription_backend' => 'Vous avez été désinscrit(e) au rendez-vous "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_desinscription_frontend' => "Vous vous êtes désinscrit(e) du rendez-vous '@evenement@' qui aura lieu le @date@ à @heure@",
	'email_desinscription_responsable_frontend' => '@adherents@ s\'est désinscrit(e) du rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@',
	'email_desinscription_responsable_frontend_p' => '@adherents@ se sont désinscrits du rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@',
	'email_erreur_desinscription' => 'En cas d\'erreur, merci de contacter rapidement un responsable&nbsp;:',
	'email_expiration_automatique_texte' => 'Votre demande d\'inscription à ce rendez-vous a été <b>annulée automatiquement</b> car nous n\'avons pas reçu le paiement dans le délai de <b> @nombre_jours@ jour(s)</b> suivant votre demande d\'inscription.<br> Si vous souhaitez participer à cet événement, vous pouvez soumettre à nouveau une demande via le site internet.<br>Si vous avez procédé au paiement mais que celui-ci n\'a pas été encaissé à temps, merci de contacter un(e) responsable dans le plus bref délai.',
	'email_expiration_automatique_titre' => "Annulation de votre demande d'inscription au rendez-vous '@evenement@' du @datev@.",
	'email_expiration_responsable_automatique' => '[Désinscription automatique] -  @adherents@ est désormais désinscrit(e) du rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@ car sa participation financière n\'a pas été encaissée à temps.',
	'email_expiration_responsable_automatique_p' => '[Désinscription automatique] -  @adherents@ sont désormais désinscrits du rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@ car sa participation financière n\'a pas été encaissée à temps.',
	'email_info_rendez_vous' => 'Rappel des informations à propos du rendez-vous&nbsp;:',
	'email_inscription_automatique_texte' => 'Votre demande d\'inscription à ce rendez-vous a été validée automatiquement suite à un désistement ou à l\'augmentation du quota pour cet événement. Vous n\'êtes plus en liste d\'attente.',
	'email_inscription_automatique_titre' => "Vous êtes désormais inscrit(e) au rendez-vous '@evenement@'",
	'email_inscription_backend_titre' => "Vous avez été inscrit(e) au rendez-vous '@evenement@'",
	'email_inscription_frontend_texte' => 'Votre inscription à ce rendez-vous a bien été enregistrée et validée.',
	'email_inscription_frontend_titre' => "Vous vous êtes inscrit(e) au rendez-vous '@evenement@'",
	'email_inscription_responsable_automatique' => '[Validation automatique] - @adherents@ est désormais inscrit(e) à un rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_inscription_responsable_automatique_p' => '[Validation automatique] - @adherents@ sont désormais inscrits à un rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_inscription_responsable_frontend' => '@adherents@ s\'est inscrit(e) à un rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_inscription_responsable_frontend_p' => '@adherents@ se sont inscrits à un rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_lien_rendez_vous' => 'Retrouvez toutes les informations à propos de ce rendez-vous sur notre site internet&nbsp;:',
	'email_modification_backend' => "Votre inscription au rendez-vous '@evenement@' qui aura lieu le @date@ à @heure@ a été modifiée.",
	'email_modification_frontend' => "Vous avez modifié votre inscription au rendez-vous '@evenement@' qui aura lieu le @date@ à @heure@.",
	'email_modification_inscription' => 'Si vous avez des questions à propos de ce rendez-vous, ou pour annuler ou modifier votre inscription, veuillez contacter un responsable&nbsp;:',
	'email_modification_responsable_frontend' => '@adherents@ a modifié son inscription à un rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_modification_responsable_frontend_p' => '@adherents@ ont modifié leur inscription à un rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_ne_pas_repondre' => 'Ne pas répondre à cet email automatique, veuillez contacter votre responsable activité sur son adresse email.',
	'email_preinscription_backend_titre' => 'Vous avez été préinscrit(e) au rendez-vous "@evenement@"',
	'email_preinscription_frontend_titre' => "Vous vous êtes préinscrit(e) au rendez-vous '@evenement@'",
	'email_preinscription_responsable_automatique' => '[Validation automatique] -  @adherents@ est désormais préinscrit(e) à un rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_preinscription_responsable_automatique_p' => '[Validation automatique] -  @adherents@ sont désormais préinscrits à un rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_preinscription_responsable_frontend' => '@adherents@ s\'est préinscrit(e) à un rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_preinscription_responsable_frontend_p' => '@adherents@ se sont préinscrits à un rendez-vous dont vous êtes responsable "@evenement@" qui aura lieu le @date@ à @heure@.',
	'email_texte_lien_module_inscription' => 'Pour valider et voir la liste des demandes d\'inscription, vous pouvez cliquer sur ce lien&nbsp;:',
	'expiration_automatique_activite_mail_sujet' => "Annulation de votre demande d'inscription au rendez-vous '@evenement@' du @datev@.",
	'expiration_automatique_activite_mail_sujet_responsable' => "[Désinscription automatique] - @adherents@ a été désinscrit(e) de '@evenement@' du @datev@.",
	'expiration_automatique_activite_mail_sujet_responsable_p' => "[Désinscription automatique] - @adherents@ ont été désinscrits de '@evenement@' du @datev@.",
	'inscription_activite_mail_sujet_backend' => "Vous avez été inscrit(e) à '@evenement@' du @datev@.",
	'inscription_activite_mail_sujet_frontend' => "Vous vous êtes inscrit(e) au rendez-vous '@evenement@' qui aura lieu le @datev@",
	'inscription_activite_mail_sujet_responsable_frontend' => "@adherents@ s'est inscrit(e) à '@evenement@' du @datev@.",
	'inscription_activite_mail_sujet_responsable_frontend_p' => "@adherents@ se sont inscrits à '@evenement@' du @datev@.",
	'inscription_automatique_activite_mail_sujet' => "Vous êtes désormais inscrit(e) à '@evenement@' du @datev@.",
	'inscription_automatique_activite_mail_sujet_responsable' => "[Validation automatique] - @adherents@ est désormais inscrit(e) à '@evenement@' du @datev@.",
	'inscription_automatique_activite_mail_sujet_responsable_p' => "[Validation automatique] - @adherents@ sont désormais inscrits à '@evenement@' du @datev@.",
	'mode_paiement_possible' => 'Mode de paiement à votre disposition&nbsp;:',
	'modification_activite_mail_sujet_backend' => "Votre inscription à '@evenement@' du @datev@ a été modifiée.",
	'modification_activite_mail_sujet_frontend' => "Vous avez modifié votre inscription au rendez-vous '@evenement@' du @datev@.",
	'modification_activite_mail_sujet_responsable_frontend' => "@adherents@ a modifié son inscription à '@evenement@' du @datev@.",
	'montant_adherent_a_regler' => 'Participation à régler&nbsp;:',
	'montant_recu' => 'Participation réglée : ',
	'nom_accompagnant' => 'Noms des participants&nbsp;:',
	'nombre_participant' => 'Nombre de participants&nbsp;:',
	'noms_accompagnants' => 'Noms des participants&nbsp;:',
	'preinscription_activite_mail_sujet_backend' => "Vous avez été préinscrit(e) à '@evenement@' du @datev@.",
	'preinscription_activite_mail_sujet_frontend' => "Vous vous êtes préinscrit(e) au rendez-vous '@evenement@' du @datev@.",
	'preinscription_activite_mail_sujet_responsable_frontend' => "@adherents@ s'est préinscrit(e) à '@evenement@' du @datev@.",
	'preinscription_activite_mail_sujet_responsable_frontend_p' => "@adherents@ se sont préinscrits à '@evenement@' du @datev@.",
	'preinscription_automatique_activite_mail_sujet' => "Vous êtes désormais préinscrit(e) à '@evenement@' du @datev@.",
	'preinscription_automatique_activite_mail_sujet_responsable' => "[Validation automatique] - @adherents@ est désormais préinscrit(e) à '@evenement@' du @datev@.",
	'preinscription_automatique_activite_mail_sujet_responsable_p' => "[Validation automatique] - @adherents@ sont désormais préinscrits à '@evenement@' du @datev@.",
	'titre_votre_inscription' => 'Informations d\'inscription',
	'votre_commentaire' => 'Message à l\'attention des responsables&nbsp;:',
	'evenement_non_presentiel_lien' => 'Lien de participation :',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
