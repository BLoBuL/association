# Notifications des cotisations et justificatifs

## But

Documenter les emails produits par le parcours d'adhésion, leur contexte, leur déclenchement et leur contrôle sans envoi.

## Fichiers de référence

- [`inc/cotisations.php`](../inc/cotisations.php)
- [`inc/notifications_cotisations_audit.php`](../inc/notifications_cotisations_audit.php)
- [`inc/justificatifs_cotisation.php`](../inc/justificatifs_cotisation.php)
- [`action/valider_justificatifs_cotisation.php`](../action/valider_justificatifs_cotisation.php)
- [`notifications/`](../notifications/)
- [`prive/squelettes/contenu/notifications.html`](../prive/squelettes/contenu/notifications.html)

## Architecture

`notifier_cotisation_preparer_contexte()` construit un contexte plat commun. `notifications_cotisation_trouver_sujet()` résout le sujet depuis les fichiers de langue. `notifier_cotisation_envoyer()` effectue le rendu et l'envoi depuis la file de travaux.

Les emails sont mis en file par défaut. Les outils de prévisualisation peuvent demander un rendu direct, mais aucun test automatisé ne doit envoyer de message.

## Visualisation dans le backoffice

La page **Notifications** est aussi le catalogue et le prévisualiseur des emails.
Dans les onglets **Adhésions** et **Échéances**, le nom de chaque notification
ouvre son rendu dans une fenêtre de prévisualisation. Le rendu utilise le type
d'adhérent, la langue et le compte d'exemple sélectionnés dans les filtres de la
page : il permet donc de contrôler le sujet, le contenu et les données de contexte
sans envoyer l'email.

La colonne **Envoyer test** constitue une action distincte : elle déclenche un
véritable envoi vers l'adresse de test déterminée par le backoffice. Elle ne doit
pas être utilisée lors d'un simple contrôle visuel.

## Scénarios audités

La page BO **Notifications > Adhésions / Échéances** contrôle onze scénarios :

1. instructions de paiement ;
2. validation avant paiement ;
3. validation après paiement ;
4. activation de l'adhésion ;
5. nouvelle cotisation à encaisser ;
6. cotisation à valider ;
7. paiement encaissé ;
8. rappel avant échéance ;
9. adhésion échue ;
10. reçu d'encaissement ;
11. justificatifs à revoir.

L'audit vérifie :

- la présence du gabarit ;
- la résolution du sujet ;
- la présence de destinataires administrateur valides, sans afficher leurs adresses ;
- les options qui activent ou désactivent certains emails ;
- le passage par la file de travaux.

## Contexte documentaire

Les champs suivants sont disponibles dans les notifications :

| Champ | Valeur |
|---|---|
| `justificatifs_requis` | `oui` ou `non`, issu de la catégorie |
| `documents_recus` | nombre de documents liés au compte |
| `documents_controles` | nombre de liens avec `vu = oui` |
| `justificatifs_complets` | `oui` à partir de deux documents |
| `justificatifs_controles` | `oui` si le dossier complet est entièrement contrôlé |

Les emails administrateur `cotisation-attente_admin`, `cotisation-demande_admin` et `cotisation-encaissement_admin` incluent le bloc `notifications/inc/justificatifs_cotisation_admin.html`. Le bloc reste invisible lorsque `justificatifs_requis = non`.

## Retour "A revoir"

L'action sécurisée `valider_justificatifs_cotisation` :

- exige le droit `modifier` sur `asso_compte` ;
- marque les liens document `vu = non` ;
- programme `cotisation-justificatifs-a-revoir` pour l'adhérent ;
- n'envoie rien lors d'un contrôle positif ;
- ne change aucun statut de paiement ou d'adhésion.

Le message demande actuellement de contacter l'association. Il ne renvoie pas vers un dépôt public inexistant.

## Contrôle sans envoi

1. Ouvrir `ecrire/?exec=notifications&tab=adhesion`.
2. Vérifier le résumé de l'audit.
3. Cliquer sur le nom d'une notification pour visualiser l'email rendu dans la fenêtre d'aperçu.
4. Ne pas utiliser **Envoyer test** pendant un contrôle non destructif.

Commande automatisée :

```bash
php tests/run_adhesions.php
```

## Points de vigilance

- Les catégories historiques restent sur `document_justificatif = non` tant qu'une activation explicite n'est pas décidée.
- L'état documentaire ne doit jamais activer automatiquement une adhésion.
- Les adresses réelles ne doivent pas apparaître dans l'audit.
- Toute nouvelle notification doit être ajoutée au catalogue, aux langues et à la matrice.
