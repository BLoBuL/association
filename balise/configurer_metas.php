<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2010                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

// Une balise qui prend en argument un squelette suppose contenir un FORM
// et gere ses saises automatiquement dans une table SQL a 2 colonnes
// nom / valeur

// Comme l'emplacement du squelette est calcule (par l'argument de la balise)
// on ne peut rien dire sur l'existence du squelette lors de la compil
// On pourrait toutefois traiter le cas de l'argument qui est une constante.
/*
include_spip('inc/editer');
include_spip('inc/mailsubscribers');
include_spip('inc/bank');*/
/*function balise_CONFIGURER_METAS_dist($p) {

	return calculer_balise_dynamique($p, $p->nom_champ, array());
}*/

// A l'execution on dispose du nom du squelette, on verifie qu'il existe.
// Pour le calcul du contexte, c'est comme la balise #FORMULAIRE_.
// y compris le controle au retour pour faire apparaitre le message d'erreur.
/*
function balise_CONFIGURER_METAS_dyn($form) {
	include_spip('balise/formulaire_');
	if (!existe_formulaire($form)) return '';
	$args = func_get_args();
	$contexte = balise_FORMULAIRE__contexte('configurer_metas', $args);

	//$contexte = $GLOBALS['association_metas'];

	$contexte['config_filtres_annuaire'] = unserialize($GLOBALS['association_metas']['config_filtres_annuaire']);
	$contexte['mode_paiement_adhesion'] = unserialize($GLOBALS['association_metas']['mode_paiement_adhesion']);
	$contexte['mode_paiement_participation'] = unserialize($GLOBALS['association_metas']['mode_paiement_participation']);
	$contexte['mode_paiement_formidable'] = unserialize($GLOBALS['association_metas']['mode_paiement_formidable']);
	$contexte['notification_echeance_cotisation'] = unserialize($GLOBALS['association_metas']['notification_echeance_cotisation']);
	$contexte['notification_echeance_cotisation_entreprise'] = unserialize($GLOBALS['association_metas']['notification_echeance_cotisation_entreprise']);
	$contexte['selection_segment'] = unserialize($GLOBALS['association_metas']['selection_segment']);
	$contexte['liste_diffusion'] = unserialize($GLOBALS['association_metas']['liste_diffusion']);
	$contexte['notification_gis_config_action'] = unserialize($GLOBALS['association_metas']['notification_gis_config_action']);

	$contexte['listes'] = lire_config('mailsubscribers/lists',array());
	//$contexte['prestas'] = lire_config('bank/prestas',array());
	if (!is_array($contexte)) return $contexte;
	return array('formulaires/' . $form, 3600, $contexte);
}*/
