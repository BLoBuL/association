# Modes de paiement

## But

Documenter la logique technique des modes de paiement du plugin, depuis la configuration globale jusqu'aux formulaires de cotisation et aux notifications.

## Principe general

Le plugin ne definit pas lui-meme un catalogue autonome de moyens de paiement.
Il s'appuie sur les configurations actives de `bank`, puis les filtre selon les listes autorisees dans `configurer_association`.

## Fichiers de reference

- [`formulaires/inc/configurer_association.php`](../formulaires/inc/configurer_association.php)
- [`formulaires/editer_asso_categorie_cotisation.php`](../formulaires/editer_asso_categorie_cotisation.php)
- [`base/association.php`](../base/association.php)
- [`base/association_champs_extras.php`](../base/association_champs_extras.php)
- [`inc/api_cotisations.php`](../inc/api_cotisations.php)
- [`notifications/`](../notifications/)

## Source des choix

La fonction `preparer_choix_mode_paiement()`:

1. charge les configurations disponibles dans `bank`;
2. conserve uniquement celles marquees comme actives;
3. formate les labels visibles pour les saisies;
4. retourne une liste indexee par identifiant technique.

Les libelles visibles viennent de `bank`, mais les valeurs stockees sont des identifiants de configuration.

## Parametres globaux

La configuration expose trois familles principales:

| Cle | Usage |
|---|---|
| `mode_paiement_adhesion` | moyens acceptes pour les adhesions / cotisations |
| `mode_paiement_participation` | moyens acceptes pour les participations evenement |
| `mode_paiement_formidable` | moyens acceptes dans les formulaires Formidable |

Ces listes sont stockees en base sous forme serialisee.

### Onglet source

Ces trois listes sont configurees dans l'onglet `mode_paiement` de `configurer_association`.

Leur effet se propage ensuite vers :

- les categories de cotisation ;
- les categories de participation financiere ;
- certains formulaires Formidable relies a `bank`.

Le meme onglet porte aussi :

- les taxes globales sur adhesions et participations ;
- le niveau d'autorisation pour encaisser une transaction.

## Utilisation dans les categories de cotisation

Le formulaire `formulaires/editer_asso_categorie_cotisation.php` utilise `mode_paiement_adhesion` pour:

- afficher les moyens de paiement disponibles;
- pre-cocher ceux deja autorises;
- limiter la selection a ce qui est actif dans `bank`.

Le champ `mode_paiement` de la categorie de cotisation est ensuite conserve comme liste compacte.

## Utilisation dans les categories de participation

Les participations evenement utilisent le meme principe, mais avec la liste `mode_paiement_participation`.

Cette configuration sert a:

- filtrer les moyens affiches;
- aligner la saisie evenement sur les capacites reelles de paiement;
- garder la coherency entre evenement, transaction et comptabilite.

## Utilisation dans les extraits de configuration

Le fichier `base/association_champs_extras.php` montre que les modes de paiement sont aussi exposes dans les champs extras et dans certains fieldsets de configuration.

### Cas importants

- validation de la presence des modes de paiement;
- activation ou non de champs dependants;
- filtrage selon le type de paiement attendu.

## Utilisation dans les notifications

Les templates de notification peuvent afficher les moyens de paiement disponibles ou choisis.

Exemples:

- `notifications/email_collectif_adherent.html`;
- `notifications/inc/inc-infos_inscription_responsable.html`;
- `notifications/recu_encaissement_adhesion.html`;
- `notifications/recu_encaissement_participation.html`.

## Rôle fonctionnel

Les modes de paiement servent a:

- limiter les choix visibles;
- harmoniser les instructions au destinataire;
- guider le traitement comptable;
- conserver une correspondance claire entre configuration, transaction et notification.

## Points de vigilance

- Si un mode est desactive dans `bank`, il ne doit pas continuer a apparaitre dans le plugin.
- Les identifiants sauvegardes ne doivent pas etre confondus avec les libelles.
- Les notifications et les formulaires doivent toujours rester synchrones avec le catalogue `bank`.

## A lire en plus

- [`categories_cotisation.md`](./categories_cotisation.md)
- [`categories_participation_financiere.md`](./categories_participation_financiere.md)
- [`configurer_association.md`](./configurer_association.md)
- [`comptabilite_evenements.md`](./comptabilite_evenements.md)
- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/bank.md`](./plugins/bank.md)
- [`plugins/facteur.md`](./plugins/facteur.md)
- [`plugins/notifications.md`](./plugins/notifications.md)
