<?php defined('SYSPATH') or die('No direct script access.');

/**
 * The App:install task creates the basic configuration files for your application
 *
 * @author Lorenzo Pisani <zeelot3k@gmail.com>
 */
class Minion_Task_App_Install extends Minion_Task {

	/**
	 * A set of config options that this task accepts
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Execute the task
	 *
	 * @param array Configuration
	 */
	public function execute(array $config)
	{
		// Generate kohana/application/bootstrap.php
		fwrite(STDOUT, "Bootstrap Configuration\n");

		$bootstrap_values = $this->get_bootstrap_values();
		$view = Kostache::factory('minion/bootstrap')
			->set($bootstrap_values);

		$filepath = APPPATH.'bootstrap.php';
		file_put_contents($filepath, $view->render());
		fwrite(STDOUT, "File written to ".$filepath."\n");

		// Generate kohana/application/config/database.php
		fwrite(STDOUT, "Database Configuration\n");

		$database_values = $this->get_database_values();
		$view = Kostache::factory('minion/config/database')
			->set($database_values);

		$filepath = APPPATH.'config/database.php';
		file_put_contents($filepath, $view->render());
		fwrite(STDOUT, "File written to ".$filepath."\n");

		// Generate the .htaccess file
		$view = Kostache::factory('minion/htaccess')
			->set('base_url', Arr::get($bootstrap_values, 'base_url'));

		$filepath = DOCROOT.'../.htaccess';
		file_put_contents($filepath, $view->render());
		fwrite(STDOUT, "File written to ".$filepath."\n");

		return;
	}

	protected function get_bootstrap_values()
	{
		return array(
			'timezone' => $this->ask('Timezone', 'America/Chicago'),
			'locale'   => $this->ask('Locale', 'en_US.utf-8'),
			'language' => $this->ask('Language', 'en-us'),
			'base_url' => $this->ask('Base URL', 'http://dev.vm/wp-kohana/'),
		);
	}

	protected function get_database_values()
	{
		return array(
			'hostname' => $this->ask('Hostname', 'localhost'),
			'database' => $this->ask('Database'),
			'username' => $this->ask('Username'),
			'password' => $this->ask('Password'),
		);
	}

	protected function ask($message, $default = NULL)
	{
		$message = (is_string($default)) ? $message.' ['.$default.']: ' : $message.': ';
		fwrite(STDOUT, $message);
		$value = trim(fgets(STDIN));

		return ($default !== NULL AND empty($value))
			? $default
			: $value;
	}
}
