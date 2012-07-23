<?php
/**
*	ПИ "Управление баннерами"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_banner extends user_interface
{
	public $title = 'Управление баннерами';

	protected $deps = array(
		'main' => array(
			'banner.banner',
			'banner.group',
		),
		'banner' => array(
			'banner.banner_grid',
		),
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	/**
	*	Вывод баннеров
	*/
	protected function pub_content()
	{
		$data = data_interface::get_instance('banner')->get((int)$this->get_args('_spid'));
                return $this->parse_tmpl("content.html", $data);
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
	public function sys_group()
	{
		$tmpl = new tmpl($this->pwd() . 'group.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_group_form()
	{
		$tmpl = new tmpl($this->pwd() . 'group_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       Управляющий JS админки
	*/
	public function sys_banner()
	{
		$tmpl = new tmpl($this->pwd() . 'banner.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       
	*/
	public function sys_banner_grid()
	{
		$tmpl = new tmpl($this->pwd() . 'banner_grid.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_banner_image_form()
	{
		$tmpl = new tmpl($this->pwd() . 'banner_image_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_banner_flash_form()
	{
		$tmpl = new tmpl($this->pwd() . 'banner_flash_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       Page configure form
	*/
	protected function sys_configure_form()
	{
		$tmpl = new tmpl($this->pwd() . 'configure_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
