# Extraction des logiques métier du socle

## Objectif

Le plugin `association` ne doit conserver que la configuration commune, les
contrats transversaux, les migrations de compatibilité et les services qui ne
peuvent appartenir à aucun domaine métier. Les contrôleurs, formulaires,
autorisations, pages et APIs d'un domaine appartiennent à son plugin.

Les migrations historiques restent dans le socle tant que tous les scénarios
de mise à jour ne sont pas validés. Leur présence ne donne pas au socle la
propriété des données migrées.

## Lot 1 : Dons, Ventes et Prêts

Le premier lot extrait du socle :

- les actions d'édition, de suppression et d'export propres au domaine ;
- les formulaires CVT ;
- les pages privées de liste et d'édition ;
- les navigations contextuelles ;
- les autorisations métier ;
- les aliases de tables et URLs chargés par les fichiers `*_options.php` ;
- l'API comptable interne des prêts.

Les chemins SPIP des actions, formulaires et squelettes sont conservés. SPIP
les résout depuis le plugin propriétaire sans modifier les URLs publiques ou
privées existantes.

## Éléments transversaux encore conservés

Le menu principal Association reste assemblé par le socle pendant cette étape.
Il référence les modules actifs mais ne réalise aucune mutation métier.

La configuration historique des modules et les fonctions RGPD transversales
restent également dans le socle jusqu'à définition de contrats d'extension
publics. Elles ne doivent pas être déplacées par simple copie, car leur ordre
d'exécution et l'exhaustivité des exports doivent rester garantis.

## Lot 2 : Comptabilité

Le module Comptabilité possède désormais :

- les actions du journal, du plan et des destinations ;
- les formulaires CVT d'édition, d'import et de migration ;
- les APIs `inc/comptes`, `inc/destinations` et les helpers de rendu ;
- les pages privées, listes, navigations et feuilles de style comptables ;
- les référentiels JSON du plan et des destinations ;
- les exports comptables et la synchronisation des écritures d'événement ;
- les autorisations de menu, de plan, de destination et de création standard ;
- les aliases de tables chargés par `association_compta_options.php`.

Dons, Ventes et Prêts déclarent maintenant leur dépendance explicite vers
`association_compta`. Les adaptateurs historiques qui combinent autorisation
comptable et responsabilité d'événement restent provisoirement dans le socle :
ils seront remplacés avec le lot Événements par un contrat entre modules.

## Lot 3 : Adhésions

Adhésions possède désormais les cotisations, leurs catégories, justificatifs,
échéances, recherches d'adhérents, exports, pages privées, modèles auteur et
notifications dédiées. Les adaptations Inscription 4 et Champs Extras portant
sur l'édition d'un auteur sont chargées par ses propres pipelines. La création
d'un auteur, le contrôle de l'âge des enfants, la date d'inscription et le
géocodage du profil ne transitent plus par les pipelines du socle.

## Lot 4 : Événements

Événements possède les formulaires d'inscription publics et privés, les
participants, tarifs, répétitions, exports, tâches cron, notifications et pages
privées. Les pipelines d'édition et d'insertion d'un événement, auparavant
mélangés aux auteurs et aux commandes, sont maintenant déclarés par
`association_evenements`. Les tarifs des répétitions et leur affichage BO sont
donc autonomes au niveau du module.

## Lot 5 : Communication, Groupes et Paiements

- Communication porte l'envoi collectif, les gabarits, Notifications,
  Mailsubscribers, Mailshot, les destinataires et la Corbeille ;
- Groupes porte les rôles et bénévoles ; Événements déclare via Champs Extras
  les champs qui étendent exclusivement `spip_evenements` ;
- Paiements porte Bank, les transactions, remboursements et callbacks de
  règlement.

Les adaptateurs de notification propres aux adhésions et aux événements restent
dans leur plugin métier et utilisent Communication comme infrastructure.

## Lot 6 : export RGPD distribué

Le socle conserve l'orchestration de l'export d'un auteur, la date de génération
et les normalisations transversales. Il ne lit plus aucune table métier. Le
pipeline public `association_rgpd_export_auteur` reçoit `id_auteur` et `email` :

- Adhésions fournit `cotisations` depuis `spip_asso_cotisations` et ses
  catégories ;
- Événements fournit `inscriptions_evenements` ;
- Comptabilité fournit `operations_comptables` ;
- Dons, Ventes et Prêts fournissent leur section homonyme ;
- Paiements expose le formateur public d'une transaction utilisé dans les
  sections qui possèdent un `id_transaction`.

Cette séparation rend l'export extensible et empêche le socle de reprendre la
propriété implicite des tables des modules.

L'anonymisation suit désormais le même contrat : le pipeline
`association_rgpd_anonymiser_auteur` transmet l'identifiant, l'email historique
et le pseudonyme calculé. Événements, Dons, Ventes, Prêts, Comptabilité et
Paiements mettent exclusivement à jour leurs propres tables et renvoient leur
compteur au résumé transversal. Le socle ne référence plus aucune table métier
dans ses deux opérations RGPD.

## Lot 7 : maintenance BDD distribuée

La première tranche déplace dans Événements toutes les opérations qui lisent
ou modifient `spip_asso_activites` : détection et suppression des inscriptions
non validées, anonymisation, ainsi que nettoyage des participations orphelines
ou obsolètes. Le cron commun conserve l'ordre des traitements, le `dry_run`,
les seuils et le rapport consolidé. Il charge l'API du module uniquement quand
celui-ci est actif et ignore proprement cette tranche sinon.

Communication possède maintenant les suppressions Mailsubscribers et URLs,
Comptabilité les nettoyages de `spip_asso_comptes`, et Paiements ceux de
`spip_transactions`. Deux pipelines SPIP collectent les historiques encaissés
et coordonnent le nettoyage préalable à une suppression d'auteur. Le cron
transversal ne requête donc plus que `spip_auteurs` et conserve la responsabilité
de l'identité, du rapport consolidé et du mode `dry_run`.

## État résiduel du socle

Le socle conserve uniquement :

- `spip_association_metas`, la configuration commune et les migrations de
  compatibilité ;
- le menu de la suite, les ressources communes et les utilitaires génériques ;
- l'intégration RGPD et Familles transversale ;
- les derniers alias d'autorisation historiques `modifier/asso`, maintenus
  temporairement pour compatibilité avec des squelettes externes non inventoriés.

Ces alias ne créent ni ne modifient de données métier. Leur suppression exige
une recherche sur les squelettes des sites migrés et constitue le dernier lot
de rupture de compatibilité.

## Lots restant après la migration et la première recette distante

1. remplacer ou déplacer les alias historiques `modifier/asso` ;
2. achever l'audit des inclusions inter-modules et des contrats publics ;
3. rejouer la passe navigateur responsive finale sur test-fiafe.

Chaque lot doit passer les tests autonomes, le staging des dix plugins, puis une
compilation et une recette SPIP réelle avant déploiement.
