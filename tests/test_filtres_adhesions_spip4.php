<?php

$racine = dirname(__DIR__);
$cotisations = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/contenu/cotisations.html');
$adherents = file_get_contents($racine . '/plugins/association-adhesions/prive/squelettes/contenu/adherents.html');
$css = file_get_contents($racine . '/plugins/association-adhesions/prive/themes/spip/css/adhesions.css');
$erreurs = array();
foreach (array('>Filtres<', '>Adhérents<', '>Entreprises<', 'aria-label="Filtre type', 'title="Du ') as $historique) {
	if (strpos($cotisations . $adherents, $historique) !== false) $erreurs[] = 'Filtre Adhésions codé en dur : ' . $historique;
}
if (preg_match('/<label>(?:<:association_adhesions:label_filtre|\[\(#VALEUR\{label\})/', $cotisations . $adherents)) {
	$erreurs[] = 'Un label sans contrôle subsiste dans les filtres.';
}
foreach (array('filtres_cotisations_explication', 'contexte_adherents', 'filtre_type_inscription_aria', 'periode_du') as $cle) {
	if (strpos($cotisations . $adherents, 'association_adhesions:' . $cle) === false) $erreurs[] = 'Clé de filtre non utilisée : ' . $cle;
}
if (strpos($css, '.filtre-groupe > .label') === false) $erreurs[] = 'Le style des libellés sémantiques est absent.';
if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}
echo "OK - filtres Adhésions natifs SPIP 4\n";
