<?php defined('SYSPATH') or die('No direct access allowed.');

class View_Minion_Htaccess extends Kostache {

	public $base_url;

	public function rewrite_base()
	{
		return (strpos($this->base_url, '://') === FALSE)
			? $this->base_url
			: parse_url($this->base_url, PHP_URL_PATH);
	}
}
