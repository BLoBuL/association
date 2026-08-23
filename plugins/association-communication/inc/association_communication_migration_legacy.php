<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Adopte les anciennes colonnes de rattachement des envois Mailshot.
 */
function association_communication_migration_legacy($version) {
	if ((string) $version !== '1.2.9') {
		return;
	}
	sql_alter('TABLE spip_mailshots ADD COLUMN id_auteur BIGINT(21) AFTER id_mailshot');
	sql_alter('TABLE spip_mailshots ADD COLUMN id_evenement BIGINT(21) AFTER id');
}
