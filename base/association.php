<?php

/**
 * Plugin Association
 * (c) 2004-2023 SPIP
 * Distribue sous licence GNU/GPL
 *
 * @package SPIP\association\base
 */
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

# # TABLES AUXILIAIRES
function association_declarer_tables_auxiliaires($tables_auxiliaires) {
	// -- Table METAS ------------------------------------------
	$spip_asso_metas = [
		'nom' => 'VARCHAR(255) NOT NULL',
		'valeur' => "TEXT DEFAULT ''",
		'impt' => "ENUM('non', 'oui') DEFAULT 'oui' NOT NULL",
		'maj' => 'TIMESTAMP',
	];
	$spip_asso_metas_key = [
		'PRIMARY KEY' => 'nom',
	];
	$tables_auxiliaires['spip_association_metas'] = [
		'field' => &$spip_asso_metas, 'key' => &$spip_asso_metas_key];
	return $tables_auxiliaires;
}
