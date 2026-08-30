<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }

function association_bons_plans_association_configuration_navigation($flux) {
	$flux['data']['bons_plans'] = array('ordre' => 59, 'label' => 'association_bons_plans:configuration_titre');
	return $flux;
}

function association_bons_plans_association_configuration_saisies($flux) {
	if (($flux['args']['config'] ?? '') !== 'bons_plans') { return $flux; }
	$flux['data'][] = array('ordre' => 10, 'saisies' => array(array(
		'saisie' => 'fieldset',
		'options' => array('nom' => 'bons_plans', 'label' => _T('association_bons_plans:configuration_titre')),
		'saisies' => array(
			array('saisie' => 'input', 'options' => array('nom' => 'bons_plans_titre_public', 'label' => _T('association_bons_plans:configuration_titre_public'), 'defaut' => _T('association_bons_plans:bons_plans'))),
			array('saisie' => 'input', 'options' => array('nom' => 'bons_plans_moderateurs', 'label' => _T('association_bons_plans:configuration_moderateurs'), 'explication' => _T('association_bons_plans:configuration_moderateurs_explication'))),
		),
	)));
	return $flux;
}

function association_bons_plans_association_capacites($capacites) {
	$capacites['bons_plans'] = array(
		'plugin' => 'association_bons_plans', 'objet' => 'bon_plan',
		'proposition_publique' => true, 'moderation' => true, 'depublication' => true,
	);
	return $capacites;
}

function association_bons_plans_affiche_enfants($flux) {
	include_spip('inc/pipelines_ecrire');
	$exec = trouver_objet_exec($flux['args']['exec'] ?? '');
	if (($exec['type'] ?? '') === 'rubrique' && empty($exec['edition'])) {
		$id_rubrique = (int) ($flux['args']['id_rubrique'] ?? 0);
		$lister = charger_fonction('lister_objets', 'inc');
		$flux['data'] .= $lister('bons_plans', array('titre' => _T('association_bons_plans:bons_plans_rubrique'), 'id_rubrique' => $id_rubrique, 'par' => 'titre'));
	}
	return $flux;
}

function association_bons_plans_affiche_milieu($flux) {
	include_spip('inc/pipelines_ecrire');
	$exec = trouver_objet_exec($flux['args']['exec'] ?? '');
	$type = $exec['type'] ?? '';
	if (!empty($exec['edition']) || !in_array($type, array('bon_plan', 'auteur'), true)) { return $flux; }
	$id = (int) ($flux['args'][$exec['id_table_objet']] ?? 0);
	$table_source = $type === 'bon_plan' ? 'auteurs' : 'bons_plans';
	$texte = recuperer_fond('prive/objets/editer/liens', array('table_source' => $table_source, 'objet' => $type, 'id_objet' => $id));
	$flux['data'] .= $texte;
	return $flux;
}

function association_bons_plans_objet_compte_enfants($flux) {
	if (($flux['args']['objet'] ?? '') === 'rubrique' && ($id = (int) ($flux['args']['id_objet'] ?? 0))) {
		$where = array('id_rubrique=' . $id, ($flux['args']['statut'] ?? '') === 'publie' ? "statut='publie'" : "statut<>'poubelle'");
		$flux['data']['bons_plans'] = sql_countsel('spip_bons_plans', $where);
	}
	return $flux;
}

function association_bons_plans_optimiser_base_disparus($flux) {
	include_spip('action/editer_liens');
	$flux['data'] += objet_optimiser_liens(array('bon_plan' => '*'), '*');
	return $flux;
}
