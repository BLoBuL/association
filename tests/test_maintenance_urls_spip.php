<?php

define('_ECRIRE_INC_VERSION', true);
function include_spip($fichier) {}
function table_objet_sql($type) {
	return array('site' => 'spip_syndic', 'article' => 'spip_articles', 'fantome' => 'spip_fantomes')[$type] ?? '';
}
function id_table_objet($type) {
	return array('site' => 'id_syndic', 'article' => 'id_article', 'fantome' => 'id_fantome')[$type] ?? '';
}
function sql_showtable($table, $complet = false) {
	$schemas = array(
		'spip_syndic' => array('id_syndic' => 'bigint'),
		'spip_articles' => array('id_article' => 'bigint'),
	);
	return isset($schemas[$table]) ? array('field' => $schemas[$table]) : array();
}

require dirname(__DIR__) . '/plugins/association-communication/inc/association_communication_maintenance.php';
$source = file_get_contents(dirname(__DIR__) . '/plugins/association-communication/inc/association_communication_maintenance.php');

if (asso_table_col_for_type('site') !== array('spip_syndic', 'id_syndic')) {
	fwrite(STDERR, "Le type site n'utilise pas l'objet SQL natif SPIP.\n");
	exit(1);
}
if (asso_table_col_for_type('fantome') !== array()) {
	fwrite(STDERR, "Une table absente ne doit pas produire de requête SQL invalide.\n");
	exit(1);
}
if (asso_table_col_for_type('article.invalide') !== array()) {
	fwrite(STDERR, "Un type URL malformé doit être rejeté avant résolution SQL.\n");
	exit(1);
}
if (preg_match("/spip_mailshots_destinataires'\s*,\s*sql_in\('id_mailsubscriber'/", $source)) {
	fwrite(STDERR, "Mailshot doit être nettoyé par email, sa table ne possède pas id_mailsubscriber.\n");
	exit(1);
}

echo "OK: les URLs utilisent les objets SQL déclarés par SPIP.\n";
