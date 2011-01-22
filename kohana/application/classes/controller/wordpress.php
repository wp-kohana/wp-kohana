<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_WordPress extends Controller_Website {

	public function before()
	{
		global $current_user;

		$this->auth = ORM::factory('wp_user', $current_user->ID);

		parent::before();
	}

	public function after()
	{
		if ($this->view !== NULL)
		{
			$this->view->auth = $this->auth;
		}

		parent::after();
	}
}
