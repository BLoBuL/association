<?php
$racine = dirname(__DIR__);
$base = file_get_contents($racine . '/base/association_compta.php');
$admin = file_get_contents($racine . '/association_compta_administrations.php');
$erreurs = array();
foreach (array('spip_asso_comptes', 'spip_asso_plan', 'spip_asso_destination', 'spip_asso_destination_op') as $table) {
	if (strpos($base, $table) === false) $erreurs[] = 'table absente: ' . $table;
}
if (strpos($base, 'spip_asso_cotisations') !== false) $erreurs[] = 'Comptabilité ne doit pas posséder les cotisations';
if (!preg_match('/id_transaction[^\n]+BIGINT NOT NULL default/', $base)) $erreurs[] = 'id_transaction doit rester un BIGINT compatible Bank';
if (strpos($admin, 'sql_drop_table') !== false) $erreurs[] = 'désinstallation destructive';
if ($erreurs) { fwrite(STDERR, implode("\n", $erreurs) . "\n"); exit(1); }
echo "OK: structure Association Comptabilité.\n";
