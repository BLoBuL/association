# Priorisation refactor - Top 30 fonctions

Generation: 2026-04-20

Objectif: prioriser les actions de refactor sans modifier le code PHP, en separant clairement:

1. Points d'entree SPIP a conserver (pas de renommage casseur)
2. Helpers metier a normaliser (`association_*`)
3. Legacy/ambigu a traiter avec prudence

## Methode de scoring

- Impact: H (haut) / M (moyen) / B (bas)
- Risque: H / M / B
- Effort: H / M / B
- Score priorite (indicatif): favoriser `Impact H`, puis `Risque B/M`, puis `Effort B/M`

## Regles de garde-fou

- Ne pas renommer directement les points d'entree SPIP (`action_*`, `autoriser_*`, `formulaires_*`, `balise_*`, `critere_*`, `filtre_*`, `exec_*`, `genie_*`).
- Favoriser alias/wrappers de compatibilite pour les helpers renommes.
- Respecter les APIs stables signalees dans `AGENTS.md`.
- Aucune modification PHP dans ce livrable.

## Top 30 priorise

| # | Fonction | Classe | Impact | Risque | Effort | Prerequis | Action proposee |
|---|---|---|---|---|---|---|---|
| 1 | `NbJours` | legacy/ambigu | H | B | B | cartographier appels exacts | creer alias `association_nb_jours` puis migrer appels internes |
| 2 | `deserialize_values` | helper metier | H | B | B | verifier formats d'entree | renommer vers `association_deserialiser_valeurs` + wrapper |
| 3 | `preparer_liste_auteurs_newsletter` | helper metier | H | M | M | valider usages BO/newsletter | normaliser prefixe `association_` |
| 4 | `parser_emails_depuis_config` | helper metier | H | B | B | valider format config | normaliser prefixe + tests unitaires simples |
| 5 | `lister_compte_recurive` | legacy/ambigu | H | B | B | audit orthographe/API | creer nom cible `association_lister_compte_recursive` |
| 6 | `lister_destination_recurive` | legacy/ambigu | H | B | B | idem item #5 | creer nom cible `association_lister_destination_recursive` |
| 7 | `preparer_liste_adherents` | helper metier | H | M | M | verifier dependances filtres | normaliser prefixe `association_` |
| 8 | `preparer_liste_evenements` | helper metier | H | M | M | verifier usage tableaux prive | normaliser prefixe `association_` |
| 9 | `preparer_liste_cotisations` | helper metier | H | M | M | verifier dependances cotisations | normaliser prefixe `association_` |
| 10 | `preparer_liste_categories` | helper metier | M | B | B | aucune | normaliser prefixe `association_` |
| 11 | `action_traiter_comptes_dist` | point entree SPIP | H | M | M | verifier securiser_action | conserver nom, limiter includes globaux |
| 12 | `action_valider_compte_dist` | point entree SPIP | H | M | M | verifier autorisations | conserver nom, rapprocher includes |
| 13 | `action_invalider_compte_dist` | point entree SPIP | H | M | M | verifier redirections | conserver nom, rapprocher includes |
| 14 | `action_synchroniser_comptabilite_evenement_dist` | point entree SPIP | H | H | M | verifier taches batch | conserver nom, factoriser helper metier |
| 15 | `action_envoyer_relances` | point entree SPIP | H | H | M | verifier genie + notifications | conserver nom, isoler preparation donnees |
| 16 | `autoriser_modifier_evenement_dist` | point entree SPIP | H | H | M | revue regles de droit | conserver nom, extraire helper metier de decision |
| 17 | `autoriser_modifier_asso_compte_dist` | point entree SPIP | H | H | M | audit cas `vu==1` | conserver nom, centraliser logique contexte |
| 18 | `autoriser_assocompte_modifier_dist` | point entree SPIP | M | M | B | verifier wrapper SPIP | conserver wrapper, documenter role |
| 19 | `autoriser_publierdans_dist` | point entree SPIP | M | M | M | verifier fallback actuel | conserver nom, clarifier delegation |
| 20 | `autoriser_voir_activites_dist` | point entree SPIP | H | M | M | audit responsabilites evenement | conserver nom, deplacer logique commune |
| 21 | `formulaires_editer_asso_comptes_charger_dist` | point entree SPIP | H | H | M | relire CVT complet | conserver nom, extraire helpers |
| 22 | `formulaires_editer_asso_comptes_verifier_dist` | point entree SPIP | H | H | M | idem #21 | conserver nom, extraire validations metier |
| 23 | `formulaires_editer_asso_comptes_traiter_dist` | point entree SPIP | H | H | M | idem #21 | conserver nom, extraire orchestration metier |
| 24 | `formulaires_inscription_evenement_charger_dist` | point entree SPIP | H | H | H | cartographier variantes public/multi | conserver nom, mutualiser avec helper central |
| 25 | `formulaires_inscription_evenement_verifier_dist` | point entree SPIP | H | H | H | idem #24 | conserver nom, isoler regles eligibilite |
| 26 | `formulaires_inscription_evenement_traiter_dist` | point entree SPIP | H | H | H | idem #24 | conserver nom, isoler bank/notifications |
| 27 | `balise_AUTORISER_PAGE` | point entree SPIP | M | M | B | verifier squelettes consommateurs | conserver nom, documenter contrat |
| 28 | `balise_ONGLETS_ASSOCIATION_dyn` | point entree SPIP | M | M | B | verifier use-cases menus | conserver nom, deporter logique metier |
| 29 | `filtre_stats_compta_activites_exercice` | point entree SPIP | M | M | M | verifier usages squelettes de stats | conserver prefixe `filtre_`, helper dedie |
| 30 | `genie_association_taches_generales` | point entree SPIP | H | H | M | verifier idempotence cron | conserver nom, segmenter sous-taches |

## Plan par lots

## Lot A - Quick wins (faible risque / fort gain)

Cible: items 1, 2, 4, 5, 6, 10, 18, 27, 28

- Standardiser le nommage des helpers legacy avec wrappers de compatibilite.
- Ajouter une note "point d'entree SPIP" sur wrappers/balises pour eviter faux refactors.
- Resultat attendu: lisibilite immediate + reduction dette de nommage sans impact runtime majeur.

## Lot B - Moyen terme (coeur metier, impact fort)

Cible: items 3, 7, 8, 9, 11, 12, 13, 16, 17, 19, 20, 21, 22, 23

- Recentrer les points d'entree SPIP sur orchestration courte.
- Deporter la logique metier vers helpers `association_*` testables.
- Resultat attendu: maintenance simplifiee, meilleur controle des droits et des flux comptables.

## Lot C - Lourd / structurant

Cible: items 14, 15, 24, 25, 26, 29, 30

- Refactor progressif des flux sensibles (cron, relances, inscription evenement, bank, filtres complexes).
- Introduire une couche de services metier interne (`inc/association/*`) sans casser les signatures publiques.
- Resultat attendu: robustesse long terme, baisse des regressions sur operations critiques.

## Backlog de verification avant execution d'un refactor

1. Verifier les signatures publiques declarees stables (`AGENTS.md`).
2. Verifier les points d'entree dynamiques SPIP (`action`, `autoriser`, `CVT`, `balises`, `filtres`, `genie`).
3. Lister les includes requis par fonction (`03_appels_dynamiques_spip.md`).
4. Preparer un plan de migration par alias (ancien nom -> nouveau nom).
5. Definir un test de non regression minimal par lot (droits, CVT, cron, bank, notifications).

## Decision rapide (ordre conseille)

1. Executer Lot A complet.
2. Traiter Lot B en 2 sous-iterations: (a) droits/autorisations, (b) formulaires CVT.
3. Traiter Lot C en dernier avec fenetre de validation etree (cron + paiement + inscriptions).

Aucune modification du code PHP du plugin dans ce document.


