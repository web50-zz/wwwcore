<?php
/**
*
* @author	Fedot B Pozdmyakov <9@u9.ru> 13022014
* @package	SBIN Diesel
*/
class ui_www_faq extends user_interface
{
	public $title = 'WWW: FAQ';

	protected $deps = array(
		'main' => array(
			'www_faq.tree',
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
	public function sys_faq_form()
	{
		$tmpl = new tmpl($this->pwd() . 'faq_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
