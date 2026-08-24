<?php

define('_ECRIRE_INC_VERSION', 1);
$GLOBALS['insertions_mailshot'] = array();
$GLOBALS['metas_mailshot'] = array();
$GLOBALS['cron_mailshot'] = 0;

function sql_insertq($table, $donnees) {
	$GLOBALS['insertions_mailshot'][] = array($table, $donnees);
	return $table === 'spip_mailshots' ? 42 : count($GLOBALS['insertions_mailshot']);
}
function sql_updateq($table, $donnees, $where) {
	throw new RuntimeException('Le total ne doit pas etre corrige quand toutes les insertions reussissent.');
}
function ecrire_meta($nom, $valeur) { $GLOBALS['metas_mailshot'][$nom] = $valeur; }
function include_spip($fichier) { return true; }
function genie_queue_watch_dist() { $GLOBALS['cron_mailshot']++; }

require dirname(__DIR__) . '/plugins/association-communication/inc/email_collectif.php';

$id = association_communication_mailshot_creer(
	'Sujet',
	'<p>Message</p>',
	array(' TEST@example.org ', 'test@example.org', 'invalide', 'autre@example.org'),
	array('date' => '2026-08-24 12:00:00', 'id_evenement' => 12)
);

$erreurs = array();
if ($id !== 42) $erreurs[] = 'Identifiant de mailshot incorrect.';
if (count($GLOBALS['insertions_mailshot']) !== 3) $erreurs[] = 'La liste des destinataires n est pas dedupliquee.';
$mailshot = $GLOBALS['insertions_mailshot'][0][1] ?? array();
if (($mailshot['total'] ?? null) !== 2) $erreurs[] = 'Le total du mailshot doit correspondre aux emails valides.';
if (($mailshot['id_evenement'] ?? null) !== 12) $erreurs[] = 'Le contexte evenement doit etre conserve.';
if (($GLOBALS['metas_mailshot']['mailshot_processing'] ?? '') !== 'oui') $erreurs[] = 'Le traitement Mailshot doit etre active.';
if ($GLOBALS['cron_mailshot'] !== 1) $erreurs[] = 'La file de travaux doit etre reveillee une fois.';

$sources_adhesions = file_get_contents(dirname(__DIR__) . '/plugins/association-adhesions/action/envoyer_relances.php')
	. file_get_contents(dirname(__DIR__) . '/plugins/association-adhesions/action/envoyer_email_collectif_adherent.php');
if (strpos($sources_adhesions, 'spip_mailshots') !== false) {
	$erreurs[] = 'Les actions Adhesions ne doivent plus ecrire dans Mailshot.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}
echo "OK: creation Mailshot centralisee dans Communication.\n";
