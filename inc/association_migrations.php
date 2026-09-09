<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Ajoute des étapes au registre historique sans dépendre de l'ordre des plugins.
 *
 * Chaque étape contient version, opération et priorité. Une opération nulle
 * conserve un jalon historique vide.
 */
function association_migrations_ajouter($flux, $module, $etapes) {
	foreach ($etapes as $etape) {
		$flux[] = [
			'version' => (string) $etape[0],
			'operation' => $etape[1],
			'priorite' => (int) ($etape[2] ?? 100),
			'module' => $module,
		];
	}
	return $flux;
}

/**
 * Assemble le tableau maj_plugin() historique de façon déterministe.
 */
function association_migrations_construire() {
	$contributions = [
		[
			'version' => '1.1.0',
			'operation' => ['maj_tables', ['spip_association_metas']],
			'priorite' => 0,
			'module' => 'association',
		],
		['version' => '1.2.7', 'operation' => null, 'priorite' => 0, 'module' => 'association'],
	];
	$contributions = pipeline('association_migrations_historiques', $contributions);

	$par_version = [];
	foreach ($contributions as $contribution) {
		$version = $contribution['version'];
		$par_version[$version][] = $contribution;
	}
	uksort($par_version, 'version_compare');

	$maj = [
		'create' => [['maj_tables', ['spip_association_metas']]],
	];
	foreach ($par_version as $version => $operations) {
		usort($operations, function ($a, $b) {
			$ordre = $a['priorite'] <=> $b['priorite'];
			return $ordre ?: strcmp($a['module'], $b['module']);
		});
		$maj[$version] = [];
		foreach ($operations as $operation) {
			if ($operation['operation'] !== null) {
				$maj[$version][] = $operation['operation'];
			}
		}
	}
	return $maj;
}
