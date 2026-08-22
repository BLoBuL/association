# Logique de gestion des tarifs — Plugin blobul-ASSO_BO

> Document historique de cadrage sur la logique de tarifs.
> Pour une vue plus structurée et exploitable par les utilisateurs, voir :
> - [`categories_participation_financiere.md`](./categories_participation_financiere.md)
> - [`parametrage_evenements.md`](./parametrage_evenements.md)

> Fichiers concernés :
> - `formulaires/inc/inscription_evenement_saisies.php`
> - `formulaires/inc/inscription_evenement.php` (`generer_array_categories_participation`)
> - `formulaires/editer_asso_categorie_activite.php`
> - `base/association.php` (table `spip_asso_categories_activites`)

---

## 1. Structure d'une catégorie de tarif

Chaque tarif est une ligne dans `spip_asso_categories_activites` avec les champs clés :

| Champ | Valeurs possibles | Rôle |
|---|---|---|
| `type_inscrit` | `indifferent`, `adherent`, `non_adherent`, `couple`, `enfant`, `invite`, `benevole`, `special` | Public cible du tarif |
| `quantite` | `1` (individuel) / `> 1` (groupe) | Nombre de personnes couvertes |
| `statut` | `ok` / `desactive` | Actif ou non |
| `montant` | décimal | Montant en devise |

---

## 2. Les deux modes d'inscription

### Mode A — "Inscrits" (`champs_saisies_inscrits`)

Utilisé quand l'événement n'est **pas** en mode famille (ou pour les non-membres). L'inscripteur choisit un nombre de participants (`nb_inscrits`), et un fieldset est généré pour chacun.

**Convention de position (implicite) :**

| Position | Données pré-remplies depuis le profil | Types de tarifs proposés |
|---|---|---|
| inscrit_1 | Adhérent principal | Types du profil + `couple` |
| inscrit_2 | Conjoint | Types du profil **sans `benevole`** + `couple` |
| inscrit_3+ | Enfant 1…5 (`prenom_enfant_N`, `nom_famille`) | `['enfant', 'indifferent']` |

> ⚠️ Cette convention est implicite et fragile : si l'utilisateur n'a pas de conjoint et veut inscrire un collègue en deuxième, il sera traité comme un "conjoint". À discuter selon le besoin métier.

**Calcul des types selon le statut de l'auteur connecté :**

| Statut | `$array_type_adherents` de base |
|---|---|
| Membre actif + comité (`1comite`/`0minirezo`) | `['adherent', 'indifferent', 'benevole']` |
| Membre actif | `['adherent', 'indifferent']` |
| Non-membre ou non connecté | `['non_adherent', 'indifferent']` |

---

### Mode B — "Famille" (`champs_saisies_famille`)

Utilisé pour les adhérents inscrits avec leur famille. Les membres sont identifiés par leur **rôle explicite** (clé du tableau `$famille`).

**Membres disponibles dans le profil :**

| Clé | Source dans `spip_auteurs` | Types de tarifs proposés |
|---|---|---|
| `adherent` | `prenom`, `nom_famille`, `email`, `mobile` | Types du profil + `couple` (bénévole inclus si comité) |
| `conjoint` | `prenom_conjoint`, `nom_conjoint`, `email_conjoint`, `mobile_conjoint` | Types du profil **sans `benevole`** + `couple` |
| `enfant_1` … `enfant_5` | `prenom_enfant_N`, `date_naissance_enfant_N` | `['enfant', 'indifferent']` |
| `invite_1`, `invite_2` | `invite_1`, `invite_2` | `['invite', 'indifferent']` |

La résolution des types par rôle est faite par `association_types_tarifs_par_role($cle, $types_adherent)`.

---

## 3. Règle : `benevole` est un rôle personnel

Le tarif `benevole` est lié au statut comité de **l'adhérent lui-même**. Il ne se transmet pas aux membres accompagnants.

| Inscrit | `benevole` proposé ? | Raison |
|---|---|---|
| adherent / inscrit_1 | ✅ Oui (si comité) | Rôle propre de la personne |
| conjoint / inscrit_2 | ❌ Non | `benevole` retiré via `array_diff` |
| enfant_* / inscrit_3+ | ❌ Non | Types `['enfant', 'indifferent']` uniquement |
| invite_* | ❌ Non | Types `['invite', 'indifferent']` uniquement |

---

## 4. Logique de masquage des tarifs de groupe

Quand le **premier inscrit** sélectionne un tarif de **type groupe** (`quantite > 1`), les champs tarif des inscrits suivants sont masqués (via `afficher_si` côté saisies SPIP) **uniquement si ce tarif couvre leur rang**.

**Déroulé dans `champs_saisies_tarifs()` :**

1. Construction de `$tarifs_par_quantite` : IDs des tarifs avec `quantite > 1` qui sont dans `$array_type_adherents` de l'inscrit courant, indexés par quantité.
2. Si l'inscrit courant ≠ premier inscrit ET `$tarifs_par_quantite` non vide :
   - Pour chaque rang N, cherche les tarifs dont `quantite >= N`.
   - Si trouvé → `afficher_si = '@categorie_{premier_inscrit}@ !IN "{ids}"'`
   - Sinon → toujours visible.
3. Sinon : champ toujours visible.

**Conséquences par rôle (exemple : tarif `couple`, quantite=2) :**

| Rang | Rôle | Couvert ? | Raison |
|---|---|---|---|
| 1 | adherent | — | Premier inscrit, choisit le tarif |
| 2 | conjoint | ✅ `2 >= 2` → masqué | `couple` est dans ses types |
| 3 | enfant_1 | ❌ `2 < 3` → visible | ET `couple` absent de `['enfant','indifferent']` |
| 4 | enfant_2 | ❌ `2 < 4` → visible | Idem |

---

## 5. Référence des types de tarifs

| `type_inscrit` | Qui peut l'utiliser | Notes |
|---|---|---|
| `indifferent` | Tous | Toujours inclus dans toutes les listes |
| `adherent` | Membres actifs | Base des droits tarifaires membre |
| `non_adherent` | Non-membres, non connectés | Mutuellement exclusif avec `adherent` |
| `benevole` | adherent/inscrit_1 avec statut comité **uniquement** | Retiré pour conjoint/inscrit_2+ |
| `couple` | adherent + conjoint, inscrit_1 + inscrit_2 | Non proposé aux enfants/invités |
| `enfant` | enfant_1…5 (mode famille), inscrit_3+ (mode inscrits) | |
| `invite` | invite_1, invite_2 (mode famille uniquement) | |
| `special` | Aucun rôle automatique | Attribution manuelle uniquement |

---

## 6. Recommandations et incohérences identifiées

### ✅ CORRIGÉ — A : Mode inscrits : inscrit_3+ voient les tarifs `enfant`

inscrit_3+ utilisent désormais `['enfant', 'indifferent']` et inscrit_2 exclut `benevole`.

---

### ✅ CORRIGÉ — B : `$premier_inscrit` forcé à `adherent` en mode famille

`adherent` est toujours utilisé comme référence de masquage s'il est présent dans la sélection.

---

### ✅ CORRIGÉ — C : Catégories désactivées filtrées

`generer_array_categories_participation` ignore les catégories avec `statut !== 'ok'`.

---

### ✅ CORRIGÉ — E : Champ `nom` pré-rempli avec `nom_famille` pour inscrit_3+

Le nom de famille de l'adhérent est utilisé comme défaut pour les enfants.

---

### ✅ CORRIGÉ — Bénévole limité à l'adhérent principal

`benevole` est retiré via `array_diff` pour conjoint/inscrit_2 et n'apparaît pas pour enfants/invités.

---

### ⚠️ D — Tarif `couple` : ambiguïté individuel vs groupe

Le tarif `couple` peut être configuré en deux façons qui ont des effets très différents :

- **Individuel (`quantite=1`)** : adhérent ET conjoint voient et choisissent chacun le tarif `couple`. Pas de masquage. Deux paiements distincts.
- **Groupe (`quantite=2`)** : l'adhérent choisit, le conjoint est masqué. Un seul paiement couvre les deux.

**Recommandation :** Ajouter une note explicative dans le formulaire `editer_asso_categorie_activite` pour clarifier la différence selon `type_tarif`.

---

### ℹ️ F — `trierparMontant` : nom trompeur

La fonction trie par `quantite` décroissant en premier (les tarifs de groupe apparaissent avant les tarifs individuels), puis par `montant` décroissant. Le nom `trierparMontant` est inexact. Renommer en `trier_categories_tarifs` serait plus clair, mais attention à la rétrocompatibilité (`usort` l'appelle par nom).

---

### ℹ️ G — Mode famille : les invités n'ont pas de `nom`

`invite_1` et `invite_2` dans `$famille` n'ont que le champ `prenom`. Le formulaire demande `nom_invite_1` avec `obligatoire => 'oui'` et aucun défaut. L'utilisateur doit toujours saisir le nom de l'invité manuellement. Probablement intentionnel.

---

## 7. Tableau récapitulatif : qui voit quoi

| Inscrit | Mode | `benevole` | `couple` | Masqué si groupe sélectionné par adhérent ? |
|---|---|---|---|---|
| Adhérent (inscrit_1) | Les deux | ✅ si comité | ✅ | Non (c'est lui qui choisit) |
| Conjoint (inscrit_2) | Les deux | ❌ | ✅ | ✅ Oui si tarif groupe adhérent/couple le couvre |
| Enfant (inscrit_3+) | Inscrits | ❌ | ❌ | ❌ Non (tarif couple quantite=2 ne couvre pas rang 3+) |
| Enfant (enfant_N) | Famille | ❌ | ❌ | ❌ Non |
| Invité (invite_N) | Famille | ❌ | ❌ | ❌ Non |
| Non connecté | Inscrits | ❌ | ✅ pour 1 et 2 | Selon tarifs configurés |
