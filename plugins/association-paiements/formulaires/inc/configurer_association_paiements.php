<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Déclare le panneau de configuration propre aux paiements.
 */
function association_paiements_configurer_saisies($config, $disable_meta_admin = true) {
	$saisies = array();
if($config == 'mode_paiement' OR empty($config)) {
// Fieldset for payment configurations
    $choix_mode_paiement = preparer_choix_mode_paiement();
    $data_mode_paiement = saisies_tableau2chaine($choix_mode_paiement);

    $saisies[]= array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'config_paiement_formulaire',
        'label' => _T('association_config:config_paiement_formulaire'),
        'explication' => _T('association_config:config_mode_paiement_explication'),
    ),
    'saisies' => array(

        // Checkbox for "adhesion" payment method
        array(
            'saisie' => 'checkbox',
            'options' => array(
                'nom' => 'mode_paiement_adhesion',
                'label' => _T('association_config:config_mode_paiement_adhesion'),
                'data' => $choix_mode_paiement,
            ),
        ),
        // Checkbox for "participation" payment method
        array(
            'saisie' => 'checkbox',
            'options' => array(
                'nom' => 'mode_paiement_participation',
                'label' => _T('association_config:config_mode_paiement_participation'),
                'data' => $choix_mode_paiement,
            ),
        ),
        // Checkbox for "formidable" payment method
        array(
            'saisie' => 'checkbox',
            'options' => array(
                'nom' => 'mode_paiement_formidable',
                'label' => _T('association_config:config_mode_paiement_formulaire'),
                'data' => $choix_mode_paiement,
            ),
        ),
    ),
);
$saisies[]= array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'config_taxes_formulaire',
        'label' => _T('association_config:fieldset_config_taxe_formulaire_label'),

    ),
    'saisies' => array(
        // Input for taxe
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'meta_cfg_taxe',
                'label' => _T('association_config:config_taxe_label_adhesion_label'),
                'explication' => _T('association_config:config_taxe_explication'),
                'obligatoire' => 'non',
            ),
        ),
        array(
            'saisie' => 'input',
            'options' => array(
                'nom' => 'meta_cfg_taxe_evenement',
                'label' => _T('association_config:config_taxe_label_participation_evenement_label'),
                'explication' => _T('association_config:config_taxe_explication'),
            ),
        ),
    ),
);

// Fieldset for authorizations configuration
$nom_tresoriere=identifier_tresorier();

$saisies[]=array(
    'saisie' => 'fieldset',
    'options' => array(
        'nom' => 'config_autorisations_fieldset',
        'label' => _T('association_config:config_autorisations_fieldset'),
        'explication' => _T('association_config:meta_cfg_autorisation_encaisser_transaction_explication'),
    ),
    'saisies' => array(
        // Selection box for authorization level
        array(
            'saisie' => 'selection',
            'options' => array(
                'nom' => 'meta_cfg_autorisation_encaisser_transaction',
                'label' => _T('association_config:meta_cfg_autorisation_encaisser_transaction_label'),
                'defaut' => 'admin_et_responsable', // Default value if none selected
                'data' => array(
                    'tresoriere' => _T('association_config:autorisation_encaisser_transaction_tresoriere') . ' ' .$nom_tresoriere,
                    'admin_only' => _T('association_config:autorisation_encaisser_transaction_admin_only'),
                    'admin_et_responsable' => _T('association_config:autorisation_encaisser_transaction_admin_et_responsable'),
                ),
                'disable_choix' => empty($nom_tresoriere) ? 'tresoriere' : false,
                'disable' => $disable_meta_admin, // Disable based on condition
                'explication' => _T('association_config:meta_cfg_autorisation_encaisser_transaction_explication'),
            ),
        ),

    ),
);
}

	return $saisies;
}
