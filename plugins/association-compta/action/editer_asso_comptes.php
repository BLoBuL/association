<?php
/***************************************************************************\
 *  Associaspip, extension de SPIP pour gestion d'associations              *
 *                                                                          *
 *  Copyright (c) 2007 Bernard Blazin & François de Montlivault (V1)        *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                          *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.      *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.    *
\***************************************************************************/


if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/comptes');

function action_editer_asso_comptes() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_compte = intval($securiser_action());

	$imputation= _request('imputation');
	$date= _request('date');
	$date= affdate($date,'Y-m-d');

	if(_request('type_operation') == 'recette') {
		$recette = association_recupere_montant(_request('montant'));
		$depense = 0;
	} else {
		$recette = 0;
		$depense = association_recupere_montant(_request('montant'));
	}

	$justification= _request('justification');
	$journal= _request('journal');

	$inscription = $id_category = $status_cotisation = $id_transaction = '';

	if (!is_int($id_compte)) {
		// pas d'id_compte, c'est un ajout
		$id_auteur = $GLOBALS['auteur_session']['id_auteur'];

		$id_compte = inserer_compte(
			$date, $recette, $depense, $justification, $imputation, $journal,
			$id_auteur, $inscription, $id_category, $status_cotisation, $id_transaction
		);
	} else {
		// c'est une modif, le paramétre id_journal de la fonction modifier operation comptable est mis a '' afin de ne pas le modifier dans la base
		modifier_compte(
			$id_compte, $date, $recette, $depense, $justification, $imputation, $journal,
			$inscription, $id_category, $status_cotisation, $id_transaction
		);
	}

	return array($id_compte, '');
}

