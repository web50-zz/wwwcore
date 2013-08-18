<?php
/**
*
* DIESEL UI:
* @author	9* Fedot B Pozdnyakov <9@u9.ru> 23072012
* @access	public
* @package	SBIN Diesel
*/
class ui_www_article_type extends user_interface
{
	public $title = 'www: Публикации - типы статей';
	protected $deps = array(
		'main' => array(
			'www_article_type.tree',
		),
		'node_form' => array(
		),
	);

	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	/**
	*       Управляющий JS админки
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'main.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       Управляющий JS админки
	*/
	public function sys_tree()
	{
		$tmpl = new tmpl($this->pwd() . 'tree.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма узла 
	*/
	public function sys_node_form()
	{
		$tmpl = new tmpl($this->pwd() . 'node_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	/**
	*       ExtJS - выбор категории 
	*/
	public function sys_category_selection()
	{
		$tmpl = new tmpl($this->pwd() . 'category_selection.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
