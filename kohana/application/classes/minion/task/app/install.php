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
		fwrite(STDOUT, "Database Configuration\n");
		$this->setup_db_config();

		return;
	}

	protected function setup_db_config()
	{
		$view = Kostache::factory('minion/config/database')
			->bind('hostname', $hostname)
			->bind('database', $database)
			->bind('username', $username)
			->bind('password', $password);

		$hostname = $this->ask('Hostname', 'localhost');
		$database = $this->ask('Database');
		$username = $this->ask('Username');
		$password = $this->ask('Password');

		$filepath = APPPATH.'config/database.php';
		file_put_contents($filepath, $view->render());
		fwrite(STDOUT, "File written to ".$filepath."\n");
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
