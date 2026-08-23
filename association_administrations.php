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
include_spip('base/abstract_sql');
// Pont de compatibilité : les callbacks 1.6.x du socle historique délèguent
// désormais leur traitement au plugin propriétaire des cotisations.
include_spip('inc/association_adhesions_migration');
include_spip('inc/association_adhesions_migration_legacy');
include_spip('inc/association_evenements_migration_legacy');
include_spip('inc/association_compta_migration_legacy');
include_spip('inc/association_communication_migration_legacy');
include_spip('inc/association_dons_migration_legacy');
include_spip('inc/association_ventes_migration_legacy');
include_spip('inc/association_prets_migration_legacy');
function association_upgrade($nom_meta_base_version, $version_cible) {
    include_spip('inc/meta');
    include_spip('inc/cextras');
    include_spip('base/association');
    include_spip('public/interfaces');
    $maj = array();
#CREATE
    // Une installation sans meta doit suivre la branche `create` native de
    // maj_plugin(). Ne surtout pas fabriquer une ancienne version de schema :
    // cela rejouerait les migrations historiques destructives sur une base
    // neuve. Le socle ne possede plus que sa table de configuration ; les
    // tables metier sont installees par leurs plugins proprietaires.
    $maj['create'] = array(
        array('maj_tables', array('spip_association_metas')),
    );
    // Les champs extras et leurs migrations appartiennent désormais aux
    // modules métier qui les déclarent. Le socle n'en installe aucun.
#V1.1.0
	$maj['1.1.0'] = array(
		array('maj_tables', array('spip_association_metas')),
		array('association_adhesions_migration_legacy', '1.1.0'),
		array('association_dons_migration_legacy', '1.1.0'),
		array('association_ventes_migration_legacy', '1.1.0'),
		array('association_compta_migration_legacy', '1.1.0'),
		array('association_prets_migration_legacy', '1.1.0'),
		array('association_evenements_migration_legacy', '1.1.0'),
	);
#V1.1.1
	$maj['1.1.1'] = array(array('association_evenements_migration_legacy', '1.1.1'));
#V1.1.2
	$maj['1.1.2'] = array(array('association_evenements_migration_legacy', '1.1.2'));
#V1.1.3
	$maj['1.1.3'] = array(array('association_adhesions_migration_legacy', '1.1.3'));
#V1.1.4
	$maj['1.1.4'] = array(array('association_adhesions_migration_legacy', '1.1.4'));
#1.1.5
	$maj['1.1.5'] = array(
		array('association_adhesions_migration_legacy', '1.1.5'),
		array('association_evenements_migration_legacy', '1.1.5'),
	);
#1.1.6
	$maj['1.1.6'] = array(array('association_adhesions_migration_legacy', '1.1.6'));
#1.1.7
	$maj['1.1.7'] = array(
		array('association_adhesions_migration_legacy', '1.1.7'),
		array('association_compta_migration_legacy', '1.1.7'),
	);
#1.1.8
	$maj['1.1.8'] = array(
		array('association_adhesions_migration_legacy', '1.1.8'),
		array('association_compta_migration_legacy', '1.1.8'),
	);
#1.1.9
	$maj['1.1.9'] = array(
		array('association_adhesions_migration_legacy', '1.1.9'),
		array('association_compta_migration_legacy', '1.1.9'),
	);
#1.1.10
	$maj['1.1.10'] = array(array('association_compta_migration_legacy', '1.1.10'));

#1.1.13 ( PB d'insertion..)
//cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.13']);
	$maj['1.1.13'] = array(
		array('association_adhesions_migration_legacy', '1.1.13'),
		array('association_evenements_migration_legacy', '1.1.13'),
	);
#1.1.15
	$maj['1.1.15'] = array(array('association_evenements_migration_legacy', '1.1.15'));
#1.2.0
	$maj['1.2.0'] = array(
		array('association_adhesions_migration_legacy', '1.2.0'),
		array('association_evenements_migration_legacy', '1.2.0'),
	);
#1.2.1
$maj['1.2.1'] = array(array('association_evenements_migration_legacy', '1.2.1'));
#1.2.2
$maj['1.2.2'] = array(array('association_adhesions_migration_legacy', '1.2.2'));
#1.2.3
$maj['1.2.3'] = array(array('association_evenements_migration_legacy', '1.2.3'));
#1.2.3
$maj['1.2.4'] = array(array('association_compta_migration_legacy', '1.2.4'));
#1.2.5
$maj['1.2.5'] = array(array('association_evenements_migration_legacy', '1.2.5'));
#1.2.6
$maj['1.2.6'] = array(array('association_evenements_migration_legacy', '1.2.6'));
#1.2.7
$maj['1.2.7'] = array();
#1.2.8
$maj['1.2.8'] = array(array('association_evenements_migration_legacy', '1.2.8'));
#1.2.9
$maj['1.2.9'] = array(array('association_communication_migration_legacy', '1.2.9'));
$maj['1.3.0'] = array(array('association_evenements_migration_legacy', '1.3.0'));
$maj['1.3.1'] = array(array('association_evenements_migration_legacy', '1.3.1'));
$maj['1.3.2'] = array(array('association_evenements_migration_legacy', '1.3.2'));
$maj['1.3.3'] = array(
	array('association_evenements_migration_legacy', '1.3.3'),
	array('association_adhesions_migration_legacy', '1.3.3'),
);
$maj['1.3.4'] = array(array('association_evenements_migration_legacy', '1.3.4'));
$maj['1.3.5'] = array(array('association_evenements_migration_legacy', '1.3.5'));
$maj['1.3.6'] = array(array('association_evenements_migration_legacy', '1.3.6'));
$maj['1.3.7'] = array(array('association_evenements_migration_legacy', '1.3.7'));
$maj['1.3.8'] = array(array('association_evenements_migration_legacy', '1.3.8'));
$maj['1.3.9'] = array(array('association_evenements_migration_legacy', '1.3.9'));
	$maj['1.4.0'] = array(array('association_evenements_migration_legacy', '1.4.0'));
	$maj['1.4.1'] = array(array('association_evenements_migration_legacy', '1.4.1'));
	$maj['1.4.2'] = array(array('association_adhesions_migration_legacy', '1.4.2'));
	$maj['1.4.3'] = array(array('association_evenements_migration_legacy', '1.4.3'));
	$maj['1.4.4'] = array(array('association_evenements_migration_legacy', '1.4.4'));
	$maj['1.4.5'] = array(array('association_evenements_migration_legacy', '1.4.5'));
	$maj['1.4.7'] = array(array('association_compta_migration_legacy', '1.4.7'));
	$maj['1.4.8'] = array(array('association_evenements_migration_legacy', '1.4.8'));
	$maj['1.4.9'] = array(array('association_evenements_migration_legacy', '1.4.9'));
	$maj['1.5.0'] = array(array('association_adhesions_migration_legacy', '1.5.0'));
	$maj['1.5.1'] = array(array('association_adhesions_migration_legacy', '1.5.1'));
	$maj['1.5.2'] = array(array('association_evenements_migration_legacy', '1.5.2'));
	$maj['1.5.3'] = array(array('association_adhesions_migration_legacy', '1.5.3'));
	$maj['1.5.4'] = array(array('association_adhesions_migration_legacy', '1.5.4'));
	$maj['1.5.5'] = array(array('association_compta_migration_legacy', '1.5.5'));
	$maj['1.5.6'] = array(array('association_evenements_migration_legacy', '1.5.6'));
	$maj['1.5.7'] = array(array('association_compta_migration_legacy', '1.5.7'));
	$maj['1.5.8'] = array(array('association_evenements_migration_legacy', '1.5.8'));
	$maj['1.5.9'] = array(array('association_adhesions_migration_legacy', '1.5.9'));
	$maj['1.6.0'] = array(
		array('association_adhesions_migration_cotisations_creer'),
	);
	$maj['1.6.1'] = array(
		array('association_completer_migration_cotisations'),
	);
  include_spip('base/upgrade');
  maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

function association_vider_tables($nom_meta_base_version) {
    // Le socle ne supprime que sa propre table. Les tables et champs métier
    // restent sous la responsabilité de leurs plugins respectifs.
    sql_drop_table("spip_association_metas");

    // Supprimer les méta-données du plugin
    effacer_meta($nom_meta_base_version);
    effacer_meta('association_metas');
}

