# Plan de tests des inscriptions evenement

## Diagnostic des formulaires

La suite `tests/test_diagnostic_formulaire_inscription.php` controle :

- la stabilite du format court `IE1` en FO et BO ;
- la distinction simple / multi et creation / modification ;
- le mode famille effectivement actif ;
- la normalisation des booleens historiques (`oui`, `non`, valeur vide) ;
- le masquage des tokens et secrets ;
- le caractere deterministe de la reference courte ;
- l'ecart entre limite configuree et limite effective (`LC` / `LE`) ;
- l'application explicite du profil de session en FO et son absence en BO ;
- l'extraction recursive des saisies, des champs obligatoires et des conditions
  `afficher_si`.

La suite de chargement verifie en plus que, pour un scenario BO reel de la
matrice, la liste portee par le diagnostic correspond exactement aux saisies CVT
generees et que le bloc n'est pas expose a un utilisateur non webmestre.

## Objectif

Verifier les parcours critiques du domaine inscriptions en FO et en BO.
Ce plan doit etre execute apres toute evolution touchant :

- les wrappers `formulaires/inscription_evenement*.php` ;
- le backend commun `ie_*` ;
- les saisies dynamiques ;
- les regles de verification ;
- la gestion des transactions ou redirections.

## Priorites

- **P0** : non regression fonctionnelle immediate.
- **P1** : paiement, modification, validations metier.
- **P2** : anti-spam, robustesse, scenarios de bord.

## P0 - Parcours critiques

### FO simple

- FO simple connecte
  - creation inscription ;
  - `id_auteur` de session en BDD.

- FO simple non connecte
  - creation inscription ;
  - `id_auteur = 0` en BDD.

### FO multi

- FO multi famille connecte
  - selection `famille` correcte ;
  - recap coherent ;
  - montant coherent ;
  - `id_auteur` de session persiste ;
  - `nombre_inscrits` conforme au nombre de membres selectionnes.

- FO multi sans famille
  - `nb_inscrits` pilote correctement les participants ;
  - recap coherent ;
  - montant coherent.

- validation finale du recapitulatif multi
  - un jeton signe `cvtm_prev_post` opaque ne devient jamais un compteur a zero ;
  - le marqueur `ie_recapitulatif` reste present dans les gabarits FO et BO ;
  - une demande inferieure aux places disponibles est acceptee ;
  - un depassement de quota reste bloque ;
  - une requete reellement vide ne cree ni activite ni transaction.

### BO

- BO simple
  - `id_auteur` = auteur selectionne ;
  - cas sans compte -> `id_auteur = 0`.
  - evenement payant avec accompagnants : les quantites tarifaires restent visibles pour tous les types d'inscrit.

- BO multi
  - meme verification sur `id_auteur` ;
  - participants et categories correctement persistes.
  - parite avec le FO pour un second participant : `nb_inscrits = 2` conserve
    depuis l'etape CVT precedente et deux identites generees dans une seule etape ;
  - une categorie saisie a l'etape courante reste collectee lorsque `nb_inscrits` provient de `cvtm_prev_post` ;
  - une place demandee avec un quota disponible ne produit ni erreur de quota ni erreur de categorie.

## P1 - Paiement et modification

### Paiement

- evenement payant
  - tarif absent ou invalide refuse en FO connecte, FO non connecte et BO ;
  - identifiant de tarif d'un autre evenement refuse ;
  - recapitulatif avec categorie et montant recalcules cote serveur ;
  - montant ou transaction falsifies refuses avant persistance et notification ;
  - tarif valide a 0 EUR conserve comme categorie selectionnee ;
  - transaction creee ou mise a jour ;
  - redirection vers paiement si applicable ;
  - absence de redirection paiement si `validation=oui` et `validation_sur_paiement!=oui`.
  - recapitulatif famille avec tarif membre visible et `cvtm_prev_post` opaque :
    aucune fausse erreur de tarif absent, puis redirection vers le paiement
    lorsque SPIP restitue le payload valide au traitement.

- evenement gratuit
  - aucune transaction inutile ;
  - redirection metier attendue.

### Modification

- modification d'inscription simple
  - prechargement correct ;
  - mise a jour BDD correcte.

- modification d'inscription multi
  - donnees des etapes precedentes bien rehydratees ;
  - suppression des donnees obsoletes ;
  - recalcul montant fiable.

## P1 - Verification metier

- quota global depasse -> blocage avec message explicite ;
- limite accompagnants/famille -> erreur portee sur le bon champ ;
- selection famille vide -> rejet ;
- email invalide -> rejet ;
- doublon auteur inscrit -> rejet.
- fermeture a date et heure exactes -> meme date effective en FO connecte, FO non connecte et BO.
- notification capturee sans envoi reel : declencheur, activite cible, sujet, modele, destinataire synthetique et unicite.

## P2 - Anti-spam et robustesse

- honeypot rempli -> rejet ;
- domaine email bloque -> rejet ;
- presence de `http` -> rejet ;
- contenu suspect -> rejet ;
- reprise multi-etapes avec `cvtm_prev_post` -> etat coherent.

## Scenarios specifiques a surveiller

### FO multi famille

- une selection famille doit produire des champs participants coherents ;
- la valeur logique documentee est `famille = ['adherent', ...]` ;
- les payloads imbriques doivent rester toleres apres normalisation ;
- `ie_multi_public_verifier_legacy()` doit continuer a deleguer a `ie_verifier_commons()`.

### BO simple et multi

- aucun glissement de la regle `id_auteur` ;
- aucun retour a une logique de verification locale en dehors du backend commun.

## Verification BDD minimale

### Table `spip_asso_activites`

Verifier au minimum :

- `id_auteur`
- `nombre_inscrits`
- `nom_participants`
- `participants_json`
- `id_transaction`

### Table `spip_transactions`

Verifier au minimum :

- `id_transaction`
- `montant`
- `statut`

## Logs a verifier

- `[IE_VERIFY]` pour les controles metier ;
- `[IE_CHARGER]` pour la generation des saisies ;
- les logs critiques residuels des wrappers publics ne doivent pas devenir la source de verite de debug.

## Cibles de tests automatises (a ouvrir)

- tests unitaires sur `formater_post_form()` ;
- tests unitaires sur `formater_post_form_multi()` ;
- tests unitaires sur `calculer_montant_total()` ;
- tests d'integration sur `ie_verifier_commons()` pour les cas :
  - simple FO anonyme ;
  - multi famille ;
  - quotas depasses ;
  - doublon auteur ;
  - email invalide.

## Harnais automatise disponible pour `charger`

Un harnais PHP est disponible pour la phase `charger` :

- `tests/test_charger_inscriptions_matrix.php`
- `tests/inc/bootstrap_charger_inscriptions.php`

Il couvre une matrice representative des cas :

- FO / BO ;
- simple / multi ;
- gratuit / payant ;
- accompagnants oui / non ;
- famille oui / non ;
- creation / modification.

Commande d'execution :

```powershell
php .\tests\test_evenements_recapitulatif.php
php .\tests\test_evenement_defauts.php
php .\tests\test_evenements_all.php
php .\tests\test_charger_inscriptions_matrix.php
php .\tests\test_verifier_traiter_inscriptions_matrix.php
php .\tests\test_listes_fermeture_evenements.php
```

`test_evenement_defauts.php` audite les correspondances du bloc global
`evenement_defaut`, le chargement du formulaire d'un evenement neuf et la
persistance du choix `accompagnants = non` sans intervention utilisateur.

`test_evenements_recapitulatif.php` reproduit la frontiere CVT qui avait echappe
aux tests fondes sur un ancien payload base64/serialize : jeton signe opaque,
etape finale explicite, maintien des quotas et barriere de persistance a zero.

Le second script couvre une matrice representative pour :

- `verifier` (FO/BO, simple/multi, famille/non famille) ;
- `traiter` (redirections, insert/update activite, creation transaction selon le mode payant).

## Check-list de sortie avant mise en prod

- [ ] parcours FO simple valide
- [ ] parcours FO multi valide
- [ ] parcours BO simple valide
- [ ] parcours BO multi valide
- [ ] cas payant valide
- [ ] cas modification valide
- [ ] quotas valides
- [ ] anti-spam valide

