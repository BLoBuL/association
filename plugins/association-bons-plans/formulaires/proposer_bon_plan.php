<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
function formulaires_proposer_bon_plan_charger_dist($id_rubrique = 0, $retour = '') {
	$charger = charger_fonction('charger', 'formulaires/editer_bon_plan');
	$valeurs = $charger('new', $id_rubrique, $retour);
	if (empty($GLOBALS['visiteur_session']['id_auteur'])) { $valeurs['editable'] = false; $valeurs['message_erreur'] = _T('association_bons_plans:connexion_requise'); }
	return $valeurs;
}
function formulaires_proposer_bon_plan_verifier_dist($id_rubrique = 0, $retour = '') {
	$verifier = charger_fonction('verifier', 'formulaires/editer_bon_plan');
	return $verifier('new', $id_rubrique, $retour);
}
function formulaires_proposer_bon_plan_traiter_dist($id_rubrique = 0, $retour = '') {
	$traiter = charger_fonction('traiter', 'formulaires/editer_bon_plan');
	$res = $traiter('new', $id_rubrique, $retour);
	$id = (int) ($res['id_bon_plan'] ?? 0);
	$id_auteur = (int) ($GLOBALS['visiteur_session']['id_auteur'] ?? 0);
	if ($id) {
		include_spip('action/editer_objet');
		objet_modifier('bon_plan', $id, array('statut' => 'prop'));
		if ($id_auteur) { include_spip('action/editer_liens'); objet_associer(array('bon_plan' => $id), array('auteur' => $id_auteur)); }
		include_spip('inc/association_capacites');
		include_spip('inc/config');
		association_notifier_metier(array(
			'type' => 'bon_plan_propose', 'objet' => 'bon_plan', 'id_objet' => $id, 'id_auteur' => $id_auteur,
			'fonction' => 'association_bons_plans_notifier_proposition',
			'arguments' => array($id, lire_config('association/bons_plans_moderateurs', '')),
		));
		$res['message_ok'] = _T('association_bons_plans:proposition_recue');
	}
	return $res;
}

function association_bons_plans_notifier_proposition($id_bon_plan, $destinataires) {
	$emails = array_filter(array_map('trim', preg_split('/[,;]+/', (string) $destinataires)));
	$emails = array_values(array_filter($emails, 'email_valide'));
	if (!$emails) { return false; }
	$titre = (string) sql_getfetsel('titre', 'spip_bons_plans', 'id_bon_plan=' . intval($id_bon_plan));
	$sujet = _T('association_bons_plans:notification_sujet', array('titre' => $titre));
	$message = _T('association_bons_plans:notification_texte', array(
		'titre' => $titre,
		'url' => url_absolue(generer_url_ecrire('bon_plan', 'id_bon_plan=' . intval($id_bon_plan))),
	));
	$envoyer_mail = charger_fonction('envoyer_mail', 'inc');
	$succes = true;
	foreach ($emails as $email) { $succes = (bool) $envoyer_mail($email, $sujet, $message) && $succes; }
	return $succes;
}
