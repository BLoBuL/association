<?php

namespace Spip\Cli\Command;

use Spip\Cli\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssociationInstallationVerifier extends Command
{
	protected function configure()
	{
		$this->setName('association:installation:verifier')
			->setDescription('Vérifie les plugins, tables, objets SQL et versions de schéma de la suite Association.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->demarrerSpip();
		include_spip('base/abstract_sql');
		include_spip('base/objets');

		$plugins = array(
			'association', 'association_adhesions', 'association_communication',
			'association_compta', 'association_dons', 'association_evenements',
			'association_groupes', 'association_paiements', 'association_prets',
			'association_ventes',
		);
		$schemas = array(
			'association_base_version' => '1.6.1',
			'association_adhesions_base_version' => '1.1.0',
			'association_compta_base_version' => '1.0.0',
			'association_dons_base_version' => '1.0.0',
			'association_evenements_base_version' => '1.1.0',
			'association_prets_base_version' => '1.0.0',
			'association_ventes_base_version' => '1.0.0',
		);
		$objetsAttendus = array(
			'spip_asso_categories_adherents', 'spip_asso_cotisations',
			'spip_asso_comptes', 'spip_asso_plan', 'spip_asso_destination', 'spip_asso_destination_op',
			'spip_asso_categories_activites', 'spip_asso_activites',
			'spip_asso_dons', 'spip_asso_ressources', 'spip_asso_prets', 'spip_asso_ventes',
		);
		$tablesAttendues = array_merge(array('spip_association_metas'), $objetsAttendus, array('spip_asso_categories_activites_liens'));

		$erreurs = array();
		foreach ($plugins as $prefixe) {
			if (!test_plugin_actif($prefixe)) {
				$erreurs[] = "Plugin inactif : $prefixe";
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
		$output->writeln('10 plugins actifs, 14 tables présentes, 12 objets SQL SPIP et 7 schémas à jour.');
		return self::SUCCESS;
	}
}
