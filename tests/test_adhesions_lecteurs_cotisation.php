<?php

$racine = dirname(__DIR__) . '/plugins/association-adhesions/';
$notification = file_get_contents($racine . 'action/test_notification_cotisation.php');
$recu = file_get_contents($racine . 'inc/fonctions/facteur_envoyer_recu_adhesion.php');
$erreurs = array();

foreach (array(
	'action/test_notification_cotisation.php' => $notification,
	'inc/fonctions/facteur_envoyer_recu_adhesion.php' => $recu,
) as $fichier => $contenu) {
	if (strpos($contenu, "sql_fetsel('*', 'spip_asso_comptes'") !== false
		|| strpos($contenu, "sql_getfetsel('id_compte', 'spip_asso_comptes'") !== false) {
		$erreurs[] = "$fichier lit encore directement la source comptable historique.";
	}
	if (strpos($contenu, 'association_cotisation_lire_par_compte(') === false) {
		$erreurs[] = "$fichier ne passe pas par l’adaptateur métier Adhésions.";
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

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK: notifications et reçus lisent la cotisation métier sans journaliser les adresses.\n";
