<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru> 24072013
* @package	SBIN Diesel
*/
class ui_www_article_tags extends user_interface
{
	public $title = 'www: Публикации -  тэги в публикациях';

	protected $deps = array(
		'main' => array(
			'www_article_tags.grid',
		),
		'selector' => array(
			'www_article_tags.main',
			'www_article_tag_types.main',
		),

	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
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
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_selector()
	{
		$tmpl = new tmpl($this->pwd() . 'selector.js');
		response::send($tmpl->parse($this), 'js');
	}

}
?>
