<?php

/**
 * Action de suppression d'une catégorie de cotisation
 */
function action_supprimer_categorie_cotisation_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_categorie = intval($securiser_action());

	if ($id_categorie && autoriser('configurer', 'association')) {
		// Option 2 : Suppression logique (recommandée)
		sql_updateq('spip_asso_categories_adherents', ['statut' => 'supprime'], "id_categorie=$id_categorie");
	}
}
