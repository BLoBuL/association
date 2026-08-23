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
        //MAJ des tables
            array('maj_tables',array('spip_asso_categories',
                                            'spip_asso_dons',
                                            'spip_asso_ventes',
                                            'spip_asso_comptes',
                                            'spip_asso_plan',
                                            'spip_asso_destination',
                                            'spip_asso_destination_op',
                                            'spip_asso_ressources',
                                            'spip_asso_prets',
                                            'spip_asso_ressources',
                                            'spip_asso_activites',
                                            'spip_association_metas',
                                            'spip_evenements')),
        // Recopier le contenue
            array('association_maj_create'),
        //Drop
        #SPIP_ASSO_ACTIVITE
            array('sql_alter', "TABLE spip_asso_activites DROP telephone"),
            array('sql_alter', "TABLE spip_asso_activites DROP adresse"),
            array('sql_alter', "TABLE spip_asso_activites DROP email"),
            array('sql_alter', "TABLE spip_asso_activites DROP DATE_paiement"),
            array('sql_alter', "TABLE spip_asso_activites DROP id_adherent"),
            array('sql_alter', "TABLE spip_asso_activites DROP membres"),
            array('sql_alter', "TABLE spip_asso_activites DROP nom"),
            array('sql_alter', "TABLE spip_asso_activites DROP participant"),
            array('sql_alter', "TABLE spip_asso_activites DROP non_membres"),
            array('sql_alter', "TABLE spip_asso_activites DROP inscrits"),
        //Reorganisation de toute la table
        # SPIP_ASSO_ACTIVITE
            array('sql_alter', "TABLE spip_asso_activites MODIFY id_auteur BIGINT NOT NULL AFTER id_evenement"),
            array('sql_alter', "TABLE spip_asso_activites MODIFY statut TEXT NOT NULL AFTER id_auteur"),
            array('sql_alter', "TABLE spip_asso_activites MODIFY ORGANISATEUR BOOLEAN NOT NULL AFTER statut"),
            array('sql_alter', "TABLE spip_asso_activites MODIFY valider BOOLEAN NOT NULL AFTER ORGANISATEUR"),
            array('sql_alter', "TABLE spip_asso_activites MODIFY nombre_inscrits BIGINT NOT NULL DEFAULT '1' AFTER valider"),
            array('sql_alter', "TABLE spip_asso_activites MODIFY nom_participants TEXT NOT NULL AFTER nombre_inscrits"),
            array('sql_alter', "TABLE spip_asso_activites MODIFY montant BIGINT NOT NULL DEFAULT '0' AFTER nom_participants"),
            array('sql_alter', "TABLE spip_asso_activites MODIFY montant_payer BOOLEAN NOT NULL AFTER montant"),
            array('sql_alter', "TABLE spip_asso_activites MODIFY commentaire TEXT NOT NULL AFTER montant_payer"),
            array('sql_alter', "TABLE spip_asso_activites MODIFY date TIMESTAMP NOT NULL AFTER commentaire"),
    );
#V1.1.1
    $maj['1.1.1'][] = array('maj_tables',array('spip_evenements')); //spip_evenement -> spip_evenements
#V1.1.2
    //cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.2']);
    $maj['1.1.2'][] = array('association_maj_112');
#V1.1.3
    $maj['1.1.3'][] = array('association_import_champs_extras');
#V1.1.4
    //cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.4']);
    $maj['1.1.4'][] = array('association_import_champs_extras');
#1.1.5
    $maj['1.1.5'] = array(
            array('association_import_champs_extras'),
            array('sql_alter', "TABLE spip_asso_activites DROP ORGANISATEUR"),
    );
#1.1.6
    $maj['1.1.6'][] = array('association_import_champs_extras');
#1.1.7
    $maj['1.1.7'][] = array('maj_tables',array('spip_asso_categories',
                                                'spip_asso_comptes'));
#1.1.8
    //cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.8']);
    $maj['1.1.8'][] = array('maj_tables',array('spip_asso_categories',
                                                'spip_asso_comptes'));
#1.1.9
    $maj['1.1.9'] = array(
            array('association_import_champs_extras'),
            array('sql_alter', "TABLE spip_asso_comptes DROP type_paiement"),
            array('sql_alter', "TABLE spip_asso_comptes DROP type_cotisation"),
            array('maj_tables',array('spip_asso_comptes'))
    );
#1.1.10
    $maj['1.1.10'][] = array('maj_tables',array('spip_asso_comptes'));

#1.1.13 ( PB d'insertion..)
//cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.13']);
    $maj['1.1.13'] = array(	array('association_import_champs_extras'),
        array('sql_alter', "TABLE spip_evenements ADD COLUMN date_ouverture datetime DEFAULT '0000-00-00 00:00:00' NOT NULL AFTER descriptif_securise"),
    );
#1.1.15
//cextras_api_upgrade(association_declarer_champs_extras(), $maj['1.1.15']);
$maj['1.1.15'] = array(array('sql_alter', "TABLE spip_evenements DROP date_ouverture"),
    array('sql_alter', "TABLE spip_evenements ADD COLUMN ouverture_differe tinytext DEFAULT '0' NOT NULL AFTER descriptif_securise"),
);
#1.2.0
$maj['1.2.0'] = array(		array('sql_alter', "TABLE spip_asso_categories RENAME TO spip_asso_categories_adherents"),
                            array('sql_alter', "TABLE spip_asso_categories_adherents DROP libelle"),
                            array('sql_alter', "TABLE spip_asso_categories_adherents DROP duree"),
                            array('sql_alter', "TABLE spip_asso_categories_activites DROP cotisation"),
                            array('sql_alter', "TABLE spip_evenements ADD COLUMN gratuit BOOLEAN NOT NULL AFTER validation"),
                            array('sql_alter', "TABLE spip_evenements DROP montant"),
                            array('sql_alter', "TABLE spip_asso_activites DROP montant"),
                            array('sql_alter', "TABLE spip_asso_activites DROP montant_payer"),
                            array('maj_tables',array(	'spip_asso_categories_adherents',
                                                        'spip_asso_categories_activites',
                                                        'spip_asso_activites',
                                                        'spip_asso_categories_activites_liens')),
);
#1.2.1
$maj['1.2.1'] = array(
                            array('sql_alter', "TABLE spip_asso_categories_activites DROP gratuit"),
                            array('sql_alter', "TABLE spip_evenements ADD COLUMN payant BOOLEAN NOT NULL AFTER validation"),
);
#1.2.2
$maj['1.2.2'] = array(
    array('sql_alter', "TABLE spip_asso_categories_adherents DROP deleted"),
    array('maj_tables',array(	'spip_asso_categories_adherents'))
);
#1.2.3
$maj['1.2.3'] = array(
    array('maj_tables',array(	'spip_asso_categories_activites'))
);
#1.2.3
$maj['1.2.4'] = array(
  array('maj_tables',array(	'spip_asso_comptes')),
  array('association_maj_124'),
  array('sql_alter', "TABLE spip_asso_comptes DROP id_journal"),
);
#1.2.5
$maj['1.2.5'] = array(
/*     array('sql_alter', "TABLE spip_asso_activites ADD COLUMN notify_the_members BOOLEAN DEFAULT '1' NOT NULL AFTER maj"), */
    array('sql_alter', "TABLE spip_evenements ADD COLUMN mute BOOLEAN DEFAULT '0' NOT NULL")
);
#1.2.6
$maj['1.2.6'] = array(
    array('sql_alter', "TABLE spip_asso_categories_activites DROP gratuit"),
    array('sql_alter', "TABLE spip_asso_activites DROP notify_the_members"),
    array('sql_alter', "TABLE spip_evenements ADD COLUMN show_list_members BOOLEAN DEFAULT '0' NOT NULL AFTER maj"),
    array('sql_alter', "TABLE spip_asso_activites ADD COLUMN visible_in_list_members BOOLEAN DEFAULT '1' NOT NULL AFTER maj"),
);
#1.2.7
$maj['1.2.7'] = array(
    //array('sql_alter', "TABLE spip_evenements ADD COLUMN mute BOOLEAN DEFAULT '0' NOT NULL")
);
#1.2.8
$maj['1.2.8'] = array(
    array('sql_alter', "TABLE spip_asso_activites ADD COLUMN notify_the_members BOOLEAN DEFAULT '1' NOT NULL")
);
#1.2.9
$maj['1.2.9'] = array(
    array('sql_alter', "TABLE spip_mailshots ADD COLUMN id_auteur BIGINT(21) AFTER id_mailshot"),
    array('sql_alter', "TABLE spip_mailshots ADD COLUMN id_evenement BIGINT(21) AFTER id")
);
$maj['1.3.0'] = array(
    array('sql_alter', "TABLE spip_evenements ADD COLUMN mode_paiement varchar(124) NOT NULL"),
    array('sql_alter', "TABLE spip_evenements ADD COLUMN type_inscrits_evenement varchar(30) NOT NULL DEFAULT 'strict'"),
    array('sql_alter', "TABLE spip_evenements ADD COLUMN fermeture_inscription varchar(30) NOT NULL DEFAULT 'last_minute'")
);
 $maj['1.3.1'] = array(
    array('sql_alter', "TABLE spip_evenements ADD COLUMN validation_sur_paiement char(3) NOT NULL DEFAULT 'non'"),
    array('sql_alter', "TABLE spip_evenements CHANGE show_list_members afficher_liste_inscrits BOOLEAN")
);
  $maj['1.3.2'] = array(
      array('sql_alter', "TABLE spip_asso_activites DROP en_attente"),
      array('sql_alter', "TABLE spip_asso_activites DROP valider"),
      array('sql_alter', "TABLE spip_asso_activites ADD COLUMN nom_inscrit varchar(255) AFTER id_auteur"),
      array('sql_alter', "TABLE spip_asso_activites ADD COLUMN prenom_inscrit varchar(255) AFTER id_auteur"),
      array('sql_alter', "TABLE spip_asso_activites ADD COLUMN email_inscrit varchar(255) AFTER id_auteur")
);
  $maj['1.3.3'] = array(
    array('association_maj_spip_asso_activites'),
    array('sql_alter', "TABLE spip_asso_categories_activites ADD COLUMN type_inscrit varchar(255) NOT NULL DEFAULT 'indifferent' AFTER statut"),
    array('sql_alter', "TABLE spip_asso_categories_adherents ADD COLUMN type_adherent varchar(255) NOT NULL DEFAULT 'adherent' AFTER statut"),
);
  $maj['1.3.4'] = array(
      array('sql_alter', "TABLE spip_asso_activites MODIFY date DATETIME NULL AFTER commentaire"),
      array('sql_alter', "TABLE spip_asso_activites ADD COLUMN log TEXT AFTER commentaire"),
      array('sql_alter', "TABLE spip_asso_activites ADD COLUMN tel_inscrit varchar(255) AFTER email_inscrit")
);
   $maj['1.3.5'] = array(
    array('sql_alter', "TABLE spip_evenements ADD COLUMN validation_attente_automatique char(3) NOT NULL DEFAULT 'non'"),
);
    $maj['1.3.6'] = array(
    array('sql_alter', "TABLE spip_evenements ADD COLUMN info_supplementaire varchar(255) NULL"),
    array('sql_alter', "TABLE spip_evenements ADD COLUMN responsables varchar(255) NULL"),
);
    $maj['1.3.7'] = array(
    array('sql_alter', "TABLE spip_evenements ADD COLUMN ouverture_differe_date datetime NULL AFTER ouverture_differe"),
);
    $maj['1.3.8'] = array(
    array('sql_alter', "TABLE spip_asso_activites ADD COLUMN journal varchar(255) NULL"),
    array('sql_alter', "TABLE spip_asso_activites ADD COLUMN annotation varchar(255) NULL"),
);
    $maj['1.3.9'] = array(
    array('sql_alter', "TABLE spip_evenements ADD COLUMN presentiel varchar(12) NULL DEFAULT 'oui'"),
    array('sql_alter', "TABLE spip_evenements ADD COLUMN lien varchar(255) AFTER lieu"),
    array('sql_alter', "TABLE spip_evenements ADD COLUMN reseau_fiafe varchar(3) NULL DEFAULT 'non'"),
);
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
		array('maj_tables', array('spip_asso_cotisations')),
		array('association_migrer_cotisations_depuis_comptes'),
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

