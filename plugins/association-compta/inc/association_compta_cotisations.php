<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

/**
 * Demande au module propriétaire des cotisations de synchroniser une écriture.
 *
 * Comptabilité ne connaît ni la table ni l'implémentation des cotisations.
 */
function association_compta_cotisation_synchroniser($id_compte, array $donnees = array()) {
    return pipeline('association_compta_cotisation_synchroniser', array(
        'args' => array(
            'id_compte' => intval($id_compte),
            'donnees' => $donnees,
        ),
        'data' => null,
    ));
}
