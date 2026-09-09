<?php

/**
 * Contrats d'intégration facultative entre les plugins Association.
 *
 * @plugin Association
 * @licence GPL-3.0-or-later
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Indique si un plugin est actif sans créer de dépendance vers son code.
 */
function association_plugin_actif(string $prefixe): bool {
	if (!function_exists('test_plugin_actif')) {
		include_spip('inc/plugin');
	}

	return function_exists('test_plugin_actif') && test_plugin_actif($prefixe);
}

/**
 * Retourne les capacités publiées par les modules actifs.
 *
 * Une capacité est indexée par un identifiant stable et contient au minimum
 * le préfixe du plugin fournisseur.
 */
function association_capacites_lister(): array {
	$capacites = pipeline('association_capacites', []);

	return is_array($capacites) ? $capacites : [];
}

function association_capacite_disponible(string $capacite): bool {
	$capacites = association_capacites_lister();

	return !empty($capacites[$capacite]);
}

/**
 * Enrichit un participant. Sans Adhésions, le profil reste universel.
 */
function association_profil_participant(array $participant): array {
	$participant += [
		'id_auteur' => 0,
		'profil' => 'public',
		'est_membre' => false,
		'famille' => [],
		'tarifs' => ['indifferent', 'non_adherent'],
	];
	$resultat = pipeline('association_profil_participant', $participant);

	return is_array($resultat) ? $resultat : $participant;
}

/**
 * Retourne le contexte familial facultatif d'un auteur ou d'un objet métier.
 *
 * Sans Familles, la réponse reste exploitable et indique simplement que la
 * capacité n'est pas disponible. Les consommateurs ne doivent jamais lire
 * directement les tables du plugin maison.
 */
function association_contexte_familial(array $contexte): array {
	$contexte += [
		'id_auteur' => 0,
		'objet' => '',
		'id_objet' => 0,
		'disponible' => false,
		'id_famille' => 0,
		'famille' => [],
		'familles' => [],
		'membres' => [],
		'role' => '',
	];
	$resultat = pipeline('association_contexte_familial', $contexte);

	return is_array($resultat) ? $resultat : $contexte;
}

/**
 * Demande facultativement la création ou la synchronisation d'un contrat.
 *
 * L'objet métier et sa commande doivent exister avant cet appel. L'absence du
 * plugin Contrats n'invalide jamais l'opération métier d'origine.
 */
function association_demander_contrat(array $demande): array {
	$demande += [
		'action' => 'synchroniser',
		'objet' => '',
		'id_objet' => 0,
		'id_contrat' => 0,
		'id_contrat_type' => 0,
		'id_commande' => 0,
		'contrat_cree' => false,
		'erreur' => '',
	];
	$resultat = pipeline('association_contrat_demander', $demande);

	return is_array($resultat) ? $resultat : $demande;
}

/**
 * Demande facultativement la comptabilisation d'une opération métier.
 *
 * L'objet métier est toujours enregistré avant cet appel. En l'absence de
 * Comptabilité, le résultat est valide avec id_compte=0.
 */
function association_comptabiliser_operation(array $operation): array {
	$operation += [
		'action' => 'synchroniser',
		'objet' => '',
		'id_objet' => 0,
		'id_compte' => 0,
		'comptabilisee' => false,
		'erreur' => '',
	];
	$resultat = pipeline('association_comptabiliser_operation', $operation);

	return is_array($resultat) ? $resultat : $operation;
}

/**
 * Enregistre ou synchronise facultativement une vente.
 *
 * Le producteur transmet des instantanés métier suffisants pour que Ventes ne
 * dépende ni du catalogue ni de la commande après l'enregistrement. Sans le
 * plugin Ventes, l'opération source reste valide et aucun identifiant n'est
 * créé.
 */
function association_enregistrer_vente(array $vente): array {
	$vente += [
		'id_vente' => 0,
		'enregistree' => false,
		'erreur' => '',
	];
	$resultat = pipeline('association_enregistrer_vente', $vente);

	return is_array($resultat) ? $resultat : $vente;
}

/**
 * Publie une notification métier sans imposer Communication.
 */
function association_notifier_metier(array $notification): array {
	$notification += ['envoyee' => false, 'erreur' => ''];
	$resultat = pipeline('association_notifier_metier', $notification);

	return is_array($resultat) ? $resultat : $notification;
}

/**
 * Programme un envoi collectif facultatif, sans abonner ses destinataires.
 */
function association_programmer_campagne(array $campagne): array {
	$campagne += ['sujet' => '', 'html' => '', 'destinataires' => [], 'options' => [], 'id_mailshot' => 0];
	$resultat = pipeline('association_programmer_campagne', $campagne);
	return is_array($resultat) ? $resultat : $campagne;
}
