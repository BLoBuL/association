# Association 4

Association 4 est la nouvelle lignée modulaire du plugin de gestion des
associations Blobul pour SPIP 4 et PHP 8.

La branche `4.x` part du comportement éprouvé du BO 6.8 et utilise Associaspip
2.1 et 2.2 comme références logiques. Le chantier remplace progressivement le
monolithe par des plugins métier autonomes conformes aux conventions SPIP.

## État

Cette branche est en développement. Elle ne doit pas remplacer une installation
6.8 sans le pont de migration et une sauvegarde vérifiée.

## Principes

- APIs, objets, liens, pipelines, autorisations et formulaires CVT natifs SPIP ;
- un propriétaire unique pour chaque table et chaque règle métier ;
- contrats publics documentés entre modules ;
- migrations idempotentes et réversibles ;
- aucune validation de paiement sur le seul retour navigateur ;
- tests automatisés sous SPIP 4 et PHP 8.

Voir [`docs/architecture-suite-4.md`](docs/architecture-suite-4.md).
