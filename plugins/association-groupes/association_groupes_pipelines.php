<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_groupes_association_capacites($capacites) {
	$capacites['groupes'] = ['plugin' => 'association_groupes'];

	return $capacites;
}
