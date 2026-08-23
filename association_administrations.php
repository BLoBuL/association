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
    cextras_api_upgrade(association_declarer_champs_extras(array()), $maj['create']);
    // Les champs auteurs historiques ne sont pas encore tous portes par la
    // declaration PHP ci-dessus. Leur description YAML versionnee reste la
    // source de compatibilite et passe par l'importeur Champs Extras officiel.
    $maj['create'][] = array('association_import_champs_extras');
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
    $maj['1.4.0'] = array(
    array('sql_alter', "TABLE spip_asso_activites ADD COLUMN association varchar(255) NULL AFTER nom_inscrit"),
);
    $maj['1.4.1'] = array(
    array('sql_alter', "TABLE spip_evenements ADD COLUMN condition_inscription varchar(3) NULL DEFAULT 'non'"),
    array('sql_alter', "TABLE spip_evenements ADD COLUMN message_condition_inscription TEXT"),
);
    $maj['1.4.2'] = array(
    array('sql_alter', "TABLE spip_asso_categories_adherents ALTER type_adherent SET DEFAULT 'adherent'"),
    array('association_maj_142'),
);
    $maj['1.4.3'] = array(
    array('sql_alter', "TABLE spip_asso_activites ADD COLUMN tel_inscrit varchar(255) AFTER email_inscrit")
);
    $maj['1.4.4'] = array(
    array('sql_alter', "TABLE spip_asso_activites ADD COLUMN participants_json TEXT AFTER nom_participants"),
);
    $maj['1.4.5'] = array(
    array('sql_alter', "TABLE spip_evenements CHANGE message_condition_inscription message_condition_inscription TEXT")
);
    $maj['1.4.7'] = array(
    array('sql_alter', "TABLE spip_asso_comptes ADD COLUMN objet varchar(30) DEFAULT 'cotisation'"),
    array('sql_alter', "TABLE spip_asso_comptes ADD COLUMN id_objet varchar(21) DEFAULT '0'"),
);
    $maj['1.4.8'] = array(
        array('sql_alter', "TABLE spip_evenements CHANGE info_supplementaire info_supplementaire TEXT"),
        array('sql_alter', "TABLE spip_asso_activites CHANGE journal journal TEXT")
    );
    $maj['1.4.9'] = array(
        array('sql_alter', "TABLE spip_asso_activites CHANGE annotation annotation TEXT")
    );
    $maj['1.5.0'] = array(
        array('sql_alter', "TABLE spip_asso_categories_adherents ADD COLUMN date_debut_validite VARCHAR(5) NULL DEFAULT NULL"),
        array('sql_alter', "TABLE spip_asso_categories_adherents ADD COLUMN date_fin_validite VARCHAR(5) NULL DEFAULT NULL")
    );
    $maj['1.5.1'] = array(
        array('sql_alter', "TABLE spip_asso_categories_adherents ADD COLUMN validation VARCHAR(32) NULL DEFAULT 'auto'"),
        array('sql_alter', "TABLE spip_asso_categories_adherents ADD COLUMN document_justificatif VARCHAR(3) NULL DEFAULT 'non'"),
    );
    $maj['1.5.2'] = array(
        array('sql_alter', "TABLE spip_asso_activites ADD COLUMN ip_inscrit VARCHAR(45) NULL AFTER tel_inscrit")
    );
    $maj['1.5.3'] = array(
        array('sql_alter', "TABLE spip_asso_categories_adherents ADD COLUMN nombre_enfants VARCHAR(2) NULL DEFAULT ''"),
    );
    $maj['1.5.4'] = array(
        array('sql_alter', "TABLE spip_asso_categories_adherents ADD COLUMN mode_paiement VARCHAR(32) NULL DEFAULT ''"),
        array('sql_alter', "TABLE spip_asso_categories_adherents ADD COLUMN eligibilite VARCHAR(32) NULL DEFAULT ''"),
    );
    $maj['1.5.5'] = array(
        array('sql_alter', "TABLE spip_asso_comptes CHANGE date date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"),
    );
    $maj['1.5.6'] = array(
        array('sql_alter', "TABLE spip_evenements ADD COLUMN invites VARCHAR(3) NOT NULL DEFAULT 'non'"),
        array('sql_alter', "TABLE spip_evenements ADD COLUMN limite_invites INT(10) UNSIGNED NOT NULL DEFAULT '5'"),
        array('sql_alter', "TABLE spip_asso_activites ADD COLUMN nb_invite INT(10) UNSIGNED NOT NULL DEFAULT '0'"),
    );
    $maj['1.5.7'] = array(
        array('sql_alter', "TABLE spip_asso_comptes MODIFY id_transaction BIGINT NOT NULL DEFAULT '0'"),
    );
    $maj['1.5.8'] = array(
        array('sql_alter', "TABLE spip_evenements ADD COLUMN fermeture_inscription_date DATETIME NULL DEFAULT NULL AFTER fermeture_inscription"),
    );
    $maj['1.5.9'] = array(
        array('sql_alter', "TABLE spip_asso_categories_adherents ADD COLUMN devise VARCHAR(3) NOT NULL DEFAULT '' AFTER cotisation"),
    );
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

// MAJ CREATE
function association_maj_create() {
    // Renomage des colonnes
    sql_update('spip_asso_activites', array(
        'id_auteur'=>'nom',
        'nombre_inscrits'=>'inscrits',
        'nom_participants'=>'non_membres',
        'responsable'=>'participant',
    ));
    // Import de champs extras
    association_import_champs_extras(); // En cas de nécessité sur les mise à jour. Ne sera pas nécessaire.
}
// MAJ 112
function association_maj_112() {
    // Valeur par defaut dans spip_evenements
    $sql_evenements = sql_select('*', 'spip_evenements', "validation='' OR accompagnants='' OR file_attentes=''");
    while($maj_evenement = sql_fetch($sql_evenements)){
        $id_evenement = $maj_evenement['id_evenement'];
        $validation = (empty($maj_evenement['validation']))? "non" : $maj_evenement['validation'];
        $accompagnants = (empty($maj_evenement['accompagnants']))? "non" : $maj_evenement['accompagnants'];
        $file_attentes = (empty($maj_evenement['file_attentes']))? "non" : $maj_evenement['file_attentes'];
        sql_updateq('spip_evenements', array(
            'validation' => $validation,
            'accompagnants' => $accompagnants,
            'file_attentes' => $file_attentes),
            "id_evenement=$id_evenement");
    }
}
// MAJ 124
function association_maj_124() {
    sql_update('spip_asso_comptes', array(
        'id_auteur'=>'id_journal',
    ));
}
function association_maj_spip_asso_activites(){
    $query_asso_activites = sql_select("id_activite,id_auteur", "spip_asso_activites", "email_inscrit IS NULL");
    while($asso_activite = sql_fetch($query_asso_activites)){
        $id_auteur =$asso_activite['id_auteur'];
        $id_activite = $asso_activite['id_activite'];
        $query_auteur = sql_fetsel("prenom,nom_famille,email","spip_auteurs","id_auteur= $id_auteur");
        sql_updateq('spip_asso_activites', array(
            'prenom_inscrit' => $query_auteur['prenom'],
            'nom_inscrit' => $query_auteur['nom_famille'],
            'email_inscrit' => $query_auteur['email']),
            "id_activite=$id_activite");
    }
}
function association_maj_142(){

    sql_updateq('spip_asso_categories_adherents', array(
        'type_adherent' => 'adherent'),
        "type_adherent IS NULL");

    sql_delete("spip_asso_categories_adherents", "statut='supprime'");


}

/**
 * Copie idempotente des données métier de cotisation hors du journal comptable.
 * Les anciennes colonnes restent temporairement lisibles pendant la transition 4.0.
 */
function association_migrer_cotisations_depuis_comptes() {
	$champ_objet = sql_showtable('spip_asso_comptes', true)['field']['objet'] ?? false;
	$where = $champ_objet
		? array("objet='cotisation' OR reinscription<>'' OR statut_cotisation<>''")
		: array("reinscription<>'' OR statut_cotisation<>''");
	foreach (sql_allfetsel('*', 'spip_asso_comptes', $where, '', 'id_compte') as $compte) {
		$id_compte = (int) $compte['id_compte'];
		if (!$id_compte) {
			continue;
		}

		$id_cotisation = (int) sql_getfetsel('id_cotisation', 'spip_asso_cotisations', 'id_compte=' . $id_compte);
		if (!$id_cotisation) {
			$id_cotisation = (int) sql_insertq('spip_asso_cotisations', array(
				'id_compte' => $id_compte,
				'id_auteur' => (int) ($compte['id_auteur'] ?? 0),
				'id_categorie' => (int) ($compte['id_categorie'] ?? 0),
				'id_transaction' => (int) ($compte['id_transaction'] ?? 0),
				'inscription' => (string) ($compte['reinscription'] ?? ''),
				'statut' => (string) ($compte['statut_cotisation'] ?? 'attente'),
				'date_creation' => (string) ($compte['date'] ?? date('Y-m-d H:i:s')),
				// Les dates de validité n'existaient pas par cotisation dans le
				// schéma historique. Elles restent donc NULL plutôt que d'être
				// déduites, à tort, de la validité actuelle de l'auteur.
				'montant' => (float) ($compte['recette'] ?? 0),
				'devise' => association_cotisation_devise_historique($compte),
			));
		}

		association_cotisation_lier_compte($compte, $id_cotisation);
	}
}

/**
 * Retrouve la devise historique la plus fiable : transaction, catégorie, puis
 * devise par défaut du site. Les installations sans l'une de ces colonnes
 * restent compatibles avec les anciens schémas.
 */
function association_cotisation_devise_historique($compte) {
	$devise = '';
	$id_transaction = (int) ($compte['id_transaction'] ?? 0);
	$transaction = sql_showtable('spip_transactions', true);
	if ($id_transaction && isset($transaction['field']['devise'])) {
		$devise = (string) sql_getfetsel('devise', 'spip_transactions', 'id_transaction=' . $id_transaction);
	}

	$id_categorie = (int) ($compte['id_categorie'] ?? 0);
	$categorie = sql_showtable('spip_asso_categories_adherents', true);
	if ($devise === '' && $id_categorie && isset($categorie['field']['devise'])) {
		$devise = (string) sql_getfetsel('devise', 'spip_asso_categories_adherents', 'id_categorie=' . $id_categorie);
	}

	$devise = strtoupper(trim($devise));
	if (preg_match('/^[A-Z]{3}$/', $devise)) {
		return $devise;
	}

	include_spip('inc/cotisations_devises');
	return function_exists('association_cotisation_devise_defaut')
		? association_cotisation_devise_defaut()
		: '';
}

/**
 * Rattache l'écriture comptable à l'identifiant de la nouvelle cotisation.
 */
function association_cotisation_lier_compte($compte, $id_cotisation) {
	$id_compte = (int) ($compte['id_compte'] ?? 0);
	if (!$id_compte || !$id_cotisation || !isset($compte['objet']) || $compte['objet'] !== 'cotisation') {
		return;
	}
	sql_updateq('spip_asso_comptes', array('id_objet' => (int) $id_cotisation), 'id_compte=' . $id_compte);
}

/**
 * Complète sans écraser les données métier déjà modifiées après la séparation.
 */
function association_completer_migration_cotisations() {
	association_migrer_cotisations_depuis_comptes();
	foreach (sql_allfetsel('*', 'spip_asso_cotisations', "devise='' OR devise IS NULL") as $cotisation) {
		$id_compte = (int) ($cotisation['id_compte'] ?? 0);
		$compte = $id_compte ? sql_fetsel('*', 'spip_asso_comptes', 'id_compte=' . $id_compte) : array();
		if (!$compte) {
			continue;
		}
		sql_updateq(
			'spip_asso_cotisations',
			array('devise' => association_cotisation_devise_historique($compte)),
			'id_cotisation=' . (int) $cotisation['id_cotisation']
		);
		association_cotisation_lier_compte($compte, (int) $cotisation['id_cotisation']);
	}
}


/* --------------------------------------------------------------------------- */
/*
 * FONCTION NECESSAIRE AUX MISE A JOUR
 */
## IMPORT DE FICHIER POUR CHAMPS EXTRAS
function association_import_champs_extras(){
    // Repris de 'importer_champs_extras.php' du plugin 'champs extras' ##
    // Importe dans champs extrat les champs nécessaire au fonctionnement du plugin
    $res = array('editable' => true);
    $fichier = find_in_path('yaml/association.yaml');
	// Les champs sont désormais déclarés en PHP par
	// association_declarer_champs_extras(). Le YAML n'existe plus sur une
	// installation neuve : les anciennes étapes d'upgrade doivent alors rester
	// idempotentes et ne surtout pas appeler lire_fichier(false), fatal en PHP 8.
	if (!$fichier || !is_file($fichier)) {
		$res['message_ok'] = 'Aucun ancien fichier YAML à importer.';
		return $res;
	}
    lire_fichier($fichier, $yaml);
    if (!$yaml) {
        $res['message_erreur'] = "Lecture du fichier en erreur.";
        return $res;
    }
    include_spip('inc/yaml');
    $description = yaml_decode($yaml, true);
    if (!$description OR !is_array($description)) {
        $res['message_erreur'] = "Pas de champ trouvé dans le fichier.";
        return $res;
    }
    include_spip('formulaires/importer_champs_extras');
    // true si on fusionne les champs présents dans la sauvegarde et aussi présents sur le site. False pour les ignorer.
    if (iextras_importer_description($description, $message, false)) {
        $res['message_ok'] = $message;
    } else {
        $res['message_erreur'] = $message;
    }
    return $res;
}

function association_vider_tables($nom_meta_base_version) {
    // Suppression des tables principales du plugin
    sql_drop_table("spip_asso_categories_adherents");
    sql_drop_table("spip_asso_categories_activites");
    sql_drop_table("spip_asso_dons");
    sql_drop_table("spip_asso_ventes");
    sql_drop_table("spip_asso_comptes");
	sql_drop_table("spip_asso_cotisations");
    sql_drop_table("spip_asso_plan");
    sql_drop_table("spip_asso_destination");
    sql_drop_table("spip_asso_destination_op");
    sql_drop_table("spip_asso_ressources");
    sql_drop_table("spip_asso_prets");
    sql_drop_table("spip_asso_activites");

    // Suppression des tables auxiliaires
    sql_drop_table("spip_association_metas");
    sql_drop_table("spip_asso_categories_activites_liens");

    // Suppression des champs extras ajoutés à d'autres tables
    include_spip('inc/cextras');
    cextras_api_vider_tables(association_declarer_champs_extras(array()));

    // Supprimer les méta-données du plugin
    effacer_meta($nom_meta_base_version);
    effacer_meta('association_metas');
}

