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

function association_upgrade($nom_meta_base_version, $version_cible) {
	include_spip('inc/association_migrations');
	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, association_migrations_construire());
}

function association_vider_tables($nom_meta_base_version) {
	// Le socle ne supprime que sa propre table. Les tables et champs métier
	// restent sous la responsabilité de leurs plugins respectifs.
	sql_drop_table('spip_association_metas');

	effacer_meta($nom_meta_base_version);
	effacer_meta('association_metas');
}
