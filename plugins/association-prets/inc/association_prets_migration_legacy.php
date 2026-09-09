<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_prets_migration_legacy($version) {
	if ((string) $version === '1.1.0') {
		maj_tables(['spip_asso_ressources', 'spip_asso_prets']);
	}
}
