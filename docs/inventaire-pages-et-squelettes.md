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
| `activites`, `adherents_bck`, `voir_adherent` | pages privées déjà partiellement éclatées | à rationaliser |
| `action_*` sous `exec/` | squelette de confirmation + actions SPIP signées | migré |
| exports CSV/PDF | actions de téléchargement autorisées | migré hors de `exec/` |

Les anciennes routes `adherents_bck`, `edit_labels` et `edit_relances` ont été
retirées : la première était une sauvegarde de code, la génération PDF des
étiquettes était intégralement commentée, et les relances sont désormais prises
en charge par le formulaire CVT d'email collectif.

## Éléments rapatriés du plugin FO

Le plugin FO `zblobul_asso` reste propriétaire de la composition visuelle du
site. La logique réutilisable qui relève de l'association est accueillie sous
le chemin public `squelettes/` du plugin Association :

| Source FO | Cible Association 4 | Décision |
|---|---|---|
| `content/fiche_adherent.html` | `squelettes/content/fiche_adherent.html` | rapatrié et débarrassé des dépendances de thème |
| `content/inc/profil-statut_cotisation.html` | même chemin sous `squelettes/` | rapatrié |

| `content/inc/profil-newsletter.html` | même chemin sous `squelettes/` | rapatrié ; textes déplacés en langue |
| `inclure/inc-item_inscrit.html` | même chemin sous `squelettes/` | rapatrié et rendu plus sémantique |
| `content/profil.html`, `right_col/profil.html`, `extra/profil.html` | FO | composition et habillage, donc laissés au FO |
| `formulaires/inscription.*` | futur module Adhésions | ne pas recopier tel quel : surcharge ancienne à réécrire avec l'API SPIP 4 |
| `formulaires/editer_mailsubscriber_public.*` | futur module Communication | à migrer avec une dépendance explicite vers Mailsubscribers |

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

La recette locale du 22 août 2026 a utilisé SPIP 4.4.21 et PHP 8.4. Elle a
installé le schéma `1.6.0`, puis compilé sans erreur les 179 squelettes HTML du
répertoire `prive/`. Cette installation à blanc a également révélé puis permis
de corriger l'appel historique à `lire_fichier()` lorsque
`yaml/association.yaml` est absent. Le script complète les tests structurels,
mais ne remplace pas la recette authentifiée de navigation et de rendu sur
`test-fiafe`.
