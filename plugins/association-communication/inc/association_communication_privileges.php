<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_communication_listes_normaliser($listes) {
	if (!is_array($listes)) {
		$listes = preg_split('/[;,\s]+/', trim((string) $listes), -1, PREG_SPLIT_NO_EMPTY);
	}
	return array_values(array_unique(array_filter(array_map('strval', $listes))));
}

function association_communication_abonne_lire($email) {
	include_spip('inc/mailsubscribers');
	include_spip('mailsubscribers_fonctions');
	return sql_fetsel(
		'email,nom,id_mailsubscriber,statut',
		'spip_mailsubscribers',
		'email=' . sql_quote($email) . ' OR email=' . sql_quote(mailsubscribers_obfusquer_email($email))
	) ?: [];
}

function association_communication_abonnements_lire($id_mailsubscriber) {
	$id_mailsubscriber = (int) $id_mailsubscriber;
	if (!$id_mailsubscriber) {
		return [];
	}
	return sql_allfetsel(
		'mailsubscriptions.id_mailsubscriber,mailsubscriptions.statut AS statut_subscription,'
			. 'mailsubscribinglists.identifiant AS identifiant_list,mailsubscribinglists.statut AS statut_list',
		'spip_mailsubscriptions AS mailsubscriptions LEFT JOIN spip_mailsubscribinglists AS mailsubscribinglists'
			. ' ON mailsubscriptions.id_mailsubscribinglist=mailsubscribinglists.id_mailsubscribinglist',
		'mailsubscriptions.id_mailsubscriber=' . $id_mailsubscriber
	) ?: [];
}

function association_communication_newsletter_options(array $auteur) {
	return [
		'lang' => $GLOBALS['spip_lang'] ?? '',
		'notify' => false,
		'force' => false,
		'nom' => trim((string) ($auteur['prenom'] ?? '') . ' ' . (string) ($auteur['nom_famille'] ?? '')),
	];
}

function association_communication_privileges_activer(array $auteur, $listes_defaut = []) {
	$email = trim((string) ($auteur['email'] ?? ''));
	include_spip('inc/filtres');
	if ($email === '' || !email_valide($email)) {
		return true;
	}
	$subscribe = charger_fonction('subscribe', 'newsletter');
	$unsubscribe = charger_fonction('unsubscribe', 'newsletter');
	$options = association_communication_newsletter_options($auteur);
	$options['listes'] = ['statut_interne_ok'];
	$subscribe($email, $options);
	$listes_defaut = association_communication_listes_normaliser($listes_defaut);
	if ($listes_defaut) {
		$options['listes'] = $listes_defaut;
		$subscribe($email, $options);
	}
	$abonne = association_communication_abonne_lire($email);
	foreach (association_communication_abonnements_lire((int) ($abonne['id_mailsubscriber'] ?? 0)) as $abonnement) {
		$identifiant = (string) ($abonnement['identifiant_list'] ?? '');
		if (strpos($identifiant, 'statut_interne') !== false
			&& $identifiant !== 'statut_interne_' . (string) ($auteur['statut_interne'] ?? '')
			&& ($abonnement['statut_subscription'] ?? '') === 'valide') {
			$options['listes'] = [$identifiant];
			$unsubscribe($email, $options);
		}
		if (($abonnement['statut_list'] ?? '') === 'ouverte' && ($abonnement['statut_subscription'] ?? '') !== 'valide') {
			$options['listes'] = [$identifiant];
			$subscribe($email, $options);
		}
	}
	return true;
}

function association_communication_privileges_desactiver(array $auteur) {
	$email = trim((string) ($auteur['email'] ?? ''));
	include_spip('inc/filtres');
	if ($email === '' || !email_valide($email)) {
		return true;
	}
	$subscribe = charger_fonction('subscribe', 'newsletter');
	$unsubscribe = charger_fonction('unsubscribe', 'newsletter');
	$options = association_communication_newsletter_options($auteur);
	$abonne = association_communication_abonne_lire($email);
	$id_abonne = (int) ($abonne['id_mailsubscriber'] ?? 0);
	if ($id_abonne) {
		sql_delete('spip_mailsubscriptions', "statut='refuse' AND id_mailsubscriber=" . $id_abonne);
	}
	$options['listes'] = ['statut_interne_echu'];
	$subscribe($email, $options);
	$options['listes'] = ['statut_interne_ok'];
	$unsubscribe($email, $options);
	foreach (association_communication_abonnements_lire($id_abonne) as $abonnement) {
		$identifiant = (string) ($abonnement['identifiant_list'] ?? '');
		if (strpos($identifiant, 'statut_interne') === false
			&& ($abonnement['statut_list'] ?? '') === 'ouverte'
			&& ($abonnement['statut_subscription'] ?? '') === 'valide') {
			$options['listes'] = [$identifiant];
			$unsubscribe($email, $options);
		}
	}
	return true;
}

function association_communication_privileges_verifier(array $auteur, $listes_defaut = []) {
	$email = trim((string) ($auteur['email'] ?? ''));
	include_spip('inc/filtres');
	if ($email === '' || !email_valide($email)) {
		return true;
	}
	$subscribe = charger_fonction('subscribe', 'newsletter');
	$unsubscribe = charger_fonction('unsubscribe', 'newsletter');
	$options = association_communication_newsletter_options($auteur);
	$listes_defaut = association_communication_listes_normaliser($listes_defaut);
	$abonne = association_communication_abonne_lire($email);
	$id_abonne = (int) ($abonne['id_mailsubscriber'] ?? 0);
	$abonnements = association_communication_abonnements_lire($id_abonne);
	$liste_statut = 'statut_interne_' . (string) ($auteur['statut_interne'] ?? '');
	$liste_statut_valide = false;
	$liste_defaut_valide = false;
	foreach ($abonnements as $abonnement) {
		$identifiant = (string) ($abonnement['identifiant_list'] ?? '');
		$valide = ($abonnement['statut_subscription'] ?? '') === 'valide';
		if ($identifiant === $liste_statut && $valide) {
			$liste_statut_valide = true;
		} elseif (strpos($identifiant, 'statut_interne') !== false && $valide) {
			$options['listes'] = [$identifiant];
			$unsubscribe($email, $options);
		}
		if ($valide && in_array($identifiant, $listes_defaut, true)) {
			$liste_defaut_valide = true;
		}
	}
	if (!$liste_statut_valide) {
		$options['listes'] = [$liste_statut];
		$subscribe($email, $options);
	}
	if ($listes_defaut && !$liste_defaut_valide) {
		$options['listes'] = $listes_defaut;
		$subscribe($email, $options);
	}

	$statut_interne = (string) ($auteur['statut_interne'] ?? '');
	$statut_spip = (string) ($auteur['statut'] ?? '');
	if (in_array($statut_interne, ['prospect', 'echu', 'relance'], true) && $statut_spip !== '5poubelle') {
		foreach ($abonnements as $abonnement) {
			if (($abonnement['statut_list'] ?? '') === 'ouverte' && ($abonnement['statut_subscription'] ?? '') === 'valide') {
				$options['listes'] = [(string) $abonnement['identifiant_list']];
				$unsubscribe($email, $options);
			}
		}
	} elseif ($statut_interne === 'sorti' || $statut_spip === '5poubelle') {
		$options['listes'] = [];
		$unsubscribe($email, $options);
		if ($id_abonne) {
			sql_delete('spip_mailsubscriptions', 'id_mailsubscriber=' . $id_abonne);
			sql_delete('spip_mailsubscribers', 'id_mailsubscriber=' . $id_abonne);
		}
	}
	return true;
}
