<?php

$racine = dirname(__DIR__);
$erreurs = array();
$verifier = function ($condition, $message) use (&$erreurs) {
	if (!$condition) $erreurs[] = $message;
};

$paquet = file_get_contents($racine . '/paquet.xml');
$schema = file_get_contents($racine . '/base/association.php');
$verifier(strpos($paquet, '<necessite nom="inscription4"') !== false, 'Inscription 4 doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_adhesions"') !== false, 'Le module Adhesions doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_evenements"') !== false, 'Le module Evenements doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_compta"') !== false, 'Le module Comptabilite doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_dons"') !== false, 'Le module Dons doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_ventes"') !== false, 'Le module Ventes doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_prets"') !== false, 'Le module Prets doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_paiements"') !== false, 'Le module Paiements doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_groupes"') !== false, 'Le module Groupes doit etre une dependance explicite.');
$verifier(strpos($paquet, '<necessite nom="association_communication"') !== false, 'Le module Communication doit etre une dependance explicite.');
$communication_paquet = file_get_contents($racine . '/plugins/association-communication/paquet.xml');
$paiements_paquet = file_get_contents($racine . '/plugins/association-paiements/paquet.xml');
$autorisation_compta = file_get_contents($racine . '/plugins/association-compta/association_compta_autoriser.php');
$verifier(strpos($communication_paquet, 'nom="association_adhesions"') === false, 'Communication ne doit pas creer de cycle vers Adhesions.');
$verifier(strpos($paiements_paquet, 'nom="association_adhesions"') === false, 'Paiements ne doit pas creer de cycle vers Adhesions.');
$verifier(strpos($paiements_paquet, 'nom="association_evenements"') === false, 'Paiements ne doit pas creer de cycle vers Evenements.');
$verifier(
	!preg_match('/function autoriser_(?:asso_modifier|modifier_asso(?:_dist)?)\s*\(/', $autorisation_compta),
	'L alias generique historique modifier/asso doit etre supprime au profit des objets metier.'
);
$adhesions_paquet = file_get_contents($racine . '/plugins/association-adhesions/paquet.xml');
$evenements_paquet = file_get_contents($racine . '/plugins/association-evenements/paquet.xml');
$compta_paquet = file_get_contents($racine . '/plugins/association-compta/paquet.xml');
$compta_fonctions = file_get_contents($racine . '/plugins/association-compta/inc/fonctions/comptes.php');
$evenements_pipelines = file_get_contents($racine . '/plugins/association-evenements/association_evenements_pipelines.php');
$verifier(
	strpos($compta_paquet, 'nom="association_evenements_stats_compta"') !== false
		&& strpos($evenements_paquet, 'nom="association_evenements_stats_compta"') !== false
		&& strpos($evenements_pipelines, 'function association_evenements_association_evenements_stats_compta(') !== false,
	'Les statistiques comptables Evenements doivent etre fournies par le plugin metier.'
);
$verifier(
	strpos($compta_fonctions, "sql_quote('activite')") === false
		&& strpos($compta_fonctions, "sql_quote('evenement')") === false
		&& strpos($compta_fonctions, "sql_quote('activite|%')") === false,
	'Comptabilite ne doit plus connaitre les criteres propres aux Evenements.'
);
$verifier(strpos($adhesions_paquet, '<chemin path="squelettes"') === false, 'Adhesions doit exposer sa racine pour rendre prive/ chargeable.');
$verifier(strpos($evenements_paquet, '<chemin path="squelettes"') === false, 'Evenements doit exposer sa racine pour rendre prive/ chargeable.');
$verifier(strpos($paquet, '<necessite nom="bank"') === false, 'Bank doit etre porte par le module Paiements.');
$verifier(strpos($paquet, '<necessite nom="mailsubscribers"') === false, 'Mailsubscribers doit etre porte par le module Communication.');
$verifier(strpos($paquet, '<necessite nom="inscription3"') === false, 'Inscription 3 ne doit plus etre une dependance.');
$verifier(
	strpos($paquet, 'lib/fontawesome-6.7.2/css/fontawesome.css') !== false,
	'Le socle doit charger Font Awesome sans dependre de blobul-CORE.'
);
foreach (array('fontawesome.css', 'regular.min.css', 'brands.min.css', 'solid.min.css') as $css_fa) {
	$verifier(is_file($racine . '/lib/fontawesome-6.7.2/css/' . $css_fa), 'Feuille Font Awesome manquante : ' . $css_fa . '.');
}
foreach (array('fa-brands-400.woff2', 'fa-regular-400.woff2', 'fa-solid-900.woff2', 'fa-v4compatibility.woff2') as $police_fa) {
	$verifier(is_file($racine . '/lib/fontawesome-6.7.2/webfonts/' . $police_fa), 'Police Font Awesome manquante : ' . $police_fa . '.');
}
$verifier(strpos($schema, "spip_asso_cotisations") === false, 'Le socle ne doit plus posseder la table metier des cotisations.');
$verifier(strpos($schema, "spip_asso_categories_adherents") === false, 'Le socle ne doit plus posseder les categories d adhesion.');
$verifier(strpos($schema, "spip_asso_activites") === false, 'Le socle ne doit plus posseder la table des inscriptions aux evenements.');
$verifier(strpos($schema, "spip_asso_categories_activites") === false, 'Le socle ne doit plus posseder les tarifs des evenements.');
$verifier(strpos($schema, "spip_asso_comptes") === false, 'Le socle ne doit plus posseder le journal comptable.');
$verifier(strpos($schema, "spip_asso_plan") === false, 'Le socle ne doit plus posseder le plan comptable.');
$verifier(strpos($schema, "spip_asso_dons") === false, 'Le socle ne doit plus posseder les dons.');
$verifier(strpos($schema, "spip_asso_ventes") === false, 'Le socle ne doit plus posseder les ventes.');
$verifier(strpos($schema, "spip_asso_prets") === false, 'Le socle ne doit plus posseder les prets.');
$verifier(
	!is_file($racine . '/formulaires/update/options.html'),
	'L ancien fragment de configuration comptable update/options doit rester supprime.'
);
foreach (array(
	'fonds/inscription2_association.html',
	'fonds/visuel_cextras.html',
	'balise/configurer_metas.php',
) as $reliquat_inscription) {
	$verifier(
		!is_file($racine . '/' . $reliquat_inscription),
		'Le reliquat Inscription2 ou Inscription3 doit rester supprime : ' . $reliquat_inscription
	);
}
$verifier(
	!is_file($racine . '/balise/meta.php')
		&& strpos(file_get_contents($racine . '/association_options.php'), "include_spip('balise/meta')") === false,
	'Le socle ne doit plus surcharger la balise META native de SPIP.'
);
$verifier(
	!is_file($racine . '/balise/onglets_association.php'),
	'Le socle ne doit plus compiler une balise personnalisee pour inclure ses onglets.'
);
$verifier(
	!is_file($racine . '/saisies/formulaires.html')
		&& !is_file($racine . '/saisies/formulaires.yaml')
		&& is_file($racine . '/plugins/association-communication/saisies/formulaires.html')
		&& is_file($racine . '/plugins/association-communication/saisies/formulaires.yaml'),
	'La saisie de formulaires doit appartenir au plugin Communication.'
);
$verifier(
	!is_file($racine . '/formulaires/inc-formidable-boutons.html')
		&& is_file($racine . '/plugins/association-communication/formulaires/inc-formidable-boutons.html'),
	'Le composant de boutons Formidable doit appartenir au plugin Communication.'
);
$communication_pipelines_sql = file_get_contents($racine . '/plugins/association-communication/association_communication_pipelines.php');
$verifier(
	strpos($schema, 'spip_mailsubscribers') === false
		&& strpos($communication_pipelines_sql, 'function association_communication_declarer_tables_objets_sql(') !== false
		&& strpos($communication_pipelines_sql, 'spip_mailsubscribers') !== false
		&& strpos($communication_paquet, 'nom="declarer_tables_objets_sql"') !== false,
	'La personnalisation SQL de Mailsubscribers doit appartenir à Communication.'
);
$verifier(
	strpos($paquet, 'nom="declarer_tables_principales"') === false
		&& strpos($paquet, 'nom="declarer_tables_objets_sql"') === false
		&& strpos($schema, 'function association_declarer_tables_principales(') === false,
	'Le socle ne doit pas déclarer de pipelines SQL sans traitement.'
);
$verifier(
	!is_file($racine . '/inc/exporter_csv.php')
		&& !is_file($racine . '/inc/csv_generer.html')
		&& is_file($racine . '/plugins/association-evenements/inc/exporter_csv.php')
		&& is_file($racine . '/plugins/association-evenements/inc/csv_generer.html'),
	'Les utilitaires CSV des participants doivent appartenir à Événements.'
);
$verifier(
	is_file($racine . '/plugins/association-adhesions/inc/association_adhesions_maintenance.php')
		&& strpos(file_get_contents($racine . '/genie/association_maintenance_bdd.php'), 'function asso_recuperer_auteurs_inactifs(') === false,
	'La maintenance des auteurs doit appartenir à Adhésions.'
);
$dons_lang = file_get_contents($racine . '/plugins/association-dons/lang/association_dons_fr.php');
$dons_sources = '';
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/plugins/association-dons')) as $dons_source) {
	if ($dons_source->isFile() && preg_match('/\.(?:php|html)$/', $dons_source->getFilename()) && strpos($dons_source->getPathname(), DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR) === false) {
		$dons_sources .= file_get_contents($dons_source->getPathname());
	}
}
$verifier(
	strpos($dons_sources, 'association:') === false
		&& strpos($dons_sources, 'association_dons:') !== false
		&& strpos($dons_lang, "'ajouter_un_don'") !== false,
	'Le plugin Dons doit utiliser son propre domaine de langue.'
);
$ventes_lang = file_get_contents($racine . '/plugins/association-ventes/lang/association_ventes_fr.php');
$ventes_sources = '';
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/plugins/association-ventes')) as $ventes_source) {
	if ($ventes_source->isFile() && preg_match('/\.(?:php|html)$/', $ventes_source->getFilename()) && strpos($ventes_source->getPathname(), DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR) === false) {
		$ventes_sources .= file_get_contents($ventes_source->getPathname());
	}
}
$verifier(
	strpos($ventes_sources, 'association:') === false
		&& strpos($ventes_sources, 'association_ventes:') !== false
		&& strpos($ventes_lang, "'ajouter_une_vente'") !== false,
	'Le plugin Ventes doit utiliser son propre domaine de langue.'
);
$groupes_lang = file_get_contents($racine . '/plugins/association-groupes/lang/association_groupes_fr.php');
$groupes_sources = '';
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/plugins/association-groupes')) as $groupes_source) {
	if ($groupes_source->isFile() && preg_match('/\.(?:php|html)$/', $groupes_source->getFilename()) && strpos($groupes_source->getPathname(), DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR) === false) {
		$groupes_sources .= file_get_contents($groupes_source->getPathname());
	}
}
$verifier(
	strpos($groupes_sources, 'association:') === false
		&& strpos($groupes_sources, 'association_groupes:') !== false
		&& strpos($groupes_lang, "'titre_page_benevoles'") !== false,
	'Le plugin Groupes doit utiliser son propre domaine de langue.'
);
$paiements_lang = file_get_contents($racine . '/plugins/association-paiements/lang/association_paiements_fr.php');
$paiements_sources = '';
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/plugins/association-paiements')) as $paiements_source) {
	if ($paiements_source->isFile() && preg_match('/\.(?:php|html)$/', $paiements_source->getFilename()) && strpos($paiements_source->getPathname(), DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR) === false) {
		$paiements_sources .= file_get_contents($paiements_source->getPathname());
	}
}
$verifier(
	strpos($paiements_sources, 'association:') === false
		&& strpos($paiements_sources, 'association_paiements:') !== false
		&& strpos($paiements_lang, "'label_remboursement_notifier_inscrit'") !== false,
	'Le plugin Paiements doit utiliser son propre domaine de langue.'
);
$communication_lang = file_get_contents($racine . '/plugins/association-communication/lang/association_communication_fr.php');
$communication_sources_lang = '';
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/plugins/association-communication')) as $communication_source_lang) {
	if ($communication_source_lang->isFile() && preg_match('/\.(?:php|html)$/', $communication_source_lang->getFilename()) && strpos($communication_source_lang->getPathname(), DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR) === false) {
		$communication_sources_lang .= file_get_contents($communication_source_lang->getPathname());
	}
}
$verifier(
	!preg_match('/(?:<:|[\'\"]|\{)association:/', $communication_sources_lang)
		&& strpos($communication_sources_lang, 'association_communication:') !== false
		&& strpos($communication_lang, "'notifications_metiers_titre'") !== false,
	'Le plugin Communication doit utiliser son propre domaine de langue Association.'
);
$prets_lang = file_get_contents($racine . '/plugins/association-prets/lang/association_prets_fr.php');
$prets_sources_lang = '';
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/plugins/association-prets')) as $prets_source_lang) {
	if ($prets_source_lang->isFile() && preg_match('/\.(?:php|html)$/', $prets_source_lang->getFilename()) && strpos($prets_source_lang->getPathname(), DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR) === false) {
		$prets_sources_lang .= file_get_contents($prets_source_lang->getPathname());
	}
}
$verifier(
	!preg_match('/(?:<:|[\'\"]|\{)association:/', $prets_sources_lang)
		&& strpos($prets_sources_lang, 'association_prets:') !== false
		&& strpos($prets_lang, "'ressources_titre_liste_ressources'") !== false,
	'Le plugin Prêts doit utiliser son propre domaine de langue.'
);
$compta_lang = file_get_contents($racine . '/plugins/association-compta/lang/association_compta_fr.php');
$compta_sources_lang = '';
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/plugins/association-compta')) as $compta_source_lang) {
	if ($compta_source_lang->isFile() && preg_match('/\.(?:php|html)$/', $compta_source_lang->getFilename()) && strpos($compta_source_lang->getPathname(), DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR) === false) {
		$compta_sources_lang .= file_get_contents($compta_source_lang->getPathname());
	}
}
$verifier(
	!preg_match('/(?:<:|[\'\"]|\{)association:/', $compta_sources_lang)
		&& strpos($compta_sources_lang, 'association_compta:') !== false
		&& strpos($compta_lang, "'informations_comptables'") !== false,
	'Le plugin Comptabilité doit utiliser son propre domaine de langue.'
);
$paiement_modeles_front = array(
	'payer_acte.html',
	'payer_acte_adhesion.html',
	'payer_acte_formidable.html',
	'payer_acte_participation.html',
);
foreach ($paiement_modeles_front as $modele_paiement_front) {
	$verifier(
		is_file($racine . '/plugins/association-paiements/modeles/' . $modele_paiement_front),
		'Paiements doit fournir le modèle autonome ' . $modele_paiement_front . '.'
	);
}
$albums_evenements_front = '';
foreach (array('album_photos_evenement.html', 'album_photos_evenement_locked.html') as $album_evenement_front) {
	$chemin_album_evenement = $racine . '/plugins/association-evenements/squelettes/inclure/' . $album_evenement_front;
	$verifier(is_file($chemin_album_evenement), 'Événements doit fournir ' . $album_evenement_front . '.');
	$albums_evenements_front .= file_get_contents($chemin_album_evenement);
}
$verifier(
	strpos($albums_evenements_front, 'zblobul_core:') === false
		&& strpos($albums_evenements_front, 'association_evenements:') !== false,
	'Les albums Événements doivent être autonomes vis-à-vis de Blobul CORE.'
);
$emails_communication_front = array(
	'texte.html',
	'inc-button.html',
	'inc/inc-header.html',
	'inc/inc-logo_site.html',
	'inc/inc-title.html',
	'inc/inc-content.html',
	'inc/inc-footer.html',
);
$sources_emails_communication = '';
foreach ($emails_communication_front as $email_communication_front) {
	$chemin_email_communication = $racine . '/plugins/association-communication/emails/' . $email_communication_front;
	$verifier(is_file($chemin_email_communication), 'Communication doit fournir emails/' . $email_communication_front . '.');
	$sources_emails_communication .= file_get_contents($chemin_email_communication);
}
$verifier(
	strpos($sources_emails_communication, 'zblobul') === false
		&& strpos($sources_emails_communication, 'logo_blobul') === false
		&& strpos($sources_emails_communication, 'association_communication:notification_automatique') !== false,
	'Le gabarit email Communication doit être autonome vis-à-vis de Blobul CORE.'
);
$evenements_lang = file_get_contents($racine . '/plugins/association-evenements/lang/association_evenements_fr.php');
$evenements_sources_lang = '';
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/plugins/association-evenements')) as $evenements_source_lang) {
	if ($evenements_source_lang->isFile() && preg_match('/\.(?:php|html)$/', $evenements_source_lang->getFilename()) && strpos($evenements_source_lang->getPathname(), DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR) === false) {
		$evenements_sources_lang .= file_get_contents($evenements_source_lang->getPathname());
	}
}
$verifier(
	!preg_match('/(?:<:|[\'\"]|\{)association:/', $evenements_sources_lang)
		&& strpos($evenements_sources_lang, 'association_evenements:') !== false
		&& strpos($evenements_lang, "'evenement_info_supplementaire'") !== false,
	'Le plugin Événements doit utiliser son propre domaine de langue.'
);
$adhesions_lang = file_get_contents($racine . '/plugins/association-adhesions/lang/association_adhesions_fr.php');
$adhesions_sources_lang = '';
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/plugins/association-adhesions')) as $adhesions_source_lang) {
	if ($adhesions_source_lang->isFile() && preg_match('/\.(?:php|html)$/', $adhesions_source_lang->getFilename()) && strpos($adhesions_source_lang->getPathname(), DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR) === false) {
		$adhesions_sources_lang .= file_get_contents($adhesions_source_lang->getPathname());
	}
}
$verifier(
	!preg_match('/(?:<:|[\'\"]|\{)association:/', $adhesions_sources_lang)
		&& strpos($adhesions_sources_lang, 'association_adhesions:') !== false
		&& strpos($adhesions_lang, "'cotisation_enregistree'") !== false
		&& strpos($adhesions_lang, "'mois_december'") !== false,
	'Le plugin Adhésions doit utiliser son propre domaine de langue complet.'
);
$meta_association_restant = [];
$iterateur_meta = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/plugins'));
foreach ($iterateur_meta as $fichier_meta) {
	if ($fichier_meta->isFile() && preg_match('/\.(?:html|php)$/', $fichier_meta->getFilename())) {
		$contenu_meta = file_get_contents($fichier_meta->getPathname());
		if (strpos($contenu_meta, '#META{/association/') !== false) {
			$meta_association_restant[] = $fichier_meta->getPathname();
		}
	}
}
$verifier(!$meta_association_restant, 'Les squelettes doivent utiliser CONFIG pour les metas Association.');
$onglets_historiques = [];
$iterateur_onglets = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine));
foreach ($iterateur_onglets as $fichier_onglets) {
	if ($fichier_onglets->isFile() && $fichier_onglets->getExtension() === 'html') {
		$contenu_onglets = file_get_contents($fichier_onglets->getPathname());
		if (strpos($contenu_onglets, '#ONGLETS_ASSOCIATION') !== false) {
			$onglets_historiques[] = $fichier_onglets->getPathname();
		}
	}
}
$verifier(!$onglets_historiques, 'Les pages privees doivent inclure le squelette des onglets avec la syntaxe SPIP native.');
$profil_association = file_get_contents($racine . '/plugins/association-adhesions/modeles/asso_profil.html');
$verifier(
	strpos($profil_association, "#CONFIG{association_metas/rue}|sinon{''}|nl2br") !== false,
	'Le profil public doit normaliser une adresse absente avant nl2br sous PHP 8.'
);
foreach (array(
	'style.css',
	'prive/themes/spip/images/numbers-line.svg',
	'prive/themes/spip/images/passport-line.svg',
	'prive/themes/spip/images/user-community-line.svg',
) as $actif_orphelin) {
	$verifier(
		!is_file($racine . '/' . $actif_orphelin),
		'L actif sans consommateur doit rester supprime : ' . $actif_orphelin
	);
}
$verifier(!preg_match('/\"(?:reinscription|statut_cotisation)\"\s*=>/', $schema), 'Le schema des comptes ne doit plus declarer de champs metier de cotisation.');
$administration_socle = file_get_contents($racine . '/association_administrations.php');
$migration_adhesions = file_get_contents($racine . '/plugins/association-adhesions/inc/association_adhesions_migration.php');
$configuration_socle = file_get_contents($racine . '/formulaires/configurer_association.php');
$configuration_helpers_socle = file_get_contents($racine . '/formulaires/inc/configurer_association.php');
$configuration_evenements = file_get_contents($racine . '/plugins/association-evenements/formulaires/inc/configurer_association_evenements.php');
$configuration_adhesions = file_get_contents($racine . '/plugins/association-adhesions/formulaires/inc/configurer_association_adhesions.php');
$configuration_paiements = file_get_contents($racine . '/plugins/association-paiements/formulaires/inc/configurer_association_paiements.php');
$configuration_compta = file_get_contents($racine . '/plugins/association-compta/formulaires/inc/configurer_association_compta.php');
$configuration_communication = file_get_contents($racine . '/plugins/association-communication/formulaires/inc/configurer_association_communication.php');
$registre_cli_socle = file_get_contents($racine . '/inc/association_config_cli_registre.php');
$api_cli_socle = file_get_contents($racine . '/inc/association_config_cli.php');
$verification_compta = file_get_contents($racine . '/plugins/association-compta/formulaires/inc/configurer_association_compta_verifier.php');
$verification_communication = file_get_contents($racine . '/plugins/association-communication/formulaires/inc/configurer_association_communication_verifier.php');
$pipelines_socle = file_get_contents($racine . '/association_pipelines.php');
$autorisation_socle = file_get_contents($racine . '/association_autoriser.php');
$fonctions_socle = file_get_contents($racine . '/association_fonctions.php');
$options_socle = file_get_contents($racine . '/association_options.php');
$options_adhesions = file_get_contents($racine . '/plugins/association-adhesions/association_adhesions_options.php');
$options_evenements = file_get_contents($racine . '/plugins/association-evenements/association_evenements_options.php');
$maintenance_socle = file_get_contents($racine . '/genie/association_maintenance_bdd.php');
$utils_socle = file_get_contents($racine . '/inc/association/utils.php');
$compta_script_destinations = $racine . '/plugins/association-compta/javascript/jquery.destinations_form.js';
$css_socle = file_get_contents($racine . '/prive/themes/spip/css/asso.css');
$css_compta = file_get_contents($racine . '/plugins/association-compta/prive/themes/spip/css/comptabilite.css');
$css_communication = file_get_contents($racine . '/plugins/association-communication/prive/themes/spip/css/communication.css');
$css_adhesions = file_get_contents($racine . '/plugins/association-adhesions/prive/themes/spip/css/adhesions.css');
$css_evenements = file_get_contents($racine . '/plugins/association-evenements/prive/themes/spip/css/evenements.css');
$css_paiements = file_get_contents($racine . '/plugins/association-paiements/prive/themes/spip/css/paiements.css');
$css_prets = file_get_contents($racine . '/plugins/association-prets/prive/themes/spip/css/prets.css');
$compta_icone = $racine . '/plugins/association-compta/prive/themes/spip/images/comptes-xx.svg';
$pipelines_adhesions = file_get_contents($racine . '/plugins/association-adhesions/association_adhesions_pipelines.php');
$pipelines_evenements = file_get_contents($racine . '/plugins/association-evenements/association_evenements_pipelines.php');
$pipelines_paiements = file_get_contents($racine . '/plugins/association-paiements/association_paiements_pipelines.php');
$pipelines_compta = file_get_contents($racine . '/plugins/association-compta/association_compta_pipelines.php');
$pipelines_communication = file_get_contents($racine . '/plugins/association-communication/association_communication_pipelines.php');
$verifier(
	strpos($maintenance_socle, 'function asso_resultat_en_echec') === false
		&& strpos($maintenance_socle, 'function association_maintenance_resultat_en_echec') !== false,
	'Le helper de maintenance transverse doit porter le préfixe du plugin.'
);
$verifier(
	strpos($utils_socle, 'function is_db_value_true') === false
		&& strpos($utils_socle, 'function association_valeur_bdd_est_vraie') !== false,
	'Le helper de normalisation BDD doit porter le préfixe du plugin.'
);
$verifier(
	strpos($css_socle, '.editer.compte.niveau-1') === false
		&& strpos($css_compta, '.formulaire_importer_plan_comptable .editer.compte.niveau-1') !== false,
	'Les styles du plan comptable doivent appartenir au plugin Comptabilité.'
);
$verifier(
	strpos($css_socle, '.notifications-filters') === false
		&& strpos($css_communication, 'body.notifications .notifications-filters') !== false
		&& strpos($css_communication, 'body.notifications table thead th') !== false
		&& strpos($css_communication, '.page_notifications') === false,
	'Les styles de notifications doivent appartenir au plugin Communication et être bornés à sa page.'
);
$verifier(
	strpos($css_socle, '.filtre-card') === false
		&& strpos($css_socle, '.filtre-important') === false
		&& strpos($css_adhesions, 'body.cotisations .filtre-card') !== false
		&& strpos($css_adhesions, 'body.adherents .asso-filtres') !== false,
	'Les styles de filtres Adhésions doivent appartenir au module et être bornés à ses pages.'
);
$verifier(
	strpos($css_socle, '.justificatifs-cotisation') === false
		&& strpos($css_adhesions, '.justificatifs-cotisation') !== false
		&& strpos($css_adhesions, '.justificatifs-action-valider') !== false,
	'Le contrôle documentaire des cotisations doit être stylé par Adhésions.'
);
$verifier(
	strpos($css_socle, '.tableau-adherents-scroll') === false
		&& strpos($css_adhesions, '.tableau-adherents-scroll .tableau_adherents') !== false
		&& strpos($css_adhesions, '.tableau_adherents .statut_mailsubscriber') !== false,
	'Le tableau privé des adhérents doit être stylé par Adhésions.'
);
$verifier(
	strpos($css_socle, '.tableau-cotisations-scroll') === false
		&& strpos($css_socle, '.cotisations-legende') === false
		&& strpos($css_adhesions, '.tableau-cotisations-scroll .tableau_cotisations_adherent') !== false
		&& strpos($css_adhesions, '@media (max-width: 640px)') !== false,
	'Le tableau responsive des cotisations doit être stylé par Adhésions.'
);
$verifier(
	strpos($css_socle, '.tableau_listes_activite') === false
		&& strpos($css_socle, '.tableau_listes_export_activite') === false
		&& strpos($css_evenements, '.tableau_listes_activite') !== false
		&& strpos($css_evenements, '.tableau_listes_export_activite') !== false,
	'Les listes privées des activités doivent être stylées par Événements.'
);
$verifier(
	strpos($css_socle, '.voir_activites_bloc_configuration') === false
		&& strpos($css_socle, '.table_responsables_activite') === false
		&& strpos($css_evenements, '.voir_activites_bloc_configuration') !== false
		&& strpos($css_evenements, '.table_responsables_activite') !== false
		&& strpos($css_evenements, '#formulaire_voir_activites.formulaire_asso') !== false,
	'La fiche privée et les inscriptions doivent être stylées par Événements.'
);
$verifier(
	strpos($css_socle, '.bloc-resume-compta') === false
		&& strpos($css_socle, '.boite_stats_compact') === false
		&& strpos($css_compta, '.bloc-resume-compta') !== false
		&& strpos($css_compta, '.tableau_evenements_payants') !== false
		&& strpos($css_compta, '.boite_stats_expanded') !== false,
	'Les résumés et statistiques comptables doivent appartenir à Comptabilité.'
);
$verifier(
	strpos($css_socle, '.mk-edge-slider') === false
		&& strpos($css_socle, '#mk-footer') === false
		&& strpos($css_socle, '.header-style-1') === false
		&& strpos($css_socle, 'font-size: 60px') === false,
	'La feuille privée du socle ne doit plus embarquer un thème public étranger à SPIP.'
);
$verifier(
	strpos($css_socle, '.formulaire_editer_evenement') === false
		&& strpos($css_socle, '.formulaire_editer_auteur') === false
		&& strpos($css_evenements, '.formulaire_editer_evenement') !== false
		&& strpos($css_adhesions, '.formulaire_editer_auteur') !== false,
	'Les adaptations des formulaires doivent appartenir à leur module métier.'
);
$verifier(
	strpos($css_socle, '.miniature_transaction') === false
		&& strpos($css_socle, '.statut_transaction.ok') === false
		&& strpos($css_paiements, '.miniature_transaction .statut_transaction') !== false
		&& strpos($css_paiements, '.statut_transaction.rembourse') !== false,
	'Les miniatures et statuts des transactions doivent appartenir à Paiements.'
);
$verifier(
	strpos($css_socle, '.adherents .formulaire_spip.formulaire_recherche_rapide') === false
		&& strpos($css_socle, '.bouton.page_cotisation') === false
		&& strpos($css_socle, '.formulaire_pret') === false
		&& strpos($css_adhesions, '.formulaire_recherche_rapide') !== false
		&& strpos($css_adhesions, '.bouton.page_cotisation') !== false
		&& strpos($css_prets, '.formulaire_pret') !== false,
	'Les derniers styles métier Adhésions et Prêts doivent quitter le socle.'
);
$navigation_configuration = file_get_contents($racine . '/prive/squelettes/navigation/configurer_association.html');
$autorisation_adhesions = file_get_contents($racine . '/plugins/association-adhesions/association_adhesions_autoriser.php');
$migration_familles = file_get_contents($racine . '/plugins/association-adhesions/inc/association_familles.php');
$verifier(
	strpos($configuration_socle, "config == 'evenement'") === false
		&& strpos($configuration_socle, "config == 'evenement_defaut'") === false,
	'Le socle ne doit plus declarer les panneaux de configuration des evenements.'
);
$verifier(
	strpos($configuration_evenements, "config == 'evenement'") !== false
		&& strpos($configuration_evenements, "config == 'evenement_defaut'") !== false,
	'Le module Evenements doit declarer ses panneaux de configuration.'
);
$verifier(
	strpos($configuration_socle, "config == 'mode_paiement'") === false
		&& strpos($configuration_paiements, "config == 'mode_paiement'") !== false,
	'Le panneau des modes de paiement doit appartenir au module Paiements.'
);
$verifier(
	strpos($configuration_helpers_socle, 'function preparer_choix_mode_paiement') === false
		&& strpos($configuration_helpers_socle, 'function identifier_tresorier') === false
		&& strpos($configuration_paiements, 'function preparer_choix_mode_paiement') !== false
		&& strpos($configuration_paiements, 'function identifier_tresorier') !== false,
	'Les helpers Bank et tresorier doivent appartenir au module Paiements.'
);
$verifier(
	strpos($configuration_socle, "include_spip('inc/comptes')") === false
		&& strpos($configuration_socle, "include_spip('inc/destinations')") === false,
	'Le formulaire du socle ne doit pas precharger les bibliotheques de Comptabilite.'
);
$verifier(
	strpos($autorisation_compta, "include_spip('inc/association_evenements_autorisations')") === false
		&& strpos($autorisation_compta, 'spip_asso_activites') === false
		&& strpos($autorisation_compta, "pipeline('association_compta_autoriser_ecriture'") !== false
		&& strpos($autorisation_compta, "autoriser('modifier', 'evenement'") === false,
	'Comptabilite doit deleguer l autorisation de l ecriture sans connaitre le module metier.'
);
$verifier(
	strpos($configuration_socle, "config == 'comptabilite'") === false
		&& strpos($configuration_compta, "config == 'comptabilite'") !== false,
	'Le panneau comptable doit appartenir au module Comptabilite.'
);
$verifier(
	strpos($configuration_socle, "config == 'adhesion'") === false
		&& strpos($configuration_socle, "config == 'entreprise'") === false
		&& strpos($configuration_adhesions, "config == 'adhesion'") !== false
		&& strpos($configuration_adhesions, "config == 'entreprise'") !== false,
	'Les panneaux adhesion et entreprise doivent appartenir au module Adhesions.'
);
$verifier(
	strpos($configuration_helpers_socle, 'function verifier_categorie_adherent_entreprise') === false
		&& strpos($configuration_helpers_socle, 'function preparer_liste_zones') === false
		&& strpos($configuration_helpers_socle, 'function preparer_liste_mailsubscribinglists') === false
		&& strpos($configuration_adhesions, 'function verifier_categorie_adherent_entreprise') !== false
		&& strpos($configuration_adhesions, 'function preparer_liste_zones') !== false
		&& strpos($configuration_adhesions, 'function preparer_liste_mailsubscribinglists') !== false,
	'Les helpers categorie entreprise, zones et listes de diffusion doivent appartenir a Adhesions.'
);
$verifier(
	strpos($configuration_socle, "config == 'segments'") === false
		&& strpos($configuration_communication, "'segments'") !== false
		&& strpos($configuration_communication, "'selection_segment'") !== false,
	'Le panneau segments doit appartenir au module Communication.'
);
$verifier(
	strpos($configuration_socle, "config == 'affichage_public'") === false
		&& strpos($configuration_adhesions, "'affichage_public'") !== false
		&& strpos($configuration_adhesions, "'config_filtres_annuaire'") !== false
		&& strpos($configuration_evenements, "'affichage_public'") !== false
		&& strpos($configuration_evenements, "'config_statuts_liste_publique_inscrits'") !== false,
	'L affichage public doit etre compose par Adhesions et Evenements.'
);
$verifier(
	strpos($configuration_socle, "config == 'affichage_prive'") === false
		&& strpos($configuration_adhesions, "'affichage_prive'") !== false
		&& strpos($configuration_adhesions, "'config_champs_filtres_adherents'") !== false
		&& strpos($configuration_adhesions, "'config_champs_colonnes_adherents'") !== false,
	'L affichage prive des adherents doit appartenir au module Adhesions.'
);
$verifier(
	strpos($configuration_socle, 'config_gis_fieldset') === false
		&& strpos($configuration_socle, 'notification_gis_config_email') === false
		&& strpos($configuration_adhesions, 'config_gis_fieldset') !== false
		&& strpos($configuration_adhesions, 'notification_gis_config_email') !== false,
	'La configuration des notifications GIS doit appartenir au module Adhesions.'
);
$verifier(
	strpos($configuration_socle, 'config_evenement_fiafe') === false
		&& strpos($configuration_socle, 'meta_cfg_event_reseau_fiafe') === false
		&& strpos($configuration_evenements, 'config_evenement_fiafe') !== false
		&& strpos($configuration_evenements, 'meta_cfg_event_reseau_fiafe') !== false,
	'La configuration du reseau FIAFE doit appartenir au module Evenements.'
);
$verifier(
	strpos($configuration_socle, 'function maintenance_build_human_summary(') === false
		&& strpos($configuration_socle, 'Remplacement ciblé') === false
		&& strpos($configuration_socle, "if (isset(\$resume) && is_array(\$resume))") === false,
	'Le formulaire racine ne doit plus contenir le prototype global mort du rapport de maintenance.'
);
$verifier(
	strpos($configuration_adhesions, 'association_evenements_configurer_saisies') === false
		&& strpos($configuration_adhesions, 'association_paiements_configurer_saisies') === false,
	'Adhesions ne doit pas orchestrer la configuration des autres modules.'
);
$verifier(
	strpos($configuration_socle, 'function association_compta_configurer_verifier') === false
		&& strpos($verification_compta, 'function association_compta_configurer_verifier') !== false,
	'Les validations comptables doivent appartenir au module Comptabilite.'
);
$verifier(
	strpos($paquet, 'nom="association_configuration_saisies"') !== false
		&& strpos($paquet, 'nom="association_configuration_verifier"') !== false
		&& strpos($configuration_socle, "pipeline('association_configuration_saisies'") !== false
		&& strpos($configuration_socle, "pipeline('association_configuration_verifier'") !== false,
	'Les saisies et validations métier doivent être composées par des pipelines SPIP.'
);
foreach (array(
	'association_communication_configurer_saisies', 'association_adhesions_configurer_saisies',
	'association_evenements_configurer_saisies', 'association_paiements_configurer_saisies',
	'association_compta_configurer_saisies', 'association_compta_configurer_verifier',
	'association_communication_configurer_verifier',
) as $appel_configuration_metier) {
	$verifier(
		strpos($configuration_socle, $appel_configuration_metier . '(') === false,
		'Le formulaire racine ne doit plus appeler directement ' . $appel_configuration_metier . '.'
	);
}
$verifier(
	strpos($paquet, 'nom="association_config_cli_registre"') !== false
		&& strpos($api_cli_socle, "pipeline('association_config_cli_registre'") !== false,
	'Le registre CLI doit être composé par un pipeline SPIP.'
);
foreach (array(
	'association-adhesions' => 'association_adhesions',
	'association-evenements' => 'association_evenements',
	'association-paiements' => 'association_paiements',
	'association-communication' => 'association_communication',
	'association-compta' => 'association_compta',
) as $module_cli => $prefixe_cli) {
	$definitions_cli = $racine . '/plugins/' . $module_cli . '/inc/' . $prefixe_cli . '_config_cli.php';
	$paquet_cli = file_get_contents($racine . '/plugins/' . $module_cli . '/paquet.xml');
	$verifier(is_file($definitions_cli), 'Définitions CLI absentes pour ' . $module_cli . '.');
	$verifier(strpos($paquet_cli, 'nom="association_config_cli_registre"') !== false, 'Pipeline CLI absent pour ' . $module_cli . '.');
}
$verifier(
	strpos($registre_cli_socle, "'adhesion.") === false
		&& strpos($registre_cli_socle, "'evenement.") === false
		&& strpos($registre_cli_socle, "'paiement.") === false
		&& strpos($registre_cli_socle, "'comptabilite.") === false
		&& strpos($registre_cli_socle, "'affichage.") === false,
	'Le registre CLI du socle ne doit plus déclarer les domaines métier.'
);
$verifier(
	strpos($paquet, 'nom="association_config_cli_snapshot_v1_options"') !== false
		&& strpos($api_cli_socle, "pipeline('association_config_cli_snapshot_v1_options'") !== false
		&& strpos($api_cli_socle, "'evenement.inscription'") === false
		&& strpos($pipelines_evenements, 'function association_evenements_association_config_cli_snapshot_v1_options') !== false
		&& strpos($pipelines_evenements, "'evenement.inscription'") !== false,
	'La compatibilite du snapshot CLI v1 doit etre fournie par Evenements.'
);
$verifier(
	strpos($configuration_socle, 'FILTER_VALIDATE_EMAIL') === false
		&& strpos($verification_communication, 'FILTER_VALIDATE_EMAIL') !== false,
	'La validation des destinataires doit appartenir au module Communication.'
);
$verifier(
	strpos($fonctions_socle, "include_spip('inc/actions')") === false
		&& strpos($fonctions_socle, "include_spip('inc/editer')") === false
		&& strpos($fonctions_socle, "include_spip('inc/autoriser')") === false
		&& substr_count($fonctions_socle, 'function ') === 5,
	'Le fichier de fonctions du socle doit rester limite aux cinq fonctions transversales.'
);
$options_socle = file_get_contents($racine . '/association_options.php');
$options_adhesions = file_get_contents($racine . '/plugins/association-adhesions/association_adhesions_options.php');
$verifier(
	strpos($options_socle, 'association_liste_des_statuts') === false
		&& strpos($options_adhesions, "'sorti', 'prospect', 'ok', 'echu', 'relance'") !== false,
	'La liste des statuts internes des adhérents doit appartenir à Adhésions.'
);
$verifier(
	strpos($options_socle, 'function NbJours(') === false
		&& strpos($options_adhesions, 'function association_adhesions_nombre_jours(') !== false
		&& strpos(file_get_contents($racine . '/plugins/association-adhesions/genie/association_taches_generales.php'), 'NbJours(') === false,
	'Le calcul calendaire des échéances doit appartenir à Adhésions avec un nom conforme.'
);
$verifier(
	!is_file($racine . '/inc/association_familles.php')
		&& is_file($racine . '/plugins/association-adhesions/inc/association_familles.php')
		&& !is_file($racine . '/formulaires/migrer_familles_association.php')
		&& is_file($racine . '/plugins/association-adhesions/formulaires/migrer_familles_association.php'),
	'La migration vers Familles doit appartenir au module Adhesions.'
);
$verifier(
	!is_file($racine . '/inc/fonctions/generer_export_csv.php')
		&& is_file($racine . '/plugins/association-adhesions/inc/fonctions/generer_export_csv.php')
		&& strpos(file_get_contents($racine . '/plugins/association-adhesions/action/exporter_adherents_csv.php'), 'generer_exporter_csv') === false,
	'L export CSV des adherents doit appartenir entierement au module Adhesions.'
);
$verifier(
	strpos($pipelines_socle, 'association_familles') === false
		&& strpos($pipelines_adhesions, 'association_familles') !== false
		&& strpos($autorisation_socle, 'migrerfamilles') === false
		&& strpos($autorisation_adhesions, 'migrerfamilles') !== false,
	'Le socle ne doit plus orchestrer ni autoriser la migration vers Familles.'
);
$verifier(
	strpos($autorisation_socle, 'newsletter') === false
		&& strpos($autorisation_socle, 'asso_compte') === false
		&& strpos($autorisation_socle, 'evenement') === false
		&& strpos($autorisation_socle, 'cotisation') === false,
	'Le fichier d autorisations du socle ne doit plus contenir de logique metier extraite.'
);
$verifier(
	strpos($pipelines_socle, 'association_saisies_lister_disponibles') === false
		&& strpos($pipelines_socle, 'Enregistrer les informations des') === false,
	'Le fichier de pipelines du socle ne doit plus exposer de hooks morts ni de documentation metier orpheline.'
);
$verifier(
	strpos($paquet, 'nom="association_configuration_navigation"') !== false
		&& strpos($navigation_configuration, 'association_configuration_navigation') !== false
		&& strpos($navigation_configuration, 'config,adhesion') === false
		&& strpos($navigation_configuration, 'config,evenement') === false
		&& strpos($navigation_configuration, 'config,comptabilite') === false,
	'La navigation de configuration doit etre composee par pipeline et non coder les domaines dans le squelette.'
);
$verifier(
	strpos($pipelines_adhesions, "['data']['adhesion']") !== false
		&& strpos($pipelines_evenements, "['data']['evenement']") !== false
		&& strpos($pipelines_paiements, "['data']['mode_paiement']") !== false
		&& strpos($pipelines_compta, "['data']['comptabilite']") !== false
		&& strpos($pipelines_communication, "['data']['segments']") !== false
		&& strpos($pipelines_communication, "['data']['notifications']") !== false,
	'Chaque plugin doit fournir ses propres entrees de navigation de configuration.'
);
$verifier(
	strpos($pipelines_compta, "association_module_actif('comptes')") === false,
	'Le fournisseur de navigation Comptabilite ne doit pas dependre du fichier d autorisations du socle.'
);
$verifier(
	strpos($options_socle, 'association_cotisation_statuts') === false
		&& strpos($options_adhesions, 'association_cotisation_statuts') !== false
		&& strpos($options_socle, 'facteur_envoyer_notification_gis') === false
		&& strpos($options_adhesions, 'facteur_envoyer_notification_gis') !== false,
	'Les statuts de cotisation et l integration GIS des auteurs doivent appartenir a Adhesions.'
);
$verifier(
	strpos($options_socle, 'association_activites_statuts') === false
		&& strpos($options_evenements, 'association_activites_statuts') !== false,
	'Les statuts d activite doivent appartenir a Evenements.'
);
$verifier(
	strpos($options_socle, 'insert_jqueryui') === false
		&& strpos($options_socle, 'association_bouton_public_fa') === false
		&& strpos($options_socle, 'association_flottant') === false,
	'Le fichier options du socle ne doit plus contenir de helpers ni de branchements inutilises.'
);
$verifier(
	!is_file($racine . '/javascript/jquery.destinations_form.js')
		&& is_file($compta_script_destinations)
		&& !is_file($racine . '/prive/themes/spip/images/comptes-xx.svg')
		&& is_file($compta_icone),
	'Le script des destinations et l icone des comptes doivent appartenir a Comptabilite.'
);
$verifier(
	!is_file($racine . '/prive/squelettes/extra/configurer_association.html')
		&& is_file($racine . '/plugins/association-compta/prive/squelettes/extra/configurer_association.html'),
	'Les raccourcis comptables de la configuration doivent appartenir a Comptabilite.'
);
$verifier(
	!is_file($racine . '/yaml/association.yaml')
		&& !is_file($racine . '/yaml/champs_auteurs_4_enfants.yaml')
		&& !is_file($racine . '/yaml/champs_auteurs_4_enfants_et_2_invites.yaml')
		&& !is_file($racine . '/yaml/evenement-webinaire_fiafe.yaml')
		&& is_file($racine . '/plugins/association-adhesions/yaml/association.yaml')
		&& is_file($racine . '/plugins/association-adhesions/yaml/champs_auteurs_4_enfants.yaml')
		&& is_file($racine . '/plugins/association-adhesions/yaml/champs_auteurs_4_enfants_et_2_invites.yaml')
		&& is_file($racine . '/plugins/association-evenements/yaml/evenement-webinaire_fiafe.yaml'),
	'Les modeles YAML des auteurs et evenements doivent appartenir a leurs modules metier.'
);
$verifier(
	strpos($migration_familles, "familles_objet_lister_familles('auteur'") !== false
		&& strpos($migration_familles, 'familles_lister_familles_auteur') === false,
	'La migration Adhesions doit utiliser l API objet actuelle du plugin Familles.'
);
$verifier(
	strpos($administration_socle, 'function association_migrer_cotisations_depuis_comptes') === false,
	'Le socle ne doit plus implementer la migration metier des cotisations.'
);
$verifier(
	!preg_match('/spip_asso_(?!ciation_metas)|spip_evenements|spip_mailshots/', $administration_socle),
	'Le fichier d administration du socle ne doit plus nommer de table metier.'
);
$verifier(
	strpos($migration_adhesions, 'function association_migrer_cotisations_depuis_comptes') !== false,
	'La migration historique des cotisations doit appartenir au module Adhesions.'
);
$verifier(
	!preg_match('/function association_(?:maj_create|maj_112|maj_124|maj_spip_asso_activites|maj_142|import_champs_extras)\s*\(/', $administration_socle),
	'Le socle ne doit plus implementer les callbacks de migration metier historiques.'
);
foreach (array(
	'association-adhesions/inc/association_adhesions_migration_legacy.php',
	'association-evenements/inc/association_evenements_migration_legacy.php',
	'association-compta/inc/association_compta_migration_legacy.php',
	'association-communication/inc/association_communication_migration_legacy.php',
	'association-dons/inc/association_dons_migration_legacy.php',
	'association-ventes/inc/association_ventes_migration_legacy.php',
	'association-prets/inc/association_prets_migration_legacy.php',
) as $migration_module) {
	$verifier(is_file($racine . '/plugins/' . $migration_module), 'Migration metier absente : ' . $migration_module . '.');
}
$verifier(
	strpos($administration_socle, 'association_migrations_construire()') !== false
		&& strpos($administration_socle, '_migration_legacy') === false
		&& strpos($paquet, 'nom="association_migrations_historiques"') !== false
		&& is_file($racine . '/inc/association_migrations.php'),
	'Les migrations historiques du socle doivent etre composees par les modules.'
);
$verifier(
	!is_file($racine . '/genie/association_taches_generales.php')
		&& is_file($racine . '/plugins/association-adhesions/genie/association_taches_generales.php'),
	'Le cron des echeances doit appartenir exclusivement au module Adhesions.'
);
$evenements_pipelines = file_get_contents($racine . '/plugins/association-evenements/association_evenements_pipelines.php');
$verifier(
	strpos($evenements_pipelines, "base/association_champs_extras.php") !== false,
	'La declaration des champs extras de spip_evenements doit appartenir au module Evenements.'
);
$verifier(!is_dir($racine . '/exec') || count(glob($racine . '/exec/*')) === 0, 'Les pages privees ne doivent plus utiliser exec/.');
$verifier(!is_file($racine . '/inc/page.php'), 'L ancien moteur de rendu PHP inc/page.php doit etre supprime.');
$verifier(!is_file($racine . '/inc/navigation_modules.php'), 'L ancienne navigation PHP doit etre supprimee.');
$verifier(!is_file($racine . '/balise/autoriser_page.php'), 'La balise d autorisation des anciens exec doit etre supprimee.');
$verifier(!is_file($racine . '/squelettes/profil.html'), 'La page profil doit appartenir au module Adhesions.');
$verifier(!is_file($racine . '/squelettes/evenement.html'), 'La page evenement doit appartenir au module Evenements.');

$iterateur_socle = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine));
foreach ($iterateur_socle as $fichier_socle) {
	if (!$fichier_socle->isFile() || !in_array($fichier_socle->getExtension(), array('php', 'html'), true)) {
		continue;
	}
	$chemin_socle = str_replace('\\', '/', substr($fichier_socle->getPathname(), strlen($racine) + 1));
	if (preg_match('#^(?:plugins|tests|docs|\.git)/#', $chemin_socle)) {
		continue;
	}
	$source_socle = file_get_contents($fichier_socle->getPathname());
	$verifier(
		!preg_match('/[\'\"](?:spip_asso_[a-z0-9_]+|spip_transactions|spip_evenements)[\'\"]/', $source_socle),
		'Le socle nomme encore une table appartenant a un plugin metier dans ' . $chemin_socle . '.'
	);
}

$cotisations_prive = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/contenu/cotisations.html');
$verifier(
	strpos($cotisations_prive, '#AUTORISER{cotisations_menu}') !== false,
	'La page privee des cotisations doit appliquer son autorisation SPIP.'
);

$pages_socle = array(
	'configurer_association',
);
foreach ($pages_socle as $page) {
	$verifier(
		is_file($racine . '/prive/squelettes/contenu/' . $page . '.html'),
		'Le squelette prive contenu/' . $page . '.html est manquant.'
	);
}

$pages_modules = array(
	'association-adhesions' => array('adherents', 'cotisations', 'edit_cotisation', 'voir_adherent'),
	'association-evenements' => array('action_activites', 'activites', 'voir_activites'),
	'association-compta' => array('bilan', 'comptes', 'destinations', 'edit_compte', 'edit_destination', 'edit_plan', 'plan_comptable'),
	'association-dons' => array('dons', 'edit_don'),
	'association-ventes' => array('ventes', 'edit_vente'),
	'association-prets' => array('prets', 'ressources', 'edit_pret', 'edit_ressource'),
);
foreach ($pages_modules as $module => $pages) {
	foreach ($pages as $page) {
		$verifier(
			is_file($racine . '/plugins/' . $module . '/prive/squelettes/contenu/' . $page . '.html'),
			'Le squelette prive ' . $page . ' doit appartenir au module ' . $module . '.'
		);
		$verifier(
			!is_file($racine . '/prive/squelettes/contenu/' . $page . '.html'),
			'Le socle contient encore le squelette metier ' . $page . '.'
		);
	}
}

$iterateur_prive = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine . '/prive'));
foreach ($iterateur_prive as $fichier) {
	if (!$fichier->isFile() || $fichier->getExtension() !== 'html') continue;
	$contenu = file_get_contents($fichier->getPathname());
	$chemin = str_replace($racine . DIRECTORY_SEPARATOR, '', $fichier->getPathname());
	$verifier(strpos($contenu, '<?php') === false, $chemin . ' contient encore du PHP embarque.');
	$verifier(
		!preg_match('/fond=(?:right_col|content)\//', $contenu),
		$chemin . ' depend encore d un squelette de theme ou de FO.'
	);
	$verifier(strpos($contenu, '#EVAL') === false, 'Usage de #EVAL dans ' . $fichier->getFilename() . '.');
}

$sources = array_merge(
	glob($racine . '/*.php'),
	glob($racine . '/{action,base,formulaires,genie,inc,notifications,prive,squelettes}/**/*.{php,html}', GLOB_BRACE)
);
foreach ($sources as $fichier) {
	$contenu = is_file($fichier) ? file_get_contents($fichier) : '';
	$chemin = str_replace($racine . DIRECTORY_SEPARATOR, '', $fichier);
	if (preg_match('/zblobul|blobul_core/i', $contenu)) {
		$erreurs[] = 'Dependance Blobul implicite dans ' . $chemin;
	}
	$verifier(strpos($contenu, '/ecrire/?exec=') === false, 'URL privee codee en dur dans ' . $chemin . '.');
}

if ($erreurs) {
	foreach ($erreurs as $erreur) echo "ECHEC: $erreur\n";
	exit(1);
}

echo "OK: architecture SPIP 4, Inscription 4 et frontieres metier.\n";
