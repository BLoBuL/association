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

include_spip('inc/association_compta_ecritures');

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

	if ($id_compte <= 0) {
		// pas d'id_compte, c'est un ajout
		$id_auteur = $GLOBALS['auteur_session']['id_auteur'];

		$id_compte = association_compta_ecriture_creer(array(
			'date' => $date, 'recette' => $recette, 'depense' => $depense,
			'justification' => $justification, 'imputation' => $imputation, 'journal' => $journal,
			'id_auteur' => (int) $id_auteur,
		));
	} else {
		// c'est une modif, le paramétre id_journal de la fonction modifier operation comptable est mis a '' afin de ne pas le modifier dans la base
		association_compta_ecriture_modifier($id_compte, array(
			'date' => $date, 'recette' => $recette, 'depense' => $depense,
			'justification' => $justification, 'imputation' => $imputation, 'journal' => $journal,
		));
	}

	return array($id_compte, '');
}

