# Versionnement de la suite Association 4

## Ligne de projet

La suite utilise volontairement une ligne sémantique `4.x`, conformément à la
décision de projet, même si les sources historiques du BO portent des tags
datés `6.x`. Ces anciens tags restent immuables et ne sont ni prolongés ni
réécrits dans la nouvelle ligne.

Le dépôt canonique est `BLoBuL/association`. Le socle et les neuf modules sont
versionnés ensemble dans ce monorepo, mais chaque plugin conserve son propre
`paquet.xml`, sa version fonctionnelle, son schéma SQL et son état SPIP. Une
correction propre à un module n'oblige donc pas à incrémenter artificiellement
les neuf autres paquets.

## Tags non ambigus

Les tags d'un module sont qualifiés par son préfixe :

```text
association-v4.0.0
association_adhesions-v4.0.0
association_prets-v4.0.2
```

Une livraison coordonnée des dix paquets peut en plus recevoir un tag
`suite-association-v4.0.0`. Ce tag désigne le commit exact depuis lequel le
staging complet et son manifeste ont été produits. Un tag historique `v6.x`
ne doit jamais être déplacé ou réutilisé pour la suite 4.

## État actuellement qualifié

| Préfixe | Version | État |
|---|---:|---|
| `association` | `4.0.0-dev` | `dev` |
| `association_adhesions` | `4.0.0` | `dev` |
| `association_communication` | `4.0.0` | `dev` |
| `association_compta` | `4.0.0` | `dev` |
| `association_dons` | `4.0.0` | `dev` |
| `association_evenements` | `4.0.0` | `dev` |
| `association_groupes` | `4.0.0` | `dev` |
| `association_paiements` | `4.0.0` | `dev` |
| `association_prets` | `4.0.2` | `dev` |
| `association_ventes` | `4.0.0` | `dev` |

`association_prets` a déjà reçu deux corrections propres à son cycle. Cette
différence est attendue et reste compatible avec la dépendance `[4.0.0;4.*]`
du socle.

## Porte de release

Avant de créer un tag ou une archive :

1. choisir explicitement le canal `test` ou `stable` ;
2. fixer l'état correspondant dans chacun des paquets livrés ;
3. retirer le suffixe `-dev` de la version du socle pour une livraison ;
4. rejouer la suite de tests, le staging des treize plugins et la compilation SPIP ;
5. produire l'archive depuis le tag qualifié, jamais depuis une tête de branche ;
6. contrôler le manifeste de staging, les versions et les empreintes ;
7. déployer d'abord sur un site de qualification et refaire les recettes BO et
   FO avant toute promotion stable.

Aucun tag n'est créé tant que le canal final n'est pas décidé. Un déploiement
de recette depuis une branche poussée ne constitue pas une release.
