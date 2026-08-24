# Réfactoring terminé : responsables d’événement

> État au 24 août 2026 : réalisé dans le lot 68. Ce document conserve le
> diagnostic historique qui a motivé la suppression.

Les deux API ambiguës `liste_responsables_evenement()` et
`responsables_evenement()` ont été supprimées. Elles sont remplacées par :

- `association_evenements_responsables_ids()` pour les notifications, exports
  et affichages, avec un retour toujours plat `int[]` ;
- `association_evenements_responsables_choix()` pour préparer les choix et les
  valeurs par défaut du champ extra Agenda ;
- `association_evenements_normaliser_responsables()` pour relire sans perte les
  anciens formats CSV et sérialisés.

Les autorisations ne dépendent plus de cette liste de diffusion : elles passent
uniquement par le contrat SPIP des auteurs de l’article parent. Les requêtes
concaténées et les ressources SQL exposées aux appelants ont disparu. Le test
`tests/test_responsables_evenements.php` couvre les formats historiques, les
retours typés, l’initialisation d’un nouvel événement et l’absence de l’API
legacy dans le module.

But : lister les raisons pour lesquelles la fonction `liste_responsables_evenement` est à considérer comme legacy, proposer un plan d'assainissement et de suppression, et donner un correctif provisoire et des consignes de migration pour les appels existants.

---

## Constat rapide

- Fichier concerné : `inc/fonctions/liste_responsables_evenement.php`
- Usage répandu dans le plugin et dans des squelettes (ex. `prive/squelettes/contenu/voir_activites.html`, `inc/fonctions/facteur_envoyer_mail_activites.php`, `bank_presta_autorisations.php`, etc.).
- La fonction est fragile, retourne des formats hétérogènes (tableau ou chaîne vide), construit des requêtes SQL de façon peu sûre et n'est pas alignée sur les API d'autorisation modernes (`droit_auteur_evenements`).

> Recommandation principale : supprimer la fonction historique et assainir tous ses appels pour utiliser une API unique et robuste (ex. `droit_auteur_evenements()` ou une nouvelle fonction wrapper normalisée). On effectuera la suppression après migration progressive.

---

## Pourquoi considérer `liste_responsables_evenement` comme legacy

1. API ambiguë : la fonction peut retourner une chaîne vide ou un tableau structuré, ce qui oblige les appelants à gérer plusieurs cas.
2. Code fragile : la fonction fait des hypothèses sur le format des champs (ex. `responsables`) et utilise `implode` sur des valeurs qui ne sont pas forcément des tableaux.
3. Risque SQL : construction de clause `IN` sans cast systématique des id (risque d'injection ou d'erreur SQL si chaîne vide).
4. Incohérences : comportement différents selon qu'on passe `id_evenement` ou `id_article` — l'API ne garantit pas un format commun.
5. Existence d'une API plus moderne : `droit_auteur_evenements()` fournit déjà une logique d'autorisation claire (type d'accès `complet`/`restreint` + liste d'activités). Pour les décisions d'autorisation, c'est la référence.
6. Duplication d'efforts : plusieurs endroits du code font des adaptations / vérifications autour de `liste_responsables_evenement` — cela complique la maintenance.

---

## Problèmes concrets repérés

- `sql_fetsel('responsables', ...)` renvoie souvent une chaîne (ex: "12,34") ; la fonction fait ensuite `implode(',', $query_evenement)` -> bad type.
- Utilisation de `sql_count()` sur une variable qui peut être un tableau ou false (selon code) -> comportement indéfini.
- Retour parfois `''` et parfois `array('auteur_array' => [...])` ; les squelettes qui utilisent `|table_valeur{auteur_array}` s'attendent au second format, mais d'autres appels non.
- Certains utilisateurs/appels ne font pas de sanitation (cast int) des ids passés à la requête `IN`.

---

## Objectifs de la refactorisation

1. Standardiser l'API : une fonction stable qui retourne toujours le même type (préférer `array('auteur_array' => [...])`).
2. Sécuriser les requêtes SQL : cast `intval()` pour chaque id, éviter `IN()` vide.
3. Remplacer les usages d'autorisation par `droit_auteur_evenements()` lorsque l'objectif est une décision (autorisation). Garder une utilité d'affichage/contacts pour les squelettes uniquement si nécessaire.
4. Préparer une migration progressive : créer un wrapper de compatibilité qui retourne le format normalisé, puis migrer les appelants un à un et vérifier.
5. Enfin supprimer la fonction legacy une fois que tous les usages ont été migrés et testés.

---

## Correctif provisoire proposé (forme normalisée)

- Remplacer le contenu de `inc/fonctions/liste_responsables_evenement.php` par une version qui :
  - retourne toujours `array('auteur_array' => array())` (liste vide par défaut),
  - parse le champ `responsables` en CSV proprement, cast int, filtre >0,
  - exécute la requête SQL uniquement si la liste d'ids est non vide,
  - gère proprement le cas `id_article` (même format de retour).

Exemple d'implémentation (proposition) :

```php
<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

function liste_responsables_evenement($id_evenement = '', $id_article = '') {
    $result = array('auteur_array' => array());

    if (!empty($id_evenement)) {
        $id_evenement = intval($id_evenement);
        $row = sql_fetsel('responsables,inscription', 'spip_evenements', 'id_evenement=' . intval($id_evenement));
        if (!$row) return $result;
        if (!isset($row['inscription']) || intval($row['inscription']) != 1) return $result;
        $responsables_raw = trim($row['responsables'] ?? '');
        if ($responsables_raw === '') return $result;
        $ids = array();
        foreach (preg_split('/\s*,\s*/', $responsables_raw) as $v) {
            $v = trim($v);
            if ($v === '') continue;
            $ids[] = intval($v);
        }
        $ids = array_filter(array_unique($ids), function($x){ return $x > 0; });
        if (empty($ids)) return $result;
        $in = implode(',', $ids);
        $res = sql_select('id_auteur', 'spip_auteurs', "id_auteur IN ($in) AND statut_interne='ok'");
        if ($res) {
            $auteurs = array();
            while ($rowa = sql_fetch($res)) {
                $auteurs[] = intval($rowa['id_auteur']);
            }
            $result['auteur_array'] = $auteurs;
        }
        return $result;
    }

    if (!empty($id_article)) {
        $id_article = intval($id_article);
        $res = sql_select(
            'auteurs.id_auteur',
            'spip_auteurs AS auteurs JOIN spip_auteurs_liens AS lien ON auteurs.id_auteur = lien.id_auteur',
            "lien.objet='article' AND lien.id_objet=" . intval($id_article) . " AND auteurs.statut_interne='ok'"
        );
        if ($res) {
            $auteurs = array();
            while ($rowa = sql_fetch($res)) {
                $auteurs[] = intval($rowa['id_auteur']);
            }
            $result['auteur_array'] = $auteurs;
        }
        return $result;
    }

    return $result;
}
```

> Important : ce correctif standardise le format de retour. Tous les appelants doivent être adaptés pour consommer `['auteur_array']`.

---

## Plan de migration étape par étape (suggestion)

1. **Inventaire** : lister tous les appels à `liste_responsables_evenement` (grep) et noter leur comportement attendu (affichage vs autorisation vs mail).
2. **Ajout d'un wrapper compat** : modifier la fonction historique pour qu'elle retourne toujours le nouveau format (comme ci‑dessus) et log une note de dépréciation (debug) — ceci empêche les erreurs immédiates.
3. **Adaptation appelant par appelant** : pour chaque fichier listé, modifier l'appelant pour utiliser le nouveau format de façon explicite :
   - si l'appel est pour autorisation : remplacer par `droit_auteur_evenements()` lorsque pertinent ;
   - si l'appel est pour affichage/contacts : adapter le code pour consommer `['auteur_array']` ;
   - remplacer usages `is_array($resp)` par `isset($resp['auteur_array'])` ou `count($resp['auteur_array'])`.
4. **Tests manuels** : pour chaque modification, exécuter les tests manuels (voir_activites, envoi mail activité, BO compta, etc.).
5. **Activer stricte** : une fois tous les appelants mis à jour, supprimer la fonction legacy (ou remplacer son fichier par un alias qui lève une notice et renvoie à la nouvelle API).
6. **Nettoyage** : supprimer includes obsolètes, retirer checks `function_exists('liste_responsables_evenement')` et retirer code mort. Commit par étape.

---

## Liste des fichiers/appels détectés (préliminaire)

- `E:/#GIT/blobul-ASSO_BO/inc/fonctions/liste_responsables_evenement.php` (fonction)
- `E:/#GIT/blobul-ASSO_BO/prive/squelettes/contenu/voir_activites.html`
- `E:/#GIT/blobul-ASSO_BO/prive/squelettes/contenu/inc-voir_activites/bloc_info_evenement.html`
- `E:/#GIT/blobul-ASSO_BO/inc/fonctions/facteur_envoyer_mail_activites.php`
- `E:/#GIT/blobul-ASSO_BO/inscriptions_evenement.csv.html`
- `E:/#GIT/blobul-BANK/bank_presta_autorisations.php`
- `E:/#GIT/blobul-ASSO_FO/inclure/inc-liste_items_responsables.html`
- d'autres squelettes FO/BO qui utilisent `#ID_EVENEMENT|liste_responsables_evenement` ou `liste_responsables_evenement()`

> Remarque : ceci est un inventaire préliminaire. Faire un `grep -R "liste_responsables_evenement"` complet avant chaque PR.

---

## Tests recommandés

- Avant modification : sauvegarder l'état (branche git).
- Après correctif de la fonction :
  - Tester l'affichage de la fiche événement (BO) — responsables affichés correctement.
  - Tester l'envoi des mails d'activité (facteur) — destinataires corrects.
  - Tester les autorisations qui utilisaient la fonction (fallbacks) et s'assurer qu'ils continuent de fonctionner.
  - Vérifier logs SQL pour éviter erreurs "You have an error in your SQL syntax" liées à une clause IN vide.

---

## Risques & rollback

- Risque : si on modifie la fonction sans adapter tous les appels, certains squelettes pourraient attendre l'ancien format et casser.
- Rollback : revert du commit / branche. C'est pourquoi on effectue la migration en deux temps : wrapper normalisé d'abord, puis adaptation des appelants.

---

## Notes finales / proposition

- Je peux appliquer le correctif normalisé ci‑dessus et lancer un script de recherche/remplacement pour adapter automatiquement les usages simples (ex. `if (is_array($resp))` -> `if (!empty($resp['auteur_array']))`).
- Je recommande de faire la migration en plusieurs PRs :
  1. corriger la fonction pour normaliser le retour (compat wrapper),
  2. adapter les squelettes et les appels dans le plugin (PR par module / zone),
  3. supprimer la fonction legacy.

Dis‑moi si tu veux que j'applique maintenant le correctif proposé sur `inc/fonctions/liste_responsables_evenement.php` et si je lance l'inventaire complet des appels (grep) pour produire la liste exhaustive des endroits à modifier.

