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
$verification_compta = file_get_contents($racine . '/plugins/association-compta/formulaires/inc/configurer_association_compta_verifier.php');
$verification_communication = file_get_contents($racine . '/plugins/association-communication/formulaires/inc/configurer_association_communication_verifier.php');
$pipelines_socle = file_get_contents($racine . '/association_pipelines.php');
$autorisation_socle = file_get_contents($racine . '/association_autoriser.php');
$pipelines_adhesions = file_get_contents($racine . '/plugins/association-adhesions/association_adhesions_pipelines.php');
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
		&& strpos($autorisation_compta, "pipeline('association_evenement_resoudre_contexte'") !== false,
	'Comptabilite doit resoudre le contexte evenement par contrat sans lire la table du module.'
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
	strpos($configuration_socle, 'FILTER_VALIDATE_EMAIL') === false
		&& strpos($verification_communication, 'FILTER_VALIDATE_EMAIL') !== false,
	'La validation des destinataires doit appartenir au module Communication.'
);
$verifier(
	!is_file($racine . '/inc/association_familles.php')
		&& is_file($racine . '/plugins/association-adhesions/inc/association_familles.php')
		&& !is_file($racine . '/formulaires/migrer_familles_association.php')
		&& is_file($racine . '/plugins/association-adhesions/formulaires/migrer_familles_association.php'),
	'La migration vers Familles doit appartenir au module Adhesions.'
);
$verifier(
	strpos($pipelines_socle, 'association_familles') === false
		&& strpos($pipelines_adhesions, 'association_familles') !== false
		&& strpos($autorisation_socle, 'migrerfamilles') === false
		&& strpos($autorisation_adhesions, 'migrerfamilles') !== false,
	'Le socle ne doit plus orchestrer ni autoriser la migration vers Familles.'
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
$debut_migrations_tardives = strpos($administration_socle, "\$maj['1.1.0']");
$fin_migrations_tardives = strpos($administration_socle, "\$maj['1.6.0']");
$migrations_tardives = ($debut_migrations_tardives !== false && $fin_migrations_tardives !== false)
	? substr($administration_socle, $debut_migrations_tardives, $fin_migrations_tardives - $debut_migrations_tardives)
	: '';
$verifier(
	$migrations_tardives !== '' && !preg_match('/spip_asso_(?!ciation_metas)|spip_evenements|spip_mailshots/', $migrations_tardives),
	'Les migrations 1.1 a 1.5 du socle doivent seulement deleguer aux modules.'
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
