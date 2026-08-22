<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Indique si les catégories de cotisation peuvent utiliser plusieurs devises.
 *
 * L'option est volontairement désactivée quand elle n'a jamais été configurée.
 */
function association_cotisations_multidevises_actives() {
    $valeur = $GLOBALS['association_metas']['meta_cfg_cotisations_multidevises'] ?? null;
    if ($valeur === null && function_exists('lire_config')) {
        $valeur = lire_config('association_metas/meta_cfg_cotisations_multidevises', 'non');
    }

    return $valeur === 'oui';
}

/**
 * Retourne la devise configurée par Intl pour le site.
 */
function association_cotisation_devise_defaut() {
    include_spip('intl_fonctions');

    $devise = function_exists('intl_devise_defaut')
        ? intl_devise_defaut()
        : lire_config('intl/devise_defaut');

    return strtoupper(trim((string) $devise));
}

/**
 * Retourne les devises proposées pour les seules catégories de cotisation.
 */
function association_cotisation_devises_disponibles() {
    include_spip('intl_fonctions');

    $devises = array();
    foreach ((array) intl_lister_devises() as $code => $informations) {
        $code = strtoupper(trim((string) $code));
        if (!preg_match('/^[A-Z]{3}$/', $code)) {
            continue;
        }
        $symbole = trim((string) ($informations['symbole'] ?? ''));
        $devises[$code] = $symbole !== '' && $symbole !== $code
            ? $code . ' — ' . $symbole
            : $code;
    }

    $devise_defaut = association_cotisation_devise_defaut();
    if ($devise_defaut !== '' && !isset($devises[$devise_defaut])) {
        $devises[$devise_defaut] = $devise_defaut;
    }

    if (!association_cotisations_multidevises_actives()) {
        return $devise_defaut !== '' ? array($devise_defaut => $devises[$devise_defaut]) : array();
    }

    ksort($devises);
    return $devises;
}

/**
 * Résout la devise d'une catégorie, avec repli sur la configuration Intl.
 */
function association_cotisation_resoudre_devise($devise) {
    $devise = strtoupper(trim((string) $devise));
    $devises = association_cotisation_devises_disponibles();

    return isset($devises[$devise])
        ? $devise
        : association_cotisation_devise_defaut();
}
