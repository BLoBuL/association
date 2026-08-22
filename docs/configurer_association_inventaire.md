# Inventaire de `configurer_association`

## But

Lister de facon exhaustive les onglets, fieldsets et champs de la page de configuration du plugin.

Cette page correspond au formulaire:

```text
formulaires_configurer_association_dist()
```

Les valeurs sont stockees dans `association_metas` et relues ensuite par le reste du plugin via `$GLOBALS['association_metas']` ou `lire_config()`.

## Regle de lecture

- Un onglet = une entree de navigation dans `prive/squelettes/navigation/configurer_association.html`.
- Un fieldset = un groupe fonctionnel dans `formulaires/configurer_association.php`.
- Une cle de champ = le nom exact du champ stocke dans `association_metas`.

## Onglets disponibles

| Onglet | Visible quand | Usage |
|---|---|---|
| `info` | toujours | identite de l'association |
| `adhesion` | toujours | cotisations, adhesion, notifications de base |
| `entreprise` | si la categorie entreprise existe | variante entreprise |
| `evenement` | toujours | regles generales evenement |
| `evenement_defaut` | toujours | parametres par defaut des inscriptions evenement |
| `mode_paiement` | toujours | choix des moyens de paiement |
| `segments` | toujours | selection des champs de filtre |
| `affichage_prive` | toujours | filtres et colonnes BO |
| `affichage_public` | toujours | annuaire public et statuts visibles |
| `modules` | toujours | activation de modules et options associees |
| `comptabilite` | si comptes actifs ou webmestre | plan comptable et destinations |
| `maintenance_bdd` | webmestre | nettoyage / maintenance |
| `debug` | webmestre | categories de logs |
| `notifications` | raccourci de navigation | acces aux sous-docs notifications |

## `info`

| Champ | Type | Remarque |
|---|---|---|
| `nom` | input | nom de l'association |
| `rue` | input | adresse |
| `cp` | input | code postal |
| `ville` | input | ville |
| `pays` | input | pays |
| `email` | input | adresse de contact principale, validee comme liste d'emails |
| `telephone` | input | telephone |
| `num_enregistrement` | input | numero d'enregistrement |
| `info_complementaires` | textarea | complement libre |

### Ce que regle cet onglet

Cet onglet regroupe l'identite de reference de l'association :

- nom et coordonnees ;
- email principal ;
- numero d'enregistrement ;
- informations libres reutilisables dans certains rendus ou notifications.

## `adhesion`

### `config_cotisation_fieldset`

| Champ | Type | Remarque |
|---|---|---|
| `validite` | radio | `scolaire` ou `annee` |
| `date_scolaire_suivante` | input | actif surtout en mode scolaire |
| `date_scolaire_nouvelle` | input | actif surtout en mode scolaire |

### `config_compte_secondaire_fieldset`

| Champ | Type | Remarque |
|---|---|---|
| `config_compte_secondaire` | radio | active les comptes secondaires |
| `config_compte_secondaire_activation` | radio | visible seulement si le compte secondaire est active |

### `config_enfants_fieldset`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_age_limit_enfants` | input | limite d'age pour les enfants |

### `config_privileges_fieldset`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_carte_adherent` | radio | carte adherent |
| `zone_adherent` | checkbox | zones associees a l'adherent |
| `liste_diffusion` | checkbox | liste de diffusion |

### `config_donation_fieldset`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_donation` | radio | active les dons |
| `meta_cfg_donation_defaut` | input | visible si les dons sont actives |

### `config_modalites_inscription_fieldset`

| Champ | Type | Remarque |
|---|---|---|
| `pages_modalite_inscription` | checkbox | pages uniques de modalites d'inscription a accepter |

### Ce qui est visible dans l'écran actuel

Le bloc `Configuration des adhésions` affiche actuellement une sélection de pages uniques à accepter lors d'une adhésion ou réinscription.

Les pages listées sont des articles publiés sans rubrique, par exemple :

- `Profil adhérent - A jour`;
- `Profil adhérent - Prospect, Echu, Désactivé`;
- `Formulaire de connexion`;
- `Profil adhérent - Responsable d'activité`;
- `Profil adhérent - Administrateur`;
- `Annuaire des membres`;
- `Carte GIS`;
- `Condition générale de vente`.

### `notification_recu_paiement`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_envoi_recu_paiement_adhesion` | radio | envoi du recu de paiement adhesion |
| `config_envoi_recu_adhesion_cc` | input | adresses en copie |

### `notification_adhesion`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_envoi_validation_paiement_adhesion` | radio | notification de validation |
| `notification_adherent_echu` | radio | notification jour J |
| `notification_echeance_cotisation` | checkbox | relances 60 / 30 / 15 / 7 jours |
| `config_destinataires_creation_cotisation_tresorier` | input | destinataires tresorerie |
| `config_destinataires_creation_cotisation_adh` | input | champ legacy verifie en validation, pas expose dans l'UI actuelle |

### Ce qui est visible dans l'écran actuel

Le bloc de notifications affiche aussi :

- l'envoi d'un reçu d'encaissement ;
- l'adresse de copie carbone pour les reçus ;
- la notification d'encaissement pour les réinscrits ;
- le passage en cotisation échue ;
- la prévention avant échéance ;
- les destinataires trésorerie.

## `entreprise`

### `config_cotisation_entreprise_fieldset`

| Champ | Type | Remarque |
|---|---|---|
| `validite_entreprise` | radio | validite specifique entreprise |
| `date_scolaire_suivante_entreprise` | input | calendrier entreprise |
| `date_scolaire_nouvelle_entreprise` | input | calendrier entreprise |

### `config_adherent_entreprise`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_cotisation_compte_entreprise` | selection | autorise le compte entreprise a creer sa cotisation |
| `meta_cfg_event_inscription_compte_entreprise` | selection | autorise l'inscription evenement pour entreprise |
| `meta_cfg_liste_diffusion_compte_entreprise` | checkbox | listes de diffusion entreprises |
| `notification_echeance_notifier_echu_entreprise` | radio | echeance entreprise |
| `notification_echeance_cotisation_entreprise` | checkbox | relances avant echeance entreprise |

### `fieldset_notification_creation_cotisation_entreprise`

| Champ | Type | Remarque |
|---|---|---|
| `config_destinataires_creation_cotisation_tresorier_entreprise` | input | destinataires tresorerie entreprise |

### Ce qui est visible dans l'ecran actuel

L'onglet `entreprise` affiche actuellement :

- le choix du type d'annee pour les comptes entreprise ;
- l'autorisation ou non de creer une cotisation depuis le profil entreprise ;
- l'autorisation ou non de s'inscrire aux evenements ;
- les listes de diffusion a alimenter automatiquement ;
- les relances d'echeance specifiques aux entreprises ;
- les destinataires BCC des notifications de creation de cotisation entreprise.

## `evenement`

### `fieldset_specifique_evenement`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_event_inscription_sur_repetition` | selection | inscription sur repetition |
| `meta_cfg_event_modification_inscription` | selection | `oui` / `non` |
| `meta_cfg_event_desinscription_inscription` | selection | `souple` / `strict` |
| `meta_cfg_event_message_responsable` | selection | message au responsable |
| `meta_cfg_event_type_quota` | selection | type de quota |
| `meta_cfg_event_delai_expiration` | selection | delai d'expiration |
| `meta_cfg_event_config_accompagnants` | selection | regle sur les accompagnants |
| `meta_cfg_event_form_info_supp` | selection | informations supplementaires |
| `meta_cfg_event_quota_inscription_adherent` | selection | quota par adherent |
| `nb_inscription_quota_adherent` | input | nombre max |
| `nb_jour_quota_adherent` | input | fenetre en jours |

### `config_notification_inscription_evenement`

| Champ | Type | Remarque |
|---|---|---|
| `config_envoi_email_notif_defaut` | input | liste d'adresses pour les notifications par defaut |
| `meta_cfg_envoi_recu_paiement_participation` | radio | recu de paiement participation |
| `config_envoi_recu_participation_cc` | input | adresses en copie participation |

### `config_inscription_contact`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_telephone_responsable` | selection | telephone du responsable |
| `meta_cfg_evenement_formulaire_contact` | selection | contact du formulaire evenement |
| `meta_cfg_event_email_defaut` | input | adresse mail par defaut pour les evenements |

### `config_modalites_evenement_fieldset`

| Champ | Type | Remarque |
|---|---|---|
| `pages_modalite_evenement` | checkbox | pages uniques de modalites a accepter avant inscription evenement |

## `evenement_defaut`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_event_inscription` | radio | autorise l'inscription evenement |
| `meta_cfg_event_type_inscrits_evenement` | selection | type d'inscrits |
| `meta_cfg_event_afficher_liste_inscrits` | selection | affichage de la liste |
| `meta_cfg_event_ouverture_differe` | selection | ouverture differee |
| `meta_cfg_event_inscription_deadline` | selection | date limite |
| `meta_cfg_event_validation` | selection | validation requise |
| `meta_cfg_event_accompagnants` | selection | accompagnants |
| `meta_cfg_event_limite_nb_accompagnants` | input | limite accompagnants |
| `meta_cfg_event_file_attente` | selection | file d'attente |
| `meta_cfg_event_validation_auto` | selection | validation automatique |
| `meta_cfg_event_limite_places_file_attente` | input | limite file d'attente |
| `meta_cfg_event_condition_inscription` | selection | condition d'inscription |
| `message_condition_inscription_defaut` | textarea | message par defaut |

Le bloc des modalités d'inscription événement est désormais documenté dans l'onglet `evenement`.

## `mode_paiement`

| Champ | Type | Remarque |
|---|---|---|
| `mode_paiement_adhesion` | checkbox | moyens de paiement adhesion |
| `mode_paiement_participation` | checkbox | moyens de paiement participation |
| `mode_paiement_formidable` | checkbox | moyens de paiement Formidable |

### `config_taxes_formulaire`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_taxe` | input | taxe d'adhesion |
| `meta_cfg_taxe_evenement` | input | taxe participation evenement |

### `config_autorisations_fieldset`

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_autorisation_encaisser_transaction` | selection | niveau d'autorisation pour encaisser une transaction |

### Ce que regle cet onglet

Cet onglet centralise :

- les moyens de paiement activables par contexte ;
- les taxes globales ;
- la regle d'autorisation pour encaisser une transaction.

## `segments`

| Champ | Type | Remarque |
|---|---|---|
| `selection_segment` | selection multiple | liste des champs de filtre |

### Ce que regle cet onglet

Cet onglet choisit quels champs pourront etre reutilises dans la segmentation et certaines recherches avancees.

## `affichage_public`

### `config_annuaire_membre`

| Champ | Type | Remarque |
|---|---|---|
| `config_filtres_annuaire` | checkbox | filtres publics annuaire |

### `config_statuts_liste_publique_inscrits_fieldset`

| Champ | Type | Remarque |
|---|---|---|
| `config_statuts_liste_publique_inscrits` | checkbox | statuts visibles publiquement |
| `info_statut_ok_fixe` | explication | statut confirme toujours affiche |

### Ce que regle cet onglet

Cet onglet pilote surtout :

- les filtres de l'annuaire public ;
- les statuts visibles dans la liste publique des inscrits d'un evenement.

## `affichage_prive`

### `config_filtres_tableau`

| Champ | Type | Remarque |
|---|---|---|
| `config_champs_filtres_adherents` | selection multiple | champs de filtres annuaire BO |

### `config_colonnes_tableau`

| Champ | Type | Remarque |
|---|---|---|
| `config_champs_colonnes_adherents` | selection multiple | colonnes du tableau BO |

### Ce que regle cet onglet

Cet onglet pilote surtout :

- les champs disponibles comme filtres dans le tableau adherents ;
- les colonnes visibles dans l'interface privee.

## `modules`

Ce bloc regroupe les options conditionnelles liées à des plugins externes.

### `config_gis_fieldset`

Visible seulement si le plugin GIS est actif.

| Champ | Type | Remarque |
|---|---|---|
| `notification_gis_config_email` | input | destinataires GIS |
| `notification_gis_config_action` | checkbox | actions suivies |

### `config_evenement_fiafe`

Visible seulement si la vérification FIAFE passe.

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_event_reseau_fiafe` | selection | activation du réseau FIAFE |
| `meta_cfg_event_profil_reseau_fiafe` | selection | profil réseau FIAFE |

### Ce que regle cet onglet

Cet onglet regroupe les reglages conditionnels affiches seulement si certains plugins ou contextes sont actifs :

- alertes GIS ;
- options FIAFE reseau.

## `comptabilite`

### `fieldset_exercice_comptable`

| Champ | Type | Remarque |
|---|---|---|
| `exercice_comptable_debut` | input | format attendu `JJ/MM` |

### `fieldset_cotisations`

| Champ | Type | Remarque |
|---|---|---|
| `pc_cotisations_creance` | selection | compte 416 par defaut |
| `pc_cotisations_paiement` | selection | compte 7010 par defaut |
| `dc_cotisations` | selection | visible si destinations actives |

### `fieldset_activites`

| Champ | Type | Remarque |
|---|---|---|
| `pc_activites_creance` | selection | compte 417 par defaut |
| `pc_activites_paiement` | selection | compte 7011 par defaut |
| `pc_activites_frais` | selection | compte 601001 par defaut |
| `dc_activites` | selection | visible si destinations actives |

### `fieldset_dons`

| Champ | Type | Remarque |
|---|---|---|
| `dons` | case | active les dons |
| `pc_dons` | selection | compte dons |
| `dc_dons` | selection | visible si destinations actives |

### `fieldset_ventes`

| Champ | Type | Remarque |
|---|---|---|
| `ventes` | case | active les ventes |
| `pc_ventes` | selection | compte ventes |
| `pc_frais_envoi` | selection | compte frais d'envoi |
| `dc_ventes` | selection | visible si destinations actives |

### `fieldset_prets`

| Champ | Type | Remarque |
|---|---|---|
| `prets` | case | active les prets |
| `pc_prets` | selection | compte prets |

### Ce que regle cet onglet

Cet onglet fixe les comptes, destinations et options comptables utilises par les briques metier du plugin.

## `maintenance_bdd`

Visible uniquement au webmestre.

### Parametres

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_maintenance_bdd_enable` | radio | active la maintenance |
| `meta_cfg_maintenance_dry_run` | radio | force un dry-run |
| `meta_cfg_maintenance_jours_inactivite` | input | seuil en jours |
| `meta_cfg_maintenance_jours_inscriptions_attente` | input | seuil inscriptions en attente |
| `meta_cfg_maintenance_mois_non_encaisse` | input | seuil de transactions non encaissees |
| `meta_cfg_maintenance_lot` | input | taille de lot |

### Actions de nettoyage

| Champ | Type | Remarque |
|---|---|---|
| `meta_cfg_maintenance_supprimer_auteurs_sans_paiements` | radio | suppression auteurs sans paiements |
| `meta_cfg_maintenance_anonymiser_auteurs_avec_paiements` | radio | anonymisation auteurs avec paiements |
| `meta_cfg_maintenance_supprimer_inscriptions_non_validees` | radio | suppression inscriptions non validees |
| `meta_cfg_maintenance_anonymiser_inscriptions_inactifs` | radio | anonymisation inscriptions inactives |
| `meta_cfg_maintenance_supprimer_cotisations_orphelines` | radio | suppression cotisations orphelines |
| `meta_cfg_maintenance_supprimer_cotisations_non_encaissees` | radio | suppression cotisations non encaissees |
| `meta_cfg_maintenance_supprimer_transactions_orphelines` | radio | suppression transactions orphelines |
| `meta_cfg_maintenance_supprimer_participations_orphelines` | radio | suppression participations orphelines |
| `meta_cfg_maintenance_supprimer_participations_obsoletes` | radio | suppression participations obsoletes |
| `meta_cfg_maintenance_supprimer_urls_mailsubscriber` | radio | suppression URLs mailsubscriber |
| `meta_cfg_maintenance_supprimer_urls_obsoletes` | radio | suppression URLs obsoletes |
| `meta_cfg_maintenance_supprimer_mailsubscribers_orphelines` | radio | suppression mailsubscribers orphelins |

### Action manuelle

| Champ | Type | Remarque |
|---|---|---|
| `exec_maintenance_dry_run` | submit | lance le dry-run immediate |

### Ce que regle cet onglet

Cet onglet pilote les seuils, actions et tests de maintenance automatique de la base.

## `debug`

Visible uniquement au webmestre.

| Champ | Type | Remarque |
|---|---|---|
| `debug_log_cat_*` | case | une case par categorie de log retournee par `association_log_categories_defaut()` |

### Ce que regle cet onglet

Cet onglet permet d'activer ou non les categories de debug journalisees par le plugin.

## Validation importante

La fonction `formulaires_configurer_association_verifier_dist()` verifie notamment:

- le format `JJ/MM` de `exercice_comptable_debut`;
- les references comptables dupliquees;
- les adresses e-mail de plusieurs champs;
- quelques valeurs numeriques de maintenance.

## Points de vigilance

- Le formulaire contient encore quelques cles legacy dans la validation.
- Certaines sections ne sont pas affichees selon le contexte ou le profil.
- Les cles de configuration sont reutilisees ailleurs: un renommage doit etre realise avec beaucoup de prudence.

## Docs a lire en plus

- [`docs/configurer_association.md`](./configurer_association.md)
- [`docs/autorisations.md`](./autorisations.md)
- [`docs/notifications.md`](./notifications.md)
- [`docs/comptabilite_evenements.md`](./comptabilite_evenements.md)
- [`docs/tarifs-logique.md`](./tarifs-logique.md)
