# Revue du corpus - logiques metier encore peu ou pas documentees

## But

Identifier les zones du plugin qui ne sont pas encore couvertes, ou seulement partiellement, par la documentation existante.

Cette revue ne remplace pas la documentation de fond. Elle sert de feuille de route pour la suite:

- prioriser les sujets a documenter;
- eviter les doublons;
- garder la base exploitable par les IA et transformable en guide utilisateur.

## Ce qui est deja bien couvert

- configuration centrale du plugin;
- autorisations;
- notifications de cotisation;
- echeances;
- comptabilite des evenements;
- categories de cotisation;
- categories de participation financiere;
- modes de paiement;
- parametres globaux des evenements.

## Zones encore insuffisamment documentees

### 1. Dons

Fichiers a couvrir:

- `formulaires/editer_asso_dons.php`;
- `action/editer_asso_dons.php`;
- `inc/comptes.php` (fonctions `compte_don`, `modifier_compte_don`);
- `prive/objets/liste/item_don.html` si present dans le flux prive;
- exports et notifications associees si elles existent.

Logiques metier a documenter:

- creation / modification d'un don;
- lien entre don et compte comptable;
- choix du journal et de l'imputation;
- destination comptable si active;
- impact sur les listes privees et exports.

### 2. Ventes

Fichiers a couvrir:

- `formulaires/editer_asso_ventes.php`;
- `action/editer_asso_ventes.php`;
- `inc/comptes.php` (fonctions `compte_vente`, `compte_vente_frais_envoi`, `modifier_compte_vente`, `modifier_activite_vente_frais_envoi`);
- `prive/objets/liste/item_vente.html` et les vues privees associees;
- `export_*.csv.html` si un export metier existe.

Logiques metier a documenter:

- vente simple vs vente avec frais d'envoi;
- rattachement a un acheteur;
- calcul du total;
- prise en charge du journal comptable;
- destination comptable de la vente et des frais.

### 3. Prets et ressources

Fichiers a couvrir:

- `formulaires/editer_asso_ressources.php`;
- `formulaires/editer_asso_prets.php` si present dans le flux ancien;
- `formulaires/editer_asso_pret.php` (remplace les anciennes actions d'ajout et de modification) ;
- `action/supprimer_prets.php`;
- `inc/comptes.php` si des liens comptables sont mobilises;
- `exec/prets.php`;
- `exec/ressources.php`.

Logiques metier a documenter:

- cycle de vie d'une ressource;
- creation / restitution d'un pret;
- duree, date de retour et statut;
- impact sur les alertes ou les listes de suivi;
- lien avec le socle comptable si une imputation existe.

### 4. Plan comptable et destinations

Fichiers a couvrir:

- `formulaires/editer_asso_plan.php`;
- `formulaires/importer_plan_comptable.php`;
- `formulaires/editer_asso_destinations.php`;
- `formulaires/importer_destination_comptable.php`;
- `inc/comptes.php`;
- `inc/destinations.php`;
- `inc/association_comptabilite.php`.

Logiques metier a documenter:

- structure du plan comptable;
- classes, comptes, type d'operation;
- structure arbre des destinations;
- import JSON recursive;
- verification de doublons et compatibilite;
- activation ou non des destinations dans les ecritures.

### 5. Migration / synchronisation comptable

Fichiers a couvrir:

- `formulaires/migrer_asso_comptabilite.php`;
- `action/synchroniser_comptabilite_evenement.php`;
- `formulaires/supprimer_asso_cotisation.php` si impact comptable;
- `action/valider_compte.php`;
- `action/invalider_compte.php`.

Logiques metier a documenter:

- migration automatique vs manuelle;
- recalage des cotisations, activites et transactions;
- detection et suppression des doublons;
- invalidation / validation d'un compte;
- impact sur l'historique.

### 6. GIS et geolocalisation

Fichiers a couvrir:

- `inc/fonctions/gis_auteur.php`;
- `inc/fonctions/facteur_envoyer_notification_gis.php`;
- `action/gis_geocoder_rechercher.php`;
- le bloc GIS de `configurer_association`;
- les notifications GIS.

Logiques metier a documenter:

- geocodage d'un auteur;
- mise a jour / suppression de localisation;
- notification sur modification ou echec;
- filtrage selon configuration GIS.

### 7. Recherche adherents et segmentation

Fichiers a couvrir:

- `formulaires/adherents_recherche_rapide.php`;
- `formulaires/adherents_recherche_avancee.php`;
- `inc/adherents_search_context.php`;
- `lang` des champs de recherche et des segments.

Logiques metier a documenter:

- moteur de recherche rapide;
- recherche avancee par champs extras;
- construction des criteres de filtrage;
- synchronisation avec la configuration des segments et des champs extras.

### 8. Emails collectifs et gabarits

Fichiers a couvrir:

- `formulaires/email_collectif_adherent.php`;
- `formulaires/choisir_gabarit_envoi_collectif.php`;
- `formulaires/email_collectif_activite.php` si present via d'autres squelettes ou flux;
- `action/envoyer_email_collectif_adherent.php`;
- `action/envoyer_email_collectif_activite.php`;
- les squelettes `notifications/email_collectif_*.html`.

Logiques metier a documenter:

- selection du gabarit;
- destinataires et filtres;
- pieces jointes ou variables de contexte;
- interaction avec inscription3 et les membres actifs.

### 9. Relances, maintenance et cron

Fichiers a couvrir:

- `genie/association_taches_generales.php`;
- `genie/association_maintenance_bdd.php`;
- `genie/association_expiration_auto_evenement.php`;
- `action/envoyer_relances.php`;
- `action/modifier_relances.php`;
- `exec/action_relances.php` et `exec/edit_relances.php`.

Logiques metier a documenter:

- cron de relance des cotisations;
- expiration automatique des inscriptions;
- maintenance BDD en mode test et en mode reel;
- nettoyage des donnees obsoletes;
- traitement lot par lot.

### 10. Journalisation et debug

Fichiers a couvrir:

- `inc/association_log.php`;
- bloc `debug` de la configuration;
- categories de logs dans `association_log_categories_defaut()`.

Logiques metier a documenter:

- structure des categories de journal;
- niveaux de severite;
- activations par webmestre;
- usages operationnels du debug.

### 11. Import / export et produits secondaires

Fichiers a couvrir:

- `export_activites.csv.html`;
- `export_cotisations.csv.html`;
- `export_evenements_compta.xml.html`;
- `export_comptes_evenement.csv.html`;
- `inscriptions_evenement.csv.html`;
- `formulaires/importer_*`;
- `formulaires/synchro_asso_membres.php`;
- `action/synchroniser_asso_membres.php`.

Logiques metier a documenter:

- structure des exports;
- colonnes metiers;
- import de comptabilite;
- synchronisation des membres;
- conventions de nommage des fichiers.

### 12. Commandes et factures

Fichiers a couvrir:

- `prive/objets/liste/item_commande.html` si la logique est integree via commandes;
- `prive/inclure/miniature_commande.html`;
- `action/supprimer_commande.php`;
- les fonctions qui manipulent les commandes et factures si elles sont presente dans d'autres plugins ou surcharges.

Logiques metier a documenter:

- creation de commande;
- lien commande/facture/transaction;
- suppression et archivage;
- integration avec l'espace client futur.

### 13. Logiques transversales encore trop peu narrees

Ces points sont visibles dans la cartographie et dans les fiches de dependances, mais ils meritent encore des pages metier ou des sous-sections plus explicites.

Fichiers a couvrir en priorite:

- `association_pipelines.php` pour les hooks de notification, paiement, inscription3 et mailsubscribers;
- `formulaires/inc/inscription_evenement.php` pour le cycle de vie complet des inscriptions et la synchro des repetitions;
- `inc/fonctions/association_validite_calculator.php` et `inc/fonctions/association_job_notifier_echeance.php` pour la logique echeance;
- `inc/fonctions/priviliges_adherent.php` pour la segmentation newsletter / mailsubscribers;
- `base/association_champs_extras.php` pour la fabrication des champs dynamiques;
- `inc/association_comptabilite.php` pour les ecritures et destinations.

Logiques metier a documenter:

- le hook `association_notifications_destinataires()` qui complete la selection des destinataires et des admins;
- la synchronisation des listes `mailsubscribers` / `newsletter` depuis les statuts d'adhesion;
- l'inscription collective via `inscrire_participant_mailsubscriber()` et ses effets sur les listes de diffusion;
- l'ajustement automatique des repetitions de tarifs via `association_sync_repetitions_tarifs()` et `association_get_liens_map()`;
- l'assouplissement des saisies obligatoires pour `inscription3` selon le contexte admin;
- la redirection apres retour bancaire via `association_bank_redirige_apres_retour_transaction()`;
- le chargement dynamique des champs extras, y compris les blocs FIAFE, attente, validation et paiement;
- la logique de disponibilite des champs de saisie via `association_saisies_lister_disponibles()`;
- la logique de requete/renseignement des champs via `association_champ_requete_renseigne()`.

## Observations transversales

### Documentation deja partiellement presente mais a renforcer

- `tarifs-logique.md` couvre la logique des tarifs evenement, mais pas tout le parcours de cotisation.
- `configurer_association_inventaire.md` recense les champs, mais ne decrit pas encore les regles de traitement metier derriere chaque champ.
- `notifications_i18n_report.md` est utile pour la maintenance des chaines, mais ne remplace pas une architecture globale des notifications.

### Themes probablement prioritaires

1. Dons, ventes, prets, ressources.
2. Plan comptable, destinations, migration et synchronisation comptable.
3. Recherche adherents et emails collectifs.
4. GIS et maintenance cron.
5. Import/export et commandes/factures.

## Conclusion

Le corpus documentaire actuel couvre bien la configuration generale, les autorisations, les notifications cotisations, les tarifs evenement et la comptabilite evenement.

En revanche, il reste encore plusieurs blocs metiers structurants a documenter pour disposer d'une base completement exploitable par les utilisateurs et les IA:

- modules financiers secondaires;
- outils d'administration du socle comptable;
- recherche et communication;
- maintenance et automation;
- partie GIS et geolocalisation.
