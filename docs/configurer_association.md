# Page de configuration du plugin `blobul-ASSO_BO`

## Objectif

Documenter la page privée `configurer_association`, son découpage fonctionnel, son stockage, et ses effets sur le reste du plugin.

Cette page est la porte d entrée de la configuration métier du plugin Association. Elle pilote les préférences qui alimentent les formulaires, les autorisations, les notifications, la comptabilité et plusieurs comportements métier.

## Vue d ensemble

La page est exposée par le squelette privé :

- [`prive/squelettes/contenu/configurer_association.html`](../prive/squelettes/contenu/configurer_association.html)
- [`prive/squelettes/top/configurer_association.html`](../prive/squelettes/top/configurer_association.html)

Le contenu principal est chargé via :

```text
#FORMULAIRE_CONFIGURER_ASSOCIATION{#ENV{config,info}}
```

Le paramètre `config` détermine l onglet affiché.

## Contrôle d accès

La page est protégée par :

```text
#AUTORISER{configurer,_association}
```

Conséquence :

- seuls les utilisateurs autorisés à configurer l objet `_association` peuvent accéder à la page ;
- certaines sous-sections ne sont visibles qu aux webmestres ou si une fonctionnalité est activée ;
- la navigation privée peut masquer des entrées selon `association_metas`.

## Structure d interface

### Squelette supérieur

Le squelette `prive/squelettes/top/configurer_association.html` inclut le bandeau d onglets :

- `info`
- `adhesion`
- `entreprise`
- `evenement`
- `evenement_defaut`
- `mode_paiement`
- `segments`
- `affichage_prive`
- `affichage_public`
- `modules`
- `comptabilite` si la configuration le permet
- `maintenance_bdd` pour les webmestres
- `debug` pour les webmestres
- `notifications`

### Squelette contenu

Le squelette `prive/squelettes/contenu/configurer_association.html` :

1. vérifie l autorisation ;
2. affiche le titre de page ;
3. charge le formulaire en AJAX ;
4. transmet `config=info` par défaut si aucun onglet n est précisé.

## Stockage des réglages

La configuration est stockée dans la table métier `spip_association_metas`.

Le formulaire charge les valeurs depuis :

- `association_metas`

Le traitement persiste les valeurs via :

- `cvtconf_configurer_stocker('configurer_association', array('_meta_table' => 'association_metas'), $valeurs)`

Le plugin charge ensuite ces valeurs dans le global :

- `$GLOBALS['association_metas']`

## Cycle de vie de la configuration

1. La page charge les valeurs courantes depuis la config.
2. Le formulaire présente un sous-ensemble de champs selon l onglet.
3. Le validateur vérifie la cohérence des champs saisis.
4. Le traitement écrit les valeurs dans `association_metas`.
5. Les pipelines et helpers lisent ensuite `$GLOBALS['association_metas']` ou `lire_config()`.

## Onglets et finalité

### `info`

Paramètres d identité de l association :

- nom ;
- adresse ;
- email ;
- téléphone ;
- numéro d enregistrement ;
- informations complémentaires.

### `adhesion`

Paramètres liés aux cotisations et à l adhésion :

- validité annuelle ou scolaire ;
- dates scolaires de début / fin ;
- activation des comptes secondaires ;
- limite d âge enfants ;
- carte adhérent ;
- zones de l adhérent ;
- liste(s) de diffusion ;
- dons ;
- pages de modalités d inscription.

Point métier important :

- la période de réinscription doit laisser un délai suffisant pour que les adhérents renouvellent sereinement ;
- si ce délai est trop court, le passage en `echu` peut intervenir trop tôt ;
- ce basculement peut ensuite retirer les privilèges adhérent et, selon le site, certains accès de rédaction, de responsabilité ou d administration.

### `entreprise`

Variante de configuration réservée à la catégorie d adhérent entreprise quand elle existe :

- validité entreprise ;
- dates scolaires entreprise ;
- options spécifiques compte entreprise ;
- activation compte / inscription / liste de diffusion ;
- notifications e-mail dédiées ;
- destinataires trésorier entreprise.

### `evenement`

Paramètres métier des événements :

- inscription sur répétition ;
- modification d inscription ;
- désinscription ;
- message responsable ;
- type de quota ;
- délai d expiration ;
- configuration des accompagnants ;
- formulaire d information supplémentaire ;
- pages de modalités d inscription événement à accepter avant validation ;
- quota par adhérent ;
- fenêtres de quota.

### `evenement_defaut`

Paramètres de base pour l inscription événement :

- type d inscrits ;
- affichage de la liste des inscrits ;
- ouverture différée ;
- date limite ;
- validation ;
- accompagnants ;
- file d attente ;
- validation automatique ;
- conditions d inscription ;
- message par défaut.

Les pages uniques de modalités événement ne sont pas gérées ici : elles se trouvent dans l onglet `evenement`.

### `mode_paiement`

Paramètres de choix des moyens de paiement :

- paiements pour les adhésions ;
- paiements pour les participations ;
- paiements pour Formidable ;
- taxes appliquées aux adhésions et aux participations ;
- niveau d'autorisation pour encaisser une transaction.

### `segments`

Paramètre lié à la sélection de segment :

- `selection_segment`.

### `affichage_prive`

Paramètres de présentation de l interface privée :

- filtres annuaire ;
- champs et colonnes affichés ;
- statuts visibles ;
- listes dynamiques.

### `affichage_public`

Paramètres de présentation publique :

- filtres de l'annuaire public ;
- statuts visibles dans la liste publique des inscrits ;
- options d affichage du répertoire public.

### `modules`

Bloc des dépendances optionnelles et intégrations conditionnelles :

- GIS si le plugin est actif ;
- options FIAFE si la vérification réseau est valide.

### `comptabilite`

Visible si la configuration active les comptes ou si l utilisateur est webmestre.

Paramètres principaux :

- exercice comptable ;
- comptes de cotisations ;
- comptes d activités ;
- comptes de dons ;
- comptes de ventes ;
- frais d envoi ;
- prêts ;
- destinations comptables.

### `maintenance_bdd`

Visible pour les webmestres.

Paramètres de maintenance :

- activation de la maintenance ;
- mode dry-run ;
- seuils d inactivité ;
- seuils d inscriptions en attente ;
- seuils de transactions non encaissées ;
- taille de lot ;
- actions de nettoyage ;
- exécution de test.

### `debug`

Visible pour les webmestres.

Paramètres de journalisation :

- catégories de logs ;
- sauvegarde des préférences de debug.

### `notifications`

Raccourci vers l interface de gestion des notifications du plugin.

## Inventaire detaille

Pour la liste exhaustive des champs, de leurs types et de leurs cles de stockage, consulter:

- [`docs/configurer_association_inventaire.md`](./configurer_association_inventaire.md)

## Interdependances par onglet

Chaque onglet de `configurer_association` alimente ensuite d'autres ecrans ou traitements du plugin.

### `adhesion`

Regle principalement :

- le comportement des cotisations ;
- les comptes secondaires ;
- les zones adherent ;
- les notifications d'echeance et de validation ;
- les modalites a accepter lors d'une adhesion.

Il faut lire cet onglet avec une contrainte fonctionnelle claire :

- la validite et les delais de reinscription ne servent pas seulement a afficher une date ;
- ils pilotent aussi le passage en `echu`, les relances, la reapparition des parcours de reinscription et la conservation ou non des privileges adherent.

Docs a relire avec cet onglet :

- [`docs/categories_cotisation.md`](./categories_cotisation.md)
- [`docs/notifications_cotisations.md`](./notifications_cotisations.md)
- [`docs/notifications_cotisation_adherent.md`](./notifications_cotisation_adherent.md)
- [`docs/notifications_cotisation_admin.md`](./notifications_cotisation_admin.md)

### `entreprise`

Regle principalement :

- la variante entreprise des cotisations ;
- l'inscription evenement des comptes entreprise ;
- les relances d'echeance propres aux entreprises ;
- les listes de diffusion dediees ;
- les notifications de creation de cotisation entreprise.

Docs a relire avec cet onglet :

- [`docs/categories_cotisation.md`](./categories_cotisation.md)
- [`docs/evenements_formulaires_inscription.md`](./evenements_formulaires_inscription.md)
- [`docs/notifications_cotisations.md`](./notifications_cotisations.md)

### `evenement`

Regle principalement :

- les regles metier d'inscription ;
- les quotas ;
- les delais d'expiration ;
- les contacts et notifications evenement ;
- les modalites evenement.

Docs a relire avec cet onglet :

- [`docs/parametrage_evenements.md`](./parametrage_evenements.md)
- [`docs/evenements_formulaires_inscription.md`](./evenements_formulaires_inscription.md)
- [`docs/categories_participation_financiere.md`](./categories_participation_financiere.md)
- [`docs/notifications_evenements.md`](./notifications_evenements.md)

### `evenement_defaut`

Regle principalement :

- les valeurs par defaut reprises a la creation d'un evenement ;
- l'ouverture des inscriptions ;
- la validation, la file d'attente et les accompagnants ;
- la condition d'inscription par defaut.

Docs a relire avec cet onglet :

- [`docs/parametrage_evenements.md`](./parametrage_evenements.md)
- [`docs/evenements_formulaires_inscription.md`](./evenements_formulaires_inscription.md)

### `mode_paiement`

Regle principalement :

- les moyens de paiement disponibles pour les cotisations ;
- les moyens de paiement disponibles pour les participations evenement ;
- les moyens de paiement disponibles dans Formidable.

Docs a relire avec cet onglet :

- [`docs/modes_paiement.md`](./modes_paiement.md)
- [`docs/categories_cotisation.md`](./categories_cotisation.md)
- [`docs/categories_participation_financiere.md`](./categories_participation_financiere.md)

### `segments`

Regle principalement :

- les champs exploitables dans la recherche avancee ;
- la segmentation reutilisee dans les listes et ciblages.

Docs a relire avec cet onglet :

- [`docs/recherche_adherents_segmentation.md`](./recherche_adherents_segmentation.md)
- [`docs/emails_collectifs.md`](./emails_collectifs.md)

### `affichage_prive`

Regle principalement :

- les filtres visibles dans le tableau adherents ;
- les colonnes visibles dans l'interface privee.

Docs a relire avec cet onglet :

- [`docs/recherche_adherents_segmentation.md`](./recherche_adherents_segmentation.md)

### `affichage_public`

Regle principalement :

- les filtres de l'annuaire public ;
- les statuts visibles dans les listes publiques d'inscrits.

Docs a relire avec cet onglet :

- [`docs/evenements_formulaires_inscription.md`](./evenements_formulaires_inscription.md)
- [`docs/gis_geolocalisation.md`](./gis_geolocalisation.md)

### `modules`

Regle principalement :

- les comportements conditionnels lies a GIS ;
- les options FIAFE actives sur certains sites.

Docs a relire avec cet onglet :

- [`docs/gis_geolocalisation.md`](./gis_geolocalisation.md)
- [`docs/parametrage_evenements.md`](./parametrage_evenements.md)

### `comptabilite`

Regle principalement :

- le plan comptable ;
- les destinations ;
- l'imputation des cotisations, activites, dons, ventes et prets.

Docs a relire avec cet onglet :

- [`docs/plan_comptable_destinations.md`](./plan_comptable_destinations.md)
- [`docs/migration_synchronisation_comptabilite.md`](./migration_synchronisation_comptabilite.md)
- [`docs/comptabilite_evenements.md`](./comptabilite_evenements.md)
- [`docs/dons.md`](./dons.md)
- [`docs/ventes.md`](./ventes.md)
- [`docs/prets_ressources.md`](./prets_ressources.md)

### `maintenance_bdd` et `debug`

Reglent principalement :

- les purges et anonymisations automatiques ;
- le dry-run manuel ;
- les categories de journalisation activees.

Docs a relire avec ces onglets :

- [`docs/maintenance_cron.md`](./maintenance_cron.md)
- [`docs/journalisation_debug.md`](./journalisation_debug.md)

## Validation

La fonction `formulaires_configurer_association_verifier_dist()` effectue notamment :

- la validation du format `JJ/MM` pour les dates d exercice ;
- la détection de références comptables dupliquées ;
- la validation des adresses e-mail saisies dans plusieurs champs ;
- la validation de certains champs numériques de maintenance.

## Traitement

La fonction `formulaires_configurer_association_traiter_dist()` :

- récupère la liste des saisies à traiter ;
- stocke les valeurs dans la table de métadonnées ;
- affiche un message de confirmation ;
- sauvegarde les préférences de debug pour les webmestres ;
- peut lancer un `dry-run` de maintenance BDD et générer un rapport JSON.

## Dépendances et consommateurs

Cette configuration est lue à plusieurs endroits du plugin, notamment :

- [`association_options.php`](../association_options.php)
- [`association_pipelines.php`](../association_pipelines.php)
- [`association_autoriser.php`](../association_autoriser.php)
- [`base/association_champs_extras.php`](../base/association_champs_extras.php)
- [`inc/cotisations.php`](../inc/cotisations.php)
- [`inc/comptes.php`](../inc/comptes.php)
- [`inc/destinations.php`](../inc/destinations.php)
- [`inc/fonctions/gestion_places.php`](../inc/fonctions/gestion_places.php)
- [`inc/fonctions/facteur_envoyer_mail_activites.php`](../inc/fonctions/facteur_envoyer_mail_activites.php)
- [`inc/fonctions/gis_auteur.php`](../inc/fonctions/gis_auteur.php)

## Points de vigilance

- Le formulaire manipule beaucoup de métadonnées : il faut garder la cohérence entre les clés de saisie, les clés de langue et les consommateurs PHP.
- Certaines sections sont conditionnelles et ne doivent pas être interprétées comme absentes si elles sont masquées par la configuration.
- La maintenance BDD peut déclencher des effets importants ; la lecture de la documentation d exécution est recommandée avant tout test.
- Les champs liés à la comptabilité ne doivent pas être renommés sans audit de tous les consommateurs.

## Fichiers à relire avec cette page

- [`docs/configurer_association_inventaire.md`](./configurer_association_inventaire.md)
- [`docs/autorisations.md`](./autorisations.md)
- [`docs/notifications.md`](./notifications.md)
- [`docs/parametrage_evenements.md`](./parametrage_evenements.md)
- [`docs/categories_cotisation.md`](./categories_cotisation.md)
- [`docs/modes_paiement.md`](./modes_paiement.md)
- [`docs/categories_participation_financiere.md`](./categories_participation_financiere.md)
- [`docs/notifications_cotisations.md`](./notifications_cotisations.md)
- [`docs/notifications_cotisation_adherent.md`](./notifications_cotisation_adherent.md)
- [`docs/notifications_cotisation_admin.md`](./notifications_cotisation_admin.md)
- [`docs/comptabilite_evenements.md`](./comptabilite_evenements.md)
- [`docs/tarifs-logique.md`](./tarifs-logique.md)
