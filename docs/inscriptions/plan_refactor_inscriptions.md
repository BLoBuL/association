# Roadmap technique - inscriptions evenement

## Objectif du chantier

Stabiliser durablement les formulaires d'inscription evenement autour d'un backend commun, tout en reduisant le code duplique et en securisant les parcours FO/BO.

## Etat courant

### En place

- backend commun actif dans `formulaires/inc/inscription_evenement_backend.php` ;
- `charger`, `verifier`, `traiter` relies au backend sur les parcours simples et multi ;
- regle `id_auteur` clarifiee entre FO et BO ;
- normalisation du payload multi active ;
- verification multi public branchee sur `ie_verifier_commons('multi_public', ...)`.

### Encore specifique / legacy

- generation des saisies du FO multi public ;
- squelettes publics historiques ;
- certains logs de diagnostic encore presents dans les wrappers FO.

## Dette restante a court terme

### 1. Finaliser la convergence des saisies

- reduire la logique propre a `ie_multi_public_saisies_legacy()` ;
- documenter une cible de convergence vers le backend commun ;
- verifier les impacts sur les squelettes multi-etapes.

### 2. Stabiliser le rollout backend

- clarifier l'usage reel de `meta_cfg_use_inscription_backend` ;
- documenter la strategie d'activation et de rollback si ce meta doit devenir operant ;
- verifier si ce feature flag reste pertinent ou doit etre retire de la doc.

### 3. Nettoyer les wrappers publics

- supprimer les logs critiques temporaires ;
- limiter les wrappers a leur role de resolution des identifiants et delegation ;
- preparer la suppression des branches legacy quand les saisies seront unifiees.

## Dette a moyen terme

- unifier davantage les squelettes FO et BO ;
- renforcer les tests automatises autour du backend commun ;
- rationaliser la couche anti-spam si de nouvelles exigences apparaissent.

## Criteres de cloture du chantier backend

- parcours FO/BO simples et multi valides en creation et modification ;
- comportements payants et gratuits verifies ;
- quotas et anti-spam stables ;
- documentation alignee sur le code en production ;
- plan de tests maintenu a jour.

## Prochaines etapes recommandees

1. ouvrir des tests automatises sur `ie_verifier_commons()` et les formateurs ;
2. converger les saisies du FO multi public ;
3. nettoyer les traces de diagnostic residuelles ;
4. reevaluer la place du feature flag documentaire.
