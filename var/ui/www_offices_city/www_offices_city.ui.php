<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru> 2103201 21032013
* @package	SBIN Diesel
*/
class ui_www_offices_city extends user_interface
{
	public $title = 'www: офисы города';

	protected $deps = array(
		'main' => array(
			'www_offices_city.grid',
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
                $data = array(
			'records' => data_interface::get_instance('www_offices_city')
				->push_args(array())
				->set_order('id', 'DESC')
				->_get()
				->pop_args()
				->get_results()
		);
                return $this->parse_tmpl($template, $data);
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
