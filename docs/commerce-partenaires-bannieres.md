# Commerce, Partenaires et Bannières

## Commerce

`association_commerce` remplace la dépendance au plugin historique
`blobul-SHOP`. Il assemble les plugins génériques Prix, Paniers et Commandes,
fournit la page publique `spip.php?page=boutique`, un catalogue et un
mini-panier. Il ne possède ni les produits (articles SPIP), ni les paniers, ni
les commandes. Le module Paiements est un enrichissement facultatif.

## Partenaires

`association_partenaires` nécessite Contacts & Organisations. La table
`spip_asso_partenaires` ne duplique pas les coordonnées : elle relie une
organisation à un titre public, un niveau, une période, un ordre, un statut et
une URL de campagne. Le BO est accessible par `exec=partenaires`, la liste
publique par `spip.php?page=partenaires`.

## Bannières

`association_bannieres` possède `spip_asso_bannieres`. Une bannière porte son
titre, sa description, sa cible, son emplacement, ses dates, son ordre et son
statut ; son visuel utilise le logo natif de l'objet SPIP. Le modèle
`<asso_bannieres|emplacement=principal>` permet l'intégration dans tout
squelette. La page de contrôle publique est `spip.php?page=bannieres`.

## Menus et configuration

Les trois plugins déclarent leurs propres entrées de menu et onglets de
configuration. Aucun champ de ces domaines n'est déclaré par le socle. Leur
désactivation retire leurs surfaces sans laisser de lien mort.
