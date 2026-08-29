# Intégrations facultatives Familles et Contrats

Les plugins maison `familles` et `contrats` complètent la suite Association 4 sans devenir des dépendances du socle. Association seul, ainsi que chacun de ses modules autonomes, continuent donc de fonctionner lorsqu’ils sont absents.

## Familles

Adhésions porte l’adaptateur vers `familles`, car la famille administrative prolonge le profil d’adhésion et la cotisation. Le socle expose `association_contexte_familial()` ; les autres modules utilisent ce contrat plutôt que les tables ou fonctions de Familles.

Le contexte normalisé fournit la disponibilité, la famille principale, les autres familles, les auteurs membres et le rôle du sujet. Il permet notamment :

- à Événements d’appliquer ultérieurement des quotas ou tarifs familiaux ;
- à Communication de dédupliquer ou regrouper des destinataires ;
- à Prêts d’identifier un foyer emprunteur ;
- à Adhésions de remplacer progressivement les anciennes notions de compte principal et secondaire.

Sans Familles, le contexte contient des tableaux vides et `disponible=false`. L’auteur, son adhésion, ses inscriptions et ses cotisations restent utilisables.

## Contrats

Commerce porte l’adaptateur vers `contrats`, car la commande constitue la source transactionnelle naturelle d’un contrat. Le socle expose `association_demander_contrat()` ; Ventes, Adhésions, Partenaires ou un futur module peuvent émettre une demande sans appeler directement l’API maison.

La demande accepte notamment l’objet producteur, son identifiant, le type de contrat, la commande, l’organisation, le contact, les produits, le montant, les dates et les variables métier. L’adaptateur Commerce appelle l’API idempotente `contrats_creer_ou_mettre_a_jour_depuis_flux()`.

Sans Contrats, l’objet métier et la commande sont conservés normalement avec `contrat_cree=false` et `id_contrat=0`. La signature ou le PDF ne sont proposés que lorsqu’un contrat a réellement été créé.

## Règles de dépendances

| Consommateur | Déclaration | Fournisseur direct |
|---|---|---|
| Adhésions | `utilise familles` | Familles |
| Commerce | `utilise contrats` | Contrats |
| Autres modules | API du socle | Aucun appel direct |

Un nouveau besoin métier doit enrichir ces contrats ou ajouter un pipeline documenté. Il ne doit pas introduire de `necessite` vers Familles ou Contrats dans le socle ou dans un module qui peut fonctionner sans eux.
