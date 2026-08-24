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

### Encore spécifique

- adaptation des saisies dynamiques du FO multi public ;
- squelettes publics historiques ;
- certains logs de diagnostic encore presents dans les wrappers FO.

## Dette restante a court terme

### 1. Maintenir la convergence des saisies

- limiter `ie_multi_public_saisies()` à l'adaptation des étapes dynamiques ;
- conserver la délégation charger/verifier/traiter vers le backend commun ;
- verifier les impacts sur les squelettes multi-etapes.

### 2. Backend commun

- le backend commun est actif sans feature flag ;
- `meta_cfg_use_inscription_backend` n'existe pas dans le code et ne doit pas être réintroduit ;
- le rollback relève de Git et du déploiement atomique, pas d'une branche métier divergente.

### 3. Nettoyer les wrappers publics

- les diagnostics sont configurables par la catégorie `inscriptions` et ne journalisent aucun contenu personnel ;
- limiter les wrappers à la résolution des identifiants et à la délégation.

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
