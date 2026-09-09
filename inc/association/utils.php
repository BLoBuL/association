<?php

/**
 * Various values can represent the value true in the db, due to framework language,
 * forms that can use a different language etc...
 * We enforce true is properly detected using this utility function wherever possible
 * @return bool
 */
function association_valeur_bdd_est_vraie($db_value) {
	return in_array(strtolower((string) $db_value), ['on', '1', 'oui']);
}
