<?php

$racine = dirname(__DIR__);
$contenu = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/contenu/voir_adherent.html');
$info = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/contenu/inc-voir_adherent/voir_adherent-info.html');
$statut = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/contenu/inc-voir_adherent/voir_adherent-statut.html');
$erreurs = array();

foreach (array('#AUTORISER{adherents_menu}', '<h1 class="grostitre">', 'association_adhesions:fiche_adherent_privee_titre') as $attendu) {
	if (strpos($contenu, $attendu) === false) {
		$erreurs[] = 'La fiche privee doit contenir ' . $attendu . '.';
	}
}
if (strpos($info, 'href="call:') !== false || strpos($info, 'href="tel:') === false) {
	$erreurs[] = 'Les numeros doivent utiliser le schema tel:.';
}
if (strpos($info, '#URL_PAGE{ecrire_auteur') !== false || strpos($info, 'href="mailto:') === false) {
	$erreurs[] = 'Le contact email doit utiliser un lien mailto autonome.';
}
foreach (array('Informations à propos', 'Ce membre est "', 'n\'est plus à jour') as $texte_historique) {
	if (strpos($statut, $texte_historique) !== false) {
		$erreurs[] = 'Un texte historique non traduit subsiste : ' . $texte_historique;
	}
}

if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}

echo "OK - fiche adherent privee SPIP 4\n";
