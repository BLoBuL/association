<?php

$racine = dirname(__DIR__);
$page = file_get_contents($racine . '/plugins/association-communication/prive/squelettes/contenu/notifications.html');
$langue = file_get_contents($racine . '/plugins/association-communication/lang/association_communication_fr.php');
$erreurs = array();

foreach (array(
	'#AUTORISER{notifications_menu}',
	'<h1 class="grostitre"><:association_communication:titre_notifications:></h1>',
	'<table class="spip liste">',
	'<caption class="offscreen">',
	'<th scope="col"',
) as $attendu) {
	if (strpos($page, $attendu) === false) $erreurs[] = 'Structure Notifications absente : ' . $attendu;
}
foreach (array('Version BETA', 'Cette page est en travaux', 'Création / Validation de compte</strong>', '<h2>Filtres</h2>', '<label>Type de destinataire') as $historique) {
	if (strpos($page, $historique) !== false) $erreurs[] = 'Texte historique codé en dur : ' . $historique;
}
foreach (array('notifications_intro', 'notifications_onglet_validation', 'notifications_tableau_titre', 'notifications_colonne_action') as $cle) {
	if (strpos($langue, "'" . $cle . "'") === false) $erreurs[] = 'Clé de langue absente : ' . $cle;
}
if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}
echo "OK - page Notifications native SPIP 4\n";
