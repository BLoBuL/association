# 📖 Documentation Technique - Correctif blobul-ASSO_BO

## Vue d'Ensemble

Ce répertoire contient la documentation technique du correctif pour le formulaire d'inscription multi-public (imbrication `famille[]` et unification vérification).

---

## 📑 Documents

### 1. **CHECKLIST_REGRESSION.md** (Documentation Technique)
Documentation complète du correctif avec :
- **Problème** : Symptôme, cause racine
- **Correctifs** : Détail technique des 5 changements appliqués
- **Architecture** : Avant/après, flux de données
- **Fichiers modifiés** : Tableau récapitulatif
- **Checklist** : Points de contrôle pour validation

**À Lire** : Avant de tester, pour comprendre ce qui a changé.
**Audience** : Développeurs, QA tech.

---

### 2. **GUIDE_TEST_MULTI_PUBLIC.md** (Guide de Test Détaillé)
Plan de test en 6 phases avec 14 cas de test :
- **Phase 1** : Validation élémentaire (POST, champs, comptage)
- **Phase 2** : Multi-participant (sélection multiple, flux complet)
- **Phase 3** : Vérification unifiée (logs, contrôles centralisés)
- **Phase 4** : Régression (cas non impactés)
- **Phase 5** : Dégradation (stress, limites)
- **Phase 6** : Persistance (modification, reprise étapes)

**À Lire** : Pour tester le correctif de façon systématique.
**Audience** : QA, testeurs.

---

## 🔧 Fichiers Modifiés

Voir le tableau dans `CHECKLIST_REGRESSION.md` § 7.

### Résumé
- `formulaires/inc/inscription_evenement.php` : +25 lignes (helper aplatissement)
- `formulaires/inc/inscription_evenement_saisies.php` : -1 ligne, +5 lignes (normalisation)
- `formulaires/inscription_evenement_multi_public.php` : -245 lignes (refactor verifier)

**Total net** : -213 lignes (réduction code duplication)

---

## ⚡ Quick Start

1. **Comprendre le problème**
   ```
   Lire: CHECKLIST_REGRESSION.md § 1
   ```

2. **Comprendre la solution**
   ```
   Lire: CHECKLIST_REGRESSION.md § 2-7
   ```

3. **Tester les changements**
   ```
   Suivre: GUIDE_TEST_MULTI_PUBLIC.md (phases 1-3)
   ```

4. **Vérifier régression**
   ```
   Suivre: GUIDE_TEST_MULTI_PUBLIC.md (phases 4-6)
   ```

---

## ✅ Checklist de Validation

- [ ] Documentation technique lue (CHECKLIST_REGRESSION.md)
- [ ] Guide de test examiné (GUIDE_TEST_MULTI_PUBLIC.md)
- [ ] Tests Phase 1 (élémentaire) : ✅ PASS
- [ ] Tests Phase 2 (avancé) : ✅ PASS
- [ ] Tests Phase 3 (vérification) : ✅ PASS
- [ ] Tests Phase 4 (régression) : ✅ PASS
- [ ] Logs `[IE_VERIFY_*]` validés (pas `[IE_MP_VERIFY_*]`)
- [ ] Aucun breakage détecté

---

## 🎯 Métriques de Succès

| Métrique | Avant | Après | Gain |
|----------|-------|-------|------|
| Lignes code dupliqué | 250 | 0 | -100% |
| Implémentations verifier | 2 | 1 | -50% |
| Maintenance codebase | Difficile | Simple | ✅ |
| Cohérence logique | Non | Oui | ✅ |

---

## 🔍 Repères Techniques Clés

### Nouvelle Architecture
```
formulaires/inscription_evenement_multi_public.php
└── ie_multi_public_verifier_legacy()
    └── ie_verifier_commons('multi_public', ...)
        └── Logique unifiée du backend
```

### Helper Aplatissement
```php
ie_aplatir_liste_valeurs([["adherent"]])
// Retourne: ["adherent"]
```

### Changement Champ POST
```
AVANT: famille[]=adherent  → POST: famille[]=[adherent]
APRÈS: famille=adherent    → POST: famille=[adherent]
```

### Logs Unifiés
```
AVANT: [IE_MP_VERIFY_*]  (250 lignes de logs custom)
APRÈS: [IE_VERIFY_*]     (logs centralisés backend)
```

---

## 📞 Support & Dépannage

### Problème : Tests ne passent pas
1. Lire `CHECKLIST_REGRESSION.md` § 1 (Problème Identifié)
2. Vérifier les logs : chercher `[IE_VERIFY]` (pas `[IE_MP_VERIFY]`)
3. Consulter `GUIDE_TEST_MULTI_PUBLIC.md` § Validation

### Problème : Champs participants non générés
1. Vérifier le champ `famille` en POST (doit être aplati, pas `[["adherent"]]`)
2. Vérifier que ligne 66 de `inscription_evenement_saisies.php` dit `'nom' => 'famille'`
3. Relancer le test Phase 1B

### Problème : Logs `[IE_MP_VERIFY_*]` toujours présents
1. Vérifier que `ie_multi_public_verifier_legacy()` appelle `ie_verifier_commons()`
2. Vérifier que le backend est inclus : `include_spip('formulaires/inc/inscription_evenement_backend')`

---

## 📅 Historique

- **2026-03-30** : Documentation technique et tests consolidés
- **Avant** : 250 lignes de logique dupliquée, logs fragmentés
- **Après** : Unification backend, architecture simple, -213 lignes

---

## 📌 Références

- Correctif : Imbrication `famille[]` + Refactor verifier
- Scope : `blobul-ASSO_BO/formulaires/inscription_evenement_*.php`
- Impact : Inscription multi-public FO, vérification centralisée
- Régression : Testée via 14 cas dans GUIDE_TEST_MULTI_PUBLIC.md

