<?php

namespace Bazo\Console\DI;


use Nette;
use Nette\DI\Container;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Application;

/**
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
		'catchExceptions' => TRUE
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

		$container->addDefinition($this->prefix('console'))
				->setClass(Application::class)
				->setFactory('Bazo\Console\DI\ConsoleExtension::createConsole', [
					'@container',
					$config
				]);
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
		$console->setCatchExceptions($config['catchExceptions']);

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
