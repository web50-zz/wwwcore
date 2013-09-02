<?php
/**
*
* @author       9*  9@u9.ru  	23072013
* @package	SBIN Diesel
*/
class ui_www_article_files extends user_interface
{
	public $title = 'www: Публикации - Файлы';

	protected $deps = array(
		'main' => array(
			'www_article_files.item_form',
			'www_article_files.grid',
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
	*/
	protected function sys_item_form()
	{
		$tmpl = new tmpl($this->pwd() . 'item_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	protected function sys_grid()
	{
		$tmpl = new tmpl($this->pwd() . 'grid.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
