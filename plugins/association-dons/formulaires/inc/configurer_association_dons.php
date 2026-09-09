<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function association_dons_configurer_saisies($config): array {
	if ($config !== 'info' && $config !== 'recu_fiscal' && !empty($config)) {
		return [];
	}
	$saisies = [];
	foreach (['objet', 'qualite', 'signataire_nom', 'signataire_fonction'] as $champ) {
		$saisies[] = [
			'saisie' => in_array($champ, ['objet', 'qualite'], true) ? 'textarea' : 'input',
			'options' => [
				'nom' => 'recu_fiscal_' . $champ,
				'label' => _T('association_dons:recu_fiscal_' . $champ),
			],
		];
	}
	return [[
		'saisie' => 'fieldset',
		'options' => [
			'nom' => 'recu_fiscal_emetteur',
			'label' => _T('association_dons:recu_fiscal_emetteur'),
			'explication' => _T('association_dons:recu_fiscal_emetteur_explication'),
		],
		'saisies' => $saisies,
	]];
}
