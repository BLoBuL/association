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

## Lots restant avant recette distante

1. remplacer ou déplacer les alias historiques `modifier/asso` ;
2. auditer les inclusions inter-modules et documenter les contrats publics ;
3. compiler les squelettes avec un SPIP 4 réel et tester installation neuve et
   migration depuis la copie de la base DEV ;
4. déployer la suite en staging puis exécuter les recettes BO et FO sur
   test-fiafe.

Chaque lot doit passer les tests autonomes, le staging des dix plugins, puis une
compilation et une recette SPIP réelle avant déploiement.
