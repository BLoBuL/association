<?php
if (!defined('_ECRIRE_INC_VERSION')) { exit(2); }
foreach (array('partenaire', 'banniere') as $nom) {
	include_spip('formulaires/editer_asso_' . $nom);
	$fonction = 'formulaires_editer_asso_' . $nom . '_charger_dist';
	$valeurs = $fonction('new', '');
	if (!is_array($valeurs) || empty($valeurs['editable'])) {
		fwrite(STDERR, "ECHEC formulaire $nom non editable\n");
		exit(1);
	}
	echo "OK formulaire $nom editable\n";
}
