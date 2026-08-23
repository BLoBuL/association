<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_evenements_association_log_categories($flux) {
	return association_log_categories_ajouter($flux, array(
		'inscriptions' => array('ordre' => 40, 'label' => _T('association_evenements:log_cat_inscriptions')),
	));
}
