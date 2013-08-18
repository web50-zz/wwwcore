<?php
/**
*
* @author   9* Fedot B Pozdnyakov  <9@u9.ru>  fully refactored 23072013
* @package	SBIN Diesel
*/
class ui_www_article extends user_interface
{
	public $title = 'www: Публикации - список и формы';

	protected $deps = array(
		'main' => array(
			'www_article.grid',
			'www_article_type.main',
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
	public function sys_grid()
	{
		$tmpl = new tmpl($this->pwd() . 'grid.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_form()
	{
		$tmpl = new tmpl($this->pwd() . 'form.js');
		response::send($tmpl->parse($this), 'js');
	}

}
?>
