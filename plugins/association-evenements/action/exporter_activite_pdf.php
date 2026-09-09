<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function action_exporter_activite_pdf_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_evenement = (int) $securiser_action();
	include_spip('inc/autoriser');
	if ($id_evenement <= 0 || !autoriser('gererinscriptions', 'evenement', $id_evenement)) {
		return false;
	}
	$evenement = sql_fetsel('titre', 'spip_evenements', 'id_evenement=' . $id_evenement);
	if (!$evenement) {
		return false;
	}
	$lignes = sql_allfetsel('id_activite,prenom_inscrit,nom_inscrit,email_inscrit,nombre_inscrits,statut', 'spip_asso_activites', 'id_evenement=' . $id_evenement, '', 'nom_inscrit,prenom_inscrit,id_activite');
	include_spip('inc/association_pdf');
	association_pdf_envoyer('prive/pdf/association_evenements', [
		'titre' => $evenement['titre'],
		'lignes' => $lignes ?: [],
	], 'inscriptions-' . $id_evenement);
	return true;
}
