<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_communication_association_log_categories($flux) {
	return association_log_categories_ajouter($flux, [
		'notifications' => [
			'ordre' => 30,
			'label' => _T('association_communication:log_cat_notifications'),
		],
		'spam' => [
			'ordre' => 80,
			'label' => _T('association_communication:log_cat_spam'),
		],
		'email' => [
			'ordre' => 90,
			'label' => _T('association_communication:log_cat_email'),
		],
	]);
}
