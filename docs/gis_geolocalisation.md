# GIS et geolocalisation

## But

Documenter le volet GIS du plugin:

- geocodage;
- mise a jour d'adresse;
- notifications GIS;
- configuration des destinataires et des actions surveillees.

## Fichiers sources

- [`inc/fonctions/gis_auteur.php`](../inc/fonctions/gis_auteur.php)
- [`inc/fonctions/facteur_envoyer_notification_gis.php`](../inc/fonctions/facteur_envoyer_notification_gis.php)
- [`action/gis_geocoder_rechercher.php`](../action/gis_geocoder_rechercher.php)
- [`formulaires/configurer_association.php`](../formulaires/configurer_association.php)

## Configuration

Le bloc GIS de la configuration expose:

- `notification_gis_config_email`;
- `notification_gis_config_action`.

Ces valeurs pilotent:

- les destinataires des alertes GIS;
- les actions concernées par les notifications.

### Onglet relie

Ce bloc est configure dans l'onglet `modules` de `configurer_association`, uniquement si le plugin GIS est actif.

## Notification GIS

La fonction `facteur_envoyer_notification_gis()`:

- choisit un modele selon l'action (`echec` ou `modification`);
- prepare le contexte du message;
- convertit une nouvelle adresse geocodee si elle est retournee comme tableau;
- lit les destinataires dans la configuration;
- envoie via `facteur_envoyer_app()`.

## Geocodage

L'action de geocodage:

- utilise le helpers GIS;
- appelle un service distant;
- permet de retrouver ou corriger les donnees de localisation;
- peut produire une notification de modification ou d'echec.

## Utilisation metier

Le GIS sert principalement a:

- suivre les adresses des adherents;
- reactiver ou corriger les donnees geographiques;
- declencher des alertes si la geolocalisation echoue.

## Points de vigilance

- Une adresse peut etre fournie sous forme structuree ou sous forme texte.
- Les destinataires doivent etre valides sinon la notification est stoppee.
- Le bloc GIS est optionnel mais impacte la communication metier si activé.
