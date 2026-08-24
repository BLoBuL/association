# Suivi — Compatibilité de la sauvegarde SPIP avec la colonne `transaction`

## Résolution dans la suite Association 4 autonome

Le contenu réel a été audité : la colonne contient la sélection tarifaire
sérialisée de l'inscription et non les données d'une transaction Bank. Le
plugin Événements 1.2.0 la renomme en `tarifs_selectionnes`. Les actions,
formulaires, exports CSV/XML et RGPD de ce monorepo sont migrés ensemble.

Le scénario a été validé sur une restauration isolée de la base DEV : les 41
activités et l'intégralité des valeurs sont conservées, la mise à jour est
idempotente et la sauvegarde SQLite SPIP inclut désormais la table complète.

Le présent document reste ouvert uniquement pour le parc historique hors de la
suite autonome : anciens plugins Blobul, thèmes ou surcharges de sites qui
liraient encore directement `#TRANSACTION`. Ils ne sont pas des dépendances de
la nouvelle suite et ne doivent pas bloquer son installation, mais doivent être
inventoriés avant toute propagation de cette évolution sur ces anciens sites.

## Contexte

La table historique `spip_asso_activites` contient une colonne `transaction`.
MySQL et MariaDB acceptent ce nom, mais `TRANSACTION` est un mot réservé de
SQLite. Lors d'une sauvegarde native SPIP, la table est recréée dans un fichier
SQLite sans protection suffisante du nom de colonne et sa création échoue.

Une première tentative de renommage en `details_transaction` a montré que cette
évolution ne peut pas être limitée à une migration SQL. La colonne est utilisée
par le plugin Association BO, les exports CSV/XML, le FO FIAFE et plusieurs
surcharges de thèmes. Cette première migration a été annulée par le schéma
`1.5.11` et la version `6.8.21.2`, afin de préparer une migration complète des
logiques métier connexes.

## Objectif cible

- Renommer définitivement la colonne historique `transaction` avec un nom non
  réservé et métier, à valider avant implémentation.
- Migrer en même temps toutes les lectures, écritures, balises, exports,
  intégrations et surcharges qui dépendent de cette colonne.
- Prévoir une transition compatible pour éviter qu'un décalage de version entre
  Association BO, le FO, Bank ou un thème ne casse un parcours métier.
- Déployer la migration progressivement, avec sauvegarde, contrôles de données
  et retour arrière propres à chaque site.

## Travail à réaliser

1. Choisir le nom cible selon le contenu réel de la colonne. Ne pas retenir
   automatiquement `details_transaction` sans vérifier sa sémantique métier.
2. Inventorier tous les usages directs et indirects dans :
   - Association BO, notamment les formulaires d'inscription et l'export RGPD ;
   - les exports CSV et XML utilisant `#TRANSACTION|unserialize` ;
   - Association FO/FIAFE et ses blocs d'information d'inscription ;
   - Bank et les prestataires qui lisent `spip_asso_activites` ;
   - les thèmes Association et FIAFE, notamment ACBL, AISG, Femmes d'Europe et
     Singapour ;
   - les scripts, API, modèles, tâches cron et surcharges propres aux sites.
3. Classer chaque usage par logique métier : saisie tarifaire, calcul du montant,
   modification d'inscription, paiement, affichage, export, notification, RGPD
   ou reprise comptable.
4. Définir une stratégie de compatibilité transitoire. Étudier notamment une
   phase où le code sait lire l'ancien et le nouveau champ, sans conserver deux
   sources de vérité divergentes.
5. Ajouter la migration SQL dans une nouvelle version de schéma, avec contrôle
   préalable de la colonne présente et journalisation explicite du résultat.
6. Adapter tous les producteurs et consommateurs dans une même livraison
   coordonnée, y compris les dépôts FO, Bank et thèmes concernés.
7. Ajouter des tests couvrant au minimum :
   - conservation d'une valeur sérialisée non vide pendant la migration ;
   - création et modification d'une inscription payante ;
   - calcul et rattachement de la transaction Bank ;
   - chargement d'une inscription existante ;
   - exports CSV, XML et RGPD ;
   - sauvegarde SPIP SQLite complète de `spip_asso_activites`.
8. Recetter d'abord sur un site synthétique contenant des inscriptions gratuites
   et payantes, puis sur les sites de test représentatifs des différentes
   surcharges.
9. Inventorier les versions et empreintes réellement actives sur le parc avant
   propagation. Exclure tout site dont une surcharge n'est pas migrée.
10. Pour chaque site : sauvegarder, déployer l'ensemble coordonné, exécuter la
    migration, purger les caches, contrôler les compteurs et valeurs, tester les
    parcours concernés et conserver un retour arrière vers l'ancien schéma.

## Solution provisoire d'exploitation du parc historique

Tant que la migration coordonnée n'est pas validée et déployée, conserver la
colonne `transaction` et utiliser une sauvegarde MySQL/MariaDB native pour les
opérations nécessitant une garantie de restauration complète. Ne pas considérer
comme complète une sauvegarde SPIP qui signale l'échec de
`spip_asso_activites`.

## Critères de clôture

- Nouveau nom de colonne validé et documenté.
- Inventaire exhaustif des logiques métier et surcharges terminé.
- Tous les dépôts et thèmes consommateurs migrés et versionnés.
- Tests automatisés de migration, inscription, paiement et exports disponibles.
- Valeurs historiques conservées à l'identique après migration.
- Sauvegarde SPIP complète réussie et fichier SQLite contrôlé directement.
- Procédure de retour arrière SQL et code testée.
- Validation multi-sites réalisée avant déploiement général.
