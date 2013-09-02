<?php
/**
*
* @author      9*  9@u9.ru 06082013	
* @package	SBIN Diesel
*/
class ui_www_article_search extends user_interface
{
	public $title = 'www: Публикации - поиск';

	protected $deps = array(
		'main' => array(
			'www_article.main',
			'www_article_search.main_filter',
		),
	);

	public function __construct ()
	{
		parent::__construct(__CLASS__);
	}

	/**
	*       Main interface
	*/
	protected function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'main.js');
		response::send($tmpl->parse($this), 'js');
	}

	/**
	*       Main interface
	*/
	protected function sys_main_filter()
	{
		$tmpl = new tmpl($this->pwd() . 'main_filter.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
