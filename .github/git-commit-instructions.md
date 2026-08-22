# Instructions de commit Git - blobul-ASSO_BO

## Format recommande
`type(scope): resume court`

Exemples :
- `fix(association): corriger validation d'inscription`
- `feat(association): ajouter export des cotisations`
- `chore(ci): harmoniser controle de style`

## Types autorises
- `fix` : correction de bug
- `feat` : nouvelle fonctionnalite
- `refactor` : amelioration interne sans changement fonctionnel voulu
- `docs` : documentation
- `test` : tests
- `chore` : maintenance

## Corps du commit (obligatoire si changement metier)
- Probleme metier constate
- Solution appliquee
- Risques connus / impacts
- Migration API si signature modifiee

## Regles projet
- Rediger les commits en francais.
- Mentionner les fichiers ou pipelines touches si utile.
- Si API stable modifiee, decrire la migration dans le commit et la PR.

