<?php

$racine = dirname(__DIR__);
$api = file_get_contents($racine . '/plugins/association-evenements/inc/association_evenements_comptabilite.php');
$formulaire = file_get_contents($racine . '/plugins/association-evenements/formulaires/desinscription_evenement_public.php');
$action = file_get_contents($racine . '/plugins/association-evenements/action/gerer_activites.php');
$comptes = file_get_contents($racine . '/plugins/association-compta/inc/comptes.php');

if (!str_contains($api, 'function association_evenements_comptes_supprimer_inscription(')
	|| !str_contains($api, "objet='evenement'")
	|| !str_contains($api, 'spip_asso_destination_op')
	|| !str_contains($api, 'function association_evenements_compte_valider_transaction(')
	|| !str_contains($formulaire, 'association_evenements_comptes_supprimer_inscription(')
	|| !str_contains($action, 'association_evenements_comptes_supprimer_inscription(')
	|| str_contains($comptes, 'function supprimer_compte_activite(')
	|| str_contains($comptes, 'function valider_compte_activite(')
) {
	fwrite(STDERR, "La suppression comptable d'une inscription n'appartient pas entièrement à Événements.\n");
	exit(1);
}

echo "OK: la désinscription supprime ses écritures par le lien événement/transaction.\n";
