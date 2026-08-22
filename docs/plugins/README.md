# Dépendances externes du plugin `blobul-ASSO_BO`

## But

Lister les plugins SPIP utilisés par `blobul-ASSO_BO` et documenter leur rôle côté Blobul.

## Ordre de lecture

1. [`Matrice des dépendances externes`](./matrix_dependances.md)
2. [`inscription3`](./inscription3.md)
3. [`bank`](./bank.md)
4. [`notifications`](./notifications.md)
5. [`cextras`](./cextras.md)
6. [`saisies`](./saisies.md)
7. [`Facteur`](./facteur.md)
8. [`mailsubscribers`](./mailsubscribers.md)
9. [`newsletter`](./newsletter.md)
10. [`mailshot`](./mailshot.md)
11. [`agenda`](./agenda.md)
12. [`verifier`](./verifier.md)
13. [`gis`](./gis.md)
14. [`destinations`](./destinations.md)

## Principe

Chaque fiche décrit:

- le rôle du plugin dans Blobul;
- les points d'entrée utilisés;
- les configurations et pipelines concernés;
- les risques d'intégration.

## Source de vérité

La déclaration officielle des dépendances se trouve dans [`paquet.xml`](../../paquet.xml).

## Points de vigilance

- Une dépendance peut être `necessite` ou simple `utilise`.
- Certaines fiches décrivent aussi des interactions de configuration, pas seulement des appels de code.
- Les plugins tiers doivent être documentés du point de vue Blobul, pas de manière générique.
