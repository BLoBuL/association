# Plan optimisation chargement des fonctions

Generation: 2026-04-20

## Constat

- `association_options.php` charge beaucoup de modules globalement.
- `association_pipelines.php` fait des includes en tete de fichier.
- Plusieurs helpers peuvent etre charges a la demande.
- Les entrees SPIP (`action_*`, `formulaires_*`, `autoriser_*`, `balise_*`) ont des besoins de chargement differents.

## Plan

1. Mesurer les includes reels executes par page privee critique.
2. Deplacer les includes non critiques au plus pres des fonctions consommatrices.
3. Conserver en chargement global uniquement ce qui est requis pour les hooks SPIP precoces (options/pipelines).
4. Segmenter les helpers par domaine (`inc/association/*`) et charger en lazy-load depuis `action_*/formulaires_*/genie_*`.
5. Garder des facades stables dans `association_*` publics.
6. Verifier cron, bank, notifications, formulaires CVT.

## Priorites

- Priorite 1: alleger `association_options.php`.
- Priorite 2: reduire les includes globaux dans `association_pipelines.php`.
- Priorite 3: lazy-load exports/newsletters/notifications.
- Priorite 4: aligner les besoins de chargement sur la convention SPIP de chaque famille (`action_`, `formulaires_`, `autoriser_`, `balise_`).

## Contraintes

- Conserver signatures API publiques stables.
- Garder `include_spip()` / `charger_fonction()` pour les appels externes SPIP.
