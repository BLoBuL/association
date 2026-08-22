# Page d'autorisations du plugin `blobul-ASSO_BO`

## But

Documenter la logique d'autorisation du plugin, ses helpers partages et les principaux `autoriser_*_dist()` qui pilotent l'acces au back-office, aux evenements, aux comptes et aux modules connexes.

Cette page sert de reference technique pour comprendre:

- qui peut voir quoi;
- comment les contextes d'evenement sont reconstruits;
- quelles autorisations sont deleguees a SPIP natif;
- quels comportements dependent de la configuration du plugin.

## Fichiers sources

- [`association_autoriser.php`](../association_autoriser.php)
- [`inc/association_autorisations.php`](../inc/association_autorisations.php)
- [`association_options.php`](../association_options.php)
- [`association_pipelines.php`](../association_pipelines.php)

## Modele d'acces

Le plugin s'appuie principalement sur les profils SPIP suivants:

- `0minirezo` non restreint: administrateur complet;
- `0minirezo` restreint: administrateur limite a un sous-ensemble de rubriques;
- `1comite`: redacteur;
- `webmestre=oui`: super utilisateur;
- `6forum`: visiteur sans acces BO.

L'essentiel des decisions d'autorisation repose ensuite sur:

- la rubrique de l'article parent d'un evenement;
- la relation auteur/evenement;
- le statut de l'utilisateur;
- quelques metas de configuration.

## Helpers partages

### `association_est_admin_complet($qui)`

Retourne `true` si l'utilisateur est un administrateur `0minirezo` non restreint.

### `association_est_responsable_evenement($qui, $id_evenement)`

Verifie si l'auteur peut etre considere comme responsable de l'evenement.

La fonction s'appuie sur la logique native de responsabilite evenement, et elle est surtout utile pour les administrateurs restreints et les redacteurs.

### `association_peut_acceder_evenement($qui, $id_evenement)`

Controle d'acces principal pour un evenement.

Rappel:

- un administrateur complet passe toujours;
- un administrateur restreint doit etre autorise via la rubrique parente de l'article;
- un redacteur doit etre auteur de l'article parent de l'evenement.

### `association_normalize_qui($qui)`

Normalise le tableau utilisateur fourni aux autorisations.

La fonction garantit la presence des cles utiles (`statut`, `id_auteur`, `webmestre`, `restreint`) et recalcule les rubriques restreintes si besoin.

### `association_obtenir_evenement_contexte($id_compte, $opt)`

Reconstitue un contexte evenement a partir de differentes sources, dans cet ordre:

1. `id_evenement` passe explicitement;
2. `id_evenement` dans la requete;
3. `id_activite` dans les options ou la requete, puis lookup dans `spip_asso_activites`;
4. `id_compte` ou `id` dans la requete, puis lookup dans `spip_asso_comptes`.

Cette fonction est centrale pour les autorisations sur les comptes et les actions liees aux evenements.

### `association_debug_log($message, $contexte, $niveau)`

Journalise des informations complementaires quand le debug est actif.

Elle sert surtout a tracer les chemins d'autorisation complexes.

## Autorisations principales

### Menus

| Fonction | Profil attendu | Remarque |
|---|---|---|
| `autoriser_adherents_menu_dist` | admin complet | menu adherents reserve |
| `autoriser_activites_menu_dist` | tous profils | menu activites plus ouvert |
| `autoriser_cotisations_menu_dist` | admin complet | menu cotisations reserve |
| `autoriser_benevoles_menu_dist` | admin complet, admin restreint, redacteur | acces plus large |
| `autoriser_comptes_menu_dist` | admin complet | menu comptes reserve |

### Evenements

| Fonction | Regle principale |
|---|---|
| `autoriser_modifier_evenement_dist` | admin complet toujours, sinon verification de la rubrique ou de la responsabilite auteur |
| `autoriser_voir_activites_dist` | depend de `association_est_responsable_evenement()` |
| `autoriser_onglet_activites_dist` | l'onglet inscriptions est ouvert plus largement, les onglets sensibles repassent par la responsabilite evenement |

### Comptes et comptabilite

| Fonction | Regle principale |
|---|---|
| `autoriser_comptes_dist` | acces prioritairement reserve aux administrateurs |
| `autoriser_asso_comptes_creer_dist` | creation liee au contexte evenement et a la responsabilite |
| `autoriser_modifier_asso_compte_dist` | un compte deja vu ne doit plus etre modifiable par un redacteur |
| `autoriser_creer_asso_compte_dist` | creation conditionnee par le contexte et le droit de modification de l'evenement |
| `autoriser_asso_modifier` | autorisation generique sur l'objet `asso` basee sur le contexte evenement |

### Articles

| Fonction | Regle principale |
|---|---|
| `autoriser_modifier_article_dist` | delegue en partie a SPIP natif, mais ajoute un cas ou un admin restreint peut modifier un article si un evenement d'inscription lie a cet article est gere par lui |

### Newsletters

| Fonction | Regle principale |
|---|---|
| `autoriser_newsletter_generer` | accessible aux `0minirezo` et `1comite` |
| `autoriser_newsletter_envoyer` | acces conditionne au mode de test ou au statut |
| `autoriser_newsletter_instituer` | statut publie ou modification standard |
| `autoriser_newsletter_modifier` | interdit si la newsletter est deja verrouillee par son etat |

### Documents

| Fonction | Regle principale |
|---|---|
| `autoriser_joindredocument` | autorise selon le contexte objet, le statut et le droit de modification |

### Autres cas

Le plugin contient aussi des autorisations specialisees pour:

- la publication dans certaines rubriques;
- la visualisation de certaines activites;
- les actions de moderation selon le module actif;
- les menus conditionnels selon la configuration.

## Wrappers et compatibilite

Certaines fonctions historiques conservent un role de compatibilite:

- `autoriser_page`;
- `autoriser_page_else_minipres`;
- autres wrappers autour de `autoriser_asso_modifier`.

La logique utile est a lire dans les fonctions `_dist()` et non dans les wrappers.

## Dependances fonctionnelles

Les autorisations ne se lisent pas seules: elles sont alimentees par:

- les rubriques et auteurs SPIP;
- les liens entre articles et evenements;
- les comptes et activites lies;
- la configuration `association_metas`;
- les modules actifs dans `association_options.php`.

## Points de vigilance

- Ne pas confondre acces BO, modification d'article et responsabilite sur un evenement.
- Les admin restreints ont des droits plus fins que les admin complets.
- Les redirections de contexte via `association_obtenir_evenement_contexte()` sont essentielles pour les formulaires lies aux comptes.
- Les droits doivent rester coherents avec les menus, sinon l'interface expose des actions inutilisables.

