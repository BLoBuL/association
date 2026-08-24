# Flux de traitement courant

## 1. Entree formulaire

Chaque wrapper :

- resolve `id_evenement` et `id_activite` depuis les arguments ou `_request()` ;
- si une modification ne fournit que `id_activite`, recharge `id_evenement`
  depuis `spip_asso_activites` et replace les deux identifiants dans la requete
  CVT ;
- choisit un mode explicite parmi `public`, `prive`, `multi_public`, `multi_prive` ;
- delegue ensuite au backend commun.

## 2. Chargement du contexte

`ie_charger_commons()` :

- recupere la configuration evenement (`affichage_dans_activites`, `gestions_places`, eligibilite) ;
- determine creation ou modification ;
- prepare les saisies adaptees au contexte : gratuit/payant, simple/multi, famille/quantite, FO/BO ;
- precharge les donnees d'une inscription existante si `id_activite` est fourni ;
- en modification, reinjecte uniquement les valeurs persistantes absentes de la
  requete courante : identites, informations supplementaires, categories,
  commentaire et annotation. Une valeur soumise par l'etape courante reste
  toujours prioritaire.

Les identifiants `inscrit_N` d'une inscription sans compte restent des
participants generiques. Seuls `adherent`, `conjoint`, `enfant_N` et `invite_N`
sont reconstruits dans la selection `famille`.

Dans le cas `multi_public`, le chargement final reste compose ainsi :

1. chargement commun via `ie_charger_commons('multi_public', ...)` ;
2. adaptation des saisies publiques par `ie_multi_public_saisies()` ;
3. recalcul de `_saisies_par_etapes`.

## 3. Normalisation de la requete

Le backend ne s'appuie pas directement sur `$_POST` brut.

### Etapes

1. `ie_requete_attendue()` recompose le payload attendu depuis `_request()`.
2. SPIP reinjecte nativement les valeurs des etapes precedentes depuis le jeton
   signe `cvtm_prev_post` ; `ie_convertir_post()` ne sert qu'a la compatibilite
   avec les anciens payloads de test encodes en base64/serialize.
3. `ie_format_post()` route vers :
   - `formater_post_form()` pour les formulaires simples ;
   - `formater_post_form_multi()` pour les formulaires multi.

### Cas particulier famille en multi

`formater_post_form_multi()` normalise la selection famille avec `ie_aplatir_liste_valeurs()`.
Cette etape permet de traiter de facon stable des structures comme :

- `['adherent']`
- `[['adherent']]`

La forme logique attendue dans la documentation metier reste :

- `famille = ['adherent', 'conjoint', ...]`

## 4. Verification metier

`ie_verifier_commons()` travaille sur le payload normalise et sur le resultat de `ie_format_post()`.

Il prend en charge :

- le calcul de `nombre_a_verifier` ;
- les limites par adherent ;
- les quotas globaux ;
- les doublons d'inscription ;
- la validation email ;
- l'anti-spam ;
- les controles specifiques multi/famille.

En `multi_public`, `ie_multi_public_verifier_legacy()` n'embarque plus de logique autonome et delegue a `ie_verifier_commons('multi_public', ...)`.

Le recapitulatif multi poste en plus `ie_recapitulatif=1`. Cette indication
evite de transformer la requete metier volontairement vide de la derniere etape
en inscription a zero. Les quotas restent controles avec le nombre normalise et
`ie_traiter_commons()` interdit toujours toute persistance si le nombre final de
participants est nul.

## 5. Traitement

`ie_traiter_commons()` :

- calcule le statut d'inscription via `activite_enregistrement_calculator` ;
- calcule ou met a jour la transaction si l'evenement est payant ;
- insere ou modifie `spip_asso_activites` ;
- conserve en modification `participants_json`, `nombre_inscrits`, les
  identites et informations supplementaires, et met a jour le commentaire et
  l'annotation demandes ;
- envoie les notifications ;
- gere les effets secondaires (cookies publics, comptabilite, redirection).

## 6. Redirection finale

### FO

- vers `paiement` si l'evenement est payant et doit passer par paiement immediat ;
- sinon vers la page `evenement`.

### BO

- vers `payer` dans le cas payant equivalent ;
- sinon vers `voir_activites`.

## 7. Points de vigilance

- `cvtm_prev_post` est un jeton signe gere par le CVT multi-etapes de SPIP : le
  code metier ne doit pas tenter de le decoder comme un simple serialise.
- Les saisies du FO multi public restent construites hors backend commun, ce qui en fait la zone la plus sensible aux regressions de structure de payload.
- Toute evolution du nommage de champs doit etre repercutee dans `fields.md`, `validation.md` et `tests-plan.md`.
