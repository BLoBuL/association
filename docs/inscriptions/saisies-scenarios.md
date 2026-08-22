# Cartographie des saisies par scénario

## Axes de variation

Les champs affichés dépendent de la combinaison des axes suivants :

| Axe | Valeurs |
|---|---|
| **Mode** | `public` · `prive` · `multi_public` · `multi_prive` |
| **Connecté** | oui (visiteur_session) · non |
| **Statut adhérent** | ok-admin · ok · non-ok / non connecté |
| **Type inscrits événement** | `public` · compte uniquement · FIAFE |
| **Payant** | oui · non |
| **Accompagnants** | oui · non |
| **Config accompagnants** | `tout` · `membre_famille` |
| **Famille active** | oui (membre_famille + auteur connecté) · non |
| **Multi-étapes** | oui · non |
| **Création / modification** | création · modification (`modif=oui` + id_activite) |

---

## Saisies produites selon les conditions

### 1. Sélection du type d'inscrit (`select_type_inscrit`)

| Contexte | Saisie produite |
|---|---|
| **BO** (`prive` / `multi_prive`) | `selection` visible : membre / non_membre / membre_reseau_fiafe / public |
| **FO** connecté, adhérent ok | `hidden` → valeur calculée `membre` |
| **FO** connecté, adhérent non ok | `hidden` → valeur calculée `non_membre` |
| **FO** non connecté + paramètre `association` ou token FIAFE | `hidden` → `membre_reseau_fiafe` |
| **FO** non connecté, événement public | `hidden` → `public` |

En BO, deux selects supplémentaires apparaissent conditionnellement :
- `membre` (select2, liste adhérents ok) — affiché si `select_type_inscrit==membre`
- `non_membre` (select2, liste non-adhérents) — affiché si `select_type_inscrit==non_membre`

---

### 2. Champs identité publique (`prenom_inscrit`, `nom_inscrit`, `email_inscrit`, `tel_inscrit`)

Produits par `generer_saisies_info_public()`.

| Condition | Affiché |
|---|---|
| `select_type_inscrit` = `public` | oui |
| `select_type_inscrit` = `membre_reseau_fiafe` | oui |
| Token inscription FIAFE présent (`eligibilite_token_inscription`) | oui |
| FO + événement de type `public` + non connecté | oui |
| Autres cas | non |

En BO, ces champs sont toujours inclus mais conditionnés visuellement (`afficher_si`).

---

### 3. Nombre d'inscrits (`nb_inscrits`)

Saisie pertinente uniquement pour les **événements gratuits**.

| Condition | Saisie produite |
|---|---|
| Gratuit + **sans accompagnants** | `hidden` = 1 |
| Gratuit + **avec accompagnants** + quota_perso = 1 | `hidden` = 1 |
| Gratuit + **avec accompagnants** + quota_perso > 1 | `selection` (1..quota_perso) |

`quota_perso` = `gestions_places['places_limites']` si accompagnants actifs, sinon 1.

Visibilité conditionnelle en BO : `afficher_si` = `@select_type_inscrit@=='public' || @select_type_inscrit@=='membre_reseau_fiafe'`.

---

### 4. Sélection famille (`famille`)

Produite uniquement si `config_accompagnants == 'membre_famille'` ET auteur connecté.

| Accompagnants | Saisie produite |
|---|---|
| **oui** | `checkbox` (choix multiples membres de la famille) |
| **non** | `radio` (choix unique) |

Visibilité conditionnelle en BO : `afficher_si` = `@select_type_inscrit@=='membre' || @select_type_inscrit@=='non_membre'`.

Structure `data_famille` pour chaque membre : `adherent`, `conjoint`, `enfant_1`..`enfant_5`, `invite_1`, `invite_2`.

---

### 5. Champs tarifs (`categorie` / `categorie[ID]`)

Pertinents uniquement pour les **événements payants**.

| Famille active | Accompagnants | Saisie produite |
|---|---|---|
| **oui** + auteur connecté | — | `checkbox` par tarif, data = membres de la famille (`categorie[ID][]`) |
| **non** | **oui** | `selection` (nb personnes) par tarif (`categorie[ID]`) |
| **non** | **non** | `selection` globale unique (`categorie`) |

**Filtrage par type d'adhérent** (catégories affichées selon statut) :

| Statut | Types de catégories affichés |
|---|---|
| ok + admin/rédacteur | adherent + indifferent + benevole |
| ok (standard) | adherent + indifferent |
| non ok / non connecté | non_adherent + indifferent |

**Tarifs de groupe** (quantite > 1) : pour les inscrits N>1 en mode multi-inscrits, si le tarif du premier inscrit est un tarif de groupe, la saisie du tarif est masquée pour les suivants (`afficher_si` dynamique).

Visibilité conditionnelle en BO : `afficher_si` = `@select_type_inscrit@=='public' || @select_type_inscrit@=='membre_reseau_fiafe'`.

---

### 6. Fieldsets participants (mode multi uniquement)

Produits uniquement en `multi_public` ou `multi_prive` via `_saisies_par_etapes`.

#### Mode famille (`saisie_famille_active == 'oui'` + auteur connecté)

Un fieldset `fieldset_inscrit_{cle}` par membre sélectionné dans `famille`.

Champs du fieldset :
- `prenom_{cle}` — input, préchargé depuis auteur
- `nom_{cle}` — input, préchargé depuis auteur
- Champs info_supplementaire configurés (voir §8)
- Tarif `categorie_{cle}` si payant

#### Mode inscrits libres (N > 0, non famille)

Un fieldset `fieldset_inscrit_N` par inscrit (1..nb_inscrits).

Champs du fieldset :
- `prenom_inscrit_N` — input
- `nom_inscrit_N` — input
- Champs info_supplementaire (voir §8)
- Tarif `categorie_inscrit_N` si payant

---

### 7. Champs de saisie info supplémentaire (`info_supplementaire`)

Configurés au niveau de l'événement (`affichage_dans_activites['info_supplementaire']`).
Produits par `champs_saisies_info_supplementaire()`.

| Clé config | Champ produit | Obligatoire |
|---|---|---|
| toujours (1er inscrit) | `email_{id}` (email) | oui (1er) / non (autres) |
| toujours (1er inscrit) | `telephone_{id}` (input) | non |
| `document_identite` | `type_document_identite_{id}` (radio passeport/carte_id) | oui |
| `document_identite` | `numero_document_identite_{id}` (input) | oui |
| `document_identite` | `date_expiration_document_identite_{id}` (date) | oui |
| `document_identite` | `lieu_naissance_{id}` (input) | oui |
| `date_naissance` | `date_naissance_{id}` (date) | oui |
| `nationalite` | `nationalite_{id}` (input) | oui |
| `fonction` | `fonction_{id}` (input) | oui |
| `entreprise` | `entreprise_{id}` (input) | oui |
| valeur libre | champ input dynamique nommé depuis la valeur | oui |

---

### 8. Noms des accompagnants (`nom_participants`)

`textarea` produit uniquement si :
- formulaire **simple** (non multi)
- `saisie_famille_active != 'oui'`
- `accompagnants == true`

---

### 9. Commentaire (`commentaire`)

`textarea` produit si `meta_cfg_event_message_responsable` est défini et != `'non'`.

---

### 10. Annotation privée (`annotation`)

`textarea` produit uniquement si :
- mode **BO** (`prive` ou `multi_prive`)
- `autoriser('voir_activites', 'evenement', $id_evenement)` retourne vrai

---

### 11. Champs CGU et condition d'inscription

> Gérés côté squelette FO, pas dans le backend PHP. Non présents dans `ie_charger_commons`.

Conditions habituelles dans les squelettes :
- `conditions_generales` (case) : FO + non connecté + article CGU existant
- `condition_inscription` (case) : si `affichage_dans_activites['condition_inscription'] == 'oui'`

---

### 12. Champs cachés techniques

| Champ | Condition |
|---|---|
| `id_activite` (hidden) | si modification (`id_activite` présent) |
| `nb_inscrits` (hidden = 1) | gratuit sans accompagnants |
| `select_type_inscrit` (hidden) | FO uniquement |
| Anti-spam : `input_{hash}`, `checkbox_{hash}`, `nobot` | toujours (FO) |

---

## Matrice des scénarios principaux

| # | Mode | Connecté | Statut | Payant | Accompagnants | Config acco | Résumé des saisies |
|---|---|---|---|---|---|---|---|
| 1 | public | non | — | non | non | tout | identité publique + hidden nb=1 + CGU |
| 2 | public | non | — | non | oui | tout | identité publique + selection nb + textarea noms + CGU |
| 3 | public | oui | ok | non | non | tout | hidden select_type + hidden nb=1 |
| 4 | public | oui | ok | non | oui | tout | hidden select_type + selection nb |
| 5 | public | oui | ok | non | oui | membre_famille | hidden select_type + checkbox/radio famille |
| 6 | public | oui | ok | oui | non | tout | hidden select_type + selection categorie globale |
| 7 | public | oui | ok | oui | oui | tout | hidden select_type + selection nb par tarif |
| 8 | public | oui | ok | oui | oui | membre_famille | hidden select_type + checkbox famille par tarif |
| 9 | public | oui | non-ok | oui | non | tout | identité publique + selection categorie (non_adherent) |
| 10 | multi_public | oui | ok | oui | oui | membre_famille | select famille + fieldsets par membre + tarif par membre |
| 11 | multi_public | non | — | oui | oui | tout | identité publique + fieldsets inscrit_N + tarif par inscrit |
| 12 | prive | — | — | non | non | tout | select type + liste membres + hidden nb=1 |
| 13 | prive | — | — | non | oui | tout | select type + liste membres + selection nb + textarea noms |
| 14 | prive | — | — | oui | non | tout | select type + liste membres + selection categorie |
| 15 | prive | — | — | oui | oui | membre_famille | select type + liste membres + checkbox famille par tarif + annotation |
| 16 | multi_prive | — | — | oui | oui | membre_famille | select type + liste membres + fieldsets famille + tarif par membre + annotation |

---

## Pipeline d'extension

`ie_charger_commons` expose un pipeline **`association_inscription_evenement_charger`** permettant d'injecter ou modifier :
- `saisies_general`
- `saisies_tarifs`
- `saisies_modalites`
- `saisies_hidden`
- `data_activite`
- `info_adherent`

Un plugin tiers (ex: `blobul-FIAFE_client`) peut ainsi enrichir les saisies sans dupliquer le formulaire.

