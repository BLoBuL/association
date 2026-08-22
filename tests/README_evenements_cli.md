# Tests CLI evenements

Ce dossier ajoute un harnais CLI autonome pour les formulaires d'inscription evenement du plugin.

## Cible

Les scripts couvrent :

- helpers metier de formatage, totalisation et anti-spam ;
- chargement des 4 variantes de formulaire ;
- verification des cas critiques ;
- traitement avec creation d'activite, transaction, notification et redirection.

## Scripts

- `tests/test_evenements_helpers.php`
- `tests/test_evenements_charger.php`
- `tests/test_evenements_verifier.php`
- `tests/test_evenements_traiter.php`
- `tests/test_evenements_all.php`

## Execution

```powershell
php .\tests\test_evenements_helpers.php
php .\tests\test_evenements_charger.php
php .\tests\test_evenements_verifier.php
php .\tests\test_evenements_traiter.php
php .\tests\test_evenements_all.php
```

## Test navigateur dev.blobul.com

Le script navigateur couvre la matrice publique observable sur `dev.blobul.com` et, si des identifiants sont fournis, les parcours connectes stricts.

Execution :

```powershell
$env:BLOBUL_TEST_LOGIN='login_de_test'
$env:BLOBUL_TEST_PASSWORD='mot_de_passe_de_test'
& 'C:\Users\Jul\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\bin\node.exe' .\tests\browser\test_evenements_devblobul.mjs
```

Le script verifie :

- absence d'erreur PHP visible ;
- affichage correct des cas publics vs membres uniquement ;
- progression minimale des parcours publics anonymes ;
- presence des saisies famille attendues pour les parcours connectes stricts.

## Notes

- le bootstrap `tests/inc/bootstrap_evenements_cli.php` simule un environnement SPIP minimal ;
- les tests utilisent une BDD memoire et n'ecrivent rien dans le site ;
- si une regle metier change dans `formulaires/` ou `formulaires/inc/`, il faut mettre a jour les scenarios du bootstrap.
