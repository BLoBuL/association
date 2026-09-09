# Conformité SPIP / BDC — corrections du 8 septembre 2026

Statut global : **partial**. Les corrections ci-dessous sont réalisées localement ; ce rapport ne vaut ni validation exhaustive de la suite ni recette du site test-fiafe.

Branche : `codex/conformite-spip-bdc`, issue de `f5236013` (`codex/recette-migration-suite`). Le checkout historique `master` est préservé. Aucun schéma de données, numéro de version ou site distant n'a été modifié pendant ce lot.

## Corrections fonctionnelles

- Actions historiques : ajout de contrôles métier après vérification du jeton. Les actions d'Événements contrôlent le droit sur l'événement concerné et la correspondance entre inscription et événement. Un jeton valide ne suffit plus à lui seul.
- Entrées d'actions : points d'entrée `_dist`, anciens noms conservés comme adaptateurs dépréciés pour ne pas casser les appels existants.
- Plan comptable : autorisations `autoriser_assoplan_*_dist` compatibles avec la normalisation des types par SPIP ; contrôles du chargeur, du vérificateur et du traitement ; identifiant entier et code alphanumérique correctement cité dans les requêtes ; sélection du sens à partir de `type_op` dans le formulaire.
- Suppression d'adhérents : contrôle préalable des droits sur tous les auteurs, puis passage à la corbeille par `auteur_modifier`. Les auteurs ne sont plus effacés directement avec leurs liens potentiellement encore utilisés. Attention : un échec de stockage en cours de lot n'annule pas les auteurs déjà mis à la corbeille.
- Ventes : insertion et modification par les APIs objet de SPIP. Une erreur de modification est remontée au consommateur ; la présence d'un identifiant ne suffit plus à annoncer le succès. Le rejeu est testé avec une valeur réellement différente.
- Prêts : vérification explicite des droits dans les trois étapes CVT et absence d'obligation d'imputation comptable quand Comptabilité est inactive.
- Compléments facultatifs : suppression des détections qui considéraient les modules comme actifs par défaut, ainsi que de la dépendance du code de production à une globale de tests. Les fixtures déclarent maintenant leurs modules actifs.
- Campagnes événementielles : nouveau contrat facultatif `association_programmer_campagne`, fourni par Communication. Le producteur ne modifie plus directement les tables Mailshot et n'abonne pas automatiquement les participants. La sélection est limitée à l'événement autorisé. Une campagne sans destinataire valide, sujet ou contenu est refusée.
- Saisies : dépendance explicite d'Adhésions, chargement par `include_spip`, suppression d'un `include_once` relatif silencieux et d'un résultat vide qui masquait l'absence de l'API.
- Export historique des adhérents : remplacement de `mysqli_num_rows` par `sql_count` et du dernier appel `mysql_real_escape_string`, incompatible PHP 8, par des identifiants entiers et `sql_in`. **Cela ne suffit pas à qualifier l'export PDF**, voir les réserves ci-dessous.
- Migration historique Champs Extras : initialisation des variables passées par référence. Quelques libellés des formulaires Événements et Comptabilité ont été déplacés dans les langues.

## Langues et compatibilité minimale

Les 31 fichiers de langue retournent désormais leur tableau. Ils conservent l'affectation de la globale uniquement lorsque le chargeur `lire_fichier_langue` est absent.

Cette exception est volontaire : le noyau SPIP 4.0.0 ignore la valeur retournée par un fichier de langue et attend la globale. Supprimer cette compatibilité au nom d'une règle de style ferait régresser la version minimale demandée.

Référence contrôlée : tag SPIP `v4.0.0`, commit `39ea7576e05e9d582d12e779340944d6f5111712`, fichiers `ecrire/inc/traduire.php`, `ecrire/inc/autoriser.php` et `ecrire/action/editer_objet.php`.

## Qualité et preuves

- Configuration ECS avec le jeu de règles SPIP officiel fourni par `spip-league/easy-coding-standard`. Normalisation initiale de 336 fichiers, puis second passage de convergence. Contrôle final : **0 erreur, 0 différence** sur son périmètre. Les dépendances, traductions, tests et documentation sont exclus de ce contrôle de style.
- `composer test` lance chaque script dans un processus distinct. Une sortie PHP Warning, Fatal error, Parse error ou Deprecated constitue désormais un échec, même si le processus retourne zéro.
- 153 scripts passent, dont les nouveaux tests des droits sur 13 actions, du contrat de campagne avec/sans fournisseur, des autorisations du plan et des 31 fichiers de langue avec les deux modes de chargement.
- Contrôle de syntaxe final : 542 fichiers PHP vérifiés, aucun échec (hors dépendances `vendor` et `lib`).
- Le contrôle renforcé a révélé un faux positif existant : quatre squelettes étaient lus à leurs anciens chemins et le test réussissait malgré les avertissements. Il contrôle désormais les fichiers dans leurs modules et échoue s'ils manquent.
- Sept tests statiques dépendaient de la syntaxe `array(...)`, des guillemets ou de l'indentation ; leurs attentes ont été adaptées à la syntaxe SPIP normalisée sans retirer les assertions métier.
- Résolution des droits du plan testée séparément avec le véritable `autoriser_dist` de SPIP 4.0.0 : 6 cas passent, avec des doublures pour l'environnement et les objets. Ce n'est pas une installation SPIP complète.
- `composer validate` et `git diff --check` passent.
- PHPStan niveau 0 est configuré, sans baseline ni masquage global : **194 diagnostics restent présents** (142 fonctions, 34 classes, 10 constantes de classe, 8 méthodes non résolues). Le SDK seul ne décrit pas toutes les APIs SPIP et externes ; certains diagnostics concernent aussi les anciennes classes PDF réellement absentes. Ce contrôle reste donc **non validé**.

## Travaux restant à qualifier ou corriger

1. Restaurer et tester les anciens exports PDF : bibliothèques `pdf/*` absentes, ancien modèle de reçu fiscal et anciennes conventions de champs. Ne pas considérer le remplacement de l'appel MySQL comme une remise en service de ces fonctions.
2. Compléter l'environnement d'analyse avec les sources des versions exactes des dépendances ; distinguer APIs externes disponibles et véritables symboles absents. Ne pas créer de fausses fonctions de production pour faire taire PHPStan.
3. Poursuivre la revue des écritures SQL métier et des APIs de plugins tiers. La centralisation de Mailshot dans Communication n'est pas encore une conversion de son stockage interne vers l'API Newsletter ; celle-ci doit conserver la sélection ponctuelle des destinataires sans abonnement implicite.
4. Achever la revue des exports, de leurs champs autorisés et des références historiques Inscription3. Vérifier les contrats d'Inscription4 avant tout renommage de clé de configuration.
5. Rejouer une installation SPIP complète et la migration DEV sur copie de test, puis compiler les squelettes et réaliser les parcours BO/FO authentifiés avec les combinaisons de modules. Les tests synthétiques ne démontrent pas ces points.
6. Commit, publication de la branche et déploiement ne sont pas réalisés dans ce lot. Aucun email, paiement ou changement de données distant n'a été déclenché.

## Références

### Complément du 9 septembre : PDF et organisme émetteur

- Adhésions utilise SpiPDF, conserve la sélection du formulaire CSV et ne lit
  que les champs connus, autorisés et réellement présents. Les auteurs
  webmestres et à la corbeille restent exclus comme dans l'export existant.
- Dons mutualise l'identité de l'organisme avec le socle et ajoute ses quatre
  précisions fiscales par le pipeline de configuration. Seul un aperçu
  explicitement non valable fiscalement est disponible ; les anciennes
  bibliothèques absentes ne sont plus appelées.
- Les trois modules exportateurs déclarent SpiPDF. Le socle ne l'exige pas.
- Le filtre PDF encode deux fois les valeurs : le nettoyage de SpiPDF 2.2.1
  effectue un décodage HTML avant mPDF. Le test vérifie qu'un balisage utilisateur
  reste du texte et ne devient pas une image distante.
- Recette locale réelle : noyau SPIP 4.0.0, base SQLite synthétique isolée,
  SpiPDF 2.2.1 / mPDF8, PHP 8.4.23 avec GD et SQLite chargés pour ce test.
  Quatre documents générés : 140 lignes sur sept pages, sélection courte,
  sélection vide et spécimen Dons. Accents, fin du tableau et balisage littéral
  contrôlés ; pages rendues et inspectées. Une dépréciation du noyau SPIP 4.0
  sous PHP 8.4 demeure dans cet environnement, pas dans le code du plugin.
- Ces preuves locales ne valent pas encore recette distante, qualification
  fiscale ni validation exhaustive des combinaisons de plugins.

Référence fiscale consultée pour les champs, sans certification juridique :
[formulaire officiel 2041-RD](https://www.impots.gouv.fr/formulaire/2041-rd/recu-des-dons-et-versements-effectues-par-les-particuliers-au-titre-des-articles).

- BDC Blobul : profil `agents/AGENTS_DEV.md` et recommandations SPIP de la BDC routée par le dépôt.
- [API de gestion des objets éditoriaux SPIP](https://programmer.spip.net/1019).
- [API des autorisations SPIP](https://programmer.spip.net/1020).
- [Convention des noms d'autorisations](https://programmer.spip.net/La-librairie-autoriser).
- [API Newsletter et bulkstart](https://contrib.spip.net/API-Newsletter).
- Mailshot `v3.4.3`, commit `c51518959540d06694c0355def2eb62b1531b76b` : consultation de `newsletter/bulkstart.php` et `inc/mailshot.php`, sans modification de cette dépendance.
