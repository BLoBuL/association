<?php

$racine = dirname(__DIR__);
$api = file_get_contents($racine . '/plugins/association-dons/inc/association_dons_comptabilite.php');
$action = file_get_contents($racine . '/plugins/association-dons/action/editer_asso_dons.php');
$formulaire = file_get_contents($racine . '/plugins/association-dons/formulaires/editer_asso_dons.php');
$suppression = file_get_contents($racine . '/plugins/association-dons/action/supprimer_dons.php');
$comptes = file_get_contents($racine . '/plugins/association-compta/inc/comptes.php');
$options = file_get_contents($racine . '/plugins/association-dons/association_dons_options.php');
$recu = file_get_contents($racine . '/plugins/association-dons/action/exporter_recu_fiscal_pdf.php');

$assertions = array(
	array(str_contains($api, "'objet' => 'asso_don'"), "l'écriture canonique doit identifier l'objet don"),
	array(str_contains($api, "'id_auteur' => (int) \$id_auteur"), "le compte doit conserver le véritable auteur"),
	array(str_contains($api, "association_compta_ecritures_objet_lister('asso_don'") && str_contains($api, "'legacy_id_journal' => true"), "la lecture doit rester compatible avec les écritures historiques via Comptabilité"),
	array(str_contains($action, 'association_dons_compte_creer('), "l'action doit utiliser l'API Dons"),
	array(str_contains($formulaire, 'association_dons_compte_lire('), "le formulaire doit retrouver le compte par l'API Dons"),
	array(str_contains($suppression, 'association_dons_compte_supprimer('), "la suppression doit déléguer le compte à Comptabilité"),
	array(!preg_match('/spip_asso_(?:comptes|destination_op)/', $api . $suppression), "Dons ne doit pas accéder directement aux tables comptables"),
	array(str_contains($api, 'function association_dons_montant_fiscal(') && str_contains($api, "association_compta_ecritures_objets_total('asso_don'"), "le reçu fiscal doit agréger les dons via Comptabilité"),
	array(str_contains($recu, 'association_dons_montant_fiscal(') && !str_contains($recu, 'spip_asso_comptes') && !str_contains($recu, 'pc_cotisations') && !str_contains($recu, '$mail'), "le reçu fiscal ne doit plus lire le journal ni l'imputation des cotisations"),
	array(!str_contains($comptes, 'function compte_don(') && !str_contains($comptes, 'function modifier_compte_don('), "Comptabilité ne doit plus posséder la logique Dons"),
	array(!str_contains($options, 'function generer_url_don('), "le pseudo-objet don ne doit plus avoir d'alias URL"),
);

foreach ($assertions as [$ok, $message]) {
	if (!$ok) {
		fwrite(STDERR, "ECHEC: {$message}.\n");
		exit(1);
	}
}

echo "OK: le cycle comptable Dons appartient au plugin métier.\n";
