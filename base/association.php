<?php
/**
 * Plugin Association
 * (c) 2004-2023 SPIP
 * Distribue sous licence GNU/GPL
 *
 * @package SPIP\association\base
 */
if (!defined("_ECRIRE_INC_VERSION")) return;

## TABLES PRINCIPALES
function association_declarer_tables_principales($tables_principales) {
	return $tables_principales;
};
## TABLES AUXILIAIRES
function association_declarer_tables_auxiliaires($tables_auxiliaires){
	//-- Table METAS ------------------------------------------
	$spip_asso_metas = array(
			"nom"		=> "VARCHAR(255) NOT NULL",
			"valeur"	=> "TEXT DEFAULT ''",
			"impt"		=> "ENUM('non', 'oui') DEFAULT 'oui' NOT NULL",
			"maj"		=> "TIMESTAMP"
	);
	$spip_asso_metas_key = array(
			"PRIMARY KEY"	=> "nom"
	);
	$tables_auxiliaires['spip_association_metas'] = array(
			'field' => &$spip_asso_metas, 'key' => &$spip_asso_metas_key);
	return $tables_auxiliaires;
}
/**
 * Déclare des champs extras pour l'association.
 *
 * Cette fonction inclut le fichier contenant la déclaration des champs extras
 * spécifiques à l'association et appelle une implémentation pour les ajouter
 * au tableau des champs extras existants.
 *
 * @param array $champs Tableau des champs extras existants.
 * @return array Tableau des champs extras mis à jour avec ceux de l'association.
 */
