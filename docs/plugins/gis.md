# Dépendance - `gis`

## Rôle dans Blobul

Le plugin `gis` est utilisé pour les fonctions de géolocalisation et certaines notifications liées aux auteurs.

Blobul s'en sert pour:

- géocoder une adresse;
- créer ou mettre à jour un point géographique;
- signaler les succès ou échecs de géolocalisation;
- enrichir les données de contact.

## Déclaration

- Plugin utilisé dans [`paquet.xml`](../../paquet.xml)
- Compatibilité déclarée: `>= v4.54.0`

## Utilisations principales

- `inc/gis_geocode`;
- `inc/fonctions/gis_auteur.php`;
- notifications GIS;
- mise à jour des données auteur.

## Points d'intégration

- création et mise à jour d'auteurs;
- notifications techniques;
- configuration des emails liés aux GIS.

## Risques d'intégration

- un changement d'API GIS peut couper le géocodage;
- les notifications GIS ne doivent pas perturber les autres notifications;
- une adresse invalide doit rester gérée proprement sans bloquer le parcours principal.

## A lire en plus

- [`../gis_geolocalisation.md`](../gis_geolocalisation.md)
- [`../notifications.md`](../notifications.md)
