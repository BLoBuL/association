# Validation et anti-spam

## Principe general

La verification passe par `ie_verifier_commons()`.
Cette fonction travaille toujours sur un payload normalise via `ie_requete_attendue()` et non sur `$_POST` brut.

## Regles metier principales

### Limite par personne

La limite de base vient de :

- `gestions_places['places_limites']`

Cette limite peut etre surchargee selon le profil adherent :

- `individuel` -> 1 place maximum ;
- `couple` -> 2 places maximum.

Cette surcharge s'applique uniquement en FO, ou la session designe la personne
qui s'inscrit. En BO, la session appartient a l'operateur : elle ne doit jamais
remplacer la limite configuree sur l'evenement pour l'adherent selectionne.

### Quota global evenement

Le controle compare `nombre_a_verifier` avec :

- `places_disponibles`
- `places_en_attentes_disponible`

Le cas modification (`id_activite`) tient compte :

- du statut precedent de l'activite ;
- du nombre d'inscrits avant modification.

### Doublons

Le backend bloque notamment :

- une nouvelle inscription d'un auteur deja inscrit sur le meme evenement ;
- certains doublons de participants famille selon la structure de `categorie_result`.

### Email

- validation serveur via `email_valide()` ;
- controles supplementaires via la couche anti-spam.

## Calcul de `nombre_a_verifier`

L'ordre logique est le suivant :

1. `data_form['nombre_participants']`
2. en multi famille, le nombre de membres coches dans `famille`
3. `array_post_inscrits`
4. `nb_inscrits`
5. derive depuis `categorie_result` si necessaire

### Cas multi famille

En mode multi avec famille active :

- la source de verite fonctionnelle est la selection `famille` ;
- la structure est normalisee avant comptage ;
- `ie_aplatir_liste_valeurs()` permet d'absorber les payloads imbriques issus du transport multi-etapes.

## Cas couverts par `ie_verifier_commons()`

- quotas globaux ;
- limites d'accompagnants ;
- selection famille vide ;
- doublons d'inscription ;
- email invalide ;
- anti-spam ;
- verification des noms de participants sur certains parcours simples.

### Recapitulatif CVT payant

Au dernier ecran d'un formulaire multi-etapes, Saisies peut conserver les
valeurs metier dans l'environnement du formulaire tout en ne republiant dans la
requete qu'un jeton `cvtm_prev_post` signe et opaque. Dans ce cas strict, un
tarif deja affiche et valide a l'etape precedente ne doit pas etre requalifie
en selection absente.

Cette exception ne vaut que pour le recapitulatif multi avec jeton opaque et
sans categorie explicitement republiee. Toute categorie presente reste
recalculee et controlee. La barriere de `ie_traiter_commons()` demeure
obligatoire avant toute activite, transaction ou notification.

## Anti-spam

### Mecanismes

- honeypot dynamique `input_<hash_horaire>` ;
- honeypot `checkbox_<hash_horaire>` ;
- champ `nobot` ;
- filtrage de contenu suspect.

### Signaux surveilles

- presence de `http` ;
- caracteres Han ou cyrilliques selon la regle actuelle ;
- domaines email bloques par defaut ;
- nom et prenom identiques dans certains cas anonymes.

## Points de vigilance

- Toute evolution de `nombre_a_verifier` doit etre repercutee dans `tests-plan.md`.
- Toute modification du honeypot ou des filtres anti-spam doit etre documentee ici.
- Les logs de verification utiles se trouvent principalement sous les traces `[IE_VERIFY]`.
