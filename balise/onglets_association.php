<?php
/***************************************************************************
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & Francois de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Balise dynamique pour les onglets de l'association.
 *
 * @param object $p L'objet de la balise.
 * @return object La balise dynamique calculée.
 */
function balise_ONGLETS_ASSOCIATION_dist($p) {
    return calculer_balise_dynamique($p, 'ONGLETS_ASSOCIATION', array());
}

/**
 * Fonction statique pour la balise des onglets de l'association.
 *
 * @param array $args Les arguments statiques de la balise.
 * @return array Les arguments statiques de la balise.
 */
function balise_ONGLETS_ASSOCIATION_stat($args) {
    return $args; // On se contente de faire suivre l'argument statique de la balise
}

/**
 * Fonction dynamique pour la balise des onglets de l'association.
 *
 * @param string $page La page actuelle.
 * @return string Le contenu HTML des onglets de l'association.
 */
function balise_ONGLETS_ASSOCIATION_dyn($page) {
    $association_onglets = recuperer_fond('prive/squelettes/top/inc-top_association', array('page' => $page));
    return $association_onglets;
}
