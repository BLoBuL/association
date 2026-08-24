# Inventaire des pages et des squelettes

## Principe de migration

La branche `4.x` remplace progressivement les contrôleurs `exec/*.php` qui
fabriquent eux-mêmes une page HTML par les composants natifs de l'espace privé
SPIP 4 :

- `prive/squelettes/contenu/<exec>.html` pour le contenu principal ;
- `prive/squelettes/navigation/<exec>.html` pour les raccourcis contextuels ;
- `prive/objets/liste/` pour les listes réutilisables ;
- formulaires CVT dans `formulaires/` pour toute mutation complexe ;
- actions signées par `#URL_ACTION_AUTEUR` et protégées à nouveau côté PHP ;
- autorisations métier centralisées dans `association_autoriser.php`.

Une suppression ne doit jamais dépendre d'un simple paramètre GET vers une
page `exec`. Les anciens écrans de confirmation pourront être remplacés par un
CVT lorsque la suppression demande plusieurs choix métier.

## État de la migration privée

| Route historique | Cible SPIP 4 | État |
|---|---|---|
| `comptes` | `prive/squelettes/contenu/comptes.html` | contrôleur mort supprimé, squelette existant conservé |
| `edit_compte` | `contenu/edit_compte.html` + CVT existant | migré |
| `destinations` | `contenu/destinations.html` + `navigation/destinations.html` | migré, liste paginée et action signée |
| `edit_destination` | `contenu/edit_destination.html` + CVT existant | migré |
| `plan_comptable` | `contenu/plan_comptable.html` + `navigation/plan_comptable.html` | migré, filtres et pagination |
| `edit_plan` | `contenu/edit_plan.html` + CVT existant | migré |
| `edit_don` | `contenu/edit_don.html` + CVT existant | migré |
| `edit_ressource` | `contenu/edit_ressource.html` + CVT existant | migré |
| `edit_vente` | `contenu/edit_vente.html` + CVT existant | migré |
| `dons`, `ressources`, `prets`, `ventes` | contenu et navigation privés | migré ; éditeur de prêt converti en CVT |
| `bilan` | `contenu/bilan.html` + fonctions de calcul structurées | migré |
| `activites`, `voir_adherent` | contenus, navigation, hiérarchie et composants privés de leurs modules | migré et rationalisé |
| `action_*` sous `exec/` | squelette de confirmation + actions SPIP signées | migré |
| exports CSV/PDF | actions de téléchargement autorisées | migré hors de `exec/` |

Les anciennes routes `adherents_bck`, `edit_labels` et `edit_relances` ont été
retirées : la première était une sauvegarde de code, la génération PDF des
étiquettes était intégralement commentée, et les relances sont désormais prises
en charge par le formulaire CVT d'email collectif.

## Éléments rapatriés du plugin FO

La suite ne dépend plus d’un plugin FO Blobul. Les compositions publiques
reposent sur les inclusions standard du squelette actif de SPIP 4 et sont
rangées dans leur plugin métier :

| Source FO | Cible Association 4 | Décision |
|---|---|---|
| `content/fiche_adherent.html` | Adhésions : `squelettes/content/fiche_adherent.html` | rapatrié et débarrassé des dépendances de thème |
| `content/inc/profil-statut_cotisation.html` | Adhésions : même chemin | rapatrié |
| `content/inc/profil-newsletter.html` | Adhésions : même chemin | rapatrié ; textes déplacés en langue |
| `inclure/inc-item_inscrit.html` | Événements : `squelettes/inclure/inc-item_inscrit.html` | rapatrié et rendu plus sémantique |
| pages `profil`, `fiche_adherent`, `inscription` | Adhésions : `squelettes/*.html` | compositions SPIP 4 autonomes |
| page `evenement` | Événements : `squelettes/evenement.html` | composition SPIP 4 autonome |

Les ressources indispensables provenant des autres plugins Blobul historiques
sont également internalisées dans les chemins standards de leur module métier :

| Source historique | Module autonome | Contrat conservé |
|---|---|---|
| `blobul-BANK/modeles/payer_acte*.html` | Paiements : `modeles/` | sélection des configurations Bank et rendu de `#PAYER_ACTE`, avec lecture des métas par `#CONFIG` |
| `blobul-CORE/emails/` | Communication : `emails/` | coque HTML responsive, logo du site, titre, contenu, bouton et pied de page, sans configuration ni marque Blobul |
| `blobul-ASSO_FO/inclure/album_photos_evenement*.html` | Événements : `squelettes/inclure/` | portfolio public et variante verrouillée, inclus directement par `squelettes/evenement.html` |

Les autres fichiers de `blobul-ASSO_FO` ont été relus avant exclusion ; ils ne
doivent pas être copiés dans un plugin métier sans leur ancien thème :

| Source historique | Remplacement autonome | Décision |
|---|---|---|
| `formulaires/inscription.*` | Adhésions : page `inscription` et formulaire public d'Inscription 4 | utiliser l'API du plugin déclaré plutôt que maintenir une surcharge Inscription 3 |
| `formulaires/editer_mailsubscriber_public.*` | Communication : page `newsletter` et formulaire public de Mailsubscribers | conserver le CVT officiel du plugin déclaré |
| `inclure/forum.html` | forum/commentaires standards du squelette SPIP actif | composant éditorial générique, sans logique Association |
| `inclure/menu_visiteur.html` | en-tête du squelette actif et URLs de connexion SPIP | composant de thème, sans logique métier |
| `inclure/inc-item_article_mini.html` | listes d'articles du squelette actif | composant éditorial de thème |
| `inclure/inc-item_auteur.html` | boucles et modèles auteurs du squelette actif | composant éditorial et présentation de thème |
| `inclure/inc-documents.html`, `inc-item_document.html` | modèles Documents et portfolio du squelette actif | composants génériques ; les deux albums réellement propres aux événements sont déjà dans Événements |

Cette exclusion évite de transformer la suite métier en thème global. Elle ne
retire aucune fonction Association : inscription/adhésion, newsletter,
événements, portfolio protégé, paiement et catalogue de prêts possèdent chacun
leur page ou modèle autonome dans leur plugin responsable.

Le compilateur SPIP de la suite parcourt désormais, pour chaque module, les
dossiers `squelettes/`, `modeles/`, `emails/` et `notifications/`. Une ressource
rapatriée n'est donc pas seulement inventoriée : son chargement comme fond SPIP
est contrôlé sans activer `blobul-BANK`, `blobul-CORE` ni `blobul-ASSO_FO`.

Les pages privées n'incluent plus de fragments `content/` ou `right_col/`
fournis par un thème ou un plugin FO. Le logo d'événement utilisé dans le BO
est rendu par `prive/inclure/asso_logo_evenement.html` et le récapitulatif
d'inscription des notifications appartient désormais à Association.

## Critères de sortie d'une page

Une route n'est considérée migrée que si elle possède une autorisation testée,
ne produit plus son HTML depuis PHP, utilise des URLs SPIP, échappe les données
au bon niveau, conserve les fonctions métier historiques attendues et compile
sur une installation SPIP 4/PHP 8. La recette finale doit aussi contrôler la
navigation et le rendu authentifié sur `test-fiafe`.

## Validation reproductible sous SPIP 4

Le script `tests/compiler_squelettes_spip.php` doit être exécuté depuis une
installation SPIP avec le plugin activé :

```console
spip php:run --include=plugins/association/tests/compiler_squelettes_spip.php
```

La recette du 22 août 2026 a utilisé SPIP 4.4.21 et PHP 8.4. Elle a
installé le schéma `1.6.0`, puis compilé sans erreur les 179 squelettes HTML du
répertoire `prive/`. Les quatre routes publiques déplacées dans Adhésions et
Événements ont ensuite répondu en HTTP 200 sans erreur fatale. Cette installation à blanc a également révélé puis permis
de corriger l'appel historique à `lire_fichier()` lorsque
`yaml/association.yaml` est absent. Le script complète les tests structurels,
mais ne remplace pas la recette authentifiée de navigation et de rendu sur
`test-fiafe`.
