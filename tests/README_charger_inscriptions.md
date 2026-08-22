# Tests de `charger` des formulaires d'inscription evenement

## Objet

Ce jeu de test couvre la phase `charger` des formulaires d'inscription evenement sur une matrice representative des cas metier :

- FO / BO
- simple / multi
- gratuit / payant
- accompagnants oui / non
- saisie famille oui / non
- creation / modification

## Fichiers

- `inc/bootstrap_charger_inscriptions.php`
  - bootstrap SPIP minimal ;
  - stubs SQL et metier ;
  - helpers d'assertion ;
  - traces d'effets de bord pour `traiter`.
- `test_charger_inscriptions_matrix.php`
  - fonction `association_tester_charger_formulaires_evenement()` ;
  - execution CLI de la matrice.
- `test_verifier_traiter_inscriptions_matrix.php`
  - fonctions `association_test_vt_run_verifier_scenarios()` et `association_test_vt_run_traiter_scenarios()` ;
  - execution CLI des matrices `verifier` et `traiter`.

## Execution rapide

```powershell
php .\tests\test_charger_inscriptions_matrix.php
php .\tests\test_verifier_traiter_inscriptions_matrix.php
```

## Resultat attendu

- sortie ligne par ligne par scenario ;
- code retour `0` si tout passe ;
- code retour `1` si au moins un scenario echoue.

Pour `verifier` et `traiter`, la sortie est separee en deux blocs :

- matrice verifier ;
- matrice traiter.

## Scenarios couverts

- FO simple gratuit sans accompagnants
- FO simple gratuit avec accompagnants
- FO simple payant sans accompagnants
- FO simple payant avec accompagnants
- FO simple famille connecte
- BO simple creation payante
- BO simple modification gratuite famille
- BO multi creation payante public
- BO multi modification payante famille
- FO multi public payant anonyme
- FO multi public payant famille

### Matrice verifier / traiter

- verifier FO simple gratuit / payant ;
- verifier FO famille vide ;
- verifier BO doublon auteur ;
- verifier FO multi public famille ;
- traiter FO simple gratuit (redirect evenement) ;
- traiter FO simple payant (redirect paiement) ;
- traiter BO creation / modification ;
- traiter FO multi public payant.

## Maintenance

Mettre a jour ce test si :

- un nom de champ change ;
- une structure `_saisies` change ;
- un nouveau mode de chargement est introduit ;
- une regle FO/BO ou famille evolue.

