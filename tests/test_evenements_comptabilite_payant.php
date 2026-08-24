<?php

$source = file_get_contents(dirname(__DIR__) . '/plugins/association-evenements/inc/association_evenements_comptabilite.php');
if (strpos($source, "array_key_exists('payant', (array) \$evenement)") === false) {
	fwrite(STDERR, "Le champ Agenda payant doit être testé sans accès à une clé absente.\n");
	exit(1);
}
echo "OK: la comptabilité événement tolère un schéma Agenda sans champ payant.\n";
