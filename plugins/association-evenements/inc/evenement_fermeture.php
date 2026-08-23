<?php

if (!defined('_ECRIRE_INC_VERSION')) {
    return;
}

/**
 * Calcule la date effective de fermeture des inscriptions.
 *
 * @param array $evenement Donnees de l'evenement.
 * @return string|null Date SQL de fermeture, ou null pour les modes sans date.
 */
function association_evenement_calculer_date_fermeture($evenement) {
    $fermeture_inscription = $evenement['fermeture_inscription'] ?? '';
    $date_debut_evenement = $evenement['date_debut'] ?? '';
    if (!$date_debut_evenement) {
        return null;
    }

    $heure_debut_evenement = affdate($date_debut_evenement, 'H:i:s');
    if ($fermeture_inscription === 'dt') {
        $date_precise = $evenement['fermeture_inscription_date'] ?? '';
        return $date_precise ?: null;
    }
    if ($fermeture_inscription === 'last_minute') {
        return $date_debut_evenement;
    }
    if ($fermeture_inscription === 'midnight') {
        return agenda_jourdecal($date_debut_evenement, 0, 'Y-m-d 00:00:00');
    }
    if ($fermeture_inscription === 'midi') {
        return agenda_jourdecal($date_debut_evenement, -1, 'Y-m-d 12:00:00');
    }

    $decalages = array(
        '24h' => -1,
        '48h' => -2,
        '72h' => -3,
        '96h' => -4,
        '7j' => -7,
        '14j' => -14,
        '21j' => -21,
        '30j' => -30,
    );
    if (isset($decalages[$fermeture_inscription])) {
        return agenda_jourdecal($date_debut_evenement, $decalages[$fermeture_inscription], 'Y-m-d ' . $heure_debut_evenement);
    }

    return null;
}
