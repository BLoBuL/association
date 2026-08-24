# Dépendances et capacités de la suite Association 4

## Règle commune

Le socle `association` est activable seul. Chaque plugin métier nécessite le
socle et uniquement ses dépendances SPIP externes. Aucun plugin métier ne
nécessite un autre plugin métier : une combinaison absente produit un résultat
neutre et ne remet jamais en cause l'existence de l'objet métier.

Les capacités communes sont publiées par les pipelines
`association_capacites`, `association_profil_participant`,
`association_comptabiliser_operation` et `association_notifier_metier`.
Les identifiants `id_compte` et `id_transaction` sont facultatifs.
Comptabilité et Paiements ne se déclarent pas mutuellement avec `utilise` :
SPIP ordonne aussi ces relations facultatives et recréerait un cycle. Leurs
échanges bidirectionnels passent exclusivement par les pipelines du socle.

## Matrice des modules

| Plugin | Dépendances externes obligatoires | Capacités fournies | Compléments facultatifs et repli |
|---|---|---|---|
| Association | Saisies | configuration, journal, autorisations communes, contrats | masque les sections métier sans contributeur |
| Adhésions/Cotisations | Inscription4, Intl, Médias | cotisations, profil membre, familles | sans Compta conserve `id_compte=NULL`; sans Paiements accepte gratuit ou règlement manuel; sans Communication ignore l'envoi facultatif |
| Événements | Agenda, Saisies, Vérifier, Inscription4, Champs Extras | catalogue, inscriptions, participants, quotas, tarifs génériques | sans Adhésions mode public universel; sans Paiements inscription sans transaction; sans Compta aucune écriture; sans Communication aucune notification facultative |
| Comptabilité | Intl | journal et comptabilisation d'opérations | consomme les règlements seulement si Paiements est actif |
| Paiements | Bank | règlements et informations de transaction | publie les règlements; Compta et producteurs métier restent facultatifs |
| Communication | Notifications, Mailsubscribers, Mailshot | campagnes, abonnements, notifications métier | Adhésions et Événements contribuent seulement leurs destinataires et modèles |
| Groupes | aucune | groupes, rôles et liens auteurs | sans Adhésions utilise les auteurs SPIP et leurs URLs privées natives |
| Prêts | Intl | ressources et prêts | persiste sans Compta/Paiements; comptabilisation facultative |
| Dons | aucune | dons et reçus | persiste sans Compta/Paiements/Communication |
| Ventes | aucune | ventes et expéditions | persiste sans Compta/Paiements/Adhésions |
| Commerce | Paniers, Commandes, Prix | catalogue, panier et passage en commande | sans Paiements, conserve le panier et la commande avec les moyens externes disponibles |
| Partenaires | Contacts & Organisations | qualification, périodes et présentation publique des partenaires | autonome vis-à-vis des autres métiers de la suite |
| Bannières | aucune | campagnes, emplacements et modèles publicitaires | autonome ; le logo SPIP porte le visuel de la bannière |

## Combinaisons supportées

| Combinaison | Comportement attendu |
|---|---|
| Association seule | configuration commune utilisable, aucune table ni entrée métier |
| Événements seul | auteurs et visiteurs, tarifs génériques, quotas et inscriptions sans transaction |
| Événements + Adhésions | profils membres, familles, restrictions et tarifs membre/non-membre |
| Événements + Compta | inscriptions autonomes et écritures synchronisées lorsque demandées |
| Événements + Paiements | transactions et redirection Bank, sans obligation de journal comptable |
| Événements + Communication | notifications et modèles événementiels |
| Adhésions seule | cotisations gratuites ou manuelles, sans écriture ni transaction obligatoire |
| Adhésions + Compta/Paiements | écriture facultative et encaissement Bank rattachés à la cotisation |
| Dons, Ventes ou Prêts sans Compta | objet métier complet avec lien comptable nul |
| Dons, Ventes ou Prêts + Compta | création ou synchronisation facultative d'une écriture |
| Suite complète | enrichissements cumulés, sans changement de propriétaire des données |
| Commerce seul | catalogue, panier et commandes ; le règlement Association reste facultatif |
| Partenaires seul | organisations Contacts qualifiées et publiées comme partenaires |
| Bannières seul | diffusion des visuels publiés par emplacement |

La désactivation puis la réactivation d'un complément ne supprime aucune donnée.
Les wrappers historiques restent des adaptateurs dépréciés; tout nouveau code
doit utiliser les capacités ou la façade locale du plugin consommateur.
