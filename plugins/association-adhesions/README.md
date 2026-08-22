# Association - Adhésions

Plugin métier SPIP 4/PHP 8 de la suite Association 4.

Il devient propriétaire des catégories d’adhésion et des cotisations :

- `spip_asso_categories_adherents` ;
- `spip_asso_cotisations`.

`spip_asso_comptes` reste exclusivement la propriété du plugin Comptabilité.
Le lien `id_compte` d’une cotisation est facultatif et permet de retrouver son
écriture comptable lorsqu’elle existe.

L’installation sur un site utilisant déjà Association 4 adopte les tables
existantes sans vider ni recopier leurs lignes. La désinstallation conserve les
données ; leur suppression demande une opération de maintenance explicite.
