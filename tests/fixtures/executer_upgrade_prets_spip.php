<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	fwrite(STDERR, "Ce contrôle doit être lancé avec SPIP CLI.\n");
	exit(2);
}
if (getenv('ASSOCIATION_TEST_FIXTURES') !== 'oui') {
	fwrite(STDERR, "Définir ASSOCIATION_TEST_FIXTURES=oui pour exécuter l'upgrade de recette.\n");
	exit(2);
}

include_spip('association_prets_administrations');
association_prets_upgrade('association_prets_base_version', '1.1.1');

$version = $GLOBALS['meta']['association_prets_base_version'] ?? '';
if (version_compare((string) $version, '1.1.1', '<')) {
	fwrite(STDERR, "L'upgrade canonique Prêts n'a pas atteint le schéma 1.1.1.\n");
	exit(1);
}
echo "OK: fonction d'administration Prêts exécutée jusqu'au schéma {$version}.\n";
