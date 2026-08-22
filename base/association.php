<?php
/**
 * Plugin Association
 * (c) 2004-2023 SPIP
 * Distribue sous licence GNU/GPL
 *
 * @package SPIP\association\base
 */
if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Complète la déclaration des objets SQL utilisés par Association.
 *
 * Le plugin Mailsubscribers ne doit pas exposer de page publique dédiée sur
 * les sites Association.
 *
 * @param array $tables
 * @return array
 */
function association_declarer_tables_objets_sql($tables) {
	if (isset($tables['spip_mailsubscribers'])) {
		unset($tables['spip_mailsubscribers']['url']);
		$tables['spip_mailsubscribers']['page'] = '';
	}

	return $tables;
}

## TABLES PRINCIPALES
function association_declarer_tables_principales($tables_principales) {
	return $tables_principales;
};
## TABLES AUXILIAIRES
function association_declarer_tables_auxiliaires($tables_auxiliaires){
	//-- Table METAS ------------------------------------------
	$spip_asso_metas = array(
			"nom"		=> "VARCHAR(255) NOT NULL",
			"valeur"	=> "TEXT DEFAULT ''",
			"impt"		=> "ENUM('non', 'oui') DEFAULT 'oui' NOT NULL",
			"maj"		=> "TIMESTAMP"
	);
	$spip_asso_metas_key = array(
			"PRIMARY KEY"	=> "nom"
	);
	$tables_auxiliaires['spip_association_metas'] = array(
			'field' => &$spip_asso_metas, 'key' => &$spip_asso_metas_key);
	return $tables_auxiliaires;
}
/**
 * Déclare des champs extras pour l'association.
 *
 * Cette fonction inclut le fichier contenant la déclaration des champs extras
 * spécifiques à l'association et appelle une implémentation pour les ajouter
 * au tableau des champs extras existants.
 *
 * @param array $champs Tableau des champs extras existants.
 * @return array Tableau des champs extras mis à jour avec ceux de l'association.
 */
function association_declarer_champs_extras($champs) {
    // Pendant l'installation initiale, le chemin du plugin peut ne pas encore
    // être recalculé par SPIP alors que ce pipeline est déjà exécuté.
    // Charger le fichier frère explicitement évite de dépendre de ce cache.
    include_once __DIR__ . '/association_champs_extras.php';
    // Appelle la fonction d'implémentation pour ajouter les champs extras de l'association.
    $champs = association_declarer_champs_extras_impl($champs);

    // Saisies 6 évalue les enfants d'un fieldset dans leur propre sous-contexte.
    // Une condition strictement identique à celle du parent est redondante et
    // provoque alors un faux « Champ ... inexistant » sur les vues CExtras.
    foreach ($champs as $table => $saisies) {
        if (is_array($saisies)) {
            $champs[$table] = association_champs_extras_dedoublonner_afficher_si($saisies);
        }
    }

    return $champs;
}

/**
 * Supprimer les conditions d'affichage redondantes des enfants d'un fieldset.
 *
 * Le parent porte déjà la même contrainte : la retirer de l'enfant ne change
 * donc pas l'affichage, mais rend la structure compatible avec les vues de
 * Saisies 6 utilisées par Champs Extras sous SPIP 4.
 *
 * @param array $saisies
 * @param string $condition_parent
 * @return array
 */
function association_champs_extras_dedoublonner_afficher_si($saisies, $condition_parent = '') {
    foreach ($saisies as $cle => $saisie) {
        if (!is_array($saisie)) {
            continue;
        }

        $condition = trim((string) ($saisie['options']['afficher_si'] ?? ''));
        if ($condition_parent !== '' && $condition === $condition_parent) {
            unset($saisies[$cle]['options']['afficher_si']);
            $condition = '';
        }

        if (!empty($saisie['saisies']) && is_array($saisie['saisies'])) {
            $condition_enfants = $condition !== '' ? $condition : $condition_parent;
            $saisies[$cle]['saisies'] = association_champs_extras_dedoublonner_afficher_si(
                $saisie['saisies'],
                $condition_enfants
            );
        }
    }

    return $saisies;
}
