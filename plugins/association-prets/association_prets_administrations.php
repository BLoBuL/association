<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
function association_prets_upgrade($meta, $cible) {
	include_spip('base/upgrade');
	maj_plugin($meta, $cible, [
		'create' => [['maj_tables', ['spip_asso_ressources', 'spip_asso_prets']]],
		'1.1.0' => [['maj_tables', ['spip_asso_prets']]],
		'1.1.1' => [['association_prets_migrer_identifiants_relationnels']],
	]);
}

/**
 * Convertit les anciennes colonnes textuelles en identifiants SQL natifs.
 *
 * Une valeur ambiguë bloque volontairement la migration : elle doit être
 * arbitrée avant toute conversion destructive.
 */
function association_prets_migrer_identifiants_relationnels() {
	$lignes = sql_allfetsel('id_pret, id_ressource, id_emprunteur', 'spip_asso_prets');
	foreach ($lignes ?: [] as $ligne) {
		$id_ressource = trim((string) ($ligne['id_ressource'] ?? ''));
		$id_emprunteur = trim((string) ($ligne['id_emprunteur'] ?? ''));
		if (!ctype_digit($id_ressource) || intval($id_ressource) <= 0
			|| !ctype_digit($id_emprunteur) || intval($id_emprunteur) <= 0
			|| !sql_countsel('spip_asso_ressources', 'id_ressource=' . intval($id_ressource))) {
			throw new RuntimeException('Identifiants de prêt invalides pour id_pret=' . intval($ligne['id_pret'] ?? 0));
		}
	}

	$type_serveur = strtolower((string) ($GLOBALS['connexions'][0]['type'] ?? ''));
	if (!str_starts_with($type_serveur, 'sqlite')) {
		if (sql_alter('TABLE spip_asso_prets MODIFY id_ressource BIGINT NOT NULL') === false
			|| sql_alter('TABLE spip_asso_prets MODIFY id_emprunteur BIGINT NOT NULL') === false) {
			throw new RuntimeException('Impossible de convertir les identifiants de prêts en BIGINT.');
		}
	}
	maj_tables(['spip_asso_prets']);
}
function association_prets_vider_tables($meta) {
	effacer_meta($meta);
}
