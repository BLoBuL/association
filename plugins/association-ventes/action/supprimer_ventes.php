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

include_spip('inc/association_ventes_comptabilite');

function action_supprimer_ventes_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$securiser_action();
	if (!autoriser('supprimer', 'vente')) {
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}
	$ids = array_values(array_unique(array_filter(array_map('intval', (array) _request('drop')))));
	if (!$ids) {
		return;
	}
	$where_ventes = sql_in('id_vente', $ids);
	sql_delete('spip_asso_ventes', $where_ventes);

	$where_lien = '(' . sql_in('id_objet', $ids) . " AND objet='asso_vente') OR " . sql_in('id_journal', $ids);
	$imputations = array_values(array_unique(array_filter(array(
		$GLOBALS['association_metas']['pc_ventes'] ?? '',
		$GLOBALS['association_metas']['pc_frais_envoi'] ?? '',
	), 'strlen')));
	$where_comptes = '(' . $where_lien . ') AND ' . sql_in('imputation', $imputations);
	$ids_comptes = sql_allfetsel('id_compte', 'spip_asso_comptes', $where_comptes);
	$ids_comptes = array_map('intval', array_column($ids_comptes, 'id_compte'));
	if ($ids_comptes) {
		sql_delete('spip_asso_destination_op', sql_in('id_compte', $ids_comptes));
	}
	sql_delete('spip_asso_comptes', $where_comptes);
}

