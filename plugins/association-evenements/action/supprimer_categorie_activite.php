<?php

/**
 * Action de suppression d'une catégorie d'activite
 */
function action_supprimer_categorie_activite_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_categorie = intval($securiser_action());

	if ($id_categorie && autoriser('configurer', 'association')) {
		// Option 2 : Suppression logique (recommandée)
		sql_updateq('spip_asso_categories_activites', ['statut' => 'supprime'], "id_categorie=$id_categorie");
	}
}
