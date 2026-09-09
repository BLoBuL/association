<?php

namespace Spip\Cli\Command;

use Spip\Cli\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssociationInstallationVerifier extends Command
{
	protected function configure() {
		$this->setName('association:installation:verifier')
			->setDescription('Vérifie les plugins, tables, objets SQL et versions de schéma de la suite Association.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->demarrerSpip();
		include_spip('base/abstract_sql');
		include_spip('base/objets');
		include_spip('inc/association_installation');
		$inventaire = association_installation_inventaire();
		$plugins = (array) ($inventaire['plugins_requis'] ?? []);
		$schemas = (array) ($inventaire['schemas'] ?? []);
		$objetsAttendus = (array) ($inventaire['objets'] ?? []);
		$tablesAttendues = (array) ($inventaire['tables'] ?? []);

		$erreurs = (array) ($inventaire['erreurs'] ?? []);
		$inventairesFournis = (array) ($inventaire['plugins'] ?? []);
		foreach ($plugins as $prefixe) {
			if (!test_plugin_actif($prefixe)) {
				$erreurs[] = "Plugin inactif : $prefixe";
			}
			if (!in_array($prefixe, $inventairesFournis, true)) {
				$erreurs[] = "Inventaire d'installation absent : $prefixe";
			}
		}
		$tablesInstallees = sql_alltable('%');
		$prefixe = $GLOBALS['connexions'][0]['prefixe'] ?? 'spip';
		foreach ($tablesAttendues as $table) {
			$tableReelle = preg_replace('/^spip(?=_)/', $prefixe, $table);
			if (!in_array($tableReelle, $tablesInstallees, true)) {
				$erreurs[] = "Table absente : $table";
			}
		}

		$objets = lister_tables_objets_sql();
		foreach ($objetsAttendus as $table) {
			if (($objets[$table]['principale'] ?? '') !== 'oui' || empty($objets[$table]['table_objet'])) {
				$erreurs[] = "Objet SQL SPIP incomplet : $table";
			}
		}
		foreach ($schemas as $meta => $version) {
			$versionInstallee = (string) lire_config($meta, '');
			if ($versionInstallee !== $version) {
				$erreurs[] = "Version de schéma $meta : $versionInstallee (attendu $version)";
			}
		}

		if ($erreurs) {
			$output->writeln('<error>Installation Association invalide.</error>');
			foreach ($erreurs as $erreur) {
				$output->writeln(' - ' . $erreur);
			}
			return self::FAILURE;
		}

		$output->writeln('<info>Installation Association valide.</info>');
		$output->writeln(sprintf(
			'%d plugins contributeurs actifs (%d obligatoire), %d tables présentes, %d objets SQL SPIP et %d schémas à jour.',
			count($inventairesFournis),
			count($plugins),
			count($tablesAttendues),
			count($objetsAttendus),
			count($schemas)
		));
		return self::SUCCESS;
	}
}
