# Comptabilite des evenements

## But

Documenter le cycle comptable complet d'un evenement:

- creation de l'inscription;
- creation de la transaction;
- ecriture comptable de recette;
- synchronisation lors d'une modification;
- remboursement eventuel;
- retrait ou recalcul d'une ecriture.

Cette page sert a comprendre comment le plugin relie:

- `spip_asso_activites`;
- `spip_transactions`;
- `spip_asso_comptes`;
- les formulaires d'inscription et de remboursement;
- les notifications et reçus.

Pour les commandes/factures Blobul hors evenement, voir aussi [`commandes_factures.md`](./commandes_factures.md).

## Fichiers de reference

- [`inc/comptes.php`](../inc/comptes.php)
- [`formulaires/inscription_evenement.php`](../formulaires/inscription_evenement.php)
- [`formulaires/inscription_evenement_public.php`](../formulaires/inscription_evenement_public.php)
- [`formulaires/inscription_evenement_multi_public.php`](../formulaires/inscription_evenement_multi_public.php)
- [`formulaires/rembourser_transaction.php`](../formulaires/rembourser_transaction.php)
- [`inc/fonctions/facteur_envoyer_recu_participation.php`](../inc/fonctions/facteur_envoyer_recu_participation.php)
- [`notifications/recu_encaissement_participation.html`](../notifications/recu_encaissement_participation.html)
- [`notifications/recu_remboursement_participation.html`](../notifications/recu_remboursement_participation.html)

## Structure des donnees

Les ecritures evenement sont principalement stockees dans `spip_asso_comptes`.

### Champs metiers importants

| Champ | Sens |
|---|---|
| `date` | date comptable de l'ecriture |
| `recette` | montant encaisse |
| `depense` | montant rembourse ou sorti |
| `justification` | description metier |
| `imputation` | code comptable |
| `journal` | journal associe |
| `id_auteur` | auteur / inscrit concerne |
| `id_objet` | identifiant de l'objet parent |
| `objet` | type d'objet, souvent `evenement` |
| `reinscription` | contexte cotisation si utilise |
| `id_categorie` | categorie associee si besoin |
| `statut_cotisation` | etat de cotisation si lie |
| `id_transaction` | transaction associee |
| `vu` | indicateur de traitement |

## Fonction centrale

La base technique repose sur:

- `inserer_compte()`;
- `modifier_compte()`;
- `inserer_compte_activite()`;
- `modifier_compte_activite()`;
- `valider_compte_activite()`;
- `supprimer_compte_activite()`;
- `inserer_compte_remboursement_activite()`.

## Creation d'une ecriture evenement

Quand une inscription payante est creee:

1. le formulaire calcule la transaction;
2. la transaction est inseree ou mise a jour;
3. `inserer_compte_activite()` est appelle;
4. une ligne de recette est ecrite dans `spip_asso_comptes`.

### Regles de base

- si l'evenement est gratuit, aucune ecriture de recette n'est creee;
- si la transaction est absente, le plugin journalise l'anomalie et n'invente pas de compte;
- l'imputation utilise le plan comptable configure dans `configurer_association`.

### Choix de l'imputation

Le code distingue:

- `pc_activites_creance` si la transaction n'est pas encore encaissee;
- `pc_activites_paiement` si la transaction est deja a `ok`.

## Synchronisation de la comptabilite

La fonction `modifier_compte_activite()` recalcule l'ecriture si:

- le montant de la transaction change;
- la date ou le contexte change;
- l'inscription est modifiee apres creation.

La fonction `valider_compte_activite()` marque ensuite l'ecriture comme vue et ajuste l'imputation si besoin.

## Remboursement

Le remboursement est traite comme une operation distincte.

### Flux

1. le formulaire de remboursement modifie la transaction;
2. `inserer_compte_remboursement_activite()` cree une ligne de depense;
3. un reçu de remboursement peut etre envoye;
4. le solde net reflète bien le paiement moins le remboursement.

### Point important

Le plugin ne supprime pas l'ancienne recette lors d'un remboursement.
Il conserve l'historique et ajoute une ecriture de sortie.

## Calcul et justification

Les justifications sont formulees a partir:

- du nom de l'inscrit;
- du titre de l'evenement;
- du type d'opération;
- du contexte de remboursement ou non.

Cette approche permet de garder des ecritures lisibles dans le back-office et dans les exports.

## Reçus de participation

Les reçus de participation sont generés dans `facteur_envoyer_recu_participation()`.

### Reçu d'encaissement

Lorsque le paiement est encaisse:

- le modèle `notifications/recu_encaissement_participation` est rendu;
- le sujet inclut le nom de l'inscrit, l'evenement et le numéro de reçu;
- le BCC peut être défini par `config_envoi_recu_participation_cc`.

### Reçu de remboursement

Lorsque le type vaut `remboursement`:

- le modèle `notifications/recu_remboursement_participation` est utilisé;
- le même canal BCC est conservé;
- le numéro de reçu reste rattache a la transaction et a l'activité.

## Configuration liee

La page `configurer_association` pilote plusieurs paramètres comptables:

- `comptes`;
- `classe_banques`;
- `destinations`;
- `exercice_comptable_debut`;
- `pc_cotisations_creance`;
- `pc_cotisations_paiement`;
- `dc_cotisations`;
- `pc_activites_creance`;
- `pc_activites_paiement`;
- `pc_activites_frais`;
- `dc_activites`;
- `pc_dons`;
- `dc_dons`;
- `dons`;
- `pc_ventes`;
- `pc_frais_envoi`;
- `dc_ventes`;
- `ventes`;
- `prets`;
- `pc_prets`;
- `meta_cfg_taxe`;
- `meta_cfg_taxe_evenement`;
- `meta_cfg_autorisation_encaisser_transaction`.

## Impact sur les vues et exports

Les vues privees et exports doivent refleter:

- la recette initiale;
- la depense de remboursement si elle existe;
- le net comptable;
- la liaison avec l'activité et la transaction.

Les commandes envoyees utilisent la meme table `spip_asso_comptes`, avec `objet=commande` et `id_objet=id_commande`. Elles ne sont pas creees par ce flux evenement, mais par la synchronisation commande documentee dans `commandes_factures.md`.

Fichiers utiles:

- [`prive/objets/liste/table_comptabilite_activites_fonctions.php`](../prive/objets/liste/table_comptabilite_activites_fonctions.php)
- [`prive/squelettes/contenu/analyse_compta_activites_fonctions.php`](../prive/squelettes/contenu/analyse_compta_activites_fonctions.php)
- [`export_compta.xml.html`](../export_compta.xml.html)
- [`export_evenements_compta.xml.html`](../export_evenements_compta.xml.html)

## Cas limites a connaitre

- Une transaction introuvable ne doit pas generer d'ecriture fantome.
- Un remboursement ne doit pas casser l'historique.
- Une modification d'inscription peut recalculer le compte sans changer la logique metier du paiement deja encaissé.
- Le champ `vu` sert au suivi du traitement, pas a valider la transaction elle-meme.

## A lire en plus

- [`notifications_evenements.md`](./notifications_evenements.md)
- [`evenements_formulaires_inscription.md`](./evenements_formulaires_inscription.md)
- [`modes_paiement.md`](./modes_paiement.md)
- [`categories_participation_financiere.md`](./categories_participation_financiere.md)
- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/bank.md`](./plugins/bank.md)
- [`plugins/destinations.md`](./plugins/destinations.md)
- [`plugins/facteur.md`](./plugins/facteur.md)
