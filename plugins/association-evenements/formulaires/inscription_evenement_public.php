<?php
/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/
if(!defined('_ECRIRE_INC_VERSION')) return;
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('formulaires/inc/inscription_evenement');
include_spip('inc/fonctions/activite_enregistrement_calculator');
include_spip('formulaires/inc/inscription_evenement_backend');

function ie_inscription_public_ids($id_evenement = '', $id_activite = '') {
    return ie_resoudre_ids_inscription($id_evenement, $id_activite);
}

function formulaires_inscription_evenement_public_charger_dist($id_evenement = '', $id_activite = '') {
    list($id_evenement, $id_activite) = ie_inscription_public_ids($id_evenement, $id_activite);
    return ie_charger_commons('public', $id_evenement, $id_activite);
}

function formulaires_inscription_evenement_public_verifier_dist($id_evenement = '', $id_activite = '') {
    list($id_evenement, $id_activite) = ie_inscription_public_ids($id_evenement, $id_activite);
    $erreurs = ie_verifier_commons('public', $id_evenement, $id_activite, null);
   return $erreurs;
}
function formulaires_inscription_evenement_public_traiter_dist($id_evenement = '', $id_activite = '') {
    list($id_evenement, $id_activite) = ie_inscription_public_ids($id_evenement, $id_activite);
   return ie_traiter_commons('public', $id_evenement, $id_activite, null);
}
