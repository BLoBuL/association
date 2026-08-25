# Interface privée de la suite Association 4

## Ligne ergonomique

Le back-office reprend les conventions du privé SPIP 4 : tableaux compacts et lisibles, formulaires CVT structurés en groupes de champs, boutons d’action sobres, focus clavier visible et comportement responsive sans débordement de page.

Les acquis métier historiques sont conservés lorsqu’ils accélèrent réellement le travail :

- le bandeau transversal reste disponible pour passer rapidement d’un domaine à l’autre ;
- Font Awesome reste le langage visuel des actions fréquentes ;
- les états métier restent distinguables par une couleur, limitée à un liseré et à des badges légers ;
- les filtres, exports et raccourcis métier restent placés à proximité du tableau concerné.

## Arbitrages

| Élément historique | Décision SPIP 4 |
|---|---|
| Lignes entièrement colorées | Remplacées par un fond neutre et un liseré d’état afin de préserver la lisibilité. |
| Entêtes sombres spécifiques | Alignées sur les entêtes des listes natives SPIP. |
| Icônes Font Awesome seules | Conservées dans un bouton neutre, avec intitulé accessible, infobulle et focus visible. |
| Formulaires construits avec des listes HTML | Remplacés par la structure `formulaire_spip`, `fieldset`, `legend` et champs `editer`. |
| Cases à cocher simulées en CSS | Retour au contrôle natif du navigateur, compatible clavier et technologies d’assistance. |
| Grands tableaux | Conservés avec défilement horizontal local ; la page privée ne doit pas déborder. |

## Matrice de recette privée

La recette couvre les listes, fiches, formulaires, filtres et actions disponibles dans chaque module : Adhésions, Événements, Groupes, Commerce, Ventes, Dons, Comptabilité, Paiements, Prêts, Communication et configuration du socle.

Chaque page est contrôlée dans Chrome en vue bureau et mobile. Une page est validée seulement si elle est servie sans erreur PHP ou de squelette, si les actions sont compréhensibles au clavier et à la souris, et si tableaux et formulaires restent contenus dans la zone centrale du privé SPIP.
