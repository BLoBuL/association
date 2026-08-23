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
| Adhésions | listes adhérents et cotisations | pages privées servies et protections contrôlées | partiel : création/renouvellement réel à rejouer |
| Événements | événement public 230 | page canonique et URL propre, formulaire `inscription_evenement_public`, affichage bureau et mobile | partiel : soumission volontairement arrêtée avant paiement |
| Comptabilité | liste des opérations | page et opération historique servies sans erreur | partiel : écriture synthétique complète à rejouer |
| Dons | liste et formulaire de création | pages servies sans erreur | partiel : cycle création/suppression à rejouer |
| Ventes | liste et formulaire de création | pages servies sans erreur | partiel : cycle création/suppression à rejouer |
| Prêts | liste globale | toutes les ressources sont listées sans faux message « ressource introuvable » | validé |
| Prêts | ressource puis réservation | création BO d'une ressource à 0 EUR, affichage public, réservation de 7 jours et affichage public de l'état | validé |
| Prêts | état sans restitution | « Non restituée » remplace la date SQL sentinelle `0000-00-00` | validé |
| Prêts | nettoyage | suppression transactionnelle ciblée, puis relecture : 0 prêt et 0 ressource synthétiques | validé |
| Communication | abonnement newsletter | squelette public et formulaire natif Newsletter servis, sans envoi | partiel : validation négative à rejouer |
| Front office | accueil, profil, inscription, événement, ressources et newsletter | H1, absence d'erreur fatale et absence de débordement horizontal sur les pages contrôlées | validé pour le rendu ; scénarios métier encore détaillés ci-dessus |

## Défauts trouvés et corrigés pendant la recette

1. La devise du prix de location dépendait de l'ancienne méta
   `/association/symbole` et produisait « Prix de la location (en ) ». Le
   formulaire utilise désormais la devise configurée par Intl ; preuve servie :
   « Prix de la location (en EUR) ».
2. La page d'une ressource précise affichait aussi le bloc alternatif global
   « Aucune ressource ». Ce bloc est désormais limité au mode liste globale.
3. Un prêt en cours exposait `0000` comme date de retour. Le catalogue affiche
   désormais l'état traduit « Non restituée ».

## Non-régression

- 52 tests PHP autonomes réussis ;
- 269 fichiers PHP contrôlés sans erreur de syntaxe ;
- compilation réelle des squelettes vérifiée par les pages privées et publiques
  après purge du cache ;
- aucune donnée synthétique du parcours prêts/ressources ne subsiste.

## Journaux

L'erreur SQL `Unknown column 'date_acquisitionDESC'` du 23 août à 03:21 est
antérieure au correctif du modèle et ne se reproduit plus. Les parcours suivants
n'ont produit aucune nouvelle erreur SQL Association.

Deux messages restent observés immédiatement après certaines purges globales de
cache : pipeline `taches_generales_cron` momentanément indisponible et connexion
SQL nommée vide. Ils ne sont pas attribués à Association sans reproduction hors
reconstruction de cache ; les pages normales et le vérificateur d'installation
restent fonctionnels.

## Reste à clôturer

- adhésion et cotisation avec données synthétiques, sans paiement réel ;
- création et suppression réversibles d'une écriture comptable, d'un don et
  d'une vente ;
- validation négative de la newsletter sans destinataire réel ;
- nouvelle passe multi-profils et responsive après ces scénarios ;
- scan final des journaux à partir de l'heure de cette dernière passe.
