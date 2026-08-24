<?php

define('_ECRIRE_INC_VERSION', 1);
$GLOBALS['appels_newsletter'] = array();
$GLOBALS['suppressions_newsletter'] = array();
$GLOBALS['abonnements_newsletter'] = array(
	array('identifiant_list' => 'statut_interne_echu', 'statut_subscription' => 'valide', 'statut_list' => 'fermee'),
	array('identifiant_list' => 'liste_defaut', 'statut_subscription' => 'refuse', 'statut_list' => 'ouverte'),
);

function include_spip($fichier) { return true; }
function email_valide($email) { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
function mailsubscribers_obfusquer_email($email) { return 'obf-' . $email; }
function sql_quote($valeur) { return "'" . addslashes($valeur) . "'"; }
function sql_fetsel($champs, $table, $where) {
	return array('id_mailsubscriber' => 7, 'email' => 'membre@example.org', 'statut' => 'valide');
}
function sql_allfetsel($champs, $table, $where) { return $GLOBALS['abonnements_newsletter']; }
function sql_delete($table, $where) { $GLOBALS['suppressions_newsletter'][] = array($table, $where); return 1; }
function charger_fonction($fonction, $repertoire) {
	return function ($email, $options) use ($fonction) {
		$GLOBALS['appels_newsletter'][] = array($fonction, $email, $options['listes'] ?? array());
		return true;
	};
}

require dirname(__DIR__) . '/plugins/association-communication/inc/association_communication_privileges.php';

$auteur = array(
	'email' => 'membre@example.org',
	'prenom' => 'Marie',
	'nom_famille' => 'Test',
	'statut' => '1comite',
	'statut_interne' => 'ok',
);
association_communication_privileges_verifier($auteur, array('liste_defaut'));

$attendus = array(
	array('unsubscribe', 'membre@example.org', array('statut_interne_echu')),
	array('subscribe', 'membre@example.org', array('statut_interne_ok')),
	array('subscribe', 'membre@example.org', array('liste_defaut')),
);
$erreurs = array();
if ($GLOBALS['appels_newsletter'] !== $attendus) {
	$erreurs[] = 'La reconciliation des listes ne correspond pas au statut adherent.';
}

$GLOBALS['appels_newsletter'] = array();
$auteur['statut_interne'] = 'sorti';
association_communication_privileges_verifier($auteur, array());
if (!in_array(array('unsubscribe', 'membre@example.org', array()), $GLOBALS['appels_newsletter'], true)) {
	$erreurs[] = 'Un auteur sorti doit etre desabonne de toutes les listes.';
}
if (count($GLOBALS['suppressions_newsletter']) !== 2) {
	$erreurs[] = 'Les donnees Mailsubscribers d un auteur sorti doivent etre retirees.';
}

$source_adhesions = file_get_contents(dirname(__DIR__) . '/plugins/association-adhesions/inc/fonctions/priviliges_adherent.php');
foreach (array('spip_mailsubscribers', 'spip_mailsubscriptions', 'spip_mailsubscribinglists') as $table) {
	if (strpos($source_adhesions, $table) !== false) {
		$erreurs[] = 'Adhesions connait encore la table ' . $table . '.';
	}
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}
echo "OK: cycle Mailsubscribers distribue vers Communication.\n";
