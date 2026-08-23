<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Recopie l'ancien identifiant de journal dans l'auteur du compte (1.2.4).
 */
function association_maj_124() {
	sql_update('spip_asso_comptes', array('id_auteur' => 'id_journal'));
}
