# Parametrage des evenements

## But

Documenter le parametrage technique des evenements du plugin:

- ce qui est configure globalement dans `configurer_association`;
- ce qui est configure evenement par evenement;
- ce qui influence l'affichage des formulaires d'inscription;
- ce qui influence les notifications, les places, les accompagnants et la comptabilite.

## Perimetre fonctionnel

Le parametrage evenement couvre:

- les regles de saisie et d'inscription;
- les accompagnants et la structure familiale;
- les delais et limites d'inscription;
- la validation des inscriptions;
- les notifications de responsable et de paiement;
- les categories de participation associees a chaque evenement;
- les modes de paiement acceptes;
- les options FIAFE quand elles sont actives.

## Fichiers de reference

- [`formulaires/configurer_association.php`](../formulaires/configurer_association.php)
- [`formulaires/editer_evenement.php`](../formulaires/editer_evenement.php)
- [`formulaires/inc/inscription_evenement.php`](../formulaires/inc/inscription_evenement.php)
- [`formulaires/inc/inscription_evenement_saisies.php`](../formulaires/inc/inscription_evenement_saisies.php)
- [`base/association.php`](../base/association.php)

## Niveau 1: configuration globale des evenements

La page `configurer_association` expose plusieurs blocs qui pilotent les evenements.

Les reglages de cette page ne restent pas isoles : ils alimentent ensuite le formulaire d'edition d'evenement, les formulaires d'inscription et les notifications associees.

### Bloc `evenement`

| Cle | Rôle |
|---|---|
| `meta_cfg_event_inscription_sur_repetition` | autorise l'inscription sur les occurrences repetitives |
| `meta_cfg_event_modification_inscription` | autorise ou non la modification d'inscription |
| `meta_cfg_event_desinscription_inscription` | definit si la desinscription est souple ou stricte |
| `meta_cfg_event_message_responsable` | active l'envoi d'un message au responsable |
| `meta_cfg_event_type_quota` | type de quota applique |
| `meta_cfg_event_delai_expiration` | delai apres lequel l'inscription expire |
| `meta_cfg_event_config_accompagnants` | regle la presence des accompagnants |
| `meta_cfg_event_form_info_supp` | contient le formulaire d'information supplementaire |
| `pages_modalite_evenement` | pages uniques de modalites a accepter avant inscription |
| `meta_cfg_event_quota_inscription_adherent` | active le quota par adherent |
| `nb_inscription_quota_adherent` | nombre max d'inscriptions sur la periode |
| `nb_jour_quota_adherent` | fenetre temporelle du quota |

### Bloc `evenement_defaut`

Ces parametres servent de base pour les formulaires publics et prives.

| Cle | Rôle |
|---|---|
| `meta_cfg_event_inscription` | active l'inscription evenement |
| `meta_cfg_event_type_inscrits_evenement` | type d'inscrits par defaut |
| `meta_cfg_event_afficher_liste_inscrits` | affichage de la liste des inscrits |
| `meta_cfg_event_ouverture_differe` | ouverture differee |
| `meta_cfg_event_inscription_deadline` | date limite d'inscription |
| `meta_cfg_event_validation` | validation requise ou non |
| `meta_cfg_event_accompagnants` | gestion des accompagnants |
| `meta_cfg_event_limite_nb_accompagnants` | limite d'accompagnants |
| `meta_cfg_event_file_attente` | file d'attente |
| `meta_cfg_event_validation_auto` | validation automatique |
| `meta_cfg_event_limite_places_file_attente` | limite de la file d'attente |
| `meta_cfg_event_condition_inscription` | condition d'inscription |
| `message_condition_inscription_defaut` | message par defaut |

Lors de la creation d'un evenement, `inc/evenement_defauts.php` traduit ces
parametres vers les champs du formulaire `evenement_edit`. Les deux chemins de
chargement historiques utilisent ce helper commun. Une valeur globale
`meta_cfg_event_accompagnants = non` doit donc charger puis persister
`accompagnants = non` si l'utilisateur ne modifie pas ce choix.

Les pages uniques de modalités événement sont configurées dans le bloc `evenement`, pas dans ce bloc de valeurs par défaut.

### Bloc `config_inscription_contact`

| Cle | Rôle |
|---|---|
| `meta_cfg_telephone_responsable` | telephone du responsable |
| `meta_cfg_evenement_formulaire_contact` | point de contact du formulaire evenement |
| `meta_cfg_event_email_defaut` | adresse mail par defaut |

### Bloc `config_notification_inscription_evenement`

| Cle | Rôle |
|---|---|
| `config_envoi_email_notif_defaut` | destinataires par defaut des notifications |
| `meta_cfg_envoi_recu_paiement_participation` | recu de paiement participation |
| `config_envoi_recu_participation_cc` | adresses en copie pour les recus |

### Bloc `config_taxes_formulaire`

Ce bloc influence les evenements, mais il est configure dans l'onglet `mode_paiement`.

| Cle | Rôle |
|---|---|
| `meta_cfg_taxe` | taxe appliquee aux adhesions |
| `meta_cfg_taxe_evenement` | taxe appliquee aux participations evenement |

### Bloc `config_autorisations_fieldset`

Ce bloc influence le traitement des paiements evenement, mais il est configure dans l'onglet `mode_paiement`.

| Cle | Rôle |
|---|---|
| `meta_cfg_autorisation_encaisser_transaction` | niveau d'autorisation pour encaisser une transaction |

## Niveau 2: parametres evenement par evenement

Le formulaire `formulaires_editer_evenement.php` derive du formulaire natif SPIP, mais conserve plusieurs regles importantes:

- `id_parent` est toujours l'article source de l'evenement;
- la date et l'heure sont normalisees au chargement et au traitement;
- les repetitions sont gerees en bloc;
- la creation peut forcer la publication de l'evenement si l'article parent est publie et si la synchro le permet;
- la date de creation est renseignee explicitement a la creation.

### Champs principaux du formulaire

| Champ | Rôle |
|---|---|
| `titre` | titre de l'evenement |
| `date_debut` | debut de l'evenement |
| `date_fin` | fin de l'evenement |
| `horaire` | evenement horaire ou journee |
| `timezone_affiche` | fuseau horaire d'affichage |
| `places` | nombre de places |
| `inscription` | activation de l'inscription |
| `fermeture_inscription` | regle relative ou mode `dt` pour une fermeture exacte |
| `fermeture_inscription_date` | date et heure exactes lorsque le mode `dt` est choisi |
| `repetitions` | liste des repetitions |
| `id_parent` / `parents_id` | article parent |

### Validation specifique

La validation controle notamment:

- la coherence des dates;
- l'existence et l'autorisation de l'article parent;
- la validite du fuseau horaire;
- l'impact sur les repetitions liees.

La fermeture exacte est calculee par
`inc/evenement_fermeture.php` via
`association_evenement_calculer_date_fermeture()`, la meme autorite pour les
parcours FO connecte, FO non connecte et BO. Les anciens delais relatifs restent
compatibles. Le champ exact est obligatoire dans l'interface lorsque le mode
`dt` est selectionne.

## Niveau 3: configuration des inscriptions evenement

Le formulaire public et prive d'inscription construit sa saisie a partir de plusieurs helpers.

### Rassemblement des donnees

| Fonction | Rôle |
|---|---|
| `preparer_info_auteur()` | calcule le contexte auteur + validite + famille |
| `champs_saisie_nb_inscrits()` | construit la saisie du nombre d'inscrits |
| `champs_saisies_selection_membres_famille()` | construit le choix des membres de la famille |
| `champs_saisies_inscrits()` | construit les inscrits en mode "liste" |
| `champs_saisies_famille()` | construit les inscrits en mode famille |
| `champs_saisies_tarifs()` | ajoute les categories de participation par inscrit |
| `champs_saisies_info_supplementaire()` | ajoute les informations supplementaires |

### Choix du mode de saisie

Deux grands modes existent:

- `nb_inscrits` quand on renseigne une liste d'inscrits;
- `famille` quand on passe par la structure famille d'un adherent.

### Règle de base sur les participants

- inscrit 1: profil principal;
- inscrit 2: conjoint ou second participant;
- inscrit 3 et suivants: enfants;
- invitations et cas FIAFE peuvent ajouter des profils specifiques;
- le mode famille utilise les membres nommes, pas une numerotation pure.

## Categories de participation dans les inscriptions

Les categories de participation sont filtrees par:

- le type d'inscrit;
- le rang du participant;
- le statut de l'auteur connecte;
- les tarifs de groupe;
- les categories actives uniquement.

Ces points sont documentes dans:

- [`categories_participation_financiere.md`](./categories_participation_financiere.md)
- [`tarifs-logique.md`](./tarifs-logique.md)

## Points de vigilance

- Les valeurs de configuration sont largement serialisees en base.
- Les blocs de configuration ne doivent pas etre renommer sans audit des consommateurs.
- Les inscriptions payantes et gratuites ne suivent pas exactement le meme chemin de calcul.
- Les formulaires FO et BO partagent plusieurs helpers, donc une modification locale peut avoir des effets larges.

## A lire en plus

- [`configurer_association.md`](./configurer_association.md)
- [`configurer_association_inventaire.md`](./configurer_association_inventaire.md)
- [`evenements_formulaires_inscription.md`](./evenements_formulaires_inscription.md)
- [`notifications.md`](./notifications.md)
- [`comptabilite_evenements.md`](./comptabilite_evenements.md)
- [`categories_participation_financiere.md`](./categories_participation_financiere.md)
