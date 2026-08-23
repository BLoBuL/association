<?php

/**
 * Registre des configurations transversales exposees a SPIP CLI.
 *
 * Les modules métier complètent ce registre par le pipeline
 * `association_config_cli_registre`.
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Ajoute des définitions sans autoriser un fournisseur à écraser une option.
 *
 * @param array<string, array<string, mixed>> $registre
 * @param array<string, array<string, mixed>> $definitions
 * @return array<string, array<string, mixed>>
 */
function association_config_cli_ajouter_definitions($registre, $definitions) {
	$registre = is_array($registre) ? $registre : array();
	foreach ((array) $definitions as $nom => $definition) {
		if (!is_string($nom) || $nom === '' || !is_array($definition)) {
			continue;
		}
		if (isset($registre[$nom])) {
			spip_log('Option CLI dupliquée ignorée : ' . $nom, 'association' . _LOG_ERREUR);
			continue;
		}
		$registre[$nom] = $definition;
	}
	return $registre;
}

/**
 * Définitions appartenant réellement au socle : identité et exécution globale.
 *
 * @return array<string, array<string, mixed>>
 */
function association_config_cli_definitions_socle() {
	$nom_site_defaut = function_exists('lire_config') ? lire_config('nom_site', '') : '';
	return array(
		'info.nom' => array('type' => 'string', 'writable' => true, 'path' => '/association_metas/nom', 'default' => $nom_site_defaut, 'description' => 'Configuration Association : info.nom.', 'max_length' => 255),
		'info.rue' => array('type' => 'string', 'writable' => true, 'path' => '/association_metas/rue', 'default' => '', 'description' => 'Configuration Association : info.rue.', 'max_length' => 255),
		'info.code_postal' => array('type' => 'string', 'writable' => true, 'path' => '/association_metas/cp', 'default' => '', 'description' => 'Configuration Association : info.code_postal.', 'max_length' => 32),
		'info.ville' => array('type' => 'string', 'writable' => true, 'path' => '/association_metas/ville', 'default' => '', 'description' => 'Configuration Association : info.ville.', 'max_length' => 128),
		'info.pays' => array('type' => 'string', 'writable' => true, 'path' => '/association_metas/pays', 'default' => '', 'description' => 'Configuration Association : info.pays.', 'max_length' => 128),
		'info.email' => array('type' => 'email_list', 'writable' => true, 'path' => '/association_metas/email', 'default' => '', 'description' => 'Configuration Association : info.email.', 'max_length' => 1000),
		'info.telephone' => array('type' => 'string', 'writable' => true, 'path' => '/association_metas/telephone', 'default' => '', 'description' => 'Configuration Association : info.telephone.', 'max_length' => 64),
		'info.numero_enregistrement' => array('type' => 'string', 'writable' => true, 'path' => '/association_metas/num_enregistrement', 'default' => '', 'description' => 'Configuration Association : info.numero_enregistrement.', 'max_length' => 128),
		'info.complement' => array('type' => 'string', 'writable' => true, 'path' => '/association_metas/info_complementaires', 'default' => '', 'description' => 'Configuration Association : info.complement.', 'max_length' => 10000),
		'maintenance.active' => association_config_cli_definition_enum('/association_metas/meta_cfg_maintenance_bdd_enable', array('oui', 'non'), 'oui', 'Configuration Association : maintenance.active.'),
		'maintenance.dry_run' => association_config_cli_definition_enum('/association_metas/meta_cfg_maintenance_dry_run', array('oui', 'non'), 'oui', 'Configuration Association : maintenance.dry_run.'),
		'maintenance.lot' => association_config_cli_definition_entier('/association_metas/meta_cfg_maintenance_lot', '1000', 1, 100000, 'Configuration Association : maintenance.lot.'),
	);
}
