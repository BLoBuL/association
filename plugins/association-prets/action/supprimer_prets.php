<?php

/*\
 *  Associaspip, extension de SPIP pour gestion d'associations             *
 *                                                                         *
 *  Copyright (c) 2007 Bernard Blazin & François de Montlivault (V1)       *
 *  Copyright (c) 2010-2011 Emmanuel Saint-James & Jeannot Lapin (V2)       *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\*/

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/prets');

function action_supprimer_prets_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	if ($id_pret = intval($arg)) {
		if (!autoriser('supprimer', 'pret', $id_pret)) {
			include_spip('inc/minipres');
			echo minipres();
			exit;
		}
		$id_ressource = intval(sql_getfetsel('id_ressource', 'spip_asso_prets', 'id_pret=' . $id_pret));
		if ($id_ressource <= 0) {
			return;
		}
		sql_query('START TRANSACTION');
		$ok = sql_delete('spip_asso_prets', 'id_pret=' . $id_pret) !== false;
		$ok = $ok && association_prets_compte_supprimer($id_pret);
		$ok = $ok && association_prets_synchroniser_statut_ressource($id_ressource);
		sql_query($ok ? 'COMMIT' : 'ROLLBACK');
	}
}
