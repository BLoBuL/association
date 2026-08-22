<?php

namespace Spip\Cli\Command;

use Spip\Cli\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AssociationConfigEcrire extends Command
{
	protected function configure()
	{
		$this->setName('association:config:ecrire')
			->setDescription('Modifie une option de configuration autorisee du plugin Association.')
			->addArgument('option', InputArgument::REQUIRED, 'Option autorisee, par exemple debug ou debug.inscriptions')
			->addArgument('valeur', InputArgument::REQUIRED, 'Valeur validee ; utiliser un tableau JSON pour les listes')
			->addOption('format', null, InputOption::VALUE_REQUIRED, 'Format de sortie : human ou json', 'human')
			->addOption('pretty', null, InputOption::VALUE_NONE, 'Indente la sortie JSON');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$format = strtolower((string) $input->getOption('format'));
		if (!in_array($format, array('human', 'json'), true)) {
			$output->writeln('Format invalide : utiliser human ou json.');
			return self::INVALID;
		}

		$this->demarrerSpip();
		include_spip('inc/association_config_cli');
		if (!function_exists('association_config_cli_ecrire')) {
			$output->writeln('Configuration Association indisponible.');
			return self::FAILURE;
		}

		$resultat = association_config_cli_ecrire($input->getArgument('option'), $input->getArgument('valeur'));
		$code = association_config_cli_code_sortie($resultat);
		$this->afficherResultat($output, $format, (bool) $input->getOption('pretty'), $resultat);
		return $code;
	}

	private function afficherResultat(OutputInterface $output, $format, $pretty, array $resultat)
	{
		$payload = array('status' => !empty($resultat['ok']) ? 'ok' : 'error', 'command' => 'association:config:ecrire');
		if (!empty($resultat['ok'])) {
			$payload += array(
				'option' => $resultat['option'],
				'previous' => $resultat['previous'],
				'value' => $resultat['value'],
				'verified' => $resultat['verified'],
				'changed' => $resultat['changed'],
				'affected_options' => $resultat['affected_options'],
			);
		} else {
			$payload['reason'] = isset($resultat['reason']) ? $resultat['reason'] : 'unknown_error';
			$payload['option'] = isset($resultat['option']) ? $resultat['option'] : null;
			if (isset($resultat['rollback_restored'])) {
				$payload['rollback_restored'] = $resultat['rollback_restored'];
			}
		}

		if ($format === 'json') {
			$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
			if ($pretty) {
				$flags |= JSON_PRETTY_PRINT;
			}
			$output->writeln(json_encode($payload, $flags));
			return;
		}

		if (empty($resultat['ok'])) {
			$output->writeln('Erreur : ' . $payload['reason'] . ($payload['option'] ? ' (' . $payload['option'] . ')' : ''));
			return;
		}
		$output->writeln($resultat['option'] . ': ' . $this->formaterValeur($resultat['previous']) . ' -> ' . $this->formaterValeur($resultat['value']));
		$output->writeln($resultat['changed'] ? 'Configuration modifiee.' : 'Configuration deja conforme.');
	}

	private function formaterValeur($valeur)
	{
		return is_array($valeur)
			? json_encode($valeur, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
			: (string) $valeur;
	}
}
