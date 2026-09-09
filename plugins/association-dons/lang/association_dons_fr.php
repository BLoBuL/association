<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$traductions = [
	'recu_fiscal_apercu' => 'Aperçu du document de dons',
	'recu_fiscal_specimen' => 'SPÉCIMEN — NON VALABLE FISCALEMENT',
	'recu_fiscal_emission_indisponible' => 'L’émission définitive reste indisponible : les versements éligibles, le registre de numérotation et la signature doivent encore être qualifiés. Cet aperçu ne justifie aucun avantage fiscal.',
	'recu_fiscal_emetteur_incomplet' => 'Complétez les informations communes de l’association et celles de l’organisme émetteur dans la configuration avant de générer un aperçu.',
	'recu_fiscal_donateur' => 'Donateur',
	'recu_fiscal_montant_apercu' => 'Montant repris pour cet aperçu, à contrôler',
	'recu_fiscal_signature_absente' => 'Aucune signature apposée. Document de préparation uniquement.',
	'recu_fiscal_emetteur' => 'Organisme émetteur des reçus fiscaux',
	'recu_fiscal_emetteur_explication' => 'L’identité, l’adresse complète, le pays et le numéro SIREN, RNA ou d’enregistrement sont repris des informations communes de l’association ci-dessus. Renseignez ici son objet, sa qualité fiscale exacte et le signataire habilité. Ces informations ne constituent ni une validation de l’éligibilité fiscale de l’organisme, ni une signature électronique.',
	'recu_fiscal_objet' => 'Objet de l’organisme',
	'recu_fiscal_qualite' => 'Qualité fiscale de l’organisme et références des agréments applicables',
	'recu_fiscal_signataire_nom' => 'Nom du signataire habilité',
	'recu_fiscal_signataire_fonction' => 'Fonction du signataire',
	'recu_fiscal_aucun_don' => 'Aucun don validé ne permet de produire un reçu fiscal pour l’année @annee@.',
	'action' => 'Action',
	'ajouter_un_don' => 'Ajouter un don',
	'annee' => 'Année',
	'argent' => 'Argent',
	'colis' => 'Colis',
	'confirmer_suppression' => 'Confirmer la suppression ?',
	'contre_valeur_en_e__' => 'Contre-valeur &nbsp;:',
	'contrepartie' => 'Contrepartie',
	'date' => 'Date',
	'date_aaaa_mm_jj' => 'Date (JJ/MM/AAAA)&nbsp;:',
	'don_aucun' => 'Aucun don pour cette année.',
	'don_compteur_plusieurs' => '@nb@ dons',
	'don_compteur_un' => 'Un don',
	'don_financier_en_e__' => 'Don financier &nbsp;:',
	'dons_titre_mise_a_jour' => 'Mise à jour des dons',
	'erreur_id_adherent' => 'Ce numéro de membre ne correspond à aucun membre de l’association',
	'erreur_montant' => 'Les valeurs négatives ne sont pas autorisées',
	'erreur_titre' => 'Votre saisie contient des erreurs !',
	'geste_association' => 'Geste de l’association&nbsp;:',
	'id' => '#',
	'mettre_a_jour_le_don' => 'Mettre à jour le don',
	'nd_de_membre' => 'N&deg; de membre&nbsp;:',
	'nom' => 'Nom',
	'nom_du_bienfaiteur' => 'Nom du bienfaiteur&nbsp;:',
	'remarques' => 'Remarques&nbsp;:',
	'supprimer_le_don' => 'Supprimer le don',
	'titre_onglet_dons' => 'Dons',
	'tous_les_dons' => 'Tous les dons',
	'valeur' => 'Valeur',
];

// SPIP 4.0 ignore la valeur de retour des fichiers de langue.
if (!function_exists('lire_fichier_langue')) {
	$GLOBALS[$GLOBALS['idx_lang']] = $traductions;
}
return $traductions;
