# Recherche adherents et segmentation

## But

Documenter la recherche des adherents, la recherche avancee et la segmentation reutilisee dans plusieurs blocs du plugin.

## Fichiers sources

- [`formulaires/adherents_recherche_rapide.php`](../formulaires/adherents_recherche_rapide.php)
- [`formulaires/adherents_recherche_avancee.php`](../formulaires/adherents_recherche_avancee.php)
- [`formulaires/inc/adherents_recherche_avancee.php`](../formulaires/inc/adherents_recherche_avancee.php)
- [`inc/adherents_search_context.php`](../inc/adherents_search_context.php)
- [`association_fonctions.php`](../association_fonctions.php)

## Recherche rapide

La recherche rapide:

- affiche un formulaire simple;
- conserve le contexte d'affichage;
- retourne les resultats sans logique complexe de segmentation.

## Recherche avancee

La recherche avancee:

- construit ses saisies a partir des champs extras d'auteurs;
- sauvegarde les criteres en session;
- permet de conserver les filtres lors de la navigation dans le tableau des adherents.

### Fonctions clefs

- `adherents_recherche_avancee_saisies()`;
- `formulaires_adherents_recherche_avancee_traiter_dist()`;
- `nettoyage_liste_config_inscription3()`;
- `preparer_criteres_adherents()`.

## Segmentation

La segmentation des auteurs est reutilisee pour:

- les listes d'adherents;
- les emails collectifs;
- les notifications de statut interne;
- les listes de diffusion.

Champs et sources typiques:

- champs extras de `spip_auteurs`;
- `statut_interne`;
- `validite`;
- `type_adherent`;
- `radio_type_adherent`.

## Point d'integration

La recherche est liee a:

- `mailshots`;
- `mailsubscribinglists`;
- `inscription3`;
- les segments configuration dans `configurer_association`.

### Onglets relies

Deux onglets pilotent directement cette partie :

- `segments` pour la liste des champs reutilisables dans la segmentation ;
- `affichage_prive` pour les filtres et colonnes visibles dans le tableau adherents.

## Points de vigilance

- Les champs extras peuvent evoluer, il faut donc que les listes de recherche soient generées dynamiquement.
- La persistence en session peut surprendre si elle n’est pas expliquee a l’utilisateur final.
