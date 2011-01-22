<?php defined('SYSPATH') or die('No direct script access.');

class View_Front_Index extends View_WordPress {

	public function user_nicename()
	{
		return ($this->auth->loaded()) ? $this->auth->user_nicename : 'Guest';
	}

}
