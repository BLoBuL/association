# Notifications - Cotisation (Adherent)

## But

Documenter les messages envoyés a l'adhérent tout au long du cycle de cotisation:

- attente de paiement;
- validation pré-paiement;
- validation post-paiement;
- activation finale.

## Fichiers de reference

- [`notifications_cotisations.md`](./notifications_cotisations.md)
- [`inc/cotisations.php`](../inc/cotisations.php)
- [`inc/api_cotisations.php`](../inc/api_cotisations.php)
- [`notifications/`](../notifications/)
- [`lang/notifications_fr.php`](../lang/notifications_fr.php)

## Types de messages

- `attente_paiement` : instructions de paiement et lien vers la page de paiement.
- `validation_pre-paiement` : la cotisation nécessite une validation avant paiement.
- `validation_post-paiement` : paiement reçu, en attente d'activation par un responsable.
- `activation` : adhésion activee après traitement.

## Contenu du contexte

Les templates attendent un contexte plat contenant notamment:

- `email_adherent`;
- `nom_adherent`;
- `id_compte`;
- `montant`;
- `id_transaction`;
- `transaction_hash`;
- `reinscription`;
- `montant_don`.

## Points techniques utiles

- Les sujets sont construits depuis les clés de langue `asso:*`.
- Le contenu affiche des liens de paiement ou de profil selon le cas.
- Les comptes entreprise peuvent être distingués du cas adhérent simple dans le rendu.

## Points de vigilance

- Ne pas exposer d'information sensible dans les URLs.
- Conserver la compatibilité des placeholders utilisés par les squelettes.
- Vérifier que les clés de langue existent dans toutes les variantes de notification.
- Les messages de relance ou d'echeance doivent rester coherents avec la vraie politique de renouvellement du site : si la periode de reinscription est trop courte, l'adherent peut recevoir un message de fin de droits avant d'avoir eu un delai raisonnable pour renouveler.
- Quand une adhesion expire, l'impact peut depasser la simple cotisation : perte d'acces a des contenus, a des formulaires reserves et, sur certains sites, a des droits de redaction, d'administration ou de responsable.

## A lire en plus

- [`notifications_cotisation_admin.md`](./notifications_cotisation_admin.md)
- [`notifications_echeances.md`](./notifications_echeances.md)
- [`notifications_evenements.md`](./notifications_evenements.md)
- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/facteur.md`](./plugins/facteur.md)
- [`plugins/notifications.md`](./plugins/notifications.md)
