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

## Lot 8 : derniers accès transversaux

Les contrôles d'accès à un événement et leur résolution article/rubrique sont
portés par Événements. La configuration commune obtient désormais l'existence
d'une catégorie entreprise auprès d'Adhésions et les listes de diffusion auprès
de Communication via deux pipelines dédiés. Hors migrations de compatibilité,
le code exécutable du socle ne contient plus de requête SQL vers une table
métier de la suite.

Le bootstrap du socle ne précharge plus les bibliothèques des inscriptions,
cotisations, paiements ou comptes. Chaque plugin les charge depuis son propre
fichier `*_options.php`. La balise `#EDITEUR_DESTINATIONS` réside également
dans Comptabilité avec le formulaire qu'elle utilise.

## Lot 9 : cycle de vie des adhésions

Le cron qui contrôle les échéances, retire les privilèges expirés et programme
les notifications appartient désormais à Adhésions. Ce module déclare lui-même
le pipeline `taches_generales_cron` et fournit le génie correspondant ; le socle
ne planifie plus que sa maintenance transversale hebdomadaire.

La copie historique des cotisations depuis `spip_asso_comptes` est également
implémentée dans Adhésions. Son schéma 1.2.0 adopte la table, complète les
devises et maintient le lien comptable de manière idempotente. Le socle conserve
uniquement un chargement de compatibilité afin que ses callbacks 1.6.0 et 1.6.1
déjà publiés puissent déléguer cette migration sans rupture. Enfin, le fichier
de fonctions globales ne précharge plus les filtres du module Paiements.

## Lot 10 : callbacks de migration et composants de communication

Les callbacks de données encore appelés par les anciennes versions du schéma
sont maintenant rangés dans leur domaine : normalisation des événements et des
inscriptions dans Événements, reprise de l'ancien journal dans Comptabilité,
catégories et ancien YAML Champs Extras dans Adhésions. Le fichier
d'administration du socle ne contient plus leur implémentation ; il les charge
uniquement pour honorer la séquence de migration historique déjà publiée.

Communication fournit désormais le composant `emails/inc-email_auteur` utilisé
par les notifications d'Événements, récupéré de la logique FO historique puis
neutralisé de ses dépendances au thème et à Blobul CORE. La page commune des
notifications collecte aussi les audits métier par pipeline : elle ne connaît
plus directement la fonction d'audit des cotisations d'Adhésions.

## État résiduel du socle

Le socle conserve uniquement :

- `spip_association_metas`, la configuration commune et les migrations de
  compatibilité ;
- le menu de la suite, les ressources communes et les utilitaires génériques ;
- l'intégration RGPD et Familles transversale.

## Lot 11 : migrations historiques distribuées

Les montées de schéma 1.1 à 1.5 sont désormais exécutées par les modules qui
possèdent les tables concernées. Le fichier d'administration du socle conserve
la chronologie publiée et la table transversale `spip_association_metas`, mais
ne nomme plus aucune table métier. Adhésions prend aussi en charge la création
de sa table de cotisations lors du passage en 1.6.0 avant la copie idempotente
de l'historique comptable.

Les callbacks historiques restent chargeables sous leurs noms d'origine pour
préserver les mises à jour directes depuis les branches 2.1 et 2.2. Leur code et
leurs opérations SQL résident toutefois dans Adhésions, Événements,
Comptabilité, Communication, Dons, Ventes ou Prêts selon leur responsabilité.

## Lot 12 : début de la configuration distribuée

Les panneaux `evenement` et `evenement_defaut` du formulaire commun sont
déclarés par le module Événements. Le socle conserve l'orchestration CVT, l'URL
historique et le stockage dans `spip_association_metas`, puis agrège les saisies
du module sans modifier leurs noms ni leurs valeurs existantes. Cette première
extraction établit le contrat utilisé pour répartir progressivement les autres
panneaux métier.

Le même contrat couvre maintenant `mode_paiement` et `comptabilite`. Paiements
porte les moyens de règlement, les taxes et l'autorisation d'encaissement ;
Comptabilité porte l'exercice, le plan, les destinations et les imputations des
différents métiers. Le socle ne fait qu'agréger leurs tableaux de saisies.

Adhésions déclare également les panneaux `adhesion` et `entreprise`, y compris
les règles de validité, privilèges, cotisations, notifications d'échéance et
options multidevises. Les listes de diffusion restent obtenues par le contrat
public de Communication sans réintroduire de dépendance Blobul historique.

Les validations CVT suivent désormais la même frontière : Comptabilité vérifie
le format de l'exercice et les références comptables, Communication contrôle
les listes d'adresses de notification, et le socle agrège leurs erreurs avec
les seuls seuils de sa maintenance transversale.

## Lot 13 : suppression de l'objet d'autorisation générique

Les alias historiques `modifier/asso` ont été supprimés après inventaire du
monorepo et des squelettes/plugins réellement servis sur test-fiafe. Aucun appel
n'utilisait plus cet objet ambigu. Les actions comptables emploient désormais
exclusivement les autorisations SPIP typées `asso_compte`, tandis que les
événements conservent leurs autorisations propres.

## Lot 14 : contrats inter-modules contrôlés

L'orchestration des panneaux Événements et Paiements réside de nouveau
exclusivement dans le socle : une extraction mécanique les avait laissés à la
fin du fichier Adhésions, créant une dépendance indirecte malgré un rendu
fonctionnel. Un test d'architecture interdit désormais cette régression.

Comptabilité ne charge plus la bibliothèque d'autorisation d'Événements et ne
lit plus `spip_asso_activites`. Lorsqu'une écriture est identifiée par une
inscription, un pipeline Événements résout son contexte ; l'autorisation finale
reste demandée à SPIP avec l'objet typé `evenement`. Les retours des pipelines
catégorie entreprise et listes de diffusion utilisent le retour direct de
`pipeline()`, après dépouillement automatique de l'enveloppe `args`/`data` par
SPIP.

Enfin, les helpers Bank/trésorier résident dans Paiements et les bibliothèques
plan/destinations sont chargées par Comptabilité, plus par le formulaire du
socle.

## Lot 15 : intégration Familles rattachée aux adhésions

La prévisualisation et l'exécution idempotente de la migration des anciens
comptes principal/secondaires vers le plugin Familles appartiennent désormais
à Adhésions. Le module porte la bibliothèque, le formulaire CVT, les squelettes
privés, l'autorisation et sa contribution à `affiche_milieu`.

Les noms historiques du formulaire, de la page privée et des fonctions sont
conservés : les liens et appels existants continuent donc de fonctionner par le
chargeur SPIP. Le socle ne connaît plus ni le plugin Familles ni cette opération
de migration métier. Familles reste une intégration facultative déclarée par
`utilise`, et non une dépendance obligatoire de la suite.

La migration consomme l'API publique actuelle de Familles,
`familles_objet_lister_familles('auteur', ...)`. L'ancien helper spécialisé
`familles_lister_familles_auteur()` n'existe plus dans Familles 0.2.25 et ne
doit pas être réintroduit.

L'intégration étant facultative, la prévisualisation retourne un rapport vide
et l'exécution une erreur contrôlée lorsque Familles n'est pas actif ou ne
fournit pas cette API. L'accès privé n'est alors pas ajouté ; aucune fonction
absente ne peut provoquer de fatal PHP.

## Lot 16 : affichages et segments distribués

Les derniers panneaux d'affichage à responsabilité métier ont quitté le grand
formulaire du socle. Communication déclare la sélection des champs de segments.
Adhésions déclare les filtres de l'annuaire public ainsi que les filtres et
colonnes du tableau privé des adhérents. Événements déclare les statuts admis
dans la liste publique des inscrits.

L'onglet historique `affichage_public` est ainsi composé de deux contributions
indépendantes sans modifier les noms des metas. Les 122 valeurs persistantes du
formulaire et leur registre CLI restent strictement identiques. Le socle charge
les trois fournisseurs et agrège leurs saisies.

Cette passe a également rendu explicite le paramètre `disable_meta_admin` des
constructeurs Adhésions et Événements. Il n'est plus lu comme variable locale
indéfinie et conserve une valeur restrictive par défaut lors d'un appel direct.

## Lot 17 : helpers de configuration Adhésions

Les helpers de catégorie entreprise, zones d'accès et listes de diffusion sont
maintenant définis par Adhésions, au même endroit que les panneaux qui les
consomment. Le socle ne connaît plus les tables de catégories d'adhésion ni les
zones et n'adapte plus les listes de Communication pour Adhésions.

La liste de diffusion reste obtenue par le pipeline public de Communication ;
la dépendance Adhésions vers Communication est déjà déclarée et aucun cycle
n'est introduit. Le pipeline de catégorie entreprise reste disponible comme
contrat public pour les autres consommateurs éventuels.

## Lot 18 : export CSV des adhérents

Le générateur du formulaire d'export CSV des adhérents réside désormais dans
Adhésions, avec son action et le squelette privé qui le charge. Son chemin SPIP
`inc/fonctions/generer_export_csv` et le nom du filtre restent inchangés, ce qui
préserve le squelette historique.

L'action ne tente plus de charger `inc/fonctions/generer_exporter_csv`, fichier
qui n'existait pas et n'apportait aucune fonction. Le moteur CSV générique reste
dans le socle car il est également consommé par Événements.

## Lot 19 : fichier de fonctions du socle normalisé

`association_fonctions.php` ne contient plus les commentaires orphelins des
fonctions Événements, Communication et Adhésions déjà déplacées. Il expose
uniquement l'agrégateur RGPD, le nommage des exports, la désérialisation de
configuration et le filtre scalaire partagés.

Les préchargements globaux de `inc/actions`, `inc/editer` et `inc/autoriser`
ont été supprimés : aucune des fonctions restantes ne les consomme. Une matrice
dédiée verrouille le comportement de sérialisation, de nommage et de conversion
scalaire.

## Lot 20 : autorisations racine normalisées

`association_autoriser.php` ne conserve plus les commentaires et signatures
fantômes des autorisations déjà portées par les plugins métier. Les droits sur
les adhérents, cotisations, comptes, événements, newsletters, dons, ventes et
prêts restent déclarés uniquement par leurs modules respectifs.

Le socle expose encore les trois contrats transverses consommés par ces
modules : normalisation de l'auteur, lecture d'activation historique et
journalisation contrôlée. L'autorisation éditoriale générique d'ajout de
document est conservée sans dépendre d'un domaine métier.

## Lot 21 : pipelines racine normalisés

`association_pipelines.php` est limité aux pipelines réellement déclarés par
le paquet : ressources privées, composants jQuery UI, cron de maintenance et
composition du menu de la suite. Plus de trois cents lignes de commentaires
orphelins et le hook de saisie non déclaré ont été retirés.

La construction du menu emploie un chemin commun pour SPIP 4.0/4.1 et SPIP
4.2+, tout en laissant les autorisations et l'activation métier aux plugins
propriétaires. Le calcul d'icône utilise désormais les retours des API SPIP au
lieu d'appeler `file_exists()` sur une valeur potentiellement vide.

## Lot 22 : options métier distribuées

Les statuts de cotisation, les styles d'adhérent, le titre enrichi des auteurs
et le chargement conditionnel de la notification GIS appartiennent désormais à
Adhésions. Les statuts de participation appartiennent à Événements. Le socle
conserve uniquement la liste d'états d'adhérent partagée avec Communication,
afin de ne pas créer le cycle Communication vers Adhésions.

Le fichier `association_options.php` a également perdu ses helpers sans aucun
appel, ses commentaires de fonctions déjà déplacées et son branchement manuel
du pipeline jQuery UI, déjà déclaré nativement par `paquet.xml`. Les helpers de
date, montant, téléphone et liens privés restent transverses car plusieurs
plugins autonomes les consomment encore.

Le fragment `formulaires/update/options.html` a été supprimé. Aucun formulaire
CVT, pipeline ou squelette ne l'appelait encore ; il lisait directement les
tables Comptabilité et dupliquait les panneaux modulaires actuels. Son absence
est maintenant contrôlée par la matrice d'architecture.

## Lot 23 : reliquats Inscription2 et Inscription3 supprimés

Les fonds `inscription2_association` et `visuel_cextras` ne contenaient plus
que du HTML commenté lié à Inscription2/Inscription3. L'ancienne balise
`CONFIGURER_METAS` ne déclarait elle aussi aucune fonction active. Ces trois
fichiers sont supprimés : la suite cible exclusivement Inscription4 et ses
pipelines déclarés par Adhésions.

## Lot 24 : actifs Comptabilité déplacés

Le script d'ajout dynamique des destinations et l'icône privée `comptes` sont
maintenant fournis par Comptabilité. Leurs noms et chemins logiques SPIP ne
changent pas : les deux bibliothèques comptables existantes continuent de les
résoudre par `find_in_path()` et le menu du socle par `find_in_theme()`.

## Lot 25 : actifs orphelins supprimés

Trois icônes privées sans aucun appel et le fichier racine `style.css` ont été
retirés. Ce dernier n'était déclaré par aucun paquet ou squelette et contenait
des règles propres à un ancien thème public tiers. La feuille privée officielle
du socle reste `prive/themes/spip/css/asso.css`, déclarée dans `paquet.xml`.

## Lot 26 : modèles YAML distribués

Le modèle historique d'installation des champs auteurs et les deux variantes
famille sont fournis par Adhésions. Le modèle du champ événementiel
`webinaire_fiafe` est fourni par Événements. Le chemin logique `yaml/...` reste
identique : la migration Adhésions qui emploie `find_in_path()` reste compatible.
Le socle ne possède plus de dossier YAML métier.

## Lot 27 : raccourcis de configuration Comptabilité

Le squelette privé `extra/configurer_association.html`, composé uniquement des
imports du plan, des destinations et de la migration comptable, appartient
désormais à Comptabilité. Son chemin privé reste identique et SPIP le charge
depuis le module actif sur la page de configuration du socle.

## Lot 28 : navigation de configuration extensible

Le squelette de navigation ne connaît plus les onglets Adhésions, Événements,
Paiements, Comptabilité ou Communication. Il parcourt un registre construit par
le pipeline `association_configuration_navigation`. Chaque plugin contribue ses
propres entrées, leur ordre et leur destination ; le socle conserve seulement
Informations, Modules, Maintenance et Debug.

La visibilité de Comptabilité continue de dépendre de son activation ou du
statut webmestre. Communication porte également le lien vers la page autonome
des notifications. Cette composition suit le modèle des pipelines SPIP et
permet à chaque plugin de rester autonome.

## Lot 29 : configuration native SPIP dans les squelettes

Les appels historiques `#META{/association/...}` ont été remplacés par
`#CONFIG{association_metas/...}` dans les modules. La surcharge locale de la
balise `META` et son préchargement global sont supprimés. Les usages SPIP
standards comme `#META{timezone}` restent inchangés.

La suite utilise ainsi la balise native de lecture de configuration, sans
modifier globalement le comportement de `#META` pour les autres plugins.

## Lot 30 : inclusion native des onglets privés

Les pages privées incluent désormais directement
`prive/squelettes/top/inc-top_association` avec leur contexte métier. La balise
personnalisée `#ONGLETS_ASSOCIATION`, qui ne faisait que déléguer à ce squelette,
est supprimée. Les dix-huit pages concernées utilisent ainsi la syntaxe
`INCLURE` native de SPIP.

## Lot 31 : saisie Formidable dans Communication

La saisie auto-découverte `formulaires` conserve son nom et son chemin logique,
mais ses gabarits HTML et YAML sont fournis par Communication. Elle sélectionne
un formulaire Formidable publié pour les parcours de communication et ne
constitue donc plus une responsabilité du socle.

## Lot 32 : composant CVT Formidable dans Communication

Le composant partagé `formulaires/inc-formidable-boutons` est fourni par
Communication, sous le même chemin SPIP. Les parcours Communication,
Événements et Comptabilité conservent leurs inclusions existantes tandis que
le socle ne porte plus ce fragment d'intégration métier.

## Lot 33 : déclaration Mailsubscribers dans Communication

La suppression de la page publique de l'objet `spip_mailsubscribers` est
désormais appliquée par le pipeline `declarer_tables_objets_sql` de
Communication. La déclaration SQL du socle ne connaît plus Mailsubscribers et
reste centrée sur la table de métas transversale.

## Lot 34 : manifeste SQL minimal du socle

Le socle ne déclare plus les pipelines SQL `declarer_tables_principales` et
`declarer_tables_objets_sql`, désormais sans responsabilité racine. Son unique
pipeline de schéma conserve la table auxiliaire de métas partagée. Le callback
principal vide et la documentation orpheline associée sont supprimés.

## Lot 35 : export CSV dans Événements

L'API `inc/exporter_csv` et le fragment `inc/csv_generer`, exclusivement
consommés par l'export des participants, sont fournis par Événements. Leurs
chemins logiques restent inchangés afin de préserver les appels SPIP et les
éventuelles surcharges existantes.

## Lot 36 : exécution de maintenance distribuée

Après le traitement transversal des auteurs, le cron appelle le pipeline
`association_maintenance_bdd_executer`. Événements, Comptabilité, Paiements et
Communication exécutent chacun leurs nettoyages et enrichissent le même
rapport. Le socle ne charge plus leurs quatre bibliothèques et ne connaît plus
les fonctions de nettoyage propres aux tables métier.

## Lot 37 : maintenance des auteurs dans Adhésions

La détection, la séparation selon les encaissements, la suppression et
l'anonymisation des auteurs inactifs appartiennent à Adhésions. Une phase
`association_maintenance_bdd_preparer` s'exécute avant les fournisseurs de
nettoyage et transmet au socle uniquement le résumé et les identifiants utiles
aux autres modules.

## Lot 38 : domaine de langue Dons

Les vingt-neuf libellés consommés par Dons sont fournis par
`association_dons_fr.php` et les squelettes/CVT utilisent le domaine
`association_dons`. Les langues sans traduction dédiée bénéficient du repli
SPIP vers le français. Le domaine historique du socle n'est plus requis par ce
plugin.

## Lot 39 : domaine de langue Ventes

Les trente-quatre libellés consommés par Ventes sont fournis par
`association_ventes_fr.php`. Les squelettes et le CVT utilisent exclusivement
le domaine `association_ventes`, avec repli SPIP vers le français lorsqu'une
traduction locale n'existe pas encore.

## Lot 40 : domaine de langue Groupes

Les seize libellés des bénévoles et responsables sont fournis par
`association_groupes_fr.php`. Les modèles et squelettes privés emploient le
domaine `association_groupes`, avec repli français pour les autres langues.

## Lot 41 : domaine de langue Paiements

Les quinze libellés de transactions et remboursements sont fournis par
`association_paiements_fr.php`. Les composants et pipelines de Paiements
emploient le domaine `association_paiements`, avec repli SPIP vers le français.

## Lot 42 : domaine de langue Communication

Les cinquante-cinq libellés d'envoi collectif, corbeille et prévisualisation
sont fournis par `association_communication_fr.php`. Ils complètent les domaines
de notifications déjà propres au module. Tous les consommateurs utilisent le
domaine `association_communication` avec repli SPIP vers le français.

## Lot 43 : domaine de langue Prêts

Les cinquante-neuf libellés des ressources et réservations sont fournis par
`association_prets_fr.php`. Les pages publiques, modèles, CVT et écrans privés
utilisent exclusivement le domaine `association_prets` avec repli français.

## Lot 44 : domaine de langue Comptabilité

Les deux-cent-dix-huit libellés consommés par la comptabilité sont fournis par
`association_compta_fr.php`. Les 189 traductions historiques sont conservées
exactement ; 29 clés auparavant absentes reçoivent un libellé français
explicite. Les exports, CVT et écrans privés utilisent exclusivement le domaine
`association_compta` avec repli français.

## Lot 45 : squelettes front autonomes Paiements et Événements

Les quatre modèles de sélection de paiement historiquement surchargés par
`blobul-BANK` sont désormais fournis par `association-paiements/modeles`.
Ils conservent l’API publique du plugin Bank sans dépendre du plugin Blobul.

Les albums photo public et public verrouillé issus de `blobul-ASSO_FO` sont
fournis par `association-evenements/squelettes/inclure`. Leurs libellés utilisent
le domaine `association_evenements`; aucune référence à `zblobul_core` ne reste.

## Lot 46 : gabarit email autonome Communication

Le gabarit responsive de `blobul-CORE` est repris dans
`association-communication/emails` selon les conventions de découverte SPIP.
Il fournit la coque HTML, l’en-tête, le logo du site avec repli textuel, le
titre, le contenu, le pied et le bouton compatible Outlook. Les métas, langues,
assets et signatures propres à Blobul CORE ont été retirés : Communication peut
produire ses emails sans charger ce plugin historique.

## Lot 47 : domaine de langue Événements

Les libellés des événements, participations, inscriptions, exports et écrans
privés sont fournis par `association_evenements_fr.php`. Les traductions
historiques sont conservées et les libellés auparavant absents sont explicités.
Les informations supplémentaires standards disposent de clés d’export stables ;
un choix alternatif conserve directement son libellé administrateur, sans
construire dynamiquement une fausse clé de langue. Le module n’utilise plus le
domaine historique `association`.

## Lot 48 : domaine de langue Adhésions

Les libellés des adhérents, familles, catégories et cotisations sont fournis par
`association_adhesions_fr.php`. Le domaine contient toutes les variantes
dynamiques de type de cotisation et les douze mois, ainsi que les libellés
historiquement absents des formulaires et notifications. Les pages publiques,
CVT, modèles et écrans privés n’utilisent plus le domaine racine.

## Lot 49 : réglages GIS et réseau FIAFE

Le socle ne décrit plus les réglages métier placés historiquement dans son
panneau « modules ». Les destinataires et événements déclencheurs des
notifications GIS sont maintenant fournis par Adhésions. L’activation des
événements et profils du réseau FIAFE est fournie par Événements.

Les noms de métas historiques sont conservés afin que la migration ne modifie
aucune valeur existante. Le fournisseur Événements vérifie explicitement la
présence de `verifier_site_fiafe()` avant de l’appeler, ce qui rend le formulaire
utilisable sans le plugin historique qui fournissait cette fonction.

## Lot 50 : réglages de maintenance distribués

Le socle ne décrit plus les seuils ni les actions de maintenance métier. Il
conserve uniquement l’activation globale, le mode simulation, la taille des
lots et la commande manuelle de simulation. Chaque fournisseur déclare les
réglages correspondant à son propre exécuteur :

- Adhésions : ancienneté et traitement des auteurs inactifs ;
- Événements : délai et nettoyage des inscriptions et participations ;
- Comptabilité : ancienneté et nettoyage des écritures de cotisation ;
- Paiements : transactions orphelines ;
- Communication : abonnés et redirections obsolètes.

Un constructeur transversal du socle normalise les saisies numériques et les
choix oui/non, mais ne connaît aucun nom d’action métier. Tous les chemins
`association_metas/meta_cfg_maintenance_*` restent identiques : une base
historique conserve donc ses réglages sans migration de valeurs.

## Lot 51 : options d’exécution et validations distribuées

La séparation du formulaire est prolongée jusqu’à l’exécution. Le cron et le
bouton CVT ne connaissent plus les seuils ni la liste des actions métier : ils
construisent uniquement les options transversales, puis appellent le pipeline
`association_maintenance_bdd_configurer`. Les cinq modules ajoutent leurs
propres valeurs et convertissent les booléens historiques.

La validation CVT des seuils suit le même principe avec le pipeline
`association_maintenance_bdd_verifier_configuration`. Le socle contrôle
seulement la taille de lot ; Adhésions, Événements et Comptabilité valident
leurs seuils respectifs. Le dry-run demandé depuis le privé reste forcé, quelle
que soit la valeur stockée de l’exécution planifiée.

## Lot 52 : suppression du prototype global de rapport

Le formulaire contenait après ses fonctions un prototype de rendu jamais
exécuté dans le parcours CVT : il dépendait de variables locales hors portée,
réécrivait potentiellement le même rapport et énumérait dans le socle douze
clés de résultats métier. Ce bloc mort et son helper inutilisé sont supprimés.

Le parcours effectif conserve la génération unique du JSON sécurisé et son
affichage dans le message CVT. Le socle ne possède donc plus de catalogue des
résultats produits par Adhésions, Événements, Comptabilité, Paiements et
Communication.

## Lot 53 : registre CLI composable

Le registre des commandes `association:config:*` est désormais construit par
le pipeline `association_config_cli_registre`. Le socle conserve uniquement le
debug, l’identité de l’association et les trois réglages transversaux de
maintenance. Les cinq modules fournissent 110 définitions :

- Adhésions : 37 options ;
- Événements : 39 options ;
- Paiements : 7 options ;
- Communication : 4 options ;
- Comptabilité : 23 options.

Une fusion protégée interdit à un fournisseur d’écraser une option déjà
déclarée. La représentation canonique des 135 définitions possède exactement
la même empreinte SHA-256 qu’avant l’extraction ; les noms publics, chemins,
types, bornes, valeurs par défaut et listes autorisées sont donc inchangés.
Les snapshots v1/v2 et les commandes de lecture, écriture et restauration
restent compatibles.

## État après le lot 22

La répartition du grand formulaire de configuration est achevée pour les
domaines actuellement extraits : Adhésions, Événements, Paiements,
Comptabilité et Communication portent leurs saisies et validations métier. Le
socle ne conserve que l'orchestration CVT, la maintenance transversale et la
compatibilité de l'URL historique.

L'audit des inclusions directes a également supprimé les couplages identifiés
entre Comptabilité et Événements. Les prochaines extractions doivent désormais
être pilotées par les responsabilités restantes du socle, et non par un nouveau
découpage mécanique du formulaire déjà distribué.

La passe Chrome authentifiée et responsive a été rejouée après le lot 8 ; elle
est consignée dans `docs/recette-metier-test-fiafe.md`.

Chaque lot suivant doit passer les tests autonomes, le staging des dix plugins, puis une
compilation et une recette SPIP réelle avant déploiement.
