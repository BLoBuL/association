<?php
/**
 * Various values can represent the value true in the db, due to framework language,
 * forms that can use a different language etc...
 * We enforce true is properly detected using this utility function wherever possible
 * @param $db_value
 * @return bool
 */
function is_db_value_true($db_value)
{
    return in_array(strtolower((string)$db_value), ["on", "1", "oui"]);
}