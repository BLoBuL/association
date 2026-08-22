# Dépendance - `bank`

## Rôle dans Blobul

`bank` est le socle des transactions de paiement.

Blobul s'appuie sur ce plugin pour:

- créer et mettre à jour les transactions;
- lister les configurations de moyens de paiement;
- gérer les retours de paiement;
- définir les modes de paiement disponibles dans les cotisations et participations.

## Déclaration

- Dépendance déclarée dans [`paquet.xml`](../../paquet.xml)
- Compatibilité déclarée: `>= 6.4.0`

## Utilisations principales

- `inc/bank`
- `bank_lister_configs()`
- `bank_config_id()`
- `bank_devise_defaut()`
- `inserer_transaction()`
- `bank_affiche_montant()`

## Zones du plugin qui en dépendent

- formulaires de cotisation;
- catégories de participation financière;
- reçus d'adhésion;
- reçus de participation;
- configuration des modes de paiement;
- synchronisation comptable.

## Ce qu'il faut garder stable

- les identifiants de configuration bancaires;
- la logique de transaction `attente` / `ok`;
- les libellés des modes de paiement exposés dans les formulaires.

## Risques d'intégration

- un mode désactivé dans `bank` ne doit plus remonter dans les listes Blobul;
- une évolution du format de configuration peut casser les filtres de choix;
- les montants affichés dans les notifications doivent rester compatibles avec la devise de `bank`.

## A lire en plus

- [`../modes_paiement.md`](../modes_paiement.md)
- [`../categories_cotisation.md`](../categories_cotisation.md)
- [`../comptabilite_evenements.md`](../comptabilite_evenements.md)
