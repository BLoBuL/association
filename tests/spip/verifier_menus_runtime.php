<?php
if (!defined('_ECRIRE_INC_VERSION')) { exit(2); }
include_spip('inc/autoriser');
$qui = array('id_auteur' => -1, 'statut' => '0minirezo', 'webmestre' => 'oui', 'restreint' => false);
$menus = array('adherents', 'cotisations', 'activites', 'benevoles', 'partenaires', 'association_commerce', 'ventes', 'dons', 'comptes', 'transactions', 'prets', 'bannieres', 'notifications', 'configurer_association');
foreach ($menus as $menu) {
	echo "TEST $menu\n";
	$resultat = autoriser($menu . '_menu', '', 0, $qui);
	echo 'OK ' . $menu . '=' . ($resultat ? 'oui' : 'non') . "\n";
}
