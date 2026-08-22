# Inventaire des champs

## Champs communs

- `id_evenement`
- `id_activite`
- `modif`
- `select_type_inscrit`
  - valeurs usuelles : `membre`, `non_membre`, `membre_reseau_fiafe`, `public`
- `membre`
- `non_membre`
- `prenom_inscrit`
- `nom_inscrit`
- `email_inscrit`
- `tel_inscrit`
- `commentaire`
- `annotation`
- `notifier`
- `notifier_adherent`
- `conditions_generales`
- `condition_inscription`
- `nobot`

## Champs de controle anti-spam

- `nobot`
- `input_<hash_horaire>`
- `checkbox_<hash_horaire>`

Ces champs sont controles cote serveur et ne doivent pas etre reutilises pour de la logique metier.

## Champs participants en mode multi

### Mode quantite

- `nb_inscrits`
- champs dynamiques par inscrit :
  - `prenom_inscrit_1`, `nom_inscrit_1`, `email_inscrit_1`, ...
  - `prenom_inscrit_2`, `nom_inscrit_2`, etc.

### Mode famille

- `famille`
  - tableau de cles participants selectionnees ;
  - exemples : `['adherent']`, `['adherent', 'conjoint']`.
- champs dynamiques suffixes par membre :
  - `prenom_adherent`, `nom_adherent`, `email_adherent`, ...
  - `prenom_conjoint`, `nom_conjoint`, ...
  - `prenom_enfant_1`, `nom_enfant_1`, ...

### Remarque importante

Le nom de champ documente et attendu est `famille`.
Des structures imbriquees peuvent encore apparaitre lors du transport multi-etapes, mais elles sont normalisees avant verification et traitement.

## Categories et tarifs

### Formulaire simple

- `categorie`

### Formulaire multi

- `categorie[ID_CATEGORIE]`

Selon le contexte, la valeur peut representer :

- une quantite ;
- une liste d'identifiants de participants ;
- une selection participant -> categorie apres normalisation.

## Champs techniques multi-etapes

- `cvtm_prev_post`
  - serialisation des etapes precedentes ;
  - decodee puis fusionnee par le backend.
  - son `nb_inscrits` pilote aussi la collecte des champs dynamiques de l'etape courante, notamment `categorie_inscrit_N`.

## Champs calcules

Ces champs ne sont pas saisis directement par l'utilisateur.

- `nombre_participants`
- `categorie_result`
- `transaction`
- `montant_total`
- `participants_json`
- `nom_participants`
- `id_participants`

## Regle `id_auteur`

### Front-office

Pour `public` et `multi_public` :

- auteur de session si connecte ;
- sinon `0`.

### Back-office

Pour `prive` et `multi_prive` :

- auteur issu du champ `membre` ou `non_membre` ;
- sinon `0`.

## Conventions de maintenance

- Tout changement de nom ou de structure doit etre reporte ici.
- Tout changement du payload multi doit etre reverifie dans `tests-plan.md`.
- Tout changement des regles de calcul doit etre reporte dans `validation.md`.
