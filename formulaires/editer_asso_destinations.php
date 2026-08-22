<?php
/***************************************************************************\
 *  Associaspip, extension de SPIP pour gestion d'associations
 *
 * @copyright Copyright (c) 2007 Bernard Blazin & Francois de Montlivault
 * @copyright Copyright (c) 2010 Emmanuel Saint-James
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
\***************************************************************************/

if (!defined('_ECRIRE_INC_VERSION'))
	return;

include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_editer_asso_destinations_charger_dist($id_destination='') {
    $contexte = formulaires_editer_objet_charger('asso_destinations', $id_destination, '', '',  generer_url_ecrire('destinations'), '');
    $contexte['title'] = $id_destination == '' ? _T('association:ajouter_destination') : _T('association:modifier_destination');
	return $contexte;
}

function formulaires_editer_asso_destinations_verifier_dist($id_destination='') {
	$erreurs = [];

	if (count($erreurs)) {
		$erreurs['erreur_message'] = _T('association:erreur_titre');
	}
	return $erreurs;
}

function formulaires_editer_asso_destinations_traiter_dist($id_destination='') {
	return formulaires_editer_objet_traiter('asso_destinations', $id_destination, '', '',  generer_url_ecrire('destinations'), '');
}

