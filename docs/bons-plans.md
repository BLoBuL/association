# Bons plans

Le module `association_bons_plans` est la reprise SPIP 4 du plugin historique Bon plan. Il conserve les tables `spip_bons_plans` et `spip_bons_plans_liens` afin qu'une activation sur une base existante retrouve les contenus sans copie ni renommage SQL.

## Responsabilités

- objet éditorial `bon_plan`, rattachable aux rubriques et aux auteurs ;
- rédaction, proposition, publication, refus et corbeille ;
- date facultative de dépublication ;
- catalogue, fiche et modèle publics ;
- formulaire de proposition pour un auteur connecté ;
- contribution aux menus, à la configuration, aux capacités et à l'inventaire de la suite.

Le plugin dépend seulement d'Association et de Saisies. La notification est publiée au moyen de `association_notifier_metier()`. Si Communication n'est pas actif, la proposition reste enregistrée avec le statut `prop` et peut être modérée normalement.

L'ancien plugin `spip_bon_plan` doit être désactivé avant l'installation, car
les deux plugins décrivent les mêmes tables et le même objet éditorial. SPIP 4
ne reconnaît pas de balise `incompatible` dans `paquet.xml` : le contrôle est
donc assuré par la procédure d'installation. La désactivation du nouveau module
conserve volontairement les tables.
