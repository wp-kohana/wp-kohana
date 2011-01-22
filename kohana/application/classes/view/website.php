<?php defined('SYSPATH') or die('No direct script access.');

abstract class View_Website extends View_Layout {

	/**
	 * Automatically sent by Controller_Website::after()
	 * @var array array of filters commonly used for search and pagination
	 */
	public $filters;

	public function profiler()
	{
		if ( ! Kohana::$profiling)
			return FALSE;

		return View::factory('profiler/stats');
	}
}
