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
