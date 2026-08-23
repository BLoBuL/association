<?php

$racine = dirname(__DIR__);
$navigation = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/navigation/voir_adherent.html');
$formulaire = file_get_contents($racine . '/plugins/association-adhesions/formulaires/editer_asso_cotisation.php');
$squelette = file_get_contents($racine . '/plugins/association-adhesions/formulaires/editer_asso_cotisation.html');

$erreurs = array();

foreach (array('auteur', 'auteur_edit', 'editer_asso_cotisation') as $exec) {
	$attendu = '#URL_ECRIRE{' . $exec . '}|parametre_url{id_auteur,#ID_AUTEUR}';
	if (strpos($navigation, $attendu) === false) {
		$erreurs[] = "Le lien $exec ne transmet pas la valeur de #ID_AUTEUR.";
	}
}

if (preg_match('/#URL_ECRIRE\{(?:auteur|auteur_edit|editer_asso_cotisation),id_auteur\}/', $navigation)) {
	$erreurs[] = 'Un lien transmet encore le nom id_auteur sans sa valeur.';
}

if (strpos($formulaire, "\$id_compte == 'new' && !\$id_auteur") === false
	|| strpos($formulaire, "'editable' => false") === false
	|| strpos($formulaire, "'message_erreur' => _T('association_adhesions:erreur_id_auteur_invalide')") === false) {
	$erreurs[] = 'Le formulaire ne bloque pas explicitement une création sans auteur.';
}

if (!preg_match('/\[\(#EDITABLE\|oui\)\s*<form[\s\S]*<\/form>\s*\]/', $squelette)) {
	$erreurs[] = 'Le formulaire HTML reste affiché lorsque le contexte CVT est non éditable.';
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK - navigation et contexte de création des cotisations\n";
