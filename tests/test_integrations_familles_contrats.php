<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
define('_ECRIRE_INC_VERSION', 1);

function pipeline($nom, $flux) {
	if ($nom === 'association_contexte_familial' && !empty($flux['id_auteur'])) {
		$flux['disponible'] = true;
		$flux['id_famille'] = 42;
		$flux['familles'] = array(42);
		$flux['membres'] = array((int) $flux['id_auteur'], 84);
	}
	if ($nom === 'association_contrat_demander' && !empty($flux['id_contrat_type'])) {
		$flux['id_contrat'] = 21;
		$flux['contrat_cree'] = true;
	}

	return $flux;
}

function verifier_integration($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, "ECHEC: {$message}\n");
		exit(1);
	}
	echo "OK: {$message}\n";
}

require_once dirname(__DIR__) . '/inc/association_capacites.php';

$famille = association_contexte_familial(array('id_auteur' => 12));
verifier_integration($famille['disponible'] === true, 'le contexte familial peut etre enrichi facultativement');
verifier_integration($famille['id_famille'] === 42, 'la famille principale est normalisee');
verifier_integration($famille['membres'] === array(12, 84), 'les membres sont exposes sans lecture SQL par le consommateur');

$sans_famille = association_contexte_familial(array());
verifier_integration($sans_famille['disponible'] === false, 'le repli sans Familles reste valide');
verifier_integration($sans_famille['familles'] === array(), 'le repli familial conserve une structure stable');

$contrat = association_demander_contrat(array('id_contrat_type' => 3, 'id_commande' => 9));
verifier_integration($contrat['contrat_cree'] === true, 'une demande de contrat peut etre satisfaite par un fournisseur');
verifier_integration($contrat['id_contrat'] === 21, 'l identifiant du contrat est retourne au producteur metier');

$sans_contrat = association_demander_contrat(array());
verifier_integration($sans_contrat['contrat_cree'] === false, 'le repli sans Contrats ne bloque pas l objet metier');
verifier_integration($sans_contrat['id_contrat'] === 0, 'le repli contrat utilise un identifiant nul explicite');

$racine = dirname(__DIR__);
$adhesions = file_get_contents($racine . '/plugins/association-adhesions/association_adhesions_pipelines.php');
$commerce = file_get_contents($racine . '/plugins/association-commerce/association_commerce_pipelines.php');
$paquet_commerce = file_get_contents($racine . '/plugins/association-commerce/paquet.xml');
verifier_integration(str_contains($adhesions, 'familles_objet_lister_familles'), 'Adhesions adapte l API publique Familles');
verifier_integration(str_contains($commerce, 'contrats_creer_ou_mettre_a_jour_depuis_flux'), 'Commerce adapte l API publique Contrats');
verifier_integration(str_contains($paquet_commerce, '<utilise nom="contrats"'), 'Contrats reste une dependance facultative de Commerce');

echo "Contrats facultatifs Familles et Contrats valides.\n";
