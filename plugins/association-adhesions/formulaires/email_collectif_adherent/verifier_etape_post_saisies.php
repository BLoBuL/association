<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function formulaires_email_collectif_adherent_verifier_etape_post_saisies_dist($etape) {
	include_spip('inc/email_collectif');
	return association_email_collectif_verifier_selection('adherent', $etape);
}
