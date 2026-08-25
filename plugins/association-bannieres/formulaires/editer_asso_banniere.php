<?php
if (!defined('_ECRIRE_INC_VERSION')) { return; }
include_spip('inc/editer');
function formulaires_editer_asso_banniere_charger_dist($id_banniere = 'new', $retour = '') { return formulaires_editer_objet_charger('asso_banniere', $id_banniere, 0, 0, $retour, ''); }
function formulaires_editer_asso_banniere_verifier_dist($id_banniere = 'new', $retour = '') { return formulaires_editer_objet_verifier('asso_banniere', $id_banniere, array('titre', 'emplacement')); }
function formulaires_editer_asso_banniere_traiter_dist($id_banniere = 'new', $retour = '') { return formulaires_editer_objet_traiter('asso_banniere', $id_banniere, 0, 0, $retour, ''); }
