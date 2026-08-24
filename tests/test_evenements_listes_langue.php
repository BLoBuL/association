<?php

$racine = dirname(__DIR__);
$fichiers = array(
	'plugins/association-evenements/prive/objets/liste/item_activite.html',
	'plugins/association-evenements/prive/objets/liste/table_export_activites.html',
	'plugins/association-evenements/prive/objets/liste/item_participation_adherent.html',
	'plugins/association-evenements/prive/objets/liste/item_suivi_activite_adherent.html',
);
$source = '';
foreach ($fichiers as $fichier) $source .= file_get_contents($racine . '/' . $fichier);
$erreurs = array();
foreach (array('Quota plein,', 'Date ouverture :', '>Gratuit<', '>Ouvert à tous<', 'Nombre de membres inscrit<', '>Filtres :<', 'Le rendez-vous a été supprimé.</td>') as $historique) {
	if (strpos($source, $historique) !== false) $erreurs[] = 'Libellé Événements codé en dur : ' . $historique;
}
foreach (array('activite_quota_plein', 'activite_ouverte_tous', 'activite_nombre_inscrits', 'export_filtres', 'evenement_supprime') as $cle) {
	if (strpos($source, 'association_evenements:' . $cle) === false) $erreurs[] = 'Clé Événements non utilisée : ' . $cle;
}
foreach (array('activites', 'categories_activites', 'editer_asso_activite', 'voir_activites') as $page) {
	$hierarchie = file_get_contents($racine . '/plugins/association-evenements/prive/squelettes/hierarchie/' . $page . '.html');
	if (strpos($hierarchie, '<:info_racine_site:>') === false || strpos($hierarchie, '>Racine du site<') !== false) {
		$erreurs[] = 'Fil d’Ariane historique : ' . $page;
	}
}
if ($erreurs) {
	fwrite(STDERR, implode("\n", $erreurs) . "\n");
	exit(1);
}
echo "OK - listes privées Événements traduisibles\n";
