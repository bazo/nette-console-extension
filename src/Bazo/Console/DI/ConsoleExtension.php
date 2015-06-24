<?php

namespace Bazo\Console\DI;

use Nette;
use Nette\DI\Container;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Application;

/**
 * Console extension
 *
 * @author Martin Bažík <martin@bazo.sk>
 */
class ConsoleExtension extends Nette\DI\CompilerExtension
{

	const COMMAND_TAG = 'console.command';
	const KDYBY_COMMAND_TAG = 'kdyby.console.command';
	const HELPER_TAG = 'console.helper';


	private $defaults = [
		'name' => 'Nette Framework Console',
		'version' => 1,
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
				->setFactory('Bazo\Console\DI\ConsoleExtension::createConsole', ['@container', $config])
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
		$console = new Application($config['name'], $config['version']);

		$helperSet = new HelperSet;

		foreach (array_keys($container->findByTag(self::HELPER_TAG)) as $name) {
			$helperSet->set($container->getService($name), $name);
		}

		$console->setHelperSet($helperSet);
		$console->setCatchExceptions(false);

		$commands = [];
		foreach (array_keys($container->findByTag(self::COMMAND_TAG)) as $name) {
			$commands[] = $container->getService($name);
		}
		foreach (array_keys($container->findByTag(self::KDYBY_COMMAND_TAG)) as $name) {
			$commands[] = $container->getService($name);
		}
		$console->addCommands($commands);

		return $console;
	}


}

