# TODO — Revue complète de la navigation du plugin

## Objectif
Uniformiser le fil d'ariane (`hierarchie/`) et la colonne de navigation gauche (`navigation/`)
pour toutes les pages du plugin, en suivant prioritairement le backoffice standard SPIP
(structure des squelettes privés + conventions visuelles de navigation).

## Convention cible

### Fil d'ariane
```
Racine du site > [Section] > [Sous-section si applicable] > Page courante (sans lien)
```
- Dernière entrée = page courante, **pas de lien**.
- Utiliser `<strong class="on">...</strong>` pour marquer la page courante.
- Utiliser une `BOUCLE_*(OBJET){id_objet=#ENV{id_objet}}{tout}` pour récupérer un titre dynamique.
- Textes via clés `<:asso:...:>`, sauf "Racine du site" (convention en dur dans tout le plugin).

### Navigation gauche
- Commencer chaque squelette `navigation/*.html` par
  `<INCLURE{fond=prive/squelettes/navigation/dist,env}>`.
- Toujours inclure un **lien de retour** vers la page parente.
- Réutiliser les blocs partiels existants (`inc-voir_activites/`, `inc-adherents/`, etc.).
- Utiliser `#BOITE_OUVRIR{'', raccourcis}` pour les liens d'action.
- Harmoniser le contexte d'identifiant (`id` vs `id_evenement`) et éviter les mélanges dans une même page.

---

## État courant par page

### ✅ Pages avec hierarchie + navigation correctes (modèle de référence)

| Page | hierarchie | navigation | Notes |
|------|-----------|------------|-------|
| `voir_activites` | ✅ créé 2026-03-27 | ✅ existant | Modèle de référence navigation |
| `editer_asso_activite` | ✅ corrigé 2026-03-27 | ✅ refait 2026-03-27 | Modèle de référence |
| `activites` | ✅ existant | ✅ existant | |
| `adherents` | ✅ existant | ✅ existant | |
| `cotisations` | ✅ existant | ✅ existant | |
| `categories_activites` | ✅ existant | ✅ existant | |
| `categories_cotisation` | ✅ existant | ✅ existant | |
| `configurer_association` | ✅ existant | ✅ existant | |
| `analyse_compta_activites` | ✅ existant | ✅ existant | |
| `editer_asso_cotisation` | ✅ existant | ✅ existant | À vérifier : lien retour |
| `editer_asso_categorie_activite` | ✅ existant | ✅ existant | À vérifier : lien retour |
| `editer_asso_categorie_cotisation` | ✅ existant | ✅ existant | À vérifier : lien retour |

---

### ❌ Pages sans hierarchie (fil d'ariane manquant)

#### Groupe Activités
| Page | hierarchie | navigation | Fil d'ariane cible |
|------|-----------|------------|-------------------|
| `suivi_activites` | ✅ lot 64 | ✅ lot 64 | Racine > Activités > Suivi des activités |
| `export_activites` | ✅ lot 64 | ✅ lot 64 | Racine > Activités > Export inscriptions |
| `export_activites_compta` | ✅ lot 64 | ✅ lot 64 | Racine > Comptabilité > Export comptable |

#### Groupe Adhérents
| Page | hierarchie | navigation | Fil d'ariane cible |
|------|-----------|------------|-------------------|
| `benevoles` | ✅ lot 64 | ✅ lot 64 | Racine > Adhérents > Bénévoles |
| `recherche_avancee` | ✅ lot 64 | ✅ lot 64 | Racine > Adhérents > Recherche avancée |

#### Groupe Cotisations
| Page | hierarchie | navigation | Fil d'ariane cible |
|------|-----------|------------|-------------------|
| `cotisation_suppression` | ✅ lot 64 | ✅ lot 64 | Racine > Cotisations > Supprimer une cotisation |

#### Groupe Comptabilité
| Page | hierarchie | navigation | Fil d'ariane cible |
|------|-----------|------------|-------------------|
| `comptes` | ✅ lot 65 | ✅ existant | Racine > Comptabilité |
| `editer_asso_comptes` | ✅ lot 65 | ✅ lot 65 | Racine > Comptabilité > Comptes > Éditer |
| `editer_asso_destinations` | ✅ lot 65 | ✅ lot 65 | Racine > Comptabilité > Destinations > Éditer |
| `editer_asso_dons` | ✅ lot 65 | ✅ lot 65 | Racine > Dons > Éditer |
| `editer_asso_plan` | ✅ lot 65 | ✅ lot 65 | Racine > Comptabilité > Plan comptable > Éditer |
| `editer_asso_ressources` | ✅ lot 65 | ✅ lot 65 | Racine > Prêts > Ressources > Éditer |
| `editer_asso_ventes` | ✅ lot 65 | ✅ lot 65 | Racine > Ventes > Éditer |
| `destination_comptable_import` | ✅ lot 65 | ✅ complété | Racine > Comptabilité > Import destinations |
| `plan_comptable_import` | ✅ lot 65 | ✅ complété | Racine > Comptabilité > Import plan comptable |
| `migration_donnees_comptables` | ✅ lot 65 | ✅ lot 65 | Racine > Comptabilité > Migration données |
| `transaction` | ✅ lot 65 | ✅ lot 65 | Racine > Comptabilité > Transaction |
| `transaction_abandon` | ✅ lot 65 | ✅ lot 65 | Racine > Comptabilité > Transaction > Abandon |
| `transaction_remboursement` | ✅ lot 65 | ✅ lot 65 | Racine > Comptabilité > Transaction > Remboursement |
| `transaction_suppression` | ✅ lot 65 | ✅ lot 65 | Racine > Comptabilité > Transaction > Suppression |

#### Groupe Configuration
| Page | hierarchie | navigation | Fil d'ariane cible |
|------|-----------|------------|-------------------|
| `notifications` | ✅ lot 65 | ✅ lot 65 | Racine > Configuration > Notifications |

#### Hors scope (lightbox / popins — pas de fil d'ariane pertinent)
- `lightbox_lien_inscription_vip`
- `popin_contact_libre`

---

### ✅ Pages historiques relues au lot 66

| Page | Problème probable |
|------|-----------------|
| `editer_asso_cotisation` | ✅ page courante et retour `cotisations` |
| `editer_asso_categorie_activite` | ✅ URL plurielle et retour `categories_activites` |
| `editer_asso_categorie_cotisation` | ✅ page courante et retour `categories_cotisation` |
| `analyse_compta_activites` | ✅ retour explicite vers `activites` |

---

## Navigation gauche — pages sans colonne (à créer)

Toutes les pages inventoriées possèdent désormais leur colonne ou réutilisent
une navigation de section existante. Chaque navigation créée inclut un lien de
retour vers la page parente.

---

## Ordre de priorité suggéré

1. **P0** — Pages fréquentes du parcours activités :
   `suivi_activites`, `export_activites`, `export_activites_compta`

2. **P1** — Pages adhérents et cotisations :
   `benevoles`, `recherche_avancee`, `cotisation_suppression`

3. **P2** — Transactions et comptabilité :
   `transaction`, `transaction_*`, `comptes`, `editer_asso_*` comptabilité

4. **P3** — Relecture des hierarchies existantes (vérifier cohérence + liens retour)

5. **P4** — Imports / migration (usage rare) :
   `plan_comptable_import`, `destination_comptable_import`, `migration_donnees_comptables`

---

## Ressources utiles
- Modèle hierarchie avec boucle dynamique : `hierarchie/editer_asso_activite.html`
- Modèle navigation complet : `navigation/editer_asso_activite.html`
- Blocs partiels réutilisables : `contenu/inc-voir_activites/`, `contenu/inc-adherents/`
- Clés de langue : `lang/asso_fr.php`

