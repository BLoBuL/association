# Categories de participation financiere

## But

Documenter la gestion technique des categories de participation aux evenements:

- comment elles sont definies;
- comment elles sont liees a un evenement;
- comment elles sont filtrees selon le profil de l'inscrit;
- comment le tarif de groupe fonctionne;
- comment le montant total est calcule.

## Structure de donnees

La categorie elle-meme est stockee dans:

- `spip_asso_categories_activites`

Le lien entre une categorie et un evenement est stocke dans:

- `spip_asso_categories_activites_liens`

### Champs principaux de `spip_asso_categories_activites`

| Champ | Rôle |
|---|---|
| `id_categorie` | identifiant technique |
| `valeur` | nom lisible |
| `statut` | actif ou non |
| `quantite` | nombre de personnes couvertes par le tarif |
| `paiement_en_ligne` | autorise le paiement en ligne |
| `commentaires` | texte descriptif |
| `deleted` | suppression logique |

### Champs principaux du lien evenement/categorie

| Champ | Rôle |
|---|---|
| `id_evenement` | evenement concerne |
| `id_categorie` | categorie rattachee |
| `montant` | prix applique pour cet evenement |

## Formulaire d'edition

Le parametrage se fait via:

- [`formulaires/editer_asso_categorie_activite.php`](../formulaires/editer_asso_categorie_activite.php)

### Champs exposes

| Champ | Rôle |
|---|---|
| `valeur` | nom de la participation |
| `statut` | active ou desactive |
| `type_inscrit` | profil autorise |
| `type_tarif` | individuel ou groupe |
| `quantite` | nombre de personnes pour un tarif groupe |
| `commentaire` | explication visible |

## Types d'inscrits

Les types techniques disponibles sont:

- `indifferent`;
- `adherent`;
- `couple`;
- `non_adherent`;
- `enfant`;
- `invite`;
- `special`;
- `benevole`.

## Règles de filtrage

### 1. Statut actif

Les categories desactivees ne doivent pas etre proposees a l'inscription.

### 2. Profil de l'inscrit

La liste des categories est filtrees selon le type d'inscrit:

- adherent;
- non membre;
- enfant;
- invite;
- benevole;
- special;
- indifferent.

### 3. Rang du participant

Le rang du participant influence l'affichage:

- le premier inscrit voit les tarifs disponibles pour lui;
- le second inscrit peut etre masque si un tarif groupe couvre deja les places;
- les enfants et invites ont leurs propres jeux de types autorises.

### 4. Tarifs de groupe

Une categorie avec `quantite > 1` est consideree comme un tarif groupe.

Règle clé:

- si le premier inscrit choisit un tarif groupe couvrant le rang suivant, le champ du participant suivant est masque;
- sinon le champ reste visible.

### 5. Couple

Le type `couple` est traite comme un cas special:

- la quantite est forcee a `2`;
- le tarif n'est propose au premier inscrit que si le conjoint est selectionne;
- le formulaire cache les options inutiles;
- le comportement de groupe est privilegie.

### 6. Validation serveur

Le filtrage visuel ne constitue pas une autorisation. Avant tout traitement, le
serveur verifie que chaque categorie postee:

- est active et liee a l'evenement;
- correspond au statut membre ou non-membre du titulaire;
- est compatible avec le role du participant;
- respecte la presence effective du conjoint pour un tarif `couple`.

Les champs tarifaires n'ont aucune valeur par defaut: l'utilisateur doit
effectuer un choix explicite.

## Calcul du montant

Le montant total est calcule dans le flux d'inscription:

- chaque categorie selectionnee apporte son montant;
- si le tarif est un groupe, le montant est multiplie par le nombre de lots necessaires;
- le calcul utilise un `ceil()` pour couvrir les blocs incomplets.

## Utilisation dans les inscriptions

Les helpers clefs sont:

- `generer_array_categories_participation()`;
- `association_types_tarifs_par_role()`;
- `champs_saisies_tarifs()`;
- `calculer_montant_total()`;
- `formater_post_form_multi()`;
- `generer_recapitulatif_multi()`.

## Lien avec la configuration globale

Les categories de participation s'appuient sur:

- `mode_paiement_participation`;
- `meta_cfg_event_form_info_supp`;
- `meta_cfg_event_config_accompagnants`;
- `meta_cfg_event_type_inscrits_evenement`;
- `meta_cfg_event_validation`;
- `meta_cfg_taxe_evenement`.

### Onglets relies

Les reglages qui impactent directement ces categories se trouvent surtout dans :

- `evenement` pour les informations supplementaires, les accompagnants et les notifications ;
- `evenement_defaut` pour les valeurs reprises a la creation d'un evenement ;
- `mode_paiement` pour la liste des moyens de paiement autorises ;
- `comptabilite` pour l'imputation comptable des participations.

## Points de vigilance

- Les categories desactivees doivent etre exclues partout, y compris dans les calculs.
- Le terme "participation" correspond ici aux tarifs evenement, pas aux cotisations annuelles.
- Un tarif de groupe mal configure peut changer la perception des participants restants.
- Les listes visibles dans les formulaires doivent rester coherentes avec les liens evenement/categorie.

## A lire en plus

- [`tarifs-logique.md`](./tarifs-logique.md)
- [`parametrage_evenements.md`](./parametrage_evenements.md)
- [`comptabilite_evenements.md`](./comptabilite_evenements.md)
- [`modes_paiement.md`](./modes_paiement.md)
