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

function action_supprimer_ressources_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_ressource = (int) $securiser_action();
	if (!$id_ressource || !autoriser('supprimer', 'ressource', $id_ressource)) {
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}
	sql_delete('spip_asso_ressources', 'id_ressource=' . $id_ressource);
}


