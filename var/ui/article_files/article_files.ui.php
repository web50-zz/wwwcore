<?php
/**
*
* @author       9*  9@u9.ru  	
* @package	CFCMS
*/
class ui_article_files extends user_interface
{
	public $title = 'Статьи: Файлы';

	protected $deps = array(
		'main' => array(
			'article_files.item_form',
			'article_files.grid',
		),
		'grid' => array(
			'article_files.types',
		),
		'item_form' => array(
			'article_files.types',
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
	
	protected function sys_types()
	{
		$tmpl = new tmpl($this->pwd() . 'types.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
