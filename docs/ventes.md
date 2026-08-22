# Ventes

## But

Documenter le cycle metier des ventes associatives et de leurs frais d'envoi.

## Fichiers sources

- [`formulaires/editer_asso_ventes.php`](../formulaires/editer_asso_ventes.php)
- [`action/editer_asso_ventes.php`](../action/editer_asso_ventes.php)
- [`inc/comptes.php`](../inc/comptes.php)
- [`inc/association_comptabilite.php`](../inc/association_comptabilite.php)
- [`inc/destinations.php`](../inc/destinations.php)

## Donnee principale

La table metier est:

- `spip_asso_ventes`

Champs clefs:

- `date_vente`;
- `date_envoi`;
- `article`;
- `code`;
- `acheteur`;
- `id_acheteur`;
- `quantite`;
- `frais_envoi`;
- `prix_vente`;
- `commentaire`.

## Formulaire

Le formulaire:

- charge l'objet de vente;
- initialise les dates si la vente est nouvelle;
- recupere le compte et le journal comptable;
- formate les montants pour l'edition;
- injecte la classe de banques et les destinations comptables.

## Validation

La validation controle:

- prix de vente positifs;
- frais d'envoi positifs;
- quantite positive;
- existence de l'acheteur si son id est renseigne;
- dates valides pour la vente et l'envoi.

## Traitement

Le traitement normalise les dates puis delegue a l'action d'edition.

## Lien comptable

La vente peut alimenter:

- une recette de vente;
- une recette de frais d'envoi;
- des destinations comptables si la brique est active.

Helpers importants:

- `compte_vente()`;
- `compte_vente_frais_envoi()`;
- `modifier_compte_vente()`;
- `modifier_activite_vente_frais_envoi()`;
- `ajouter_destinations()`.

## Configuration liee

- `ventes`;
- `pc_ventes`;
- `pc_frais_envoi`;
- `dc_ventes`;
- `destinations`;
- `classe_banques`;
- `comptes`.

## Points de vigilance

- Le frais d'envoi est traite comme une composante comptable distincte.
- Les destinations doivent rester coherentes avec les montants saisis.
- La logique d'affichage prive depend fortement du compte rattache.

