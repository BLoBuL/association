# Matrice des dépendances externes

## But

Offrir une vue rapide des dépendances externes de `blobul-ASSO_BO` avec:

- leur statut;
- leur rôle dans Blobul;
- les pages de référence;
- les principaux risques d'intégration.

## Légende

- **Directe**: déclarée dans `paquet.xml` via `necessite` ou `utilise`.
- **Fonctionnelle**: brique utilisée intensivement par le code, même si elle n'est pas toujours déclarée comme dépendance directe.
- **Transversale**: brique de base utilisée par de nombreux formulaires ou helpers.

## Matrice

| Plugin | Statut | Rôle Blobul | Références | Points de vigilance |
|---|---|---|---|---|
| `inscription4` | Directe | Compte auteur, inscription, champs extras, notifications d'inscription | [`inscription4.md`](./inscription4.md) | API historiques `i3_*`, champs extras, doublons de destinataires |
| `bank` | Directe | Transactions, moyens de paiement, devises, affichage des montants | [`bank.md`](./bank.md) | Identifiants de config, statuts de transaction, choix de paiement |
| `notifications` | Directe | Rendu des mails SPIP et clés de langue de notification | [`notifications.md`](./notifications.md) | Synchronisation des squelettes et des clés de langue |
| `cextras` | Directe | Champs extras sur les auteurs et objets métier | [`cextras.md`](./cextras.md) | Schéma de champs, conditions d'affichage, compatibilité des formulaires |
| `saisies` | Transversale | Construction déclarative des formulaires | [`saisies.md`](./saisies.md) | Types de champs, `afficher_si`, validation HTML5 |
| `Facteur` | Fonctionnelle | Envoi email, queue, BCC, rendu HTML / texte | [`facteur.md`](./facteur.md) | API d'envoi, file de travaux, compatibilité des templates |
| `mailsubscribers` | Directe | Abonnements email, listes de diffusion, nettoyage orphelin | [`mailsubscribers.md`](./mailsubscribers.md) | Normalisation des emails, suppression en maintenance |
| `newsletter` | Fonctionnelle | Couche liste de diffusion / abonnements collectifs | [`newsletter.md`](./newsletter.md) | Cohérence avec `mailsubscribers`, conservation des listes |
| `mailshot` | Fonctionnelle | Envois collectifs et suivi des destinataires | [`mailshot.md`](./mailshot.md) | Schéma des tables mailshot, file de traitement |
| `agenda` | Directe | Événements, dates, répétitions, fuseaux horaires | [`agenda.md`](./agenda.md) | Formats de date, répétitions, timezone |
| `verifier` | Directe | Validation complémentaire | [`verifier.md`](./verifier.md) | Règles serveur, messages d'erreur, compatibilité formulaire |
| `gis` | Utilisée | Géolocalisation des auteurs et notifications associées | [`gis.md`](./gis.md) | API de géocodage, adresse invalide, notifications non bloquantes |
| `destinations` | Fonctionnelle | Ventilation comptable et destinations de comptes | [`destinations.md`](./destinations.md) | Hiérarchie comptable, imports, compatibilité historique |

## Lecture recommandée

1. Lire cette matrice pour le survol.
2. Ouvrir la fiche du plugin concerné.
3. Revenir aux pages métier Blobul liées.
4. Vérifier ensuite le code source si une évolution est nécessaire.

## Ce qu'il faut retenir

- `inscription4`, `bank`, `notifications`, `cextras`, `agenda`, `verifier`, `mailsubscribers` sont les dépendances les plus structurantes.
- `Facteur` est la brique d'envoi réelle au coeur des notifications.
- `newsletter` et `mailshot` décrivent les mécanismes d'audience et d'envoi collectif.
- `saisies` et `destinations` sont des briques de base transversales, importantes pour les formulaires et la comptabilité.

## A lire en plus

- [`README.md`](./README.md)
- [`../guide_base_connaissance_ia.md`](../guide_base_connaissance_ia.md)
- [`../normalisation_corpus.md`](../normalisation_corpus.md)
