# Cadrage cartographie fonctions - blobul-ASSO_BO

- Date generation: 2026-04-20
- Perimetre: plugin `association` (repo `blobul-ASSO_BO`), sans modification du code.
- Sources analysees: fichiers `.php`, `.html`, `.xml`, `.js`, `.md`, `.yaml`, `.yml` (hors `docs/cartographie/`).
- Methode: extraction statique des definitions `function`, puis detection des usages textuels (appels PHP et filtres templates).
- Limites: les appels dynamiques (autoriser, callbacks en chaine, appels variables) restent partiellement non resolus.

## Logiques SPIP explicitement prises en compte

- Prefixe `action_*`: points d'entree actions SPIP (souvent appeles via URL/action, pas par appel PHP direct).
- Prefixe `autoriser_*`: droits resolves par le pipeline `autoriser` et `autoriser()`.
- Prefixe `formulaires_*_{charger,verifier,traiter}[_dist]`: cycle CVT, resolution par nom de formulaire.
- Prefixe `balise_*` / `critere_*` / `filtre_*`: resolution depuis les squelettes SPIP.
- Pipelines (`association_<pipeline>`): resolution par declaration dans `paquet.xml`.
- Chargement dynamique: `include_spip()` / `charger_fonction()` documentes separement.

## Chiffres cles

- Nombre de fichiers analyses: **454**
- Nombre de fonctions definies (uniques): **646**
- Nombre de definitions detectees: **655**
- Nombre de lignes include_spip/charger_fonction: **608**
