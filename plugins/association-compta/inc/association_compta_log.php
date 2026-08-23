<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_compta_association_log_categories($flux) {
	return association_log_categories_ajouter($flux, array(
		'comptabilite' => array('ordre' => 50, 'label' => _T('association_compta:log_cat_comptabilite')),
	));
}
