<?php
if (!defined('_ECRIRE_INC_VERSION')) { exit(2); }
$GLOBALS['visiteur_session'] = array('id_auteur' => -1, 'statut' => '0minirezo', 'webmestre' => 'oui', 'restreint' => false);
foreach (array('partenaire', 'banniere') as $nom) {
	include_spip('formulaires/editer_asso_' . $nom);
	$fonction = 'formulaires_editer_asso_' . $nom . '_charger_dist';
	$valeurs = $fonction('new', '');
	if (!is_array($valeurs) || empty($valeurs['editable'])) {
		fwrite(STDERR, "ECHEC formulaire $nom non editable (type=" . objet_type('asso_' . $nom, false) . ")\n");
		exit(1);
	}
	echo "OK formulaire $nom editable\n";
}
