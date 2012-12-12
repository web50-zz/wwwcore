<?php
/**
*	ПИ "WWW: Персоны"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_www_person extends user_interface
{
	public $title = 'WWW: Персоны';

	protected $deps = array(
		'main' => array(
			'www_person.grid',
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
		$data = array(
			'persons' => $this->get_persons(),
			'path' => data_interface::get_instance('www_person')->path_to_storage,
		);

                return $this->parse_tmpl('content.html', $data);
        }

	/**
	*	Получить все доступные услуги
	*/
	private function get_persons()
	{
                $di = data_interface::get_instance('www_person');
		$di->push_args(array());
		$di->_flush();
		$di->set_order('order');
		$di->_get();
		$di->pop_args();
		return $di->get_results();
	}
	
	/**
	*       Управляющий JS админки
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'www_person.js');
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
	/*
	protected function sys_configure_form()
	{
		$tmpl = new tmpl($this->pwd() . 'configure_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	*/
}
?>
