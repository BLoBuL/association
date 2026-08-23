<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Exporter les données RGPD fournies par la suite Association.
 */
function association_export_donnees_auteur($id_auteur) {
	include_spip('inc/rgpd_export');
	return association_rgpd_export_donnees_auteur($id_auteur);
}

/**
 * Construire un nom de fichier explicite pour un export RGPD.
 */
function association_nom_fichier_export_rgpd($date = null, $id_auteur = 0) {
	$adresse_site = $GLOBALS['meta']['adresse_site'] ?? '';
	$host = parse_url($adresse_site, PHP_URL_HOST);
	if (!$host) {
		$host = preg_replace(',^https?://,i', '', (string) $adresse_site);
		$host = explode('/', $host)[0] ?? '';
	}
	$host = strtolower(trim((string) $host));
	$host = preg_replace('/^www\./', '', $host);
	$host = preg_replace('/[^a-z0-9.-]+/', '-', $host);
	$host = trim(str_replace('.', '_', $host), '-_');
	if ($host === '') {
		$host = 'site';
	}

	$timestamp = $date ? strtotime((string) $date) : time();
	if (!$timestamp) {
		$timestamp = time();
	}
	$suffixe_auteur = intval($id_auteur) > 0 ? '-' . intval($id_auteur) : '';

	return 'export-association-' . $host . $suffixe_auteur . '-' . date('Y-m-d', $timestamp) . '.json';
}

/**
 * Désérialiser les valeurs PHP d'un tableau de configuration.
 */
function deserialize_values($array) {
	foreach ($array as $key => $value) {
		if (is_string($value) && association_is_serialized($value)) {
			$array[$key] = unserialize($value);
		}
	}
	return $array;
}

/**
 * Tester si une valeur est une chaîne PHP sérialisée.
 */
function association_is_serialized($value) {
	return is_string($value) && ($value === 'b:0;' || @unserialize($value) !== false);
}

/**
 * Ramener une valeur de squelette à une chaîne scalaire.
 */
function filtre_scalar_val($val, $defaut = '') {
	if (is_array($val)) {
		foreach ($val as $value) {
			if ($value === null || is_array($value)) {
				continue;
			}
			$scalar = trim((string) $value);
			if ($scalar !== '') {
				return $scalar;
			}
		}
		$valeurs = array();
		array_walk_recursive($val, static function ($value) use (&$valeurs) {
			if (!is_array($value)) {
				$valeurs[] = (string) $value;
			}
		});
		return $valeurs ? implode(',', $valeurs) : $defaut;
	}
	if ($val === null || $val === '') {
		return $defaut;
	}
	return (string) $val;
}

/**
 * Construire la navigation de configuration fournie par les modules actifs.
 *
 * @param mixed $webmestre
 * @return array
 */
function association_configuration_navigation($webmestre = false) {
	$webmestre = in_array($webmestre, [true, 1, '1', 'oui', 'on'], true);
	$flux = [
		'args' => ['webmestre' => $webmestre],
		'data' => [
			'info' => ['ordre' => 10, 'label' => 'association_config:navigation_config_info'],
			'modules' => ['ordre' => 90, 'label' => 'association_config:navigation_config_modules'],
		],
	];
	if ($webmestre) {
		$flux['data']['maintenance_bdd'] = ['ordre' => 110, 'label' => 'association_config:navigation_config_maintenance_bdd'];
		$flux['data']['debug'] = ['ordre' => 120, 'label' => 'association_config:navigation_config_debug'];
	}

	$flux = pipeline('association_configuration_navigation', $flux);
	$navigation = !empty($flux['data']) && is_array($flux['data']) ? $flux['data'] : [];
	foreach ($navigation as $config => &$entree) {
		$page = $entree['page'] ?? 'configurer_association';
		$entree['url'] = generer_url_ecrire(
			$page,
			$page === 'configurer_association' ? 'config=' . urlencode($config) : ''
		);
	}
	unset($entree);
	uasort($navigation, static fn ($a, $b) => ($a['ordre'] ?? 999) <=> ($b['ordre'] ?? 999));

	return $navigation;
}
