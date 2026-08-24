<?php

$racine = dirname(__DIR__) . '/plugins/association-adhesions/';
$notification = file_get_contents($racine . 'action/test_notification_cotisation.php');
$recu = file_get_contents($racine . 'inc/fonctions/facteur_envoyer_recu_adhesion.php');
$recherche = file_get_contents($racine . 'inc/adherents_search_context.php');
$recherche_avancee = file_get_contents($racine . 'formulaires/inc/adherents_recherche_avancee.php');
$justificatifs = file_get_contents($racine . 'action/valider_justificatifs_cotisation.php');
$cotisations = file_get_contents($racine . 'inc/cotisations.php');
$stockage = file_get_contents($racine . 'inc/cotisations_stockage.php');
$erreurs = array();

foreach (array(
	'action/test_notification_cotisation.php' => $notification,
	'inc/fonctions/facteur_envoyer_recu_adhesion.php' => $recu,
	'action/valider_justificatifs_cotisation.php' => $justificatifs,
) as $fichier => $contenu) {
	if (strpos($contenu, "sql_fetsel('*', 'spip_asso_comptes'") !== false
		|| strpos($contenu, "sql_getfetsel('id_compte', 'spip_asso_comptes'") !== false) {
		$erreurs[] = "$fichier lit encore directement la source comptable historique.";
	}
	if (strpos($contenu, 'association_cotisation_lire_par_compte(') === false) {
		$erreurs[] = "$fichier ne passe pas par l’adaptateur métier Adhésions.";
	}
	if (strpos($contenu, 'spip_transactions') !== false
		|| strpos($contenu, 'association_adhesions_transaction_lire(') === false
		|| strpos($contenu, 'association_paiements_transaction_lire(') !== false) {
		$erreurs[] = "$fichier contourne encore la facade Paiements.";
	}
}

if (strpos($notification, "'spip_asso_cotisations'") === false
	|| strpos($recu, "'spip_asso_cotisations'") === false) {
	$erreurs[] = 'La recherche de cotisation ne part pas de la nouvelle table métier.';
}
if (strpos($recu, "'email' => \$email_adherent") !== false
	|| strpos($recu, "'bcc' => \$bcc") !== false
	|| strpos($recu, "'bcc_meta='") !== false) {
	$erreurs[] = 'Le reçu journalise encore des adresses destinataires.';
}
foreach (array(
	'inc/adherents_search_context.php' => $recherche,
	'formulaires/inc/adherents_recherche_avancee.php' => $recherche_avancee,
	'action/valider_justificatifs_cotisation.php' => $justificatifs,
) as $fichier => $contenu) {
	if (strpos($contenu, 'spip_asso_comptes') !== false) {
		$erreurs[] = "$fichier lit encore les colonnes métier du journal comptable.";
	}
}
if (strpos($cotisations, "sql_countsel('spip_asso_comptes', 'id_transaction='") !== false) {
	$erreurs[] = 'La détection des paiements en cours lit encore les statuts historiques.';
}
if (strpos($stockage, 'association_compta_ecriture_lire(') === false
	|| strpos($stockage, 'association_compta_ecriture_modifier(') === false
	|| substr_count($stockage, 'spip_asso_comptes') > 2) {
	$erreurs[] = 'Le stockage Cotisations contourne encore l API Comptabilite hors colonne transitoire.';
}

$acces_comptables_autorises = array(
	'inc/association_adhesions_migration.php',
	'inc/association_adhesions_migration_compta.php',
	'inc/cotisations_stockage.php',
);
$iterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($racine));
foreach ($iterateur as $fichier) {
	if (!$fichier->isFile() || $fichier->getExtension() !== 'php' || str_contains($fichier->getPathname(), DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR)) {
		continue;
	}
	$relatif = str_replace('\\', '/', substr($fichier->getPathname(), strlen($racine)));
	if (in_array($relatif, $acces_comptables_autorises, true)) {
		continue;
	}
	if (strpos(file_get_contents($fichier->getPathname()), 'spip_asso_comptes') !== false) {
		$erreurs[] = "$relatif accède au journal comptable hors adaptateur ou migration.";
	}
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: notifications et reçus lisent la cotisation métier sans journaliser les adresses.\n";
