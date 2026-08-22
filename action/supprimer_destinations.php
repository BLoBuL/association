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

function action_supprimer_destinations_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_destination = (int) $securiser_action();
	if (!$id_destination || !autoriser('supprimer', 'destination', $id_destination)) {
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}
	sql_delete('spip_asso_destination', 'id_destination=' . $id_destination);
}


