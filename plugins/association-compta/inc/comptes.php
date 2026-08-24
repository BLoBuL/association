<?php
include_spip('inc/destinations');

/**
 * Prépare une liste des classes du plan associatif.
 *
 * Cette fonction récupère toutes les classes de la table `spip_asso_plan`
 * et les reformate en fonction du format demandé.
 *
 * @param string $format Le format de sortie souhaité.
 *                       Par défaut, 'data_saisies'. Les options possibles sont :
 *                       - 'data_saisies' : Retourne une chaîne formatée pour les saisies.
 *                       - tout autre valeur : Retourne un tableau associatif brut.
 *
 * @return string|array Retourne les données dans le format demandé :
 *                      - Une chaîne formatée si `$format` est 'data_saisies'.
 *                      - Un tableau associatif brut sinon.
 */
function preparer_liste_asso_plan_classe($format = 'data_saisies') {
    // Récupère toutes les classes de la table `spip_asso_plan`.
    $asso_plans = sql_allfetsel('classe', 'spip_asso_plan', '');

    // Initialise un tableau vide pour stocker les données reformattées.
    $asso_plans_array = array();

    // Parcourt les données récupérées et les reformate en tableau associatif.
    foreach ($asso_plans as $asso_plan) {
        $asso_plans_array[$asso_plan['classe']] = $asso_plan['classe'];
    }

    // Retourne les données dans le format demandé.
    if ($format == 'data_saisies') {
        // Convertit le tableau en chaîne formatée pour les saisies.
        return saisies_tableau2chaine($asso_plans_array);
    } else {
        // Retourne le tableau associatif brut.
        return $asso_plans_array;
    }
}
/**
 * Prépare une liste des comptes du plan associatif.
 *
 * Cette fonction récupère les comptes de la table `spip_asso_plan` et les reformate
 * en fonction du format demandé. Elle peut également filtrer les comptes par classe.
 *
 * @param string $format Le format de sortie souhaité.
 *                       Par défaut, 'data_saisies'. Les options possibles sont :
 *                       - 'data_saisies' : Retourne une chaîne formatée pour les saisies.
 *                       - tout autre valeur : Retourne un tableau associatif brut.
 * @param string $classe (optionnel) La classe des comptes à filtrer. Si vide, tous les comptes sont récupérés.
 *
 * @return string|array Retourne les données dans le format demandé :
 *                      - Une chaîne formatée si `$format` est 'data_saisies'.
 *                      - Un tableau associatif brut sinon.
 */
function preparer_liste_asso_plan_compte($format = 'data_saisies', $classe = '') {
    // Si une classe est spécifiée, ajoute une condition WHERE pour la requête SQL.
    if ($classe) {
        $where = "classe='$classe'";
    } else {
        $where = '';
    }

    // Récupère les données de la table `spip_asso_plan` en fonction de la condition WHERE.
    $asso_plans = sql_allfetsel('classe,code,intitule', 'spip_asso_plan', $where, '', 'code');

    // Initialise un tableau vide pour stocker les données reformattées.
    $asso_plans_array = array();

    // Parcourt les données récupérées et les reformate en tableau associatif.
    foreach ($asso_plans as $asso_plan) {
        $asso_plans_array[$asso_plan['code']] = $asso_plan['classe'] . ' - ' . $asso_plan['code'] . ' - ' . $asso_plan['intitule'];
    }

    // Retourne les données dans le format demandé.
    if ($format == 'data_saisies') {
        // Convertit le tableau en chaîne formatée pour les saisies.
        return saisies_tableau2chaine($asso_plans_array);
    } else {
        // Retourne le tableau associatif brut.
        return $asso_plans_array;
    }
}
/**
 * Indique si une table dispose d'un champ donne.
 *
 * @param string $table
 * @param string $champ
 * @return bool
 */
function association_comptes_table_a_champ($table, $champ) {
    $desc = sql_showtable($table, true);
    return !empty($desc['field']) && isset($desc['field'][$champ]);
}

/**
 * Retourne une date utilisable en comptabilite.
 *
 * @param string|null $date
 * @return string
 */
function association_comptes_date_normalisee($date) {
    $date = trim((string)$date);
    if (
        $date === ''
        || $date === '0000-00-00'
        || $date === '0000-00-00 00:00:00'
    ) {
        return '';
    }

    $timestamp = strtotime($date);
    return $timestamp ? date('Y-m-d', $timestamp) : '';
}

/**
 * Recupere la derniere transaction rattachee a une commande.
 *
 * @param int $id_commande
 * @param int $id_transaction
 * @return array
 */
function association_commande_comptable_transaction($id_commande, $id_transaction = 0) {
    $id_commande = intval($id_commande);
    $id_transaction = intval($id_transaction);
    if (!sql_showtable('spip_transactions', true)) {
        return array();
    }

    if ($id_transaction > 0) {
        $transaction = sql_fetsel('*', 'spip_transactions', 'id_transaction=' . $id_transaction);
        if ($transaction) {
            return $transaction;
        }
    }

    if ($id_commande <= 0 || !association_comptes_table_a_champ('spip_transactions', 'id_commande')) {
        return array();
    }

    return sql_fetsel(
        '*',
        'spip_transactions',
        'id_commande=' . $id_commande,
        '',
        'id_transaction DESC'
    ) ?: array();
}

/**
 * Calcule un montant de commande sans inventer de donnee.
 *
 * @param array $commande
 * @param array $transaction
 * @return float
 */
function association_commande_comptable_montant($commande, $transaction = array()) {
    if (!empty($transaction['montant'])) {
        return round(floatval($transaction['montant']), 2);
    }

    foreach (array('montant', 'montant_ttc', 'total', 'total_ttc', 'prix') as $champ) {
        if (isset($commande[$champ]) && floatval($commande[$champ]) > 0) {
            return round(floatval($commande[$champ]), 2);
        }
    }

    $id_commande = intval($commande['id_commande'] ?? 0);
    if (
        $id_commande <= 0
        || !sql_showtable('spip_commandes_details', true)
        || !association_comptes_table_a_champ('spip_commandes_details', 'id_commande')
    ) {
        return 0.0;
    }

    $lignes = sql_allfetsel('*', 'spip_commandes_details', 'id_commande=' . $id_commande);
    $total = 0.0;
    foreach ($lignes as $ligne) {
        $quantite = isset($ligne['quantite']) ? max(1, floatval($ligne['quantite'])) : 1;
        if (isset($ligne['montant']) && floatval($ligne['montant']) > 0) {
            $total += floatval($ligne['montant']);
        } elseif (isset($ligne['prix_unitaire']) && floatval($ligne['prix_unitaire']) > 0) {
            $total += floatval($ligne['prix_unitaire']) * $quantite;
        } elseif (isset($ligne['prix']) && floatval($ligne['prix']) > 0) {
            $total += floatval($ligne['prix']) * $quantite;
        }
    }

    return round($total, 2);
}

/**
 * Construit une justification lisible pour une operation issue d'une commande.
 *
 * @param array $commande
 * @return string
 */
function association_commande_comptable_justification($commande) {
    $reference = trim((string)($commande['reference'] ?? ''));
    $id_commande = intval($commande['id_commande'] ?? 0);
    $libelle = $reference !== '' ? $reference : '#' . $id_commande;
    $client = '';

    if (!empty($commande['commentaire'])) {
        $commentaire = json_decode((string)$commande['commentaire'], true);
        if (is_array($commentaire)) {
            $client = trim((string)($commentaire['client'] ?? ''));
        }
    }
    if ($client === '' && !empty($commande['id_auteur'])) {
        $client = trim((string)sql_getfetsel('nom', 'spip_auteurs', 'id_auteur=' . intval($commande['id_auteur'])));
    }

    return trim('Commande ' . $libelle . ($client !== '' ? ' - ' . $client : ''));
}

/**
 * Synchronise une commande avec la comptabilite interne.
 *
 * Regle metier Blobul :
 * - a l'envoi de la commande, creation ou mise a jour d'une ecriture de creance non validee ;
 * - au paiement Bank, validation de cette ecriture et bascule sur l'imputation de paiement.
 *
 * @param int $id_commande
 * @param array $options
 * @return int Identifiant de l'ecriture comptable creee ou mise a jour.
 */
function association_commande_comptable_synchroniser($id_commande, $options = array()) {
    $id_commande = intval($id_commande);
    if ($id_commande <= 0 || empty($GLOBALS['association_metas']['comptes'])) {
        return 0;
    }
    if (!sql_showtable('spip_commandes', true) || !sql_showtable('spip_asso_comptes', true)) {
        return 0;
    }

    $commande = sql_fetsel('*', 'spip_commandes', 'id_commande=' . $id_commande);
    if (!$commande) {
        return 0;
    }

    $transaction = association_commande_comptable_transaction($id_commande, intval($options['id_transaction'] ?? 0));
    $force_paiement = !empty($options['forcer_paiement']);
    $statut_transaction = strtolower(trim((string)($transaction['statut'] ?? '')));
    $statut_commande = strtolower(trim((string)($commande['statut'] ?? '')));
    $est_paye = $force_paiement
        || in_array($statut_transaction, array('ok', 'paye', 'paid'), true)
        || in_array($statut_commande, array('paye', 'paid'), true);

    $date_envoi = association_comptes_date_normalisee($commande['date_envoi'] ?? '');
    $date_paiement = association_comptes_date_normalisee($transaction['date_paiement'] ?? ($commande['date_paiement'] ?? ''));
    if ($date_envoi === '' && !$est_paye) {
        return 0;
    }

    $date_compte = $est_paye
        ? ($date_paiement ?: association_comptes_date_normalisee($commande['date'] ?? '') ?: date('Y-m-d'))
        : $date_envoi;
    $montant = association_commande_comptable_montant($commande, $transaction);
    if ($montant <= 0) {
        association_log('comptabilite', 'Commande comptable ignoree: montant nul id_commande=' . $id_commande, 'info');
        return 0;
    }

    $id_transaction = intval($transaction['id_transaction'] ?? 0);
    $imputation_creance = $GLOBALS['association_metas']['pc_commandes_creance']
        ?? $GLOBALS['association_metas']['pc_activites_creance']
        ?? '101';
    $imputation_paiement = $GLOBALS['association_metas']['pc_commandes_paiement']
        ?? $GLOBALS['association_metas']['pc_ventes']
        ?? $GLOBALS['association_metas']['pc_activites_paiement']
        ?? '701';
    $imputation = $est_paye ? $imputation_paiement : $imputation_creance;
    $vu = $est_paye ? 1 : 0;
    $justification = association_commande_comptable_justification($commande);
    $journal = 'commande|' . $id_commande;
    $id_auteur = intval($commande['id_auteur'] ?? ($transaction['id_auteur'] ?? 0));

    $where = "objet='commande' AND id_objet=" . $id_commande;
    $compte = sql_fetsel('id_compte', 'spip_asso_comptes', $where);
    if (!$compte && $id_transaction > 0) {
        $compte = sql_fetsel('id_compte', 'spip_asso_comptes', 'id_transaction=' . $id_transaction . " AND objet='commande'");
    }

    if ($compte && !empty($compte['id_compte'])) {
        $id_compte = intval($compte['id_compte']);
        sql_updateq(
            'spip_asso_comptes',
            array(
                'date' => $date_compte,
                'recette' => $montant,
                'depense' => 0,
                'justification' => $justification,
                'imputation' => $imputation,
                'journal' => $journal,
                'id_auteur' => $id_auteur,
                'id_objet' => $id_commande,
                'objet' => 'commande',
                'id_transaction' => $id_transaction,
                'vu' => $vu,
            ),
            'id_compte=' . $id_compte
        );
        return $id_compte;
    }

	include_spip('inc/association_compta_ecritures');
	return association_compta_ecriture_creer(array(
		'date' => $date_compte, 'recette' => $montant, 'depense' => 0,
		'justification' => $justification, 'imputation' => $imputation, 'journal' => $journal,
		'id_auteur' => $id_auteur, 'id_objet' => $id_commande, 'objet' => 'commande',
		'id_transaction' => $id_transaction, 'vu' => $vu,
	));
}

