<?php
if (!defined('_ECRIRE_INC_VERSION')) { exit(2); }
$GLOBALS['visiteur_session'] = array('id_auteur' => -1, 'statut' => '0minirezo', 'webmestre' => 'oui', 'restreint' => false);
foreach (array('partenaire', 'banniere') as $nom) {
	include_spip('formulaires/editer_asso_' . $nom);
	$fonction = 'formulaires_editer_asso_' . $nom . '_charger_dist';
	$valeurs = $fonction('new', '');
	if (!is_array($valeurs) || !array_key_exists('titre', $valeurs)) {
		$autorise = autoriser('creer', $nom, 0, $GLOBALS['visiteur_session']);
		fwrite(STDERR, "ECHEC formulaire $nom non editable (type=" . objet_type('asso_' . $nom, false) . ', autorise=' . ($autorise ? 'oui' : 'non') . ', valeurs=' . implode(',', array_keys((array) $valeurs)) . ")\n");
		exit(1);
	}
	echo "OK formulaire $nom charge\n";
}
