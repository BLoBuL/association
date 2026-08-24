# Domaine inscriptions evenement

## Objet

Ce dossier documente l'etat courant des formulaires d'inscription evenement du plugin `association`.
La documentation est organisee par sujet technique, avec une distinction nette entre :

- l'architecture effective du code ;
- les regles metier courantes ;
- les tests a executer en cas d'evolution ;
- la roadmap des chantiers restants.

## Source de verite

La source de verite fonctionnelle est le backend commun :

- `formulaires/inc/inscription_evenement_backend.php`

Les wrappers de formulaires `formulaires/inscription_evenement*.php` deleguent majoritairement vers ce backend.
Le principal residu legacy concerne la generation de saisies du formulaire FO multi public.

## Parcours de lecture recommande

1. `architecture.md`
   - pour comprendre les points d'entree et la repartition des responsabilites.
2. `data-flow.md`
   - pour suivre le cycle complet charge / verifie / traite.
3. `fields.md`
   - pour identifier les noms de champs, formats de payload et conventions FO/BO.
4. `validation.md`
   - pour les regles de quotas, doublons et anti-spam.
5. `tests-plan.md`
   - pour la recette manuelle et les cibles de tests automatises.
6. `plan_refactor_inscriptions.md`
   - pour la dette restante et les evolutions prevues.

## Fichiers du dossier

- `architecture.md` : cartographie des formulaires, backend commun, zones legacy.
- `data-flow.md` : cycle d'execution et normalisation du payload.
- `fields.md` : inventaire des champs saisis et calcules.
- `saisies-scenarios.md` : **matrice des saisies par scenario** (mode, connecte, payant, accompagnants, famille, BO/FO).
- `validation.md` : regles de verification metier.
- `tests-plan.md` : plan de recette et axes d'automatisation.
- `plan_refactor_inscriptions.md` : roadmap technique et dette restante.
- `suggestions.md` : idees d'amelioration non planifiees a ce stade.
- `plan_status.json` : etat synthétique du chantier, a usage technique.

## Etat courant

- Backend commun actif pour `charger`, `verifier` et `traiter`.
- Wrappers simples et multi relies au backend commun en FO et BO.
- `formulaires/inscription_evenement_multi_public.php` adapte les saisies FO multi via `ie_multi_public_saisies()` avant de déléguer le cycle CVT au backend commun.
- La verification multi public passe desormais par `ie_verifier_commons('multi_public', ...)`.
- Le payload famille en multi public est normalise via `ie_aplatir_liste_valeurs()` ; le champ principal est `famille`.

## Quand mettre a jour cette documentation

Mettre a jour ce dossier si l'un de ces elements change :

- un point d'entree de formulaire ;
- un nom de champ ou format de payload ;
- une regle de verification ;
- une redirection de traitement ;
- un scenario de test P0 ou P1.
