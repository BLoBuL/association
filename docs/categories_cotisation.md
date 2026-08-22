# Categories de cotisation

## But

Documenter la logique technique des categories de cotisation, depuis le formulaire d'édition jusqu'au traitement des adhésions et des notifications.

Cette page sert de reference pour:

- la configuration des catégories dans le back-office;
- la génération des formulaires de cotisation;
- la validation des montants et des pièces justificatives;
- le déclenchement des notifications et des activations;
- le filtrage des catégories selon le profil de l'adhérent.

## Fichiers de reference

- [`formulaires/editer_asso_categorie_cotisation.php`](../formulaires/editer_asso_categorie_cotisation.php)
- [`formulaires/inc/configurer_association.php`](../formulaires/inc/configurer_association.php)
- [`inc/api_cotisations.php`](../inc/api_cotisations.php)
- [`inc/cotisations.php`](../inc/cotisations.php)
- [`notifications/`](../notifications/)
- [`base/association.php`](../base/association.php)

## Structure de donnees

Les categories de cotisation sont stockées dans `spip_asso_categories_adherents`.

### Champs principaux

| Champ | Role |
|---|---|
| `id_categorie` | identifiant technique |
| `valeur` | libelle visible |
| `statut` | `ok` ou `desactive` |
| `eligibilite` | inscription, reinscription ou tout |
| `type_adherent` | profil cible |
| `nombre_enfants` | filtre familial |
| `document_justificatif` | justificatif requis ou non |
| `validation` | mode de validation |
| `cotisation` | montant de la cotisation |
| `devise` | code ISO 4217 propre à la catégorie, ou devise Intl du site pour l'historique |
| `mode_paiement` | moyens de paiement autorisés |
| `paiement_en_ligne` | paiement en ligne actif ou non, réglé par traitement |
| `commentaires` | aide ou explication |

## Formulaire d'edition

Le formulaire technique est `formulaires/editer_asso_categorie_cotisation.php`.

### Champs saisis

| Champ | Role |
|---|---|
| `valeur` | nom de la catégorie |
| `statut` | activation |
| `eligibilite` | contexte d'utilisation |
| `type_adherent` | type d'adhérent concerné |
| `nombre_enfants` | filtre pour familles / enfants |
| `document_justificatif` | demande de document |
| `validation` | règle de traitement |
| `commentaires` | texte d'aide visible |
| `cotisation` | montant |
| `devise` | devise de cette catégorie, choisie dans le référentiel Intl lorsque les cotisations multidevises sont activées |
| `mode_paiement` | cases à cocher des moyens de paiement autorisés |

### Valeurs par defaut

La création initialise notamment:

- `statut = ok`;
- `paiement_en_ligne = 1`;
- `eligibilite = tout`;
- `validation = auto`;
- `type_adherent = adherent`;
- `nombre_enfants = 0`;
- `document_justificatif = non`.
- `devise = intl/devise_defaut`.

### Ce qui est visible dans l'écran actuel

Le formulaire expose aujourd'hui les champs suivants, dans cet ordre logique :

1. nom de la cotisation ;
2. statut de la cotisation ;
3. éligibilité de la cotisation ;
4. type de cotisation ;
5. nombre d'enfants si le type cible une famille ou un enfant ;
6. document justificatif ;
7. validation de la cotisation ;
8. explication de la participation ;
9. devise, uniquement si les cotisations multidevises sont activées, initialisée avec `intl/devise_defaut` mais surchargeable pour cette catégorie ;
10. montant ;
11. modes de paiement autorisés.

Le champ `paiement_en_ligne` n'est pas affiché dans l'écran actuel, mais la création le positionne à `1` par défaut.

## Mode de paiement rattache

Le champ `mode_paiement` est alimenté par les configurations actives de `bank`, filtrées par la liste autorisée dans `mode_paiement_adhesion`.

Le stockage final est une liste serialisee / compacte qui conserve les identifiants techniques, pas les labels.

## Regles metier

### 1. Statut

Seules les categories avec `statut = ok` sont proposées dans les parcours normaux.

### 2. Eligibilite

La categorie peut etre reservee a:

- l'inscription;
- la reinscription;
- les deux.

### 3. Type d'adherent

Les valeurs visibles dans le formulaire couvrent:

- `adherent`;
- `famille`;
- `couple`;
- `individuel`;
- `conjoint`;
- `etudiant`;
- `enfant`;
- `entreprise`;
- `babysitting`;
- `partenaire`;
- `vip`.

### 4. Nombre d'enfants

Quand le type cible une logique familiale ou enfant, le champ `nombre_enfants` sert de filtre de compatibilite dans la sélection.

### 5. Justificatif

Si `document_justificatif = oui`, la cotisation exige deux fichiers justificatifs.

Règles appliquées par le FO, le BO et l'API commune :

- deux fichiers minimum, y compris si le champ d'upload est totalement absent de la requête ;
- formats PDF, JPEG et PNG uniquement ;
- taille maximale de 10 Mo par fichier ;
- refus des erreurs d'upload et contrôle du type MIME réel quand le fichier temporaire est disponible ;
- validation avant toute création de transaction Bank ou de compte de cotisation ;
- aucune contrainte documentaire lorsque la catégorie conserve `document_justificatif = non`.

La configuration reste désactivée par défaut afin de préserver les catégories historiques.

### 6. Validation

Les modes actuellement documentes dans le code sont:

- `auto`;
- `pre-paiement`;
- `post-paiement`.

Ces modes influencent:

- le statut de la cotisation;
- le déclenchement des notifications;
- l'activation de l'adhérent;
- la synchronisation avec la transaction bancaire.

### 7. Devise

La devise globale du site reste définie par le plugin Intl dans `intl/devise_defaut`.
La configuration Association `meta_cfg_cotisations_multidevises` est désactivée par défaut. Lorsqu'elle vaut `oui`, seules les catégories de cotisation peuvent surcharger la devise du site : le formulaire de catégorie propose les codes ISO connus par `intl_lister_devises()` et enregistre le choix dans `spip_asso_categories_adherents.devise`.

Quand l'option est désactivée, le sélecteur n'est pas affiché et la devise Intl du site est forcée lors de l'enregistrement et de la résolution des catégories existantes.

Les formulaires de création de cotisation FO et BO ne proposent pas une nouvelle devise indépendante. Ils affichent le montant et la devise de chaque catégorie, puis l'API commune transmet cette devise à la transaction Bank. Une catégorie historique dont la devise est vide reprend automatiquement la devise Intl du site.

## Traitement de cotisation

Le helper `api_traiter_cotisation()`:

- recupere la categorie choisie;
- calcule le montant final;
- cree ou met a jour la transaction bancaire;
- transmet à la transaction la devise de la catégorie ;
- cree ou met a jour le compte de cotisation;
- traite les justificatifs;
- appelle `changer_statut_cotisation()`.

### Calcul du montant

Le montant final est compose de:

- `cotisation`;
- un eventuel don additionnel;
- une taxe si elle est configuree.

Un montant final négatif est refusé. Un montant final à `0` est une cotisation gratuite valide.

Pour une cotisation gratuite :

- aucune transaction Bank n'est créée ;
- le mode `auto` produit directement le statut `ok` ;
- les modes `pre-paiement` et `post-paiement` conservent une validation humaine avec le statut `demande` ;
- l'activation publique est possible lorsque le statut final est `ok`.

### Creation

Lors d'une creation:

- une transaction est ouverte dans `bank` uniquement si le montant final est strictement positif;
- le compte cotisation est cree dans `spip_asso_comptes`;
- le statut initial est derive du mode de validation.

### Modification

Lors d'une modification:

- la transaction est mise a jour;
- le compte cotisation est synchronise;
- les notifications peuvent etre renvoyees;
- les pieces justificatives peuvent etre rattachees.

## Filtrage des categories

La fonction `preparer_liste_categories()` filtre les catégories selon:

- le contexte public ou prive;
- le statut de l'adhérent;
- le type d'adherent;
- le contexte d'inscription ou de reinscription;
- le nombre d'enfants pour les profils enfant.

Cette fonction peut renvoyer:

- une liste specifique si un type exact est trouve;
- sinon la liste de secours.

## Justificatifs

La fonction `identifier_categories_necessite_justificatif()` repere les categories qui demandent un document justificatif.

La fonction `traiter_upload_justificatif()`:

- enregistre les fichiers;
- les renomme avec un titre metier;
- les lie au compte de cotisation;
- évite les doublons de liaison.

Les parcours public et privé peuvent transmettre `_fichiers` sous deux formes :
un arbre nommé fourni par SPIP ou une liste déjà aplatie. L'API commune les
reconstruit en structure PHP multi-fichiers avant l'appel à `ajouter_documents()`;
la validation et la liaison au compte suivent ainsi le même chemin dans les deux
parcours.

La fonction `cotisation_verifier_documents_justificatifs()` centralise la validation avant traitement. Elle est appelée par l'API, et les formulaires FO/BO réutilisent le même résultat.

### Consultation et contrôle dans le BO

L'écran `editer_asso_cotisation` affiche un bloc **Contrôle des justificatifs** avant le formulaire :

- liste des fichiers avec nom, format et taille ;
- ouverture de chaque fichier dans un nouvel onglet ;
- état `À contrôler` ou `Contrôlé` ;
- action groupée `Documents contrôlés` ;
- retour possible à l'état `À revoir` ;
- interdiction de valider un dossier de moins de deux documents.

Le marqueur natif `vu` de `spip_documents_liens` porte cet état. Aucun nouveau schéma SQL n'est nécessaire. La validation documentaire ne modifie ni le paiement, ni le statut de cotisation, ni l'activation de l'adhérent.

La liste des cotisations affiche un badge uniquement lorsqu'un compte possède des documents. Le badge distingue les dossiers à contrôler des dossiers contrôlés.

## Notifications

Le fichier `inc/cotisations.php` centralise les notifications de cotisation.

Les principaux cas sont:

- attente de paiement;
- attente de validation;
- validation pre-paiement;
- validation post-paiement;
- activation apres encaissement;
- relance d'echeance.
- justificatifs à revoir.

Les emails administrateur de création, demande de validation et encaissement affichent conditionnellement :

- si des justificatifs sont requis ;
- le nombre de documents reçus ;
- le nombre de documents contrôlés ;
- un lien vers la cotisation à vérifier.

L'action BO `À revoir` programme un email à l'adhérent. Une validation positive ne produit aucun message supplémentaire. Comme le FO ne propose pas encore le remplacement de documents sur une cotisation existante, l'email demande de contacter l'association et ne présente pas de faux bouton de dépôt.

### Particularite importante

L'activation de l'adhérent est gérée en liaison avec:

- le statut de la cotisation;
- le statut de la transaction;
- le mode de validation;
- le contexte public ou prive.

## Lien avec la configuration globale

La page `configurer_association` alimente notamment:

- `mode_paiement_adhesion`;
- `comptes`;
- `destinations`;
- `pc_cotisations_creance`;
- `pc_cotisations_paiement`;
- `dc_cotisations`;
- `meta_cfg_taxe`;
- `meta_cfg_taxe_evenement`.

### Onglets relies

Les categories de cotisation dependent surtout de quatre onglets :

- `adhesion` pour la validite, les modalites, les notifications et les comptes secondaires ;
- `entreprise` pour les parcours de cotisation de type entreprise ;
- `mode_paiement` pour la liste des moyens de paiement effectivement proposables ;
- `comptabilite` pour les comptes et destinations utilises lors des ecritures.

### Impact de la periode de reinscription

Le choix d'une categorie de cotisation ne peut pas etre lu seul : il depend aussi de la fenetre de reinscription et de la duree de validite configurees dans l'onglet `adhesion`.

Point metier important :

- si la fenetre de renouvellement est trop courte, un adherent peut basculer trop vite en `echu` ;
- ce basculement ne change pas seulement la presentation du dossier, il peut aussi retirer des privileges lies a l'adhesion active ;
- selon le site, cela peut aussi retirer des acces d'administration, de redaction ou de responsable d'activite quand ces droits reposent sur une adhesion valide.

La configuration des categories doit donc etre pensee avec les delais reels de renouvellement laisses aux adherents, pas seulement avec la logique tarifaire.

## Points de vigilance

- Les valeurs sont souvent sérialisées et doivent rester compatibles avec les anciens formats.
- Le formulaire et le traitement ne doivent pas diverger sur le sens des statuts.
- Les notifications doivent rester synchronisees avec les templates `notifications/`.
- Une categorie desactivee peut encore exister en base et ne doit pas etre supprimee sans audit.

## A lire en plus

- [`modes_paiement.md`](./modes_paiement.md)
- [`tarifs-logique.md`](./tarifs-logique.md)
- [`configurer_association.md`](./configurer_association.md)
- [`notifications_cotisations.md`](./notifications_cotisations.md)
- [`tests_adhesions.md`](./tests_adhesions.md)
- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/bank.md`](./plugins/bank.md)
- [`plugins/cextras.md`](./plugins/cextras.md)
- [`plugins/saisies.md`](./plugins/saisies.md)
