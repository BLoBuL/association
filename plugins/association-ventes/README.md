# Association - Ventes

Plugin SPIP 4/PHP 8 propriétaire de `spip_asso_ventes`. L’installation adopte
la table historique et la désinstallation conserve les données.

Le module fonctionne avec le seul socle Association pour les ventes manuelles.
Produits, Prix et Commandes sont facultatifs : lorsqu'ils sont actifs, chaque
ligne Produit d'une commande validée alimente idempotemment une vente et ses
instantanés de prix. Comptabilité, Paiements et Adhésions restent également des
enrichissements facultatifs.

Capacités publiées :

- `ventes` dans `association_capacites` ;
- fournisseur du pipeline `association_enregistrer_vente` ;
- consommateur de `post_edition` pour les commandes validées ;
- export et anonymisation RGPD ;
- déclaration facultative des objets comptabilisables.
