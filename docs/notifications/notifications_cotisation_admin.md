# Notifications - Cotisation (Admin / Tresorerie)

## But

Documenter les messages envoyés a la tresorerie ou aux administrateurs lorsqu'une cotisation demande une action:

- attente de validation;
- demande de paiement;
- encaissement reçu;
- suivi de transaction.

## Fichiers de reference

- [`notifications_cotisations.md`](./notifications_cotisations.md)
- [`inc/cotisations.php`](../inc/cotisations.php)
- [`inc/api_cotisations.php`](../inc/api_cotisations.php)
- [`notifications/`](../notifications/)
- [`lang/notifications_fr.php`](../lang/notifications_fr.php)

## Templates

- `notifications/cotisation-attente_admin.html`
- `notifications/cotisation-demande_admin.html`
- `notifications/cotisation-encaissement_admin.html`

## Contexte attendu

Les templates utilisent notamment:

- `nom_adherent`;
- `nom_entreprise`;
- `type_cotisation`;
- `montant`;
- `id_compte`;
- `id_transaction`;
- `url_voir_adherent`;
- `url_editer_cotisation`;
- `url_transaction`.

## Rôle fonctionnel

La notification admin sert a:

- identifier rapidement la personne ou l'entreprise;
- comprendre le type de cotisation;
- ouvrir directement les vues utiles;
- agir sur la cotisation ou la transaction sans chercher ailleurs.

## Points techniques utiles

- Le sujet est construit via les clés de langue de notifications.
- Le bloc résumé doit rester lisible et court.
- Les destinataires sont normalisés et peuvent provenir d'une configuration par défaut si les responsables ne sont pas explicitement définis.

## Points de vigilance

- Conserver les libelles de bloc résumé cohérents entre les templates.
- Éviter les divergences entre les contenus admin et adherent.
- Vérifier que les liens internes pointent vers les bonnes actions.

## A lire en plus

- [`notifications_cotisation_adherent.md`](./notifications_cotisation_adherent.md)
- [`notifications_echeances.md`](./notifications_echeances.md)
- [`notifications_evenements.md`](./notifications_evenements.md)
- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/facteur.md`](./plugins/facteur.md)
- [`plugins/notifications.md`](./plugins/notifications.md)
