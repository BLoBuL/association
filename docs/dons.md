# Dons

## But

Documenter le cycle metier des dons: saisie, validation, lien comptable, imputation, destinations et affichage prive.

## Fichiers sources

- [`formulaires/editer_asso_dons.php`](../formulaires/editer_asso_dons.php)
- [`action/editer_asso_dons.php`](../action/editer_asso_dons.php)
- [`inc/comptes.php`](../inc/comptes.php)
- [`inc/association_comptabilite.php`](../inc/association_comptabilite.php)
- [`inc/destinations.php`](../inc/destinations.php)

## Donnee principale

La table metier est:

- `spip_asso_dons`

Champs clefs:

- `date_don`;
- `bienfaiteur`;
- `id_adherent`;
- `argent`;
- `valeur`;
- `contrepartie`;
- `commentaire`.

## Formulaire

Le formulaire `editer_asso_dons`:

- charge l'objet et le compte associe;
- pre-remplit la date du jour pour une nouvelle entree;
- recupere le journal et l'id_compte depuis `spip_asso_comptes`;
- formate les montants en presentation locale;
- injecte la classe de banques et les destinations comptables si actives.

## Validation

La validation controle:

- montants positifs;
- existence de l'adherent quand `id_adherent` est renseigne;
- validite de la date;
- coherence de la destination comptable via `verifier_destination_comptable()`.

## Traitement

Le traitement:

- convertit les dates saisies au format attendu;
- delegue a `formulaires_editer_objet_traiter`;
- propage le lien vers le compte comptable.

## Lien comptable

Le don est rattache a `spip_asso_comptes` avec:

- une imputation issue de `pc_dons`;
- un journal comptable;
- éventuellement une ventilation par destinations si la brique est active.

Les helpers clefs sont:

- `compte_don()`;
- `modifier_compte_don()`;
- `association_ajouter_operation_comptable()`;
- `association_modifier_operation_comptable()`;
- `ajouter_destinations()`.

## Configuration liee

- `dons`;
- `pc_dons`;
- `dc_dons`;
- `destinations`;
- `classe_banques`;
- `comptes`.

## Points de vigilance

- Les montants sont relies au socle comptable, pas seulement a la table don.
- Les destinations ne servent que si elles sont actives.
- Les formulaires et les actions doivent rester synchrones pour ne pas casser le lien `don <-> compte`.

