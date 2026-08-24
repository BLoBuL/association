<?php

$racine = dirname(__DIR__);
$api = file_get_contents($racine . '/plugins/association-ventes/inc/association_ventes_comptabilite.php');
$action = file_get_contents($racine . '/plugins/association-ventes/action/editer_asso_ventes.php');
$formulaire = file_get_contents($racine . '/plugins/association-ventes/formulaires/editer_asso_ventes.php');
$suppression = file_get_contents($racine . '/plugins/association-ventes/action/supprimer_ventes.php');
$comptes = file_get_contents($racine . '/plugins/association-compta/inc/comptes.php');

$assertions = array(
	array(str_contains($api, "'objet' => 'asso_vente'"), "les écritures doivent identifier l'objet vente"),
	array(str_contains($api, "'id_auteur' => (int) \$id_auteur"), "les écritures doivent conserver le véritable acheteur"),
	array(str_contains($api, 'OR id_journal={$id_vente}'), "la lecture doit accepter les liens historiques"),
	array(substr_count($action, 'association_ventes_compte_creer(') === 3, "l'action doit couvrir vente simple et frais séparés"),
	array(str_contains($formulaire, 'association_ventes_compte_lire('), "le formulaire doit utiliser la résolution métier"),
	array(str_contains($suppression, "objet='asso_vente'"), "la suppression doit cibler les liens canoniques"),
	array(!preg_match('/function (?:compte_vente|compte_vente_frais_envoi|modifier_compte_vente|modifier_activite_vente_frais_envoi)\s*\(/', $comptes), "Comptabilité ne doit plus posséder la logique Ventes"),
);

foreach ($assertions as [$ok, $message]) {
	if (!$ok) {
		fwrite(STDERR, "ECHEC: {$message}.\n");
		exit(1);
	}
}

echo "OK: le cycle comptable Ventes appartient au plugin métier.\n";
