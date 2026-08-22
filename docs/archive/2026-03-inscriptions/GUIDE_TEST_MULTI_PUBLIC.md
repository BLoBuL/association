# 🧪 Guide de Test Technique - Correctif Inscription Multi-Public

## Vue d'Ensemble

Ce guide détaille les tests à effectuer pour valider le correctif de l'imbrication `famille[]` et l'unification de la vérification.

---

## Phase 1 : Validation Élémentaire

### Test 1A : Checkbox Famille - Structure POST
**Objectif** : Vérifier que le champ `famille` envoie bien un tableau aplati.

**Setup**
- Configuration : Événement payant, multi-étapes, famille active
- Étape 1 : Cocher un membre famille (ex: "adherent")

**Validation**
- ✅ POST contient `famille=adherent` (pas `famille[]=adherent` ni `famille[][]=adherent`)
- ✅ Logs : vérifier dans `[IE_VERIFY]` : `nb_famille_cochee=1`

**Cas d'erreur**
- ❌ POST : `famille[]` ou `famille[][]` → ancienne version, recheck paquet.xml
- ❌ Logs : `nb_famille_cochee=0` → aplatissement non appliqué

---

### Test 1B : Génération des Champs Participants
**Objectif** : Vérifier que les champs de saisie du participant s'affichent après sélection famille.

**Setup**
- Reprendre setup Test 1A
- Passer à l'étape 2 du formulaire

**Validation**
- ✅ Étape 2 affiche un fieldset avec champs `prenom_adherent`, `nom_adherent`
- ✅ Les champs sont préremplis avec les données du profil (si dispo)

**Cas d'erreur**
- ❌ Étape 2 n'affiche rien → aplatissement manquant dans `champs_saisies_famille()`
- ❌ Champs mal nommés (ex: `prenom_0` au lieu de `prenom_adherent`) → aplatissement non appliqué

---

### Test 1C : Comptage des Participants
**Objectif** : Vérifier que `nombre_participants` est calculé correctement.

**Setup**
- Reprendre setup Test 1A + compléter étape 2
- Valider et soumettre le formulaire

**Validation**
- ✅ Logs (recapitulatif) : `nombre_participants: 1`
- ✅ Pas d'erreur "aucun membre sélectionné"
- ✅ Inscription créée avec 1 participant

**Cas d'erreur**
- ❌ Logs : `nombre_participants: 0` → normalisation POST cassée
- ❌ Erreur : "Vous devez sélectionner au moins un membre" → logique comptage cassée

---

## Phase 2 : Validation Familiale Avancée

### Test 2A : Multi-Sélection Famille
**Objectif** : Vérifier la sélection de plusieurs membres.

**Setup**
- Configuration : Événement payant, multi-étapes, famille active
- Étape 1 : Cocher "adherent" ET "conjoint"

**Validation**
- ✅ POST : `famille=["adherent", "conjoint"]`
- ✅ Étape 2 : deux fieldsets générés (un pour adherent, un pour conjoint)
- ✅ Logs : `nb_famille_cochee=2`
- ✅ Logs : `nombre_participants=2`

**Cas d'erreur**
- ❌ Seul un fieldset affiché → boucle de génération cassée
- ❌ Logs : `nombre_participants=1` → comptage incorrect

---

### Test 2B : Saisie Complète Multi-Participant
**Objectif** : Vérifier le flux complet multi-participant.

**Setup**
- Reprendre setup Test 2A
- Compléter les deux fieldsets (prenom, nom, email, téléphone, etc.)
- Sélectionner tarifs
- Valider l'étape 2

**Validation**
- ✅ Pas d'erreur de validation
- ✅ Étape 3 (récapitulatif) affiche les 2 participants
- ✅ Montant total correct (pour les 2 participants)
- ✅ Inscription créée avec `nombre_inscrits: 2`

**Cas d'erreur**
- ❌ Erreur de validation sur les champs → structure POST incorrecte
- ❌ Récapitulatif incomplet → formateur multi cassé
- ❌ Montant incorrect → calcul catégories cassé

---

## Phase 3 : Vérification Unifiée

### Test 3A : Logs IE_VERIFY (Nouveau Format)
**Objectif** : Vérifier que la vérification utilise bien `ie_verifier_commons()`.

**Setup**
- Reprendre setup Test 1A
- Soumettre étape 1

**Validation**
- ✅ Logs contiennent `[IE_VERIFY_INPUT]` (pas `[IE_MP_VERIFY_ENTREE]`)
- ✅ Logs contiennent `[IE_VERIFY]` avec infos unifiées
- ✅ Pas de logs `[IE_MP_VERIFY_*]` (ancien format)

**Exemple de logs attendus**
```
[IE_VERIFY_INPUT] mode=multi_public id_evenement=224
[IE_VERIFY] sources mode_multi=oui famille_active=oui nb_famille_cochee=1
[IE_VERIFY] calcul nombre_inscrits=1
[IE_VERIFY_RETURN_NONE] aucune_erreur_detectee
```

---

### Test 3B : Vérifications Communes (Anti-Spam, Doublons, Quotas)
**Objectif** : Vérifier que les contrôles centralisés fonctionnent.

#### 3B1 : Anti-Spam
**Setup**
- Multi-public avec famille
- Saisir un email valide mais spam-like

**Validation**
- ✅ Logs : `[IE_VERIFY]` contient infos spam
- ✅ Erreur levée si détection

#### 3B2 : Doublon Auteur
**Setup**
- Utilisateur connecté (id_auteur=1)
- Événement avec inscription existante pour cet auteur
- Essayer d'inscrire à nouveau (modification via id_activite=0)

**Validation**
- ✅ Erreur : "Vous êtes déjà inscrit à cet événement"

#### 3B3 : Quota Dépassé
**Setup**
- Événement avec places limitées (ex: 2 places total)
- Sélectionner 3 membres famille

**Validation**
- ✅ Erreur : "Pas assez de places disponibles"
- ✅ Logs : `[IE_VERIFY] quotas places_limites=2 nombre_a_verifier=3`

---

## Phase 4 : Régression - Cas Non Impactés

### Test 4A : Gratuit Simple (Aucun Impact)
**Objectif** : Vérifier qu'aucune régression sur les cas gratuits simples.

**Setup**
- Événement gratuit, simple (pas multi, pas famille)
- 1 inscription simple

**Validation**
- ✅ Inscription créée
- ✅ Aucune erreur
- ✅ Logs OK

---

### Test 4B : Payant Simple, Accompagnants (Impact Indirect)
**Objectif** : Vérifier que les accompagnants (sans famille) restent OK.

**Setup**
- Événement payant, simple, accompagnants=oui, famille=non
- Sélectionner "2 personnes"
- Saisir noms participants

**Validation**
- ✅ Validation OK
- ✅ Paiement OK
- ✅ Inscription créée avec 2 participants

---

### Test 4C : Back-Office - Créer Inscription
**Objectif** : Vérifier qu'aucune régression en BO.

**Setup**
- BO, créer inscription pour un adhérent
- Configuration payante avec tarifs

**Validation**
- ✅ Inscription créée
- ✅ Même verifier commons utilisée
- ✅ Logs `[IE_VERIFY]` (pas `[IE_MP_VERIFY]`)

---

## Phase 5 : Dégradation et Limite

### Test 5A : Très Nombreux Participants (Stress)
**Objectif** : Vérifier les limites de performance.

**Setup**
- Multi-famille avec sélection maximale (8 membres : adherent+conjoint+5 enfants+2 invités)
- Tous les champs remplis
- Validation

**Validation**
- ✅ Performance acceptable (< 5s de traitement)
- ✅ POST pas trop volumineux
- ✅ BDD update cohérente

**Cas limite**
- ⚠️ Lent mais OK : 10-20s acceptable pour cas extrême

---

### Test 5B : Sélection Famille Vide
**Objectif** : Vérifier gestion du cas "pas de sélection".

**Setup**
- Multi-famille active
- Étape 1 : aucune case cochée
- Soumettre

**Validation**
- ✅ Erreur : "Vous devez sélectionner au moins un membre de la famille"
- ✅ Logs : `famille_count=0` → erreur levée

---

## Phase 6 : Données Persistantes

### Test 6A : Modification d'Inscription Multi-Famille
**Objectif** : Vérifier que modification recharge et traite correctement les données.

**Setup**
- Créer inscription multi-famille (Test 2B)
- Aller en modification (si UI prévue)

**Validation**
- ✅ Étape 1 : sélections famille préchargées
- ✅ Étape 2 : champs participants préchargés
- ✅ Modification OK, inscription mise à jour

---

### Test 6B : Reprise Multi-Étapes après Erreur
**Objectif** : Vérifier que `cvtm_prev_post` (sauvegarde étapes) fonctionne.

**Setup**
- Étape 1 : sélectionner famille, valider
- Étape 2 : saisir données, essayer de valider avec email invalide
- Corriger email, revalider

**Validation**
- ✅ Données étape 1 conservées (famille sélection visible)
- ✅ Correction email suffisante pour passer
- ✅ Inscription créée au final

---

## Résumé des Points de Contrôle

| Phase | Test | Cible | Validation |
|-------|------|-------|-----------|
| 1 | 1A | Structure POST famille | ✅ `famille=adherent` |
| 1 | 1B | Génération champs | ✅ `prenom_adherent` affiché |
| 1 | 1C | Comptage participants | ✅ `nombre_participants=1` |
| 2 | 2A | Multi-sélection | ✅ `nb_famille_cochee=2` |
| 2 | 2B | Flux complet 2 pers | ✅ Inscription OK pour 2 |
| 3 | 3A | Format logs `[IE_VERIFY]` | ✅ Pas de `[IE_MP_VERIFY_*]` |
| 3 | 3B | Verifications communes | ✅ Anti-spam, quota OK |
| 4 | 4A | Gratuit simple (régression) | ✅ OK |
| 4 | 4B | Payant accompagnants | ✅ OK |
| 4 | 4C | BO créer inscription | ✅ OK |
| 5 | 5A | Stress (8 membres) | ✅ Performance OK |
| 5 | 5B | Pas de sélection famille | ✅ Erreur attendue |
| 6 | 6A | Modification inscription | ✅ Données préchargées |
| 6 | 6B | Reprise multi-étapes | ✅ Données conservées |

---

## Critères de Succès Global

✅ **Phase 1 (Élémentaire)** : 3/3 tests PASS → Correctif de base OK
✅ **Phase 2 (Avancé)** : 2/2 tests PASS → Multi-participant OK
✅ **Phase 3 (Vérification)** : 2/2 tests PASS → Unification OK
✅ **Phase 4 (Régression)** : 3/3 tests PASS → Pas de breakage
⚠️ **Phase 5 (Limite)** : 2/2 tests PASS (ou dégradation acceptable)
✅ **Phase 6 (Persistance)** : 2/2 tests PASS → Données OK

**PASS Global** : Phases 1-4 + au moins l'une de 5/6 → Correction validée

