<?php

$racine = dirname(__DIR__) . '/action/';
$actions = array(
	'editer_asso_dons.php' => 'action_editer_asso_dons($id_don = null)',
	'editer_asso_ventes.php' => 'action_editer_asso_ventes($id_vente = null)',
	'editer_asso_ressources.php' => 'action_editer_asso_ressources($id_ressource = null)',
);

foreach ($actions as $fichier => $signature) {
	$source = file_get_contents($racine . $fichier);
	if (strpos($source, $signature) === false) {
		fwrite(STDERR, "L'action $fichier n'accepte pas l'identifiant transmis par CVT\n");
		exit(1);
	}
	if (!preg_match('/if \(\$id_[a-z_]+ === null\) \{\s*\$securiser_action/s', $source)) {
		fwrite(STDERR, "L'action $fichier appelle encore securiser_action hors appel direct\n");
		exit(1);
	}
}

echo "Actions d'édition compatibles avec CVT SPIP 4\n";
