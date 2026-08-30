# Référence complète des plugins et de leurs interactions

Cette documentation décrit l’état du code de la suite Association 4 dans le
monorepo. Elle distingue les fonctions déjà implémentées des intégrations
décidées mais restant à réaliser.

## 1. Principes d’architecture

La suite comporte un socle et treize modules. Tous résident dans le même dépôt,
mais chacun reste un plugin SPIP installable et activable séparément.

- `association` est le socle et ne dépend d’aucun module métier ;
- chaque module métier exige le socle ;
- aucun module métier ne doit exiger un autre module métier de la suite ;
- une dépendance SPIP externe indispensable est déclarée avec `necessite` ;
- une intégration facultative est déclarée avec `utilise` ou détectée par une capacité ;
- chaque table et chaque objet métier possède un seul plugin propriétaire ;
- l’absence d’un complément produit un comportement dégradé normal ;
- les désinstallations conservent les données métier ;
- les échanges utilisent des pipelines, capacités ou façades publiques, jamais
  les fichiers internes d’un plugin facultatif.

### Sens des relations

| Relation | Signification |
|---|---|
| `necessite association` | utilisation des contrats communs du socle |
| `necessite <plugin externe>` | fonction principale impossible sans ce plugin SPIP |
| `utilise <plugin>` | enrichissement facultatif ; fonctionnement maintenu sans lui |
| capacité | service détectable avec `association_capacite_disponible()` |
| pipeline public | contrat extensible entre producteur et consommateurs |

## 2. Vue d’ensemble

| Plugin | Préfixe | Responsabilité principale | Données possédées |
|---|---|---|---|
| Association | `association` | socle, configuration, capacités, migrations, maintenance, RGPD, menus | configuration commune et journal de migration |
| Adhésions | `association_adhesions` | catégories d’adhérents, adhésions et cotisations | `spip_asso_categories_adherents`, `spip_asso_cotisations` |
| Événements | `association_evenements` | activités, inscriptions, participants et tarifs | `spip_asso_categories_activites`, `spip_asso_activites`, `spip_asso_categories_activites_liens` et champs événementiels |
| Comptabilité | `association_compta` | journal, plan comptable et analytique | `spip_asso_comptes`, `spip_asso_plan`, `spip_asso_destination`, `spip_asso_destination_op` |
| Paiements | `association_paiements` | adaptateur Bank et orchestration des règlements | aucune table propre ; Bank possède les transactions |
| Communication | `association_communication` | abonnements, notifications et campagnes | aucune table propre ; les plugins externes possèdent leurs tables |
| Groupes | `association_groupes` | groupes, rôles, responsables et bénévoles | aucune table propre ; auteurs et groupes SPIP existants |
| Prêts | `association_prets` | ressources prêtables, sorties et restitutions | `spip_asso_ressources`, `spip_asso_prets` |
| Dons | `association_dons` | dons et suivi administratif | `spip_asso_dons` |
| Ventes | `association_ventes` | ventes manuelles et expéditions | `spip_asso_ventes` |
| Commerce | `association_commerce` | boutique, panier et commande en ligne | aucune table propre ; Paniers et Commandes possèdent les données |
| Partenaires | `association_partenaires` | qualification et publication des partenaires | `spip_asso_partenaires` |
| Bannières | `association_bannieres` | campagnes publicitaires et emplacements | `spip_asso_bannieres` |
| Bons plans | `association_bons_plans` | recommandations, proposition publique et modération | `spip_bons_plans`, `spip_bons_plans_liens` |

## 3. Contrats transversaux du socle

### Capacités

`association_capacites_lister()` agrège le pipeline `association_capacites`.
`association_capacite_disponible($nom)` permet de tester une fonction métier
sans connaître son fournisseur.

| Capacités | Fournisseur |
|---|---|
| `adhesions`, `cotisations`, `profil_membre` | Adhésions |
| `familles` | Familles via l’adaptateur Adhésions lorsqu’il est actif |
| `evenements`, `inscriptions_evenements` | Événements |
| `comptabilite`, `ecritures_comptables` | Comptabilité |
| `paiements`, `transactions` | Paiements |
| `communication`, `notifications_metier`, `campagnes_email` | Communication |
| `groupes` | Groupes |
| `prets`, `ressources` | Prêts |
| `dons` | Dons |
| `ventes` | Ventes |
| `commerce` | Commerce |
| `partenaires` | Partenaires |
| `bannieres` | Bannières |
| `bons_plans` | Bons plans |

### APIs facultatives communes

| API | Pipeline | Résultat neutre sans fournisseur |
|---|---|---|
| `association_profil_participant()` | `association_profil_participant` | participant générique, non membre, sans famille |
| `association_contexte_familial()` | `association_contexte_familial` | contexte individuel inchangé |
| `association_demander_contrat()` | `association_contrat_demander` | demande valide avec `id_contrat=0` |
| `association_comptabiliser_operation()` | `association_comptabiliser_operation` | `comptabilisee=false`, `id_compte=0` |
| `association_notifier_metier()` | `association_notifier_metier` | `envoyee=false`, sans erreur métier |

### Contrats d’infrastructure

- `association_menu_entrees` : entrées du menu privé ;
- `association_configuration_navigation`, `association_configuration_saisies`
  et `association_configuration_verifier` : configuration distribuée ;
- `association_installation_inventaire` : plugins, tables, objets et schémas ;
- `association_migrations_historiques` : adoption des anciennes structures ;
- `association_rgpd_export_auteur` et `association_rgpd_anonymiser_auteur` :
  traitement par le propriétaire de chaque donnée ;
- pipelines `association_maintenance_*` : maintenance distribuée ;
- `association_log_categories` : catégories de journaux ;
- `association_config_cli_registre` : options exposées à la CLI.

### Pipelines métier spécialisés

| Famille | Pipelines | Objet du contrat |
|---|---|---|
| inscription événement | `association_inscription_evenement_charger`, `association_inscription_evenement_verifier`, `association_inscription_evenement_traiter` | enrichir le CVT sans imposer un module voisin |
| contexte événement | `association_evenement_resoudre_contexte` | résoudre participant, droits et données de l’événement |
| paiement | `association_paiements_reglement_traiter`, `association_paiements_redirection_transaction`, `association_paiements_remboursement_traiter` | distribuer les changements d’état financier aux propriétaires métier |
| références de transaction | `association_paiements_transactions_references`, `association_paiements_transactions_informations` | ajouter les références et informations utiles sans modifier Bank |
| objets comptables | `association_compta_objets_declarer` | déclarer les objets pouvant produire des écritures |
| délégation comptable | `association_compta_autoriser_ecriture`, `association_compta_redirection_ecriture` | laisser le propriétaire métier décider l’autorisation et le retour BO |
| migration comptable | `association_compta_migration_metiers`, `association_compta_ecritures_devises` | répartir migrations et devises entre propriétaires |
| statistiques | `association_evenements_stats_compta` | enrichir les synthèses d’exercice avec les événements |
| communication événement | `association_communication_email_collectif_evenement` | contribuer destinataires et contenu d’un email collectif |
| catalogue de notifications | `association_notifications_metiers`, `association_notification_exemple`, `association_notifications_audit_html` | déclarer modèles, exemples et preuves d’audit |

## 4. Fiches détaillées

### 4.1 Association — socle

**Rôle.** Configuration commune, registre de capacités, pipelines, autorisations
communes, navigation, journalisation, migrations, maintenance et RGPD.

**Dépendance obligatoire.** Saisies.

**Autonomie.** Activable seul, sans table ni écran métier. Il ne doit gérer ni
cotisation, transaction, activité, écriture ni campagne.

### 4.2 Adhésions / Cotisations

**Rôle.** Catégories d’adhérents, périodes et états d’adhésion, cotisations,
inscription et profil membre.

**Dépendances obligatoires.** Inscription4, Intl et Médias.

**Compléments facultatifs.** Comptabilité, Paiements, Communication, Familles,
Accès restreint et GIS.

**Interactions.**

- fournit à Événements le profil, l’éligibilité, la famille et les droits
  tarifaires par `association_profil_participant` ;
- enrichit le contexte familial lorsque Familles est actif ;
- conserve `id_compte` nullable si Comptabilité est absente ;
- rattache facultativement un règlement Bank à la cotisation ;
- contribue aux modèles, destinataires et audits de notifications ;
- expose ses objets à Comptabilité sans lui transférer leur propriété.

**Repli.** Une cotisation gratuite, manuelle ou non comptabilisée reste valide.

### 4.3 Événements

**Rôle.** Compléter Agenda avec catégories, participants, inscriptions, quotas,
tarifs, règlements et statistiques.

**Dépendances obligatoires.** Agenda, Saisies, Vérifier, Inscription4 et Champs
Extras.

**Compléments facultatifs.** Adhésions, Comptabilité, Paiements et Communication.

**Interactions.**

- interroge le profil participant sans lire les tables d’Adhésions ;
- publie et consomme les contrats de règlement, redirection et remboursement ;
- déclare ses objets comptabilisables et contribue aux statistiques d’exercice ;
- fournit à Communication les destinataires et modèles des emails collectifs.

**Repli.** Sans Adhésions, profil public universel et tarifs génériques. Sans
Paiements ou Comptabilité, inscription valide sans transaction ni écriture.

### 4.4 Comptabilité

**Rôle.** Journal `spip_asso_comptes`, plan comptable, destinations analytiques,
exercices et APIs d’écriture.

**Dépendance obligatoire.** Intl. Commandes est facultatif.

**Interactions.**

- implémente `association_comptabiliser_operation()` ;
- consomme les règlements publiés par Paiements ;
- reçoit les objets comptabilisables d’Adhésions, Événements, Dons, Ventes et Prêts ;
- délègue au propriétaire métier autorisation et redirection d’une écriture ;
- fournit les statistiques d’exercice demandées par Événements.

**Repli.** Le journal fonctionne sans Paiements et accepte des écritures manuelles.

### 4.5 Paiements

**Rôle.** Adapter Bank aux actes métier, unifier lectures de transaction,
retours, remboursements, redirections et références.

**Dépendance obligatoire.** Bank.

**Compléments facultatifs.** Commandes et Formidable.

**Interactions.** Bank reste propriétaire des transactions. Paiements publie
les règlements avec `association_paiements_reglement_traiter`; Adhésions,
Événements et Comptabilité enrichissent ou consomment ce flux.

**Repli.** Tout objet métier peut exister sans transaction.

### 4.6 Communication

**Rôle.** Abonnements, listes, campagnes Mailshot, notifications, privilèges,
gabarits et emails collectifs.

**Dépendances obligatoires.** Notifications, Mailsubscribers et Mailshot.

**Interactions.** Implémente `association_notifier_metier()`, reçoit les
destinataires et modèles d’Adhésions et Événements, orchestre l’email collectif
et peut notifier les modérateurs d’un Bon plan proposé.

**Repli.** Les opérations métier restent enregistrées sans envoi facultatif.

### 4.7 Groupes

**Rôle.** Groupes, fonctions, rôles, responsables et bénévoles à partir des
auteurs et liens SPIP.

**Dépendance obligatoire.** Aucune en dehors du socle.

**Complément facultatif.** Adhésions enrichit les fiches avec le profil membre
et la cotisation. Sans lui, les URLs et auteurs SPIP natifs sont utilisés.

### 4.8 Prêts

**Rôle.** Catalogue des ressources, emprunteurs, sorties, retours et disponibilités.

**Dépendance obligatoire.** Intl.

**Compléments facultatifs.** Comptabilité et Paiements. Le prêt est persisté
avant toute demande facultative d’écriture ou de règlement.

### 4.9 Dons

**Rôle.** Enregistrer les dons indépendamment de leur mode de règlement ou de
comptabilisation.

**Dépendance obligatoire.** Aucune en dehors du socle.

**Compléments facultatifs.** Comptabilité, Paiements et Communication. Le don
reste valide lorsque ces modules sont absents.

### 4.10 Ventes

**Rôle actuel.** Ventes manuelles ou hors ligne, acheteur, article, quantité,
prix appliqué, frais et expédition.

**Dépendance obligatoire.** Aucune en dehors du socle.

**Compléments actuellement déclarés.** Comptabilité, Paiements et Adhésions.

**Frontière avec Commerce.** Ventes est un registre opérationnel manuel ;
Commerce orchestre un achat en ligne. Aucun des deux ne doit devenir propriétaire
d’un catalogue ou d’un tarif courant concurrents.

### 4.11 Commerce

**Rôle.** Boutique, mini-panier, panier, création et consultation de commande.

**Dépendances obligatoires actuelles.** Paniers, Commandes et Prix.

**Compléments facultatifs.** Paiements et Contrats.

**Interactions.** Paniers possède le panier, Commandes la commande et ses lignes
historiques, Prix calcule les montants. L’adaptateur Contrats implémente
`association_demander_contrat()` et retourne `id_contrat=0` si Contrats est absent.

**Écart connu.** Le catalogue boucle encore sur les articles SPIP et ajoute
l’objet `article` au panier. Le passage au plugin Produits est décidé mais pas
encore implémenté.

### 4.12 Partenaires

**Rôle.** Qualifier une organisation comme partenaire et définir niveau,
périodes, ordre et affichage public.

**Dépendance obligatoire.** Contacts & Organisations, qui reste propriétaire de
l’organisation et de ses coordonnées.

### 4.13 Bannières

**Rôle.** Campagnes, emplacements, périodes, ordre et publication. Le logo SPIP
porte le visuel.

**Dépendance obligatoire.** Aucune en dehors du socle. Le module n’exige ni
Commerce ni Partenaires.

### 4.14 Bons plans

**Rôle.** Reprise SPIP 4 du plugin Bon plan : objet éditorial, rubriques,
auteurs, proposition publique, modération, refus, corbeille et dépublication.

**Dépendance obligatoire.** Saisies.

**Complément facultatif.** Communication. Sans lui, la proposition reste au
statut `prop`. Les tables historiques sont adoptées sans copie et l’ancien
plugin `spip_bon_plan` est incompatible pour éviter une double déclaration.

## 5. Points d’entrée BO et FO

Les noms ci-dessous correspondent aux valeurs `page` ou `exec`; SPIP les sert
habituellement avec `spip.php?page=<page>` et `ecrire/?exec=<exec>`.

| Plugin | Front office fourni | Back office principal |
|---|---|---|
| Association | aucun métier | `configurer_association`, installation et maintenance |
| Adhésions | `inscription`, `profil`, `fiche_adherent` | `adherents`, `cotisations`, catégories, recherche et fiches |
| Événements | `evenement` | `activites`, catégories, suivi, exports, statistiques et emails collectifs |
| Comptabilité | aucun écran public | `comptes`, `bilan`, `plan_comptable`, `destinations` et imports |
| Paiements | modèles de paiement intégrables | `transactions` et actions d’abandon, suppression ou remboursement |
| Communication | `newsletter` | `notifications` |
| Groupes | modèles de responsables/membres | `benevoles` |
| Prêts | `ressources` | `ressources`, `prets` et formulaires d’édition |
| Dons | aucun écran public propre | `dons` |
| Ventes | aucun écran public propre | `ventes` |
| Commerce | `boutique`, `panier`, `commande` | `commerce` |
| Partenaires | `partenaires`, `partenaire` | `partenaires` |
| Bannières | `bannieres`, `banniere` | `bannieres` |
| Bons plans | `bons_plans`, `bon_plan`, `proposer_bon_plan` | `bons_plans`, `bon_plan`, `bon_plan_edit` |

Les modèles publics restent préférables lorsqu’une fonctionnalité doit être
insérée dans un squelette existant plutôt qu’exposée comme page autonome.

## 6. Matrice des interactions métier

| Producteur | Consommateur facultatif | Contrat | Effet présent | Repli absent |
|---|---|---|---|---|
| Adhésions | Événements | profil participant | membre, famille, droits, tarifs | participant générique |
| Familles | Adhésions | contexte familial | foyer, membres, rôles | profil individuel |
| Adhésions | Comptabilité | objet comptabilisable | écriture et `id_compte` | cotisation sans écriture |
| Adhésions | Paiements | règlement/redirection | transaction liée | règlement manuel ou gratuit |
| Adhésions | Communication | notification/audit | relances et modèles | aucun envoi |
| Événements | Comptabilité | objet, autorisation, statistiques | écritures et synthèses | aucune écriture |
| Événements | Paiements | règlement, redirection, remboursement | transaction Bank | aucune transaction |
| Événements | Communication | destinataires et modèles | emails collectifs | aucun envoi |
| Paiements | Comptabilité | règlement publié | synchronisation d’écriture | transaction seule |
| Dons | Compta/Paiements | objet et règlement | écriture ou transaction | don autonome |
| Prêts | Compta/Paiements | objet et règlement | caution/frais suivis | prêt autonome |
| Ventes | Compta/Paiements | objet et règlement | vente comptabilisée/réglée | vente autonome |
| Groupes | Adhésions | profil membre | informations d’adhésion | auteur SPIP seul |
| Commerce | Paiements | règlement | paiement de commande | commande non réglée en ligne |
| Commerce | Contrats | demande idempotente | création/synchronisation | `id_contrat=0` |
| Bons plans | Communication | notification métier | alerte modérateurs | proposition conservée |

## 7. Plugins externes

| Plugin externe | Utilisé par | Responsabilité conservée |
|---|---|---|
| Saisies | Socle, Événements, Bons plans | description et rendu des formulaires |
| Vérifier | Événements | validation des saisies |
| Inscription4 | Adhésions, Événements | inscription et champs associés |
| Intl | Adhésions, Comptabilité, Prêts | montants, devises et formats |
| Médias (`medias`) | Adhésions | documents et médias |
| Agenda | Événements | événements éditoriaux de référence |
| Champs Extras (`cextras`) | Événements | champs complémentaires |
| Bank | Paiements | transactions, prestataires et encaissement |
| Notifications | Communication | infrastructure de notification |
| Mailsubscribers | Communication | abonnés et listes |
| Mailshot | Communication | campagnes et envois collectifs |
| Paniers | Commerce | panier courant |
| Commandes | Commerce ; facultatif Compta/Paiements | commandes et lignes historiques |
| Prix | Commerce | prix courants et taxes |
| Contacts & Organisations | Partenaires | organisations et coordonnées |
| Familles | Adhésions, facultatif | foyers et rôles familiaux |
| Contrats | Commerce, facultatif | contrats et cycle contractuel |
| Accès restreint (`accesrestreint`) | Adhésions, facultatif | restrictions d’accès |
| GIS | Adhésions, facultatif | géolocalisation |
| Formidable | Paiements, facultatif | paiements de formulaires |

## 8. Produits, Prix, Commerce et Ventes : cible décidée

| Domaine | Source de vérité cible |
|---|---|
| identité, référence et description | Produits |
| tarif courant, taxes et calcul | Prix |
| panier temporaire | Paniers |
| commande et photographie du montant | Commandes |
| orchestration de boutique | Commerce |
| vente manuelle et expédition | Ventes |

Une photographie du libellé et du prix dans une commande ou une vente prouve
les conditions appliquées ; elle ne concurrence pas le catalogue ni Prix.

### Travail restant

1. ajouter Produits aux dépendances obligatoires de Commerce ;
2. remplacer le catalogue d’articles par une boucle `PRODUITS` ;
3. conserver Prix comme seule API du tarif courant ;
4. ajouter à Ventes des liens facultatifs vers Produit et Commande, une origine
   et des instantanés historiques ;
5. publier un contrat idempotent Commande vers Vente ;
6. tester Ventes seule, Commerce avec ses dépendances, puis la combinaison.

## 9. Combinaisons garanties

| Combinaison | Comportement attendu |
|---|---|
| Association seule | configuration commune, aucune table métier |
| module seul avec ses dépendances externes | pages et objets propres, aucune fonction absente |
| Événements sans Adhésions | inscriptions génériques et quotas |
| Événements + Adhésions | droits membres, familles et tarifs enrichis |
| Adhésions sans Compta/Paiements | cotisations gratuites ou manuelles |
| producteur sans Comptabilité | objet conservé avec lien comptable nul |
| producteur + Comptabilité | écriture créée ou synchronisée facultativement |
| producteur sans Paiements | objet valide sans transaction |
| producteur + Paiements | transaction Bank rattachée par contrat |
| module sans Communication | aucune notification facultative, aucune perte métier |
| suite complète | enrichissements cumulés sans transfert de propriété |

## 10. Règles pour une nouvelle interaction

1. identifier le propriétaire de la donnée ;
2. enregistrer l’objet principal avant l’appel facultatif ;
3. exposer une capacité ou un pipeline documenté ;
4. fournir un résultat neutre stable ;
5. garder les identifiants externes nullable ;
6. rendre la synchronisation idempotente ;
7. ne jamais inclure un fichier interne facultatif ;
8. tester activation, désactivation et réactivation ;
9. migrer sans supprimer ni recopier inutilement les données ;
10. documenter fournisseur, consommateur et comportement de repli.

## 11. Portée de cette référence

Cette référence est fondée sur les `paquet.xml`, les inventaires
`association_installation_inventaire`, les capacités et les pipelines présents
dans la branche de travail. Les décisions Produits/Ventes indiquées comme cible
ne sont pas considérées comme livrées avant modification et recette du code.
