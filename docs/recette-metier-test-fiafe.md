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

## Non-régression

- 69 tests PHP autonomes réussis, y compris les tests propres aux neuf modules ;
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

La recette de la suite Association est clôturée. Le débordement propre à
l'accueil du thème et le code de sortie non nul de la commande externe
`spip test:spip` malgré tous ses contrôles affichés en vert sont consignés hors
périmètre Association.
