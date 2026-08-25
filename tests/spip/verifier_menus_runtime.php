<?php
if (!defined('_ECRIRE_INC_VERSION')) { exit(2); }
include_spip('inc/autoriser');
$id_auteur = intval(sql_getfetsel('id_auteur', 'spip_auteurs', "statut='0minirezo' AND webmestre='oui'", '', 'id_auteur', '0,1'));
if (!$id_auteur) {
	fwrite(STDERR, "ECHEC aucun webmestre reel disponible pour verifier les menus\n");
	exit(1);
}
$qui = array('id_auteur' => $id_auteur, 'statut' => '0minirezo', 'webmestre' => 'oui', 'restreint' => false);
$menus = array('adherents', 'cotisations', 'activites', 'benevoles', 'partenaires', 'association_commerce', 'ventes', 'dons', 'comptes', 'transactions', 'prets', 'bannieres', 'notifications', 'configurer_association');
$echecs = array();
foreach ($menus as $menu) {
	echo "TEST $menu\n";
	$resultat = autoriser($menu . '_menu', '', 0, $qui);
	echo 'OK ' . $menu . '=' . ($resultat ? 'oui' : 'non') . "\n";
	if (!$resultat) {
		$echecs[] = $menu;
	}
}
if ($echecs) {
	fwrite(STDERR, 'ECHEC menus refuses pour le webmestre #' . $id_auteur . ' : ' . implode(', ', $echecs) . "\n");
	exit(1);
}
