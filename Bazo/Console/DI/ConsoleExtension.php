<?php

namespace Bazo\Console\DI;

use Nette;
use Nette\DI\Container;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Application;

/**
 * Console service.
 *
 * @author Martin Bažík
 */
class ConsoleExtension extends \Nette\DI\CompilerExtension
{

	const COMMAND_TAG = 'console.command';
	const HELPER_TAG = 'console.helper';


	private $defaults = [
		'name' => Nette\Framework::NAME,
		'version' => Nette\Framework::VERSION,
	];


	/**
	 * Processes configuration data
	 *
	 * @return void
	 */
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		// console application
		$container->addDefinition($this->prefix('console'))
				->setClass('Symfony\Component\Console\Application')
				->setFactory('Extensions\ConsoleExtension::createConsole', ['@container', $config])
				->setAutowired(FALSE);

		// aliases
		$container->addDefinition('console')
				->setClass('Symfony\Component\Console\Application')
				->setFactory('@container::getService', [$this->prefix('console')]);
	}


	/**
	 * @param Container
	 * @return Application
	 */
	public static function createConsole(Container $container, $config)
	{
		$console = new Application(sprintf('%s command line interface, version: %s', $config['name'], $config['version']));

		$helperSet = new HelperSet;

		foreach (array_keys($container->findByTag(self::HELPER_TAG)) as $name) {
			$helperSet->set($container->getService($name), $name);
		}

		$console->setHelperSet($helperSet);
		$console->setCatchExceptions(false);

		$commands = array();
		foreach (array_keys($container->findByTag(self::COMMAND_TAG)) as $name) {
			$commands[] = $container->getService($name);
		}
		$console->addCommands($commands);

		return $console;
	}


}

