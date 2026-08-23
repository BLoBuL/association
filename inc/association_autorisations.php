<?php
// Helpers d'autorisation propres au plugin Association.

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}

/**
 * Helpers centraux de vérification de droits — plugin Association.
 *
 * Chargé via include_spip('inc/association_autorisations') depuis association_autoriser.php.
 * Regroupe les utilitaires partagés par toutes les fonctions d'autorisation du plugin.
 *
 * Licence GPL 3
 */

/**
 * Vérifie si l'utilisateur est administrateur non-restreint.
 * Centralise le pattern récurrent : $qui['statut'] === '0minirezo' && !$qui['restreint']
 *
 * @param array $qui utilisateur normalisé (via association_normalize_qui)
 * @return bool
 */
function association_est_admin_complet($qui) {
	return isset($qui['statut'], $qui['restreint'])
		&& $qui['statut'] === '0minirezo'
		&& !$qui['restreint'];
}
