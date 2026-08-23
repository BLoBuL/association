<?php

/**
 * Pipelines transverses de la suite Association.
 *
 * @plugin     Association
 * @copyright  2016-2026
 * @licence    GPL-3.0-or-later
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/boutons');

/**
 * Préparer les ressources de l'espace privé.
 *
 * @param string $flux
 * @return string
 */
function association_header_prive($flux) {
	// La barre est générée par son squelette public. Cet appel préserve le
	// préchargement historique sans injecter directement de balise script.
	generer_url_public('barre_generalisee.js');

	return $flux;
}

/**
 * Déclarer les composants jQuery UI utilisés par les formulaires privés.
 *
 * @param array $scripts
 * @return array
 */
function association_jqueryui_plugins($scripts) {
	$scripts[] = 'jquery.ui.datepicker';
	$scripts[] = 'jquery.ui.i18n/jquery.ui.datepicker-fr';
	$scripts[] = 'jquery.ui.autocomplete';

	return array_values(array_unique($scripts));
}

/**
 * Planifier la maintenance transversale de la suite.
 *
 * @param array $taches
 * @return array
 */
function association_taches_generales_cron($taches) {
	$taches['association_maintenance_bdd'] = 7 * 24 * 60 * 60;

	return $taches;
}

/**
 * Regrouper les entrées des modules dans le menu privé Association.
 *
 * @param array $menus
 * @return array
 */
function association_ajouter_menus($menus) {
	$entrees = [
		'adherents' => '010. ',
		'cotisations' => '020. ',
		'activites' => '030. ',
		'benevoles' => '040. ',
		'configurer_association' => '099. ',
	];

	foreach (['dons' => '060. ', 'comptes' => '070. ', 'prets' => '080. '] as $module => $ordre) {
		if (association_module_actif($module)) {
			$entrees[$module] = $ordre;
		}
	}
	foreach ($entrees as $cle => $ordre) {
		if (!autoriser($cle . '_menu', '', 0, $GLOBALS['visiteur_session'])) {
			unset($entrees[$cle]);
		}
	}

	$classe_bouton = version_compare($GLOBALS['spip_version_branche'], '4.2', '>=')
		? '\\Spip\\Admin\\Bouton'
		: 'Bouton';
	$menu = new $classe_bouton(
		icone_association('association'),
		_T('association:titre_menu_association'),
		generer_url_ecrire('navigation', 'menu=association')
	);
	foreach ($entrees as $cle => $ordre) {
		$menu->sousmenu[$cle] = new $classe_bouton(
			find_in_theme("images/{$cle}-xx.svg"),
			"<span class='d-none'>{$ordre}</span>" . _T('association:titre_onglet_' . $cle),
			generer_url_ecrire($cle)
		);
	}

	return array_merge(array_slice($menus, 0, 2), ['association' => $menu], array_slice($menus, 2));
}

/**
 * Trouver l'icône d'un plugin de la suite.
 *
 * @param string $prefixe_plugin
 * @return string
 */
function icone_association($prefixe_plugin) {
	$icone = find_in_theme("images/{$prefixe_plugin}-xx.svg");
	if (!$icone && function_exists('fouiller_paquet')) {
		$logo = fouiller_paquet($prefixe_plugin, 'logo');
		$icone = $logo ? find_in_path($logo) : '';
	}
	if (!$icone) {
		$prefixe_plugin = rtrim($prefixe_plugin, 's');
		$icone = find_in_theme("images/{$prefixe_plugin}-xx.svg");
	}

	return $icone ?: '';
}
