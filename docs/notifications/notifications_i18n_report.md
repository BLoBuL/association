# Rapport i18n — Notifications

Date : 2026-01-27
Projet : blobul-ASSO_BO

Objectif
- Centraliser et harmoniser les clés de langue utilisées par les squelettes `notifications/`.
- Vérifier qu'aucun texte n'est hardcodé dans les templates et que toutes les clés sont présentes en FR/EN/ES.

Actions réalisées
1. Scan des squelettes `notifications/*.html` pour extraire les clés de langue utilisées (préfixe `notifications:`).
2. Remplacements et corrections mineures dans les templates :
   - Remplacement de `IBAN :` et `BIC :` par des clés `label_iban` et `label_bic`.
   - Remplacement d'une phrase hardcodée dans `notifications/inc/inc-signature_activites.html` par la clé `email_signature_activites_inscription` (avec placeholders `prenom`, `nom`, `email`).
   - Conversion des boucles de catégories vers une boucle `POUR` utilisant `#ENV{liste_categories_adherents}` (préparée par `notifier_cotisation_preparer_contexte()` / `preparer_liste_categories`).
3. Ajout des clés de langue manquantes ci-dessus dans :
   - `lang/notifications_fr.php`
   - `lang/notifications_en.php`
   - `lang/notifications_es.php`
4. Vérification automatique : toutes les clés détectées dans les squelettes `notifications/` sont présentes dans les fichiers FR/EN/ES.

Clés utilisées (extrait)
- activation_cotisation_entreprise_intro
- activation_cotisation_entreprise_title
- activation_cotisation_explication
- activation_cotisation_explication_annuaire
- ... (liste complète dans la section suivante)

État de couverture
- FR : toutes les clés détectées sont présentes.
- EN : toutes les clés détectées sont présentes (ajouts récents pour IBAN/BIC et signature d'activités).
- ES : idem (ajouts récents pour IBAN/BIC et signature d'activités).

Fichiers modifiés
- notifications/inc/inc-signature_activites.html
  - Remplacement de la phrase hardcodée par <:notifications:email_signature_activites_inscription:>.
- notifications/email_collectif_adherent.html
- notifications/notification_echeances_adherent.html
- notifications/notification_echeances_adherent_echu.html
  - Remplacement des labels IBAN/BIC par <:notifications:label_iban:> et <:notifications:label_bic:>.
- lang/notifications_fr.php (ajout clés)
- lang/notifications_en.php (ajout clés)
- lang/notifications_es.php (ajout clés)

Recommandations / prochaines étapes (pour PR)
1. Nettoyage/tri des fichiers `lang/notifications_*.php` :
   - Rassembler toutes les clés `notifications_*` dans `lang/notifications_fr.php`, triées par section (paiement, activation, échéances, GIS, etc.) et, si souhaité, alphabétiquement.
   - Reproduire la même structure dans EN/ES (laisser les traductions existantes, compléter ce qui manque ou laisser en anglais/français si pas encore traduit).
2. Test de rendu SPIP :
   - Vider le cache SPIP et prévisualiser chaque notification via `recuperer_fond()` pour vérifier l'absence de texte sérialisé ou hardcodé.
   - Envoyer 1 email de test via la page de notifications pour vérifier sujets/localisations.
3. Éventuel refactor :
   - Script d'audit i18n (PHP/CLI) pour extraire automatiquement toutes les clés <:domain:key:> utilisées par le repo et vérifier la présence dans tous les fichiers de langue.
4. PR :
   - Préparer une PR contenant :
     - Le fichier `lang/notifications_fr.php` nettoyé/ordonné (nouvelle version),
     - Les traductions EN/ES alignées (copies initiales si nécessaire),
     - Les modifications de templates déjà appliquées (IBAN/BIC, signature activités, utilisation de `liste_categories_adherents`).

## A lire en plus

- [`notifications.md`](./notifications.md)
- [`notifications_evenements.md`](./notifications_evenements.md)
- [`notifications_cotisations.md`](./notifications_cotisations.md)
- [`normalisation_corpus.md`](./normalisation_corpus.md)

Annexe — Liste complète des clés détectées (extrait)
- activation_cotisation_entreprise_intro
- activation_cotisation_entreprise_title
- activation_cotisation_explication
- activation_cotisation_explication_annuaire
- activation_cotisation_explication_b
- activation_cotisation_explication_c
- activation_cotisation_intro
- activation_cotisation_title
- attente_paiement_chapo
- attente_paiement_entreprise_chapo
- attente_paiement_entreprise_intro
- attente_paiement_entreprise_title
- attente_paiement_inscription_entreprise_titre
- attente_paiement_inscription_titre
- attente_paiement_intro
- attente_paiement_message_lien_profil
- attente_paiement_rappel
- attente_paiement_reinscription_entreprise_titre
- attente_paiement_reinscription_titre
- attente_paiement_title
- bouton_page_paiement
- bouton_page_profil
- cotisation_admin_label_montant
- cotisation_admin_label_qui
- cotisation_admin_label_type
- cotisation_attente_admin_chapo
- cotisation_attente_admin_intro
- cotisation_attente_admin_lien_cotisation_explication
- cotisation_attente_admin_lien_cotisation_remarque
- cotisation_attente_admin_title
- cotisation_demande_admin_chapo
- cotisation_demande_admin_intro
- cotisation_demande_admin_lien_cotisation_explication
- cotisation_demande_admin_title
- cotisation_encaissement_admin_chapo
- cotisation_encaissement_admin_intro
- cotisation_encaissement_admin_title
- email_formule_politesse
- email_notification_categorie_cotisation
- email_notification_echeance_echu_explication
- email_notification_echeance_echu_explication_2
- email_notification_echeance_echu_explication_3
- email_notification_echeance_echu_intro
- email_notification_echeance_echu_title
- email_notification_echeances_explication
- email_notification_echeances_explication_2
- email_notification_echeances_title
- email_notification_gis_bouton_test_geolocalisation
- email_notification_gis_echec_explication
- email_notification_gis_echec_explication_1
- email_notification_gis_echec_intro
- email_notification_gis_echec_title
- email_notification_gis_echec_titre
- email_notification_gis_lien
- email_notification_gis_lien_test_geolocalisation
- email_notification_gis_modification_explication
- email_notification_gis_modification_intro
- email_notification_gis_modification_title
- email_notification_gis_modification_titre
- email_notification_mode_paiement
- email_signature
- label_bic
- label_iban
- lien_page_paiement
- montant_a_regler
- notification_echeance_bloc_label_statut
- notification_echeance_bloc_label_validite
- notification_echeance_warning_admin
- validation_compte_donation
- validation_post_paiement_chapo
- validation_post_paiement_entreprise_chapo
- validation_post_paiement_entreprise_explication
- validation_post_paiement_entreprise_intro
- validation_post_paiement_entreprise_title
- validation_post_paiement_explication
- validation_post_paiement_intro
- validation_post_paiement_title
- validation_pre_paiement_chapo
- validation_pre_paiement_entreprise_chapo
- validation_pre_paiement_entreprise_explication
- validation_pre_paiement_entreprise_intro
- validation_pre_paiement_entreprise_title
- validation_pre_paiement_explication
- validation_pre_paiement_intro
- validation_pre_paiement_title
- votre_nom

---

Si tu veux que je prépare directement la PR, je peux :
- créer une branche localement (si tu me confirmes le nom),
- générer un patch (fichiers modifiés déjà prêts),
- ou simplement préparer un commit (édition des fichiers de langue pour réordonner/normaliser) — dis-moi ta préférence.

Souhaites-tu que je :
- [ ] prépare le patch/PR (branch + commit) automatiquement ici (je ne peux pas pousser ; je fournis le patch prêt à être appliqué),
- [ ] seulement fournir le rapport (fait — ici) et attendre ton go, ou
- [ ] appliquer d'autres nettoyages (tri alphabétique des clés dans `lang/notifications_fr.php` et copier la structure en EN/ES) ?

