<?php
/**
*	ПИ "WWW: Отзывы"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_www_response extends user_interface
{
	public $title = 'www: Отзывы';

	protected $deps = array(
		'main' => array(
			'www_response.tree',
		),
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}
	
	/**
	*       Точка входа
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'main.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       Дерево дискуссий
	*/
	public function sys_tree()
	{
		$tmpl = new tmpl($this->pwd() . 'tree.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_response_form()
	{
		$tmpl = new tmpl($this->pwd() . 'response_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
