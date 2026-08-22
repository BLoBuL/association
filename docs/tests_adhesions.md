# Matrice automatique des adhésions

## But

La suite `tests/run_adhesions.php` couvre les règles partagées entre `blobul-ASSO_BO`, `blobul-ASSO_FO` et Bank sans modifier la base du site.

## Exécution locale

Depuis `blobul-ASSO_BO` :

```bash
php tests/run_adhesions.php
```

Le FO est recherché dans `../blobul-ASSO_FO`. Un autre chemin peut être fourni :

```bash
BLOBUL_ASSO_FO_PATH=/chemin/blobul-ASSO_FO php tests/run_adhesions.php
```

## Fichiers lancés

| Test | Périmètre |
|---|---|
| `test_adhesions_api.php` | statuts, Bank, montants, catégories, justificatifs |
| `test_adhesions_echeances.php` | sujets, échéances, activation |
| `test_adhesions_parametrage.php` | formulaire de catégorie et configuration globale |
| `test_adhesions_notifications_audit.php` | catalogue, sujets, gabarits, destinataires, documents |
| `test_adhesions_justificatifs_bo.php` | contrôle BO et dossier incomplet |
| `test_validite_reinscription_scolaire.php` | validité et période scolaire |
| FO `test_adhesions_formulaire.php` | formulaire public, token, documents, redirections |

## Cas métier couverts

### Montants et Bank

- cotisations positives avec `auto`, `pre-paiement` et `post-paiement` ;
- cotisation à zéro sans transaction Bank ;
- refus d'un montant négatif ;
- don et taxe ;
- propagation d'une erreur Bank sans création de cotisation.

### Justificatifs

- champ fichier totalement absent ;
- un seul document ;
- deux JPEG valides ;
- extension interdite ;
- fichier supérieur à 10 Mo ;
- upload partiel ;
- blocage avant Bank ;
- validation BO groupée ;
- retour `À revoir` ;
- refus de valider un dossier incomplet.

### Notifications

- onze scénarios inventoriés ;
- gabarits et sujets présents ;
- destinataires comptés mais masqués ;
- reçu désactivé signalé comme attention ;
- contexte documentaire disponible ;
- bloc documentaire présent dans les trois emails administrateur ;
- notification adhérent uniquement pour `À revoir` ;
- aucun envoi pendant l'audit.

## Validation sur dev

La commande distante doit être lancée depuis le plugin BO actif :

```bash
cd sites/dev.blobul.com/plugins/blobul-ASSO_BO
BLOBUL_ASSO_FO_PATH=../blobul-ASSO_FO php tests/run_adhesions.php
```

Après déploiement de squelettes ou de langues :

```bash
cd sites/dev.blobul.com
../../bin/spip cache:vider
```

Le contrôle navigateur doit rester non destructif : pas de soumission de formulaire, pas de validation documentaire et pas de bouton **Envoyer test**.

## Limites connues

- Les tests unitaires ne remplacent pas un essai réel d'upload dans un environnement contenant des données de test dédiées.
- Dev ne possédait pas de justificatif sur les cotisations inspectées lors de la mise en place ; l'état vide a été validé visuellement et les autres états par la matrice.
- La production n'est pas une cible de test interactif.
