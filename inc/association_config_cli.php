<?php

/**
 * API de configuration exposee aux commandes SPIP CLI du plugin Association.
 *
 * Le registre constitue une liste blanche explicite. Une option absente du
 * registre ne peut etre ni lue ni ecrite, ce qui exclut notamment les secrets.
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Retourne les options de configuration autorisees en ligne de commande.
 *
 * @return array<string, array<string, mixed>>
 */
function association_config_cli_registre() {
	include_spip('inc/association_log');
	include_spip('inc/association_config_cli_registre');

	$registre = [
		'debug' => [
			'type' => 'aggregate_boolean',
			'writable' => true,
			'description' => 'Activation de toutes les categories de debug du plugin Association.',
		],
	];

	foreach (array_keys(association_log_categories_defaut()) as $categorie) {
		$registre['debug.' . $categorie] = [
			'type' => 'boolean',
			'writable' => true,
			'path' => 'association/debug/categories/' . $categorie,
			'default' => 'off',
			'description' => 'Activation du debug pour la categorie ' . $categorie . '.',
		];
	}

	$registre = association_config_cli_ajouter_definitions($registre, association_config_cli_definitions_socle());
	$registre = pipeline('association_config_cli_registre', [
		'args' => [],
		'data' => $registre,
	]);

	return is_array($registre) ? $registre : [];
}

function association_config_cli_definition_enum($path, $allowed, $default, $description) {
	return [
		'type' => 'enum',
		'writable' => true,
		'path' => $path,
		'allowed' => $allowed,
		'default' => $default,
		'description' => $description,
	];
}

function association_config_cli_definition_entier($path, $default, $min, $max, $description) {
	return [
		'type' => 'integer',
		'writable' => true,
		'path' => $path,
		'default' => $default,
		'min' => $min,
		'max' => $max,
		'description' => $description,
	];
}

/**
 * Normalise et valide une valeur selon sa definition.
 *
 * @return array<string, mixed>
 */
function association_config_cli_normaliser($valeur, $definition) {
	$type = $definition['type'] ?? '';
	if ($type === 'list') {
		return association_config_cli_normaliser_liste($valeur, $definition);
	}
	if (is_array($valeur) || is_object($valeur)) {
		return ['ok' => false];
	}
	$valeur = trim((string) $valeur);
	if ($definition['type'] === 'boolean' || $definition['type'] === 'aggregate_boolean') {
		$normalisee = association_config_cli_normaliser_booleen($valeur);
		return $normalisee === null
			? ['ok' => false]
			: ['ok' => true, 'value' => $normalisee];
	}
	if ($definition['type'] === 'enum') {
		return in_array($valeur, $definition['allowed'], true) ? ['ok' => true, 'value' => $valeur] : ['ok' => false];
	}
	if ($definition['type'] === 'integer') {
		if ($valeur === '' && (!empty($definition['optional']) || !empty($definition['allow_empty']))) {
			return ['ok' => true, 'value' => ''];
		}
		if (!preg_match('/^\d+$/', $valeur)) {
			return ['ok' => false];
		}
		$entier = (int) $valeur;
		return ($entier >= $definition['min'] && $entier <= $definition['max'])
			? ['ok' => true, 'value' => (string) $entier]
			: ['ok' => false];
	}
	if ($type === 'decimal') {
		if ($valeur === '' && (!empty($definition['optional']) || !empty($definition['allow_empty']))) {
			return ['ok' => true, 'value' => ''];
		}
		$valeur = str_replace(',', '.', $valeur);
		if (!preg_match('/^\d+(?:\.\d+)?$/', $valeur)) {
			return ['ok' => false];
		}
		$nombre = (float) $valeur;
		return ($nombre >= $definition['min'] && $nombre <= $definition['max'])
			? ['ok' => true, 'value' => rtrim(rtrim(sprintf('%.8F', $nombre), '0'), '.')]
			: ['ok' => false];
	}
	if ($type === 'string') {
		$longueur = function_exists('mb_strlen') ? mb_strlen($valeur) : strlen($valeur);
		return strpos($valeur, "\0") === false && $longueur <= $definition['max_length']
			? ['ok' => true, 'value' => $valeur]
			: ['ok' => false];
	}
	if ($type === 'identifier') {
		$longueur = function_exists('mb_strlen') ? mb_strlen($valeur) : strlen($valeur);
		return $longueur <= $definition['max_length'] && preg_match('/^[A-Za-z0-9_.:-]*$/', $valeur)
			? ['ok' => true, 'value' => $valeur]
			: ['ok' => false];
	}
	if ($type === 'email_list') {
		$longueur = function_exists('mb_strlen') ? mb_strlen($valeur) : strlen($valeur);
		if (isset($definition['max_length']) && $longueur > $definition['max_length']) {
			return ['ok' => false];
		}
		if ($valeur === '') {
			return ['ok' => true, 'value' => ''];
		}
		$emails = preg_split('/[;,\s]+/', $valeur, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($emails as $email) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				return ['ok' => false];
			}
		}
		return ['ok' => true, 'value' => implode(', ', $emails)];
	}
	if ($type === 'day_month') {
		if (!preg_match('/^(\d{2})\/(\d{2})$/', $valeur, $match)) {
			return ['ok' => false];
		}
		$jour = (int) $match[1];
		$mois = (int) $match[2];
		return checkdate($mois, $jour, 2000)
			? ['ok' => true, 'value' => $valeur]
			: ['ok' => false];
	}
	return ['ok' => false];
}

function association_config_cli_normaliser_liste($valeur, $definition) {
	if ($valeur === '' || $valeur === null) {
		$liste = [];
	} elseif (is_array($valeur)) {
		$liste = array_values($valeur);
	} elseif (is_string($valeur)) {
		$json = trim($valeur);
		if ($json === '' || $json[0] !== '[' || substr($json, -1) !== ']') {
			return ['ok' => false];
		}
		$liste = json_decode($json, true);
		if (!is_array($liste) || json_last_error() !== JSON_ERROR_NONE) {
			return ['ok' => false];
		}
	} else {
		return ['ok' => false];
	}
	if (count($liste) > 1000) {
		return ['ok' => false];
	}
	$normalisee = [];
	foreach ($liste as $element) {
		if (!is_scalar($element) || is_bool($element)) {
			return ['ok' => false];
		}
		$element = trim((string) $element);
		if ($element === '' || strlen($element) > 255 || preg_match('/[\x00-\x1F\x7F]/', $element)) {
			return ['ok' => false];
		}
		if (isset($definition['allowed']) && is_array($definition['allowed']) && !in_array($element, $definition['allowed'], true)) {
			return ['ok' => false];
		}
		if (!in_array($element, $normalisee, true)) {
			$normalisee[] = $element;
		}
	}
	return ['ok' => true, 'value' => $normalisee];
}

function association_config_cli_normaliser_booleen($valeur) {
	$valeur = strtolower(trim((string) $valeur));
	if (in_array($valeur, ['1', 'on', 'oui', 'true'], true)) {
		return 'on';
	}
	if (in_array($valeur, ['0', 'off', 'non', 'false'], true)) {
		return 'off';
	}
	if ($valeur === '') {
		return 'off';
	}
	return null;
}

function association_config_cli_configuration_disponible($ecriture = false) {
	if (!function_exists('lire_config') || ($ecriture && (!function_exists('ecrire_config') || !function_exists('effacer_config')))) {
		include_spip('inc/config');
	}
	return function_exists('lire_config')
		&& (!$ecriture || (function_exists('ecrire_config') && function_exists('effacer_config')));
}

/**
 * Lit une option autorisee, ou toutes les options.
 *
 * @return array<string, mixed>
 */
function association_config_cli_lire($nom = null) {
	if (!association_config_cli_configuration_disponible()) {
		return ['ok' => false, 'reason' => 'configuration_unavailable'];
	}
	$registre = association_config_cli_registre();
	$nom = $nom === null ? '' : strtolower(trim((string) $nom));
	if ($nom !== '' && !isset($registre[$nom])) {
		return ['ok' => false, 'reason' => 'unknown_option', 'option' => $nom];
	}

	$noms = $nom === '' ? array_keys($registre) : [$nom];
	$options = [];
	foreach ($noms as $option) {
		$options[$option] = association_config_cli_valeur_effective($option, $registre);
	}
	return ['ok' => true, 'options' => $options];
}

function association_config_cli_valeur_effective($nom, $registre = null) {
	$registre = is_array($registre) ? $registre : association_config_cli_registre();
	$definition = $registre[$nom];
	if ($definition['type'] === 'aggregate_boolean') {
		return association_config_cli_lire_debug_agrege($registre);
	}
	$brute = lire_config($definition['path'], $definition['default']);
	$normalisee = association_config_cli_normaliser($brute, $definition);
	return $normalisee['ok'] ? $normalisee['value'] : $brute;
}

function association_config_cli_lire_debug_agrege($registre = null) {
	$registre = is_array($registre) ? $registre : association_config_cli_registre();
	$total = 0;
	$actives = 0;
	foreach ($registre as $nom => $definition) {
		if (strpos($nom, 'debug.') !== 0 || $definition['type'] !== 'boolean') {
			continue;
		}
		$total++;
		if (association_config_cli_valeur_effective($nom, $registre) === 'on') {
			$actives++;
		}
	}
	if ($actives === 0) {
		return 'off';
	}
	return $actives === $total ? 'on' : 'partial';
}

/**
 * Capture l'etat brut exact de toutes les options physiques autorisees.
 *
 * L'existence est conservee pour pouvoir effacer une meta qui etait absente.
 *
 * @return array<string, mixed>
 */
function association_config_cli_capturer() {
	if (!association_config_cli_configuration_disponible()) {
		return ['ok' => false, 'reason' => 'configuration_unavailable'];
	}
	$registre = association_config_cli_registre();
	$options = [];
	foreach ($registre as $nom => $definition) {
		if (empty($definition['path'])) {
			continue;
		}
		$sentinelle = ['__association_config_cli_absent__' => $nom];
		$brute = lire_config($definition['path'], $sentinelle);
		$existe = $brute !== $sentinelle;
		$options[$nom] = [
			'exists' => $existe,
			'value' => $existe ? $brute : null,
			'effective' => association_config_cli_valeur_effective($nom, $registre),
		];
	}
	return [
		'ok' => true,
		'snapshot' => [
			'format' => 'association-config-snapshot-v2',
			'plugin' => 'association',
			'registry_options' => count($options),
			'captured_at' => gmdate('c'),
			'options' => $options,
		],
	];
}

/**
 * Ecrit une option autorisee et verifie sa valeur effective.
 *
 * En cas d'echec, toutes les metas touchees retrouvent leur etat exact.
 */
function association_config_cli_ecrire($nom, $valeur) {
	$nom = strtolower(trim((string) $nom));
	$registre = association_config_cli_registre();
	if (!isset($registre[$nom]) || empty($registre[$nom]['writable'])) {
		return ['ok' => false, 'reason' => 'unknown_option', 'option' => $nom];
	}
	$normalisee = association_config_cli_normaliser($valeur, $registre[$nom]);
	if (!$normalisee['ok']) {
		return ['ok' => false, 'reason' => 'invalid_value', 'option' => $nom];
	}
	if (!association_config_cli_configuration_disponible(true)) {
		return ['ok' => false, 'reason' => 'configuration_unavailable', 'option' => $nom];
	}

	$precedente = association_config_cli_valeur_effective($nom, $registre);
	$affectees = association_config_cli_options_affectees($nom, $registre);
	$etat_initial = association_config_cli_capturer_options($affectees, $registre);
	foreach ($affectees as $option) {
		ecrire_config($registre[$option]['path'], $normalisee['value']);
	}

	$verification = true;
	foreach ($affectees as $option) {
		if (association_config_cli_valeur_effective($option, $registre) !== $normalisee['value']) {
			$verification = false;
			break;
		}
	}
	if (!$verification) {
		$restauree = association_config_cli_appliquer_etat($etat_initial, $registre);
		return [
			'ok' => false,
			'reason' => 'write_failed',
			'option' => $nom,
			'rollback_restored' => $restauree,
		];
	}

	$effective = association_config_cli_valeur_effective($nom, $registre);
	return [
		'ok' => true,
		'option' => $nom,
		'previous' => $precedente,
		'value' => $effective,
		'verified' => true,
		'changed' => $precedente !== $effective,
		'affected_options' => $affectees,
	];
}

function association_config_cli_options_affectees($nom, $registre) {
	if ($registre[$nom]['type'] !== 'aggregate_boolean') {
		return [$nom];
	}
	$options = [];
	foreach ($registre as $option => $definition) {
		if (strpos($option, 'debug.') === 0 && $definition['type'] === 'boolean') {
			$options[] = $option;
		}
	}
	return $options;
}

function association_config_cli_capturer_options($options, $registre) {
	$etat = [];
	foreach ($options as $nom) {
		$sentinelle = ['__association_config_cli_absent__' => $nom];
		$valeur = lire_config($registre[$nom]['path'], $sentinelle);
		$etat[$nom] = ['exists' => $valeur !== $sentinelle, 'value' => $valeur !== $sentinelle ? $valeur : null];
	}
	return $etat;
}

function association_config_cli_appliquer_etat($etat, $registre) {
	foreach ($etat as $nom => $valeur) {
		if (!empty($valeur['exists'])) {
			ecrire_config($registre[$nom]['path'], $valeur['value']);
		} else {
			effacer_config($registre[$nom]['path']);
		}
	}
	return association_config_cli_etat_identique($etat, $registre);
}

function association_config_cli_etat_identique($etat, $registre) {
	$actuel = association_config_cli_capturer_options(array_keys($etat), $registre);
	return $actuel === $etat;
}

/**
 * Restaure un instantane complet apres validation, avec rollback technique.
 */
function association_config_cli_restaurer($snapshot) {
	if (!association_config_cli_configuration_disponible(true)) {
		return ['ok' => false, 'reason' => 'configuration_unavailable'];
	}
	$validation = association_config_cli_valider_snapshot($snapshot);
	if (!$validation['ok']) {
		return $validation;
	}
	$registre = association_config_cli_registre();
	$etat_initial = association_config_cli_capturer_options(array_keys($validation['state']), $registre);
	if (!association_config_cli_appliquer_etat($validation['state'], $registre)) {
		$rollback = association_config_cli_appliquer_etat($etat_initial, $registre);
		return ['ok' => false, 'reason' => 'restore_failed', 'rollback_restored' => $rollback];
	}
	return ['ok' => true, 'restored' => count($validation['state']), 'verified' => true];
}

function association_config_cli_valider_snapshot($snapshot) {
	if (!is_array($snapshot)
		|| !isset($snapshot['format'])
		|| !in_array($snapshot['format'], ['association-config-snapshot-v1', 'association-config-snapshot-v2'], true)
		|| !isset($snapshot['plugin'])
		|| $snapshot['plugin'] !== 'association'
		|| !isset($snapshot['options'])
		|| !is_array($snapshot['options'])) {
		return ['ok' => false, 'reason' => 'invalid_snapshot'];
	}
	$registre = association_config_cli_registre();
	$attendues = [];
	foreach ($registre as $nom => $definition) {
		if (!empty($definition['path'])) {
			$attendues[] = $nom;
		}
	}
	$noms = array_keys($snapshot['options']);
	sort($attendues);
	sort($noms);
	if ($snapshot['format'] === 'association-config-snapshot-v2' && $noms !== $attendues) {
		return ['ok' => false, 'reason' => 'snapshot_options_mismatch'];
	}
	if ($snapshot['format'] === 'association-config-snapshot-v1') {
		$legacy = association_config_cli_snapshot_v1_options($registre);
		sort($legacy);
		if ($noms !== $legacy) {
			return ['ok' => false, 'reason' => 'snapshot_options_mismatch'];
		}
	}
	$etat = [];
	foreach ($snapshot['options'] as $nom => $option) {
		if (!is_array($option) || !array_key_exists('exists', $option) || !is_bool($option['exists'])) {
			return ['ok' => false, 'reason' => 'invalid_snapshot', 'option' => $nom];
		}
		if ($option['exists']) {
			if (!array_key_exists('value', $option)) {
				return ['ok' => false, 'reason' => 'invalid_snapshot', 'option' => $nom];
			}
			$normalisee = association_config_cli_normaliser($option['value'], $registre[$nom]);
			if (!$normalisee['ok']) {
				return ['ok' => false, 'reason' => 'invalid_snapshot_value', 'option' => $nom];
			}
		}
		$etat[$nom] = ['exists' => $option['exists'], 'value' => $option['exists'] ? $option['value'] : null];
	}
	return ['ok' => true, 'state' => $etat];
}

function association_config_cli_snapshot_v1_options($registre) {
	$options = [];
	foreach ($registre as $nom => $definition) {
		if (strpos($nom, 'debug.') === 0 && !empty($definition['path'])) {
			$options[] = $nom;
		}
	}
	$options = pipeline('association_config_cli_snapshot_v1_options', $options);
	return array_values(array_unique($options));
}

function association_config_cli_code_sortie($resultat) {
	if (!empty($resultat['ok'])) {
		return 0;
	}
	if (in_array($resultat['reason'] ?? '', [
		'unknown_option',
		'invalid_value',
		'invalid_snapshot',
		'invalid_snapshot_value',
		'snapshot_options_mismatch',
		'invalid_format',
	], true)) {
		return 2;
	}
	return 1;
}
