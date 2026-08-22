Suggestions d'amélioration (priorisées)

Critique / Priorité haute (sécurité / cohérence)
1) Centraliser la logique commune public/multi
   - Problème : duplication entre `inscription_evenement_public.php`, `inscription_evenement_multi.php` et `inscription_evenement_multi_public.php`.
   - Action : extraire orchestration commune (`charger/verifier/traiter`) dans `formulaires/inc/inscription_evenement_backend.php` ou fonctions réutilisables. Effort : moyen. Risque : medium (tests requis).
   - Fichiers ciblés : `formulaires/*.php` -> déplacer appels à `formater_post_form(_multi)` et `activite_enregistrement_calculator` vers helpers.

2) Durcir la validation anti-spam et honeypot
   - Problème : honeypot basé sur heure et nom de champ calculé peut produire faux positifs; filtre basé sur langues peut bloquer utilisateurs légitimes.
   - Action : remplacer honeypot par token CSRF simple + champ honeypot statique ; ajouter rate-limiting par IP et reCAPTCHA optionnel.
   - Fichiers ciblés : `formulaires/inc/inscription_evenement.php` (`verifier_spam_formulaire_inscription`) et `formulaires/*_charger_dist` (ajout token).
   - Effort : moyen. Risque : bas si déployé graduellement.

3) Sécuriser et paramétrer les domaines d'email bloqués
   - Action : déplacer la liste des domaines bloqués vers la configuration (`association_metas`) et permettre whitelist/blacklist.
   - Fichiers ciblés : `verifier_spam_formulaire_inscription`.

Haute / Priorité fonctionnelle
4) Transactions & idempotence
   - Problème : risque de double-transaction si soumission multi onglets/refresh.
   - Action : rendre l'opération d'insertion idempotente (vérifier par hash ou token de transaction avant création) ; lock/flag côté DB.
   - Fichiers ciblés : `formulaires/inc/inscription_evenement.php` (`inserer_transaction_activites`, `inserer_asso_activites`).
   - Effort : moyen/élevé. Risque : medium.

Moyen / Priorité maintenabilité
5) Tests unitaires et couverture
   - Action : ajouter tests unitaires PHP pour `formater_post_form`, `calculer_montant_total`, `generer_detail_participants` et tests d'intégration pour insertion `inserer_asso_activites` (utiliser une DB de test ou mocks).
   - Fichiers ciblés : `formulaires/inc/inscription_evenement.php`.
   - Effort : moyen. Risque : faible.

6) Meilleure structuration des logs
   - Action : utiliser logs structurés (json) et ajouter contexte (id_evenement, id_auteur, ip) pour faciliter surveillance.
   - Fichiers ciblés : multiples (où `spip_log` est appelé).

Faible / Améliorations UX
7) Messages d'erreur plus précis côté utilisateur
   - Action : uniformiser clés d'erreur et messages traduits; renvoyer champs invalides ciblés (ex: 'categorie[3]') pour focus JS.

8) Accessibilité et expérience mobile
   - Action : contrôler rendu des fieldsets, labels et tailles de champs; tester avec lecteur d'écran.

Livrables proposés
- Cette documentation (créée).
- Proposition de patch 1 : extraire une fonction utilitaire `processer_inscription_common($context)` pour réduire duplication (PR de taille moyenne).
- Proposition de patch 2 : renforcer anti-spam et rendre honeypot configurable (PR plus petite).

