<?php
/***************************************************************************\
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & François de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/


if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/association_dons_comptabilite');
include_spip('inc/destinations');

function action_editer_asso_dons($id_don = null) {
	if ($id_don === null) {
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$id_don = $securiser_action();
	}
	$id_don = (int) $id_don;

	$journal= _request('journal');
	$date_don = _request('date_don');

	$bienfaiteur = _request('bienfaiteur');
	$id_adherent = intval(_request('id_adherent'));

	if (!$bienfaiteur AND $id_adherent) {
		$bienfaiteur = generer_info_entite($id_adherent, 'auteur', 'titre');
	}

	if ($id_adherent) {
		$bienfaiteur = "[$bienfaiteur" . "->auteur$id_adherent]";
	}

	$argent = association_recupere_montant(_request('argent'));
	$colis = _request('colis');
	$valeur = association_recupere_montant(_request('valeur'));
	$contrepartie = _request('contrepartie');
	$commentaire = _request('commentaire');

	if ($id_don) { /* c'est une modification */
		$id_compte = _request('id_compte');

		// on modifie l'operation comptable associe au don
		association_dons_compte_modifier(
			$id_compte, $date_don, $argent, $journal, $bienfaiteur, $id_don, $id_adherent
		);
		ajouter_destinations((int) $id_compte, (float) $argent, 0);

		sql_updateq('spip_asso_dons', array(
				'date_don' => $date_don,
				'bienfaiteur' => $bienfaiteur,
				'id_adherent' => $id_adherent,
				'argent' => $argent,
				'colis' => $colis,
				'valeur' => $valeur,
				'contrepartie' => $contrepartie,
				'commentaire' => $commentaire),
			    "id_don=$id_don");
	} else { /* c'est un ajout */
		$id_don = sql_insertq('spip_asso_dons', array(
			'date_don' => $date_don,
			'bienfaiteur' => $bienfaiteur,
			'id_adherent' => $id_adherent,
			'argent' => $argent,
			'colis' => $colis,
			'valeur' => $valeur,
			'contrepartie' => $contrepartie,
		 	'commentaire' => $commentaire));

		$id_compte = association_dons_compte_creer($date_don, $argent, $journal, $bienfaiteur, $id_don, $id_adherent);
		ajouter_destinations((int) $id_compte, (float) $argent, 0);
	}

	return array($id_don, '');
}

