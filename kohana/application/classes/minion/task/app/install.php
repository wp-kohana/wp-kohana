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

		fwrite(STDOUT, "Hostname [localhost]: ");
		$hostname = trim(fgets(STDIN));
		$hostname = (Valid::not_empty($hostname)) ? $hostname : 'localhost';


		fwrite(STDOUT, "Database: ");
		$database = trim(fgets(STDIN));

		fwrite(STDOUT, "Username: ");
		$username = trim(fgets(STDIN));

		fwrite(STDOUT, "Password: ");
		$password = trim(fgets(STDIN));

		$filepath = APPPATH.'config/database.php';
		file_put_contents($filepath, $view->render());
		fwrite(STDOUT, "File written to ".$filepath."\n");
	}
}
