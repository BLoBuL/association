# Emails collectifs

## But

Documenter les envois d'emails collectifs aux adherents ou aux participants.

## Fichiers sources

- [`formulaires/email_collectif_adherent.php`](../formulaires/email_collectif_adherent.php)
- [`formulaires/email_collectif_evenement.php`](../formulaires/email_collectif_evenement.php)
- [`formulaires/inc/email_collectif.php`](../formulaires/inc/email_collectif.php)
- [`action/envoyer_email_collectif_adherent.php`](../action/envoyer_email_collectif_adherent.php)
- [`formulaires/choisir_gabarit_envoi_collectif.php`](../formulaires/choisir_gabarit_envoi_collectif.php)
- [`formulaires/inc/adherents_recherche_avancee.php`](../formulaires/inc/adherents_recherche_avancee.php)
- [`notifications/email_collectif_adherent.html`](../notifications/email_collectif_adherent.html)

## Flux global

1. selection du gabarit;
2. redaction du message;
3. ajout des documents joints;
4. recherche des destinataires;
5. visualisation;
6. envoi;
7. mise en queue du traitement.

Les parcours sont distincts :

- `email_collectif_adherent` traite uniquement une sélection d'adhérents ;
- `email_collectif_evenement` traite les inscriptions d'un événement et les adhérents ajoutés explicitement ;
- `formulaires/inc/email_collectif.php` mutualise le moteur CVT, les documents, la recherche et la création du mailshot ;
- la page privée `edit_email_collectif_activite` est un squelette SPIP 4.4, sans contrôleur PHP dans `exec/`.

Le parcours événement :

- propose d'abord un gabarit libre, puis rappel, annulation, report et modification ;
- pré-remplit le sujet, le titre, la date, le lieu et le lien de l'événement ;
- permet d'inclure ou non le contenu complet de l'événement, option activée par défaut ;
- les inscriptions valides sont preselectionnees par `id_activite` ;
- une recherche explicite peut ajouter des adherents ;
- les emails des inscrits sont resolus cote serveur au traitement et bornes a l'evenement ;
- la previsualisation et les documents joints restent geres par le flux commun.

Sans critere de recherche, le parcours evenement ne charge pas tous les adherents
actifs. Cela evite d'elargir implicitement un rappel a toute l'association.

## Formulaire collectif adherent

Le formulaire multi-etapes comporte:

- edition du sujet, titre, chapeau et texte;
- televersement d'un visuel principal;
- ajout de documents joints;
- recherche avancee des destinataires;
- parametres d'envoi;
- date d'envoi programmee.

L'option d'ajout des tarifs de cotisation et des modes de paiement appartient
uniquement au parcours adhérent. Elle n'est jamais affichée dans le parcours
événement.

## Destinataires

Le formulaire s'appuie sur la recherche avancée des adherents:

- les criteres sont construits depuis les champs extras;
- les destinataires peuvent etre filtrés avant l'envoi;
- l'interface conserve l'etape de recherche et le resultat.

Les listes automatiques `statut_interne_*` sont pilotees par le statut metier
de l'adherent et par son statut SPIP actif. Le droit technique `webmestre` ne
retire pas un adherent de ces listes.

La duplication d'une liste est fournie par le plugin `mailsubscribers`. Elle
cree une liste fermee avec un nouvel identifiant et copie les abonnements
existants. Cette copie constitue un snapshot uniquement tant que son identifiant
reste distinct d'une liste automatique et que ses abonnements ne sont pas
modifies. Pour un electorat d'AG, conserver la copie fermee et utiliser cette
meme liste pour la convocation, les documents et le vote. Le plugin Association
ne peut pas expliquer une perte ulterieure d'abonnements sans les traces du
plugin `mailsubscribers` ou l'historique de la liste concernee.

## Action d'envoi

L'action:

- securise la requete;
- recupere les IDs selectionnes;
- cree un mailshot;
- deduplique les adresses;
- actualise le nombre total de destinataires;
- relance le cron de traitement.

## Gabarits

Le gabarit pilote:

- le sujet;
- le titre;
- le chapeau;
- le texte;
- les pieces jointes visuelles.

## Configuration et dependances

- recherche adherent;
- `mailshots`;
- `mailsubscribinglists`;
- `notifications/email_collectif_adherent.html`;
- configuration d'envoi de piece jointe et expéditeur.

## Points de vigilance

- Les emails vides sont filtres.
- La liste de destinataires doit rester dedoublonnee.
- L'envoi collectif doit rester compatible avec la segmentation adherent.
