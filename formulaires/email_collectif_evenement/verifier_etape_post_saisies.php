<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function formulaires_email_collectif_evenement_verifier_etape_post_saisies_dist($etape, $id_evenement = 0) {
	include_spip('inc/email_collectif');
	return association_email_collectif_verifier_selection('evenement', $etape, intval($id_evenement ?: _request('id_evenement')));
}
