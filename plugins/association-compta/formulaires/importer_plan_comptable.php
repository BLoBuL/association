<?php
// TODO Dans le futur il faudra ajouter un champ de selection pour le fichier JSON, on pourrait en prévoir plusieurs par pays
// TODO Désactiver la selection des comptes déjà existants en BDD
if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/comptes');

/**
 * Déclare les saisies pour le formulaire d'importation du plan comptable.
 *
 * Cette fonction génère les champs nécessaires pour afficher les classes et comptes
 * d'un plan comptable à partir d'un fichier JSON.
 *
 * @return array Tableau des saisies pour le formulaire.
 */
function formulaires_importer_plan_comptable_saisies_dist(){
    $saisies = $saisies_classes = $saisies_comptes = array();

    // On décode le fichier JSON
    $json_data = decoder_fichier_json('json/plan_comptable_2024.json');

    // On boucle sur les classes du plan comptable
    $saisies_classes = [];
    $compteur_niveau = 0;
    foreach ($json_data['Classes'] as $classe) {
        $fieldset = [
            'saisie' => 'fieldset',
            'options' => [
                'nom' => 'class_' . $classe['Numero'],
                'label' => $classe['Numero'] . ' - ' . $classe['Libelle'],
            ],
            'saisies' => generer_saisies_comptes([$classe], '', $compteur_niveau),
        ];

        $saisies_classes[] = $fieldset;
    }

    // On ajoute les saisies des classes au formulaire
    $saisies[] = array(
        'saisie' => 'fieldset',
        'options' => array(
            'nom' => 'import_plan_comptable',
            'label' => '<:association_compta:form_import_pc_fieldset_label:>',
            'explication' => '<:association_compta:form_import_pc_fieldset_explication:>',
        ),
        'saisies' => $saisies_classes
    );

    return $saisies;
}

/**
 * Charge le contexte du formulaire d'importation du plan comptable.
 *
 * Cette fonction prépare les données nécessaires pour afficher le formulaire.
 *
 * @return array Contexte du formulaire.
 */
function formulaires_importer_plan_comptable_charger_dist(){
    $contexte = array();
    $contexte = preparer_liste_asso_plan_compte('array');
    return $contexte;
}

/**
 * Vérifie les saisies du formulaire d'importation du plan comptable.
 *
 * Cette fonction valide les données soumises par l'utilisateur.
 *
 * @return array Tableau des erreurs détectées.
 */
function formulaires_importer_plan_comptable_verifier_dist(){
    $erreurs = array();
    return $erreurs;
}

/**
 * Traite les données soumises par le formulaire d'importation du plan comptable.
 *
 * Cette fonction met à jour la table `spip_asso_plan` avec les données sélectionnées
 * dans le formulaire.
 *
 * @return array Résultat du traitement, incluant un message de succès.
 */
function formulaires_importer_plan_comptable_traiter_dist(){
    $retour = array();

    // On décode le fichier JSON
    $json_data = decoder_fichier_json('json/plan_comptable_2024.json');
    $array_comptes_json = lister_comptes_json($json_data);
    $comptes = array_keys(preparer_liste_asso_plan_compte('array'));

    // On boucle sur toutes les valeurs du formulaire
    foreach ($array_comptes_json as $code => $value) {
        if (_request($code) == 'on') {
            $classe = substr($code, 0, 1);
            if (!in_array($code, $comptes)) {
                $args = array(
                    'code' => $code,
                    'intitule' => $value['Libelle'],
                    'commentaire' => $value['Description'],
                    'classe' => $classe,
                    'solde_anterieur' => 0,
                    'date_anterieure' => date('Y-m-d')
                );
                sql_insertq('spip_asso_plan', $args);
            }
        }
    }

    $retour['message_ok'] = _T('association_compta:message_import_reussi');
    return $retour;
}

/**
 * Lit et décode un fichier JSON.
 *
 * @param string $fichier_json Chemin du fichier JSON.
 * @return array|void Données décodées du fichier JSON ou message d'erreur.
 */
function decoder_fichier_json($fichier_json){
    $file = find_in_path($fichier_json);
    $json = file_get_contents($file);
    $json_data = json_decode($json, true);

    if ($file == false) {
        echo _T('association_compta:erreur_fichier_inexistant');
    } elseif ($json_data == false) {
        echo _T('association_compta:erreur_fichier_invalide');
    } else {
        return $json_data;
    }
}

/**
 * Liste les comptes inclus dans un fichier JSON.
 *
 * @param array $json_data Données JSON décodées.
 * @return array Liste des comptes avec leurs ID et intitulés.
 */
function lister_comptes_json($json_data){
    $comptes = array();
    foreach ($json_data['Classes'] as $class) {
        if (isset($class['Comptes'])) {
            lister_compte_recurive($class['Comptes'], $comptes);
        }
    }
    return $comptes;
}

/**
 * Ajoute récursivement les comptes et sous-comptes à une liste.
 *
 * @param array $comptesArray Liste des comptes à traiter.
 * @param array &$comptesList Liste des comptes accumulés.
 * @return void
 */
function lister_compte_recurive($comptesArray, &$comptesList) {
    foreach ($comptesArray as $compte) {
        $comptesList[$compte['Numero']] = array(
            'Libelle' => $compte['Libelle'],
            'Description' => $compte['Description']
        );

        if (isset($compte['SousComptes'])) {
            lister_compte_recurive($compte['SousComptes'], $comptesList);
        }
    }
}

/**
 * Génère les saisies pour les comptes d'un plan comptable.
 *
 * @param array $data Données des comptes.
 * @param string $nomParent Nom du parent (facultatif).
 * @param int $compteur_niveau Niveau de profondeur dans la hiérarchie.
 * @return array Tableau des saisies générées.
 */
function generer_saisies_comptes($data, $nomParent = '', $compteur_niveau = 0) {
    $resultat = [];
    $compte_existant = preparer_liste_asso_plan_compte('array');

    foreach ($data as $element) {
        $numero = $element['Numero'];
        $nom = $numero;
        $label = $numero . ' - ' . $element['Libelle'];
        $description = isset($element['Description']) ? $element['Description'] : '';
        $classe = substr($numero, 0, 1);
        $case = [
            'saisie' => 'case',
            'options' => [
                'nom' => $nom,
                'label_case' => $label,
                'explication' => $description,
                'defaut' => isset($compte_existant[$numero]) ? 'on' : false,
                'disable' => isset($compte_existant[$numero]) ? 'oui' : '',
            ]
        ];
        if ($nomParent) {
            $case['options']['conteneur_class'] = 'compte niveau-' . $compteur_niveau;
            $case['options']['afficher_si'] = '@' . $classe . '@=="on"';
        } else {
            $case['options']['conteneur_class'] = 'classe';
        }

        $resultat[] = $case;

        if (isset($element['Comptes'])) {
            $resultat = array_merge($resultat, generer_saisies_comptes($element['Comptes'], $nom, $compteur_niveau + 1));
        }
        if (isset($element['SousComptes'])) {
            $resultat = array_merge($resultat, generer_saisies_comptes($element['SousComptes'], $nom, $compteur_niveau + 1));
        }
    }
    return $resultat;
}
