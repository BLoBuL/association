# Préparer les dix plugins depuis le monorepo

Le dépôt conserve le socle à sa racine et les neuf modules dans `plugins/`.
SPIP doit cependant recevoir dix dossiers frères. Le script
`tools/stage-suite.ps1` construit cette disposition sans modifier le dépôt ni
écraser une destination existante.

## Construire le staging

Depuis la racine du dépôt, sous PowerShell :

```powershell
.\tools\stage-suite.ps1 -Destination D:\Codex\temp\association-suite-stage
```

La destination doit être absente ou vide et située hors du dépôt. Le script ne
la nettoie jamais automatiquement. Il copie uniquement les fichiers suivis par
Git, avec les modifications locales éventuelles de ces fichiers.

Le résultat contient :

```text
association-suite-stage/
  association/
  association-adhesions/
  association-communication/
  association-compta/
  association-dons/
  association-evenements/
  association-groupes/
  association-paiements/
  association-prets/
  association-ventes/
  association-suite-manifest.json
```

Il ne doit jamais exister de dossier `association/plugins/` dans le résultat.

## Manifeste

Le manifeste JSON indique pour chaque plugin son préfixe, sa version, le nombre
de fichiers et un SHA-256 déterministe. Ce SHA-256 est calculé sur la liste
triée des chemins relatifs et du SHA-256 de chaque fichier. Le manifeste indique
aussi le commit Git source ; il permet donc de comparer un staging à la source
avant déploiement.

Le champ `generated_at_utc` varie à chaque exécution, contrairement aux SHA-256
des plugins lorsque leurs fichiers sont identiques.

## Vérifier l'outil

```powershell
php .\tests\test_staging_monorepo.php
```

Le test construit un staging temporaire, exige exactement dix `paquet.xml` au
premier niveau, contrôle le manifeste puis vérifie qu’aucun module n’est
imbriqué dans le socle.
