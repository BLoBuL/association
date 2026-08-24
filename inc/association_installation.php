<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Fusionne une contribution au contrat d'installation de la suite.
 *
 * @param array $inventaire
 * @param array $contribution
 * @return array
 */
function association_installation_ajouter($inventaire, $contribution) {
	foreach (array('plugins', 'tables', 'objets') as $cle) {
		$inventaire[$cle] = array_values(array_unique(array_merge(
			(array) ($inventaire[$cle] ?? array()),
			(array) ($contribution[$cle] ?? array())
		)));
	}
	foreach ((array) ($contribution['schemas'] ?? array()) as $meta => $version) {
		if (isset($inventaire['schemas'][$meta]) && $inventaire['schemas'][$meta] !== $version) {
			$inventaire['erreurs'][] = "Schéma déclaré deux fois avec des versions différentes : $meta";
			continue;
		}
		$inventaire['schemas'][$meta] = $version;
	}
	return $inventaire;
}

/**
 * Construit l'inventaire distribué de l'installation Association.
 *
	 * Le socle décrit uniquement sa propre installation. Les modules actifs
	 * contribuent à l'inventaire sans être requis par le socle.
 *
 * @return array
 */
function association_installation_inventaire() {
	$inventaire = array(
		'plugins_requis' => array('association'),
		'plugins' => array('association'),
		'tables' => array('spip_association_metas'),
		'objets' => array(),
		'schemas' => array('association_base_version' => '1.6.1'),
		'erreurs' => array(),
	);
	return pipeline('association_installation_inventaire', $inventaire);
}
