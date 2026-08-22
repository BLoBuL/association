# Architecture des formulaires d'inscription evenement

## Vue generale

Le domaine inscriptions repose sur un backend commun dans `formulaires/inc/inscription_evenement_backend.php`.
Ce backend centralise :

- le chargement du contexte et des saisies ;
- la verification metier ;
- le traitement final (persistance, transaction, notifications, redirection).

Les formulaires publics et prives servent principalement de wrappers autour de ce backend.

Les filtres utilises directement par leurs squelettes doivent en revanche etre
declares dans `association_fonctions.php`, charge par SPIP au calcul des
squelettes. C'est notamment le cas de `ie_message_erreur_texte()`, qui nettoie
les messages Saisies/CVT sans rendre le balisage d'erreur brut. Le backend charge
explicitement ce fichier afin d'utiliser la meme implementation pendant la
verification PHP et dans les tests sans environnement SPIP complet.

## Points d'entree actifs

### Formulaires simples

- `formulaires/inscription_evenement.php`
  - wrapper BO simple ;
  - utilise `ie_charger_commons('prive', ...)`, `ie_verifier_commons('prive', ...)`, `ie_traiter_commons('prive', ...)`.

- `formulaires/inscription_evenement_public.php`
  - wrapper FO simple ;
  - utilise `ie_charger_commons('public', ...)`, `ie_verifier_commons('public', ...)`, `ie_traiter_commons('public', ...)`.
  - conserve encore des logs specifiques FO a nettoyer ulterieurement.

### Formulaires multi

- `formulaires/inscription_evenement_multi.php`
  - wrapper BO multi ;
  - mode `multi_prive` ;
  - expose encore `formulaires_inscription_evenement_multi_saisies()` pour compatibilite CVT.

- `formulaires/inscription_evenement_multi_public.php`
  - wrapper FO multi ;
  - mode `multi_public` ;
  - verification et traitement relies au backend commun ;
  - generation de saisies encore specifique via `ie_multi_public_saisies_legacy()`.

## Backend commun

### Fonctions structurantes

- `ie_charger_commons($mode, $id_evenement, $id_activite)`
  - determine creation/modification ;
  - calcule les contextes evenement, quotas et eligibilite ;
  - produit `_saisies` et `_saisies_par_etapes`.

- `ie_verifier_commons($mode, $id_evenement, $id_activite, $post)`
  - normalise le payload ;
  - calcule `nombre_a_verifier` ;
  - applique quotas, doublons, anti-spam, controle email.

- `ie_traiter_commons($mode, $id_evenement, $id_activite, $post)`
  - formate les donnees ;
  - calcule statut et transaction ;
  - persiste l'activite ;
  - envoie notifications et gere la redirection finale.

### Fonctions de support importantes

- `ie_format_post()`
- `ie_requete_attendue()`
- `ie_convertir_post()`
- `ie_handle_transaction()`
- `ie_persist_activite()`

### Diagnostic commun FO / BO

Le collecteur `inc/diagnostic_formulaire_inscription.php` produit un diagnostic
normalise a partir des memes donnees metier que le formulaire charge :

- ouverture de l'inscription ;
- eligibilite de l'inscrit ;
- options effectives de l'evenement ;
- gestion des places ;
- configuration globale utile ;
- structure exacte des saisies CVT et conditions `afficher_si`.

Le resultat est expose dans `diagnostic_inscription` uniquement lorsque la
session courante est celle d'un webmestre. Il contient un code de scenario
versionne `IE1`, une reference courte deterministe et les blocs detailles. Les
cles sensibles (`token`, secret, mot de passe, cle API) sont masquees avant
l'export et avant le calcul de la reference.

Le code distingue notamment l'interface `FO` / `BO`, la creation ou la
modification, le parcours simple ou multi, le mode famille effectivement actif,
le paiement, les accompagnants, le type d'inscrits, les quotas et le nombre de
tarifs disponibles.

Les segments de capacite permettent d'identifier une surcharge incorrecte sans
relire les logs :

- `LC` : limite configuree sur l'evenement ;
- `LE` : limite effectivement utilisee par la validation ;
- `PD` : places disponibles ;
- `AE` : places disponibles en attente ;
- `PS` : profil de la session applique (`1`) ou sans effet (`0`).

Exemple : `LC05-LE01-PS1` signale immediatement qu'une limite configuree a 5 a
ete ramenee a 1 par le profil de session. En BO, `PS` doit toujours valoir `0`.

## Helpers metier hors backend

### `formulaires/inc/inscription_evenement.php`

- `formater_post_form()`
- `formater_post_form_multi()`
- `calculer_montant_total()`
- `inserer_asso_activites()` / `modifier_asso_activites()`
- `verifier_spam_formulaire_inscription()`
- `ie_aplatir_liste_valeurs()` pour normaliser certains payloads multi, notamment `famille`.

### `formulaires/inc/inscription_evenement_saisies.php`

- generation des champs CVT dynamiques ;
- saisies famille, quantites, tarifs, infos supplementaires ;
- prechargements selon auteur, contexte evenement et etape multi.

## Redirections metier centralisees

### Front-office

Redirection vers `paiement` si :

- evenement payant ;
- et (`validation` inactive ou `validation_sur_paiement=oui`) ;
- et statut final different de `liste_attente`.

Sinon, redirection vers la page `evenement`.

### Back-office

Redirection vers `payer` sous la meme condition de paiement.
Sinon, redirection vers `voir_activites`.

## Dette technique restante

- `formulaires_inscription_evenement_multi_public_saisies()` conserve un chemin de generation legacy.
- Les squelettes publics restent separes des equivalents BO.
- `formulaires/inscription_evenement_public.php` conserve des logs critiques de diagnostic qui ne relevent plus de la doc courante.
- La suppression complete des wrappers publics legacy est differee apres validation fonctionnelle complete.

## Tables SQL principales

- `spip_asso_activites`
- `spip_transactions`
- `spip_asso_categories_activites`
- `spip_asso_categories_activites_liens`
- `spip_auteurs`
