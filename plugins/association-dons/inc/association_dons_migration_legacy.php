<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_dons_migration_legacy($version) {
	if ((string) $version === '1.1.0') {
		maj_tables(array('spip_asso_dons'));
	}
}
