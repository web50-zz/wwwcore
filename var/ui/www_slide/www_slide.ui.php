<?php
/**
*	ПИ "WWW: Слайды"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_www_slide extends user_interface
{
	public $title = 'WWW: Слайды';

	protected $deps = array(
		'main' => array(
			'www_slide.slide',
			'www_slide.group',
		),
		'slide' => array(
			'www_slide.slide_grid',
		),
		'slide_grid' => array(
			'www_slide.content_type',
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
		$data = data_interface::get_instance('www_slide')->get((int)$this->get_args('_spid'));
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
	public function sys_slide()
	{
		$tmpl = new tmpl($this->pwd() . 'slide.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       
	*/
	public function sys_slide_grid()
	{
		$tmpl = new tmpl($this->pwd() . 'slide_grid.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_slide_image_form()
	{
		$tmpl = new tmpl($this->pwd() . 'slide_image_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_slide_flash_form()
	{
		$tmpl = new tmpl($this->pwd() . 'slide_flash_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_slide_video_form()
	{
		$tmpl = new tmpl($this->pwd() . 'slide_video_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_slide_video2_form()
	{
		$tmpl = new tmpl($this->pwd() . 'slide_video2_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_slide_text_form()
	{
		$tmpl = new tmpl($this->pwd() . 'slide_text_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       Page configure form
	*/
	protected function sys_content_type()
	{
		$tmpl = new tmpl($this->pwd() . 'content_type.js');
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
