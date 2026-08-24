# Recette métier SPIP 4 sur test-fiafe

Cette matrice consigne les parcours réellement servis sur
`https://test-fiafe.blobul.com`. Elle distingue les preuves navigateur, les
contrôles serveur et les tests automatisés. Une page qui se charge sans erreur
ne suffit pas à valider son parcours métier.

## Environnement contrôlé

- SPIP 4.4.21 et PHP 8 ;
- dix plugins de la suite Association actifs ;
- installation vérifiée par `spip association:installation:verifier` ;
- 14 tables métier, 12 objets SQL SPIP et 7 versions de schéma conformes ;
- branche déployée : `codex/recette-migration-suite`.

## Matrice courante

| Module | Parcours | Preuve | État |
| --- | --- | --- | --- |
| Socle | accueil privé webmaster | H1, navigation et menus métier servis sans erreur fatale | validé |
| Socle | autorisations rédacteur | activités et bénévoles accessibles ; adhérents, cotisations, comptabilité, dons, prêts, ressources, ventes, destinations et configuration interdits | validé |
| Adhésions | cotisation gratuite documentée | auteur sans email, catégorie gratuite, refus sans deux justificatifs, création avec deux PNG synthétiques et notification désactivée | validé |
| Adhésions | nettoyage de recette | documents physiques et SQL, cotisation, compte et auteur synthétiques supprimés ; relecture à zéro | validé |
| Événements | inscription gratuite à l'événement public 230 | auteur synthétique sans email, responsable synthétique, soumission publique, activité créée sans transaction ni notification, contrôle BO puis nettoyage ciblé | validé |
| Événements | nettoyage de recette | activité et auteurs synthétiques supprimés, responsable d'origine restauré ; relecture à zéro | validé |
| Comptabilité | écriture manuelle | montant nul et champs obligatoires refusés ; création à 0,01, redirection vers `exec=comptes`, puis suppression ciblée relue | validé |
| Dons | création et comptabilisation | date impossible et montant négatif refusés ; création à montant nul, écriture liée puis suppression ciblée relue | validé |
| Ventes | création et comptabilisation | date, quantité et montants invalides refusés ; création à montant nul, écriture liée puis suppression ciblée relue | validé |
| Prêts | liste globale | toutes les ressources sont listées sans faux message « ressource introuvable » | validé |
| Prêts | ressource puis réservation | création BO d'une ressource à 0 EUR, affichage public, réservation de 7 jours et affichage public de l'état | validé |
| Prêts | état sans restitution | « Non restituée » remplace la date SQL sentinelle `0000-00-00` | validé |
| Prêts | nettoyage | suppression transactionnelle ciblée, puis relecture : 0 prêt et 0 ressource synthétiques | validé |
| Communication | abonnement newsletter | squelette public et formulaire natif Newsletter servis ; adresse invalide bloquée nativement, sans inscription ni envoi | validé sans envoi réel |
| Groupes | bénévoles, rôles et autorisations | page bénévoles rendue, accessible au rédacteur ; module sans table propre, champs complémentaires portés par Champs Extras | validé selon le périmètre actuel du module |
| Paiements | intégration Bank | inscriptions gratuites sans transaction, scénarios automatisés payants et redirections Bank, icônes indépendantes des plugins Blobul ; aucun paiement réel déclenché | validé sans débit externe |
| Front office | accueil, profil, inscription, événement, ressources et newsletter | H1, absence d'erreur fatale et absence de débordement horizontal sur les pages contrôlées | validé pour le rendu ; scénarios métier encore détaillés ci-dessus |
| Interface | Font Awesome | glyphes calculés avec `Font Awesome 6 Free` sur adhérents, activités, bénévoles, comptabilité, cotisations et configuration | validé |

## Défauts trouvés et corrigés pendant la recette

1. La devise du prix de location dépendait de l'ancienne méta
   `/association/symbole` et produisait « Prix de la location (en ) ». Le
   formulaire utilise désormais la devise configurée par Intl ; preuve servie :
   « Prix de la location (en EUR) ».
2. La page d'une ressource précise affichait aussi le bloc alternatif global
   « Aucune ressource ». Ce bloc est désormais limité au mode liste globale.
3. Un prêt en cours exposait `0000` comme date de retour. Le catalogue affiche
   désormais l'état traduit « Non restituée ».
4. Les traitements de dons, ventes, ressources, plan comptable et membres
   appliquaient une expression régulière aux champs POST tableaux. Sous PHP 8,
   la création d'un don provoquait un `TypeError`. Les normalisations de dates
   ignorent désormais explicitement les valeurs non textuelles.
5. Les crochets de `name="drop[]"` fermaient prématurément un bloc optionnel du
   squelette des ventes et exposaient `[( |oui)]`. Ils sont encodés dans le
   source et restitués par le navigateur sous le nom attendu `drop[]`.
6. Une écriture comptable valide redirigeait vers la page legacy
   `exec=asso_comptes`. La cible est désormais la page SPIP 4 `exec=comptes`.
7. Les routes de création et d'édition de cotisation ne possédaient aucun H1.
   Elles servent désormais exactement un titre privé « Ajout de cotisation ».
8. L'API de cotisation écrasait la justification préparée par le formulaire avec
   une chaîne codée en dur et mal encodée (`nÂ°`). Elle conserve désormais le
   libellé fourni, avec repli sur la traduction métier.
9. Un échec de suppression SPIP après dissociation pouvait laisser un
   justificatif orphelin. Le helper réassocie désormais le document à la
   cotisation avant de retourner l'erreur.
10. Le détail des participants d'une inscription événement recevait aussi des
    identifiants POST vides ou non scalaires et déclenchait une dépréciation
    sous PHP 8. Le générateur ignore désormais ces valeurs avant toute lecture.
11. Le tableau de bord d'un événement ne possédait pas le titre principal
    attendu par les pages privées SPIP 4. Il sert désormais exactement un H1
    portant le titre de l'événement.
12. Les pipelines d'extension `association_inscription_evenement_verifier` et
    `association_inscription_evenement_traiter` étaient appelés sans être
    déclarés par le module Événements. SPIP consignait donc une fonction
    `execute_pipeline_*` absente à chaque inscription. Les trois pipelines
    charger, vérifier et traiter sont désormais déclarés comme points
    d'extension sans handler interne.
13. Les traces diagnostiques `IE_*` d'un parcours événementiel normal étaient
    écrites au niveau `CRITIQUE`. Elles passent au niveau `DEBUG` ; seule une
    impossibilité métier réelle de créer une liste de diffusion reste critique.
14. La page canonique d'ajout d'une écriture comptable cumulait le H1 de sa
    composition et celui de l'ancien fragment inclus. La composition réutilise
    désormais le titre métier unique du fragment, y compris sur la route legacy.
15. Les filtres partagés de la page Notifications restaient dans le compagnon
    d'un seul squelette. Les inclusions fournies par Adhésions et Événements
    étaient donc compilées sans `listes_notifications`,
    `exemple_adherent_par_type` ni le catalogue métier. Communication charge
    désormais ce compagnon depuis son fichier global de fonctions.
16. L'installation du socle sur une base historique appelait encore la fonction
    supprimée `association_declarer_champs_extras()`. Le socle n'installe et ne
    désinstalle désormais que `spip_association_metas`; chaque module reste seul
    propriétaire de ses tables et Champs Extras.

## Migration isolée depuis la base dev

Le 23 août 2026, la base de `dev.blobul.com` a été exportée sans aucune écriture
sur la base source, puis restaurée dans une base SQLite confinée à un répertoire
non publié. L'exporteur SPIP ne créait pas `spip_asso_activites`; les 41 lignes
ont été ajoutées au dump à partir d'une lecture directe de la table source avant
la restauration. Ce contournement concerne le protocole de copie, pas la
migration du plugin.

La première exécution a détecté puis permis de corriger l'appel Champs Extras
résiduel du socle. Une seconde copie fraîche a ensuite validé :

- activation d'Inscription 4.1.14 et des dix plugins de la suite ;
- mise à jour de tous les schémas, puis seconde exécution idempotente sans mise
  à jour restante ;
- 26 écritures comptables, 41 inscriptions d'événements, 14 catégories
  d'activités, 6 catégories d'adhésion, 3 ventes et 156 métas historiques
  conservées ;
- 10 écritures historiques reconnues comme cotisations et exactement 10 lignes
  créées dans `spip_asso_cotisations` ; aucune cotisation sans compte, aucun
  compte de cotisation non lié et aucune devise vide ;
- vérificateur final conforme : 10 plugins, 14 tables, 12 objets SQL et 7
  schémas ; compilation de 186 squelettes privés et 10 publics sous SPIP
  4.4.21.

## Non-régression

- 66 points d'entrée `tests/test_*.php` réussis, y compris les tests propres aux
  modules métier ;
- 274 fichiers PHP contrôlés sans erreur de syntaxe ;
- compilation réelle des squelettes vérifiée par les pages privées et publiques
  après purge du cache ;
- aucune donnée synthétique des parcours prêts/ressources, dons, ventes et
  comptabilité ne subsiste, y compris dans `spip_asso_comptes`.

## Journaux

L'erreur SQL `Unknown column 'date_acquisitionDESC'` du 23 août à 03:21 est
antérieure au correctif du modèle et ne se reproduit plus. Les parcours suivants
n'ont produit aucune nouvelle erreur SQL Association.

La recette événementielle synthétique a volontairement utilisé un auteur sans
email afin qu'aucun message réel ne puisse partir. Le job public de notification
a donc consigné l'absence de destinataire, sans envoi. Ce résultat ne constitue
pas un échec d'envoi réel ; les tests automatisés couvrent séparément la création
unique du job attendu pour une inscription publique.

Deux messages restent observés immédiatement après certaines purges globales de
cache : pipeline `taches_generales_cron` momentanément indisponible et connexion
SQL nommée vide. Ils ne sont pas attribués à Association sans reproduction hors
reconstruction de cache ; les pages normales et le vérificateur d'installation
restent fonctionnels.

Après le déploiement `6464e4f`, les appels directs aux pipelines vérifier et
traiter ne produisent plus de fonction `execute_pipeline_*` absente. La passe
navigateur effectuée à partir de 10:45 n'a produit aucune erreur Association,
erreur SQL, dépréciation ou erreur fatale. Le seul message de niveau erreur est
le pipeline cron transitoire apparu après la purge globale de cache.

La passe finale a contrôlé treize listes et tableaux de bord privés en bureau et
mobile, neuf écrans de détail ou d'édition, les six squelettes publics de la
suite, puis la matrice d'autorisation rédacteur. Toutes les pages Association
ont un titre, aucune ne déborde horizontalement et aucune ne rend de fatal. La
page d'accueil fournie par le thème actif conserve un débordement de 8 px à
390 px ; ce défaut ne se reproduit sur aucun squelette public Association et
reste hors du dépôt de la suite.

## Audit final de déploiement

Le staging des dix plugins a été reconstruit depuis le commit `b97fd9e`, puis
les dix dossiers actifs ont été remplacés ensemble sur test-fiafe. Les SHA-256
des 742 fichiers du staging concordent avec les fichiers servis. L'ancien
ensemble temporaire et l'archive de transfert ont été supprimés après les
contrôles.

Après ce redéploiement complet :

- `plugins:maj:bdd` n'annonce aucune mise à jour nécessaire ;
- `association:installation:verifier` confirme les 10 plugins, 14 tables,
  12 objets SQL et 7 schémas ;
- les pages accueil privé, adhérents, activités, comptabilité, événement public
  et ressources publiques ont été rejouées sans fatal ;
- Font Awesome reste servi par `Font Awesome 6 Free` ;
- aucun journal contrôlé depuis 10:55 ne contient d'erreur, dépréciation,
  erreur SQL ou trace critique.

La recette serveur et la migration historique sont validées.

## Passe Chrome authentifiée après extraction du socle

Après le déploiement `b994ae2a`, une nouvelle passe authentifiée a contrôlé les
pages privées adhérents, cotisations, activités, comptes, dons, prêts, ventes,
bénévoles, notifications, configuration et gestion des plugins. Chacune sert
son titre métier sans fatal PHP ni bloc d'erreur.

La route réelle `exec=editer_asso_cotisation&id_compte=957` sert le formulaire
métier complet : catégorie, statut, première inscription ou renouvellement,
justification, notification et trois justificatifs. La régression du formulaire
simplifié n'est donc plus présente sur le code déployé.

Le formulaire de cotisation a été observé à 390 et 768 px dans Chrome : aucun
débordement horizontal n'est mesuré. Les pages publiques accueil, événement,
profil et inscription ont été rejouées, puis l'événement a été contrôlé à 390,
768 et 1440 px. Aucun fatal n'est rendu et les consoles Chrome des parcours BO
et FO ne contiennent ni avertissement ni erreur.

Le débordement propre à l'accueil du thème et le code de sortie non nul de la
commande externe `spip test:spip` malgré tous ses contrôles affichés en vert
restent consignés hors périmètre Association.

## Contrôle après distribution complète de la configuration

Le déploiement `713e752c` a été vérifié côté serveur après actualisation du
registre des pipelines SPIP. Les six panneaux distribués retournent leurs
structures complètes : Adhésions (8 fieldsets, 18 champs), Entreprise (4/10),
Événement (4/18), Valeurs événement (1/14), Paiements (3/6) et Comptabilité
(1/20). Les validations propres à Communication, Comptabilité et à la
maintenance transversale sont toutes appelées.

Le contrat Événements résout l'activité de contrôle vers l'événement 228, et
les contrats Communication retournent cinq listes de diffusion. Les modules
Adhésions, Événements, Paiements et Comptabilité sont chargés depuis leurs
propres fichiers ; l'ancien alias d'autorisation générique `modifier/asso`
reste absent.

Les 66 tests autonomes passent sur ce commit. Une nouvelle passe Chrome a été
engagée, mais la prise de contrôle de tout onglet authentifié bloque actuellement
au niveau de l'extension avant même la lecture du DOM. Cette indisponibilité ne
remet pas en cause la passe visuelle précédente ; elle empêche seulement de
produire une nouvelle preuve visuelle postérieure à `713e752c` tant que la
connexion Chrome n'est pas rétablie.

## Lot 15 — migration Familles portée par Adhésions

Le socle et la copie active du plugin `association_adhesions` ont été déployés
depuis `7308ffd`, puis le correctif de compatibilité et de dégradation contrôlée
depuis `a02d4a7`. Les deux emplacements servis possèdent l'empreinte SHA-256
`e33087d098fe9665b4df486f19cf709d6e8c83e0d28e2c94a1e105dd90728619`
pour `inc/association_familles.php`.

La preuve SPIP réelle confirme que le formulaire CVT, le contenu privé, le lien
privé et la bibliothèque sont tous résolus depuis `plugins/association-adhesions`.
L'autorisation est accordée à un administrateur complet et le pipeline actif
contient `association_adhesions_affiche_milieu`.

Le plugin Familles n'est pas installé sur test-fiafe : l'intégration annonce
donc `disponible=false`, ne rend pas le lien de migration et retourne un rapport
vide sans fatal PHP. La migration positive et son idempotence restent couvertes
par les tests autonomes contre l'API Familles 0.2.25. L'accueil public répond
`200`; la page privée non authentifiée répond normalement `302` vers la
connexion.

## Lot 16 — panneaux d'affichage et segments distribués

Le commit `ad5f4eb` a été déployé atomiquement dans les quatre emplacements
actifs concernés : socle, Adhésions, Événements et Communication. L'artefact
unique possède l'empreinte SHA-256
`08c7ea62e8f6ff7021aa195aba2904b8f6ead7ad66373f3b4154700d9de91dc0`.

Dans le SPIP réellement servi, `segments` expose toujours `selection_segment` ;
`affichage_public` compose `config_filtres_annuaire` depuis Adhésions et
`config_statuts_liste_publique_inscrits` depuis Événements ; `affichage_prive`
expose toujours les filtres et colonnes des adhérents. Les trois fichiers
fournisseurs sont résolus depuis leurs plugins actifs et leurs fonctions sont
chargées.

Le nombre exact de champs disponibles sur le serveur varie avec les plugins
facultatifs actifs, mais les cinq clés déplacées sont présentes. La matrice
autonome, qui fixe le périmètre complet attendu, conserve exactement ses 122
configurations persistantes. L'accueil public répond `200` après purge ; les
seules erreurs SQL retrouvées dans les fichiers de log sont antérieures de plus
de six heures au déploiement.

## Lot 17 — helpers de configuration Adhésions

Le commit `9366ad8` a été déployé dans le socle et le plugin Adhésions actif à
partir de l'artefact SHA-256
`a168b2d958d1b3c717f799477d5c025d07e3f92e94f51e0953a4e022164968ba`.

La recette SPIP réelle conserve les huit fieldsets et dix-huit champs du panneau
Adhésions, les quatre fieldsets et dix champs Entreprise, ainsi que les cinq
listes de diffusion fournies par Communication. La catégorie entreprise est
détectée et les panneaux déplacés au lot 16 restent intégralement servis.

Les validations Communication, Comptabilité et maintenance passent toujours,
et la résolution activité vers événement retourne encore l'événement attendu
228. Les 66 tests autonomes passent avant déploiement.

## Lot 18 — export CSV des adhérents

Le commit `629e695` a été déployé dans le socle et Adhésions depuis l'artefact
SHA-256 `da44202d4465413fdc33a8783d7119b41214ab2a0e675762622f67b496f76cfe`.
Le serveur résout désormais `inc/fonctions/generer_export_csv.php` depuis le
plugin Adhésions actif ; le fichier n'existe plus dans le socle.

Une exécution SPIP réelle avec un critère vide charge le filtre, produit le
formulaire HTML, son action signée et le bouton de génération CSV sans erreur.
Les 66 tests autonomes passent avant déploiement.

## Lot 19 — fonctions transversales du socle

Le commit `5709134` a été déployé dans le socle depuis l'artefact SHA-256
`230fd8a6941c610be63fedd8c3e15bf83d1f4a8ac63088d5139b93deeb731cfc`.
La suite comprend désormais 67 tests autonomes, tous validés.

Dans le SPIP servi, l'agrégateur RGPD reste chargé, le nom d'export produit
`export-association-test-fiafe_blobul_com-42-2026-08-23.json`, les valeurs PHP
sont correctement détectées et désérialisées, et le filtre scalaire conserve
son comportement historique. Le formulaire de configuration se charge encore
et son panneau d'informations contient son fieldset attendu.

## Lot 20 — autorisations transversales du socle

Le fichier d'autorisations racine est réduit aux contrats transverses utilisés
par les modules et à l'autorisation éditoriale des documents. Une assertion
d'architecture interdit désormais d'y réintroduire les domaines cotisation,
comptabilité, événements ou communication. Les 67 tests autonomes passent
avant déploiement.

Le commit `3edfe82` a été déployé atomiquement dans le socle depuis l'artefact
SHA-256 `d3cfb99c7c09f83e0e6b7f0410cf80ec908e0aaacceee1bb41e6cc9d0637d7ad`.
Le SPIP servi résout le fichier depuis `plugins/association`, charge les quatre
contrats transverses attendus et délègue encore correctement les menus
Adhérents et Comptes à leurs plugins actifs. Les erreurs retrouvées dans les
journaux concernent les anciens plugins Blobul le 22 août et non ce lot.

## Lot 21 — pipelines transversaux du socle

Le fichier de pipelines racine ne conserve que les cinq fonctions réellement
utilisées par le paquet. Le hook mort de déclaration d'une saisie et toute la
documentation des traitements déjà déplacés ont été supprimés. La matrice
d'architecture interdit leur réintroduction.

Le commit `bfb1f26` a été déployé atomiquement depuis l'artefact SHA-256
`9f342168fb5b89858c8dc5b5ebd78719543b0d591ac53e5af2c3f19544d26e50`.
Dans le SPIP servi, le cron reste planifié à 604800 secondes, les trois
composants jQuery UI sont dédupliqués et le menu est une instance native
`Spip\\Admin\\Bouton`. Ses huit sous-menus autorisés et l'icône du socle sont
résolus sans erreur depuis les plugins actifs.

## Lot 22 — options métier distribuées

Les constantes d'exécution propres aux cotisations, adhérents et événements
sont chargées depuis leurs plugins propriétaires. Le socle ne conserve que les
helpers réellement partagés et le chargement des metas Association. Les
frontières sont verrouillées par la matrice d'architecture.

Le fragment historique `formulaires/update/options.html`, sans appel ni code
CVT associé, est supprimé plutôt que déplacé dans Comptabilité : ses réglages
sont déjà servis par la configuration modulaire actuelle.

## Lot 23 — retrait des surcharges Inscription2 et Inscription3

Les deux fonds intégralement commentés et la balise de configuration sans code
actif sont supprimés. Aucun appel n'existait dans le monorepo ; la matrice
d'architecture contrôle désormais leur absence au profit d'Inscription4.

Le commit `3ff0572` a été déployé atomiquement dans le socle depuis l'artefact
SHA-256 `526eab5216f6e61d2e2b0be29fdab83927e16c2fd0f1542493b850f6ba17645a`.
Le path SPIP ne résout plus aucun des trois reliquats. Inscription4 4.1.14 et
Association-Adhésions 4.0.0 restent tous deux actifs après purge du cache.

## Lot 24 — actifs statiques de Comptabilité

Le script des destinations analytiques et l'icône du menu Comptes quittent le
socle pour le plugin Comptabilité, sans changement de leur chemin logique SPIP.

Le commit `6cfbdbb` a été déployé atomiquement dans le socle et Comptabilité
depuis l'artefact SHA-256
`fbda5fc1b40650c4e634c5e6dcb31e1b075788a5a6cbe36485481c30cfc32644`.
Le path SPIP résout désormais le script et l'icône exclusivement depuis
`plugins/association-compta`; la fonction JavaScript d'ajout reste présente.

## Lot 25 — actifs orphelins

Les trois icônes privées non référencées et la feuille publique de thème tiers,
jamais chargée par le paquet, sont supprimées du socle.

Le commit `13cef38` a été déployé atomiquement dans le socle depuis l'artefact
SHA-256 `45a223bf329c271c7c34201036dfc39ca858bff538a85982f292b29411de9ddf`.
Le serveur confirme leur absence et conserve la feuille privée déclarée
`prive/themes/spip/css/asso.css`.

## Lot 26 — modèles YAML métier

Les trois modèles de champs auteurs sont déplacés dans Adhésions et le modèle
`webinaire_fiafe` dans Événements, tout en conservant leurs chemins logiques
pour les imports et migrations existants.

## Lot 27 — raccourcis de configuration comptables

Les raccourcis de la colonne extra de la configuration sont fournis par
Comptabilité sous le même chemin de squelette privé.

Le commit `8fef1f7` a été déployé atomiquement dans le socle et Comptabilité
depuis l'artefact SHA-256
`b55dd36e6c57cece4b377f7d4f4ea057b4fc9d9c03fee97ddd69c8bd0b1f6de1`.
Le SPIP servi résout le squelette depuis `plugins/association-compta` et y
retrouve les raccourcis d'import du plan et de migration.

## Lot 28 — navigation de configuration distribuée

La navigation privée est désormais composée par le pipeline
`association_configuration_navigation`. Les cinq plugins propriétaires
déclarent leurs entrées et le squelette racine ne contient plus de liste métier
codée en dur.

Le registre a été déployé dans les six emplacements actifs depuis le commit
`a8ebbd4` et l'artefact SHA-256
`d24638d8e7b8f7ea9d3815740f7af3c919890db6fbce14a236a52765b9d317d0`.
L'actualisation des métadonnées de plugins a été exécutée avec
`plugins:maj:bdd`, nécessaire après l'ajout d'un pipeline dans les manifests.

Deux défauts détectés par la première compilation réelle ont été corrigés : le
retour `data` natif de `pipeline()` dans `4ea5487`, puis l'autonomie du
fournisseur Compta dans `4218eac`. Après redéploiement, le registre expose les
quatorze entrées attendues dans le bon ordre et le squelette produit 2283
octets de HTML avec les URLs Adhésions et Notifications. Les anciennes erreurs
du journal précèdent le dernier correctif et ne se reproduisent plus.

## Lot 29 — balises de configuration SPIP

Tous les accès à la table `association_metas` emploient désormais la balise
native `#CONFIG`. La surcharge historique de `#META` est supprimée du socle.

Le commit `11ef25e` a été déployé dans les six emplacements actifs depuis
l'artefact SHA-256
`64d9a3e424e71ecbd3d65babe4880c97b3395f5510c39ae68f5e699ecb974817`.
Le serveur ne résout plus `balise/meta.php` et les quatre familles de
squelettes contrôlées utilisent toutes `#CONFIG`. Le modèle public et la
navigation privée compilent réellement.

La compilation a révélé puis permis de corriger `nl2br(null)` sous PHP 8 dans
le modèle de profil. Le correctif `20a009f`, déployé depuis l'artefact SHA-256
`a54326579f7b60a6d73989015375878c5e9c9b3a6b1394674660290c63447e7c`,
supprime l'avertissement ; la compilation est désormais propre.

## Lot 30 — onglets privés natifs

Les dix-huit pages privées remplacent `#ONGLETS_ASSOCIATION` par une inclusion
SPIP explicite. Le compilateur de balise historique est supprimé.

Le commit `21039f3` a été déployé atomiquement dans le socle, Adhésions,
Comptabilité, Événements et Groupes depuis l'artefact SHA-256
`c27d27c8330da39eaf4d7b72bf38a5c15d54f8a794f722b70f5aa8562a558b6b`.
Le serveur ne résout plus la balise supprimée. Les cinq squelettes privés
représentatifs utilisent l'inclusion native et compilent sans erreur, avec des
sorties comprises entre 602 et 606 octets.

## Lot 31 — saisie Formidable dans Communication

La saisie auto-découverte `formulaires` est déplacée sans modification dans le
plugin Communication. Le commit `136f654` a été déployé atomiquement dans le
socle et Communication depuis l'artefact SHA-256
`3a721490b43f9149ff61433d68781115bf90c850778fbb495654c5caec52e671`.
Le SPIP servi résout les fichiers HTML et YAML depuis
`plugins/association-communication/saisies`, ne conserve aucune copie dans le
socle et compile le champ de sélection sans erreur.

## Lot 32 — boutons CVT Formidable dans Communication

Le composant partagé de boutons CVT est désormais fourni par Communication.
Le commit `0933240` a été déployé atomiquement dans le socle et Communication
depuis l'artefact SHA-256
`c15f2ee6a1dfa31f269a2fd04b5c66482ce54b9be18738f5434f605a8ee09a3d`.
Le serveur le résout depuis Communication, confirme les trois consommateurs
Communication, Événements et Comptabilité, puis le compile sans erreur en 214
octets.

## Lot 33 — déclaration SQL Mailsubscribers

La personnalisation de l'objet Mailsubscribers est fournie par Communication.
Le commit `e53da85` a été déployé atomiquement dans le socle et Communication
depuis l'artefact SHA-256
`6f6de7c3a8637e5dbdb2baf06dc1f2582b0e10147ae2e74f4e0e4cd7c7d9c912`,
puis le registre des plugins a été actualisé. Sur le SPIP servi, le pipeline
actif retire bien l'URL et vide la page publique de `spip_mailsubscribers`,
tandis que la déclaration SQL du socle ne référence plus cet objet.

## Lot 34 — manifeste SQL minimal

Les pipelines SQL racine sans traitement sont supprimés. Le commit `2ff56c3`
a été déployé atomiquement dans le socle depuis l'artefact SHA-256
`4d1d63aef42300e0d8bd0dfc338e2f6c5dbff5161fe75f8404dd5ae46f686f7b`,
puis le registre des plugins a été actualisé. Le serveur confirme que le socle
ne déclare plus les pipelines principal et objets, que sa table auxiliaire de
métas reste déclarée et que le pipeline objet de Communication reste actif.

## Lot 35 — export CSV des événements

L'API CSV et son fragment privé sont désormais fournis par Événements. Le
commit `bec3ba7` a été déployé atomiquement dans le socle et Événements depuis
l'artefact SHA-256
`a95fb2cfb01b073999fe15ef145ea9dc5aed719b2cf7bdf7cefe09265fb808ca`.
Le serveur résout les deux ressources depuis Événements, charge réellement
`inc_exporter_csv_dist`, produit une ligne CSV conforme et compile le fragment
en 495 octets sans erreur.

## Lot 36 — maintenance distribuée

Le cron transversal délègue désormais les nettoyages métier à quatre
fournisseurs. Le commit `98b8815` a été déployé dans le socle, Événements,
Comptabilité, Paiements et Communication depuis l'artefact SHA-256
`0fefe8702fdcee77ea981a2f24822208388940918830a133604599e27287825a`,
puis le registre des plugins a été actualisé. Un dry-run réel sur la base
test-fiafe charge les quatre fournisseurs, produit les onze clés métier
attendues et laisse inchangés les effectifs des six tables contrôlées.

## Lot 37 — maintenance des auteurs dans Adhésions

La phase auteurs appartient désormais à Adhésions. Le commit `680868d` a été
déployé atomiquement dans le socle et Adhésions depuis l'artefact SHA-256
`1e98e8a8b326c5a9c18ad8c6053817ce841ff01fb2a8bd5d33d59e8ac1598e95`,
puis le registre des plugins a été actualisé. Le dry-run serveur charge la
phase Adhésions, produit le résumé auteurs et les résumés métier, confirme
l'absence de l'API auteurs dans le cron racine et laisse inchangés les
effectifs des six tables contrôlées.

## Lot 38 — domaine de langue Dons

Dons utilise désormais son domaine `association_dons`. Le commit `43770f4` a
été déployé atomiquement dans le plugin Dons depuis l'artefact SHA-256
`501cacfa1cb9b6991bb23e711bb58c353b662ec8704eb7b3d7f882adfe7f5163`.
Le serveur résout le fichier français depuis Dons, restitue les trois libellés
représentatifs attendus et compile le CVT en 2038 octets sans erreur. La page
privée complète conserve son garde d'autorisation, observé en CLI par sa 404
normale hors session.

## Lot 39 — domaine de langue Ventes

Ventes utilise désormais son domaine `association_ventes`. Le commit
`073d141` a été déployé atomiquement dans le plugin Ventes depuis l'artefact
SHA-256 `e2e38b379650817fc8cb0e683c510ea2ae8e96faf3b224ad683280dfe9ef4c53`.
Le serveur résout le domaine depuis Ventes, restitue les libellés de liste et
d'ajout attendus puis compile le CVT en 2369 octets sans erreur.

## Lot 40 — domaine de langue Groupes

Groupes utilise désormais son domaine `association_groupes`. Le commit
`868b9ab` a été déployé atomiquement depuis l'artefact SHA-256
`81dbc9c2b71c21d36cad80512ef8fd8679301c638bc913001daab631581670e1`.
Le serveur résout le domaine depuis Groupes, restitue les libellés Bénévoles et
Activité puis compile le modèle représentatif en 1023 octets sans erreur.

## Lot 41 — domaine de langue Paiements

Paiements utilise désormais son domaine `association_paiements`. Le commit
`ee70131` a été déployé atomiquement depuis l'artefact SHA-256
`0219a18559119fade822f023bb531c41f3b49e0d116dfa101497699f0b42fc28`.
Le serveur résout le domaine depuis Paiements, restitue les libellés de statut
et remboursement puis compile le formulaire en 1292 octets sans erreur.

## Lot 42 — domaine de langue Communication

Communication utilise désormais son domaine `association_communication`. Le
commit `0d524fb` a été déployé atomiquement depuis l'artefact SHA-256
`1fa6b553a5d2f2419eb8bbb6a075ae836b55a6bf8590143a95c34bf2eccd78c7`.
Le serveur confirme les clés statiques, la famille dynamique des sujets
d'événements et la clé Newsletter préexistante, puis compile le formulaire de
gabarit en 387 octets sans erreur.

## Lot 43 — domaine de langue Prêts

Prêts utilise désormais son domaine `association_prets`. Le commit `472476a`
a été déployé atomiquement dans le seul plugin Prêts depuis l'artefact SHA-256
`2957c681f59ded9cb0c09e7362f374df8f4624e9c364da5387125ba1b496017b`.
Le serveur restitue les libellés « Liste des ressources » et « Liste des
réservations », compile le CVT d'édition d'une ressource en 2103 octets et
valide l'installation complète : 10 plugins actifs, 14 tables, 12 objets SQL
SPIP et 7 schémas à jour.

## Lot 44 — domaine de langue Comptabilité

Comptabilité utilise désormais son domaine `association_compta`. Le commit
`23431b9` a été déployé atomiquement dans le seul plugin Comptabilité depuis
l'artefact SHA-256
`5da78ab6aeed5703c99cb548977ab593746f94a1ec700e82dc89dbe668cc76e6`.
Le serveur restitue une clé historique et deux des nouveaux libellés français,
compile le CVT du plan comptable en 2596 octets et valide encore l'installation
complète. L'accès direct au squelette privé sans session conserve son garde 404
normal ; sa recette visuelle authentifiée reste dans la campagne navigateur.

## Lot 45 — squelettes front Paiements et Événements

Le commit `0287584` a été déployé atomiquement dans Paiements et Événements.
Les artefacts ont pour SHA-256
`dc4bcdcaaaffcb9db966043a0819825f50a26ca602df903225fa96a404c1c7b9`
et `bc3125cffae85d66476e124a212fc51e03916c8953bb389855e6f4649f0eab90`.
Le SPIP servi compile les quatre modèles de paiement (577 octets chacun), les
deux albums sans données de contexte et résout le nouveau domaine événementiel.
L’installation complète reste valide.

## Lot 46 — gabarit email Communication

Le commit `5b8b88c` a été déployé atomiquement dans Communication depuis
l’artefact SHA-256
`6e5c56ff09ccac5739bfb1fb71569681fc6cee2b9a8121d72fd028d4698b6fe1`.
Le rendu serveur du gabarit autonome produit 2837 octets, contient la coque
responsive et le contenu injecté, et ne contient aucune référence `zblobul`.

## Lot 47 — domaine de langue Événements

Le commit `4bb0937` a été déployé atomiquement dans Événements depuis
l’artefact SHA-256
`0891c1d5f24811d1485f7a321cd1aeab51892881c81f761a46b1ea3e215acdb5`.
Le serveur résout les clés historique, export dynamique et montant gratuit,
compile le CVT de catégorie, puis valide encore les 10 plugins et leurs schémas.
Les formulaires publics sans événement retournent normalement une sortie vide ;
leurs parcours avec données restent couverts par la recette navigateur.

## Lot 48 — domaine de langue Adhésions

Le commit `36fba13` a été déployé atomiquement dans Adhésions depuis
l’artefact SHA-256
`3a7dd78ee1923359c792f3ba25f438661a6ddffd122c8b2453479fdf9da28f8e`.
Le serveur résout un message de cotisation, le mois dynamique décembre et un
compteur d’adhérents auparavant absent. Les deux CVT d’édition compilent, le
profil public produit 19295 octets et l’installation complète reste valide.

## Lot 49 — réglages GIS et réseau FIAFE

Le commit `fc06681` a été déployé atomiquement dans le socle, Adhésions et
Événements depuis l’artefact SHA-256
`ec7eb72c838abb63d19a0d49ad929b7f6efd835bca059f4976c5b0ba71327b67`.
Les trois fichiers servis ont exactement les empreintes de l’état Git, le cache
a été vidé et les trois plugins restent actifs. Sur test-fiafe, GIS est inactif
et la fonction historique `verifier_site_fiafe()` n’est plus fournie : les deux
panneaux conditionnels sont donc correctement absents, sans notice ni erreur.
Le formulaire racine reste exécutable dans ce contexte autonome et
`spip test:spip` valide SPIP, PDO, la base et la version 4.4.21.

## Lot 50 — réglages de maintenance distribués

Le commit `818e14a` a été déployé atomiquement dans le socle, Adhésions,
Événements, Comptabilité, Paiements et Communication depuis l’artefact
SHA-256 `4af2f722bb07d3b44ec2ae3657f7c0b8a2879426bc673ce0e39a0cc92517caed`.
Les sept fichiers de configuration contrôlés ont exactement les empreintes de
l’état Git. Le formulaire servi expose 19 champs de maintenance et rattache les
réglages représentatifs aux cinq fieldsets métier attendus, tandis que les
réglages globaux restent dans le socle.

Un dry-run réel charge les cinq fournisseurs, retourne 12 clés de résultat et
laisse inchangés les effectifs des six tables contrôlées. Les six plugins sont
actifs, `spip test:spip` reste sain et le journal SPIP ne contient aucune erreur
sur les quinze minutes couvrant le déploiement et la recette.

## Lot 51 — options d’exécution et validations distribuées

Le commit `55b9ae9` a été déployé atomiquement dans le socle et les cinq
fournisseurs de maintenance depuis l’artefact SHA-256
`ec7540c959752eaaf568abf0f1c0dd10884157aa447d7f6df022d28f4dbe9370`.
Le registre des plugins a ensuite été actualisé afin de charger les deux
nouveaux pipelines.

Dans le SPIP servi, un jeu synthétique confirme les trois seuils, les douze
actions, les conversions oui/non et le dry-run forcé. Le pipeline de validation
refuse zéro pour Adhésions et une valeur non numérique pour Événements, tout en
acceptant le seuil comptable valide. Un dry-run construit depuis les métas
réelles charge 12 actions et retourne 12 résultats sans modifier les six tables
contrôlées. `spip test:spip` est fonctionnel malgré son code de sortie historique
et aucun journal d’erreur n’est présent sur la fenêtre de recette.

## Lot 52 — suppression du prototype global de rapport

Le commit `03eb1fa` a été déployé atomiquement dans le socle depuis l’artefact
SHA-256 `6e550e6d6b029c756cf4c754ac4e857bb8584486d38c5bba0d4dd3601947adba`.
Le fichier PHP servi passe le lint et ne contient plus le helper ni le bloc
global morts. L’assemblage réel conserve ses 19 champs ; le dry-run charge
toujours 12 actions et 12 résultats sans modifier les six tables contrôlées.
Aucune erreur SPIP n’est relevée sur la fenêtre du déploiement.

## Lot 53 — registre CLI composable

Le commit `6d22162` a été déployé atomiquement dans le socle, Adhésions,
Événements, Comptabilité, Paiements et Communication depuis l’artefact
SHA-256 `40618e9c5a569a6aede36327402392d4c12e18afd167c91101853e31214fa14b`.
Le registre des plugins et le cache ont été actualisés avec le binaire SPIP
CLI canonique.

La commande servie `association:config:lire --format=json` retourne les 135
options attendues. Les six échantillons couvrant le socle et chacun des cinq
fournisseurs sont présents : `maintenance.active`, `adhesion.validite`,
`evenement.inscription`, `paiement.modes_adhesion`, `affichage.segments` et
`comptabilite.active`. `spip test:spip` valide encore PDO, SPIP et la version
4.4.21 ; aucun fatal, appel non capturé ou doublon du registre n’apparaît dans
les journaux de la fenêtre de recette.

## Lot 54 — inventaire d'installation distribué

Le commit `4695033` a été déployé atomiquement dans les dix plugins depuis
l'artefact SHA-256
`9684bab6f2d6cc7d55aa21a343080f678ebea1d0feb646766e2cab171f25f78e`.
Le registre des plugins et le cache ont été actualisés avec SPIP CLI.

La commande réelle `association:installation:verifier` assemble les dix
contributions et confirme 10 plugins actifs, 14 tables présentes, 12 objets SQL
SPIP complets et 7 versions de schéma exactes. `spip test:spip` confirme PDO,
SPIP et la version 4.4.21. Aucun fatal, fournisseur absent ou conflit de schéma
n'apparaît dans les journaux couvrant le déploiement et la recette.

## Lot 55 — compatibilité des snapshots CLI v1

Le commit `67d897d` a été déployé atomiquement dans le socle et Événements
depuis l'artefact SHA-256
`154d18d58cb11949613cbaa6685c63343a40eae02de8bf556a7b33b2202fddba`.
Après actualisation du registre et du cache, l'API servie reconstruit exactement
24 options du format v1 : 12 options `evenement.*` fournies par Événements et
12 options `debug.*` fournies par le socle. Le vérificateur d'installation reste
valide et aucun fatal ou échec de validation de snapshot n'est journalisé.

## Lot 56 — migrations historiques composables

Le commit `5ad0ab7` a été déployé atomiquement dans le socle et les sept
modules qui possèdent des migrations historiques depuis l'artefact SHA-256
`3ab8df82fe5562f32392f1d4e2618ec5acdb2ff64af2f2d2ebed870aef7cefd3`.
L'actualisation du registre n'avait aucune migration à rejouer sur la base déjà
à jour.

Sur le SPIP servi, le pipeline reconstruit 55 jalons ; le jalon 1.1.0 contient
ses 7 opérations dans l'ordre attendu et la sérialisation complète possède
l'empreinte historique
`aeefc0bcd675e66c3ad52eeb6b033c031f59508cad4c8460a9e0641b91982670`.
Le vérificateur d'installation reste valide et aucun fatal ni message de
migration anormal n'est présent dans les journaux de la recette.

## Lot 57 — catégories de logs distribuées

Le commit `fa6c676` a été déployé atomiquement dans le socle, Adhésions,
Communication, Comptabilité et Événements depuis l'artefact SHA-256
`559e467e902f13a6538b977fc322687089286d875e840a13a0e6e0dea43bb274`.

Le SPIP servi expose exactement 12 catégories dans l'ordre historique :
autorisations, cotisations, notifications, inscriptions, comptabilité,
adhérents, cron, spam, email, GIS, migration et synchronisation. Le registre
CLI conserve simultanément ses 135 options. L'installation reste valide et
aucun fatal ou défaut du pipeline n'est journalisé.

## Lot 58 — navigation privée distribuée

Le commit `8bd2c3b` a été déployé atomiquement dans le socle, Adhésions,
Comptabilité, Dons, Événements, Groupes et Prêts depuis l'artefact SHA-256
`bc16e45a243a0b23c42f3b4121d280c12d98f20862b16c04618a43286b3051af`.

Le pipeline servi retourne exactement les huit entrées dans l'ordre attendu :
Adhérents, Cotisations, Activités, Bénévoles, Dons, Comptes, Prêts et
Paramètres. Le vérificateur des dix plugins reste valide et aucun fatal ou
défaut de composition n'est journalisé. Chrome expose une session privée
test-fiafe authentifiée, mais son contrôle a expiré deux fois lors de la
première lecture DOM, y compris sans navigation : la preuve visuelle du menu
rendu reste donc à reprendre, sans remettre en cause les preuves serveur.

## Lot 59 — configuration entièrement composable

Le commit `11918c6` a été déployé dans le socle et les cinq fournisseurs depuis
l'artefact SHA-256
`5a86910a488f3944518c6397348a997d73de33370097db78f5dd43a9af10c150`.
La première sonde CLI a révélé que SPIP retourne directement `data` pour un
pipeline structuré `args/data`, contrairement au double compatible du test.
Le correctif `f73863e`, couvert par un double reproduisant désormais la forme
native, a été redéployé dans le socle depuis l'artefact SHA-256
`392e47c2cf95646b4b7686397dd90401da69ec438a700cf052ec4cc6c564be3e`.

Le serveur charge les cinq fournisseurs. Leur ordre brut de pipeline est
50/10/40/20/30, ce qui prouve l'utilité du tri explicite ; le CVT final restitue
bien Communication, Adhésions, Événements, Paiements puis Comptabilité avant
les fieldsets transversaux. Il expose 30 saisies racines et 127 noms de saisies
au total, dont les 122 configurations persistantes couvertes par le registre
CLI. L'installation reste valide et aucune erreur ne subsiste après la sonde de
validation finale.

## Lot 60 — statuts internes rattachés à Adhésions

Le commit `85e8e46` a été déployé atomiquement dans le socle, Adhésions et
Communication depuis l'artefact SHA-256
`9996ca7474b5e096b0091ba9dbc4027848563367d7eabb27e015bfb529cff274`.
Dans le contexte SPIP servi, Adhésions expose exactement la liste historique
`sorti, prospect, ok, echu, relance`. L'installation des dix plugins reste
valide et Communication peut lire la liste sans notice.

## Lot 61 — actifs front historiques autonomisés

Les commits `1360b16` puis `6333560` intègrent les actifs historiques dans leur
plugin propriétaire et rendent effectif leur usage public. Les 72 tests
autonomes passent. Le test front parcourt désormais Paiements `modeles`,
Communication `emails` et Événements `squelettes`, et refuse toute référence
résiduelle à `blobul-BANK`, `blobul-CORE` ou `blobul-ASSO_FO`.

Le correctif final a été déployé atomiquement dans Événements depuis l'artefact
SHA-256
`30122c91190c9f3bf3e18138a4b634975ed0b046f83df8f33d4587592fabaa19`.
Le SPIP servi confirme 10 plugins actifs, 14 tables, 12 objets SQL et 7 schémas
valides. La compilation réelle de la page de l'événement 230 produit 24 464
octets et contient à la fois le portfolio et le formulaire d'inscription. La
condition finale réserve le portfolio complet aux sessions dont
`statut_interne=ok`; les autres visiteurs utilisent le squelette verrouillé.

## Lot 62 — répétition de migration depuis la base DEV

Une sauvegarde fraîche de `dev.blobul.com` a été restaurée dans un SPIP 4
isolé, hors chemin public et hors base de test-fiafe. Elle contient notamment
26 comptes, 10 cotisations, 41 activités, 14 catégories d'activité, 6
catégories d'adhérent, 3 ventes et 156 métas Association.

La sauvegarde initiale reproduit l'anomalie historique : SPIP ne peut pas
créer `spip_asso_activites` dans SQLite à cause de la colonne réservée
`transaction`. Les 41 lignes ont donc été réinjectées dans la table déclarée
par Événements 1.2.0, en conservant la valeur sous
`tarifs_selectionnes`. Le nombre de lignes et l'empreinte canonique des valeurs
avant/après sont identiques
(`c4153b33e271949218168ea9e59bf908737f365b1f9325147a2ae3ab17aa96c5`).

Le vérificateur réel confirme ensuite 10 plugins, 14 tables, 12 objets SQL et
7 schémas à jour. Une seconde exécution de `plugins:maj:bdd` est idempotente.
Enfin, une nouvelle sauvegarde SPIP inclut bien les 41 activités avec
`tarifs_selectionnes`, sans colonne `transaction`; son empreinte SHA-256 est
`07f7339b414cd0ff9b29b02fe3ad6844e0d7dca506a2d7368af54ca6ec502a7d`.

Cette preuve valide le scénario de reprise DEV. La migration MySQL et les
parcours servis sur test-fiafe sont contrôlés dans l'étape de déploiement du
même lot.

Le commit `9120472` a ensuite été déployé atomiquement dans Événements et
Comptabilité depuis l'artefact SHA-256
`70b31051e352ebc9ac4681b4ecd1ab1f7f3fb50cfd6a024194b754c5c1c7707a`.
Sur MySQL, les 41 lignes sont conservées et l'empreinte avant/après reste
`55d7102115e08fe2ae40aaf07d50cffa32904202a1f57bdf6c387073eba33a52`.
La méta passe de 1.1.0 à 1.2.0 ; une seconde mise à jour ne trouve plus aucune
opération. Le vérificateur confirme 10 plugins, 14 tables, 12 objets SQL et 7
schémas à jour. Une sauvegarde SPIP réelle contient les 41 activités avec
`tarifs_selectionnes`, sans `transaction` (SHA-256
`b06243722426e5be2039618b5eaf04a755764f88fa73ebc524f2db339dcca0d3`).

La première passe Chrome a détecté deux défauts de chargement issus de la
scission, corrigés et redéployés : les champs extras chargeaient encore
`association_options` au lieu des options Événements (`118a3c9`, artefact
`ab7e986796515286663defa9919d281c60d2f48d8ddaf176ac7afab9b861c2d5`),
puis la surcharge du formulaire Agenda appelait l'API d'édition avant son
inclusion (`95f0451`, artefact
`64c0952985ec0659ee0bd8128c766e79a87e27c830415aadc696a3daa2305b2e`).

Après ces corrections, Chrome authentifié valide sans fatal :

- la page publique de l'événement 230 et son formulaire d'inscription ;
- la page privée Activités et ses huit entrées de navigation distribuées ;
- la fiche privée de l'événement et son tableau de bord d'inscription ;
- la liste des inscriptions, ses raccourcis CSV/XML et le formulaire BO
  d'ajout ;
- le formulaire Agenda complet, y compris responsables, participation
  financière, tarifs, modes de paiement, accompagnants et liste d'attente.

Le contrôle HTTP sans session reste bloqué par Cloudflare en 403, tandis que le
parcours Chrome authentifié est bien servi. La tentative de forcer les largeurs
390/768/1440 dans cette session Chrome est restée à la largeur native de 1920
pixels ; elle ne constitue donc pas une preuve responsive et devra être rejouée
sur une surface acceptant réellement l'émulation de viewport.

## Lot 63 — matrice navigateur de la suite autonome

Une passe Chrome authentifiée a ensuite ouvert les huit pages privées fournies
par la navigation distribuée : Adhérents, Cotisations, Activités, Bénévoles,
Dons, Comptabilité, Prêts et Paramètres. Chaque page possède son titre et son
contenu métier, sans fatal ni erreur d'exécution. Les écrans exposent notamment
les filtres de cotisation, la liste des activités, les actions de dons, les
totaux comptables, les ressources de prêts et les onglets de configuration
composés par les modules.

La matrice publique couvre les cinq usages réellement portés par les sources
historiques FO et les modules autonomes :

- `inscription` : formulaire d'inscription ou message connecté ;
- `profil` : fiche adhérent et édition du compte ;
- `newsletter` : abonnement public fourni par Communication ;
- `ressources` : catalogue public fourni par Prêts ;
- `evenement&id_evenement=230` : contenu, portfolio conditionnel et formulaire
  d'inscription fourni par Événements.

Ces cinq pages sont servies dans le squelette standard SPIP 4, avec leurs
formulaires présents et sans fatal. Les autres modules (Comptabilité, Dons,
Groupes, Paiements et Ventes) conservent leurs écrans BO ou leurs modèles
embarquables : les sources historiques BANK/CORE/FO n'y définissent pas de page
publique autonome supplémentaire à recopier.

## Lot 64 — hiérarchies et retours privés prioritaires

Le commit `f9f4ffc` a été déployé atomiquement dans Adhésions, Comptabilité,
Événements et Groupes depuis l'artefact SHA-256
`cb884bf94276775d4585b75cf31b0eea93a937e50ab4e3ef2a2b2d816c2f79b3`.
Le vérificateur conserve 10 plugins, 14 tables, 12 objets SQL et 7 schémas à
jour.

Chrome authentifié confirme les six parcours servis, sans fatal :

- `Suivi des inscriptions` : Accueil > Activités > Suivi ;
- `Export des événements` : Accueil > Activités > Export ;
- `Analyse comptable des activités` : Accueil > Comptabilité > Analyse ;
- `Bénévoles` : Accueil > Adhérents > Bénévoles ;
- `Recherche avancée` : Accueil > Adhérents > Recherche avancée ;
- `Suppression de cotisation` : Accueil > Cotisations > Supprimer.

La colonne gauche de chacune de ces pages contient le retour vers sa section
métier. La suppression a seulement été affichée sur une cotisation existante :
aucune action destructive n'a été soumise pendant la recette.

## Lot 65 — navigation métier complète

Le commit `70fedfa` a été déployé dans Communication, Comptabilité, Dons,
Paiements, Prêts et Ventes depuis l'artefact SHA-256
`4f48b1803c62c301f2aa71d2f58df2f96acc9b8361aa22589a397479b0ab9999`.
Les 74 tests autonomes passent et le vérificateur réel conserve les dix plugins
et leurs schémas à jour.

Chrome authentifié valide sans fatal les quinze parcours du lot : comptes,
édition d'une opération, destination et plan comptable, édition d'un don,
d'une ressource et d'une vente, import des destinations et du plan, migration
comptable, fiche transaction, abandon, remboursement, suppression et
notifications. Les actions Bank et suppressions ont uniquement été affichées ;
aucun bouton d'action n'a été soumis.

La recette a détecté puis fait corriger trois titres : Dons et Ventes
affichaient une clé générique inexistante, et la migration reprenait le titre de
l'import des destinations. Le correctif `4271834` a été redéployé depuis
l'artefact SHA-256
`1888fb6e4fcce0292d94c50f9351eb14d14bc8752c9a8d9a9cb4ab417f74c11e`.
Les fils d'Ariane servis affichent désormais « Mettre à jour le don »,
« Mettre à jour la vente » et « Migration des données comptables ».

## Lot 66 — relecture des anciennes hiérarchies

Le commit `fd96567` a été déployé dans Adhésions, Comptabilité et Événements
depuis l'artefact SHA-256
`9dfea2d5ab315b83263f7a19314ff3a4f023de8ec860e7b4bac008b83b170d13`.

Chrome authentifié confirme sans fatal les quatre corrections : édition de
cotisation, édition d'une catégorie d'activité, édition d'une catégorie de
cotisation et analyse comptable des activités. Les fils d'Ariane affichent les
sections intermédiaires attendues et les colonnes gauches proposent le retour
vers Cotisations, Catégories ou Activités. Aucun formulaire n'a été soumis.

## Lot 67 — compilation des actifs front autonomes

Le compilateur SPIP contrôle désormais les dossiers standards `modeles/`,
`emails/` et `notifications/` de chacun des dix plugins, en plus des pages
publiques et privées. Cette extension a nécessité le chargement explicite de
`public/assembler` dans le contexte SPIP CLI afin de rendre disponible
`styliser_modele()` comme dans une requête web normale.

Sur test-fiafe, SPIP 4.4.21 compile sans erreur 221 squelettes privés, 12 pages
publiques et 62 composants front. Le manifeste déployé correspondait au commit
`8b334497`; l'installation conservait 10 plugins actifs, 14 tables, 12 objets
SQL et 7 schémas à jour.

## Lot 68 — API unique des responsables d'événement

Les fonctions historiques `liste_responsables_evenement()` et
`responsables_evenement()` ont été supprimées au profit d'une API métier typée.
Les notifications, l'email collectif, les deux exports CSV et les affichages
privés consomment une liste plate d'identifiants. Le champ extra Agenda reçoit
séparément ses choix et ses valeurs par défaut. Les formats historiques CSV et
sérialisés restent lisibles et seuls les auteurs internes actifs sont retenus.

Le premier contrôle navigateur a révélé qu'un responsable sélectionné pouvait
ne plus être auteur de l'article parent : le groupe était alors vide dans le
formulaire, avec un risque de perte silencieuse à l'édition. L'API réinjecte
désormais les responsables sélectionnés et encore actifs dans les choix, sans
leur accorder de droit éditorial supplémentaire.

La sonde SPIP sur les données servies audite 16 événements ouverts, dont 14 avec
responsables et 15 sélections actives. L'empreinte de la répartition est
`3e2fcb00a7425fced66cd6a76020e8b53c0d5e8091b41b70ab47ecc6b964b1f3`.
Chrome authentifié confirme ensuite sur l'événement de recette que le champ
Responsables contient une case sélectionnée, et que la fiche Activités affiche
sa section Responsables sans fatal. Aucun formulaire ni email n'a été soumis.

## Lot 69 — identifiants et cycle des prêts

Le module Prêts 4.0.2 porte le schéma 1.1.1. Les colonnes historiques
`id_ressource` (`VARCHAR`) et `id_emprunteur` (`TEXT`) deviennent des `BIGINT`
indexés. La migration refuse explicitement une valeur non numérique ou un lien
vers une ressource absente avant toute conversion. Sur SQLite, où l'affinité de
type n'impose pas cette reconstruction, la déclaration canonique s'applique aux
installations neuves et les valeurs continuent d'être validées par le métier.

Une fixture bornée a été insérée sur le schéma 1.0.0 avant déploiement avec les
anciens identifiants textuels. La première implémentation fondée uniquement sur
`maj_tables()` a correctement été rejetée par la vérification, car SPIP n'altère
pas le type d'une colonne existante. La migration 1.1.1 emploie donc un `ALTER
TABLE ... MODIFY` explicite sur MySQL/MariaDB, puis laisse `maj_tables()` créer
les index déclarés.

La fixture a été conservée pendant la conversion, puis le cycle réel a validé
les transitions `reserve` pour un prêt ouvert et `ok` après restitution. Elle a
ensuite été supprimée. L'état final de test-fiafe est propre : zéro ressource et
zéro prêt de recette, aucun identifiant invalide, aucun lien orphelin, aucun
statut incohérent, et deux colonnes servies en `BIGINT(20) NOT NULL`.

Le formulaire Ressource conserve maintenant le statut existant en édition, le
formulaire Prêt refuse une ressource absente et synchronise son statut dans la
transaction, et la suppression retrouve elle-même la ressource depuis le prêt.
Chrome authentifié confirme sans fatal les listes Ressources et Prêts, le
formulaire de création d'une ressource, le formulaire du prêt de recette et le
statut Réservé effectivement coché sur la ressource existante. Aucun formulaire
navigateur n'a été soumis.

## Lot 70 — calcul calendaire des adhésions

Le helper historique global `NbJours()` a quitté le socle. Son unique appelant,
le génie des échéances, utilise maintenant
`association_adhesions_nombre_jours()` fourni par Adhésions. Les 76 tests
autonomes passent et test-fiafe compile 221 squelettes privés, 12 pages
publiques et 64 composants front. Le manifeste servi correspond au commit
`5f6e4c23`; les dix plugins, quatorze tables, douze objets SQL et sept schémas
restent valides.

La page d'accueil publique a été rejouée dans Chrome après le déploiement. Le
contenu, la navigation et le formulaire Inscription 4 sont rendus et la console
JavaScript ne contient aucune erreur. La recette visuelle révèle toutefois de
nombreux avertissements PHP 8.4 imprimés avant et au milieu du HTML par
`accesrestreint` 6.3.1 (`inc/accesrestreint.php`). Cette anomalie appartient à
une dépendance externe à la suite Association ; elle n'est pas masquée ni
attribuée aux squelettes autonomes du monorepo.

## Lot 93 — objets du formulaire comptable distribués

Le commit `43516bf1` est déployé depuis l'artefact SHA-256
`7c886b06ddca7fe472b99edba3a23a1ee6ad73073cf199ea3a02c5f12037561e`.
Les 87 tests autonomes passent. Le serveur confirme dix plugins actifs,
quatorze tables, douze objets SQL SPIP et sept schémas à jour ; il compile 221
squelettes privés, douze pages publiques et 64 composants front sous SPIP
4.4.21. `spip test:spip` conserve son code 1 connu malgré des contrôles SPIP,
PDO et webmestres tous positifs.

Après régénération du cache des pipelines, Chrome authentifié affiche dans le
formulaire générique les six choix attendus : Autres, Don, Prêt, Vente,
Cotisation et Événement. Avec `id_evenement=183`, l'objet et le sélecteur sont
verrouillés sur « Balade urbaine du samedi » et l'imputation de recette est
présélectionnée. Aucun formulaire n'a été soumis et aucune donnée métier n'a
été modifiée. Le premier chargement, réalisé avant la régénération effective du
cache des pipelines, avait seulement affiché « Autres » et journalisé le
pipeline désactivé ; le recalcul suivant a confirmé les six contributions.

## Lots 94 à 96 — langue minimale et filtres propriétaires

Le lot 94 (`c4fe0adc`) est déployé depuis l'artefact SHA-256
`0b9b6478c27d26020cc9923a965b8994db188c24d1b173b6d5b9eeba1c765355`.
Le domaine `association` ne contient plus que dix clés transversales. Le serveur
résout « Vie associative », « Notification par email » depuis Événements et
« Cotisations » depuis Adhésions. Chrome authentifié confirme les menus, la
liste des activités et leurs actions sans clé de langue manquante.

Le lot 95 (`a07e3a55`, artefact
`aad7a1d99713e94e38c3c8798a0c7317e8ec15376218190e9867515130c1d2ec`)
remplace le filtre absent `local_to_utc` par `date_iso` et retire la syntaxe
invalide de `strtoupper` dans les notifications Événements. Une compilation
complète produit 221 squelettes privés, douze pages publiques et 64 composants
front sans ajouter une seule ligne au journal SPIP.

Le filtre de période d'Adhésions est ensuite extrait du socle au commit
`1428fa0c`. La première recette navigateur a correctement détecté quatre appels
courts `scalar_val` encore présents dans les squelettes, malgré les tests PHP.
Le correctif `f7da0ef0`, déployé depuis l'artefact SHA-256
`55933990a94a82ff7c323ba8795075e13abc9883202820c0399b863a14e51522`,
préfixe ces quatre appels et les couvre par le test. Chrome authentifié affiche
de nouveau la liste de treize adhérents, les périodes et les filtres sans table
d'erreur de squelette. La suite compte désormais 90 tests autonomes réussis.

## Lot 97 — export CSV Événements

Le commit `42189995` est déployé depuis l'artefact SHA-256
`cf507c51df0c89b851826e094b8ea049ab4422d2e14149a236f34772a0777b4c`.
Les 90 tests autonomes passent, l'installation des dix plugins et des quatorze
tables est valide, et le serveur compile 221 squelettes privés, douze pages
publiques et 64 composants front.

Depuis la session Chrome authentifiée, l'URL publique
`inscriptions_evenement.csv` de l'événement 183 répond en HTTP 200 avec le type
`text/csv`, 2 948 octets et aucune erreur de squelette. Les avertissements PHP
8.4 imprimés en tête de réponse proviennent toujours d'Accès restreint 6.3.1,
hors du monorepo Association.
