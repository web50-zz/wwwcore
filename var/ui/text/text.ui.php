<?php
/**
*	ПИ "Текст"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_text extends user_interface
{
	public $title = 'Текст';

	protected $deps = array(
		'main' => array(
			'text.grid',
		)
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}
        
        /**
        *       Отрисовка контента для внешней части
        */
        public function pub_content()
        {
		$template = $this->get_args('tmpl', 'content.html');
                $di = data_interface::get_instance('text');
		$di->set_args($this->get_args());
		$data = $di->get();
		$data = array_merge($data, $this->args);
                return $this->parse_tmpl($template, $data);
        }
	
	/**
	*       Управляющий JS админки
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'text.js');
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
	public function sys_item_form()
	{
		$tmpl = new tmpl($this->pwd() . 'item_form.js');
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
