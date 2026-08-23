<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function association_communication_association_migrations_historiques($flux) {
	include_spip('inc/association_communication_migration_legacy');
	return association_migrations_ajouter($flux, 'association_communication', array(
		array('1.2.9', array('association_communication_migration_legacy', '1.2.9'), 10),
	));
}
