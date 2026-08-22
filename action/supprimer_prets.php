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

function action_supprimer_prets_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	if (preg_match('/^(\d+)\D(\d+)/', $arg, $r)) {
		list(,$id_pret,$id_ressource) = $r;
		$id_pret = (int) $id_pret;
		$id_ressource = (int) $id_ressource;
		if (!autoriser('supprimer', 'pret', $id_pret)) {
			include_spip('inc/minipres');
			echo minipres();
			exit;
		}
		sql_delete('spip_asso_prets', 'id_pret=' . $id_pret);
		sql_delete('spip_asso_comptes', 'id_journal=' . $id_pret);
		sql_updateq('spip_asso_ressources',
			array('statut'=>'ok'),
			'id_ressource=' . $id_ressource);
	}
}


