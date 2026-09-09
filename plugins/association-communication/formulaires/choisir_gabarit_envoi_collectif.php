<?php

/*\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\*/
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/saisies');
include_spip('inc/filtres');
function formulaires_choisir_gabarit_envoi_collectif_saisies() {
	$gabarit_email_collectifs = sql_select('page, titre', 'spip_articles', "page LIKE 'email_collectif_%'");
	$gabarits = [];
	while ($gabarit_email_collectif = sql_fetch($gabarit_email_collectifs)) {
		$gabarits += [$gabarit_email_collectif['page'] => $gabarit_email_collectif['titre']];
	}
	if (!empty($gabarits)) {
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'fieldset_select_gabarit_nom',
				'label' => '<:association_communication:email_collectif_select_gabarit_explication:>',
			],
			'saisies' => [
				[
					'saisie' => 'selection',
					'options' => [
						'label' => '<:association_communication:email_collectif_select_gabarit_titre:>',
						'nom' => 'select_gabarit',
						// 'conteneur_class' => 'date_debut',
						'explication' => '<:association_communication:email_collectif_sujet_explication:>',
						'attributs' => "onchange=window.location.href=window.location.href+'&'+this.name+'='+this.value",
						'data' => $gabarits,
					],
				],
			],
		];
	} else {
		$saisies[] = [
			'saisie' => 'fieldset',
			'options' => [
				'nom' => 'fieldset_select_gabarit_nom',
				'label' => '<:association_communication:email_collectif_select_gabarit_explication:>',
			],
		];
	}
	return $saisies;
}
function formulaires_choisir_gabarit_envoi_collectif_charger_dist() {
	$contexte = [];
	$selected_gabarit = _request('select_gabarit');
	$contenu_gabarit = [];
	if (!empty($selected_gabarit)) {
		$email_collectif = sql_fetsel('*', 'spip_articles', "page = '$selected_gabarit'");
		// $contenu_gabarit['visuel_principal'] = $email_collectif['logo_article'];
		$contenu_gabarit['sujet'] = ($email_collectif['soustitre']) ? $email_collectif['soustitre'] : $email_collectif['titre'];
		$contenu_gabarit['titre'] = $email_collectif['titre'];
		$contenu_gabarit['chapeau'] = $email_collectif['chapo'];
		$contenu_gabarit['texte'] = $email_collectif['texte'];
		// $contexte['contenu_gabarit'] = $contenu_gabarit;
		$contexte['selected_gabarit'] = $contenu_gabarit;
		$contexte += array_merge($contenu_gabarit, ['selected_gabarit' => $selected_gabarit]);
	}
	return $contexte;
}
/*function formulaires_choisir_gabarit_envoi_collectif_traiter_dist(){
	return $res;
}*/
