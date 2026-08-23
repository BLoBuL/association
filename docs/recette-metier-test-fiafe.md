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
