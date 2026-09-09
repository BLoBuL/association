<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_communication_association_installation_inventaire($flux) {
	return association_installation_ajouter($flux, [
		'plugins' => ['association_communication'],
	]);
}
