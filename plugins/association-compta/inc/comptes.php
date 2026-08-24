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
 * Insère un compte dans la table `spip_asso_comptes`.
 *
 * Cette fonction permet d'ajouter une nouvelle entrée dans la table des comptes
 * avec les informations fournies. Elle gère également les cas spécifiques aux cotisations.
 *
 * @param string $date La date de l'opération au format `Y-m-d H:i:s`.
 * @param float $recette Le montant de la recette associée à l'opération.
 * @param float $depense Le montant de la dépense associée à l'opération.
 * @param string $justification Une description ou justification de l'opération.
 * @param string $imputation Le code d'imputation comptable.
 * @param string $journal Le journal comptable associé.
 * @param int|null $id_auteur L'identifiant de l'auteur de l'opération.
 * @param int|null $id_objet (optionnel) L'identifiant de l'objet lié à l'opération.
 * @param string|null $objet (optionnel) Le type d'objet lié à l'opération.
 * @param string $inscription (optionnel) Indique si l'opération est liée à une cotisation.
 * @param int $id_categorie (optionnel) L'identifiant de la catégorie associée.
 * @param string|null $statut_cotisation (optionnel) Le statut de la cotisation (par exemple, "ok").
 * @param int|null $id_transaction (optionnel) L'identifiant de la transaction associée.
 * @param array $destination_map (optionnel) Une carte des destinations comptables pour l'opération.
 *
 * @return int L'identifiant de l'entrée insérée dans la table `spip_asso_comptes`.
 */
function inserer_compte(
    $date,
    $recette,
    $depense,
    $justification,
    $imputation,
    $journal,
    $id_auteur,
    $id_objet = null,
    $objet = null,
    $reinscription = '',
    $id_categorie = 0,
    $statut_cotisation = null,
    $id_transaction = null,
    $vu = 0,
    $destination_map = []
) {
    $is_cotisation = strlen($reinscription) != 0;
    if (!$is_cotisation and strlen($imputation) == 0) {
        $imputation = '101';
        $journal = '101';
    }
    $id_compte = sql_insertq('spip_asso_comptes', [
        'date'              => $date,
        'recette'           => $recette,
        'depense'           => $depense,
        'justification'     => $justification,
        'imputation'        => $imputation,
        'journal'           => $journal,
        'id_auteur'         => $id_auteur,
        'id_objet'          => $id_objet,
        'objet'             => $objet,
        'id_transaction'    => $id_transaction,
        'vu'                => $vu,
    ]);
	if ($id_compte && $is_cotisation) {
		include_spip('inc/association_compta_cotisations');
		association_compta_cotisation_synchroniser($id_compte, array(
			'reinscription' => $reinscription,
			'statut_cotisation' => $statut_cotisation,
			'id_categorie' => $id_categorie,
			'id_transaction' => $id_transaction,
			'montant' => $recette,
			'date' => $date,
		));
	}

    //ajouter_destinations($id_compte, $recette, $depense, $destination_map);
    return $id_compte;
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

    return inserer_compte(
        $date_compte,
        $montant,
        0,
        $justification,
        $imputation,
        $journal,
        $id_auteur,
        $id_commande,
        'commande',
        '',
        0,
        null,
        $id_transaction,
        $vu
    );
}
/**
 * Modifie une entrée dans la table `spip_asso_comptes`.
 *
 * Cette fonction met à jour les informations d'un compte existant dans la table
 * `spip_asso_comptes` en fonction des paramètres fournis. Elle gère également
 * les champs spécifiques aux cotisations.
 *
 * @param int $id_compte L'identifiant du compte à modifier.
 * @param string $date La date de l'opération au format `Y-m-d H:i:s`.
 * @param float $recette Le montant de la recette associée à l'opération.
 * @param float $depense Le montant de la dépense associée à l'opération.
 * @param string $justification Une description ou justification de l'opération.
 * @param string $imputation Le code d'imputation comptable.
 * @param string|null $journal (optionnel) Le journal comptable associé.
 * @param int|null $id_objet (optionnel) L'identifiant de l'objet lié à l'opération.
 * @param string|null $objet (optionnel) Le type d'objet lié à l'opération.
 * @param string|null $inscription (optionnel) Indique si l'opération est liée à une cotisation.
 * @param int|null $id_categorie (optionnel) L'identifiant de la catégorie associée.
 * @param string|null $statut_cotisation (optionnel) Le statut de la cotisation (par exemple, "ok").
 * @param int $id_transaction (optionnel) L'identifiant de la transaction associée.
 * @param int $vu (optionnel) Indique si l'opération a été vue (1 pour oui, 0 pour non).
 *
 * @return int Retourne l'identifiant du compte modifié.
 */
function modifier_compte(
    $id_compte,
    $date,
    $recette,
    $depense,
    $justification,
    $imputation,
    $journal = null,
    $id_objet = null,
    $objet = null,
    $reinscription = null,
    $id_categorie = null,
    $statut_cotisation = null,
    $id_transaction = 0,
    $vu = 0
) {
    // Si l'imputation est vide, on ne modifie pas l'imputation/journal par défaut ici.

    // Ne recalculer `vu` que quand le statut de cotisation est explicitement fourni.
    // Sinon, on conserve l'état courant (évite de repasser à 0 lors d'une simple édition).
    $forcer_vu = ($statut_cotisation !== null);
    if ($forcer_vu) {
        $vu = ($statut_cotisation === 'ok') ? 1 : 0;
    }

    // Prépare les données à mettre à jour dans la table.
    $update = [
        'date'              => $date,
        'recette'           => $recette,
        'depense'           => $depense,
        //'justification'     => $justification, // Commenté, non mis à jour.
        'imputation'        => $imputation,
        //'journal'           => $journal, // Commenté, non mis à jour.
        //'reinscription'     => $reinscription, // Commenté, non mis à jour.
    ];

    if ($forcer_vu) {
        $update['vu'] = $vu;
    }

    // Ajoute le statut de cotisation si fourni.

    // Ajoute l'identifiant de la transaction si fourni.
    if ($id_transaction != null) {
        $update['id_transaction'] = $id_transaction;
    }

    // Exécute la mise à jour dans la base de données.
    // Validation : s'assurer que nous avons un id_compte valide
    $id_compte_int = intval($id_compte);
    if ($id_compte_int <= 0) {
        association_log('comptabilite', "modifier_compte: id_compte invalide, mise à jour ignorée", 'info');
        return 0;
    }
    sql_updateq('spip_asso_comptes', $update, "id_compte=$id_compte_int");
	if ($reinscription !== null || $statut_cotisation !== null) {
		include_spip('inc/association_compta_cotisations');
		association_compta_cotisation_synchroniser($id_compte_int, array_merge($update, array(
			'inscription' => $reinscription,
			'statut' => $statut_cotisation,
			'id_categorie' => $id_categorie,
		)));
	}

    // Retourne l'identifiant du compte modifié.
    return $id_compte_int;
    //ajouter_destinations($id_compte, $recette, $depense, $destination_map); // Code commenté.
}
/**
* Insère une opération comptable liée à une activité dans la table `spip_asso_comptes`.
*
* Cette fonction récupère les informations nécessaires à partir des données de l'activité
* et de la transaction associée, puis insère une nouvelle entrée dans la table des comptes.
*
* @param int $id_activite L'identifiant de l'activité concernée.
* @param array $data_form Les données du formulaire contenant les informations sur le participant.
* @param array $gestions_places Les informations sur l'événement associé à l'activité.
*
* @return int L'identifiant de l'entrée insérée dans la table `spip_asso_comptes`.
*/
function inserer_compte_activite($id_activite,$gestions_places) {

  // Récupère les informations de l'activité à partir de la base de données.
  $row_activite = sql_fetsel('*', 'spip_asso_activites', "id_activite=". intval($id_activite));

    if(empty($gestions_places)){
        $gestions_places = gestions_places($row_activite['id_evenement']);
    }

    // Vérifier si l'événement est gratuit (payant=0) et dans ce cas ne rien créer
    $row_evenement = sql_fetsel('payant', 'spip_evenements', 'id_evenement='.intval($row_activite['id_evenement']));
    if ($row_evenement && isset($row_evenement['payant']) && intval($row_evenement['payant']) === 0) {
        association_log('comptabilite', "Compta activité ignorée (événement gratuit) id_evenement=".$row_activite['id_evenement']." id_activite=".$id_activite, 'info');
        return 0;
    }

    // Récupère les informations de la transaction associée à l'activité.
  $row_transaction = sql_fetsel('*', 'spip_transactions', "id_transaction=". intval($row_activite['id_transaction']));
  if (!$row_transaction) {
      association_log('comptabilite', "Compta activite ignoree (transaction introuvable) id_activite=" . intval($id_activite), 'erreur');
      return 0;
  }

  // Utiliser la date d'inscription (fallback: maintenant si vide)
  $date = !empty($row_activite['date']) ? $row_activite['date'] : date('Y-m-d H:i:s');

  // Récupère le montant total de l'inscription.
  $recette = $row_transaction['montant'];

  // Crée une justification pour l'opération en incluant le nom et prénom du participant ainsi que le titre de l'événement.
  $titre_evenement = $gestions_places['evenement_titre'] ?? ('#' . intval($row_activite['id_evenement']));
  $justification = 'Participation de ' . $row_activite['nom_inscrit'] . ' ' . $row_activite['prenom_inscrit'] . ' à l\'activité "' . $titre_evenement . '"';

  // Définit l'imputation comptable pour les créances liées aux activités.
  $imputation_creance = $GLOBALS['association_metas']['pc_activites_creance'] ?? '101';
  $imputation_paiement = $GLOBALS['association_metas']['pc_activites_paiement'] ?? '101';

  // Choisit l'imputation en fonction du statut de la transaction.
  if($row_transaction['statut'] == 'ok'){
      $imputation = $imputation_paiement ?? '101';
    }else {
      $imputation = $imputation_creance ?? '101';
  }

  // Détermine l'identifiant de l'auteur de l'activité.
  if($row_activite['id_auteur'] != 0) {
      $id_auteur = $row_activite['id_auteur'];
  }
  // Insère l'opération dans la table des comptes en utilisant la fonction générique `inserer_compte`.
  return inserer_compte(
      $date,
      $recette,
      0, // Pas de dépense associée.
      $justification,
      $imputation,
      "activite|$id_activite", // Le journal est défini mais actuellement commenté.
      $id_auteur ?? 0, // ID de l'auteur de l'activité.
      $row_activite['id_evenement'], // ID de l'activité.
      'evenement', // Type d'opération.
      '', // Pas d'inscription spécifique.
      '',
      '', // Pas de catégorie spécifique.
      $row_activite['id_transaction'] // Pas de statut de cotisation spécifique.
  );
}

/**
 * Insere une ecriture comptable de remboursement pour une inscription evenement.
 *
 * @param int $id_transaction
 * @param int $id_activite
 * @param array $gestions_places
 * @return int
 */
function inserer_compte_remboursement_activite($id_transaction, $id_activite = 0, $gestions_places = array()) {
    $id_transaction = intval($id_transaction);
    if ($id_transaction <= 0) {
        association_log('comptabilite', 'inserer_compte_remboursement_activite: id_transaction invalide', 'erreur');
        return 0;
    }

    if ($id_activite <= 0) {
        $id_activite = intval(sql_getfetsel('id_activite', 'spip_asso_activites', 'id_transaction=' . $id_transaction));
    }

    if ($id_activite <= 0) {
        association_log('comptabilite', 'inserer_compte_remboursement_activite: activite introuvable pour id_transaction=' . $id_transaction, 'erreur');
        return 0;
    }

    $row_activite = sql_fetsel('*', 'spip_asso_activites', 'id_activite=' . intval($id_activite));
    $row_transaction = sql_fetsel('*', 'spip_transactions', 'id_transaction=' . $id_transaction);

    if (!$row_activite || !$row_transaction) {
        association_log('comptabilite', 'inserer_compte_remboursement_activite: donnees activite/transaction manquantes id_transaction=' . $id_transaction, 'erreur');
        return 0;
    }

    // Eviter les doublons de remboursement pour la meme transaction.
    $deja = sql_getfetsel(
        'id_compte',
        'spip_asso_comptes',
        "id_transaction=" . $id_transaction
        . " AND objet='evenement'"
        . " AND id_objet=" . intval($row_activite['id_evenement'])
        . ' AND depense > 0'
    );
    if (intval($deja) > 0) {
        return intval($deja);
    }

    if (empty($gestions_places)) {
        $gestions_places = gestions_places($row_activite['id_evenement']);
    }

    $date = date('Y-m-d H:i:s');
    $depense = floatval($row_transaction['montant']);
    $justification = 'Remboursement de ' . $row_activite['nom_inscrit'] . ' ' . $row_activite['prenom_inscrit'] . ' pour l\'activité "' . $gestions_places['evenement_titre'] . '"';
    $imputation = $GLOBALS['association_metas']['pc_activites_paiement'] ?? '101';

    return inserer_compte(
        $date,
        0,
        $depense,
        $justification,
        $imputation,
        "activite_remboursement|$id_activite",
        intval($row_activite['id_auteur']),
        intval($row_activite['id_evenement']),
        'evenement',
        '',
        '',
        '',
        $id_transaction,
        1
    );
}
/**
 * Modifie une opération comptable liée à une activité dans la table `spip_asso_comptes`.
 *
 * Cette fonction met à jour les informations d'une activité existante dans la table
 * `spip_asso_comptes` en fonction des paramètres fournis.
 *
 * @param int $id_compte L'identifiant du compte à modifier.
 * @param int $id_activite L'identifiant de l'activité concernée.
 * @param float $montant_total Le montant total de l'activité.
 * @param int $id_transaction (optionnel) L'identifiant de la transaction associée. Par défaut, 0.
 * @param int $vu (optionnel) Indique si l'opération a été vue (1 pour oui, 0 pour non). Par défaut, 0.
 *
 * @return void
 */
function modifier_compte_activite($id_activite, $id_transaction) {

     // Récupère les informations actuelles de l'activité à partir de la base de données.
     $query_asso_activite = sql_fetsel('*', 'spip_asso_activites', "id_activite=". intval($id_activite));

     // Récupère le montant total de l'activité à partir de la transaction associée.
     $query_transaction = sql_fetsel('*', 'spip_transactions', "id_transaction=". intval($id_transaction));

    // Identifie l'entrée comptable à mettre à jour :
    // 1) Priorité : chercher par id_transaction (écriture créée via inserer_compte_activite utilise id_transaction et objet='evenement')
    // 2) Fallback : chercher par id_objet=id_activite et objet='activite' (ancien format)
    $id_compte = 0;
    if (!empty($id_transaction)) {
        $row_compte = sql_fetsel('id_compte, recette', 'spip_asso_comptes', 'id_transaction=' . intval($id_transaction) . " AND objet='evenement' AND id_objet=" . intval($query_asso_activite['id_evenement']));
        if (is_array($row_compte) && isset($row_compte['id_compte'])) {
            $id_compte = intval($row_compte['id_compte']);
        }
    }
    // Fallback historique
    if (empty($id_compte)) {
        $row_compte = sql_fetsel('id_compte, recette', 'spip_asso_comptes', "id_objet=" . intval($id_activite) . " AND objet='activite'");
        if (is_array($row_compte) && isset($row_compte['id_compte'])) {
            $id_compte = intval($row_compte['id_compte']);
        }
    }

     // Utiliser la date d'inscription (fallback: maintenant si vide)
     $date = !empty($query_asso_activite['date']) ? $query_asso_activite['date'] : date('Y-m-d H:i:s');

     // Définit les nouvelles valeurs pour la mise à jour.
     // Le montant réel est stocké dans le champ 'montant' de la transaction
     $recette = isset($query_transaction['montant']) ? floatval($query_transaction['montant']) : 0.0;
     $justification = $query_asso_activite['justification'] ?? '';
     $imputation = $query_asso_activite['imputation'] ?? '';
     $journal = $query_asso_activite['journal'];
     $id_transaction = $query_asso_activite['id_transaction'];

     // Appelle la fonction générique pour effectuer la mise à jour de l'activité.
     $vu = 0;
    // Ne rien faire si nous n'avons pas d'id_compte valide
    if (empty($id_compte)) {
        association_log('comptabilite', "modifier_compte_activite: id_compte introuvable pour id_activite=" . intval($id_activite) . " id_transaction=" . intval($id_transaction), 'info');
        return;
    }

    // Récupérer la valeur actuelle de la recette pour comparer
    $row_current = sql_fetsel('recette', 'spip_asso_comptes', 'id_compte=' . intval($id_compte));
    $current_recette = $row_current ? floatval($row_current['recette']) : 0.0;

    // Si le montant diffère, on met à jour via modifier_compte (qui mettra à jour les champs nécessaires)
    if (abs($current_recette - $recette) > 0.001) {
        modifier_compte(
            $id_compte,
            $date,
            $recette,
            '0',
            $justification,
            $imputation,
            $journal,
            $query_asso_activite['id_evenement'],
            'evenement',
            '',
            '',
            '',
            $id_transaction,
            $vu
        );
        association_log('comptabilite', 'modifier_compte_activite: ajustement recette id_compte=' . intval($id_compte) . ' de ' . $current_recette . ' vers ' . $recette, 'info');
    } else {
        // Si pas de changement de montant, on s'assure que les autres métadonnées sont à jour (date/imputation/vu)
        modifier_compte(
            $id_compte,
            $date,
            $recette,
            '0',
            $justification,
            $imputation,
            $journal,
            $query_asso_activite['id_evenement'],
            'evenement',
            '',
            '',
            '',
            $id_transaction,
            $vu
        );
    }
}

/**
 * Valide une opération comptable liée à une activité dans la table `spip_asso_comptes`.
 *
 * Cette fonction met à jour une entrée existante dans la table `spip_asso_comptes`
 * en fonction des informations de la transaction associée. Elle marque également
 * l'activité comme vue.
 *
 * @param int $id_transaction L'identifiant de la transaction associée.
 *
 * @return void
 */
function valider_compte_activite($id_transaction) {
    // Récupère les informations de l'opération comptable associée à la transaction.
    $query_asso_comptes = sql_fetsel('*', 'spip_asso_comptes', "id_transaction=$id_transaction");

    // Récupère les informations de la transaction.
    $query_transaction = sql_fetsel('*', 'spip_transactions', "id_transaction=$id_transaction");

    // Récupérer la date d'inscription via l'activité liée à la transaction
    $row_activite = sql_fetsel('date', 'spip_asso_activites', 'id_transaction=' . intval($id_transaction));
    $date_inscription = (!empty($row_activite['date'])) ? $row_activite['date'] : date('Y-m-d H:i:s');

    $id_compte = 0;
    if (is_array($query_asso_comptes) && isset($query_asso_comptes['id_compte'])) {
        $id_compte = intval($query_asso_comptes['id_compte']);
    }
    if ($id_compte <= 0) {
        association_log('comptabilite', "valider_compte_activite: aucun compte trouvé pour id_transaction=" . intval($id_transaction), 'info');
        return;
    }

    $args = [
        'date' => $date_inscription,
        //'recette' => $query_transaction['montant_total'],
        'imputation' => $GLOBALS['association_metas']['pc_activites_paiement'],
        'vu'  => 1,
    ];

    sql_updateq('spip_asso_comptes', $args, "id_compte=$id_compte");
}

/**
 * Supprime une opération comptable liée à une activité dans la table `spip_asso_comptes`.
 *
 * Cette fonction supprime une entrée de la table `spip_asso_comptes` en fonction
 * de l'identifiant de l'activité spécifié. Elle est utilisée pour retirer les
 * opérations comptables associées à une activité donnée.
 *
 * @param int $id_activite L'identifiant de l'activité à supprimer.
 *
 * @return void
 */
function supprimer_compte_activite($id_activite) {
    // Supprime l'activité de la table `spip_asso_comptes` en fonction de l'ID de l'activité.
    sql_delete('spip_asso_comptes', "id_objet=$id_activite AND objet='activite'");
}
