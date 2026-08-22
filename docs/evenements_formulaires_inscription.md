# Evenements et formulaires d'inscription

## But

Documenter, de facon technique, tout ce qui pilote:

- la configuration des evenements dans le back-office;
- le formulaire d'edition d'un evenement;
- les formulaires d'inscription a un evenement;
- les regles de validation, de calcul de places et de tarifs;
- les modalites d'inscription, CGU et conditions a accepter.

Cette documentation sert de base technique pour:

- la maintenance du plugin;
- la redaction de la documentation utilisateur;
- les automatisations de mise a jour via Codex.

## Fichiers de reference

- [`association_fonctions.php`](../association_fonctions.php)
- [`formulaires/editer_evenement.php`](../formulaires/editer_evenement.php)
- [`formulaires/inscription_evenement.php`](../formulaires/inscription_evenement.php)
- [`formulaires/inscription_evenement_public.php`](../formulaires/inscription_evenement_public.php)
- [`formulaires/inscription_evenement_multi_public.php`](../formulaires/inscription_evenement_multi_public.php)
- [`formulaires/inc/inscription_evenement.php`](../formulaires/inc/inscription_evenement.php)
- [`formulaires/inc/inscription_evenement_saisies.php`](../formulaires/inc/inscription_evenement_saisies.php)
- [`docs/parametrage_evenements.md`](./parametrage_evenements.md)
- [`docs/categories_participation_financiere.md`](./categories_participation_financiere.md)
- [`docs/tarifs-logique.md`](./tarifs-logique.md)
- [`docs/comptabilite_evenements.md`](./comptabilite_evenements.md)

## Vue d'ensemble

Le plugin repose sur trois couches:

1. **Configuration globale**: valeurs par defaut definies dans `configurer_association`.
2. **Configuration evenement**: valeurs appliquees a un evenement precis au moment de sa creation ou de son edition.
3. **Formulaires d'inscription**: construction dynamique des champs selon le type d'evenement, le statut du visiteur, les accompagnants, la famille et les categories de participation.

Le code d'inscription s'appuie sur des helpers communs pour eviter de dupliquer la logique entre:

- le front-office public;
- le back-office prive;
- le mode simple;
- le mode multi-etapes.

## 1. Configuration globale des evenements

La page `configurer_association` fournit les valeurs de base recuperees ensuite par le formulaire d'edition d'evenement.

Les dependances les plus fortes se trouvent dans les onglets `evenement`, `evenement_defaut` et `affichage_public`.

### Metas principales

| Cle | Role |
|---|---|
| `meta_cfg_event_inscription` | active l'inscription par defaut |
| `meta_cfg_event_type_inscrits_evenement` | definit le type d'inscrits par defaut (`public`, `prive`, `strict`) |
| `meta_cfg_event_afficher_liste_inscrits` | controle l'affichage de la liste des inscrits |
| `meta_cfg_event_ouverture_differe` | differe l'ouverture des inscriptions |
| `meta_cfg_event_inscription_deadline` | fixe la regle de fermeture des inscriptions |
| `meta_cfg_event_validation` | impose ou non une validation des inscriptions |
| `meta_cfg_event_accompagnants` | active la gestion des accompagnants |
| `meta_cfg_event_limite_nb_accompagnants` | limite le nombre d'accompagnants |
| `meta_cfg_event_file_attente` | active la liste d'attente |
| `meta_cfg_event_limite_places_file_attente` | fixe la taille de la liste d'attente |
| `meta_cfg_event_message_responsable` | active le champ commentaire vers le responsable |
| `meta_cfg_event_condition_inscription` | active la case de validation d'une condition |
| `message_condition_inscription_defaut` | message de condition par defaut |
| `meta_cfg_event_config_accompagnants` | controle la logique famille / accompagnants |

### Point cle

Ces metas ne servent pas seulement d'affichage. Elles entrent directement dans:

- le chargement du formulaire d'edition d'evenement;
- la construction des champs de l'inscription;
- la validation du formulaire;
- les calculs de places disponibles;
- le comportement des cas familiaux et FIAFE.

## 2. Formulaire d'edition d'un evenement

Le formulaire `formulaires_editer_evenement_charger()` prepare les valeurs par defaut de l'evenement.

### Comportements importants

- le fuseau horaire est lu via `agenda/fuseaux_horaires`;
- `id_parent` est force a partir de l'article parent;
- `parents_id` est rempli pour le selecteur d'article;
- a la creation, la date de debut est initialisee a `now` et la fin a `now + 1h`;
- les dates sont normalisees selon le fuseau de l'evenement;
- les repetitions sont rechargees depuis `spip_evenements` via `id_evenement_source`.

### Valeurs prechargees depuis la configuration

Lors d'une creation, le formulaire recupere automatiquement:

- `inscription`;
- `type_inscrits_evenement`;
- `afficher_liste_inscrits`;
- `ouverture_differe`;
- `fermeture_inscription`;
- `validation`;
- `accompagnants`;
- `limite_places`;
- `file_attentes`;
- `attentes`.

Ces correspondances sont centralisees dans
`inc/evenement_defauts.php`. Le test CLI
`tests/test_evenement_defauts.php` relie la configuration globale au contexte
charge, puis verifie que `accompagnants = non` est conserve lors de la
persistance d'un evenement neuf sans intervention sur ce champ.

### Validation du formulaire d'edition

Le verifier controle notamment:

- la presence du titre;
- la coherence des dates debut/fin;
- l'existence de l'article parent;
- l'autorisation `creerevenementdans`;
- la validite des montants si l'evenement est payant.

### Regle Blobul specifique

Si l'evenement est payant, le verifier impose qu'au moins une categorie de prix ait:

- une valeur numerique;
- une valeur non vide.

Si une valeur est non numerique, une erreur est associee a la cle de la categorie.

## 3. Formulaire d'inscription public

Le formulaire public `formulaires_inscription_evenement_public.php` construit dynamiquement la saisie selon:

- l'ouverture des inscriptions;
- l'eligibilite de l'utilisateur;
- la nature de l'evenement;
- le mode payant ou gratuit;
- la presence d'accompagnants;
- la configuration famille;
- le contexte FIAFE.

### Etapes de decision

1. Charger les infos evenement via `affichage_dans_activites()` et `gestions_places()`.
2. Verifier si l'inscription est ouverte via `ouverture_inscription_evenement()`.
3. Verifier si l'utilisateur peut s'inscrire via `eligibilite_inscription_evenement()`.
4. Gérer la modification si `modif=oui` et si une activite existe deja.

### Cas utilisateur connecte

Si le visiteur est connecte:

- le statut auteur est exploite;
- le statut cotisation est calcule par rapport a la date de fermeture d'inscription;
- le type `individuel` interdit les accompagnants;
- si la configuration `membre_famille` est active, la structure familiale est proposee.

### Cas utilisateur non connecte

Si l'evenement est de type `public`, ou si un jeton / un contexte FIAFE le permet:

- les champs d'identite publique sont charges;
- l'utilisateur peut s'inscrire sans compte local;
- les saisies de modalites sont ajoutees si des pages sont configurees.

## 4. Formulaire d'inscription multi-etapes

Le formulaire `formulaires_inscription_evenement_multi_public.php` decoupe le parcours en etapes plus lisibles.

### Organisation

Selon le contexte, il affiche:

- une etape de selection des membres de la famille;
- une etape de saisie des participants ou du nombre d'inscrits;
- une etape de modalites et de validation finale.

Lorsque le nombre de participants et leurs identites sont repartis sur deux
etapes, la validation du nom et de la categorie commence seulement apres la
soumission de l'etape d'identite. Le choix valide du nombre de participants ne
doit donc jamais etre bloque par un champ encore masque.

### Logique famille

Si le compte a une structure familiale:

- le formulaire limite les choix a `adherent`, `conjoint`, `enfant`, `invite` selon le cas;
- le type `individuel` neutralise les accompagnants;
- le type `couple` limite les donnees familiales a deux profils.

### Etape finale

L'etape finale peut contenir:

- le commentaire vers le responsable;
- les pages de modalites / CGU;
- la case de condition d'inscription si elle est active;
- les champs caches `id_evenement` et `id_activite`.

## 5. Formulaire prive d'inscription

Le formulaire prive `formulaires/inscription_evenement.php` permet a un back-office d'enregistrer ou modifier une inscription.

### Particularites

- il verifie les droits sur l'evenement et sur l'auteur;
- il permet de choisir un membre, un non-membre ou un profil FIAFE/public;
- il propose les champs de notification;
- il ajoute l'annotation privee;
- il peut notifier l'adherent apres enregistrement.

### Gestion des listes

Le formulaire prive:

- retire des listes les personnes deja inscrites;
- retrouve l'evenement depuis l'activite lorsque l'URL d'edition ne transmet
  que `id_activite`;
- conserve les identifiants dans les champs caches a travers les etapes;
- rehydrate en modification les identites, informations supplementaires,
  commentaire et annotation deja enregistres sans ecraser une nouvelle saisie;
- s'adapte au statut du paiement deja existant;
- peut pre-remplir la structure familiale.

Pour une inscription BO multi sans compte, les participants `inscrit_N` restent
dans le parcours generique. Ils ne doivent pas etre convertis en membres de la
famille lors d'une modification.

## 6. Champs dynamiques et construction des saisies

Les helpers de `formulaires/inc/inscription_evenement.php` construisent le coeur du formulaire.

### Fonctions principales

| Fonction | Role |
|---|---|
| `champs_saisie_nb_inscrits()` | selection du nombre de participants |
| `champs_saisies_selection_membres_famille()` | choix des membres de la famille |
| `champs_saisies_inscrits()` | construction des champs participant par participant |
| `champs_saisies_famille()` | construction de la version "famille" |
| `champs_saisies_tarifs()` | ajout des tarifs / categories de participation |
| `generer_array_categories_participation()` | filtre les categories actives et compatibles |
| `calculer_montant_total()` | calcule le montant final a partir des categories |
| `formater_post_form()` et `formater_post_form_multi()` | reorganisent les donnees postees |
| `champs_saisies_modalites_evenement()` | ajoute les pages de modalites / CGU |

## 7. Gestion des evenements gratuits

Pour un evenement gratuit:

- le nombre d'inscrits est pilote par `nb_inscrits` si les accompagnants sont autorises;
- en mode famille, la famille remplace la numerotation des participants;
- sans accompagnants, le champ `nb_inscrits` est force a 1.

Si plusieurs participants sont possibles:

- le formulaire ajoute un champ de noms des accompagnants;
- la validation impose ce champ si le nombre d'inscrits est superieur a 1.

Dans le formulaire multi-etapes, la premiere identite `inscrit_1` est aussi
collectee lorsque l'etape courante ne poste pas encore `nb_inscrits`. Cette
collecte ne force pas le compteur : le nombre de participants et les categories
restent valides par les regles du parcours avant tout enregistrement.

## 8. Gestion des evenements payants

Pour un evenement payant, le formulaire utilise les categories de participation.

### Filtrage des categories

Les categories sont filtrees selon:

- le type d'inscrit;
- le statut de l'auteur connecte;
- la configuration famille ou accompagnants;
- le rang du participant;
- le caractere actif de la categorie.

### Types exploites

Le code distingue notamment:

- `adherent`;
- `non_adherent`;
- `indifferent`;
- `benevole`;
- `special`;
- `enfant`;
- `invite`;
- `couple`.

### Cas important

Si les accompagnants sont actives:

- chaque participant peut choisir une categorie distincte;
- le formulaire genere des champs `categorie[ID]`;
- la quantite autorisee depend du nombre de places.

Si les accompagnants sont desactives:

- un seul champ `categorie` est propose;
- la selection est restreinte aux categories compatibles.

## 9. Modalites, CGU et condition d'inscription

La fonction `champs_saisies_modalites_evenement()` lit la configuration `pages_modalite_evenement`.
Cette configuration est fournie par `configurer_association`, onglet `evenement`, via le bloc `config_modalites_evenement_fieldset`.

### Comportement

- si des pages sont configurees, elles sont cherchees dans `spip_articles`;
- chaque page publiee ajoute une case a cocher obligatoire;
- si rien n'est configure, la fonction retombe sur `page_cgu`;
- le texte du label contient un lien vers l'article concerne.

### Condition d'inscription

Si l'evenement active une condition specifique:

- une case obligatoire est ajoutee;
- le texte provient de `message_condition_inscription`;
- cette condition est distincte des CGU globales.

## 10. Validation des inscriptions

La validation controle:

- la presence des champs obligatoires;
- la coherence du nombre d'inscrits;
- les doublons;
- les limites de places;
- les listes d'attente;
- les contraintes FIAFE;
- la validite de l'email pour les inscriptions publiques;
- le fait de choisir une association quand c'est requis.

La commande `php tests/test_evenements_all.php` execute aussi la matrice des
configurations de chargement et les transitions intermediaires du formulaire
multi-etapes. Toute correction de l'inscription doit couvrir au minimum les
variantes gratuit/payant, avec/sans accompagnants, anonyme/connecte et famille,
ainsi que la restitution de `cvtm_prev_post` entre les etapes.

### Points sensibles

- un evenement payant sans categorie valide est refuse;
- la catégorie doit être active et liée à l'événement ; un identifiant absent,
  invalide ou appartenant à un autre événement est refusé en BO comme en FO ;
- le récapitulatif payant affiche la catégorie retenue et le montant recalculé
  côté serveur, y compris lorsqu'un tarif valide vaut 0 EUR ;
- le traitement final recalcule catégorie, quantité et montant, puis refuse une
  transaction absente, falsifiée ou incohérente avant toute activité ou notification ;
- un evenement sans accompagnants ne peut pas recevoir plus d'un inscrit;
- la capacite globale est controlee uniquement si la gestion des places est
  activee sur l'evenement; une disponibilite technique a zero ne bloque pas un
  evenement configure sans limitation;
- si les inscriptions hors quota sont illimitees, le formulaire reste valide
  et le calculateur attribue le statut de liste d'attente;
- les inscriptions publiques ne doivent pas dupliquer un email deja present sur l'evenement;
- les modifications revalident les places en tenant compte de l'inscription existante.

## 11. Enregistrement et post-traitement

Au traitement, le plugin:

- reformate les donnees du formulaire;
- calcule le statut d'inscription;
- cree ou met a jour la transaction si l'evenement est payant;
- enregistre le compte comptable si la comptabilite est active;
- envoie les notifications si la case de notification est active;
- ajoute l'inscrit aux listes de diffusion si besoin.

### Effet de la modification

En modification:

- la transaction est recrree ou mise a jour si le montant change;
- le compte comptable est synchronise;
- le journal est enrichi;
- le statut de l'inscription peut etre recalculé.

## 12. Regles FIAFE

Quand l'evenement est rattache au reseau FIAFE:

- un champ supplementaire de site / association peut etre ajoute;
- le formulaire peut charger des informations adherent depuis l'association et l'email;
- les types d'inscrits peuvent inclure un profil `membre_reseau_fiafe`.

## 13. Ce qu'il faut garder synchronise

- `association_fonctions.php`
- `formulaires/editer_evenement.php`
- `formulaires/inscription_evenement.php`
- `formulaires/inscription_evenement_public.php`
- `formulaires/inscription_evenement_multi_public.php`
- `formulaires/inc/inscription_evenement.php`
- `formulaires/inc/inscription_evenement_saisies.php`
- `docs/parametrage_evenements.md`
- `docs/categories_participation_financiere.md`
- `docs/tarifs-logique.md`
- `docs/comptabilite_evenements.md`

## 14. Resume operationnel

Si tu dois comprendre rapidement le systeme:

1. la configuration globale fournit les valeurs par defaut;
2. le formulaire d'edition d'evenement copie ces valeurs au moment de la creation;
3. les formulaires d'inscription adaptent leurs champs en fonction du type d'evenement;
4. les categories de participation pilotent le paiement;
5. les modalites / CGU et les conditions sont ajoutees dynamiquement;
6. les transactions et la comptabilite sont synchronisees apres enregistrement.

## A lire en plus

- [`configurer_association.md`](./configurer_association.md)
- [`configurer_association_inventaire.md`](./configurer_association_inventaire.md)
- [`parametrage_evenements.md`](./parametrage_evenements.md)
- [`categories_participation_financiere.md`](./categories_participation_financiere.md)
- [`comptabilite_evenements.md`](./comptabilite_evenements.md)
- [`plugins/README.md`](./plugins/README.md)
- [`plugins/matrix_dependances.md`](./plugins/matrix_dependances.md)
- [`plugins/agenda.md`](./plugins/agenda.md)
- [`plugins/saisies.md`](./plugins/saisies.md)
- [`plugins/verifier.md`](./plugins/verifier.md)
- [`plugins/notifications.md`](./plugins/notifications.md)
- [`plugins/facteur.md`](./plugins/facteur.md)
