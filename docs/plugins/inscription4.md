# Dépendance - `inscription4`

## Rôle dans Blobul

`inscription4` gère la base du compte auteur et du formulaire d'inscription utilisateur sous SPIP 4.

Blobul s'en sert pour:

- l'inscription et l'édition d'auteur;
- les champs extras;
- les notifications liées à l'inscription auteur;
- la recherche et la segmentation de certains attributs de profil.

## Déclaration

- Dépendance déclarée dans [`paquet.xml`](../../paquet.xml)
- Compatibilité déclarée: `4.1.14` à `4.*`

## Utilisations principales

- pipelines historiques `i3_*`, encore publiés par Inscription 4 ;
- lecture de la configuration historique `inscription3`, conservée par Inscription 4 ;
- logique des champs extras;
- destinataires des notifications d'inscription.

## Points d'intégration

- `notifications_destinataires` pour les mails user/admin;
- `i3_verifier_formulaire` pour assouplir certaines contraintes côté admin;
- `formulaire_saisies` pour adapter les champs requis au navigateur;
- `pre_insertion` pour initialiser les données auteur.

## Risques d'intégration

- des changements dans les champs extras peuvent impacter la recherche et les formulaires Blobul;
- les notifications d'inscription doivent éviter les doublons si le pipeline est partagé;
- les champs conditionnels doivent rester compatibles avec les validations serveur et navigateur.

Association ne dépend plus du paquet `inscription3`. Les noms `i3_*` et
`inscription3` ci-dessus sont des contrats de compatibilité du paquet
`inscription4`, pas des appels à un plugin Blobul ni à une ancienne installation.

## A lire en plus

- [`../guide_base_connaissance_ia.md`](../guide_base_connaissance_ia.md)
- [`../notifications.md`](../notifications.md)
